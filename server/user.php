<?php

/**
 * Datei       : Artget_user.php
 * Beschreibung:
 *
 * Author      : VolkerA
 * Erstellt    : 31.08.2020
 *
 */

include_once "../server/db_odbc.php";
include_once "../config.inc.php"; 
/**
 * Variablen für eingehende Werte
 */

if(isset($_GET["userlogin"]))
    $username = $_GET['userlogin'];
else    
	$username = '-';

if(isset($_GET["userpasswort"]))
    $userpasswort = $_GET['userpasswort'];
else    
	$userpasswort = '-';	

/**
 * Verbindung zur Datenbank
 */
 
$connection = new db_odbc();
$connection->connect();

$range = '';
$columns = '*';
$filter = '';
$sql  = '';
$kuerzel = '';


/**
 *  Zugang legitimieren 
 */

$columns = "users.name \"login\", mitarb.name, wpze_loginpw, kuerzel";
$filter = "WHERE  id = user_id AND users.name = '".$username."' AND wpze_loginpw = '".$userpasswort."'";
$sql = "SELECT ".$columns." FROM \"users\",\"mitarb\" ".$filter." ;";
$result = $connection->query($sql);
 
$in = '{"userlogin":"   ","username":"FALSCH","userpasswort":"   ",';
while($row = $connection->fetch_array($result)){

	$kuerzel = $row['kuerzel'];

	$in = '{';
	$in .= '"userlogin":"'.$row['login'].'",';
	$in .= '"username":"'.$row['name'].'",';
	$in .= '"userpasswort":"'.$row['wpze_loginpw'].'",';
		
}

/**
 * Status ermitteln 
 */

$stat = '"bde_funktion":"0",';
$stat .= '"berech_unter_fkt":"0"';

$sql = <<<SQL
SELECT FIRST bde_funktion, berech_unter_fkt, LEFT(stempelzeit,10) + 'T' + LEFT(RIGHT(stempelzeit,15),8) AS zeit  FROM bde_receive WHERE ressourcen_id = '$kuerzel' AND  DATE(stempelzeit) = DATE(TODAY())  ORDER BY lfd_nr DESC;
SQL;

$result = $connection->query($sql);
while($row = $connection->fetch_array($result)){
	$stat  = '"bde_funktion":"'.$row['bde_funktion'].'",';
	$stat .= '"berech_unter_fkt":"'.$row['berech_unter_fkt'].'",';
	$stat .= '"stempelzeit":"'.$row['zeit'].'"';	
}
$in .= $stat; 
$in .= '}';


$out = utf8_encode ($in);
// $out = substr($out, 0, -1);
// $out .= ']';
echo $out; 

?> 