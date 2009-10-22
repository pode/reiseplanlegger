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

if (!$config['modules']['google_map']['enabled']) {
  exit;
}

?>

<script type="text/javascript">
/**
 *Funksjon som tar imot parametre fra søkesiden og skriver ut riktig kart
 *Funksjonen tar imot 5 parametre (lengdegrad, breddegrad, kartbredde, karthøyde og zoom-nivå)
 *map variabelen må nåes fra to funksjoner og er derfor deklarert "globalt"
 */
var map;
function map(lat, lng, width, height, zoom)
{ 
    var coordinates = new GLatLng(lat, lng);
	var container = document.getElementById("map");
	map = new GMap2(container, {size:new GSize(width,height)});
    map.setCenter(coordinates, zoom);
	var UI = map.getDefaultUI();
	UI.zoom.scrollwheel = false; //zoom på scroll-hjulet til musa true/false
	map.setUI(UI);
}

/**
 *Funksjon som legger på de forskjellige lagene
 *switchLayer blir kallt fra hoveddokumentet og legger på riktig lag på kartutsnittet
 *Funksjonen tar to parametre (checked(om den er huket av) og layer(hvilket lag det gjelder)).
 */
var pano = new GLayer("com.panoramio.all");
var wiki = new GLayer("org.wikipedia.no");//ved å bytte ut .no med feks .en vil man få bare engelske wikipediaoppslag
var tube = new GLayer("com.youtube.all");

function switchLayer(checked,layer)
{
  if(checked)map.addOverlay(layer);
  if(!checked)map.removeOverlay(layer);
}
</script>

<?php

echo "\t<div id=\"map\">\n";
getMap($_GET['lat'], $_GET['lon'], "280", "280", "12");
echo "\t</div>\n";

/*
funksjon som skriver kart fra Google Maps. parameteren $lat og $lon
angir lengde- og breddegrad kartet skal vise. $width og $height
forteller hva dimensjonene på kartet skal være. $zoom angir hvilket
zoom-nivå kartet skal ha i utgangspunktet
*/
function getMap($lat, $lon, $width, $height, $zoom)
{

    echo("<script id=\"api\" src=\"http://maps.google.com/maps?file=api&amp;v=2.x&amp;hl=no&amp;key=ABQIAAAA4bkAB4c8qRFagE_bUojf3hS-l405Vpwg0XB2Ibm6AlGOmZ6JbhTiF85MU3mn8joSdlDb19Mam3gFSw\" type=\"text/javascript\"></script>");
    // echo("<script src=\"../scripts/googlemaps.js\" type=\"text/javascript\"></script>");
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


?>