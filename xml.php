<?php

// Parse tree XML

error_reporting(E_ALL);

$xml = file_get_contents('AALF015423_gene_tree.xml');

$dom = new DOMDocument;
$dom->loadXML($xml, LIBXML_NOCDATA); // Elsevier wraps text in <![CDATA[ ... ]]>
$xpath = new DOMXPath($dom);


//----------------------------------------------------------------------------------------
// Recursively traverse DOM and process tags
function dive($dom, $node)
{	
	global $xpath;

	$is_leaf = true;
	
	$label = "";
	
	$branch_length = 0;
	
	$child_nodes = array();
	
	if ($node->hasAttributes())
	{
		$branch_length = $node->getAttribute('branch_length');
	}	
	
	if ($node->hasChildNodes())
	{
		foreach ($node->childNodes as $children) 
		{
			if ($children->nodeName == "clade")
			{
				$child_nodes[] = $children;
			}
			
			if ($children->nodeName == "name")
			{
				$label = $children->nodeValue;
			}			
						
		}
	}
	
	if ($label == "")
	{
		if ($node->hasChildNodes())
		{
			foreach ($node->childNodes as $children) 
			{
				if ($children->nodeName == "taxonomy")
				{
					foreach ($children->childNodes as $c) 
					{
						if ($c->nodeName == "scientific_name")
						{
							$label = $c->nodeValue;
						}
					}
				}
			}
		}
	}
	
	$n = count($child_nodes);
	
	$is_leaf = ($n == 0);
	
	if ($is_leaf)
	{
		echo $label;
	}
	else
	{
		echo "(";
	}

	//echo "--" . $node->nodeName . "\n";
	//echo "--" . $node->nodeValue . "\n";
	//echo "--------\n\n";

	
	// Visit any children of this node
	$count = 0;
	foreach ($child_nodes as $children)
	{
		if ($count > 0 && $count < $n)
		{
			echo ",";
		}
		$count++;
		dive($dom, $children);
	}
	
	
	if ($is_leaf)
	{
	}
	else
	{
		echo ")";
		echo "'" . $label . "'";
		echo ":" . $branch_length;
	}
	
}



foreach ($dom->documentElement->childNodes as $node) 
{
   		dive($dom, $node);
}


echo ";";

?>
