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

include_once('../config.php');

if (!$config['modules']['weather']['enabled']) {
  exit;
}

//henter været basert på lengde-, breddegrad og tidssone
$weather = getWeather($_GET['lat'], $_GET['lon'], $_GET['timeZone']);

//skriver ut været
echo "\t\t\tVind: ".$weather['windSpeed']."<br />\n";
echo "\t\t\tTemperatur: ".$weather['temperature']." &deg; C<br /><br />\n";
echo "\t\t</td>\n";
echo "\t\t<td>\n";
echo "\t\t\t&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src=\"".$weather['symbol']."\" alt=\"v&aelig;rsymbol\"/><br />\n";
echo "\t\t</td>\n";
echo "\t\t</tr>\n";
echo "\t</table>\n";

//skriver ut lenke til yr slik lisensen krever
echo "V&aelig;rvarsel fra <a href=\"http://www.yr.no/\">yr.no</a>, levert av Meteorologisk institutt og NRK<br /><br />\n";

/*
funksjon som henter returnerer vindinfo, tempinfo og værsymbol
for gitt lengde- og breddegrad ($lat og $lon). $timeZone brukes
for å bestemme om det er natt eller dag
*/
function getWeather($lat, $lon, $timeZone)
{
	//stammen i URL'en der symbolene befinner seg
	$symbolPath = "images/symbols/";

	/*
	arrayer som forteller hvilket symbol som tilsvarer hvilket i
	XML'en. alle filer som slutter på 'd' har sol, de med 'n'
	viser måne
	*/
	$symbolsDay = array(1 => "01d.png",
							2 => "02d.png",
							3 => "03d.png",
							4 => "04.png",
							5 => "05d.png",
							6 => "06d.png",
							7 => "07d.png",
							8 => "08d.png",
							9 => "09.png",
							10 => "10.png",
							11 => "11.png",
							12 => "12.png",
							13 => "13.png",
							14 => "14.png",
							15 => "15.png",
							16 => "16.png",
							17 => "17.png",
							18 => "18.png",
							19 => "19.png");

	$symbolsNight = array(1 => "01n.png",
							2 => "02n.png",
							3 => "03n.png",
							4 => "04.png",
							5 => "05n.png",
							6 => "06n.png",
							7 => "07n.png",
							8 => "08n.png",
							9 => "09.png",
							10 => "10.png",
							11 => "11.png",
							12 => "12.png",
							13 => "13.png",
							14 => "14.png",
							15 => "15.png",
							16 => "16.png",
							17 => "17.png",
							18 => "18.png",
							19 => "19.png");
	
	//henter XML med varsel for gitt lengde- og breddegrad fra yr
	$xml = simplexml_load_file("http://api.yr.no/weatherapi/locationforecast/1.7/?lat=$lat;lon=$lon");
	
	//henter ut hvilken time  det er basert på tidssone
	date_default_timezone_set($timeZone);
	$hour = date("G")+0;

	//henter ut windSpeed-nodene med XPath
	$windData = $xml->xpath("/weatherdata/product/time/location/windSpeed");
	
	//henter ut attributter i første windSpeed-node
	foreach ($windData[0]->attributes() as $a => $b)
	{
		//lager attributten med navn 'name' i $windSpeed
		if ($a=='name')
			$windSpeed = $b;
	}

	//tilsvarende windSpeed
	$temperatureData = $xml->xpath("/weatherdata/product/time/location/temperature");
	
	foreach ($temperatureData[0]->attributes() as $a => $b)
	{
		if ($a=='value')
			$temperature= $b;
	}
	
	//tilsvarende windSpeed
	$symbolData = $xml->xpath("/weatherdata/product/time/location/symbol");
	
	foreach ($symbolData[0]->attributes() as $a => $b)
	{
		if ($a=='number')
			$symbol = $b;
	}
	
	/*
	velger symbol på grunnlag av hvilken tid på dagen det er.
	her har vi valgt å vise sol hvis timen er fra og med 6 til
	og med 19
	*/
	if ($hour<20 and $hour>=6)
	{
		$imageLink = $symbolPath.$symbolsDay[$symbol+0];
	}
	else
	{
		$imageLink = $symbolPath.$symbolsNight[$symbol+0];
	}
	
	/*
	returnerer en array med windSpeed(tekst), temperature(tall)
	og symbol(bildelink)
	*/
	return array("windSpeed" => $windSpeed, "temperature" => $temperature, "symbol" => $imageLink);
}


?>
