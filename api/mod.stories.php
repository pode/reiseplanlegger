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

if (!$config['modules']['stories']['enabled']) {
  exit;
}

include_once('../include/functions.php');

/*
Henter ut fortellinger fra katalogen, basert på land
*/

if (!empty($_GET['country']) && !empty($_GET['bib'])) {

  if (get_type($_GET['bib']) == 'z39.50') {

  	if ($zresult = z_search("eo={$_GET['country']} fortellinger", $config['modules']['stories']['limit'], 1, 'descending', 'year', true)) {
  		echo $zresult;
  	} else {
  		echo $config['msg']['zero_hits'];	
  	}

  } else {

  	if ($result = sru_search("dc.subject = {$_GET['country']} and dc.subject = fortellinger", $config['modules']['stories']['limit'], 1, 'descending', 'year', true)) {
  		echo $result;
  	} else {
  		echo $config['msg']['zero_hits'];	
  	}

  }	
	
}

?> 