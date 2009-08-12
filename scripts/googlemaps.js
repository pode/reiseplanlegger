/**
 *Funksjon som tar imot parametre fra søkesiden og skriver ut riktig kart
 *Funksjonen tar imot 5 parametre (lengdegrad, breddegrad, kartbredde, karthøyde og zoom-nivå)
 *map variabelen må nåes fra to funksjoner og er derfor deklarert "globalt"
 */
var map;
function map(lat, lng, width, height, zoom)
{ 
	var container = document.getElementById("map");
	map = new GMap2(container, {size:new GSize(width,height)});
    map.setCenter(new google.maps.LatLng(lat, lng), zoom);

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
