<?php

include_once('../include/functions.php');
include_once('inc.catalogue.php');

/*
Henter ut språkkurs og lærebøker fra katalogen
*/

if (!empty($_GET['langs']) && !empty($_GET['type'])) {

  ?>
  <script type="text/javascript">

  function getLanguage(lang) {
		
    $.get("api/index.php", { mod: 'language',
				             lang: lang, 
				             type: '<?php echo($_GET['type']); ?>' 
				             },
	    function(data){
	      $("#langsearch").text("");
	      $("#langsearch").append(data);
	});
		
  }
	
  </script>
  <?php

  $langs = explode(',', $_GET['langs']);

  echo('<form>'. "\n" . '<select onChange="getLanguage(this.value)">' . "\n" . '<option>Velg språk...</option>' . "\n");
  foreach($langs as $this_lang) {
  	if ($this_lang != '') {
  	  echo("<option>$this_lang</option>\n");
  	}
  }
  echo("</select>\n</form><br />\n" . '<div id="langsearch"></div>' . "\n");

} elseif (!empty($_GET['lang'])) {
	
  if ($_GET['type'] == 'z39.50') {
    echo z_search("eo={$_GET['lang']} and (eo=lærebøker or eo=språkkurs)", 5);	
  } else {
    echo sru_search("dc.subject = {$_GET['lang']} and (dc.subject = lærebøker or dc.subject = språkkurs)", 5);	
  }
  
}

?> 