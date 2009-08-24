<?php

include_once('../config.php');

if (!$config['modules']['travel']['enabled']) {
  exit;
}

include_once('../include/functions.php');
include_once('inc.catalogue.php');

/*
Henter ut reiseskildringer fra katalogen, basert på land
*/

if (!empty($_GET['country']) && !empty($_GET['type'])) {

  if ($_GET['type'] == 'z39.50') {
  	if ($zresult = z_search("eo={$_GET['country']} and eo=Geografi og reiser", $config['modules']['travel']['limit'])) {
  		echo $zresult;
  	} else {
  		echo $config['msg']['zero_hits'];
  	}
  } else {
  	if ($result = sru_search("dc.subject = {$_GET['country']} and dc.subject = Geografi og reiser", $config['modules']['travel']['limit'])) {
  		echo $result;
  	} else {
  		echo $config['msg']['zero_hits'];
  	}
  }	
	
}

?> 