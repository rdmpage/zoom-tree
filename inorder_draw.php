<?php

// Output tree INORDER

require_once(dirname(__FILE__) . '/tree.php');
require_once(dirname(__FILE__) . '/utils.php');

//----------------------------------------------------------------------------------------
// draw whole tree at given zoom level
function inorder_draw_tree($t, $drawing_options)
{
	// Get list of nodes INORDER
	$order_to_label = array();
	$order_to_node = array();

	get_inorder($t, $order_to_label, $order_to_node);
	
	$height = $t->GetNumNodes() * $drawing_options->row_height + $drawing_options->row_height;
	
	$y_offset = $drawing_options->row_height/2;
	
	// text needs to fit within row
	$font_height = $drawing_options->row_height * 0.7;

	// drawing width includes eveything (tree + label, etc)
	$port = new SVGPort('', $drawing_options->width, $height, $font_height, false);

	$port->StartGroup('tree');

	$n = count($order_to_node);
	
	/*
	for ($i = 0; $i < $n; $i++)
	{
		
		// draw a rect corresponding to this row
		{
			$p0 = array(
				'x' => 0,
				'y' => $order_to_node[$i]->GetAttribute('order') * $interval + ($y_offset / 2)
				);
				
			$p1 = array(
				'x' => $width,
				'y' => $p0['y'] + $interval
				);
		
			$port->DrawRect($p0, $p1, 
				array(
					'row'   => $i,
					'id'	=> $order_to_node[$i]->GetId()
				)
				
			);
		
		}
		
	}	
	*/

	for ($i = 0; $i < $n; $i++)
	{	
		// Get coordinates of node in original tree
		$p0 = $order_to_node[$i]->GetAttribute('xy');
	
		// y coordinate is determined by position in the INORDER traversal
		$p0['y'] = $order_to_node[$i]->GetAttribute('order') * $drawing_options->row_height;
	
		// if we have an ancestor then draw line back to ancestor
		$anc = $order_to_node[$i]->GetAncestor();
		if ($anc)
		{	
			// Rectangle
			$p1 = $anc->GetAttribute('xy');
			$p1['y'] = $p0['y'];
		
			$port->DrawLine($p0, $p1);
		}
		
		// node is a leaf
		if ($order_to_node[$i]->IsLeaf())
		{
			// Node in original tree
			$original = $order_to_node[$i]->original;
						
			if ($original->IsLeaf())
			{
				// Also a leaf in the original tree				
				$p_label = array(
					'x' => $p0['x'] + $font_height, 
					'y' => $p0['y']
				);
				
				$port->DrawText($p_label, $order_to_node[$i]->Getlabel()); 
			}
			else
			{
				// node is an internal node in original tree, so draw as collapsed node
				// When drawn as a triangle we extend it out in the x-axis to the 
				// furtherest point in any of the descendants of this node in the
				// original tree
				
				$s = get_subtree_circuit($original);
				
				$left 	= $s->leftmost;
				$right 	= $s->rightmost;

				$left_pt 	= $left->GetAttribute('xy');
				$right_pt 	= $right->GetAttribute('xy');
								
				$x = $original->GetAttribute('max_x');
				
				$pl = array(
					'x' => $x,
					'y' => $p0['y'] - $drawing_options->row_height 
				);
				
				$pr = array(
					'x' => $x,
					'y' => $p0['y'] + $drawing_options->row_height
				);
				
				// Draw polygon (triangle)
				$pts = array();
				
				$pts[] = $p0; // root of subtree
				$pts[] = $pl; // left 
				$pts[] = $pr; // right
				
				$port->DrawPolygon($pts);
								
				$p_label = array(
					'x' => $x + $font_height, 
					'y' => $p0['y']
				);
				
				$label = $order_to_node[$i]->GetLabel();
				
				/*
				// for debugging add zoom level
				$label = $order_to_node[$i]->GetAttribute('zoom') . ' ' . $label;
				
				// Show weight in original tree (how big is this subtree whn opened?)
				$label .= " [" . $order_to_node[$i]->original->GetAttribute('weight') . "]";
				*/
				
				$port->DrawText($p_label, $label); 
			}
			
		}
		else
		{
			// Internal node in collapsed tree
			
			// rectangle-style
			
			// draw vertical line connecting left and right descendants
			$pl = $order_to_node[$i]->GetChild()->GetAttribute('xy');
			$pl['y'] = $order_to_node[$i]->GetChild()->GetAttribute('order') * $drawing_options->row_height;
	
			$pr = $order_to_node[$i]->GetChild()->GetRightMostSibling()->GetAttribute('xy');
			$pr['y'] = $order_to_node[$i]->GetChild()->GetRightMostSibling()->GetAttribute('order') * $drawing_options->row_height;
				
			$p1['x'] = $p0['x'];
			$p1['y'] = $pl['y'];
			
			$p2['x'] = $p0['x'];
			$p2['y'] = $pr['y'];

			$port->DrawLine($p1, $p2);
			
			// Labelling
			$p_label = array(
					'x' => $p0['x'] + $font_height, 
					'y' => $p0['y']
				);			
			
			// exploring zoom levels...
			$label = $order_to_node[$i]->GetAttribute('zoom');
			
			if ($order_to_node[$i]->GetAttribute('changed'))
			{
				// this node corresponds to a change in internal labels 
				$label .= " *";
				
				// draw a rectangle that encloses this subtree 
				// get left and right bounds of visible descendants of this node
				$s = get_subtree_circuit($order_to_node[$i]);
								
				$left_top = array(
					'x' => $p0['x'],
					'y' => $s->leftmost->GetAttribute('order') * $drawing_options->row_height - $drawing_options->row_height			
				);
				
				$right_bottom = array(
					'x' => $order_to_node[$i]->original->GetAttribute('max_x'),
					'y' => $s->rightmost->GetAttribute('order') * $drawing_options->row_height + $drawing_options->row_height									
				);
				
				$port->DrawRect($left_top, $right_bottom);
								
			}
			
			$port->DrawText($p_label, $label); 
		}
	}

	// done with drawing
	$port->EndGroup();

	$svg = $port->GetOutput();		

	return $svg;
}



?>
