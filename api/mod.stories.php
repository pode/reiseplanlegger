<?php

include_once('../include/functions.php');
include_once('inc.catalogue.php');

/*
Henter ut fortellinger fra katalogen
*/

if (!empty($_GET['country']) && !empty($_GET['type'])) {

  if ($_GET['type'] == 'sru') {
  	echo sru_search("dc.subject = {$_GET['country']} and dc.subject = Fortellinger", 5);
  } else {
  	echo z_search("eo={$_GET['country']} Fortellinger", 5);
  }	
	
}

?>