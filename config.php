<?php

/*
MODULER

Moduler konfigureres med et array på formen
$config['modules']['MODUL'] = array();
der MODUL tilsvarer den midterste delen av filnavnet modulen 
er implementert i: mod.MODUL.php. 

Rekkefølgen på modulene nedenfor bestemmer rekkfølgen modulene
vises i på siden. (Men dette kan overstyres av brukerne, som selv kan
flytte rundt på modulene.)

Alle moduler har minst to parametere: 
'enabled': true eller false, dvs om modulen er slått av eller på. 
'title': tittelen som vises i modul/widget-boksen

Dersom modulen inneholder en liste med elementer hvor antallet 
elementer skal kunne begrenses ved hjelp av en parameter gjøres 
dette med en parameter som heter 'limit'.
*/

$config['modules']['language'] = array(
  'enabled' => true, 
  'title' => "Lærebøker og språkkurs",  
  'limit' => 7, 
);

$config['modules']['stories'] = array(
  'enabled' => true,
  'title' => "Fortellinger",  
  'limit' => 7, 
);

$config['modules']['snl'] = array(
  'enabled' => true, 
  'title' => "Store norske leksikon", 
  'limit' => 5,
);

$config['modules']['weather'] = array(
  'enabled' => true, 
  'title' => "Været", 
);

$config['modules']['map'] = array(
  'enabled' => true, 
  'title' => "Kart", 
);

$config['modules']['debug'] = array(
  'enabled' => true, 
  'title' => "Debug", 
);

?>