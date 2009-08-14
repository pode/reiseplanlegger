<p>Dette er en liten debug-modul som viser hvilken informasjon som er tilgjengelig for modulene.</p>

<ul>

<?php

foreach ($_GET as $key => $value) {

  echo("<li>$key: $value</li>");
	
}

?>

</ul>