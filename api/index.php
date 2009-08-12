<?php
/*
--------------- STEDSFORSLAG ---------------
lar bruker velge sted som passer med søkeord
*/
//geoId er ikke satt, place er satt
if (!isset($_GET['geoId']) && isset($_GET['place']))
{
	$place = $_GET['place'];
	$type = $_GET['type'];
	$tnr = "";
	if(isset($_GET['tittelnr']))
		$tnr = "&amp;tittelnr=".$_GET['tittelnr'];

	//hvis place ikke er tom
	if (!empty($place))
	{
		//søker GeoNames
		$res = searchGeo($place);
		//fant ikke stedet
		if(empty($res))
			echo "<h3>Fant ikke stedet $place.</h3>\n";
		//ett resultat, tid, værdata og kart blir skrevet
		else if(count($res)==1)
		{
			//henter ut geoId
			$geoId = $res['0']['geoId'];
			//henter info om sted basert på geoId
			$placeInfo = getGeo($geoId);
			
			//lagrer sted og land
			$placeEng = $placeInfo['place'];
			$country = $placeInfo['country'];
			
			echo "<h3>$placeEng, $country</h3>\n";
			
			//skriver ut tid basert på tidssone
			echo "\t<table>\n";
			echo "\t\t<tr>\n";
			echo "\t\t<td>\n";
			echo "\t\t\t<br />".getLocalTime($placeInfo['timeZone'])."<br /><br />\n";
			
			//henter været basert på lengde-, breddegrad og tidssone
			$weather = getWeather($placeInfo['lat'], $placeInfo['lon'], $placeInfo['timeZone']);
			
			//skriver ut været
			echo "\t\t\tVind: ".$weather['windSpeed']."<br />\n";
			echo "\t\t\tTemperatur: ".$weather['temperature']." &deg; C<br /><br />\n";
			echo "\t\t</td>\n";
			echo "\t\t<td>\n";
			echo "\t\t\t&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src=\"".$weather['symbol']."\" alt=\"værsymbol\"/><br />\n";
			echo "\t\t</td>\n";
			echo "\t\t</tr>\n";
			echo "\t</table>\n";

			//skriver ut lenke til yr slik lisensen krever
			echo "Værvarsel fra <a href=\"http://www.yr.no/\">yr.no</a>, levert av Meteorologisk institutt og NRK<br /><br />\n";

			/*
			skriver ut kartet basert på lengde-, breddegrad, dimensjoner og zoom-level
			*/
			echo "\t<div id=\"map\">\n";
			echo "\t</div>\n";
			getMap($placeInfo['lat'], $placeInfo['lon'], "340", "280", "12");
		}
		//flere treff, bruker må velge sted
		else
		{
			echo "<h3>Hente tilleggsdata for:</h3>\n";
			//lager linker
			foreach ($res as $pl)
			{
				if (!empty($sortBy)) //&&$type!='rss'
					echo "<a class=\"extras\" href=\"reiseplanlegger.php?geoId=$pl[geoId]&amp;place=$place&amp;country=$pl[country]&amp;type=$type$tnr&amp;sortBy=$sortBy&amp;order=$order\">$pl[place], $pl[country]</a><br />\n";
				else
					echo "<a class=\"extras\" href=\"reiseplanlegger.php?geoId=$pl[geoId]&amp;place=$place&amp;country=$pl[country]&amp;type=$type$tnr\">$pl[place], $pl[country]</a><br />\n";
			}
		}
	}
}
//geoId er satt
else if(isset($_GET['geoId']))
{
	//henter ut geoId
	$geoId = $_GET['geoId'];
	//henter info om sted basert på geoId
	$placeInfo = getGeo($geoId);

	//lagrer sted og land
	$place = $placeInfo['place'];
	$country = $placeInfo['country'];

	echo "<h3>$place, $country</h3>\n";

	//skriver ut tid basert på tidssone
	echo "\t<table>\n";
	echo "\t\t<tr>\n";
	echo "\t\t<td>\n";
	echo "\t\t\t<br />".getLocalTime($placeInfo['timeZone'])."<br /><br />\n";

	//henter været basert på lengde-, breddegrad og tidssone
	$weather = getWeather($placeInfo['lat'], $placeInfo['lon'], $placeInfo['timeZone']);

	//skriver ut været
	echo "\t\t\tVind: ".$weather['windSpeed']."<br />\n";
	echo "\t\t\tTemperatur: ".$weather['temperature']." &deg; C<br /><br />\n";
	echo "\t\t</td>\n";
	echo "\t\t<td>\n";
	echo "\t\t\t&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src=\"".$weather['symbol']."\" alt=\"værsymbol\"/><br />\n";
	echo "\t\t</td>\n";
	echo "\t\t</tr>\n";
	echo "\t</table>\n";

	//skriver ut lenke til yr slik lisensen krever
	echo "Værvarsel fra <a href=\"http://www.yr.no/\">yr.no</a>, levert av Meteorologisk institutt og NRK<br /><br />\n";

	/*
	skriver ut kartet basert på lengde-, breddegrad, dimensjoner og zoom-level
	*/
	echo "\t<div id=\"map\">\n";
	echo "\t</div>\n";
	getMap($placeInfo['lat'], $placeInfo['lon'], "340", "280", "12");
}
?>
