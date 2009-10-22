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

if (!$config['modules']['language']['enabled']) {
  exit;
}

include_once('../include/functions.php');

/*
Henter ut språkkurs og lærebøker fra katalogen
*/

if (!empty($_GET['langs']) && !empty($_GET['bib'])) {

  ?>
  <script type="text/javascript">

  function getLanguage(lang) {
		
	if (!lang) {
		lang = document.getElementById("selectLang").options[document.getElementById("selectLang").selectedIndex].text;	
	}
    $.get("api/index.php", { mod: 'language',
				             lang: lang, 
				             bib: '<?php echo($_GET['bib']); ?>' 
				             },
	    function(data){
	      $("#langsearch").text("");
	      $("#langsearch").append(data);
	      // Sørg for å aktivere eventuelle trekkspill
			  	  $(".trekkspill").accordion({ autoHeight: false, active: false });
	});
		
  }
	
  </script>
  <?php

  $langs = explode(',', $_GET['langs']);

  echo('<form>'. "\n" . '<select id="selectLang" onChange="getLanguage(selectLang.value)">' . "\n" . '<option>Velg språk...</option>' . "\n");
  foreach($langs as $this_lang) {
  	if ($this_lang != '') {
  	  echo("<option>$this_lang</option>\n");
  	}
  }
  echo("</select>\n</form><br />\n" . '<div id="langsearch"></div>' . "\n");

} elseif (!empty($_GET['lang'])) {
	
  if (get_type($_GET['bib']) == 'z39.50') {
  	
  	if ($zs_result = z_search("eo={$_GET['lang']} and eo=språkkurs", $config['modules']['language']['limit'])) {
  		echo "<p>Språkkurs</p>";
    	echo $zs_result;
  	}
  	if ($zl_result = z_search("eo={$_GET['lang']} and eo=lærebøker", $config['modules']['language']['limit'])) {
    	echo "<p>Lærebøker</p>";
    	echo $zl_result;
  	}
  	if (!$zs_result && !$zl_result) {
  		echo $config['msg']['zero_hits'];
  	}
  
  } else {
  	
  	if ($s_result = sru_search("dc.subject = {$_GET['lang']} and dc.subject = språkkurs", $config['modules']['language']['limit'])) {
  		echo "<p>Språkkurs</p>";
    	echo $s_result;
  	}
  	if ($l_result = sru_search("dc.subject = {$_GET['lang']} and dc.subject = lærebøker", $config['modules']['language']['limit'])) {
    	echo "<p>Lærebøker</p>";
    	echo $l_result;
  	}
  	if (!$s_result && !$l_result) {
  		echo $config['msg']['zero_hits'];
  	}
  	
  }
  
}

?> 