<?php

include_once('../config.php');

if (!$config['modules']['debug']['enabled']) {
  exit;
}

echo("<p>Dette er en liten debug-modul som viser hvilken informasjon som er tilgjengelig for modulene.</p>");

echo("<ul>");

foreach ($_GET as $key => $value) {

  echo("<li>$key: $value</li>");
	
}

echo("<ul>");

?>