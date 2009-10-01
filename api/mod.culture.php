<?php

include_once('../config.php');

if (!$config['modules']['culture']['enabled']) {
  exit;
}

include_once('../include/functions.php');

/*
Henter ut reiseskildringer fra katalogen, basert på land
*/

if (!empty($_GET['country']) && !empty($_GET['bib'])) {

  if (get_type($_GET['bib']) == 'z39.50') {
  	if ($zresult = z_search("eo={$_GET['country']} and eo=kulturhistorie", $config['modules']['culture']['limit'])) {
  		echo $zresult;
  	} else {
  		echo $config['msg']['zero_hits'];
  	}
  } else {
  	if ($result = sru_search("dc.subject = {$_GET['country']} and dc.subject = kulturhistorie", $config['modules']['culture']['limit'])) {
  		echo $result;
  	} else {
  		echo $config['msg']['zero_hits'];
  	}
  }	
	
}

?> 