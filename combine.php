<?php

// Take a Newick tree and a file with classification strings for each node
// and make an internally labelled tree. The classiifctaion strings can be created
// using NCBI, for exmaple.


require_once('tree-parse.php');
require_once('node_iterator.php');


// read classification strings for each leaf
$newick = "(KJ836409.1:0.0394167,((((KJ837499.1:0.000790185,(HQ948094.1:0.005835,((KJ836642.1:0.00223928,(KJ836862.1:1.48198e-07,KJ836538.1:0.00237892):0.000143574):0.000696052,(((KJ838652.1:0,KJ839820.1:7.36166e-05):7.36166e-05,KJ836425.1:0):7.36166e-05,HQ948093.1:0):0.00170478):0.00135383):0.000386304):0.0209252,(((((KT074045.1:0,KJ836515.1:0):0,KJ838226.1:0):0,HM901923.1:0):0.000592874,KJ836448.1:0.00178619):0.000974122,KJ839494.1:0.00140873):0.0347398):0.0326188,(KR786504.1:0.00510067,((FJ582232.1:0,(KJ163559.1:0.00235992,FJ582230.1:0.00481717):8.77903e-05):0.000672437,FJ582231.1:0.00171802):0.00450818):0.0518167):0.0119368,((((((((KX957905.1:0,GU705980.1:0.00478067):1.49689e-05,KJ837790.1:0):1.49689e-05,KJ837713.1:0):0.000689581,(((((((((((((((KX957869.1:0,KX374766.1:5.56418e-09):5.56418e-09,KX957868.1:0):5.56418e-09,KX957867.1:0):5.56418e-09,KX957865.1:0):5.56418e-09,KX957864.1:0):5.56418e-09,KX957863.1:0):5.56418e-09,KX957862.1:0):5.56418e-09,KX957861.1:0):5.56418e-09,KX957860.1:0):5.56418e-09,KX374767.1:0):5.56418e-09,GU705983.1:0):0.000600043,(KX957904.1:0.00178088,((KX374770.1:0,KX957903.1:7.10403e-06):7.10403e-06,GU705978.1:0):0.000602462):0.00178281):0.00060507,KY121842.1:0.0011822):0.00115752,(KX957866.1:0,(((((JQ909849.1:0.00237954,GU705979.1:0):4.78638e-07,KT164634.1:0):4.78638e-07,KT074073.1:0):4.78638e-07,KY121818.1:0):4.78638e-07,KY121823.1:0):4.78638e-07):3.21721e-05):0.000188326,KC709831.1:0):0.00171617):0.0110093,AF250940.1:0.00779681):0.00451422,(AF250941.1:0.0115977,(((((EU726628.1:0,EU726627.1:0):0,EU726626.1:0):0,EU726621.1:0):0,EU726549.1:0):0.00124566,(EU726624.1:0,EU726601.1:0):0.00113908):0.0213067):0.00363395):0.0511406,(((KJ838228.1:0.00238854,HM901915.1:0.00238854):0.000383077,KJ837591.1:0.00200547):0.0398015,((KM568799.1:0,KM562022.1:0):0.0367986,HQ948088.1:0.038264):0.0135556):0.0220487):0.0134001,(KC560283.1:0.00647048,(KY072536.1:0.00206123,((((((((((((((((((KY072692.1:3.09203e-09,KY072384.1:0.00237906):0,KY072512.1:3.09203e-09):0,KY072457.1:3.09203e-09):0,KY072408.1:3.09203e-09):0,KY072378.1:3.09203e-09):0,KY072344.1:3.09203e-09):0,KY072316.1:3.09203e-09):0,KY072314.1:3.09203e-09):0,KY072271.1:3.09203e-09):0,KY072263.1:3.09203e-09):0,KY072262.1:3.09203e-09):0,KY072261.1:3.09203e-09):0,KY072174.1:3.09203e-09):0,KY072146.1:3.09203e-09):0,KP259020.1:3.09203e-09):0,KP259004.1:3.09203e-09):0.00234357,((KY072458.1:0,((KY072490.1:0,KY072495.1:0):0,(KY072601.1:0,KY072662.1:0):0):0):0,((KY072382.1:0,KP259033.1:0):0,KY072383.1:0):0):3.54967e-05):0.000187633,(KY072600.1:0.00236741,((KY072422.1:0,(KY072388.1:0,KY072389.1:0):0):0,((KY072370.1:0,KY072377.1:0):0,(KP259058.1:0,KY072268.1:0):0):0):0):1.16517e-05):0.00220091):0.000322574):0.000708561):0.0266027):0.0069557):0.0394167);";
$filename = "wasp.tsv";


$newick = "(MW318377.1:0.0311987,(((((((((JQ392618.1:0.0208833,JQ392616.1:0.0282111)butterflies:0.0109857,(JQ392595.1:0.0169673,JQ392590.1:0.0320583)butterflies:0.0100487)butterflies:0.00124683,(((MN433458.1:0.0356878,((JQ392671.1:0.0006257,JQ392672.1:0.00413514)butterflies:0.00912995,JQ392670.1:0.0150951)butterflies:0.0149322)butterflies:0.00211535,(JQ392688.1:0.0213464,(JQ392682.1:0.00254103,JQ392683.1:0.000856482)butterflies:0.014846)butterflies:0.00573592)butterflies:0.00376064,JQ392584.1:0.0408599)butterflies:0.00102531)butterflies:0.000234361,(((JQ392674.1:0.00254021,JQ392673.1:0.0166872)butterflies:0.0225629,JQ392669.1:0.0330722)butterflies:0.00521808,JQ392645.1:0.0436189)butterflies:0.000664578)butterflies:0.00371596,GU205874.1:0.0384934)butterflies:0.0020093,MW318505.1:0.0369255)butterflies:0.000807037,(MW318463.1:0.0449054,(JQ392608.1:0,JQ392607.1:0.000720099)butterflies:0.0367594)butterflies:0.00559293)butterflies:0.000935899,GQ864789.1:0.0545645)butterflies:0.00245161,((MN264783.1:0.0470026,((KT880200.1:0.036602,MF489982.1:0.037038)butterflies:0.00427311,((KU340865.1:0.00609209,KU340862.1:0.00209353)butterflies:0.000451702,(KU340859.1:0.000497638,MN264787.1:0.00225088)butterflies:0.00135832)butterflies:0.0347003)butterflies:0.00214412)butterflies:0.00854182,(((KU340856.1:0.00755069,KU340872.1:0.0100098)butterflies:0.0316449,KT880201.1:0.0538269)butterflies:0.00119867,((((((MN264792.1:0.000339218,MN264790.1:0.000328636)butterflies:0.0222943,(((KU340861.1:0,MN264798.1:0.00204973)butterflies:0.0100441,KU340884.1:0.00996571)butterflies:0.007112,KU340870.1:0.0162892)butterflies:0.00951501)butterflies:0.00115703,((MN264793.1:0.000560541,(KU340855.1:0,MN264794.1:0.00611391)butterflies:0.000110011)butterflies:0.00374705,(KU340871.1:0.00225848,MN264795.1:0.00667429)butterflies:0.00204822)butterflies:0.0189201)butterflies:0.00637543,((((((MN264803.1:0,MN264802.1:0.00137784)butterflies:0.00767311,(MN264821.1:0.00949868,(MN264819.1:0.000654049,MN264818.1:1.38053e-05)butterflies:0.0080697)butterflies:0.000439439)butterflies:0.0216633,(GU205854.1:0.032019,(MN264832.1:0.000321795,MN264834.1:0.001733)butterflies:0.0214011)butterflies:0.00882153)butterflies:0.0017642,MN264825.1:0.0388087)butterflies:0.00172635,(((((KU340857.1:0.000784453,MN264815.1:0.0012209)butterflies:0.00021431,(MN264831.1:0.00336275,MN264813.1:0.00132477)butterflies:0.000288071)butterflies:0.000242281,MN264816.1:0.00285806)butterflies:0.00174873,(MN264830.1:0.000332145,KU340860.1:0.00100416)butterflies:0.00684585)butterflies:0.0195511,(MN264829.1:0.00455695,MN264828.1:0.00626623)butterflies:0.0281389)butterflies:0.00528048)butterflies:0.000201433,(KU340889.1:0.00102649,MN264809.1:0.00567896)butterflies:0.0410239)butterflies:0.00518581)butterflies:0.0114972,KU340867.1:0.047918)butterflies:0.00108944,((((((KU525708.2:0.000826393,KU525710.2:0.00117896)butterflies:0.00278184,(KU525707.2:0.000435176,KU525706.2:0.00170006)butterflies:0.00530118)butterflies:0.0216355,((MN264854.1:0.00765995,MT358273.1:0.00948365)butterflies:0.0275861,(((MN264870.1:0.00244029,MN264871.1:0.00362443)butterflies:0.0189896,((JQ797599.1:0.000542989,JQ797600.1:0.000817864)butterflies:0.0258295,(MN264848.1:0.00240043,MN264849.1:0.00113648)butterflies:0.0155372)butterflies:0.00524673)butterflies:0.00318757,MN264867.1:0.0340364)butterflies:0.000785527)butterflies:0.00129845)butterflies:0.0137947,(((MN264864.1:0.00606682,MN264863.1:0.000895401)butterflies:0.0273468,(MN264856.1:0.0105118,MN264855.1:0.0132483)butterflies:0.0204736)butterflies:0.00223312,(MN264837.1:0.0409492,(((DQ338585.1:0.0470547,DQ338814.1:0.0421883)butterflies:0.00645605,GU205876.1:0.0371989)butterflies:0.00276811,((KT880198.1:0.0131257,JQ797610.1:0.0280096)butterflies:0.0174317,(MN264851.1:0.00387873,JQ797607.1:0.00393014)butterflies:0.0227735)butterflies:0.00284011)butterflies:0.00196477)butterflies:0.00091215)butterflies:0.000983114)butterflies:0.00314719,(((KU340869.1:0.000466631,GU205853.1:0.0187378)butterflies:0.0159441,MT036314.1:0.0239361)butterflies:0.0111884,(KU340874.1:0.0245822,(MG209761.1:0.0103905,DQ338801.1:0.0192117)butterflies:0.0124093)butterflies:0.0100436)butterflies:0.00266453)butterflies:0.000446285,(KU340883.1:0.0567223,(MN264846.1:0.00266609,(MN264844.1:0.00393848,(MN264845.1:0,MN264845.1:0)butterflies:0.00276697)butterflies:0.00102356)butterflies:0.0344422)butterflies:0.00203506)butterflies:0.000873767)butterflies:0.00155418)butterflies:0.000770189)butterflies:0.00408659)butterflies:0.0311987)butterflies;";
$filename = "butterflies.tsv";


$t = parse_newick($newick);


// get list of leaf node labels (assumes they are unique)
$leaf_label_map = array();

// Get leaf label map so we can refer to levaes by OTU label
{
	$n = new NodeIterator ($t->GetRoot());
	$q = $n->Begin();
	while ($q != NULL)
	{
		if ($q->IsLeaf())
		{
			$leaf_label_map[$q->GetLabel()] = $q;
		}
		$q = $n->Next();
	}
}

// read classification strings and store as arrays
$node_classification_map = array();

$file_handle = fopen($filename, "r");
while (!feof($file_handle)) 
{
	$line = trim(fgets($file_handle));

	$row = explode("\t",$line);

	if (count($row) == 2)
	{
		$label = $row[0];

		$q = $leaf_label_map[$label];
		if ($q)
		{
			$node_classification_map[$q->GetId()] = explode("|", $row[1]);
		}
	}
}	

if (0)
{
	foreach ($node_classification_map as $id => $classification)
	{
		echo $id . ' ' . join("|", $classification) . "\n";
	}
}

// Colour tree with classification
{
	$n = new NodeIterator ($t->GetRoot());
	$q = $n->Begin();
	while ($q != NULL)
	{
		$q_id = $q->GetId();

		$anc = $q->GetAncestor();
		if ($anc)
		{
			$anc_id = $anc->GetId();
			
			if (!isset($node_classification_map[$q_id]))
			{
				// start with $q's classification set
				$node_classification_map[$q_id] = array();
			}			
	
			// first time visit?
			if (!isset($node_classification_map[$anc_id]))
			{
				// start with $q's classification set
				$node_classification_map[$anc_id] = $node_classification_map[$q_id];
			}
			else
			{
				// get intersection of sets which gives us LCA of two classifications
				$node_classification_map[$anc_id] = array_intersect($node_classification_map[$anc_id], $node_classification_map[$q_id]);
			}
		}

		$q = $n->Next();
	}
}

// Label internal nodes with top of classification list
foreach ($node_classification_map as $id => $classification)
{
	//echo $id . ' ' . join("|", $classification) . "\n";

	$q = $t->id_to_node_map[$id];

	$q->SetAttribute('taxon', array_pop($classification));
	
	if (!$q->IsLeaf())
	{
		$q->SetLabel($q->GetAttribute('taxon'));
	}

}

// remove redundancy in labelling
{
	$n = new NodeIterator ($t->GetRoot());
	$q = $n->Begin();
	while ($q != NULL)
	{
		$anc = $q->GetAncestor();
		if ($anc)
		{
			if ($q->GetLabel() != '')
			{
				if ($q->GetLabel() == $anc->GetLabel())
				{
					$q->SetLabel('');
				}
			}
		}
		$q = $n->Next();
	}
}

echo $t->WriteNewick() . "\n";


?>

