<?php

/**
 * Datei       	: pruefen.php
 * Beschreibung	: Prüft, ob die geplante Stempelung sinnoll ist 
 *
 * Author      	: VolkerA
 * Erstellt    	: 04.09.2020
 * Version		: 08.09.2020 12:07
 */

/**
 * Debug  
 * $out = '{"type":"error", "text":"Debug  '.$var.'"}'; echo $out; 
 * $newfile = fopen('./debug.txt', 'w');$str =$aktive_funktion  ;fwrite($newfile, $str);fclose($newfile);
 */

include_once "../server/db_odbc.php";
include_once "../config.inc.php"; 

//-----------------------------------------------------------------------------
// Parameter prüfen 
//-----------------------------------------------------------------------------

$bde_funktion = $_POST['bde_funktion']; 
if (!$bde_funktion) {
	echo '{"type":"error", "text":"BDE-Funktion nicht angegeben! Vorgang wird abgebrochen."}'; 
	return;
}

$berech_unter_fkt = $_POST['berech_unter_fkt']; 
if ( !($berech_unter_fkt == 0 || $berech_unter_fkt > 0) ) {
	echo '{"type":"error", "text":"BDE-Unterfunktion nicht angegeben! Vorgang wird abgebrochen."}'; 
	return;
}

$userlogin = $_POST['userlogin']; 
if (!$userlogin) {
	echo '{"type":"error", "text":"User-Login nicht angegeben! Vorgang wird abgebrochen."}'; 
	return;
}

//-----------------------------------------------------------------------------
// Variablen 
//-----------------------------------------------------------------------------

$rowcount=0;
$aktive_funktion=0;
$aktive_unterfunktion=0;

$connection = new db_odbc();
$connection->connect();

// Mitarbeiter Kürzel für ressourcen_id holen 
$columns = "kuerzel";
$filter = "WHERE user_id = id AND users.name = '".$userlogin."'";
$sql = "SELECT ".$columns." FROM \"users\",\"mitarb\" ".$filter." ;";
$result = $connection->query($sql);
$in = '';
while($row = $connection->fetch_array($result)){
	$ressourcen_id = $row['kuerzel'];
}

//-----------------------------------------------------------------------------
// Gültigkeit der Buchung prüfen 
//-----------------------------------------------------------------------------

$columns = "FIRST bde_funktion, berech_unter_fkt";
$filter = "WHERE ressourcen_id = '".$ressourcen_id."' AND  DATE(stempelzeit) = DATE(TODAY()) and bde_funktion <> 2500 "; // Änderung durch Herrn Abrahams 01.12.2020
$order = "ORDER BY lfd_nr DESC";
$sql    = "SELECT ".$columns." FROM bde_receive ".$filter." ".$order.";";


$result = $connection->query($sql);
$in = '';
while($row = $connection->fetch_array($result)){
	$aktive_funktion =  $row['bde_funktion'];
	$aktive_unterfunktion =  $row['berech_unter_fkt'];
	
}

// Auswertung 
// Es ist eine Anstemplung (500), das Letzte muss eine Abstemplung (510) sein oder kein Datensatz 
if ($bde_funktion == 500) {
	if($result){
		if ($aktive_funktion == 510 || $aktive_funktion == 0){
			$out =  '{ "type":"default", "text":"Ok" }';
		} else {
			$out =  '{ "type":"error", "text":"NOK: Sie haben Ihren Dienst bereits begonnen!"}';
		}		
	} else {
		$out =  '{ "type":"default", "text":"OK" }';
	}
}

// Es ist ein Dienstgang Anfang: Das Letzte muss eine Anstemplung sein (500) oder eine Dienstgang Ende (520,20)
if ($bde_funktion == 520 && $berech_unter_fkt == 10 ) {
	if ($aktive_funktion == 510 ) {
		$out =  '{ "type":"error", "text":"NOK: Sie haben Ihren Dienst bereits beendet!" }';	
	} else {
		if($aktive_funktion == 520 && $aktive_unterfunktion == 10){
			$out =  '{ "type":"error", "text":"NOK: Sie haben bereits einen Dienstgang begonnen!" }';	
		}else{
			$out =  '{ "type":"default", "text":"Ok" }';
		}
	}
}

// Es ist ein Dienstgang Ende: Das Letzte muss eine Anstemplung sein (500) oder eine Dienstgang Beginn (520,10)
if ($bde_funktion == 520 && $berech_unter_fkt == 20 ) {
	if ($aktive_funktion == 500 || ($aktive_funktion == 520 && $aktive_unterfunktion == 10 )  ) {
		$out =  '{ "type":"default", "text":"Ok" }';
	} else {
		$out =  '{ "type":"error", "text":"NOK: Ohne Dienstbeginn kein Dienstgang Ende möglich!" }';
	}
}


// Es ist eine Abstemplung: Das Letzte muss ein Dienstang (520,20) oder eine Anstemplung sein (500)
if ($bde_funktion == 510  )  {
	if ($aktive_funktion == 500 || $aktive_funktion == 520 ) {
	 	$out =  '{ "type":"default", "text":"OK" }';
	} else {

		if ($aktive_funktion == 510) {
			$out =  '{ "type":"error", "text":"NOK: Sie haben Ihren Dienst bereits beendet!" }';
		}
		if ($aktive_unterfunktion == 10){
			$out =  '{ "type":"error", "text":"NOK: Es besteht noch ein offener Dienstgang!" }';
		} else {
			$out =  '{ "type":"error", "text":"NOK: Es fehlt ein Dienstbeginn!" }';
		}
	}
}

echo $out;  

?> 