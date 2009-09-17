<?php
//forteller PHP at feilmeldinger skal vises
ini_set('display_errors', 1);
error_reporting(E_ALL|E_STRICT);

// Inkluderer konfigurasjonen - herfra kommer arrayet $config 
require_once('config.php');

//inkluderer funksjoner
require_once 'include/functions.php';

//lagrer antall sekunder siden 1. januar 1970
$time = microtime();
$time = explode(' ', $time);
$start = $time[1] + $time[0];

//velger hva som skal inn i head-taggen
$header_extras = array("<link rel=\"stylesheet\" type=\"text/css\" href=\"css/mashup2.css\" />", 
					   "<script type=\"text/javascript\" src=\"scripts/bsn.AutoSuggest_2.1.3_comp.js\" charset=\"utf-8\"></script>",
					   "<link rel=\"stylesheet\" href=\"css/autosuggest_inquisitor.css\" type=\"text/css\" media=\"screen\" charset=\"utf-8\" />", 
					   "<link rel=\"stylesheet\" href=\"css/jquery.easywidgets.css\" type=\"text/css\" media=\"screen\" />", 
					   "<script src=\"http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAA4bkAB4c8qRFagE_bUojf3hS-l405Vpwg0XB2Ibm6AlGOmZ6JbhTiF85MU3mn8joSdlDb19Mam3gFSw&sensor=false\" type=\"text/javascript\"></script>", 
						"<script type=\"text/javascript\" src=\"scripts/googlemaps.js\"></script>");
						
$header_extras[] = '<script src="http://www.google.com/jsapi"></script>
<script>
  // Load jQuery
  google.load("jquery", "1.3.2");
  google.load("jqueryui", "1.7.2");
  // Load language API
  google.load("language", "1"); 
</script>
<script src="scripts/jquery.easywidgets.min.js" type="text/javascript"></script>
<script src="scripts/bokser.js" type="text/javascript"></script>
<script type="text/javascript" src="http://jqueryui.com/latest/ui/ui.accordion.js"></script>
  <script type="text/javascript">
  $(document).ready(function(){
    $(".trekkspill").accordion({ autoHeight: false, active: false });
  });
  </script>
';

//forteller hvilken filtype (MIME) og tegnsett som skal brukes
header('Content-Type: text/html; Extension: xhtml; charset=utf-8');

//skriver header delen
writeHeader($config['app_title'], $header_extras);
?>
		<div id="content">
			<div id="menu">
				<!-- MENYLINKER -->
				<a class="menulinks" href="reiseplanlegger.php">Hjem</a>
				<a class="menulinks" href="?about">Om</a>
			</div>
			<div id="header">
<?php

$type = "";
if (isset($_GET['bib'])) {
	if (!empty($config['libraries'][$_GET['bib']]['sru'])) {
		$type = 'sru';
	} else {
		$type = 'z39.50';
	}
}
$place = "";
if (isset($_GET['place'])) {
  $place = $_GET['place'];
}
writeSearchForm($_GET['bib'], $place);

?>
			</div>
			<div id="main">
				<div id="left-col">
<?php
$search_time = "";
/*
--------------- INFO OM BOK ---------------
tittelnummer er satt, altså vil man ha info om en bok
*/
if (isset($_GET['about']))
{
?>
					<h3>Velkommen til reiseplanleggeren!</h3>
					<p>
						Herfra kan du søke på spennende reisemål rundt om i hele verden. Da vil du
						få tilgang til informasjon om reisehåndbøker fra Deichmanske biblioteks
						fantastiske samling. I tillegg vil du kunne få ekstra opplysninger knyttet
						til ditt reisemål som lokal tid, værdata og kart.<br />
						<br />
						NB! Nedtrekksboksen ved siden av søkefeltet er til for å kunne velge mellom
						ulike måter å hente katalogdata fra Deichmanske bibliotek på. Vi anbefaler å
						benytte Z39.50 eller SRU da disse søkene er raskest.
					</p>
<?php
}
if (isset($_GET['tittelnr']))
{
	echo "<h3>Reisehåndbok:</h3>\n";
	//lagrer tittelnummer
	$tnr = $_GET['tittelnr'];
	//lagrer søketype (Z39.50, SRU)
	$type = $_GET['type'];
	$place = "";
	
	if(isset($_GET['place']))
		$place = $_GET['place'];
	
	//hvis søketype er Z39.50
	if($type=="z39.50")
	{
		//lagrer sti til XSL
		$xsl_url = 'xsl/bok.xsl';
		
		//lagrer XML-data som streng
		$xml_data = get_ccl_results_as_xml("tnr=$tnr") or exit("Feil");
		
		//lagrer array med parametere til XSLT
		$params = array(array('namespace' => '', 'name' => 'url_ext', 'value' => '&type='.$type));
		
		//gjennomfører transformasjon og skriver ut resultatet
		echo transformToHTML($xml_data, $xsl_url, $params);
	}
	//hvis søketype er SRU
	else if($type=="sru")
	{
		//lagrer sti til XSL
		$xsl_url = 'xsl/boksru.xsl';
		//bygger opp URL til KOHA-server
		$xml_url = getSRUURL("rec.id=$tnr");
		
		//henter XML-data
		$xml_data = file_get_contents($xml_url) or exit("Feil");
		
		//Replacer <record xmlns....> med <record>, fungerer ikke med namespace
		$xml_data = str_replace("<record xmlns=\"http://www.loc.gov/MARC21/slim\">", "<record>", $xml_data);
		
		//lagrer parametere til XSLT
		$params = array(array('namespace' => '', 'name' => 'url_ext', 'value' => '&type='.$type), 
		                array('namespace' => '', 'name' => 'target', 'value' => 'local'));
		
		//gjennomfører trans. og skriver ut
		echo transformToHTML($xml_data, $xsl_url, $params);
	}
	//ingen av typene... noe er feil
	else
	{
		echo "Noe er feil...<br />\n";
	}
}
/*
--------------- SØKERESULTAT ---------------
place er satt, altså vil man se reisebøker for dette stedet
*/
else if (isset($_GET['place']))
{
	//lagrer script-variablene
	$place = $_GET['place'];
	$order = $_GET['order'];
	$sortBy = $_GET['sortBy'];
	
	/*
	hvis ikke $order er satt til stigende eller synkende
	blir den satt til stigende
	*/
	if ($order!='ascending'&&$order!='descending')
	{
		$order = 'ascending';
	}
	/*
	hvis ikke $sortBy er satt til tittel eller år
	blir den satt til tittel
	*/
	if ($sortBy!='title'&&$sortBy!='year')
	{
		$sortBy = 'title';
	}
	//bruker har ikke skrevet noe
	if (empty($place))
	{
		echo "<h3>Søkeord ikke oppgitt...</h3>\n";
	}
	else
	{
		//lagrer starttiden for søket
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$search_start = $time;

		
		// $type = $_GET['type'];
		$geoId = "";
		$geoId2 = "";
		if(isset($_GET['geoId']))
		{
			$geoId = "&geoId=".$_GET['geoId'];
			$geoId2 = $_GET['geoId'];
		}
		
		echo "\t<h3>Søkeresultat:</h3>\n";
		

		if (empty($geoId2)) 
		{
			writeSortingForm($place, $type, $sortBy, $order);
		}
		else
		{
			writeSortingForm($place, $type, $sortBy, $order, $geoId2);
		}
		
		//hvis type er z39.50, man vil søke med z39.50
		if($type=="z39.50")
		{
			//oppretter ccl-søkestreng
			$ccl = getCcl($place, "dewey/dewey_list.txt", 'z39.50');
		
			//sti til XSL
			$xsl_url = 'xsl/bokliste.xsl';

			//oppretter DOM-dok med XML-data
			$xml = new DOMDocument;
			$xml->loadXML(get_ccl_results_as_xml($ccl));

			//teller antallet <record>-noder (antall søketreff)
			$nodeList = $xml->getElementsByTagName('record');
			$hits = $nodeList->length;
			
			//ingen treff
			if ($hits==0)
			{
				echo "Ingen reisehåndbøker funnet...\n";
			}
			//treff, XML blir transformert og skrevet ut
			else
			{
				echo "<p>Antall treff: $hits</p>\n";
				
				$params = array(array('namespace' => '', 'name' => 'url_ext', 'value' => "$geoId&place=$place&type=".$type),
							array('namespace' => '', 'name' => 'sortBy', 'value' => $sortBy),
							array('namespace' => '', 'name' => 'order', 'value' => $order), 
							array('namespace' => '', 'name' => 'target', 'value' => "local"));
		
				echo transformToHTML($xml, $xsl_url, $params);
			}
		}
		//type er SRU
		else if($type=="sru")
		{
			//oppretter cql-spørresetning
			$cql = getCql($place, "dewey/dewey_list.txt");
			//oppretter URL til KOHA med cql
			$xml_url = getSRUURL($cql);
			//sti til XSL
			$xsl_url = 'xsl/boklistesru.xsl';

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
			
			//parametere til XSL
			$params = array(array('namespace' => '', 'name' => 'url_ext', 'value' => "$geoId&place=$place&type=".$type),
							array('namespace' => '', 'name' => 'sortBy', 'value' => $sortBy),
							array('namespace' => '', 'name' => 'order', 'value' => $order), 
							array('namespace' => '', 'name' => 'target', 'value' => "local"));
		
			//transformerer til HTML
			echo transformToHTML($xml, $xsl_url, $params);
		}
		else
		{
			echo "Something is wrong\n";
		}
		//lagrer tid etter søk, differanse er tid løpt
		$time = microtime();
		$time = explode(' ', $time);
		$finish = $time[1] + $time[0];
		$searchTime = round(($finish - $search_start), 4);
		$search_time = "Søket tok $searchTime sekunder.";
	}
}
/*
--------------- SØKEORD IKKE OPPGITT ---------------
*/
else if (!isset($_GET['about']))
{
?>
					<h3>Velkommen til reiseplanleggeren!</h3>
					<p>
						Herfra kan du søke på spennende reisemål rundt om i hele verden. Da vil du
						få tilgang til informasjon om reisehåndbøker fra Deichmanske biblioteks
						fantastiske samling. I tillegg vil du kunne få ekstra opplysninger knyttet
						til ditt reisemål som lokal tid, værdata og kart.<br />
						<br />
						God reise!<br />
					</p>
<?php
}


?>
				</div>
				<div id="right-col" class="widget-place">
				<div class="right-col-box" id="place">
                    <div class="widget-content"><img src="images/widgets/loading.gif" alt="Henter data..." /></div>
				</div>
<?php

foreach ($config['modules'] as $key => $mod) {

  if ($mod['enabled']) {

    echo('<div class="widget movable collapsable right-col-box" id="widget_' . $key . '">');
    echo('	<div class="widget-header">' . $mod['title'] . '</div>');
    echo('  <div class="widget-content"><img src="images/widgets/loading.gif" alt="Henter data..." /></div>');
    echo('</div>');
  
  }				

}

?>
				</div>
								
				<div id="footer">
					<div id="left-footer">
<?php
//lagrer slutttid for hele siden
$time = microtime();
$time = explode(' ', $time);
$finish = $time[1] + $time[0];
$total_time = round(($finish - $start), 4);
//skriver ut tidtaking
echo "\t\t\t\t\t<p>Siden lastet på $total_time sekunder. $search_time</p>\n";
?>
					</div>
					<div id="right-footer">
					</div>
				</div>
			</div>
		</div>
<script type="text/javascript">
//<![CDATA[
	var options = {
		script:"autosuggest/autosuggest.php?json=true&limit=10&info=true&",
		varname:"input",
		json:true,
		shownoresults:false,
		maxresults:10
	};
	var as_json = new bsn.AutoSuggest('autosuggest', options);
//]]>
</script>
	</body>
</html>
<?php

/*
Skriver benchmark-data til fil
if (isset($_GET['place'])&&!isset($_GET['tittelnr']))
{
  $place = $_GET['place'];
  $type = $_GET['type'];
  
  date_default_timezone_set('Europe/Oslo');
  $timeStamp = date('d.m.y H:i:s');
  
  $benchmark = "$type\t$place\t$hitsToFile\t$mashupTimeToFile\t$searchTimeToFile\t$timeStamp\n";
  file_put_contents($benchmarkUrl, $benchmark, FILE_APPEND) or exit("Feil");
}
*/

function get_ccl_results_as_xml($ccl) {

	/*
	henter funksjonene i catalog.php, catalog.php inneholder
	funksjoner for å hente ut katalogdata fra z39.50-servere
	*/
	require_once 'include/catalog.php';
	
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
		$fetch = yazCclArray($ccl, 'normarc');
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
	
	// DEBUG echo($out);
	
	return $out;
	
}

?>
