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