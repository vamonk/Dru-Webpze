<?php

/**
 * Datei       : buchen.php
 * Beschreibung: Fügt eine Stempelung in bde_receive ein 
 *
 * Author      : VolkerA
 * Erstellt    : 04.09.2020
 */

/**
 * Debug  
 * $out = '{"type":"error", "text":"Debug  '.$var.'"}'; 
 * echo $out; 
 */

include_once "../server/db_odbc.php";
include_once "../config.inc.php"; 

$connection = new db_odbc();
$connection->connect();

$bde_funktion = $_POST['bde_funktion']; 
if (!$bde_funktion) {
	echo '{"type":"error", "text":"BDE-Funktion nicht angegeben! Vorgang wird abgebrochen."}'; 
	return;
}

$berech_unter_fkt = $_POST['berech_unter_fkt']; 
if ( !($berech_unter_fkt == 0 || $berech_unter_fkt == 10  || $berech_unter_fkt == 20) ) {

	echo '{"type":"error", "text":"BDE-Unterfunktion nicht angegeben! Vorgang wird abgebrochen."}'; 
	return;
}

$userlogin = $_POST['userlogin']; 
if (!$userlogin) {
	echo '{"type":"error", "text":"User-Login nicht angegeben! Vorgang wird abgebrochen."}'; 
	return;
}



// Mitarbeiter Kürzel für ressourcen_id holen 
$columns = "kuerzel";
$filter = "WHERE user_id = id AND users.name = '".$userlogin."'";
$sql = "SELECT ".$columns." FROM \"users\",\"mitarb\" ".$filter." ;";
$result = $connection->query($sql);
$in = '';
while($row = $connection->fetch_array($result)){
	$ressourcen_id = $row['kuerzel'];
}

// Die Buchung 
$sql  = "INSERT INTO bde_receive (lfd_nr, bde_funktion,status,stempelzeit, ressourcen_id, berech_unter_fkt, ind_string01)";
$sql .= " VALUES (";
$sql .= " (SELECT MAX(lfd_nr)+1 FROM bde_receive bdr ),";
$sql .= "  ".$bde_funktion.",";
$sql .= "    0,";
$sql .= "    NOW(),";
$sql .= "   '".$ressourcen_id."',";
$sql .= "   '".$berech_unter_fkt."',";
$sql .= "   'WEBPZE'";
$sql .= ");"; 

$res = $connection->query($sql);

//$out = utf8_encode ($in);


if($res){
	$out =  '{ "type":"default", "text":"Buchung wurde durchgeführt." }';
} else {
	$out = '{ "type":"error", "text":"Buchung wurde nicht durchgeführt!" }';
}

echo $out;  

?> 