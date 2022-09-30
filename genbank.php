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
