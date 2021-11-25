<?php
/**
 * Datei       : config.inc.php
 * Beschreibung: Dient der Konfiguration und kann/muss vom Admin angepasst werden
  */
 
// ini_set ( 'date.timezone' , 'UTC'); Geht nur bei ollem Schreibrecht 
date_default_timezone_set('UTC'); // Geht nicht bei älteren PHP-Versionen 

//** SERVER Sybase-Einstellungen **//

//** LOKALE Sybase-Einstellungen **//
define('DB_DSN', 'druseidt');   // <<== DSN-Name ODBC
define('DB_ENG', '-');
define('DB_NAME', '-7');
define('DB_USER', 'dba');       // <<== Benutzer 
define('DB_PASSWORD', 'sql');   // <<== Password 
define('DB_LINKS', 'tcpip');
define('DB_HOST', '');          // <<== Host (bspw. dbsrv12)
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');


?>
