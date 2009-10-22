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
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
	echo "<records>\n";
	//lagrer ccl-parameteren i $query
	$query = $_GET['ccl'];
	/*
	kjører funksjonen yazCclArray som returnerer en array med
	MARCXML-data basert på $query. syntaksen er 'normarc'. mot
	deichmanske kan denne byttes til hvertfall USMARC og MARC21
	*/
	$fetch = yazCclArray($query, 'normarc');
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
		echo utf8_encode(implode("\n", $lines));
	}
	echo "</records>";
}
?>