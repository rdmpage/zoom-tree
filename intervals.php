<?php

// https://en.wikipedia.org/wiki/Maximum_disjoint_set
// https://www.geeksforgeeks.org/maximal-disjoint-intervals/

$intervals = array(
	[1,4],
	[2,3],
	[4,6],
	[8,9]
);

$intervals = array(
	[1,10],
	[1,4],
	[5,10],
	[5,6],
	[7,10],
);

echo "Input\n";
print_r($intervals);

// sort
function cmp($a, $b) {
    if ($a[1] == $b[1]) {
        return 0;
    }
    return ($a[1]< $b[1]) ? -1 : 1;
}

uasort($intervals, 'cmp');

echo "Sorted\n";
print_r($intervals);

$result = array();
$result[] = array_shift($intervals);

$endpoint = $result[0][1];

foreach ($intervals as $interval)
{
	if ($interval[0] > $endpoint)
	{
		$result[] = $interval;
		
		$endpoint = $interval[1];
	}
	

}

echo "Intervals\n";
print_r($result);


?>
