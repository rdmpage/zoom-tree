<?php

// Read JSON structure and render tree

error_reporting(E_ALL);

require_once('tree/svg.php');

function render_tree($tree_obj, $width = 400, $height = 12)
{
	$html = '<html>';
	$html .= '<div>';

	// how much do we have to rescale x and y axes?
	$x_scale = $width / 1000 ;
	
	// we have three classes of node
	$node_types = array('leaf', 'internal_open', 'internal_closed');

	foreach ($node_types as $type)
	{
		// consider increasing height of closed nodes
		if ($type == 'internal_closed')
		{
			$row_height = 2 * $height;
		}
		else
		{
			$row_height = $height;
		}
		
		$y_scale = $row_height / 100;
		$y_midpoint = $row_height / 2;

		foreach ($tree_obj->inorder as $inorder => $node_id)
		{
			$node = $tree_obj->nodes->{$node_id};
	
			print_r($node->xy);
	
			$port = new SVGPort('', 
				$width + $height + 300, // hack so we have space for labels
				$row_height, 
				$height, false);
			$port->StartGroup('tree', true);
	
			$pt0 = array('x' => $node->xy[0] * $x_scale, 'y' => $node->xy[1] * $y_scale);
			
			//----------------------------------------------------------------------------
			// node itself
			
			switch ($type)
			{
				case 'internal_closed':
					if (isset($node->polygon))
					{
						$pts = array();
						
						foreach($node->polygon as $xy)
						{
							$pt = array(
								'x' => $xy[0] * $x_scale,
								'y' => $xy[1] * $y_scale,
							);
							
							$pts[] = $pt;
						}
						
						$port->DrawPolygon($pts);
						
						// label
						$pt_label = array(
								'x' => $pts[1]['x'] + $height, 
								'y' => $pt0['y']
							);
			
						$label = $node->order . ' [' . $node_id . ']';
		
						if (isset($node->label))
						{
							$label .= ' ' . $node->label;
						}
						
						$port->DrawText($pt_label, $label);	
						
						
					}
					break;
					
				case 'internal_open':
					if (!$node->leaf)
					{
						// circle for node
						//$port->Circle($pt0, $height/2 - 1);
						
						// label
						$pt_label = array(
								'x' => $pt0['x'] + $height, 
								'y' => $pt0['y']
							);
			
						$label = $node->order . ' [' . $node_id . ']';
		
						if (isset($node->label))
						{
							$label .= ' ' . $node->label;
						}
			
						$port->DrawText($pt_label, $label);	
						
						// vertical bar
						$pt3 = $pt0;
						$pt4 = $pt0;
	
						$pt3['y'] -= $y_midpoint;
						$pt4['y'] += $y_midpoint;		
						$port->DrawLine($pt3, $pt4);				
					}				
					break;
						
				case 'leaf':
					if ($node->leaf)
					{
						// circle for node
						$port->Circle($pt0, $height/2 - 1);
						
						// label
						$pt_label = array(
								'x' => $pt0['x'] + $height, 
								'y' => $pt0['y']
							);
			
						$label = $node->order . ' [' . $node_id . ']';
		
						if (isset($node->label))
						{
							$label .= ' ' . $node->label;
						}
			
						$port->DrawText($pt_label, $label);	
					}
					break;
					
				default:
					break;
			
			
			}

			//----------------------------------------------------------------------------
			// line back to ancestor
			if (isset($node->ancxy))
			{
				$pt1 = array('x' => $node->ancxy[0] * $x_scale, 'y' => $node->ancxy[1] * $y_scale);
				$port->DrawLine($pt0, $pt1);
		
				// back connection
				switch ($node->style)
				{
					// LEFT
					case 0:
						$pt2 = $pt1;
						$pt2['y'] += $y_midpoint;			
						$port->DrawLine($pt1, $pt2);
						break;

					// MIDDLE
					case 1:
						$pt2 = $pt1;
						$pt3 = $pt1;
						$pt2['y'] -= $y_midpoint;
						$pt3['y'] += $y_midpoint;			
						$port->DrawLine($pt2, $pt3);
						break;

					// RIGHT
					case 2:
						$pt2 = $pt1;
						$pt2['y'] -= $y_midpoint;			
						$port->DrawLine($pt1, $pt2);
						break;

					default:
						break;
				}
		
			}
	
			//----------------------------------------------------------------------------
			// crossings (which are indexed by order NOT id)
			foreach ($tree_obj->crossings->{$node->order} as $cross_id)
			{		
				$cross = $tree_obj->nodes->{$tree_obj->inorder[$cross_id]};
	
				$pt3 = array('x' => $cross->xy[0] * $x_scale, 'y' => $y_midpoint);
				$pt4 = $pt3;
				$pt3['y'] -= $y_midpoint;
				$pt4['y'] += $y_midpoint;		
				$port->DrawLine($pt3, $pt4);
			}

			$port->EndGroup();
			$svg = $port->GetOutput();
	
		
			$go = true;
		
			switch ($type)
			{
				case 'leaf':
					$go = $node->leaf;
					break;

				case 'internal_closed':
				case 'internal_open':
				default:
					$go = !$node->leaf;
					break;
			}
	
			if ($go)
			{
				$filename = 'x/' . $node_id . '-' . $type . '.svg';
				//$filename = 'x/' . $node_id . '.svg';
	
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
	file_put_contents('tree2.html', $html);
}


$filename = 'tree.json';

$json = file_get_contents($filename);
$tree_obj = json_decode($json);

print_r($tree_obj);

render_tree($tree_obj);

?>
