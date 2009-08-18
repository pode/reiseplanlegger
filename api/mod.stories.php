<?php

include_once('../config.php');

if (!$config['modules']['stories']['enabled']) {
  exit;
}

include_once('../include/functions.php');
include_once('inc.catalogue.php');

/*
Henter ut fortellinger fra katalogen, basert på land
*/

if (!empty($_GET['country']) && !empty($_GET['type'])) {

  if ($_GET['type'] == 'z39.50') {
  	echo z_search("eo={$_GET['country']} fortellinger", $config['modules']['stories']['limit']);
  } else {
  	echo sru_search("dc.subject = {$_GET['country']} and dc.subject = fortellinger", $config['modules']['stories']['limit']);
  }	
	
}

?> 