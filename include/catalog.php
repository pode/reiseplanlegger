<?php
//forteller PHP at feilmeldinger skal vises
ini_set('display_errors', 1);
error_reporting(E_ALL|E_STRICT);

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

/*
funksjon som returnerer katalogdata, parametere er ccl-søkestreng,
syntax(normarc, usmarc, marc21), returformat(string, xml), om det
skal legges til en ny root-node i XML-strukturen og
z39.50-server-url:portnummer/database
*/
function yazCclSearch()
{
	
	global $config;
	
	//henter den konfigurasjonen for Z39.50
	$zconfig = get_config();
	
	//$GLOBALS['fields'];
	
	//funksjonen kan ha maks 5 parametere
	$args = func_get_args();
	
	if (!$args[1]) { $args[1] = "normarc"; }
	if (!$args[2]) { $args[2] = "xml"; }
	if (!$args[3]) { $args[3] = true; }
	if (!$args[4]) { $args[4] = $config['libraries'][$_GET['bib']]['z3950']; }
	
	$ccl 			=& $args[0];
	$syntax			=& $args[1];
	$type			=& $args[2];
	$rootNode		=& $args[3];
	$host			=& $args[4];
	
	//oppretter connection-objektet
	$id = yaz_connect($host);
	//ber om full records (F, B=brief records)
	yaz_element($id, "F");
	//velger syntaks
	yaz_syntax($id, $syntax);
	/*
	vet ikke hva denne gjør, men var i eksempel hentet fra 
	http://no.php.net/manual/en/yaz.examples.php
	*/
	yaz_range($id, 1, 1);
	
	//gjør om ccl-søkestrengen til rpn og søker
	yaz_ccl_conf($id, $zconfig);
	$cclresult = array();
	if (!yaz_ccl_parse($id, $ccl, $cclresult))
	{
		echo 'Error: '.$cclresult["errorstring"];
	}
	else
	{
		$rpn = $cclresult["rpn"];
		//søker på rpn gjort om til ISO-8859-1
		yaz_search($id, "rpn", utf8_decode($rpn));
	}
	
	//venter på at alle forespørsler skal gjennomføres
	yaz_wait();

	/*
	skriver ut errormelding hvis noe ble galt, ellers lagres
	antallet treff i $hits
	*/
	$error = yaz_error($id);
	if (!empty($error))
	{
		echo "Error: $error";
	}
	else
	{
		$hits = yaz_hits($id);
	}
	
	$data = null;
	
	//går gjennom alle returnerte records og legger disse i $data
	for ($p = 1; $p <= $hits; $p++)
	{
		$rec = yaz_record($id, $p, $type);
		//fortsetter hvis record er tom
		if (empty($rec)) continue;
		$data .= $rec;
	}

	/*
	hvis valgt type er XML blir XML-dataene omsluttet med
	<records>-noden (hvis ikke blir XML-strukturen gal)
	*/
	if ($type=='xml')
	{
		if ($rootNode)
			$data = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<records>\n$data</records>";		
	}
	
	//lager en returarray med hits og result
	$ret = array("hits" => $hits, "result" => utf8_encode($data));
	
	return $ret;
}

/*
returnerer en array med XML-data, hvert element i arrayen
inneholder XML-data om en record. funksjonen fungerer omtrent
på samme måte som yazCclSearch
*/
function yazCclArray($ccl, $syntax = 'marc21', $limit = 20, $host = 'default')
{
	
	global $config;
	
	if ($host == 'default') {
		$host = $config['libraries'][$_GET['bib']]['z3950'];
	}
	
	$zconfig = get_config();
	$hits = 0;
	
	$type = 'xml';
		
	$id = yaz_connect($host);
	yaz_element($id, "F");
	yaz_syntax($id, $syntax);
	yaz_range($id, 1, 1);
	
	yaz_ccl_conf($id, $zconfig);
	$cclresult = array();
	if (!yaz_ccl_parse($id, $ccl, $cclresult))
	{
		echo 'Error: '.$cclresult["errorstring"];
	}
	else
	{
		// NB! Ser ikke ut som Z39.50 fra Bibliofil støtter "sort"
		// Se nederst her: http://www.bibsyst.no/produkter/bibliofil/z3950.php
		// PHP/YAZ-funksjonen yaz-sort ville kunne dratt nytte av dette: 
		// http://no.php.net/manual/en/function.yaz-sort.php
		// Sort Flags
		// a Sort ascending
		// d Sort descending
		// i Case insensitive sorting
		// s Case sensitive sorting
		// Bib1-attributter man kunne sortert på: 
		// http://www.bibsyst.no/produkter/bibliofil/z/carl.xml
		// yaz_sort($id, "1=31 di");
		$rpn = $cclresult["rpn"];
		yaz_search($id, "rpn", utf8_decode($rpn));
	}
	
	yaz_wait();

	$error = yaz_error($id);
	if (!empty($error))
	{
		echo "Error yazCclArray: $error";
	}
	else
	{
		$hits = yaz_hits($id);
	}
	
	$data = array();
	
	for ($p = 1; $p <= $hits; $p++)
	{
		$rec = yaz_record($id, $p, $type);
		if (empty($rec)) continue;
		$data[] = $rec;
		if ($p == $limit) {
		  break;
		}
	}
	
	$ret = array("hits" => $hits, "result" => $data);
	
	return $ret;
}

function get_config() {

	/*
	kvalifikatorsetup til yaz_ccl_conf, disse verdiene er hentet fra
	BIB-1 attributtsettet funnet her:
	http://bibsyst.no/produkter/bibliofil/bib1.php
	ti => 1=4
	ti = tittel
	1 = structure (virker bare med 1 her)
	4 = use attribute
	
	KVALIFIKATORFORKLARING:
	ti -> tittel
	kl -> klassifikasjon (dewey)
	fo -> forfatter
	år -> år
	sp -> språk
	eo -> emneord
	is -> isbn
	tnr -> tittelnummer
	*/
	return $config = array(
		"ti" => "1=4",
		"kl" => "1=13",
		"fo" => "1=1003",
		"år" => "1=31",
		"sp" => "1=54",
		"eo" => "1=21",
		"is" => "1=7",
		"tnr" => "1=12",
		"ke" => "1=21");	
	
}

?>