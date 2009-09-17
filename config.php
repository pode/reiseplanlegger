<?php

// Setter navnet på applikasjonen
$config = array('app_title' => 'Reiseplanlegger');

/*
BIBLIOTEK

Her konfigureres de bibliotekene det skal være mulig å søke i, og 
de opplysningene som trengs for å utføre søket. Rekkefølgen her
bestemmer rekkefølgen når bibliotekene skal velges ved søk. 

Opplysninger som trengs: 
title: navn på biblioteket 
z3950 ELLER sru og item_url
z3950: tilkoblings-streng for Z39.50
sru: tilkoblingsstreng for SRU
item_url: grunn-URL for postvisning i katalogen
*/
$config['libraries']['deich'] = array(
	'title' => 'Deichmanske', 
	'z3950' => 'z3950.deich.folkebibl.no:210/data'
);
$config['libraries']['pode'] = array(
	'title'    => 'Pode', 
	'sru'      => 'http://torfeus.deich.folkebibl.no:9999/biblios', 
	'item_url' => 'http://dev.bibpode.no/cgi-bin/koha/opac-detail.pl?biblionumber='
);
/*
$config['libraries']['bibsys'] = array(
	'title' => 'BIBSYS', 
	'z3950' => 'z3950.bibsys.no:2100/BIBSYS', 
);
$config['libraries']['trondheim'] = array(
	'title' => 'Trondheim folkebibliotek', 
	'z3950' => 'z3950.trondheim.folkebibl.no:210/data', 
);
$config['libraries']['bergen'] = array(
	'title' => 'Bergen offentlige', 
	'z3950' => 'z3950.bergen.folkebibl.no:210/data', 
);
*/

/*
DIVERSE MELDINGER
*/

$config['msg'] = array( 
	'zero_hits' => '<p>Ingen treff...</p>', 
);

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
  'limit' => 5, 
);

$config['modules']['travel'] = array(
  'enabled' => true,
  'title' => "Reiseskildringer",  
  'limit' => 5, 
);

$config['modules']['stories'] = array(
  'enabled' => true,
  'title' => "Fortellinger",  
  'limit' => 5, 
);

$config['modules']['snl'] = array(
  'enabled' => true, 
  'title' => "Store norske leksikon", 
  'limit' => 1,
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