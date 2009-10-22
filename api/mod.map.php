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