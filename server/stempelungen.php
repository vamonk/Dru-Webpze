<?php

/**
 * Datei       	: stempelungen.php
 * Beschreibung	: Lädt die tagesaktuellen Stempelungen 
 *
 * Author      	: VolkerA
 * Erstellt    	: 10.09.2020
 * Version		: 16.09.2020 17:31
 * Historie 	: 04.01.2021 _500 -> 5500 _510 -> 5510
 * 				: 05.05.2021 	Summe für die Dauer hinzugefügt
 * 								Dazu von String auf Integer umgestellt und deswegen keine Formatierung (fett) mehr möglich 
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

$userlogin = $_GET["userlogin"]; 
if (!$userlogin) {
	echo '{"type":"error", "text":"User-Login nicht angegeben! Vorgang wird abgebrochen."}'; 
	return;
}

//-----------------------------------------------------------------------------
// Daten  
//-----------------------------------------------------------------------------

$rowcount=0;

$connection = new db_odbc();
$connection->connect();

//-----------------------------------------------------------------------------
// Kürzel 
//-----------------------------------------------------------------------------

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
// Userinfo 
//-----------------------------------------------------------------------------

$sql = <<<SQL
SELECT  (IF bde_funktion = 500 THEN 'kommen' 
            ELSE (IF bde_funktion = 520 AND berech_unter_fkt = 10 THEN 'Dienstgang Anfang' ELSE 
                (IF bde_funktion = 520 AND berech_unter_fkt = 20 THEN 'Dienstgang Ende' ELSE 'gehen' END IF) 
         END IF) 
            END IF) bdefunktion,
			LEFT(RIGHT(stempelzeit,15),8 )  zeit , 
			
			( /* Aus Original mit vielen Spalten ein verschachteles Sub-Select mit nun einer Stalte */
				
				SELECT (IF bde_funktion = 500 THEN datediff(  minute,  
					( IF ( /*c_letzte_nr*/ COALESCE((SELECT FIRST nr FROM "bde_header" h2 WHERE h2."sart" = "bde_header"."sart" AND h2."karten_nr" = "bde_header"."karten_nr" AND h2."function_id" = "bde_header"."function_id" AND DATE(h2."start") = date("bde_header"."start") AND h2.nr < "bde_header"."nr" ORDER BY nr desc),-1)) > 0 
					THEN (SELECT "ende" FROM "bde_header" h3 WHERE h3.nr = (
					       /*c_letzte_nr*/ COALESCE((SELECT FIRST nr FROM "bde_header" h2 WHERE h2."sart" = "bde_header"."sart" AND h2."karten_nr" = "bde_header"."karten_nr" AND h2."function_id" = "bde_header"."function_id" AND DATE(h2."start") = date("bde_header"."start") AND h2.nr < "bde_header"."nr" ORDER BY nr desc),-1)
					) ) ELSE NULL END IF), 
				"bde_header"."start" ) ELSE ' ' END IF) c_pause 
				FROM "bde_header",   
				"bde_sart" 
				WHERE ( "bde_header"."sart" = "bde_sart"."id" ) AND 
				( 
					("bde_header"."nr" = "bde_receive"."bde_header_nr") AND
					( "bde_header"."function_id" = 'KOMMT' ) AND 
					( "bde_header"."start" >= DATE(TODAY()) )
				)   
				
		    ) pause 
FROM bde_receive 
WHERE ressourcen_id = '$ressourcen_id' 
AND  DATE(stempelzeit) = DATE(TODAY()) and bde_funktion <> 2500 
ORDER BY zeit 
SQL;

$ZeilenZaehler = 0;
$rowcount = array();
$sDstDauer = '00:00:00';
$tDstDauer = Date($sDstDauer);

$result = $connection->query($sql);
if ($result) {
	$in = '[';
	$tZeitVorher = time(  );
	
	while($row = $connection->fetch_array($result)){
		
		$tZeit = strtotime( $row['zeit'] );	
		$ZeilenZaehler++;	
		
		if($ZeilenZaehler == 1){ // Erste Stempelung des Tages 
			$tDstDauer = time() ; //
			$sDstDauer = '';
			$sPause = '';
		} else if ($ZeilenZaehler > 1 ) {
			
			// Dauer 
			$tDstDauer = ($tZeit - $tZeitVorher )  ; // - 3600 Ohne date_default_timezone_set('UTC'), welches mit alter PHP-Verion nicht funktioniert und ohne Schreibrecht auf die PHP.INI muss die 1 Stunde hier "manuell" abgezogen werden 

			if ($row['bdefunktion'] == 'kommen') {		
				$sDstDauer = '';
			} else {
				$sDstDauer = round(floor($tDstDauer)/60);
			}

			// Pause 
			$row['pause'] = round($row['pause']);
			if ( $row['pause'] == '0' ) {
				$sPause = ' ';	
			} else {
				$sPause =    $row['pause'] ;
			}
		} else {

		}

		$in .= '{';
		$in .= '"bdefunktion":"'.$row['bdefunktion'].'",';
		$in .= '"zeit":"'.$row['zeit'].'",';

		if( $tDstDauer < 900) {
			$in .= '"dauer":"'.$sDstDauer.'",';
			
		} else {
			$in .= '"dauer":"'.$sDstDauer.'",';
		}
		
		$in .= '"pause":"'.$sPause.'"';

		$in .= '},';
		
		$tZeitVorher = $tZeit;

	}
}

if (!$ZeilenZaehler > 0 ){
	$in = '[';
	$in .=  '{"bdefunktion":"Keine Stempelungen","zeit":"","dauer":" "},';
}

$out = utf8_encode ($in);
$out = substr($out, 0, -1);
$out .= ']';

echo $out; 


?> 