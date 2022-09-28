<?php

require_once(dirname(__FILE__) . '/port.php');

//-------------------------------------------------------------------------------------------------
class SVGPort extends Port
{
	var $document = null;
	var $node_stack = array();

	//----------------------------------------------------------------------------------------------
	function Circle($pt, $r, $id = '')
	{
		$circle = $this->document->createElement('circle');
		
		if ($id != '')
		{		
			$circle->setAttribute('id', $id);
		}
			
		$circle->setAttribute('cx', $pt['x']);		
		$circle->setAttribute('cy', $pt['y']);		
		$circle->setAttribute('r', $r);		
	
		$n = count($this->node_stack);
		$this->node_stack[$n-1]->appendChild($circle);		
	}

	//----------------------------------------------------------------------------------------------
	function DrawCircleArc($p0, $p1, $radius, $large_arc_flag = false)
	{
		$path = $this->document->createElement('path');
		
		$path->setAttribute('vector-effect', 'non-scaling-stroke');		
		
		$path_string = 'M ' 
			. $p0['x'] . ' ' . $p0['y'] // start x,y
			. ' A ' . $radius . ' ' . $radius  //
			. ' 0 ';

		if ($large_arc_flag)
		{
			$path_string .= ' 1 ';		
		}
		else
		{
			$path_string .= ' 0 ';
		}
			
		$path_string .=
			' 1 '
			. $p1['x'] . ' ' . $p1['y']; // end x,y
		
		
		$path->setAttribute('d', $path_string);		
		$n = count($this->node_stack);
		$this->node_stack[$n-1]->appendChild($path);
	}
		
	//----------------------------------------------------------------------------------------------
	function DrawRect($p0, $p1, $data = null)
	{
		$rect = $this->document->createElement('rect');
		
		$rect->setAttribute('vector-effect', 'non-scaling-stroke');
		
		$rect->setAttribute('x', $p0['x']);
		$rect->setAttribute('y', $p0['y']);

		$rect->setAttribute('width', $p1['x']- $p0['x']);
		$rect->setAttribute('height', $p1['y']- $p0['y']);
		
		$rect->setAttribute('opacity', '0.1');
		$rect->setAttribute('style', 'fill:rgb(0,0,255);');

		
		if ($data)
		{
			foreach ($data as $k => $v)
			{
				$rect->setAttribute('data-' . $k, $v);
			}
		}

		$n = count($this->node_stack);
		$this->node_stack[$n-1]->appendChild($rect);
	}
	
	//----------------------------------------------------------------------------------------------
	function DrawPolygon($pts, $color = array())
	{
		$polygon = $this->document->createElement('polygon');
		$polygon->setAttribute('vector-effect', 'non-scaling-stroke');
		
		$polygon->setAttribute('fill', "#EEEEEE");
		$polygon->setAttribute('fill', "#999999");
		//$polygon->setAttribute('fill', "none");
		//$polygon->setAttribute('stroke', "#000000");
		
		//$polygon->setAttribute('stroke', "none");
		
		$polygon->setAttribute('fill', "#000");
		$polygon->setAttribute('stroke', 1);
		
		//$polygon->setAttribute('opacity', "0.5");
		
		$points = '';
		foreach ($pts as $pt)
		{
			$points .=  $pt['x'] . ',' . $pt['y'] . ' ';
		}
		$polygon->setAttribute('points', $points);

		$n = count($this->node_stack);
		$this->node_stack[$n-1]->appendChild($polygon);
	}
	
	//----------------------------------------------------------------------------------------------
	function DrawLine($p0, $p1)
	{
		$path = $this->document->createElement('path');
		
		$path->setAttribute('vector-effect', 'non-scaling-stroke');
		
		$path->setAttribute('d', 
			'M ' . $p0['x'] . ' ' . $p0['y'] . ' ' . $p1['x'] . ' ' . $p1['y']);
		
		$n = count($this->node_stack);
		$this->node_stack[$n-1]->appendChild($path);
	}
		
	//----------------------------------------------------------------------------------------------
	function DrawText ($pt, $text, $action = '')
	{
		$text_node = $this->document->createElement('text');
		$text_node->setAttribute('x', $pt['x']);
		$text_node->setAttribute('y', $pt['y']);
		
		$align = 'left';
	
		switch ($align)
		{
			case 'left':
				$text_node->setAttribute('text-anchor', 'start');
				break;
			case 'centre':
				$text_node->setAttribute('text-anchor', 'middle');
				break;
			case 'right':
				$text_node->setAttribute('text-anchor', 'end');
				break;
			default:
				$text_node->setAttribute('text-anchor', 'start');
				break;
		}
	
		$text_node->appendChild($this->document->createTextNode($text));
		
		$n = count($this->node_stack);
		$this->node_stack[$n-1]->appendChild($text_node);
	}
	
	//----------------------------------------------------------------------------------------------
	function DrawRotatedText ($pt, $text, $action = '', $align = 'left', $angle = 0)
	{
		$text_node = $this->document->createElement('text');
		$text_node->setAttribute('x', $pt['x']);
		$text_node->setAttribute('y', $pt['y']);
		
		switch ($align)
		{
			case 'left':
				$text_node->setAttribute('text-anchor', 'start');
				break;
			case 'centre':
				$text_node->setAttribute('text-anchor', 'middle');
				break;
			case 'right':
				$text_node->setAttribute('text-anchor', 'end');
				break;
			default:
				$text_node->setAttribute('text-anchor', 'start');
				break;
		}
		
		if ($angle != 0)
		{
			$text_node->setAttribute('transform', 'rotate(' . $angle . ' ' . $pt['x'] . ' ' . $pt['y'] . ')');
		}
		
		$text_node->appendChild($this->document->createTextNode($text));
		
		$n = count($this->node_stack);
		$this->node_stack[$n-1]->appendChild($text_node);
	}
	
	
	//----------------------------------------------------------------------------------------------
	function StartPicture($centre = false)
	{
		$this->document = new DomDocument('1.0', 'UTF-8');
		
		$this->document->preserveWhiteSpace = false;
		$this->document->formatOutput = true;			
		
		$svg = $this->document->createElement('svg');
		$svg->setAttribute('xmlns', 		'http://www.w3.org/2000/svg');
		$svg->setAttribute('xmlns:xlink', 	'http://www.w3.org/1999/xlink');
		$svg->setAttribute('width', 		$this->width . 'px');
		$svg->setAttribute('height', 		$this->height . 'px');
		
		$svg = $this->document->appendChild($svg);

		$style = $this->document->createElement('style');
		$style->setAttribute('type', 	'text/css');
		$style->appendChild($this->document->createCDATASection(
	'path {
		stroke: #000;
		stroke-width:1;
		stroke-linecap:square;
		fill: none;
	}
	text {
		alignment-baseline:middle;
		font-family:sans-serif;
		font-size: ' . $this->font_size . 'px;
	}
	/*
	text:hover {
		font-weight:bold;
		}
	*/
	circle {
		stroke: black;
		fill:black;
		/*opacity:0.2;*/
		}
	/*circle:hover {opacity:1.0; }*/
	rect {
		fill:white;
		stroke: black;
	}'
		));
		
		$style = $svg->appendChild($style);
		
		$g = $this->document->createElement('g');
		$g->setAttribute('id', 'viewport');
		
		if ($centre)
		{
			$g->setAttribute('transform', 'translate(' . $this->width/2.0 . ' ' . $this->height/2.0 . ')');
		}
		$g = $svg->appendChild($g);
		
		$this->node_stack[] = $g;
    }
	
	//----------------------------------------------------------------------------------------------
	function EndPicture ()
	{
	}

	//----------------------------------------------------------------------------------------------
	function OpenLink($link)
	{
		//$this->output .= '<a xlink:href="' . $link . '">';
	}	
	//----------------------------------------------------------------------------------------------
	function CloseLink()
	{
		//$this->output .= '</a>';
	}	
	
	//----------------------------------------------------------------------------------------------
	function StartGroup($group_name, $visible=true)
	{
		$group = $this->document->createElement('g');
		$group->setAttribute('id', $group_name);
		if ($visible)
		{
			$group->setAttribute('display', 'inline');
		}
		else
		{
			$group->setAttribute('display', 'none');
		}
		
		$n = count($this->node_stack);
		$this->node_stack[$n-1]->appendChild($group);
		$this->node_stack[] = $group;
	}	
	
	//----------------------------------------------------------------------------------------------
	function EndGroup()
	{
		array_pop($this->node_stack);
	}	
	
	//----------------------------------------------------------------------------------------------
	function GetOutput()
	{
		$this->EndPicture();
		return $this->document->saveXML();
	}
	
}

?>
