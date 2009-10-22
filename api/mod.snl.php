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
if (!$config['modules']['snl']['enabled']) {
  exit;
}
include_once('../include/functions.php');

/*
Viser lenker til artikler fra SNL

Info fra SNL: 

  Man søker i leksikonet med følgende url:
  http://www.snl.no/.search?query=test&format=xml&size=0&y=0
  query er spørreordet, x og y er antall ønskede svar og startpunkt, 
  format er hvilket format dere ønsker resultatet i. Vi støtter 
  formatene html, xml og json.

  Strengen kan også ta parameteren authorized som kan være 0 eller 1, 
  avhengig av om man vil søke etter autorisert innhold, uautorisert, 
  eller begge deler (i siste tilfellet utelater man parameteren).

NB! Det ser ikke ut som x- og y-parameterne i URLen til SNL funker! 
Ny info: parameteren size skal avgrense antall treff

*/

if (!empty($_GET['q'])) {
	
  $data = json_decode(file_get_contents("http://www.snl.no/.search?query=" . $_GET['q'] . "&format=json&size=" . $config['modules']['snl']['limit'] . "&y=0"));
  
  if ($data) {
    foreach ($data->result->list as $item) {
      echo('<p><a href="http://snl.no/' . $item->link . '">' . $item->title . '</a>, ' . strip_tags($item->shortview) . '<br /><a href="http://snl.no/' . $item->link . '">Les mer i Store norske leksikon</a></p>');
    }
  }
	
}

?> 