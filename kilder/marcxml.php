<?php
//forteller PHP at feilmeldinger skal vises
ini_set('display_errors', 1);
error_reporting(E_ALL|E_STRICT);

/*
kjører header funksjonen som forteller at dokumentet er XML
med utf-8 som tegnsett
*/
header('Content-Type: text/xml; Extension: xml; charset=utf-8');

/*
henter funksjonene i catalog.php, catalog.php inneholder
funksjoner for å hente ut katalogdata fra z39.50-servere
*/
require_once '../include/catalog.php';

/*
hvis ikke ccl-parameteren er oppgitt får man en tom XML-struktur
tilbake med records som rotnode
*/
if (!isset($_GET['ccl']))
{
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
	echo "<records>\n</records>";
}

/*
hvis ccl-parameteren er satt får man MARCXML basert på ccl-
parameteren tilbake
*/
else
{
	//lagrer ccl-parameteren i $query
	$query = $_GET['ccl'];
	/*
	kjører funksjonen yazCclXML med $query som parameter. MARCXML-
	dataene blir da lagret i $data
	*/
	$data = yazCclSearch($query, 'normarc', 'xml', true, '');
	//skriver ut teksten med nøkkelen 'result'
	echo $data['result'];
}
?>