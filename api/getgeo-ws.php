<?php

/*

- Dokumentasjon av search-APIet: 
	http://www.geonames.org/export/geonames-search.html
	featureClass og featureCode hentes herfra:
	http://www.geonames.org/export/codes.html
	featureClass er på en bokstav, feks A for "country, state, region,..."
	featuteCode er på flere bokstaver
	Merk av søkebegrepet kan angis som parameterne q, name eller name_equals, 
	avhengig av hvor spesifik man vil være. 
	
- Dokumetasjon av countryInfo-APIet: 
	http://www.geonames.org/export/web-services.html#countryInfo

- Har ikke klart å finne et API der geoId kan brukes som parameter

*/

//geoId er ikke satt, place er satt
if (!empty($_GET['place'])) {

	// Array for å samle opp landene vi finner
	$data      = '';
	$sted_data = '';

	$place = urlencode($_GET['place']);
	
	// Se etter et land først
	$sted_data = json_decode(file_get_contents("http://ws.geonames.org/search?name=$place&maxRows=10&featureCode=PCLI&lang=nb&style=MEDIUM&type=json"));
 
	if ($sted_data->totalResultsCount == 0) {
	
		// Vi fant ikke noe land, da ser vi etter et sted
		$sted_data = json_decode(file_get_contents("http://ws.geonames.org/search?name=$place&maxRows=10&featureClass=P&lang=nb&style=MEDIUM&type=json"));
	
	}
	
	// Har vi noe sted nå? I så fall beriker vi stedet med mere info
	if ($sted_data->totalResultsCount > 0) {
	
		foreach ($sted_data->geonames as $sted) {
		
			// Sjekk om geoId er satt - i så fall skal vi bare ha ett av de stedne vi har funnet
			// Dersom geoId ikke er lik geonameId for dette stedet går vi videre til neste
			if(!empty($_GET['geoId']) && ($_GET['geoId'] != $sted->geonameId)) {
				continue;
			} 
		
			$landekode = $sted->countryCode;
		
			// Hent mer info om landet
			$landedata = json_decode(file_get_contents("http://ws.geonames.org/countryInfo?country=$landekode&lang=nb&type=json"));
	
			// Hent ut landedataene ($landedata->geonames[0]) og lagre dem som placeInfo i $land
			$sted->placeInfo = $landedata->geonames[0];
			
			// Utvid språk til fulle språknavn og lagre dem som et array i placeInfo->languages_long for dette landet
			$langs = explode(',', $sted->placeInfo->languages);
			foreach ($langs as $lang) {
				if ($langlang = get_lang($lang)) {
					$sted->placeInfo->languages_long[] = $langlang;
				}
			}
			
			// Hent info om hovedstad
			$hovedstad = $sted->placeInfo->capital;
			$hovedstad_data = json_decode(file_get_contents("http://ws.geonames.org/search?q=$hovedstad&maxRows=1&featureCode=PPLC&lang=nb&type=json&style=MEDIUM"));
			// Sjekk at vi fikk akkurat ett treff
			if ($hovedstad_data->totalResultsCount == 1) {
				$sted->placeInfo->capital_long = $hovedstad_data->geonames[0];
			}
			
			// Legg til dette landet i arrayet
			$data[] = $sted;
			
		} 
	
	} else {
	
		$data = 0;	
		
	}
		
}

output($data);

/* FUNKSJONER */

function output($d) {

	if (!empty($_GET['debug'])) {
		echo("<pre>");
		print_r($d);
		echo("</pre>");
	} else {
		print(json_encode($d));
	}
	
}

/*

Sted: 
http://ws.geonames.org/search?name=g%C3%B6teborg&maxRows=10&featureClass=P&lang=nb&style=MEDIUM

?place=london

{"result":2,"links":[
	{"url":"geoId=6058560&place=london&country=Canada&bib=","place":"London, Canada"},
	{"url":"geoId=2643743&place=london&country=United Kingdom&bib=","place":"London, United Kingdom"}
],"debug":"place=london"}

?place=frankrike

{"result":1,
"geoId":"2988507",
"placeInfo":{
	"lat":"48.85341",
	"lon":"2.3488",
	"timeZone":"Europe\/Paris",
	"place":"Paris",
	"lang":["Fransk","Bretonsk","Korsikansk","Katalansk","Baskisk"],
	"country":"France"
},
"localTime":
"Fredag 18. September 14:49 CEST",
"debug":"place=frankrike"}

?place=københavn

{
"result":1,
"geoId":"2618425",
"placeInfo":{
	"lat":"55.6776812020993",
	"lon":"12.5709342956543",
	"timeZone":"Europe\/Copenhagen",
	"place":"Copenhagen",
	"lang":["Dansk","Engelsk","F\u00e6r\u00f8ysk","Tysk"],
	"country":"Denmark"
},
"localTime":"Fredag 18. September 14:31 CEST",
"debug":"place=k%C3%B8benhavn"}

?geoId=2618425

{
"result":1,
"geoId":"2618425",
"placeInfo":{
	"lat":"55.6776812020993",
	"lon":"12.5709342956543",
	"timeZone":"Europe\/Copenhagen",
	"place":"Copenhagen",
	"lang":["Dansk","Engelsk","F\u00e6r\u00f8ysk","Tysk"],
	"country":"Denmark"
},
"localTime":"Fredag 18. September 14:36 CEST",
"debug":"geoId=2618425"}

*/


//geoId er ikke satt, place er satt
if (empty($_GET['geoId']) && isset($_GET['place'])) {

	

//geoId er satt
} else if(isset($_GET['geoId'])) {

}

function get_lang($kode) {

$lang = array(
	'aa' => 'Afar', 
	'ab' => 'Abkhasisk',
	'ae' => 'Avestisk',
	'af' => 'Afrikaans',
	'ak' => 'Akan',
	'am' => 'Amharisk',
	'an' => 'Aragonesisk',
	'ar' => 'Arabisk',
	'as' => 'Assamesisk',
	'av' => 'Avarisk',
	'ay' => 'Aymara',
	'az' => 'Aserbajdsjansk',
	'ba' => 'Basjkirsk',
	'be' => 'Hviterussisk',
	'bg' => 'Bulgarsk',
	'bh' => 'Bihari',
	'bi' => 'Bislama',
	'bm' => 'Bambara',
	'bn' => 'Bengali',
	'bo' => 'Tibetansk',
	'br' => 'Bretonsk',
	'bs' => 'Bosnisk',
	'ca' => 'Katalansk',
	'ce' => 'Tsjetsjensk',
	'ch' => 'Chamorro',
	'co' => 'Korsikansk',
	'cr' => 'Cree',
	'cs' => 'Tsjekkisk',
	'cu' => 'Kirkeslavisk',
	'cv' => 'Tsjuvansk',
	'cy' => 'Walisisk',
	'da' => 'Dansk',
	'de' => 'Tysk',
	'dv' => 'Dhivehi',
	'dz' => 'Dzongkha',
	'ee' => 'Ewe',
	'el' => 'Gresk',
	'en' => 'Engelsk',
	'eo' => 'Esperanto',
	'es' => 'Spansk',
	'et' => 'Estisk',
	'eu' => 'Baskisk',
	'fa' => 'Persisk',
	'ff' => 'Fulfulde',
	'fi' => 'Finsk',
	'fj' => 'Fijisk',
	'fo' => 'Færøysk',
	'fr' => 'Fransk',
	'fy' => 'Frisisk',
	'ga' => 'Irsk',
	'gd' => 'Skotsk gælisk',
	'gl' => 'Galisisk',
	'gn' => 'Guaraní',
	'gu' => 'Gujarati',
	'gv' => 'Mansk',
	'ha' => 'Hausa',
	'he' => 'Hebraisk',
	'hi' => 'Hindi',
	'ho' => 'Hiri motu',
	'hr' => 'Kroatisk',
	'ht' => 'Haitisk kreolsk',
	'hu' => 'Ungarsk',
	'hy' => 'Armensk',
	'hz' => 'Herero',
	'ia' => 'Interlingua',
	'id' => 'Indonesisk',
	'ie' => 'Interlingue',
	'ig' => 'Ibo',
	'ii' => 'Yi',
	'ik' => 'Inupiak',
	'io' => 'Ido',
	'is' => 'Islandsk',
	'it' => 'Italiensk',
	'iu' => 'Inuittisk',
	'ja' => 'Japansk',
	'jv' => 'Javanesisk',
	'ka' => 'Georgisk',
	'kg' => 'Kongolesisk',
	'ki' => 'Gikuyu',
	'kj' => 'Kwanyama',
	'kk' => 'Kasakhisk',
	'kl' => 'Kalaallisut',
	'km' => 'Khmer',
	'kn' => 'Kannada',
	'ko' => 'Koreansk',
	'kr' => 'Kanuri',
	'ks' => 'Kashmiri',
	'ku' => 'Kurdisk',
	'kv' => 'Komi',
	'kw' => 'Kornisk',
	'ky' => 'Kirgisisk',
	'la' => 'Latin',
	'lb' => 'Luxembourgsk',
	'lg' => 'Luganda',
	'li' => 'Limburgisk',
	'ln' => 'Lingala',
	'lo' => 'Laotisk',
	'lt' => 'Litauisk',
	'lu' => 'Luba-Katanga',
	'lv' => 'Latvisk',
	'mg' => 'Gassisk',
	'mh' => 'Marshallesisk',
	'mi' => 'Maoriski',
	'mk' => 'Makedonsk',
	'ml' => 'Malayalam',
	'mn' => 'Mongolsk',
	'mo' => 'Moldovsk',
	'mr' => 'Marathi',
	'ms' => 'Malayisk',
	'mt' => 'Maltesisk',
	'my' => 'Burmesisk',
	'na' => 'Naurisk',
	'nb' => 'Bokmål',
	'nd' => 'Nord-ndebele',
	'ne' => 'Nepali',
	'ng' => 'Ndonga',
	'nl' => 'Nederlandsk',
	'nn' => 'Nynorsk',
	'no' => 'Norsk',
	'nr' => 'Sør-ndebele',
	'nv' => 'Navajo',
	'ny' => 'Chichewa',
	'oc' => 'Oksitansk',
	'oj' => 'Ojibwa',
	'om' => 'Oromo',
	'or' => 'Oriya',
	'os' => 'Ossetisk',
	'pa' => 'Punjabi',
	'pi' => 'Pali',
	'pl' => 'Polsk',
	'ps' => 'Pashto',
	'pt' => 'Portugisisk',
	'qu' => 'Quechua',
	'rm' => 'Retoromansk',
	'rn' => 'Kirundi',
	'ro' => 'Rumensk',
	'ru' => 'Russisk',
	'rw' => 'Kinyarwanda',
	'sa' => 'Sanskrit',
	'sc' => 'Sardisk',
	'sd' => 'Sindhi',
	'se' => 'Nordsamisk',
	'sg' => 'Sango',
	'sh' => 'Serbokroatisk',
	'si' => 'Singalesisk',
	'sk' => 'Slovakisk',
	'sl' => 'Slovensk',
	'sm' => 'Samoansk',
	'sn' => 'Shona',
	'so' => 'Somalisk',
	'sq' => 'Albansk',
	'sr' => 'Serbisk',
	'ss' => 'Swati',
	'st' => 'Sesotho',
	'su' => 'Sundanesisk',
	'sv' => 'Svensk',
	'sw' => 'Swahili',
	'ta' => 'Tamilsk',
	'te' => 'Telugu',
	'tg' => 'Tadsjikisk',
	'th' => 'Thai',
	'ti' => 'Tigrinya',
	'tk' => 'Turkmensk',
	'tl' => 'Tagalog',
	'tn' => 'Tswana',
	'to' => 'Tonganesisk',
	'tr' => 'Tyrkisk',
	'ts' => 'Tsonga',
	'tt' => 'Tatarsk',
	'tw' => 'Twi',
	'ty' => 'Tahitisk',
	'ug' => 'Uighur',
	'uk' => 'Ukrainsk',
	'ur' => 'Urdu',
	'uz' => 'Usbekisk',
	've' => 'Venda',
	'vi' => 'Vietnamesisk',
	'vo' => 'Volapük',
	'wa' => 'Vallonsk',
	'wo' => 'Wolof',
	'xh' => 'Xhosa',
	'yi' => 'Jiddisk',
	'yo' => 'Yoruba',
	'za' => 'Zhuang',
	'zh' => 'Kinesisk',
	'zu' => 'Zulu'
);

	if ($lang[$kode]) {
		return $lang[$kode];
	} else {
		return false;	
	}
	
}

?>