<?php

echo "\t<div id=\"map\"></div>\n";
getMap($_GET['lat'], $_GET['lon'], "280", "280", "12");

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

<div id="map"></div>
<script type="text/javascript">
  google.load("maps", "2",{"other_params":"sensor=false"});
  // Call this function when the page has been loaded
  function initialize() {
    var coordinates = new GLatLng(<?php echo($_GET['lat']); ?>, <?php echo($_GET['lon']); ?>);
	var container = document.getElementById("map");
	map = new GMap2(container, {size:new GSize(250,250)});
    map.setCenter(coordinates, 13);
	var UI = map.getDefaultUI();
	UI.zoom.scrollwheel = false; //zoom på scroll-hjulet til musa true/false
	map.setUI(UI);
  }
  google.setOnLoadCallback(initialize);
</script>

*/

?>