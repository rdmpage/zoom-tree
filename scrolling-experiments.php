<?php

$zoom = 2;
$row_id = 4;


// 1
$z1 = array();

$z1[] = 0;
$z1[] = 1;
$z1[] = 2;
$z1[] = 3;
$z1[] = 4;
$z1[] = 5;
$z1[] = 6;

// 2
$z2 = array();

$z2[] = 0;
$z2[] = 1;
$z2[] = 2;


$z2[] = '3a';
$z2[] = 3;
$z2[] = '3b';

$z2[] = '4a';
$z2[] = 4;
$z2[] = '4b';

$z2[] = 5;

$z2[] = '6a';
$z2[] = 6;
$z2[] = '6b';
$z2[] = '6c';
$z2[] = '6d';


// display current list and centre the current row
// how do we adjust for centre being not exactly trhe centre of the row?


?>

<html>
<head>
<style>
	body {
		padding:0px;
		margin: 0px;
		background: url(g1001cc2.gif);
	}
	
	.page {
		width:160px;
		
		border:1px solid red; 
	
	}
	
	.well {
		
	}
	
	.row {
		width:100%;
		height:30px;
		background:yellow;
		opacity:0.3;
		
	}
</style>
</head>
<body>
	<!-- viewer -->
	<div id="viewer" style="width:220px;height:300px;border:1px solid black;overflow-y:auto;">

		<!-- debugging -->
		<div style="position:absolute;height:10px;width:200px;top:145px;border:1px solid blue;"></div>

		<div class="well">
			<div id="spacer-top"></div>
		
			<div id="page0" data-page="0" class="page">
	<?php
		$rows = array();
		
		switch ($zoom)
		{
			case 2:
				$rows = $z2;
				break;
				
			case 1:
			default:
				$rows = $z1;
				break;
		}
		
		foreach ($rows as $k => $v)
		{
			echo '<div id="' . $k . '" class="row">[' . $k . '] ' . $v . '</div>' . "\n";		
		}
	?>
			</div> <!-- page -->
			
			<div id="spacer-top"></div>	
		</div> <!-- well -->
	</div> <!-- viewer -->
	
	<!-- call javascript to position correctly -->
	
</body>
</html>

