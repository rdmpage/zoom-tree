<?php

// Read a tree and convert to simple JSON structure

error_reporting(E_ALL);

require_once('tree/node.php');
require_once('tree/tree.php');
require_once('tree/node_iterator.php');
require_once('tree/tree-parse.php');
require_once('tree/tree-order.php');
require_once('tree/utils.php');

require_once('tree/fake_priority_queue.php');


//----------------------------------------------------------------------------------------
// Read a Newick tree and output data structure
function read_tree($newick)
{
	// Read tree
	$t = parse_newick($newick);

	//------------------------------------------------------------------------------------
	// Order tree to look nice
	$t->BuildWeights($t->GetRoot());
	$o = new RightOrder($t);
	$o->Order();

	//------------------------------------------------------------------------------------
	// Compute INORDER traversal of tree
	$order_to_node 		= array();
	$order_to_span 		= array();
	$order_to_crossings = array();

	// Get INORDER ordering
	get_inorder($t, $order_to_node);

	// Get span (in INORDER) for each internal node, that is, the INORDER position for
	// left and right children of node. This defines range of the vertical line we need
	// to draw for each internal node
	$n = count($order_to_node);
	for ($i = 0; $i < $n; $i++)
	{
		$q = $order_to_node[$i];
	
		if ($q->IsLeaf())
		{
		}
		else
		{
			$order_to_span[$i] = array();
			$left = $q->GetChild()->GetAttribute('order');
			$right = $q->GetChild()->GetRightMostSibling()->GetAttribute('order');
		
			$order_to_span[$i] = array($left, $right);
		}
	}

	// Any ancestor of this node whose left-right span includes the current node will require a 
	// vertical line to be drawn below this node
	for ($i = 0; $i < $n; $i++)
	{
		$q = $order_to_node[$i];
	
		$order_to_crossings[$i] = array();
	
		while ($q->GetAncestor())
		{
			$anc 		= $q->GetAncestor();
			$anc_order 	= $anc->GetAttribute('order');
		
			if (($i > $order_to_span[$anc_order][0]) && ($i < $order_to_span[$anc_order][1]))
			{
				$order_to_crossings[$i][] = $anc_order;
			}
		
			$q = $anc;
		}
	}

	//------------------------------------------------------------------------------------

	// each node will be placed in a rect w x h
	// since we won't know this until we decide on drawing size and fonts, compute 
	// using arbitrary units of 1000 x 100
	
	$w = 1000;
	$h = 100;

	get_node_heights($t, $w);
	get_max_subtree_height($t);

	//------------------------------------------------------------------------------------
	// The tree object
	
	$tree_obj = new stdclass;
	
	$tree_obj->newick = $newick;
	
	$tree_obj->nodes = array();
	$tree_obj->inorder = array();
	$tree_obj->crossings = array();

	// visit each node
	$n = new NodeIterator ($t->getRoot());
	$q = $n->Begin();
	while ($q != NULL)
	{
		$node = new stdclass;	
		$node->id = $q->GetId();
		
	
		// leaf?
		$node->leaf = false;
		if ($q->IsLeaf())
		{
			$node->leaf = true;
		}
	
		// position of this node in INORER ordering
		$node->order = $q->GetAttribute('order');
	
		// store node's label
		$label = $q->GetLabel();
		if ($label != '')
		{
			$node->label = $label;
		}
	
		// get ancestor, and direction w.r.t. node. We use this to draw the left-most
		// par tof the line from node to ancestor
		if ($q->GetAncestor())
		{
			$node->parent = $q->GetAncestor()->GetId();
		
			// direction of connection to ancestor
			$node->style = 0;
			if ($q->IsChild())
			{
				$node->style = 0;
			}
			else
			{
				if ($q->GetSibling())
				{
					$node->style = 1;
				}
				else
				{
					$node->style = 2;
				}
			}		
		}
		
		// store this node
		$tree_obj->nodes[$node->id] = $node;

		// store node in list that will be sorted INORDER
		$tree_obj->inorder[$q->GetAttribute('order')] = $node->id;

		// store nodes whose horizontal lines will cross below this node (if any)
		$tree_obj->crossings[$q->GetAttribute('order')] = $order_to_crossings[$q->GetAttribute('order')];	
	
		// display bounds of this row
		$node->bounds = array(0, 0, $w, $h);
	
		// display position of this node
		$pt = $q->GetAttribute('xy');
		$node->xy = array($pt['x'], $h / 2.0);
	
		// display position of ancestor
		if ($q->GetAncestor())
		{
			$pt = $q->GetAncestor()->GetAttribute('xy');
			$node->ancxy = array($pt['x'], $h / 2.0);		
		}
		
		$q = $n->Next();
	}
	
	// Now we can compute polygons onasce we have visited the whole tree
	$q = $n->Begin();
	while ($q != NULL)
	{		
		// span of internal node (ids of leftmost and rightmost descendants),
		// we need this to help draw polygons for collapsed subtrees
		if (!$q->IsLeaf())
		{
			$subtree = get_subtree_circuit($q);
			$left 	= $subtree->leftmost;
			$right 	= $subtree->rightmost;
			
			// positions of left and right span
			$left_pt 	= $left->GetAttribute('xy');
			$right_pt 	= $right->GetAttribute('xy');
	
			// largest x value of any descendant of q
			$subtree_x = $q->GetAttribute('max_x');
	
			$pt_left = array(
				'x' => $subtree_x,
				'y' => 0 
			);
	
			$pt_right = array(
				'x' => $subtree_x,
				'y' => $h
			);
	
			// Draw polygon (triangle)
			$pts = array();
	
			$pt_root = $q->GetAttribute('xy');
			$pt_root['y'] = $h / 2.0;
			//$pt_root['y'] -= 0.5;
	
			$pts[] = array($pt_root['x'], $pt_root['y']); // root of subtree
			$pts[] = array($pt_left['x'], $pt_left['y']); // left 
			$pts[] = array($pt_right['x'], $pt_right['y']);// right
			
			$pts[] = array($pt_root['x'], $pt_root['y']); // root of subtree
			
			$node->polygon = 
			
			$tree_obj->nodes[$q->GetId()]->polygon = $pts;
		}
		
		$q = $n->Next();		
	}
	

	// sort INORDER
	ksort($tree_obj->inorder);

	//------------------------------------------------------------------------------------

	// Flags for internal nodes, by default internal nodes are "closed" to start with
	$open_flags = array();	
	foreach ($tree_obj->nodes as $node_id => $node)
	{
		$open_flags[$node_id] = false;
	}

	// List of nodes to be included in collapsed tree
	$subtree = array();

	// Include root of tree in collapsed tree
	add_children_to_subtree($subtree, $t->GetRoot(), true);

	// Initialise the priority queue
	$queue = array();

	// Add root of tree to the priority queue
	add_children_to_queue($queue, $t->GetRoot());
		
	// zoom levels	
	$initial_size = 9; // number of rows for tree at zoom level 1
	
	$num_nodes = $t->num_nodes;
	
	// number of zoom levels needed to generate complete tree
	$zoom_levels = ceil(log(($t->num_nodes / $initial_size),2)) + 1;
	
	//$zoom_levels = 2; // debugging
	
	$tree_obj->min_zoom = 1;
	$tree_obj->max_zoom = $zoom_levels;
		
	$tree_obj->zoom = new stdclass;
	
	// Grow the collapsed tree until we reach size limit at each zoom level
	for ($zoom = 1; $zoom <= $zoom_levels; $zoom++)
	{
		// how many lines is this drawing allowed?
		$k = pow(2, $zoom - 1) * $initial_size;

		while(count($queue) > 0 && count($subtree) < $k)
		{
			// pop off next node to add
			$c = array_shift($queue);
			
			// note implicit assumption that tree is binary,
			// if we have a polytomy then subtree may grow beyond k 
			// without us realising it (e.g., last node added could add 10 addiitonal nodes)
			add_children_to_subtree($subtree, $c->node);

			add_children_to_queue($queue, $c->node);
		}
		
		// Any internal node that has chilkdren in subtree will be drawn "open"		
		foreach ($subtree as $index => $node_id)
		{
			if (isset($tree_obj->nodes[$node_id]->parent))
			{
				$open_flags[$tree_obj->nodes[$node_id]->parent] = true;
			}
		}
		
		print_r($subtree);
		//print_r($open_flags);
		
		
		//ksort($subtree, SORT_NUMERIC);
		
		foreach ($subtree as $node_id)
		{
			echo str_pad($node_id, 4, ' ', STR_PAD_LEFT);
			
			echo ' ' .  str_pad($tree_obj->nodes[$node_id]->order, 4, ' ', STR_PAD_LEFT);
			
			if ($open_flags[$node_id])
			{
				echo " +";
			}
			
			echo "\n";
		}
		
		$tree_obj->zoom->{$zoom} = new stdclass;
		
		$tree_obj->zoom->{$zoom}->inorder = array();
		$tree_obj->zoom->{$zoom}->type = array();
		
		foreach ($subtree as $node_id)
		{
			$tree_obj->zoom->{$zoom}->inorder[$tree_obj->nodes[$node_id]->order] = $node_id;
			
			$type = '';
			if ($tree_obj->nodes[$node_id]->leaf)
			{
				$type = 'leaf';
			}
			else
			{
				if ($open_flags[$node_id])
				{
					$type = 'internal_open';
				}
				else
				{
					$type = 'internal_closed';					
				}
			}
			
			$tree_obj->zoom->{$zoom}->type[$node_id] = $type;
		}
		
		// sort nodes in order we will draw them 
		ksort($tree_obj->zoom->{$zoom}->inorder, SORT_NUMERIC);
	}	

	return $tree_obj;
}

$newick = "('KJ836409.1':0.03942,(((('KJ837499.1':0.00079,('HQ948094.1':0.00584,(('KJ836642.1':0.00224,('KJ836862.1':0.00000,'KJ836538.1':0.00238)'Heriades crenulatus':0.00014)'Heriades crenulatus':0.00070,((('KJ838652.1':0.00000,'KJ839820.1':0.00007)'Heriades crenulatus':0.00007,'KJ836425.1':0.00000)'Heriades crenulatus':0.00007,'HQ948093.1':0.00000)'Heriades crenulatus':0.00170)'Heriades crenulatus':0.00135)'Heriades crenulatus':0.00039)'Heriades crenulatus':0.02093,((((('KT074045.1':0.00000,'KJ836515.1':0.00000)'Heriades truncorum':0.00000,'KJ838226.1':0.00000)'Heriades truncorum':0.00000,'HM901923.1':0.00000)'Heriades truncorum':0.00059,'KJ836448.1':0.00179)'Heriades truncorum':0.00097,'KJ839494.1':0.00141)'Heriades truncorum':0.03474)Heriades:0.03262,('KR786504.1':0.00510,(('FJ582232.1':0.00000,('KJ163559.1':0.00236,'FJ582230.1':0.00482)'Heriades carinatus':0.00009)'Heriades carinatus':0.00067,'FJ582231.1':0.00172)'Heriades carinatus':0.00451)'Heriades carinatus':0.05182)Heriades:0.01194,(((((((('KX957905.1':0.00000,'GU705980.1':0.00478)'Osmia bicornis':0.00001,'KJ837790.1':0.00000)'Osmia bicornis':0.00001,'KJ837713.1':0.00000)'Osmia bicornis':0.00069,((((((((((((((('KX957869.1':0.00000,'KX374766.1':0.00000)'Osmia bicornis':0.00000,'KX957868.1':0.00000)'Osmia bicornis':0.00000,'KX957867.1':0.00000)'Osmia bicornis':0.00000,'KX957865.1':0.00000)'Osmia bicornis':0.00000,'KX957864.1':0.00000)'Osmia bicornis':0.00000,'KX957863.1':0.00000)'Osmia bicornis':0.00000,'KX957862.1':0.00000)'Osmia bicornis':0.00000,'KX957861.1':0.00000)'Osmia bicornis':0.00000,'KX957860.1':0.00000)'Osmia bicornis':0.00000,'KX374767.1':0.00000)'Osmia bicornis':0.00000,'GU705983.1':0.00000)'Osmia bicornis':0.00060,('KX957904.1':0.00178,(('KX374770.1':0.00000,'KX957903.1':0.00001)'Osmia bicornis':0.00001,'GU705978.1':0.00000)'Osmia bicornis':0.00060)'Osmia bicornis':0.00178)'Osmia bicornis':0.00061,'KY121842.1':0.00118)Apoidea:0.00116,('KX957866.1':0.00000,((((('JQ909849.1':0.00238,'GU705979.1':0.00000)'Osmia bicornis':0.00000,'KT164634.1':0.00000)'Osmia bicornis':0.00000,'KT074073.1':0.00000)'Osmia bicornis':0.00000,'KY121818.1':0.00000)Apoidea:0.00000,'KY121823.1':0.00000)Apoidea:0.00000)Apoidea:0.00003)Apoidea:0.00019,'KC709831.1':0.00000)Apoidea:0.00172)Apoidea:0.01101,'AF250940.1':0.00780)Apoidea:0.00451,('AF250941.1':0.01160,((((('EU726628.1':0.00000,'EU726627.1':0.00000)'Osmia cornifrons':0.00000,'EU726626.1':0.00000)'Osmia cornifrons':0.00000,'EU726621.1':0.00000)'Osmia cornifrons':0.00000,'EU726549.1':0.00000)'Osmia cornifrons':0.00125,('EU726624.1':0.00000,'EU726601.1':0.00000)'Osmia cornifrons':0.00114)'Osmia cornifrons':0.02131)Osmia:0.00363)Apoidea:0.05114,((('KJ838228.1':0.00239,'HM901915.1':0.00239)'Hoplitis claviventris':0.00038,'KJ837591.1':0.00201)'Hoplitis claviventris':0.03980,(('KM568799.1':0.00000,'KM562022.1':0.00000)'Hoplitis sp. bc4':0.03680,'HQ948088.1':0.03826)Hoplitis:0.01356)Hoplitis:0.02205)Apoidea:0.01340,('KC560283.1':0.00647,('KY072536.1':0.00206,(((((((((((((((((('KY072692.1':0.00000,'KY072384.1':0.00238)'unclassified Apoidea':0.00000,'KY072512.1':0.00000)'unclassified Apoidea':0.00000,'KY072457.1':0.00000)'unclassified Apoidea':0.00000,'KY072408.1':0.00000)'unclassified Apoidea':0.00000,'KY072378.1':0.00000)'unclassified Apoidea':0.00000,'KY072344.1':0.00000)'unclassified Apoidea':0.00000,'KY072316.1':0.00000)'unclassified Apoidea':0.00000,'KY072314.1':0.00000)'unclassified Apoidea':0.00000,'KY072271.1':0.00000)'unclassified Apoidea':0.00000,'KY072263.1':0.00000)'unclassified Apoidea':0.00000,'KY072262.1':0.00000)'unclassified Apoidea':0.00000,'KY072261.1':0.00000)'unclassified Apoidea':0.00000,'KY072174.1':0.00000)'unclassified Apoidea':0.00000,'KY072146.1':0.00000)'unclassified Apoidea':0.00000,'KP259020.1':0.00000)Apoidea:0.00000,'KP259004.1':0.00000)Apoidea:0.00234,(('KY072458.1':0.00000,(('KY072490.1':0.00000,'KY072495.1':0.00000)'unclassified Apoidea':0.00000,('KY072601.1':0.00000,'KY072662.1':0.00000)'unclassified Apoidea':0.00000)'unclassified Apoidea':0.00000)'unclassified Apoidea':0.00000,(('KY072382.1':0.00000,'KP259033.1':0.00000)Apoidea:0.00000,'KY072383.1':0.00000)Apoidea:0.00000)Apoidea:0.00004)Apoidea:0.00019,('KY072600.1':0.00237,(('KY072422.1':0.00000,('KY072388.1':0.00000,'KY072389.1':0.00000)'unclassified Apoidea':0.00000)Apoidea:0.00000,(('KY072370.1':0.00000,'KY072377.1':0.00000)'unclassified Apoidea':0.00000,('KP259058.1':0.00000,'KY072268.1':0.00000)Apoidea:0.00000)Apoidea:0.00000)Apoidea:0.00000)Apoidea:0.00001)Apoidea:0.00220)Apoidea:0.00032)Apoidea:0.00071)Apoidea:0.02660)Apoidea:0.00696)Apoidea:0.03942;";

// BLAST query = MW496845
//$newick='((JQ534364.1:0.000664498,HM377932.1:0.00238119):0.0158508,((GU336118.1:0.0299451,((((MF923603.1:0.00144871,JQ560149.1:7.25928e-05):0.00103588,(MF922836.1:0.00105745,(((GU658490.1:0,JQ554463.1:0):3.63216e-05,MF924198.1:0.00148498):0.000238447,MF924177.1:0.0012844):0.000465396):0.000489872):0.00762471,((HQ556542.1:0,JQ551610.1:0.0015284):0.00019077,(JQ565745.1:0.00282908,HM408169.1:0.000216611):0.00133673):0.00819708):0.0130086,AF277443.1:0.0278096):0.00213639):0.00630278,((JN266473.1:0.000332212,(HQ553212.1:0.00119493,MK767790.1:0.000326372):0.00119063):0.0302603,((JN262846.1:0.0191797,(((((JQ556316.1:0.00148774,(HM408299.1:0.00151358,HM409842.1:7.71581e-06):3.51094e-05):4.07505e-05,JQ558137.1:0.00148209):0.000639751,(JQ545067.1:0,JQ548064.1:1.32211e-05):0.00241355):0.00710851,(((HM408168.1:6.13358e-05,(JQ556315.1:0,JQ548044.1:5.82833e-06):0.00146112):0.000281758,(((((JQ551063.1:0.00305049,(JQ547907.1:0,(JQ552569.1:0,JQ552568.1:0.00152472):0.0015266):4.33725e-07):1.31139e-06,HM408460.1:0.00152134):2.9275e-06,HM408461.1:0):1.58982e-05,JQ552570.1:0.00303376):0.000334128,JQ562164.1:0.00424939):0.00124682):0.00464347,((KX300289.1:0,JX571578.1:0):0.00152252,JF854867.1:0):0.00457624):0.00950048):0.00406438,((((((JQ578644.1:0.00274102,(JQ571546.1:0.00278948,((JQ569548.1:0.00306,GU700040.1:0):0.000102627,JQ578086.1:0.00142294):0.000261448):0.000310879):0.00051296,JQ573881.1:0.0056083):0.00560477,(HM403389.1:0,(JQ548012.1:0,JQ564645.1:9.36314e-06):2.63113e-05):0.00712252):0.00564858,(JQ568903.1:0.00164051,((JN266340.1:0,(JQ547905.1:0,JF844512.1:0.00152698):7.70211e-06):0.000124126,JQ569694.1:0.00292893):0.00141605):0.0146022):0.000947217,(((HQ568274.1:0,(KF533459.1:0,JN266461.1:0.00152526):9.53151e-06):0.00242324,HQ567922.1:0.003686):0.00161778,KX300252.1:0.00451872):0.00579766):0.00158158,((JX571167.1:0,JX571169.1:0.0015408):0.000666605,JX571168.1:0.00391124):0.0244588):0.00196066):0.000455247):0.00102225,((((((JQ556321.1:0.00117271,((((GU147164.1:0,(JQ574230.1:0,GU335433.1:1.36745e-06):5.55217e-06):3.97566e-06,JQ551045.1:0.00152198):2.46985e-06,GU160538.1:0):2.96526e-05,JQ551046.1:0.00301992):0.000350714):0.000623485,((GU335427.1:0,HQ556675.1:0.0015271):0.000280422,JQ559031.1:0.00124242):0.000903601):0.00129078,GU160540.1:0.00482864):0.00189019,HQ571061.1:0.00577895):0.00679539,(KX300291.1:0,JN262740.1:0):0.0135512):0.00946709,(MN621041.1:0.0257085,(((((JQ557343.1:0.00121528,(GU699730.1:0.00149222,GU147586.1:2.9081e-05):0.000307566):0.00118575,(GU147583.1:0,JQ560034.1:0.00152346):0.0003398):0.00113981,HM403740.1:0.000383613):0.0244556,MK767638.1:0.0271593):0.00614908,(((OM594190.1:0.0105504,((GU335861.1:0.000189918,GU335864.1:0):0.0166235,MN621036.1:0.0169641):0.00535654):0.0014135,(MN621026.1:0.0107948,(JN266442.1:0,JN266444.1:0.00155798):0.0108116):0.00732199):0.00866236,(((((JQ555038.1:0.00153756,JQ547796.1:0.00150813):0.000326153,(JQ547664.1:0.00227641,((JQ546935.1:0,JQ567146.1:3.98983e-05):0.00079921,HM409276.1:0.000736233):0.000786605):0.00196743):0.00126957,JQ566102.1:0.0036319):0.00142321,HM408897.1:0.00397348):0.011195,(MK767210.1:0.00779523,(MW496845.1:0,MW496845.1:0):0.00291923):0.0165676):0.0104006):0.00245598):0.00266401):0.00165969):0.00186284):0.00152768):0.000738379):0.0158508);';
//$newick="(('JQ534364.1':0.00066,'HM377932.1':0.00238)Lepidoptera:0.01585,(('GU336118.1':0.02995,(((('MF923603.1':0.00145,'JQ560149.1':0.00007)'Cosmosoma festivum':0.00104,('MF922836.1':0.00106,((('GU658490.1':0.00000,'JQ554463.1':0.00000)'Cosmosoma festivum':0.00004,'MF924198.1':0.00148)'Cosmosoma festivum':0.00024,'MF924177.1':0.00128)'Cosmosoma festivum':0.00047)'Cosmosoma festivum':0.00049)'Cosmosoma festivum':0.00762,(('HQ556542.1':0.00000,'JQ551610.1':0.00153)Lepidoptera:0.00019,('JQ565745.1':0.00283,'HM408169.1':0.00022)Lepidoptera:0.00134)Lepidoptera:0.00820)Lepidoptera:0.01301,'AF277443.1':0.02781)Lepidoptera:0.00214)Lepidoptera:0.00630,(('JN266473.1':0.00033,('HQ553212.1':0.00119,'MK767790.1':0.00033)'Cosmosoma metallescens':0.00119)Cosmosoma:0.03026,(('JN262846.1':0.01918,((((('JQ556316.1':0.00149,('HM408299.1':0.00151,'HM409842.1':0.00001)'Lepidoptera sp. BOLD:AAA1399':0.00004)Lepidoptera:0.00004,'JQ558137.1':0.00148)Lepidoptera:0.00064,('JQ545067.1':0.00000,'JQ548064.1':0.00001)'Saurita tipulina':0.00241)Lepidoptera:0.00711,((('HM408168.1':0.00006,('JQ556315.1':0.00000,'JQ548044.1':0.00001)'Saurita afflicta':0.00146)Lepidoptera:0.00028,((((('JQ551063.1':0.00305,('JQ547907.1':0.00000,('JQ552569.1':0.00000,'JQ552568.1':0.00152)'Saurita afflicta':0.00153)'Saurita afflicta':0.00000)'Saurita afflicta':0.00000,'HM408460.1':0.00152)Lepidoptera:0.00000,'HM408461.1':0.00000)Lepidoptera:0.00002,'JQ552570.1':0.00303)Lepidoptera:0.00033,'JQ562164.1':0.00425)Lepidoptera:0.00125)Lepidoptera:0.00464,(('KX300289.1':0.00000,'JX571578.1':0.00000)'Mesothen desperata':0.00152,'JF854867.1':0.00000)'Mesothen desperata':0.00458)Lepidoptera:0.00950)Lepidoptera:0.00406,(((((('JQ578644.1':0.00274,('JQ571546.1':0.00279,(('JQ569548.1':0.00306,'GU700040.1':0.00000)Lepidoptera:0.00010,'JQ578086.1':0.00142)Lepidoptera:0.00026)Lepidoptera:0.00031)Lepidoptera:0.00051,'JQ573881.1':0.00561)Lepidoptera:0.00560,('HM403389.1':0.00000,('JQ548012.1':0.00000,'JQ564645.1':0.00001)'Dycladia sp. emeritaDHJ03':0.00003)Lepidoptera:0.00712)Lepidoptera:0.00565,('JQ568903.1':0.00164,(('JN266340.1':0.00000,('JQ547905.1':0.00000,'JF844512.1':0.00153)Lepidoptera:0.00001)Lepidoptera:0.00012,'JQ569694.1':0.00293)Lepidoptera:0.00142)Lepidoptera:0.01460)Lepidoptera:0.00095,((('HQ568274.1':0.00000,('KF533459.1':0.00000,'JN266461.1':0.00153)'Dycladia lucetia':0.00001)'Dycladia lucetia':0.00242,'HQ567922.1':0.00369)'Dycladia lucetia':0.00162,'KX300252.1':0.00452)'Dycladia lucetia':0.00580)Lepidoptera:0.00158,(('JX571167.1':0.00000,'JX571169.1':0.00154)'Arctiinae sp. JAT15':0.00067,'JX571168.1':0.00391)'Arctiinae sp. JAT15':0.02446)Lepidoptera:0.00196)Lepidoptera:0.00046)Lepidoptera:0.00102,(((((('JQ556321.1':0.00117,(((('GU147164.1':0.00000,('JQ574230.1':0.00000,'GU335433.1':0.00000)'Cosmosoma sp. stibostictumDHJ01':0.00001)'Cosmosoma sp. stibostictumDHJ01':0.00000,'JQ551045.1':0.00152)'Cosmosoma sp. stibostictumDHJ01':0.00000,'GU160538.1':0.00000)'Cosmosoma sp. stibostictumDHJ01':0.00003,'JQ551046.1':0.00302)'Cosmosoma sp. stibostictumDHJ01':0.00035)'Cosmosoma sp. stibostictumDHJ01':0.00062,(('GU335427.1':0.00000,'HQ556675.1':0.00153)Lepidoptera:0.00028,'JQ559031.1':0.00124)Lepidoptera:0.00090)Lepidoptera:0.00129,'GU160540.1':0.00483)Lepidoptera:0.00189,'HQ571061.1':0.00578)Lepidoptera:0.00680,('KX300291.1':0.00000,'JN262740.1':0.00000)'Cosmosoma harpalyce':0.01355)Lepidoptera:0.00947,('MN621041.1':0.02571,((((('JQ557343.1':0.00122,('GU699730.1':0.00149,'GU147586.1':0.00003)Lepidoptera:0.00031)Lepidoptera:0.00119,('GU147583.1':0.00000,'JQ560034.1':0.00152)'Loxophlebia flavipicta':0.00034)Lepidoptera:0.00114,'HM403740.1':0.00038)Lepidoptera:0.02446,'MK767638.1':0.02716)Lepidoptera:0.00615,((('OM594190.1':0.01055,(('GU335861.1':0.00019,'GU335864.1':0.00000)'Loxophlebia sp. Janzen01':0.01662,'MN621036.1':0.01696)Loxophlebia:0.00536)Loxophlebia:0.00141,('MN621026.1':0.01079,('JN266442.1':0.00000,'JN266444.1':0.00156)'Mesothen desperata':0.01081)Arctiinae:0.00732)Arctiinae:0.00866,((((('JQ555038.1':0.00154,'JQ547796.1':0.00151)Arctiinae:0.00033,('JQ547664.1':0.00228,(('JQ546935.1':0.00000,'JQ567146.1':0.00004)'Loxophlebia imitata':0.00080,'HM409276.1':0.00074)Lepidoptera:0.00079)Lepidoptera:0.00197)Lepidoptera:0.00127,'JQ566102.1':0.00363)Lepidoptera:0.00142,'HM408897.1':0.00397)Lepidoptera:0.01120,('MK767210.1':0.00780,('MW496845.1':0.00000,'MW496845.1':0.00000):0.00292):0.01657):0.01040):0.00246):0.00266):0.00166):0.00186):0.00153):0.00074):0.01585);";

//require_once('butterflies.php');
//require_once('AALF015423.php');
//require_once('phylo.io.php');
//require_once('examples/figwasp.php');

//require_once('Culex.php');


//$newick = file_get_contents('odonto_decoded.tre');

//$newick="(Staphylus_chlorocephala:0.0237732,((((((Hesperopsis_alpheus:0.000639391,Hesperopsis_alpheus:0.000884226):0.00308805,(((Hesperopsis_gracielae:0,Hesperopsis_gracielae:4.14235e-06):0.00113185,((Hesperopsis_gracielae:0,(Hesperopsis_gracielae:0,Hesperopsis_gracielae:0.00152654):4.96578e-06):4.96578e-06,Hesperopsis_alpheus:0):0.000391288):0.000425861,((Hesperopsis_gracielae:0.000515801,Hesperopsis_gracielae:0.00100782):0.000923844,Hesperopsis_gracielae:0.000601324):0.000338371):0.00709352):0.0114105,((Hesperopsis_libya:0.00143,(Hesperopsis_libya:0,Hesperopsis_libya:0.00154496):9.51686e-05):0.00147503,Hesperopsis_libya:0.00311137):0.0329149):0.010115,(((Muschampia_lavatherae:0.0014139,((Muschampia_lavatherae:0,Muschampia_lavatherae:1.69411e-05):1.69411e-05,Muschampia_lavatherae:0):0.000109141):0.000487225,Muschampia_lavatherae:0.00103562):0.00647381,((((Muschampia_lavatherae:0.00151711,((((Muschampia_lavatherae:0,Muschampia_lavatherae:0):0,Muschampia_lavatherae:0):0,Muschampia_lavatherae:0):0.00152069,(Muschampia_lavatherae:0.00152069,Muschampia_lavatherae:9.92705e-06):2.15901e-06):5.73105e-06):1.1395e-05,Muschampia_lavatherae:0):1.37187e-05,Muschampia_lavatherae:0):1.37187e-05,Muschampia_lavatherae:0):0.00735905):0.0428279):0.00256505,((((Staphylus_floridus:0.00479477,Staphylus_hayhurstii:0.00593613):0.00950263,(((Staphylus_ascalaphus:6.94104e-05,((Staphylus_ascalaphus:0,Staphylus_ascalaphus:0):7.80598e-05,'Lepidopterasp.BOLD:AAB4636':0.00297227):0.00145731):0.00126891,Staphylus_ascalaphus:0.00025782):0.00135436,Staphylus_mazans:0.00170222):0.021793):0.017035,(((((Staphylus_azteca:0.0048385,((((Staphylus_azteca:0.000155294,(Staphylus_azteca:0.000925573,Staphylus_azteca:0.00212012):0.000605355):0.00029665,Staphylus_azteca:0.00581649):0.000539017,(((Staphylus_azteca:0,(Staphylus_azteca:0.000375426,(Staphylus_azteca:0.00190361,(Staphylus_azteca:0,Staphylus_azteca:0.00152484):0.00114518):0.000384447):0.00115245):1.23462e-05,Staphylus_azteca:0):0.000177805,Staphylus_azteca:0.00134584):0.000422943):0.000739331,(Staphylus_azteca:0.000772086,Staphylus_azteca:0.000749213):0.000212706):0.00229232):0.0286512,(((Staphylus_caribbea:0.000807814,(((Staphylus_caribbea:0.00149666,(Staphylus_caribbea:0,Staphylus_caribbea:2.30837e-06):2.58021e-05):0.000657654,(Staphylus_caribbea:0.00150199,(Staphylus_caribbea:0,Staphylus_caribbea:0.00152446):2.0857e-05):0.000868337):0.000141939,'Lepidopterasp.BOLD:AAB1786':0.00061791):0.000716146):0.000367916,Staphylus_caribbea:0.000583016):0.000729467,(Staphylus_caribbea:0.00411255,Staphylus_caribbea:0.000460636):0.00461221):0.0289247):0.00220124,((Staphylus_musculus:0.0218633,(Staphylus_vulgata:0.00042134,Staphylus_vulgata:0.00109996):0.0300987):0.00627445,(Staphylus_melangon:0,(Staphylus_melangon:0.00151861,Staphylus_melangon:0.00152708):7.1837e-05):0.0380202):0.00204968):0.00036291,Staphylus_ascalon:0.0224798):0.000873614,((((((((Staphylus_oeta:0,(Staphylus_oeta:0.000755917,Staphylus_oeta:0.00393893):0.000766374):5.72473e-06,Staphylus_oeta:0):5.72473e-06,Staphylus_oeta:0):5.72473e-06,Staphylus_oeta:0):5.72473e-06,Staphylus_oeta:0):5.72473e-06,Staphylus_oeta:0):0.00067373,(Staphylus_oeta:0,Staphylus_oeta:0):0.00237279):0.0193498,(Staphylus_vulgata:0.000989222,(Staphylus_vulgata:0.000772344,((Staphylus_vulgata:0.00123535,(Staphylus_vulgata:0,Staphylus_vulgata:3.82946e-06):0.000287111):0.00134764,((Staphylus_vulgata:0,(Staphylus_vulgata:0,Staphylus_vulgata:0.00305233):5.29557e-06):0.000132615,(Staphylus_vulgata:0,Staphylus_vulgata:4.15268e-06):0.00139226):0.000178865):0.000368856):0.00072456):0.0221729):0.0070417):0.00529456):0.00517641,Bolla_imbras:0.0434287):0.00166742):0.000872441,((Staphylus_ecos:0.0162318,(((Staphylus_ceos:0,Staphylus_ceos:0):0,Staphylus_ceos:0):0.00221818,Staphylus_ceos:0.00541936):0.0117738):0.0183841,(Staphylus_sp._Janzen10:0.00679433,(Staphylus_vincula:0.00115124,(Staphylus_vincula:0,Staphylus_vincula:0):0.00189445):0.00703648):0.0173017):0.00400419):0.0237732);";

$newick="(MF546168.1:0.0237732,((((((KP895747.1:0.000639391,KP895745.1:0.000884226)Hesperopsis_alpheus:0.00308805,(((HQ543507.1:0,KP895750.1:4.14235e-06):0.00113185,((HQ543505.1:0,(KP895748.1:0,KP895757.1:0.00152654):4.96578e-06):4.96578e-06,JF861941.1:0):0.000391288):0.000425861,((KP895752.1:0.000515801,KP895755.1:0.00100782):0.000923844,KP895749.1:0.000601324):0.000338371)Hesperopsis_gracielae:0.00709352):0.0114105,((KP895737.1:0.00143,(KP895738.1:0,KP895740.1:0.00154496):9.51686e-05):0.00147503,KP895739.1:0.00311137)Hesperopsis_libya:0.0329149)Hesperopsis:0.010115,(((KP870251.1:0.0014139,((OK345694.1:0,KP870647.1:1.69411e-05):1.69411e-05,GU676160.1:0):0.000109141):0.000487225,OK345735.1:0.00103562):0.00647381,((((MW501583.1:0.00151711,((((MW503439.1:0,MW503023.1:0):0,MW499484.1:0):0,HQ004177.1:0):0.00152069,(MN145117.1:0.00152069,MK186200.1:9.92705e-06):2.15901e-06):5.73105e-06):1.1395e-05,OK345676.1:0):1.37187e-05,MW503421.1:0):1.37187e-05,MN143287.1:0):0.00735905)Muschampia_lavatherae:0.0428279):0.00256505,((((ON351018.1:0.00479477,HM428328.1:0.00593613):0.00950263,(((GU151661.1:6.94104e-05,((JF753157.1:0,GU150865.1:0):7.80598e-05,HM885448.1:0.00297227):0.00145731):0.00126891,ON351019.1:0.00025782):0.00135436,HQ581193.1:0.00170222)Staphylus_mazans:0.021793):0.017035,(((((GU155502.1:0.0048385,((((GU151676.1:0.000155294,(GU155500.1:0.000925573,JF778480.1:0.00212012):0.000605355):0.00029665,GU150881.1:0.00581649):0.000539017,(((GU151665.1:0,(GU151668.1:0.000375426,(GU151674.1:0.00190361,(GU155501.1:0,GU151673.1:0.00152484):0.00114518):0.000384447):0.00115245):1.23462e-05,JF761179.1:0):0.000177805,GU151672.1:0.00134584):0.000422943):0.000739331,(GU155505.1:0.000772086,GU151669.1:0.000749213):0.000212706):0.00229232)Staphylus_azteca:0.0286512,(((GU151663.1:0.000807814,(((HM885447.1:0.00149666,(GU161858.1:0,JF753161.1:2.30837e-06):2.58021e-05):0.000657654,(HM885911.1:0.00150199,(HM885909.1:0,HM885908.1:0.00152446):2.0857e-05):0.000868337):0.000141939,JF858328.1:0.00061791):0.000716146):0.000367916,JF762935.1:0.000583016):0.000729467,(JF762937.1:0.00411255,JF762936.1:0.000460636):0.00461221)Staphylus_caribbea:0.0289247):0.00220124,((MF546431.1:0.0218633,(MF545712.1:0.00042134,MF546957.1:0.00109996)Staphylus_vulgata:0.0300987):0.00627445,(MF545480.1:0,(MF545508.1:0.00151861,MF546357.1:0.00152708):7.1837e-05)Staphylus_melangon:0.0380202):0.00204968):0.00036291,OP231467.1:0.0224798):0.000873614,((((((((MZ336002.1:0,(MZ335598.1:0.000755917,MZ334988.1:0.00393893):0.000766374):5.72473e-06,MZ335920.1:0):5.72473e-06,MZ335764.1:0):5.72473e-06,MZ335526.1:0):5.72473e-06,MZ335467.1:0):5.72473e-06,MZ335101.1:0):0.00067373,(MZ336013.1:0,MZ335785.1:0):0.00237279)Staphylus_oeta:0.0193498,(GU151692.1:0.000989222,(GU151694.1:0.000772344,((JF761184.1:0.00123535,(GU151687.1:0,JF753171.1:3.82946e-06):0.000287111):0.00134764,((GU151697.1:0,(JF753170.1:0,GU151701.1:0.00305233):5.29557e-06):0.000132615,(GU151706.1:0,JF753169.1:4.15268e-06):0.00139226):0.000178865):0.000368856):0.00072456)Staphylus_vulgata:0.0221729):0.0070417):0.00529456)Staphylus:0.00517641,MW807749.1:0.0434287):0.00166742):0.000872441,((ON351020.1:0.0162318,(((KY019902.1:0,HM421563.1:0):0,HQ583467.1:0):0.00221818,JF861935.1:0.00541936)Staphylus_ceos:0.0117738):0.0183841,(GU150888.1:0.00679433,(ON351021.1:0.00115124,(ON351022.1:0,ON351022.1:0):0.00189445)Staphylus_vincula:0.00703648):0.0173017)Staphylus:0.00400419):0.0237732);";

$tree_obj = read_tree($newick);

file_put_contents('tree.json', json_encode($tree_obj));


?>
