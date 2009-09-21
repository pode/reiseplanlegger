// Sjekk om det er gitt noen verdi for "place" i URLen (query string)
if (getQueryVariable('place')) {

// Dette er en jQuery funksjon som automatisk kjøres når hele siden er lastet inn 
// (dvs, egentlig når DOMen er klar)
$(function(){

  // Gjør boksene til "widgets"
  // useCookies : true gjør at det brukes cookies for å huske tilstanden til boksene 
  // mellom sesjoner
  $.fn.EasyWidgets({
  	
  	behaviour : {
      useCookies : true
    },
  	
    i18n : {
      editText       : '<img src="images/widgets/edit.png"     alt="Rediger" width="16" height="16" />',
      closeText      : '<img src="images/widgets/close.png"    alt="Close"   width="16" height="16" />',
      collapseText   : '<img src="images/widgets/collapse.png" alt="Lukk"    width="16" height="16" />',
      cancelEditText : '<img src="images/widgets/edit.png"     alt="Avbryt"  width="16" height="16" />',
      extendText     : '<img src="images/widgets/extend.png"   alt="Close"   width="16" height="16" />'
    }
  });
  
  // Vis boksen som inneholder navn på sted, evt mulighet for å velge sted  
  $("#place").css({'visibility' : 'visible'});
  
  // Hent inn verdier fra URLen (query string) Denne funksjonen er definert lenger ned i fila
  var place = getQueryVariable('place');
  // var placeEng = place; 
  var geoId = getQueryVariable('geoId');
  
  // Bruk Google Translate for å oversette navnet som vi antar er på norsk, til engelsk
  // (Det funker å søke med norske navn mot hoved-fila fra GeoNames, men ikke mot fila
  // som gir oss koblingen mellom land og hovedstad.)
  // google.language.translate(place, "no", "en", function(result) {
  //   if (!result.error) {
  // 	  placeEng = result.translation;
  // }
  // });
  
  // Hent nødvendige geodata fra serveren
  // Ved suksess vil getgeo.php returnere et array som inneholder et element "result" som vil 
  // være antallet treff. Vi bruker dette for å bestemme videre gang i skriptet
  $.getJSON("api/getgeo.php", { geoId: geoId, place: place }, function(json){
    
   
    // Fjern inneholdet i boksen som viser sted/tid
    $("#place").find(".widget-content").text("");
    
    if (json == 0) {
        
        // INGEN TREFF, skriver ut en feilmelding
        
        $("#place").find(".widget-content").append("<p>Beklager, 0 treff. Pr&oslash;v &aring; s&oslash;ke etter et annet stedsnavn.</p>");
    
        // TODO: Kunne man fått til å foreslå alternative skrivemåter? 
    
    } else if (json.length == 1) {
    	
    	// ETT TREFF - lager bokser med info om stedet/landet
    	
    	// Vi vil bare ha data fra det første og eneste stedet
    	var place = json[0];
    	
    	// Hent ut koordinater
    	var lat = place.lat;
		var lon = place.lng;
    	
    	if (place.fcode == 'PCLI') {
    		// Vi har et land
	    	$("#place").find(".widget-content").append("<h3>" + place.name + "</h3>");
	    	$("#place").find(".widget-content").append("<p>Hovedstad: " + place.placeInfo.capital + "</p>");
	    	$("#place").find(".widget-content").append("<p>Befolkning: " + addCommas(place.placeInfo.population) + "</p>"); 
	    	$("#place").find(".widget-content").append("<p>Valuta: " + addCommas(place.placeInfo.currencyCode) + "</p>"); 
	    	// Sett koordinatene til hovedstadens koordinater
	    	lat = place.placeInfo.capital_long.lat
	    	lon = place.placeInfo.capital_long.lng
    	} else {
    		// Vi har et sted	
	    	$("#place").find(".widget-content").append("<h3>" + place.name + ", " + place.countryName + "</h3>");
	    	$("#place").find(".widget-content").append("<p>Befolkning: " + addCommas(place.population) + "</p>"); 
	    	$("#place").find(".widget-content").append("<p>Valuta: " + addCommas(place.placeInfo.currencyCode) + "</p>"); 
    	}
    	
	    // Lag en liste med navn på språkene
	    var langs = '';
	    jQuery.each($(place.placeInfo.languages_long), function() {
		  langs = langs + this + ",";
	    });
	    
	   	// Gjør widgetene synlige
    	$(".widget").css({'visibility' : 'visible'});
    	  
    	// Gå igjennom alle widgetene og legg til innhold
		jQuery.each($(".widget"), function() {
		  var this_widget = this;
		  target = this_widget.id.replace(/widget_/g, "");
				
		  // Vi sender den samme informasjonen til modulene, uavhengig av hva de skal gjøre for noe. 
		  // På denne måten slipper vi å vite noe om hver enkelt modul før vi kaller dem opp. 
		  $.get("api/index.php", { mod: target, 
		                           lat: lat, 
		                           lon: lon, 
		                           place: place.name, 
		                           country: place.countryName, 
		                           langs: langs, 
		                           q: getQueryVariable('place'),
		                           bib: getQueryVariable('bib')
		                          },
		    function(data){
		      $("#" + this_widget.id).find(".widget-content").text("");
		      $("#" + this_widget.id).find(".widget-content").append(data);
		      // Sørg for å aktivere eventuelle trekkspill
		  	  $(".trekkspill").accordion({ autoHeight: false, active: false });
		    });
		
		  });
		    
    } else {
    	
    	// FLERE TREFF - vis liste med mulighet for å velge hvilket sted som var ment
    	
    	$("#place").find(".widget-content").append('<h3>Velg sted</h3>');
    	var place = "place="  + getQueryVariable('place');
    	var sort  = "&sortBy=" + getQueryVariable('sortBy');
    	var order = "&order="  + getQueryVariable('order');
    	var bib   = "&bib="    + getQueryVariable('bib');
    	jQuery.each(json, function() {

			var name  = this.name;
			var cname = this.countryName;
    		var geoId = "&geoId="  + this.geonameId;
    		var adminname = '';
    		if (this.adminName1) {
    			adminname = ", " + this.adminName1;
    		}
    		
    		placename = '';
    		if (this.fcode == 'PCLI') {
    			// Land
    			placename = this.countryName;
    		} else {
    			// Sted
    			placename = name + adminname + ", " + cname;
    		}
    		
		    $("#place").find(".widget-content").append('<p><a href="?' + place + geoId + sort + order + bib + '">' + placename + '</a></p>');
    		
         });

    }
  });

});

}

// From: http://www.mredkj.com/javascript/nfbasic.html
function addCommas(nStr) {
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + '.' + '$2');
	}
	return x1 + x2;
}


// From: http://www.webdeveloper.com/forum/showthread.php?t=166692
function getQueryVariable(variable)
{
	var query = window.location.search.substring(1);
	var vars = query.split("&");
	for (var i=0; i<vars.length; i++)
	{
		pair = vars[i].split("=");
		if (pair[0] == variable)
		{
			str_arr = pair[1].split("+");
			return str_arr.join(" ");
		}
	}
	return "";
}