<?php

// Generate tree images

error_reporting(E_ALL);

require_once('node.php');
require_once('tree.php');
require_once('node_iterator.php');
require_once('svg.php');

require_once('tree-parse.php');

require_once('fake_priority_queue.php');
require_once('utils.php');
require_once('inorder_draw.php');


//----------------------------------------------------------------------------------------
// Add node q to subtree of informative nodes
function add_children_to_subtree(&$subtree, $q, $is_root = false)
{
	// Add root to subtree, in any subsequent call $q will already be in the subtree
	if ($is_root)
	{
		$subtree[] = $q->id;
		$q->SetAttribute('marked', true);
	}
	
	// add children
	$children = get_children($q);
	
	foreach ($children as $child)
	{
		$subtree[] = $child->id;
		$child->SetAttribute('marked', true);
	}
	
	/*
	echo '<pre>';
	echo "Subtree\n";
	print_r($subtree);
	echo '</pre>';
	*/
	
}

//----------------------------------------------------------------------------------------
// Get node "heights" and store in x coordinate. Root has x=0, leaf furthest from root
// has x = limit
function get_node_heights($t, $limit = 400)
{
	$max_path_length = 0.0;		
	$t->GetRoot()->SetAttribute('path_length', $t->GetRoot()->GetAttribute('edge_length'));

	// Get path lengths
	$n = new PreorderIterator ($t->getRoot());
	$q = $n->Begin();
	while ($q != NULL)
	{			
		$d = $q->GetAttribute('edge_length');
		
		// Avoid negative branch lengths
		if ($d < 0.00001)
		{
			$d = 0.0;
		}
		
		if ($q != $t->GetRoot())
			$q->SetAttribute('path_length', $q->GetAncestor()->GetAttribute('path_length') + $d);

		$max_path_length = max($max_path_length, $q->GetAttribute('path_length'));
		$q = $n->Next();
	}
	
	// scale to drawing size
	$n = new NodeIterator ($t->getRoot());
	
	$q = $n->Begin();
	while ($q != NULL)
	{
		$pt = array();
		$pt['y'] = 0;
		$pt['x'] = ($q->GetAttribute('path_length') / $max_path_length) * $limit;
		
		$q->SetAttribute('xy', $pt);

		$q = $n->Next();
	}

}


//----------------------------------------------------------------------------------------
// Get max heights of subtree rooted at each node. This enables us to draw
// "closed" subtrees as shapes that cover the full range of node heights
function get_max_subtree_height($t)
{
	$n = new PreorderIterator ($t->GetRoot());
	$q = $n->Begin();
	while ($q != NULL)
	{	
		$pt = $q->GetAttribute('xy');		
		$x = $pt['x'];
		$q->SetAttribute('max_x', $x);

		$q = $n->Next();
	}

	// update each node with max_x of its descendants
	{
		$n = new NodeIterator ($t->GetRoot());
		$q = $n->Begin();
		while ($q != NULL)
		{	
			$anc = $q->GetAncestor();
			if ($anc)
			{
				$anc->SetAttribute('max_x', max($anc->GetAttribute('max_x'), $q->GetAttribute('max_x')));
			}

			$q = $n->Next();
		}
	}
}



$newick = "('KJ836409.1':0.03942,(((('KJ837499.1':0.00079,('HQ948094.1':0.00584,(('KJ836642.1':0.00224,('KJ836862.1':0.00000,'KJ836538.1':0.00238)'Heriades crenulatus':0.00014)'Heriades crenulatus':0.00070,((('KJ838652.1':0.00000,'KJ839820.1':0.00007)'Heriades crenulatus':0.00007,'KJ836425.1':0.00000)'Heriades crenulatus':0.00007,'HQ948093.1':0.00000)'Heriades crenulatus':0.00170)'Heriades crenulatus':0.00135)'Heriades crenulatus':0.00039)'Heriades crenulatus':0.02093,((((('KT074045.1':0.00000,'KJ836515.1':0.00000)'Heriades truncorum':0.00000,'KJ838226.1':0.00000)'Heriades truncorum':0.00000,'HM901923.1':0.00000)'Heriades truncorum':0.00059,'KJ836448.1':0.00179)'Heriades truncorum':0.00097,'KJ839494.1':0.00141)'Heriades truncorum':0.03474)Heriades:0.03262,('KR786504.1':0.00510,(('FJ582232.1':0.00000,('KJ163559.1':0.00236,'FJ582230.1':0.00482)'Heriades carinatus':0.00009)'Heriades carinatus':0.00067,'FJ582231.1':0.00172)'Heriades carinatus':0.00451)'Heriades carinatus':0.05182)Heriades:0.01194,(((((((('KX957905.1':0.00000,'GU705980.1':0.00478)'Osmia bicornis':0.00001,'KJ837790.1':0.00000)'Osmia bicornis':0.00001,'KJ837713.1':0.00000)'Osmia bicornis':0.00069,((((((((((((((('KX957869.1':0.00000,'KX374766.1':0.00000)'Osmia bicornis':0.00000,'KX957868.1':0.00000)'Osmia bicornis':0.00000,'KX957867.1':0.00000)'Osmia bicornis':0.00000,'KX957865.1':0.00000)'Osmia bicornis':0.00000,'KX957864.1':0.00000)'Osmia bicornis':0.00000,'KX957863.1':0.00000)'Osmia bicornis':0.00000,'KX957862.1':0.00000)'Osmia bicornis':0.00000,'KX957861.1':0.00000)'Osmia bicornis':0.00000,'KX957860.1':0.00000)'Osmia bicornis':0.00000,'KX374767.1':0.00000)'Osmia bicornis':0.00000,'GU705983.1':0.00000)'Osmia bicornis':0.00060,('KX957904.1':0.00178,(('KX374770.1':0.00000,'KX957903.1':0.00001)'Osmia bicornis':0.00001,'GU705978.1':0.00000)'Osmia bicornis':0.00060)'Osmia bicornis':0.00178)'Osmia bicornis':0.00061,'KY121842.1':0.00118)Apoidea:0.00116,('KX957866.1':0.00000,((((('JQ909849.1':0.00238,'GU705979.1':0.00000)'Osmia bicornis':0.00000,'KT164634.1':0.00000)'Osmia bicornis':0.00000,'KT074073.1':0.00000)'Osmia bicornis':0.00000,'KY121818.1':0.00000)Apoidea:0.00000,'KY121823.1':0.00000)Apoidea:0.00000)Apoidea:0.00003)Apoidea:0.00019,'KC709831.1':0.00000)Apoidea:0.00172)Apoidea:0.01101,'AF250940.1':0.00780)Apoidea:0.00451,('AF250941.1':0.01160,((((('EU726628.1':0.00000,'EU726627.1':0.00000)'Osmia cornifrons':0.00000,'EU726626.1':0.00000)'Osmia cornifrons':0.00000,'EU726621.1':0.00000)'Osmia cornifrons':0.00000,'EU726549.1':0.00000)'Osmia cornifrons':0.00125,('EU726624.1':0.00000,'EU726601.1':0.00000)'Osmia cornifrons':0.00114)'Osmia cornifrons':0.02131)Osmia:0.00363)Apoidea:0.05114,((('KJ838228.1':0.00239,'HM901915.1':0.00239)'Hoplitis claviventris':0.00038,'KJ837591.1':0.00201)'Hoplitis claviventris':0.03980,(('KM568799.1':0.00000,'KM562022.1':0.00000)'Hoplitis sp. bc4':0.03680,'HQ948088.1':0.03826)Hoplitis:0.01356)Hoplitis:0.02205)Apoidea:0.01340,('KC560283.1':0.00647,('KY072536.1':0.00206,(((((((((((((((((('KY072692.1':0.00000,'KY072384.1':0.00238)'unclassified Apoidea':0.00000,'KY072512.1':0.00000)'unclassified Apoidea':0.00000,'KY072457.1':0.00000)'unclassified Apoidea':0.00000,'KY072408.1':0.00000)'unclassified Apoidea':0.00000,'KY072378.1':0.00000)'unclassified Apoidea':0.00000,'KY072344.1':0.00000)'unclassified Apoidea':0.00000,'KY072316.1':0.00000)'unclassified Apoidea':0.00000,'KY072314.1':0.00000)'unclassified Apoidea':0.00000,'KY072271.1':0.00000)'unclassified Apoidea':0.00000,'KY072263.1':0.00000)'unclassified Apoidea':0.00000,'KY072262.1':0.00000)'unclassified Apoidea':0.00000,'KY072261.1':0.00000)'unclassified Apoidea':0.00000,'KY072174.1':0.00000)'unclassified Apoidea':0.00000,'KY072146.1':0.00000)'unclassified Apoidea':0.00000,'KP259020.1':0.00000)Apoidea:0.00000,'KP259004.1':0.00000)Apoidea:0.00234,(('KY072458.1':0.00000,(('KY072490.1':0.00000,'KY072495.1':0.00000)'unclassified Apoidea':0.00000,('KY072601.1':0.00000,'KY072662.1':0.00000)'unclassified Apoidea':0.00000)'unclassified Apoidea':0.00000)'unclassified Apoidea':0.00000,(('KY072382.1':0.00000,'KP259033.1':0.00000)Apoidea:0.00000,'KY072383.1':0.00000)Apoidea:0.00000)Apoidea:0.00004)Apoidea:0.00019,('KY072600.1':0.00237,(('KY072422.1':0.00000,('KY072388.1':0.00000,'KY072389.1':0.00000)'unclassified Apoidea':0.00000)Apoidea:0.00000,(('KY072370.1':0.00000,'KY072377.1':0.00000)'unclassified Apoidea':0.00000,('KP259058.1':0.00000,'KY072268.1':0.00000)Apoidea:0.00000)Apoidea:0.00000)Apoidea:0.00000)Apoidea:0.00001)Apoidea:0.00220)Apoidea:0.00032)Apoidea:0.00071)Apoidea:0.02660)Apoidea:0.00696)Apoidea:0.03942;";

require_once('butterflies.php');
require_once('AALF015423.php');

$t = parse_newick($newick);
//echo $t->WriteNewick() . "\n";

$drawing_options = new stdclass;
$drawing_options->zoom 			= 1;
$drawing_options->tree_width 	= 400;
$drawing_options->width 		= 1000;
$drawing_options->row_height 	= 12;

$t->BuildWeights($t->GetRoot());

get_node_heights($t, $drawing_options->tree_width);
get_max_subtree_height($t);

$initial_size = 9;
$zoom_levels = 7;

// store zoom level at which node first appears
$node_zoom_level = array(
	$t->GetRoot()->GetID() => 1
);

for ($zoom = 1; $zoom < $zoom_levels; $zoom++)
{

	// how many lines is this drawing allowed?
	$k = pow(2, $zoom - 1) * $initial_size;
	
	echo "k=$k\n";

	// Clear any "marked" nodes in input the tree
	clear_marks($t);

	// Clear list of nodes to be included in collapsed tree
	$subtree = array();

	// Include root of tree in collapsed tree
	add_children_to_subtree($subtree, $t->GetRoot(), true);

	// Clear the priority queue
	$queue = array();

	// Add root of tree to the priority queue
	add_children_to_queue($queue, $t->GetRoot());

	// Grow the collapsed tree until we reach size limit
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

	// Create a copy of the original tree containing just the "marked" nodes.
	// This is the subtree we will draw.
	
	$copy_of_tree = copy_marked_tree($t);
	
	// Store zoom levels for nodes that make their first appearance in this tree
	foreach ($copy_of_tree->id_to_node_map as $id => $q)
	{
		if (!isset($node_zoom_level[$id]))
		{
			$node_zoom_level[$id] = $zoom;
		}
			
		$q->SetAttribute('zoom', $node_zoom_level[$id]);
		
		// default state is not changed
		$q->SetAttribute('changed', false);
	}
	
	// find cases where node has different zoom level from its ancestor, 
	// we can use these to decide to display internal labels
	foreach ($copy_of_tree->id_to_node_map as $id => $q)
	{
		$p = $q->GetAncestor();
		if ($p)
		{
			if ($p->GetAttribute('zoom') != $q->GetAttribute('zoom'))
			{
				$p->SetAttribute('changed', true);
			}
		}
	}

	/*
	$node_count = 0;

	// dump copy of tree
	{
		echo "\nCopy of tree\n";
		$n = new PreorderIterator ($copy_of_tree->GetRoot());
		$q = $n->Begin();
		while ($q != NULL)
		{	
			//echo $q->GetLabel() . "\n";
			if ($q->IsLeaf())
			{
				//echo " *";
			}
			else
			{
				//echo " x=" . $q->original->GetAttribute('xy')['x'];
			}
		
			//echo "\n<br>";
			$q = $n->Next();
			
			$node_count++;
		}

	}
	*/
	
	/*
	// debugging inorder iterator
	$node_count = 0;
	{
		echo "\nInorder\n";
		$ni = new BinaryInorderIterator ($copy_of_tree->GetRoot());
		$q = $ni->Begin();
		while ($q != NULL)
		{	
			//echo $q->GetLabel() . "\n";
			$q = $ni->Next();
			
			$node_count++;
		}

	}	
	*/
	
	// for transitions we need to know what happens when we zoom in or out on a node
	// what row does the node occupy after one of these operations?
	

	// Draw tree in SVG
	$svg = inorder_draw_tree($copy_of_tree, $drawing_options);

	$svg_filename = $k . '.svg';
	file_put_contents($svg_filename, $svg);
}



?>
