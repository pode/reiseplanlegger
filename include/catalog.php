<?php
//forteller PHP at feilmeldinger skal vises
ini_set('display_errors', 1);
error_reporting(E_ALL|E_STRICT);

/*
funksjon som returnerer katalogdata, parametere er ccl-søkestreng,
syntax(normarc, usmarc, marc21), returformat(string, xml), om det
skal legges til en ny root-node i XML-strukturen og
z39.50-server-url:portnummer/database
*/
function yazCclSearch()
{
	//henter den globale konfigurasjonen
	$config = get_config();
	
	//$GLOBALS['fields'];
	
	//funksjonen kan ha maks 5 parametere
	$args = func_get_args();
	
	if (!$args[1]) { $args[1] = "normarc"; }
	if (!$args[2]) { $args[2] = "xml"; }
	if (!$args[3]) { $args[3] = true; }
	if (!$args[4]) { $args[4] = "z3950.deich.folkebibl.no:210/data"; }
	
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
	yaz_ccl_conf($id, $config);
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
function yazCclArray($ccl, $syntax = 'marc21', $host = 'z3950.deich.folkebibl.no:210/data')
{
	$config = get_config();
	
	$type = 'xml';
		
	$id = yaz_connect($host);
	yaz_element($id, "F");
	yaz_syntax($id, $syntax);
	yaz_range($id, 1, 1);
	
	yaz_ccl_conf($id, $config);
	$cclresult = array();
	if (!yaz_ccl_parse($id, $ccl, $cclresult))
	{
		echo 'Error: '.$cclresult["errorstring"];
	}
	else
	{
		$rpn = $cclresult["rpn"];
		yaz_search($id, "rpn", utf8_decode($rpn));
	}
	
	yaz_wait();

	$error = yaz_error($id);
	if (!empty($error))
	{
		echo "Error: $error";
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