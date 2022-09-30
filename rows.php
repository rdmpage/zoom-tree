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



$newick = "('KJ836409.1':0.03942,(((('KJ837499.1':0.00079,('HQ948094.1':0.00584,(('KJ836642.1':0.00224,('KJ836862.1':0.00000,'KJ836538.1':0.00238)'Heriades crenulatus':0.00014)'Heriades crenulatus':0.00070,((('KJ838652.1':0.00000,'KJ839820.1':0.00007)'Heriades crenulatus':0.00007,'KJ836425.1':0.00000)'Heriades crenulatus':0.00007,'HQ948093.1':0.00000)'Heriades crenulatus':0.00170)'Heriades crenulatus':0.00135)'Heriades crenulatus':0.00039)'Heriades crenulatus':0.02093,((((('KT074045.1':0.00000,'KJ836515.1':0.00000)'Heriades truncorum':0.00000,'KJ838226.1':0.00000)'Heriades truncorum':0.00000,'HM901923.1':0.00000)'Heriades truncorum':0.00059,'KJ836448.1':0.00179)'Heriades truncorum':0.00097,'KJ839494.1':0.00141)'Heriades truncorum':0.03474)Heriades:0.03262,('KR786504.1':0.00510,(('FJ582232.1':0.00000,('KJ163559.1':0.00236,'FJ582230.1':0.00482)'Heriades carinatus':0.00009)'Heriades carinatus':0.00067,'FJ582231.1':0.00172)'Heriades carinatus':0.00451)'Heriades carinatus':0.05182)Heriades:0.01194,(((((((('KX957905.1':0.00000,'GU705980.1':0.00478)'Osmia bicornis':0.00001,'KJ837790.1':0.00000)'Osmia bicornis':0.00001,'KJ837713.1':0.00000)'Osmia bicornis':0.00069,((((((((((((((('KX957869.1':0.00000,'KX374766.1':0.00000)'Osmia bicornis':0.00000,'KX957868.1':0.00000)'Osmia bicornis':0.00000,'KX957867.1':0.00000)'Osmia bicornis':0.00000,'KX957865.1':0.00000)'Osmia bicornis':0.00000,'KX957864.1':0.00000)'Osmia bicornis':0.00000,'KX957863.1':0.00000)'Osmia bicornis':0.00000,'KX957862.1':0.00000)'Osmia bicornis':0.00000,'KX957861.1':0.00000)'Osmia bicornis':0.00000,'KX957860.1':0.00000)'Osmia bicornis':0.00000,'KX374767.1':0.00000)'Osmia bicornis':0.00000,'GU705983.1':0.00000)'Osmia bicornis':0.00060,('KX957904.1':0.00178,(('KX374770.1':0.00000,'KX957903.1':0.00001)'Osmia bicornis':0.00001,'GU705978.1':0.00000)'Osmia bicornis':0.00060)'Osmia bicornis':0.00178)'Osmia bicornis':0.00061,'KY121842.1':0.00118)Apoidea:0.00116,('KX957866.1':0.00000,((((('JQ909849.1':0.00238,'GU705979.1':0.00000)'Osmia bicornis':0.00000,'KT164634.1':0.00000)'Osmia bicornis':0.00000,'KT074073.1':0.00000)'Osmia bicornis':0.00000,'KY121818.1':0.00000)Apoidea:0.00000,'KY121823.1':0.00000)Apoidea:0.00000)Apoidea:0.00003)Apoidea:0.00019,'KC709831.1':0.00000)Apoidea:0.00172)Apoidea:0.01101,'AF250940.1':0.00780)Apoidea:0.00451,('AF250941.1':0.01160,((((('EU726628.1':0.00000,'EU726627.1':0.00000)'Osmia cornifrons':0.00000,'EU726626.1':0.00000)'Osmia cornifrons':0.00000,'EU726621.1':0.00000)'Osmia cornifrons':0.00000,'EU726549.1':0.00000)'Osmia cornifrons':0.00125,('EU726624.1':0.00000,'EU726601.1':0.00000)'Osmia cornifrons':0.00114)'Osmia cornifrons':0.02131)Osmia:0.00363)Apoidea:0.05114,((('KJ838228.1':0.00239,'HM901915.1':0.00239)'Hoplitis claviventris':0.00038,'KJ837591.1':0.00201)'Hoplitis claviventris':0.03980,(('KM568799.1':0.00000,'KM562022.1':0.00000)'Hoplitis sp. bc4':0.03680,'HQ948088.1':0.03826)Hoplitis:0.01356)Hoplitis:0.02205)Apoidea:0.01340,('KC560283.1':0.00647,('KY072536.1':0.00206,(((((((((((((((((('KY072692.1':0.00000,'KY072384.1':0.00238)'unclassified Apoidea':0.00000,'KY072512.1':0.00000)'unclassified Apoidea':0.00000,'KY072457.1':0.00000)'unclassified Apoidea':0.00000,'KY072408.1':0.00000)'unclassified Apoidea':0.00000,'KY072378.1':0.00000)'unclassified Apoidea':0.00000,'KY072344.1':0.00000)'unclassified Apoidea':0.00000,'KY072316.1':0.00000)'unclassified Apoidea':0.00000,'KY072314.1':0.00000)'unclassified Apoidea':0.00000,'KY072271.1':0.00000)'unclassified Apoidea':0.00000,'KY072263.1':0.00000)'unclassified Apoidea':0.00000,'KY072262.1':0.00000)'unclassified Apoidea':0.00000,'KY072261.1':0.00000)'unclassified Apoidea':0.00000,'KY072174.1':0.00000)'unclassified Apoidea':0.00000,'KY072146.1':0.00000)'unclassified Apoidea':0.00000,'KP259020.1':0.00000)Apoidea:0.00000,'KP259004.1':0.00000)Apoidea:0.00234,(('KY072458.1':0.00000,(('KY072490.1':0.00000,'KY072495.1':0.00000)'unclassified Apoidea':0.00000,('KY072601.1':0.00000,'KY072662.1':0.00000)'unclassified Apoidea':0.00000)'unclassified Apoidea':0.00000)'unclassified Apoidea':0.00000,(('KY072382.1':0.00000,'KP259033.1':0.00000)Apoidea:0.00000,'KY072383.1':0.00000)Apoidea:0.00000)Apoidea:0.00004)Apoidea:0.00019,('KY072600.1':0.00237,(('KY072422.1':0.00000,('KY072388.1':0.00000,'KY072389.1':0.00000)'unclassified Apoidea':0.00000)Apoidea:0.00000,(('KY072370.1':0.00000,'KY072377.1':0.00000)'unclassified Apoidea':0.00000,('KP259058.1':0.00000,'KY072268.1':0.00000)Apoidea:0.00000)Apoidea:0.00000)Apoidea:0.00000)Apoidea:0.00001)Apoidea:0.00220)Apoidea:0.00032)Apoidea:0.00071)Apoidea:0.02660)Apoidea:0.00696)Apoidea:0.03942;";

require_once('butterflies.php');
//require_once('AALF015423.php');
//require_once('figwasp.php');
//require_once('phylo.io.php');
//require_once('figwasp.php');

$t = parse_newick($newick);

// we want some space under the root, but need to do this intellgently
if ($t->GetRoot()->GetAttribute('edge_length') == 0)
{
	$t->GetRoot()->SetAttribute('edge_length', 0.1);
}


//echo $t->WriteNewick() . "\n";

$drawing_options = new stdclass;
$drawing_options->tree_width 	= 400;
$drawing_options->width 		= 1000;
$drawing_options->row_height 	= 12;

$t->BuildWeights($t->GetRoot());

get_node_heights($t, $drawing_options->tree_width);
get_max_subtree_height($t);

// draw all nodes, each in a separate file

// Get list of nodes INORDER
$order_to_label = array();
$order_to_node = array();

$order_to_span = array();
$order_to_crossings = array();

get_inorder($t, $order_to_label, $order_to_node);

// compute span of each internal node, where span is the INORDER visit number for the
// left and right children of the node 

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

// any ancestral node whose left-right span includes the current node will require a 
// vertical line to be drawn below the current node

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

// debugging
$html = '<html>';
$html .= '<div>';


$font_height = $drawing_options->row_height * 1.0;

// we have three classes of node
$node_types = array('leaf', 'internal_open', 'internal_closed');

foreach ($node_types as $type)
{
	$node_height = $drawing_options->row_height;
	
	// the height of a row may depend on the node type being drawn
	switch ($type)
	{
		case 'internal_closed':
			$node_height = $drawing_options->row_height * 2;
	
		default:
			break;
	}

		
	for ($i = 0; $i < $n; $i++)
	{
		$port = new SVGPort('', $drawing_options->width, $node_height, $font_height, false);
		$port->StartGroup('tree', true);
		
		$y_offset = $node_height / 2.0;
	
		// row
		$pt0 = array('x' => 0, 'y' => 0);
		$pt1 = array('x' => $drawing_options->width, 'y' => $y_offset);
			
		$q = $order_to_node[$i];
		$pt = $q->GetAttribute('xy');

		$pt2 = $q->GetAttribute('xy');
		$pt2['y'] = $y_offset;
		
		// Different node types have different drawings
		switch ($type)
		{
			// polygon enclosing subtree rooted on this node
			case 'internal_closed':
				if (!$q->IsLeaf())
				{
					$subtree = get_subtree_circuit($q);
					$left 	= $subtree->leftmost;
					$right 	= $subtree->rightmost;
			
					$left_pt 	= $left->GetAttribute('xy');
					$right_pt 	= $right->GetAttribute('xy');
			
					$subtree_x = $q->GetAttribute('max_x');
			
					$pt_left = array(
						'x' => $subtree_x,
						'y' => 0 
					);
			
					$pt_right = array(
						'x' => $subtree_x,
						'y' => $node_height
					);
			
					// Draw polygon (triangle)
					$pts = array();
			
					// we want polygon apex to have same width as line used to draw tree
					$pt_root = $pt2;
					$pt_root['y'] = $node_height / 2.0;
					$pt_root['y'] -= 0.5;
			
					$pts[] = $pt_root; // root of subtree
					$pts[] = $pt_left; // left 
					$pts[] = $pt_right; // right
			
					$pt_root['y'] += 1;
					$pts[] = $pt_root;
			
					$port->DrawPolygon($pts);
							
					$pt_label = array(
						'x' => $subtree_x + $font_height, 
						'y' => $pt2['y']
					);
			
					$port->DrawText($pt_label, $q->GetLabel());	
				}			
				break;
				
			// vertical bar to connect two children
			case 'internal_open':
				if (!$q->IsLeaf())
				{
					$pt3 = $pt2;
					$pt4 = $pt3;
					$pt3['y'] -= $y_offset;
					$pt4['y'] += $y_offset;			
					$port->DrawLine($pt3, $pt4);
			
					$port->Circle($pt2, $y_offset - 1);	
					
					$pt_label = array(
						'x' => $pt2['x'] + $font_height, 
						'y' => $pt2['y']
					);
			
					$port->DrawText($pt_label, $q->GetLabel());	
					
				}		
				break;
				
			// leaf label
			case 'leaf':
				if ($q->IsLeaf())
				{
					$port->Circle($pt2, $y_offset - 1);
			
					// label
					$pt_label = array(
							'x' => $pt2['x'] + $font_height, 
							'y' => $pt2['y']
						);
						
					$port->DrawText($pt_label, $q->GetLabel());	
				}		
				break;
				
			default:
				break;
		
		}
				
		// horizontal line to ancestor
		$anc = $q->GetAncestor();
		if ($anc)
		{
				$pt = $anc->GetAttribute('xy');
				$pt3 = array('x' => $pt['x'], 'y' => $y_offset);					
				$port->DrawLine($pt2, $pt3);
			
				// Decide on joining style				
				$style = 0;
				if ($q->IsChild())
				{
					$style = 0;
				}
				else
				{
					if ($q->GetSibling())
					{
						$style = 1;
					}
					else
					{
						$style = 2;
					}
				}
			
				switch ($style)
				{
					// LEFT
					case 0:
						$pt4 = $pt3;
						$pt4['y'] += $y_offset;			
						$port->DrawLine($pt3, $pt4);
						break;

					// MIDDLE
					case 1:
						$pt4 = $pt3;
						$pt3['y'] -= $y_offset;
						$pt4['y'] += $y_offset;			
						$port->DrawLine($pt3, $pt4);
						break;

					// RIGHT
					case 2:
						$pt4 = $pt3;
						$pt4['y'] -= $y_offset;			
						$port->DrawLine($pt3, $pt4);
						break;
		
					default:
						break;
				}
		
		}		
		
		// what crossings do we need to fill in?
		foreach ($order_to_crossings[$i] as $cross)
		{
			$p = $order_to_node[$cross];
		
			$pt3 = $p->GetAttribute('xy');
			$pt3['y'] = $y_offset;
			$pt4 = $pt3;
			$pt3['y'] -= $y_offset;
			$pt4['y'] += $y_offset;			
			$port->DrawLine($pt3, $pt4);
		}

		$port->EndGroup();
		$svg = $port->GetOutput();
		
		$go = true;
		
		switch ($type)
		{
			case 'leaf':
				$go = $q->IsLeaf();
				break;

			case 'internal_closed':
			case 'internal_open':
			default:
				$go = !$q->IsLeaf();
				break;
		}
		
		if ($go)
		{
			$filename = 'rows/' . $i . '-' . $type . '.svg';
		
			file_put_contents($filename, $svg);
		
			// debugging
			$html .= '<div style="display:block;overflow: auto;">';				
			$html .= '<img src="' . $filename . '" />';
			$html .= '</div>';
		}

	}
}

$html .= '</div>';
$html .= '</html>';
file_put_contents('tree.html', $html);


?>
