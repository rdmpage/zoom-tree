<?php


$filename = 'tree.json';

$json = file_get_contents($filename);

$tree_obj = json_decode($json);

print_r($tree_obj);




?>
