<?php
ini_set('display_errors', 1);
error_reporting(E_ALL|E_STRICT);

/*
Funksjoner som trengs for å søke i katalogen med Z39.50 eller SRU. 
*/

function z_search($q, $limit = 20, $start = 1, $order = 'descending', $sortBy = 'year', $showAuthor = false) {
	
	//sti til XSL
	$xsl_url = '../xsl/bokliste.xsl';
	
	//oppretter DOM-dok med XML-data
	$xml = new DOMDocument;
	$xml->loadXML(get_ccl_results_as_xml($q, $limit));
	
	//teller antallet <record>-noder (antall søketreff)
	$nodeList = $xml->getElementsByTagName('record');
	$hits = $nodeList->length;
	
	//ingen treff
	if ($hits==0) 
	{
		return false;
	}
	//treff, XML blir transformert og skrevet ut
	else
	{
		// echo "<p>Antall treff: $hits</p>\n";
		
		$params = array(array('namespace' => '', 'name' => 'url_ext', 'value' => "type=z39.50"), // TODO: Brukes denne? 
					    array('namespace' => '', 'name' => 'sortBy',  'value' => $sortBy),
					    array('namespace' => '', 'name' => 'order',   'value' => $order), 
					    array('namespace' => '', 'name' => 'target',  'value' => "remote"), 
					    array('namespace' => '', 'name' => 'visForfatter',  'value' => $showAuthor)); 
	
		return transformToHTML($xml, $xsl_url, $params);
	}
	
}

function sru_search($q, $limit = 20, $start = 1, $order = 'descending', $sortBy = 'year', $showAuthor = false) {
		
	global $config;
		
	//oppretter URL til KOHA med cql
	$xml_url = getSRUURL($q, $start, $limit);
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
	
	if ($hits > 0) {
	
	//parametere til XSL
	$params = array(array('namespace' => '', 'name' => 'url_ext', 'value' => "type=sru"), // TODO: Brukes denne? 
					array('namespace' => '', 'name' => 'sortBy',  'value' => $sortBy),
					array('namespace' => '', 'name' => 'order',   'value' => $order), 
					array('namespace' => '', 'name' => 'target',  'value' => "remote"),
					array('namespace' => '', 'name' => 'visForfatter',  'value' => $showAuthor),  
					array('namespace' => '', 'name' => 'showHits',  'value' => "false"), 
					array('namespace' => '', 'name' => 'item_url', 'value' => $config['libraries'][$_GET['bib']]['item_url']));
	
	//transformerer til HTML
	return transformToHTML($xml, $xsl_url, $params);

	} else {
	
		return false;
		
	}

}

/*
funksjon som returnerer katalogdata, parametere er ccl-søkestreng,
syntax(normarc, usmarc, marc21), returformat(string, xml), om det
skal legges til en ny root-node i XML-strukturen og
z39.50-server-url:portnummer/database
*/
function yazCclSearch()
{
	
	global $config;
	
	//henter den konfigurasjonen for Z39.50
	$zconfig = get_config();
	
	//$GLOBALS['fields'];
	
	//funksjonen kan ha maks 5 parametere
	$args = func_get_args();
	
	if (!$args[1]) { $args[1] = "normarc"; }
	if (!$args[2]) { $args[2] = "xml"; }
	if (!$args[3]) { $args[3] = true; }
	if (!$args[4]) { $args[4] = $config['libraries'][$_GET['bib']]['z3950']; }
	
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
	yaz_ccl_conf($id, $zconfig);
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
function yazCclArray($ccl, $syntax = 'marc21', $limit = 20, $host = 'default')
{
	
	global $config;
	
	if ($host == 'default') {
		$host = $config['libraries'][$_GET['bib']]['z3950'];
	}
	
	$zconfig = get_config();
	$hits = 0;
	
	$type = 'xml';
		
	$id = yaz_connect($host);
	yaz_element($id, "F");
	yaz_syntax($id, $syntax);
	yaz_range($id, 1, 1);
	
	yaz_ccl_conf($id, $zconfig);
	$cclresult = array();
	if (!yaz_ccl_parse($id, $ccl, $cclresult))
	{
		echo 'Error: '.$cclresult["errorstring"];
	}
	else
	{
		// NB! Ser ikke ut som Z39.50 fra Bibliofil støtter "sort"
		// Se nederst her: http://www.bibsyst.no/produkter/bibliofil/z3950.php
		// PHP/YAZ-funksjonen yaz-sort ville kunne dratt nytte av dette: 
		// http://no.php.net/manual/en/function.yaz-sort.php
		// Sort Flags
		// a Sort ascending
		// d Sort descending
		// i Case insensitive sorting
		// s Case sensitive sorting
		// Bib1-attributter man kunne sortert på: 
		// http://www.bibsyst.no/produkter/bibliofil/z/carl.xml
		// yaz_sort($id, "1=31 di");
		$rpn = $cclresult["rpn"];
		yaz_search($id, "rpn", utf8_decode($rpn));
	}
	
	yaz_wait();

	$error = yaz_error($id);
	if (!empty($error))
	{
		echo "Error yazCclArray: $error";
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
		if ($p == $limit) {
		  break;
		}
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

/*
funksjon som skriver ut en søkelinje med tre valg, parameteren
angir hvilket element i nedtrekkslisten som skal være forhånds-
valgt
*/
function writeSearchForm($selected, $text='')
{

	global $config;

?>
<form method="get" action="">
	<p>
		<label for="autosuggest">Sted/land</label> 
		<input id="autosuggest" type="text" size="50" name="place" value="<?php echo($text); ?>" />
		<select name="bib">
<?php
//skriver nedtrekksliste
foreach ($config['libraries'] as $key => $value)
{
	if ($selected==$key)
		echo "\t\t\t<option selected=\"selected\" value=\"$key\">{$value['title']}</option>\n";
	else
		echo "\t\t\t<option value=\"$key\">{$value['title']}</option>\n";
}
?>
		</select>
		<input type="submit" value="Søk" />
		<input type="hidden" name="sortBy" value="year" />
		<input type="hidden" name="order" value="descending" />
	</p>
</form>
<?php
}

/*
funksjon som skriver sorteringsskjema
*/
function writeSortingForm($place, $type, $selected_sortBy, $selected_order, $geoId=null)
{
	$options_sortBy = array('title' => 'tittel', 'year' => 'utgivelsesår');
	$options_order = array('ascending' => 'stigende', 'descending' => 'synkende');
?>
<form method="get" action="">
	<p>
<?php
echo "\t\t<input type=\"hidden\" name=\"place\" value=\"$place\" />\n";
echo "\t\t<input type=\"hidden\" name=\"type\" value=\"$type\" />\n";
if (!empty($geoId))
	echo "\t\t<input type=\"hidden\" name=\"geoId\" value=\"$geoId\" />\n";
?>
		<label for="sort">Sorter på</label>
		<select id="sort" name="sortBy">
<?php
foreach ($options_sortBy as $a => $b)
{
	if ($selected_sortBy==$a)
		echo "\t\t\t<option selected=\"selected\" value=\"$a\">$b</option>\n";
	else
		echo "\t\t\t<option value=\"$a\">$b</option>\n";
}
?>
		</select>
		<select name="order">
<?php
foreach ($options_order as $a => $b)
{
	if ($selected_order==$a)
		echo "\t\t\t<option selected=\"selected\" value=\"$a\">$b</option>\n";
	else
		echo "\t\t\t<option value=\"$a\">$b</option>\n";
}
?>
		</select>
		<input type="submit" value="Sorter" />
	</p>
</form>
<?php
}

/*
funksjon som skriver et enkelt søkefelt
*/
function searchField()
{
?>
	<form method='get'>
		Sted:<br />
		<input type='text' size='50' name='place' />
		<input type='submit' value='Søk' />
	</form>
<?php
}


/* Diverse geo-realterte funksjoner flyttet til getgeo.php */

/* Flyttet getWeather til mod.weather.php */

/*
funksjon som skriver kart fra Google Maps. parameteren $lat og $lon
angir lengde- og breddegrad kartet skal vise. $width og $height
forteller hva dimensjonene på kartet skal være. $zoom angir hvilket
zoom-nivå kartet skal ha i utgangspunktet
*/
function getMap($lat, $lon, $width, $height, $zoom)
{

	echo "\t\t<script type=\"text/javascript\">
			map($lat,$lon,$width,$height,$zoom);	
		</script>\n";
		
?>
		<label for="informasjon">Informasjon</label>
		<input id="informasjon" type="checkbox" onclick="switchLayer(this.checked,wiki)" />
		<label for="bilder">Bilder</label>
		<input id="bilder" type="checkbox" onclick="switchLayer(this.checked,pano)" />
		<label for="filmer">Filmer</label>
		<input id="filmer" type="checkbox" onclick="switchLayer(this.checked,tube)" />
<?php
}

/*
funksjon som skriver toppen av HTML-dokumenter. disse dokumentene
kan da valideres som XHTML 1.0 Strict (forutsatt at man ikke skriver
noe andre steder i koden som ikke følger denne standarden)
*/
function writeHeader($title, $css_scripts=null)
{
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
	echo "<!DOCTYPE html
	PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"
	\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n";
	echo "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">
	<head>
		<title>".$title."</title>\n";
	if (!empty($css_scripts))
	{
		foreach ($css_scripts as $head_line)
		{
			echo "\t\t".$head_line."\n";
		}
	}
	echo "\t</head>
	<body>\n";
}

/*
funksjon som lister ut filer og underfiler til en gitt mappe
*/
function get_files($root_dir, $all_data=array())
{
	// only include files with these extensions
	$allow_extensions = array("php", "xml", "py", "js", "css", "xsl", "xslt", "html", "htm");
	// make any specific files you wish to be excluded
	$ignore_files = array("index.php");
	$ignore_regex = '/^_/';
	// skip these directories
	$ignore_dirs = array(".", "..", "pygments", "simplepie", "magpierss");

	// run through content of root directory
	$dir_content = scandir($root_dir);
	foreach ($dir_content as $key => $content)
	{
		$path = $root_dir.'/'.$content;
		if (is_file($path) && is_readable($path))
		{
			// skip ignored files
			if (!in_array($content, $ignore_files))
			{
				if (preg_match($ignore_regex,$content) == 0)
				{
					$content_chunks = explode(".",$content);
					$ext = $content_chunks[count($content_chunks) - 1];
					// only include files with desired extensions
					if (in_array($ext, $allow_extensions))
					{
						// save file name with path
						$all_data[] = $path;
					}
				}
			}
		}
		// if content is a directory and readable, add path and name
		elseif (is_dir($path) && is_readable($path))
		{
			// skip any ignored dirs
			if (!in_array($content, $ignore_dirs))
			{
				// recursive callback to open new directory
				$all_data = get_files($path, $all_data);
			}
		}
	} // end foreach
	return $all_data;
} // end get_files()

/*
Funksjoner som bygger opp søkestrenger 

getCclDewey og getCqlDewey er de opprinnelige funksjonene, utviklet av studentene. Disse er beholdt, med et 
tilegg av "Dewey" i navnet, for å gjøre det lett å bytte tilbake til disse funksjonene, dersom det skulle
vise seg å være ønskelig. De nye funksjonene getCcl og getCql har beholdt de samme argumentene som de 
gamle funksjonene, for at det ikke skal være nødvendig å endre på koden som kaller disse funksjonene. 
*/

/*
funksjon som bygger opp ccl-søkestrenger til reiseplanlegger
*/
function getCcl($query, $path, $type)
{
	return "eo=$query Reisehåndbøker";
}
function getCclDewey($query, $path, $type)
{
	//Prøver og åpne filen
	$fil = file_get_contents($path) or exit("Kunne ikke hente fil... ".$path);
	//Lager et standardsøk, hvis vi ikke finner et land som stemmer med det i filen, blir denne brukt
	$ccl = "(ke=914*04 or ke=915*04 or ke=916*04 or ke=917*04 or ke=918*04 or ke=919*04) and ti=".preg_replace('/\s+/', '+', $query);
	$funnet = false;
	
	$fil_array = explode("\n", $fil);
	
	//Går gjennom filen
	foreach ($fil_array as $linje)
	{
		//Splitter hver linje med :, og legger det i en array
		//Hver linje er bygd opp med bynavn og dewey-nummer slik:
		//London:914.2104
		$data = explode(":", $linje);
		$place = $data[0];
		
		$query = mb_convert_case($query, MB_CASE_LOWER, "UTF-8");
		$place = mb_convert_case($place, MB_CASE_LOWER, "UTF-8");

		//Sjekker om det man har søkt på stemmer med et stedsnavn i filen
		if ($place==$query)
		{
			//Sjekker om vi har funnet et stedsnavn som stemmer tidligere
			if(!$funnet)
				$ccl = '';
			else
				$ccl .= ' or ';
			//Hvis det stemmer, blir dewey-nummeret lagt i variabelen $ccl, istedenfor standardsøket
			$ccl .= "ke=".preg_replace('/\s+/', '', $data[1]);
			$funnet = true;
		}
	}
	return $ccl;
}

/*
funksjon som bygger opp cql-søkestrenger til reiseplanlegger
*/
function getCql($query, $path)
{
	return "dc.subject = $query and dc.subject = Reisehåndbøker";
}
function getCqlDewey($query, $path)
{
	//Prøver og åpner filen
	$fil = file_get_contents($path) or exit("Kunne ikke hente fil... ".$path);
	//Lager et standardsøk, hvis vi ikke finner et land som stemmer med det i filen, blir denne brukt
	$cql = "(pode.dewey=914*04 or pode.dewey=915*04 or pode.dewey=916*04 or pode.dewey=917*04 or pode.dewey=918*04 or pode.dewey=919*04) and dc.title=$query";
	$funnet = false;
	
	$fil_array = explode("\n", $fil);
	
	//Går gjennom filen
	foreach ($fil_array as $linje)
	{
		//Splitter hver linje med :, og legger det i en array
		//Hver linje er bygd opp med bynavn og dewey-nummer slik:
		//London:914.2104
		$data = explode(":", $linje);
		$place = $data[0];
		
		$query = mb_convert_case($query, MB_CASE_LOWER, "UTF-8");
		$place = mb_convert_case($place, MB_CASE_LOWER, "UTF-8");

		//Sjekker om det man har søkt på stemmer med et stedsnavn i filen
		if ($place==$query)
		{
			//Sjekker om vi har funnet et stedsnavn som stemmer tidligere
			if(!$funnet)
				$cql = '';
			else
				$cql .= ' or ';
			//Hvis det stemmer, blir dewey-nummeret lagt i variabelen $ccl, istedenfor standardsøket
			$cql .= "pode.dewey=".preg_replace('/\s+/', '', $data[1]);
			$funnet = true;
		}
	}
	return $cql;
}

/*
tar string med en query og lager en SRU-link
*/
function getSRUURL($query,
                $startRecord = "1",
                $maximumRecords = "1000",
                $operation = "searchRetrieve",
                $version = "1.2",
                $recordSchema = "marcxml")
{
	
	global $config;
	
	$query = urlencode($query);
        return "{$config['libraries'][$_GET['bib']]['sru']}?operation=$operation&version=$version&query=$query&recordSchema=$recordSchema&startRecord=$startRecord&maximumRecords=$maximumRecords";
}

/*
funksjon som utfører XSLT. $xml_data kan være ren XML i string-
format eller DOM-objekt, $xsl_path er stien til XSL-dokumentet,
$params er en array med parametere til XSLT
*/
function transformToHTML($xml_data, $xsl_path, $params)
{
	//hvis type er 'string' blir $xml_data lastet inn i et DOM-dok
	if (getType($xml_data)=='string')
	{
		//oppretter DOM-dokument og laster inn XML-data
		$xml = new DOMDocument;
		$xml->loadXML($xml_data);
	}
	else
		$xml =& $xml_data;

	//oppretter DOM-dokument og laster inn XSL-data
	$xsl = new DOMDocument;
	$xsl->load($xsl_path);

	//konfigurerer omformer
	$proc = new XSLTProcessor;
	//sender med parametere til XSL
	foreach ($params as $param)
	{
		$proc->setParameter($param['namespace'], $param['name'], $param['value']);
	}
	
	//legger til XSL
	$proc->importStyleSheet($xsl);

	//gjennomfører transformasjon (på en måte som gir XHTML Strict kode)
	$dom = $proc->transformToDoc($xml);
	//lagrer til HTML
	$html = $dom->saveXML();
	//splitter på nylinjetegn
	$tmp = explode("\n", $html);
	//lagrer ny array uten første element (fjerner XML-deklarasjon, den har vi allerede)
	$tmp = array_splice($tmp, 1);
	//returnerer ferdig transformert XML
	return implode("\n", $tmp);
}

function get_type($bib) {
	global $config;
	if (!empty($config['libraries'][$bib]['sru'])) {
		return 'sru';
	} else {
		return 'z39.50';
	}
}

function get_ccl_results_as_xml($ccl, $limit) {


	
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
