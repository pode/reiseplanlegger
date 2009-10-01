<?php

/*
Funksjoner som trengs for å søke i katalogen med Z39.50 eller SRU. 
*/

function z_search($q, $limit = 20, $start = 1, $order = 'descending', $sortBy = 'year', $showAuthor = false) {
	
	//sti til XSL
	$xsl_url = '../xsl/bokliste.xsl';
	
	//oppretter DOM-dok med XML-data
	$xml = new DOMDocument;
	$xml->loadXML(get_ccl_results_as_xml($q, $limit));
	
	//teller antallet <record>-noder (antall søketreff)
	$nodeList = $xml->getElementsByTagName('record');
	$hits = $nodeList->length;
	
	//ingen treff
	if ($hits==0) 
	{
		return false;
	}
	//treff, XML blir transformert og skrevet ut
	else
	{
		// echo "<p>Antall treff: $hits</p>\n";
		
		$params = array(array('namespace' => '', 'name' => 'url_ext', 'value' => "type=z39.50"), // TODO: Brukes denne? 
					    array('namespace' => '', 'name' => 'sortBy',  'value' => $sortBy),
					    array('namespace' => '', 'name' => 'order',   'value' => $order), 
					    array('namespace' => '', 'name' => 'target',  'value' => "remote"), 
					    array('namespace' => '', 'name' => 'visForfatter',  'value' => $showAuthor)); 
	
		return transformToHTML($xml, $xsl_url, $params);
	}
	
}

function sru_search($q, $limit = 20, $start = 1, $order = 'descending', $sortBy = 'year', $showAuthor = false) {
		
	global $config;
		
	//oppretter URL til KOHA med cql
	$xml_url = getSRUURL($q, $start, $limit);
	//sti til XSL
	$xsl_url = '../xsl/boklistesru.xsl';
	
	//henter XML-data
	$xml_data = file_get_contents($xml_url) or exit("Feil");
	//fjerner namespace
	$xml_data = str_replace("<record xmlns=\"http://www.loc.gov/MARC21/slim\">", "<record>", $xml_data);
	
	//oppretter DOM-dok med XML-data
	$xml = new DOMDocument;
	$xml->loadXML($xml_data);
	
	//teller antallet <recordData>-noder (antall søketreff)
	$nodeList = $xml->getElementsByTagName('recordData');
	$hits = $nodeList->length;
	
	if ($hits > 0) {
	
	//parametere til XSL
	$params = array(array('namespace' => '', 'name' => 'url_ext', 'value' => "type=sru"), // TODO: Brukes denne? 
					array('namespace' => '', 'name' => 'sortBy',  'value' => $sortBy),
					array('namespace' => '', 'name' => 'order',   'value' => $order), 
					array('namespace' => '', 'name' => 'target',  'value' => "remote"),
					array('namespace' => '', 'name' => 'visForfatter',  'value' => $showAuthor),  
					array('namespace' => '', 'name' => 'showHits',  'value' => "false"), 
					array('namespace' => '', 'name' => 'item_url', 'value' => $config['libraries'][$_GET['bib']]['item_url']));
	
	//transformerer til HTML
	return transformToHTML($xml, $xsl_url, $params);

	} else {
	
		return false;
		
	}

}

?>
