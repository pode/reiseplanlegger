<?php

include_once('../config.php');
include_once('../include/functions.php');

/*
Viser lenker til artikler fra SNL

Info fra SNL: 

  Man søker i leksikonet med følgende url:
  http://www.snl.no/.search?query=test&format=xml&x=0&y=0
  query er spørreordet, x og y er antall ønskede svar og startpunkt, 
  format er hvilket format dere ønsker resultatet i. Vi støtter 
  formatene html, xml og json.

  Strengen kan også ta parameteren authorized som kan være 0 eller 1, 
  avhengig av om man vil søke etter autorisert innhold, uautorisert, 
  eller begge deler (i siste tilfellet utelater man parameteren).

NB! Det ser ikke ut som x- og y-parameterne i URLen til SNL funker! 

*/

if (!empty($_GET['q'])) {
	
  $data = json_decode(file_get_contents("http://www.snl.no/.search?query=" . $_GET['q'] . "&format=json&x=" . $config['modules']['snl']['limit'] . "&y=0"));
  
  if ($data) {
  	echo("<ul>");
    foreach ($data->result->list as $item) {
      echo('<li><a href="http://snl.no/' . $item->link . '">' . $item->title . '</a></li>');
    }
    echo("</ul>");
  }
	
}

?> 