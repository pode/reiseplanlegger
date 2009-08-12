<?php

/*
--------------- STEDSFORSLAG ---------------
lar bruker velge sted som passer med søkeord
*/
//geoId er ikke satt, place er satt
if (empty($_GET['geoId']) && isset($_GET['place']))
{
	$place = rawurldecode($_GET['place']);
	$type = $_GET['type'];
	
	$tnr = "";
	if(isset($_GET['tittelnr']))
		$tnr = "&amp;tittelnr=".$_GET['tittelnr'];

	//hvis place ikke er tom
	if (!empty($place))
	{
		
		//søker GeoNames
		$res = searchGeo($place);
		
		if (empty($res)) { 
		
		  // Det kan være at bruker søkte etter et land, i så fall skal vi vise data for hovedstaden
		  // Dette forutsetter at navnet vi ser etter foreligger på engelsk
		  $place_url = urlencode($place);
		  $json = json_decode(file_get_contents("http://ajax.googleapis.com/ajax/services/language/translate?v=1.0&q={$place_url}&langpair=no|en"));
          $placeEng = $json->responseData->translatedText;
		  $res = searchGeo(getCapital($placeEng), $placeEng);
		}
			
		// Hvis $res fortsatt er tom fant vi ikke stedet, hverken som by eller land
		if(empty($res)) 
		{
			
			$out['result'] = 0;
			$out['debug'] = $_SERVER['QUERY_STRING'];
			echo(json_encode($out));
			
		//ett resultat
		}
		else if(count($res)==1)
		{
			
			$out['result'] = 1;
			
			//henter ut geoId
			$out['geoId'] = $res['0']['geoId'];
			//henter info om sted basert på geoId
			$out['placeInfo'] = getGeo($out['geoId']);
			
			// lagrer sted og land
			// $out['placeEng'] = $placeInfo['place'];
			// $out['country'] = $placeInfo['country'];
			
			$out['localTime'] = getLocalTime($placeInfo['timeZone']);
			
			$out['debug'] = $_SERVER['QUERY_STRING'];
			
			echo(json_encode($out));
			
		}
		//flere treff, bruker må velge sted
		else
		{
			
			$out['result'] = 2;
			
			//lager linker
			$out['links'] = array();
			$c = 0;
			foreach ($res as $pl)
			{
				if (!empty($sortBy)) //&&$type!='rss'
				{
					$out['links'][$c]['url'] = "geoId=$pl[geoId]&amp;place=$place&amp;country=$pl[country]&amp;type=$type$tnr&amp;sortBy=$sortBy&amp;order=$order";
				} else {
					$out['links'][$c]['url'] = "geoId=$pl[geoId]&amp;place=$place&amp;country=$pl[country]&amp;type=$type$tnr";
				}
				$out['links'][$c]['place'] = "$pl[place], $pl[country]";
				$c++;
			}
			
			$out['debug'] = $_SERVER['QUERY_STRING'];
			
			echo(json_encode($out));
			
		}
	}
}

//geoId er satt
else if(isset($_GET['geoId']))
{
	
	$out['result'] = 1;
	$out['geoId'] = $_GET['geoId'];
	//henter info om sted basert på geoId
	$out['placeInfo'] = getGeo($_GET['geoId']);
	$out['localTime'] = getLocalTime($out['placeInfo']['timeZone']);

    $out['debug'] = $_SERVER['QUERY_STRING'];

    echo(json_encode($out));

}

/*
funksjon som henter ut geoId, engelsk stedsnavn og engelsk
landsnavn basert på norske stedsnavn (med alternative navn)
*/
function searchGeo($place, $country="")
{
	//URL til cities15000.txt hentet fra GeoNames.org
	$world = "../geonames/cities15000.txt";
	
	//åpner fil for lesing
	$file_world = file_get_contents($world) or exit("Kunne ikke hente fil... $world");
	
	//oppretter returarray
	$ret = array();
	
	//splitter filen i linjer
	$file_world_array = explode("\n", $file_world);
	
	//gjør dette så lenge man ikke har kommet til slutten på filen
	foreach ($file_world_array as $line)
	{
		if (!$line)
			continue;
			
		//splitter på tabulator
		$raw_data = explode("\t", $line);

		//lagrer engelsk stedsnavn fra andre kolonne i filen
		$place_file = $raw_data[1];
		
		/*
		hvis dette stedet er det man søker etter blir data lagt i
		ret[]
		*/
		if (strtolower($place_file)==strtolower($place))
		{
			/*
			henter geoId (kolonne 1), place, språk (kolonne 14) og land via
			landskodefunksjonen (kolonne 9)
			*/
			$place_info = array("geoId" => $raw_data[0],
									"place" => $place_file,
									"country" => getCountryName($raw_data[8]));
			
			if ($country == "") {
			  $ret[] = $place_info;
			} elseif (strtolower($place_info['country']) == strtolower($country)) {
			  $ret[] = $place_info;	
			}
		}
		/*
		finnes ikke ønsket stedsnavn i filen blir også alternative
		navn søkt i (kolonne 4)
		*/
		else
		{
			$alternative_place_raw = $raw_data[3];
			//splitter på komma
			$alternative_place = explode(",", $alternative_place_raw);

			//går gjennom alternative navn
			foreach ($alternative_place as $alternative_place_file)
			{
				/*
				hvis stedsnavnet finnes puttes det i ret[] på
				samme måte som engelske stedsnavn
				*/
				if (strtolower($alternative_place_file)==strtolower($place))
				{
					$place_info = array("geoId" => $raw_data[0],
											"place" => $alternative_place_file,
											"country" => getCountryName($raw_data[8]));
					if ($country == "") {
			          $ret[] = $place_info;
			        } elseif (strtolower($place_info['country']) == strtolower($country)) {
			          $ret[] = $place_info;	
			        }
				}
			}
		}
	}
	
	return $ret;
}

/*
returnerer lat, lon, tidssone, engelsk stedsnavn, språk og land i et array
med nøkler 'lat', 'lon', 'timeZone', 'place', 'lang' og 'country'.
parameter er geonames-id
*/
function getGeo($geoId)
{
	//$norway = "http://frigg.hiof.no/h09d08/geonames/NO.txt";
	//$contents_norway = file_get_contents($norway) or exit("Kunne ikke hente fil... $norway");
	$world = "../geonames/cities15000.txt";
	$contents_world = file_get_contents($world) or exit("Kunne ikke hente fil... $world");
	
	/*
	splitter strengen på nylinjetegn, vi har da cities15000.txt
	linje for linje i en array
	*/
	$data_world = explode("\n", $contents_world);
	
	foreach ($data_world as $line)
	{
		//splitter opp hver linje på tabulator
		$countryInfo = explode("\t", $line);
		
		//oppretter når riktig geonames-id er funnet
		if ($countryInfo[0]==$geoId)
		{
			//indeksen forteller hvilken kolonne verdiene er hentet fra
			$ret = array("lat"      => $countryInfo[4],
						 "lon"      => $countryInfo[5],
						 "timeZone" => $countryInfo[17],
						 "place"    => $countryInfo[1],
						 "lang"     => getLang($countryInfo[8]),
						 "country"  => getCountryName($countryInfo[8]));
		}
	}
	
	return $ret;
}

/*
funksjon som på basis av ISO 3166-1-landskode på to bokstaver
gir fullt engelsk landsnavn
*/
function getCountryName($countryCode)
{
	$country_list = "../geonames/countryInfo.txt";
	$contents = file_get_contents($country_list) or exit("Kunne ikke hente fil... $country_list");
	
	$data = explode("\n", $contents);
	
	foreach ($data as $line)
	{
		$countryInfo = explode("\t", $line);
		
		if (strtolower($countryInfo[0])==strtolower($countryCode))
		{
			return $countryInfo[4];
		}
	}
	
	return -1;
}

/*
funksjon som på basis av ISO 3166-1-landskode på to bokstaver
gir forkortelsene for landets språk
*/
function getLang($countryCode)
{
	
	$language_names = array();
	
	$country_list = "../geonames/countryInfo.txt";
	$contents = file_get_contents($country_list) or exit("Kunne ikke hente fil... $country_list");
	$data = explode("\n", $contents);
	
	$language_list = "../geonames/lang.csv";
	$languages_file = file_get_contents($language_list) or exit("Kunne ikke hente fil... $language_list");
	$language_lines = explode("\n", $languages_file);
	// Gjør om språk-kodene til et array med koden som nøkkel og navnet på språket som verdi
	$languages = array();
	foreach ($language_lines as $language_line) 
	{
	  	list($key, $value) = explode("\t", $language_line);
	  	$languages[$key] = $value;
	}
	
	foreach ($data as $line)
	{
		$countryInfo = explode("\t", $line);
		
		if (strtolower($countryInfo[0])==strtolower($countryCode))
		{
			$lang_as_string = $countryInfo[15];
			$langs = explode(",", $lang_as_string);
			foreach ($langs as $lang) 
			{
				// Varianter av feks angis som en-NZ, vi er bare interessert i de to første tegnene
				$lang = substr($lang, 0, 2);
				// Sjekk om dette er et språk vi kjenner til
				if ($languages[$lang]) 
				{
				  $language_names[] = $languages[$lang];
				} 
				else 
				{
				  $language_names[] = "Ukjent språk ($lang)";	
				}
			}	
			return $language_names;
		}
	}
	
	return -1;
}

/*Funksjon som antar at argumentet er navnet på et land, og forsøke
å finne navnet på hovedstaden basert på dette
*/
function getCapital($country) {

	$country_list = "../geonames/countryInfo.txt";
	$contents = file_get_contents($country_list) or exit("Kunne ikke hente fil... $country_list");
	
	$data = explode("\n", $contents);
	
	foreach ($data as $line)
	{
		$countryInfo = explode("\t", $line);
		
		if (strtolower($countryInfo[4])==strtolower($country))
		{
			return $countryInfo[5];
		}
	}
	
	return -1;

	
}

/*
funksjon som returnerer lokal tid for valgt tidssone $timeZone
*/
function getLocalTime($timeZone)
{
	//oversettelsestabell fra engelske forkortede ukedager til norske
	$days = array("Mon" => "Mandag",
					"Tue" => "Tirsdag",
					"Wed" => "Onsdag",
					"Thu" => "Torsdag",
					"Fri" => "Fredag",
					"Sat" => "Lørdag",
					"Sun" => "Søndag");

	////oversettelsestabell fra engelske månedsnavn til norske
	$months = array("January" => "Januar",
						"February" => "Februar",
						"March" => "Mars",
						"April" => "April",
						"May" => "Mai",
						"June" => "Juni",
						"July" => "Juli",
						"August" => "August",
						"September" => "September",
						"October" => "Oktober",
						"November" => "November",
						"December" => "Desember");
						
	//forteller PHP hvilken tidssone man ønsker tid for
	date_default_timezone_set($timeZone);
	//lagrer ukedag, måned, dag og tid
	$weekday = date("D");
	$month= date("F");
	$day = date("j");
	$time = date("G:i T");
	
	//returnerer tiden i formatet dag nr. måned TT:MM tidssone
	return "$days[$weekday] $day. $months[$month] $time";
}


?>