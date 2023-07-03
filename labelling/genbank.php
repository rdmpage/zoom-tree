<?php

// Given a set of sequence accession numbers retrieve the NCBI taxonomy as a tab-delimited 
// list

//----------------------------------------------------------------------------------------
function get($url, $accept = "text/html")
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	
	$response = curl_exec($ch);
	
	if($response == FALSE) 
	{
		$errorText = curl_error($ch);
		curl_close($ch);
		//die($errorText);
		return "";
	}
	
	$info = curl_getinfo($ch);
	$http_code = $info['http_code'];
		
	curl_close($ch);
		
	return $response;
}

//----------------------------------------------------------------------------------------

putenv('NCBI_API_KEY=56b7d0c702af1e3fa9e863c48a53cfc89b09');

$ids = array(
'MN264845.1',
'MN264844.1',
'MN264846.1',
'KU340869.1',
'MN264864.1',
'MT036314.1',
'KT880198.1',
'KU525708.2',
'KU525710.2',
'MN264856.1',
'KU340855.1',
'MN264793.1',
'KU340874.1',
'JQ797599.1',
'JQ797600.1',
'MG209761.1',
'KU340871.1',
'MN264863.1',
'MN264870.1',
'MN264848.1',
'MN264792.1',
'MN264790.1',
'MN264871.1',
'MN264794.1',
'KU525707.2',
'MN264851.1',
'KU340856.1',
'KU340872.1',
'JQ797607.1',
'MN264849.1',
'KU340857.1',
'MN264831.1',
'MN264815.1',
'MN264821.1',
'MN264816.1',
'MN264854.1',
'MN264803.1',
'DQ338801.1',
'MN264830.1',
'KU340860.1',
'MN264829.1',
'MN264819.1',
'MN264818.1',
'MN264813.1',
'MN264802.1',
'KU340861.1',
'GU205854.1',
'MN264832.1',
'MN264798.1',
'MT358273.1',
'MN264855.1',
'MN264825.1',
'DQ338585.1',
'MN264795.1',
'MN264828.1',
'JQ392618.1',
'KU340883.1',
'MW318377.1',
'KU340889.1',
'KU340867.1',
'KU340859.1',
'JQ392595.1',
'MW318463.1',
'JQ392608.1',
'GU205853.1',
'MW318505.1',
'JQ392607.1',
'KU340870.1',
'JQ392674.1',
'MN264809.1',
'KU340862.1',
'KU525706.2',
'JQ797610.1',
'MN264834.1',
'KT880200.1',
'JQ392645.1',
'DQ338814.1',
'MN264837.1',
'JQ392688.1',
'KU340884.1',
'GU205876.1',
'JQ392671.1',
'MF489982.1',
'JQ392670.1',
'KU340865.1',
'GU205874.1',
'JQ392669.1',
'JQ392590.1',
'GQ864789.1',
'MN264787.1',
'JQ392616.1',
'KT880201.1',
'MN264783.1',
'MN264867.1',
'MN433458.1',
'JQ392682.1',
'JQ392584.1',
'JQ392683.1',
'JQ392672.1',
'JQ392673.1',
);

$ids=array(
'JQ534364.1',
'HM377932.1',
'GU336118.1',
'MF923603.1',
'JQ560149.1',
'MF922836.1',
'GU658490.1',
'JQ554463.1',
'MF924198.1',
'MF924177.1',
'HQ556542.1',
'JQ551610.1',
'JQ565745.1',
'HM408169.1',
'AF277443.1',
'JN266473.1',
'HQ553212.1',
'MK767790.1',
'JN262846.1',
'JQ556316.1',
'HM408299.1',
'HM409842.1',
'JQ558137.1',
'JQ545067.1',
'JQ548064.1',
'HM408168.1',
'JQ556315.1',
'JQ548044.1',
'JQ551063.1',
'JQ547907.1',
'JQ552569.1',
'JQ552568.1',
'HM408460.1',
'HM408461.1',
'JQ552570.1',
'JQ562164.1',
'KX300289.1',
'JX571578.1',
'JF854867.1',
'JQ578644.1',
'JQ571546.1',
'JQ569548.1',
'GU700040.1',
'JQ578086.1',
'JQ573881.1',
'HM403389.1',
'JQ548012.1',
'JQ564645.1',
'JQ568903.1',
'JN266340.1',
'JQ547905.1',
'JF844512.1',
'JQ569694.1',
'HQ568274.1',
'KF533459.1',
'JN266461.1',
'HQ567922.1',
'KX300252.1',
'JX571167.1',
'JX571169.1',
'JX571168.1',
'JQ556321.1',
'GU147164.1',
'JQ574230.1',
'GU335433.1',
'JQ551045.1',
'GU160538.1',
'JQ551046.1',
'GU335427.1',
'HQ556675.1',
'JQ559031.1',
'GU160540.1',
'HQ571061.1',
'KX300291.1',
'JN262740.1',
'MN621041.1',
'JQ557343.1',
'GU699730.1',
'GU147586.1',
'GU147583.1',
'JQ560034.1',
'HM403740.1',
'MK767638.1',
'OM594190.1',
'GU335861.1',
'GU335864.1',
'MN621036.1',
'MN621026.1',
'JN266442.1',
'JN266444.1',
'JQ555038.1',
'JQ547796.1',
'JQ547664.1',
'JQ546935.1',
'JQ567146.1',
'HM409276.1',
'JQ566102.1',
'HM408897.1',
'MK767210.1',
'MW496845.1',
'MW496845.1',
);

$ids=array(
'MF172277.1',
'KF671026.1',
'KF919242.1',
'MF172279.1',
'MF509895.1',
'KJ812987.1',
'KJ812981.1',
'MF509887.1',
'MF509888.1',
'MF040165.1',
'KX671402.1',
'NC_036005.1',
'NC_036008.1',
'KJ812985.1',
'KM593009.1',
'KM593013.1',
'KJ812976.1',
'KJ812980.1',
'KJ812979.1',
'KJ812978.1',
'KM593018.1',
'KM593016.1',
'KM592996.1',
'KF919226.1',
'KJ812975.1',
'HE599225.1',
'KF919224.1',
'MW363419.1',
'MW363420.1',
'MW363421.1',
'KF671025.1',
'NC_037797.1',
'KF919233.1',
'MN997483.1',
'MN997482.1',
'NC_036006.1',
'KF919209.1',
'KX671414.1',
'KJ812990.1',
'MF509893.1',
'MF509891.1',
'MF509892.1',
'NC_036007.1',
'MT108653.1',
'MT108621.1',
'MT108602.1',
'MT552528.1',
'MT552583.1',
'MT552442.1',
'MT552436.1',
'MT552401.1',
'MT552389.1',
'MT552460.1',
'MT552505.1',
'MF381691.1',
'KJ812993.1',
'KF919230.1',
'KF919232.1',
'MN793283.1',
'MN793299.1',
'MN793270.1',
'MF381620.1',
'MN793302.1',
'KF919216.1',
'MT999243.1',
'MT552557.1',
'MT552530.1',
'LC473622.1',
'KU380407.1',
'LC473621.1',
'LC473620.1',
'AB738178.1',
'AB690839.1',
'LC646380.1',
'AB738254.1',
'KT358432.1',
'HQ398899.1',
'HQ398898.1',
'LC054453.1',
'LC646384.1',
'LC646381.1',
'LC646382.1',
'LC646379.1',
'AB738237.1',
'LC646383.1',
'KF406794.1',
'KT358431.1',
'KT358430.1',
'LC473628.1',
'LC473626.1',
'KU380408.1',
'LC473627.1',
'LC544017.1',
'LC544019.1',
'LC544018.1',
'MW476155.1',
'MW476149.1',
'MW476147.1',
'KU380355.1',
'LC473619.1',
'LC473618.1',

);


$parameters = array(
	'api_key' 	=> getenv('NCBI_API_KEY'),
	'retmode' 	=> 'xml',
	'db' 		=> 'nuccore',
	'id'		=> join(',', $ids)
);

$url = 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi';

$url .= '?' . http_build_query($parameters);

$xml = get($url);

$dom = new DOMDocument;
$dom->loadXML($xml, LIBXML_NOCDATA); 
$xpath = new DOMXPath($dom);

$seqs = array();

foreach ($xpath->query('//GBSeq') as $GBSeq)
{			
	$obj = new stdclass;
	
	if (0)
	{
		foreach ($xpath->query('GBSeq_primary-accession', $GBSeq) as $node)
		{
			$obj->id = $node->firstChild->nodeValue;
		}
	}
	
	// with version 
	if (1)
	{
		foreach ($xpath->query('GBSeq_accession-version', $GBSeq) as $node)
		{
			$obj->id = $node->firstChild->nodeValue;
		}
	}

	foreach ($xpath->query('GBSeq_taxonomy', $GBSeq) as $node)
	{
		$obj->lineage = preg_split('/;\s+/', $node->firstChild->nodeValue);
	}

	foreach ($xpath->query('GBSeq_organism', $GBSeq) as $node)
	{
		$obj->lineage[] = $node->firstChild->nodeValue;
	}
	
	foreach ($xpath->query('GBSeq_sequence', $GBSeq) as $node)
	{
		$obj->sequence = $node->firstChild->nodeValue;
	}
		
	$seqs[] = $obj;
	
}		

foreach ($seqs as $seq)
{
	echo $seq->id . "\t" . join("|", $seq->lineage) . "\n";

}

?>
