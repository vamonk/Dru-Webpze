<?php

/**
 * Datei       	: info.php
 * Beschreibung	: Lädt informelle Daten zum aktuellen Mitarbeiter 
 *
 * Author      	: VolkerA
 * Erstellt    	: 06.09.2020
 * Historie		: 07.06.2021 - Neues Select für Fehlzeit und diese zusätzlich abgezogen 
 * 
 * 
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
// Arbeit
//-----------------------------------------------------------------------------

// if ($datasetname = 'info'){

$sql = <<<SQL
SELECT (IF "anwesend" = 1 THEN 'ja' ELSE 'nein' END IF) anwesend, "users"."name" AS login_name, "mitarb"."name" AS mitarb_name,
(
SELECT COALESCE(SUM(istzeit)/60.0,0)
FROM lo_pze_tagesdaten,  lo_persstamm, mitarb, users 
WHERE ( MONTHS(datum) = MONTHS( TODAY() ) AND DATE(datum) < DATE(TODAY())  )
AND lo_persstamm.tl_mitarbnr = mitarb.kuerzel 
AND users.id = mitarb.user_id 
AND lo_pze_tagesdaten.persnr = lo_persstamm.persnr
AND users.name  = '$userlogin'
AND 
DATE(gueltigab) >= (
SELECT DATE(MAX(gueltigab))
FROM lo_pze_tagesdaten,  lo_persstamm

WHERE DATE(datum) < today() AND MONTH(DATE(datum)) = MONTH( today() )AND YEAR(DATE(datum)) = YEAR( today() )

AND lo_persstamm.tl_mitarbnr = mitarb.kuerzel 
AND users.id = mitarb.user_id 
AND lo_pze_tagesdaten.persnr = lo_persstamm.persnr
AND users.name  = '$userlogin')
) as istzeit,
(
SELECT COALESCE(SUM(arbeitszeit)/60,0)
FROM lo_pze_tagesdaten,  lo_persstamm
WHERE (MONTHS(datum) = MONTHS(today())-1 )
AND lo_persstamm.tl_mitarbnr = mitarb.kuerzel 
AND users.id = mitarb.user_id 
AND lo_pze_tagesdaten.persnr = lo_persstamm.persnr
AND users.name  = '$userlogin'
AND 
DATE(gueltigab) >= (
SELECT DATE(MAX(gueltigab))
FROM lo_pze_tagesdaten,  lo_persstamm, mitarb, users
WHERE ( MONTHS(datum) = MONTHS( TODAY() )-1 ) 
AND lo_persstamm.tl_mitarbnr = mitarb.kuerzel 
AND users.id = mitarb.user_id 
AND lo_pze_tagesdaten.persnr = lo_persstamm.persnr
AND users.name  = '$userlogin')
) as vormonat
,
(
SELECT COALESCE(SUM(sollzeit)/60.0,0)
FROM lo_pze_tagesdaten,  lo_persstamm, mitarb, users 
WHERE ( MONTHS(datum) = MONTHS( TODAY() ) AND DATE(datum) < DATE(TODAY())  ) 
AND lo_persstamm.tl_mitarbnr = mitarb.kuerzel 
AND users.id = mitarb.user_id 
AND lo_pze_tagesdaten.persnr = lo_persstamm.persnr
AND users.name  = '$userlogin'
AND 
DATE(gueltigab) >= (
SELECT DATE(MAX(gueltigab))
FROM lo_pze_tagesdaten,  lo_persstamm

WHERE DATE(datum) < today() AND MONTH(DATE(datum)) = MONTH( today() )AND YEAR(DATE(datum)) = YEAR( today() )


AND lo_persstamm.tl_mitarbnr = mitarb.kuerzel 
AND users.id = mitarb.user_id 
AND lo_pze_tagesdaten.persnr = lo_persstamm.persnr
AND users.name  = '$userlogin')
) as sollarbeitszeit,
(
SELECT COALESCE(SUM(fehlzeit)/60.0,0)
FROM lo_pze_tagesdaten,  lo_persstamm, mitarb, users 
WHERE ( MONTHS(datum) = MONTHS( TODAY() ) AND DATE(datum) < DATE(TODAY())  AND fehlzeit_id = 16) 
AND lo_persstamm.tl_mitarbnr = mitarb.kuerzel 
AND users.id = mitarb.user_id 
AND lo_pze_tagesdaten.persnr = lo_persstamm.persnr
AND users.name  = '$userlogin'
AND 
DATE(gueltigab) >= (
SELECT DATE(MAX(gueltigab))
FROM lo_pze_tagesdaten,  lo_persstamm

WHERE DATE(datum) < today() AND MONTH(DATE(datum)) = MONTH( today() )AND YEAR(DATE(datum)) = YEAR( today() )
AND fehlzeit_id = 16

AND lo_persstamm.tl_mitarbnr = mitarb.kuerzel 
AND users.id = mitarb.user_id 
AND lo_pze_tagesdaten.persnr = lo_persstamm.persnr
AND users.name  = '$userlogin')
) as fehlzeit,

(istzeit - fehlzeit - sollarbeitszeit) mehrminderarbeit

FROM mitarb INNER JOIN users ON
mitarb.user_id = users.id
WHERE "users"."name" = '$userlogin';
SQL;

//-----------------------------------------------------------------------------
// Urlaub 
//-----------------------------------------------------------------------------

$sqlsql = <<<SQL
SELECT
lo_pze_mitarb_uebertrag.jahr as jahr,
urlaub_anspruch_std as anspruch,
(SELECT FIRST saldo_urlaub_minuten/60.0
FROM lo_pze_tagesdaten, lo_persstamm, users, mitarb 
WHERE lo_pze_tagesdaten.buchkr = 1 
AND users.name = '$userlogin' 
AND lo_pze_tagesdaten.persnr = lo_persstamm.persnr 
AND users.id = mitarb.user_id 
AND lo_persstamm.tl_mitarbnr = mitarb.kuerzel
AND YEAR(datum) = YEAR(TODAY()) 
AND DATE(datum) < DATE(TODAY()) 
ORDER BY datum DESC
) 
as genommen,
lo_pze_mitarb_uebertrag.urlaub_vorj_std as vorjahr,
anspruch - genommen + vorjahr  as rest,

(SELECT FIRST  COALESCE(saldo_urlaub_minuten/60.0-genommen,0)
 FROM lo_pze_tagesdaten, lo_persstamm, users, mitarb
 WHERE lo_pze_tagesdaten.buchkr = 1 
 AND users.name = '$userlogin'
 AND lo_pze_tagesdaten.persnr = lo_persstamm.persnr
 AND users.id = mitarb.user_id
 AND lo_persstamm.tl_mitarbnr = mitarb.kuerzel
 AND YEAR(datum) = YEAR(TODAY()) 
 AND DAY(datum) >= DAY(TODAY())  
 ORDER BY datum DESC
 )	as geplant,

(SELECT FIRST saldo_ueberstunden/60.0 
FROM lo_pze_tagesdaten, lo_pze_mitarb_uebertrag, lo_persstamm, users, mitarb 
WHERE lo_pze_tagesdaten.persnr = lo_persstamm.persnr 
AND users.id = mitarb.user_id 
AND lo_persstamm.tl_mitarbnr = mitarb.kuerzel 
AND lo_pze_mitarb_uebertrag.persnr = lo_persstamm.persnr AND users.name = '$userlogin'
AND date(datum) < date(today())
ORDER BY datum DESC) 
as saldo_ueberstunden,
      
       
       (rest - geplant) AS verfuegbar


FROM
lo_pze_mitarb_uebertrag, 
lo_persstamm, 
"users",
mitarb
WHERE
"users"."id" = "mitarb"."user_id"
AND 
lo_persstamm.tl_mitarbnr = mitarb.kuerzel
AND
lo_pze_mitarb_uebertrag.persnr = lo_persstamm.persnr 
AND
"users"."name" = '$userlogin'
AND
jahr= YEAR(TODAY())  
AND 
DATE(gueltigab) >= (
SELECT
DATE(MAX(gueltigab))
FROM
lo_pze_mitarb_uebertrag, 
lo_persstamm, 
"users",
mitarb
WHERE
"users"."id" = "mitarb"."user_id"
AND 
lo_persstamm.tl_mitarbnr = mitarb.kuerzel
AND
lo_pze_mitarb_uebertrag.persnr = lo_persstamm.persnr 
AND
"users"."name" = '$userlogin'
AND
jahr= YEAR(TODAY()) 
);
SQL;

//-----------------------------------------------------------------------------
// Arbeit
//-----------------------------------------------------------------------------

$result = $connection->query($sql);
$in = '';

	$in = '[';
	while($row = $connection->fetch_array($result)){
		$in .= '{';
		$in .= '"anwesend":"'.$row['anwesend'].'",';
		$in .= '"login_name":"'.$row['login_name'].'",';
		$in .= '"mitarb_name":"'.$row[ 'mitarb_name'].'",';
		$in .= '"monat":"'.$row[ 'istzeit'].'",';
		$in .= '"vormonat":       "'.$row[ 'vormonat'].'",';
		$in .= '"sollarbeitszeit":"'.$row[ 'sollarbeitszeit'].'",';
		$in .= '"mehrminderarbeit":"'.$row[ 'mehrminderarbeit'].'",';

	}

//-----------------------------------------------------------------------------
// Urlaub 
//-----------------------------------------------------------------------------

$result = $connection->query($sqlsql);

	$rowcount=0; 
	while($row = $connection->fetch_array($result)){
		// $in .= '{';
		$in .= '"jahr":"'.$row['jahr'].'",';
		$in .= '"anspruch":"'.$row['anspruch'].'",';
		$in .= '"genommen":"'.$row['genommen'].'",';
		$in .= '"vorjahr":"'.$row['vorjahr'].'",';
		$in .= '"rest":"'.$row['rest'].'",';
		$in .= '"geplant":"'.$row['geplant'].'",';
		$in .= '"verfuegbar":"'.$row['verfuegbar'].'",';
		$in .= '"saldo_ueberstunden":"'.$row['saldo_ueberstunden'].'"';
	    $in .= '}';
		$rowcount++;
	}

	if($rowcount < 1 ) {
	 	$in .=  '"jahr":"0","anspruch":"0","genommen":"0","vorjahr":"0","rest":"0","geplant":"0","saldo_ueberstunden":"0"}';
	}

$out = utf8_encode ($in);
$out .= ']';

echo $out; 

?> 