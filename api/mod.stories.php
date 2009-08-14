<?php

include_once('../include/functions.php');

/*
Henter ut fortellinger fra katalogen
*/

if (!empty($_GET['country'])) {

  search($_GET['country'], 5, $_GET['type']);	
	
}

function search($search, $limit = 20, $type = 'sru', $order = 'ascending', $sortBy = 'year') {
	
	/*
	hvis ikke $type er satt til sru eller z39.50
	blir den satt til sru
	*/
	if ($type!='sru' && $type!='z39.50')
	{
		$type = 'sru';
	}
	
	/*
	hvis ikke $order er satt til stigende eller synkende
	blir den satt til stigende
	*/
	if ($order!='ascending'&&$order!='descending')
	{
		$order = 'ascending';
	}
	
	/*
	hvis ikke $sortBy er satt til tittel eller år
	blir den satt til år
	*/
	if ($sortBy!='title'&&$sortBy!='year')
	{
		$sortBy = 'year';
	}
	
	//hvis type er z39.50, man vil søke med z39.50
	if($type=="z39.50")
	{
	
		//sti til XSL
		$xsl_url = '../xsl/bokliste.xsl';

		//oppretter DOM-dok med XML-data
		$xml = new DOMDocument;
		$xml->loadXML(get_ccl_results_as_xml("eo=$search Fortellinger", $limit));

		//teller antallet <record>-noder (antall søketreff)
		$nodeList = $xml->getElementsByTagName('record');
		$hits = $nodeList->length;
		
		// TODO: Bør begrense når man søker, ikke etterpå
		/* Funker ikke. Feilmelding: 
		Fatal error: Uncaught exception 'DOMException' with message 'Not Found Error' in /home/magnus/public_html/reiseplanlegger/api/mod.stories.php:64 Stack trace: #0 /home/magnus/public_html/reiseplanlegger/api/mod.stories.php(64): DOMNode->removeChild(Object(DOMElement)) #1 /home/magnus/public_html/reiseplanlegger/api/mod.stories.php(11): search('Frankrike', 2, 'z39.50') #2 /home/magnus/public_html/reiseplanlegger/api/index.php(3): include('/home/magnus/pu...') #3 {main} thrown in /home/magnus/public_html/reiseplanlegger/api/mod.stories.php on line 64
		for ($i=0; $i<=$hits; $i++) {
		  if ($i > $limit) {
		    $record = $xml->getElementsByTagName('record')->item($i);
            $old = $xml->removeChild($record);
		  }
		}
		*/
		
		//ingen treff
		if ($hits==0) 
		{
			echo "<p>Ingen treff...</p>\n";
		}
		//treff, XML blir transformert og skrevet ut
		else
		{
			// echo "<p>Antall treff: $hits</p>\n";
			
			$params = array(array('namespace' => '', 'name' => 'url_ext', 'value' => "type=".$type),
						    array('namespace' => '', 'name' => 'sortBy',  'value' => $sortBy),
						    array('namespace' => '', 'name' => 'order',   'value' => $order));
	
			echo transformToHTML($xml, $xsl_url, $params);
		}
	}
	//type er SRU
	else if($type=="sru")
	{
		//oppretter cql-spørresetning
		$cql = getCql($search, "../dewey/dewey_list.txt");
		//oppretter URL til KOHA med cql
		$xml_url = getSRUURL($cql);
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
		
		//parametere til XSL
		$params = array(array('namespace' => '', 'name' => 'url_ext', 'value' => "type=".$type),
						array('namespace' => '', 'name' => 'sortBy',  'value' => $sortBy),
						array('namespace' => '', 'name' => 'order',   'value' => $order));
	
		//transformerer til HTML
		echo transformToHTML($xml, $xsl_url, $params);
	}

}

function get_ccl_results_as_xml($ccl, $limit) {

	/*
	henter funksjonene i catalog.php, catalog.php inneholder
	funksjoner for å hente ut katalogdata fra z39.50-servere
	*/
	require_once '../include/catalog.php';
	
	$out = '';
	
	/*
	hvis ikke ccl-parameteren er oppgitt får man en tom XML-struktur
	tilbake med records som rotnode
	*/
	if (!isset($ccl))
	{
		
		$out .= "<records>\n</records>";
	} 
	/*
	hvis ccl-parameteren er satt får man MARCXML basert på ccl-
	parameteren tilbake
	*/
	else
	{
		
		$out .= "<records>\n";
		/*
		kjører funksjonen yazCclArray som returnerer en array med
		MARCXML-data basert på $query. syntaksen er 'normarc'. mot
		deichmanske kan denne byttes til hvertfall USMARC og MARC21
		*/
		$fetch = yazCclArray($ccl, 'normarc', $limit);
		/*
		henter ut verdien med nøkkelen 'result'. det er her selve
		dataene ligger lagret. $fetch-arrayen har også en verdi med
		nøkkel 'hits' som forteller hvor mange records $fetch inneholder
		*/
		$data = $fetch['result'];
		//går gjennom $data-arrayen
		foreach ($data as $record)
		{
			//splitter på nylinjetegn
			$lines = explode("\n", $record);
			/*
			overskriver den første noden i hver record med en
			'<record>'-node. dette gjør at namespacet blir fjernet
			og gjør parsing og transformering av XML lettere
			*/
			$lines[0] = "<record>";
			/*
			samler arrayen $lines til en streng og konverterer til
			utf-8
			*/
			$out .= utf8_encode(implode("\n", $lines));
		}
		$out .= "</records>";
	}
	
	return $out;
	
}


?>