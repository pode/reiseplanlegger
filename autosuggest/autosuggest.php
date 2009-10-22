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

//leser inn listen med dewey-nummere
$dewey_list_string = file_get_contents('../dewey/dewey_list.txt') or exit("Feil");

//splitter på nylinjetegn
$dewey_list_array = explode("\n", $dewey_list_string);

//lager to tomme arrays for stedsnavn og dewey-nummere
$dewey_list_places = array();
$dewey_list_numbers = array();

//går gjennom dewey-list linje for linje og splitter på kolon
foreach ($dewey_list_array as $dewey_list_item)
{
	if ($dewey_list_item)
	{
		$tmp = explode(':', $dewey_list_item);
		//putter stedsnavn i den ene arrayen
		$dewey_list_places[] = $tmp[0];
		//putter dewey-nummer i den andre
		$dewey_list_numbers[] = $tmp[1];
	}
}

//lager en tom resultatarray
$results_array = array();

//gjør hvis input er satt
if (isset($_GET['input']))
{
	//gjør om til lowercase og tar hensyn til øæå
	$input = mb_convert_case($_GET['input'], MB_CASE_LOWER, "UTF-8");
	//finner lengden på input-parameteren
	$len = mb_strlen(utf8_decode($_GET['input']));
	//setter limit til null hvis den ikke er satt, limit begrenser antall treff
	$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 0;
	
	//teller
	$count = 0;

	//gjør hvis $len inneholder noe
	if ($len)
	{
		//går gjennom stedsnavnlisten
		for ($i=0; $i<count($dewey_list_places); $i++)
		{
			//sammenligner input med hvert sted i dewey-list, kun de $len første tegnene sammenlignes
			if (mb_convert_case(mb_substr($dewey_list_places[$i], 0, $len, "UTF-8"), MB_CASE_LOWER, "UTF-8") == $input)
			{
				$count++;
				//stedet blir lagt til i resultatarrayet
				$results_array[] = array(
					'id' 		=> ($i + 1),
					'value' 	=> $dewey_list_places[$i],
					'info' 		=> $dewey_list_numbers[$i]
				);
			}
			
			//stopper hvis $count blir lik $limit
			if ($limit&&$count==$limit)
			{
				break;
			}
		}
	}
}

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
header ("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header ("Pragma: no-cache"); // HTTP/1.0

//hvis json er satt
if (isset($_GET['json']))
{
	//setter content-type mm.
	header('Content-Type: text/plain; Extension: json; charset=utf-8'); // application/json

	echo "{\n\t\"results\": [";
	$arr = array();
	
	//skriver ut arrayen i json-format
	foreach ($results_array as $result)
	{
		if (!empty($_GET['info']) && $_GET['info'] == 'true')
			$arr[] = "\n\t\t{\"id\": \"$result[id]\", \"value\": \"$result[value]\", \"info\": \"Deweynummer: $result[info]\"}";
		else
			$arr[] = "\n\t\t{\"id\": \"$result[id]\", \"value\": \"$result[value]\", \"info\": \"\"}";
	}
	echo implode(", ", $arr);
	echo "\n\t]\n}";
}
//hvis json ikke er satt blir det skrevet i xml-format
else
{
	//setter content-type mm.
	header('Content-Type: text/xml; Extension: xml; charset=utf-8');

	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<results>\n";
	foreach ($results_array as $result)
	{
		echo "\t<rs id=\"$result[id]\" info=\"$result[info]\">$result[value]</rs>\n";
	}
	echo "</results>";
}
?>