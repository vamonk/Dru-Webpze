<?php

/**
 * Datei       : password.php
 * Beschreibung: Ändert das Passwort des Benutzers 
 *
 * Author      : VolkerA
 * Erstellt    : 24.09.2020
 */

/**
 * Debug  
 * $out = '{"type":"error", "text":"Debug  '.$var.'"}'; 
 * echo $out; 
 */

include_once "../server/db_odbc.php";
include_once "../config.inc.php"; 

error_reporting(0);


$connection = new db_odbc();
$connection->connect();

$userlogin = $_POST['userlogin']; 
if (!$userlogin) {
	echo '{"type":"error", "text":"Es wurde kein Benutzername übergeben! Vorgang wird abgebrochen."}'; 
	return;
}

$newpass = $_POST['passnew']; 
if (!$newpass) {
	echo '{"type":"error", "text":"Es wurde kein gültiges Password übergeben! Vorgang wird abgebrochen."}'; 
	return;
}

// Passwort verschleiern 

// $newpass = md5($newpass);

// Die Buchung 
$sql  = "UPDATE users SET wpze_loginpw = '".$newpass."' WHERE name = '".$userlogin."'";
$res = $connection->query($sql);

if($res){
	$out =  '{ "type":"default", "text":"Änderung wurde durchgeführt." }';
} else {
	$out = '{ "type":"error", "text":"Änderung wurde nicht durchgeführt!" }';
}
if (odbc_error())
{
	$out = '{ "type":"error", "text":"Es ist ein Fehler aufgetreten. <smal>Vermutlich eine gesperrte Tabelle der Datenbank.</smal>"  }';
}


echo $out;  

?> 