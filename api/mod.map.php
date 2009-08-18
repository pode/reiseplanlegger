<?php

include_once('../config.php');

if (!$config['modules']['map']['enabled']) {
  exit;
}

echo "\t<div id=\"map\"></div>\n";
getMap($_GET['lat'], $_GET['lon'], "280", "280", "12");

/*
funksjon som skriver kart fra Google Maps. 

Parametere: 
$lat og $lon angir lengde- og breddegrad kartet skal vise. 
$width og $height forteller hva dimensjonene på kartet skal være. 
$zoom angir hvilket zoom-nivå kartet skal ha i utgangspunktet
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

?>