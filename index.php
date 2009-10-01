<?php

/*

Copyright 2009 Pode

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
				<a class="menulinks" href=".">Hjem</a>
				<a class="menulinks" href="?about">Om</a>
			</div>
			<div id="header">
<?php

$type = "";
$bib = "";
if (isset($_GET['bib'])) {
	$bib = $_GET['bib'];
	$type = get_type($_GET['bib']);
}
$place = "";
if (isset($_GET['place'])) {
  $place = $_GET['place'];
}
writeSearchForm($bib, $place);

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
						få tilgang til informasjon om reisehåndbøker fra ditt biblioteks
						fantastiske samling. I tillegg vil du kunne få ekstra opplysninger knyttet
						til ditt reisemål som lokal tid, værdata og kart.<br />
						<br />
						NB! Nedtrekksboksen ved siden av søkefeltet er til for å kunne velge mellom
						ulike bibliotek. 
					</p>
<?php
}
if (isset($_GET['tittelnr']))
{
	echo "<h3>Reisehåndbok:</h3>\n";
	//lagrer tittelnummer
	$tnr = $_GET['tittelnr'];
	//lagrer søketype (Z39.50, SRU)
	$type = get_type($_GET['bib']);
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
		                array('namespace' => '', 'name' => 'target', 'value' => 'local'), 
		                array('namespace' => '', 'name' => 'item_url', 'value' => $config['libraries'][$_GET['bib']]['item_url']));
		
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
			$xml->loadXML(get_ccl_results_as_xml($ccl, $config['main_limit']));

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
				
				$params = array(array('namespace' => '', 'name' => 'url_ext', 'value' => "$geoId&place=$place&bib=".$_GET['bib']),
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
			
			// Sjekk om sidetallet er satt. Hvis ikke: sett det til 1
			$page = !empty($_GET['page']) ? $_GET['page'] : 1; 
			
			//parametere til XSL
			$params = array(array('namespace' => '', 'name' => 'url_ext', 'value' => "$geoId&place=$place&bib=".$_GET['bib']),
							array('namespace' => '', 'name' => 'sortBy', 'value' => $sortBy),
							array('namespace' => '', 'name' => 'order', 'value' => $order), 
							array('namespace' => '', 'name' => 'page', 'value' => $page), 
							array('namespace' => '', 'name' => 'perPage', 'value' => $config['mainPerPage']), 
							array('namespace' => '', 'name' => 'querystring', 'value' => get_querystring($_SERVER['QUERY_STRING'])),
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
						få tilgang til informasjon om reisehåndbøker fra ditt biblioteks
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
		<?php
		if (!empty($config['autosuggest']['show_dewey']) && $config['autosuggest']['show_dewey'] == 'true') {
			echo('script:"autosuggest/autosuggest.php?json=true&limit=' . $config['autosuggest']['maxresults'] . '&info=true&",');
		} else {
			echo('script:"autosuggest/autosuggest.php?json=true&limit=' . $config['autosuggest']['maxresults'] . '&",');
		}
		echo('varname:"input",');
		echo('json:true,');
		echo('shownoresults:false,');
		?>
	};
	var as_json = new bsn.AutoSuggest('autosuggest', options);
//]]>
</script>
	</body>
</html>
<?php

/*
Lager en querystring-"mal" som vi lett kan bruke til å lage "forrige"- og "neste"-lenker i XSLT. 
XSLTen må bytte ut ZZZ med det ønskede sidetallet. Dette forutsetter at ZZZ ikke forekommer på 
andre steder i querystringen, men det burde vel være relativt trygt. Sikkert ikke den mest 
elegante måten å gjøre dette på, men...
*/
function get_querystring($q) {
	
	if (substr_count($q, 'page=') == 0) {
		// querystring inneholder ikke noe page-attributt fra før, så vi legger til et
		return $q . '&page=ZZZ';
	} else {
		// querystring inneholder et page attributt, vi bytter ut tallet med ZZZ
		return preg_replace('/page=\d{1,}/i', 'page=ZZZ', $q);
	}
	
}

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

?>
