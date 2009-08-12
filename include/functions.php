<?php
ini_set('display_errors', 1);
error_reporting(E_ALL|E_STRICT);

/*
funksjon som skriver ut en søkelinje med tre valg, parameteren
angir hvilket element i nedtrekkslisten som skal være forhånds-
valgt
*/
function writeSearchForm($selected='z39.50', $text='')
{
	//array med valg, nøkkel blir 'value', verdi blir listevalg
	$options = array('z39.50' => 'Z39.50', 'sru' => 'SRU', 'rss' => 'RSS');
?>
<form method="get" action="">
	<p>
		<label for="autosuggest">Sted/land</label> 
		<input id="autosuggest" type="text" size="50" name="place" value="<?php echo($text); ?>" />
		<select name="type">
<?php
//skriver nedtrekksliste
foreach ($options as $a => $b)
{
	if ($selected==$a)
		echo "\t\t\t<option selected=\"selected\" value=\"$a\">$b</option>\n";
	else
		echo "\t\t\t<option value=\"$a\">$b</option>\n";
}
?>
		</select>
		<input type="submit" value="Søk" />
		<input type="hidden" name="sortBy" value="title" />
		<input type="hidden" name="order" value="ascending" />
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

/*
funksjon som bygger opp URL til RSS-strøm. tar tre argumenter $ccl,
$number og $format. $ccl er ccl-søkestrengen, $number er maks antall
verker i RSS-strømmen, $format angir hvilket format man ønsker. vi
bruker for det meste format 11 som er vanlig tekst. format 5 er marc
i xml
*/
function getRSSURL()
{
	$args = func_get_args();

	if (!$args[1]) { $args[1] = "100"; }
	if (!$args[2]) { $args[2] = "11"; }
	
	$ccl 		=& $args[0];
	$number		=& $args[1];
	$format		=& $args[2];
	
	$url = trim("http://www.deich.folkebibl.no/cgi-bin/rss?websok=websok&amp;format=$format&amp;antall=$number&amp;ccl=$ccl");

	return $url;
}

/*
funksjon som henter bokdata gjennom RSS
*/
function getRSS($url, $params, $bookInfo, $php_file)
{
	/*
	bruker CURL for å hente data gjennom RSS for å unngå timeout
	*/
	//create curl resource
	$ch = curl_init();

	//set url
	curl_setopt($ch, CURLOPT_URL, $url);

	//return the transfer as a string
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	//$output contains the output string
	$output = curl_exec($ch);

	//close curl resource to free up system resources
	curl_close($ch);
	
	//Bruker simplexml til å lese RSS-XML'en
	$rss = simplexml_load_string($output);
	
	$count = 0;
	$result = array();
	
	//Går gjennom XML'en og henter ut informasjonen som ligger i item noden
	foreach ($rss->channel->item as $item)
	{
		//Henter ut informasjonen som ligger i link noden
		$link = $item->link;
		/*
		Henter ut nummeret som Deichmanske bruker som id-nummer for hver bok. 
		Dette brukes til å finne ut hvilken bok som blir trykket på, og derfor
		hvilken bok det skal hentes ut informasjon om.
		*/
		$tnr = substr($link, strpos($link, "tnr=")+4, mb_strlen($link));
		
		if(!empty($bookInfo))
		{
			//Sjekker om id-nummeret som ble sendt med som parameter, stemmer med tnr
			if ((int)$bookInfo==(int)$tnr)
			{	
				//Skriver ut informasjon
				$result[] = "<strong>$item->title</strong>";
				$result[] = $item->description;
				$result[] = "<br /><a href=\"$link\">Link til Deichmanske bibliotek</a>";
				break;
			}
		}
		else
		{
			//Lager linkene med parametere som skal sendes med
			$result[$count] = "<a href=\"$php_file?$params&amp;tittelnr=$tnr\">$item->title</a><br />\n";
			$count++;
		}
	}

	//Skriver ut antall treff og listen med linker
	$return = array("result" => $result,
					"count" => $count);
					
	return $return;
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
funksjon som lager et skjema, som tar et søkeord som parameter
*/
function sok_old()
{
?>
		<h2>Testsøk mot RSS-strømmer fra Deichmanske</h2>
		<div>
			<form action="rss.php" method="get">
				<p>
					<input type="text" name="sok" />
					<input type="submit" value="Søk" />
					Antall:
					<select name="antall">
						<option value="10">10</option>
						<option value="100" selected="selected">100</option>
						<option value="1000">1000</option>
						<option value="10000">10000</option>
					</select>
				</p>
			</form>
		</div>
<?php
}

/*
funksjon som bygger opp ccl-søkestrenger til reiseplanlegger
*/
function getCcl($query, $path, $type)
{
	//Prøver og åpne filen
	$fil = file_get_contents($path) or exit("Kunne ikke hente fil... ".$path);
	//Lager et standardsøk, hvis vi ikke finner et land som stemmer med det i filen, blir denne brukt
	if ($type!='rss')	
		$ccl = "(ke=914*04 or ke=915*04 or ke=916*04 or ke=917*04 or ke=918*04 or ke=919*04) and ti=".preg_replace('/\s+/', '+', $query);
	else
		$ccl = "(914*04/ke or 915*04/ke or 916*04/ke or 917*04/ke or 918*04/ke or 919*04/ke) and ti=".preg_replace('/\s+/', '+', $query);
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
			if ($type!='rss')
				$ccl .= "ke=".preg_replace('/\s+/', '', $data[1]);
			else
				$ccl .= preg_replace('/\s+/', '', $data[1])."/ke";
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
	//Prøver og åpner filen
	$fil = file_get_contents($path) or exit("Kunne ikke hente fil... ".$path);
	//Lager et standardsøk, hvis vi ikke finner et land som stemmer med det i filen, blir denne brukt
	$cql = "(pode.dewey=914*04 or pode.dewey=915*04 or pode.dewey=916*04 or pode.dewey=917*04 or pode.dewey=918*04 or pode.dewey=919*04) and dc.title=".preg_replace('/\s+/', '+', $query);
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
	return urlencode($cql);
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
        return "http://torfeus.deich.folkebibl.no:9999/biblios?operation=$operation&version=$version&query=$query&recordSchema=$recordSchema&startRecord=$startRecord&maximumRecords=$maximumRecords";
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
?>
