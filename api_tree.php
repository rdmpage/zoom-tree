<?php

// Read JSON structure and draw tree

error_reporting(E_ALL);

$zoom = 2;

if (isset($_GET['zoom']))
{
	$zoom = $_GET['zoom'];
}


function draw_tree($tree_obj, $zoom = 1)
{
	$html = '<div>';
	
	foreach ($tree_obj->zoom->{$zoom}->inorder as $order => $node_id)
	{
		$filename = 'x/' . $node_id . '-' . $tree_obj->zoom->{$zoom}->type->{$node_id} . '.svg';
		$html .= '<div style="display:block;overflow: auto;">';				
		$html .= '<img src="' . $filename . '" />';
		$html .= '</div>';
	}

	$html .= '</div>';
	
	$obj = new stdclass;
	$obj->html = $html;	
	header("Content-type: application/json");
	echo json_encode($obj);
	//file_put_contents('tree-' . $zoom . '.html', $html);
}


$filename = 'tree.json';

$json = file_get_contents($filename);
$tree_obj = json_decode($json);

$zoom = max($zoom, $tree_obj->min_zoom);
$zoom = min($zoom, $tree_obj->max_zoom);


//print_r($tree_obj);


draw_tree($tree_obj, $zoom);

?>
