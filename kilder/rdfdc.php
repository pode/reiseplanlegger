<?php

/*

Copyright 2009 ABM-utvikling

This file is part of "Podes reiseplanlegger".

"Podes reiseplanlegger" is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

"Podes reiseplanlegger" is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with "Podes reiseplanlegger".  If not, see <http://www.gnu.org/licenses/>.

Source code available from: 
http://github.com/pode/reiseplanlegger/

*/

//forteller PHP at feilmeldinger skal vises
ini_set('display_errors', 1);
error_reporting(E_ALL|E_STRICT);

/*
kjører header funksjonen som forteller at dokumentet er XML
med utf-8 som tegnsett
*/
header('Content-Type: text/xml; Extension: xml; charset=UTF-8');

/*
henter funksjonene i catalog.php, catalog.php inneholder
funksjoner for å hente ut katalogdata fra z39.50-servere
*/
require_once '../include/catalog.php';

$xsl_path = 'http://www.loc.gov/standards/marcxml/xslt/MARC21slim2RDFDC.xsl';

if (isset($_GET['ccl']))
{
	$query = $_GET['ccl'];
	$res = yazCclArray($query, 'marc21');

	$data = $res['result'];

	// Load the XSL source
	$xsl = new DOMDocument;
	$xsl->load($xsl_path);

	// Configure the transformer
	$proc = new XSLTProcessor;
	$proc->importStyleSheet($xsl); //attach the xsl rules
	
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
	echo "<!DOCTYPE rdf:RDF PUBLIC \"-//DUBLIN CORE//DCMES DTD 2002/07/31//EN\"
    \"http://dublincore.org/documents/2002/07/31/dcmes-xml/dcmes-xml-dtd.dtd\">\n";
	echo "<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\">\n";
	
	foreach ($data as $rec)
	{
		$xml_data = utf8_encode($rec);

		// Load the XML source
		$xml = new DOMDocument;
		$xml->loadXML($xml_data);

		$raw = $proc->transformToXML($xml);
		
		$split = explode("\n", $raw);
		$split = array_splice($split, 1);
		$split[0] = "<rdf:Description>";
		$fixed = implode("\n", $split);
		
		echo $fixed;
	}
	
	echo "</rdf:RDF>";
}
else
{
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
	echo "<!DOCTYPE rdf:RDF PUBLIC \"-//DUBLIN CORE//DCMES DTD 2002/07/31//EN\"
    \"http://dublincore.org/documents/2002/07/31/dcmes-xml/dcmes-xml-dtd.dtd\">\n";
	echo "<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\">\n</rdf:RDF>";
}
?>