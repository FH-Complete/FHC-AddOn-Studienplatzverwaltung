<?php
/* Copyright (C) 2013 FH Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Werner Masik <masik@technikum-wien.at>
 * 
 * FH-Complete Addon Studienplatzverwaltung Datenbank Check
 *
 * Prueft und aktualisiert die Datenbank
 */
require_once('../../config/system.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');

// Datenbank Verbindung
$db = new basis_db();

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<title>Addon Datenbank Check</title>
</head>
<body>
<h1>Addon Datenbank Check</h1>';

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('basis/addon'))
{
	exit('Sie haben keine Berechtigung für die Verwaltung von Addons');
}

echo '<h2>Aktualisierung der Datenbank</h2>';

/**
 *  
 * Code fuer die Datenbankanpassungen
 * 
 */ 

/**  
 *  Schema für Addons anlegen (Kopie aus Kompetenzen Addon)
 *  @todo duplizierten Code in 'Utility'-Library für Addons umwandeln
 */
if($result = $db->db_query("SELECT schema_name FROM information_schema.schemata WHERE schema_name = 'addon'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "CREATE SCHEMA addon;
				GRANT USAGE ON SCHEMA addon TO vilesci;
				GRANT USAGE ON SCHEMA addon TO web;
				";
		
		if(!$db->db_query($qry))
			echo '<strong>Schema addon: '.$db->db_last_error().'</strong><br>';
		else 
			echo ' Neues Schema addon hinzugefügt<br>';
	}
}


/*
 *  Tabelle für FÖBISAktive
 */
 
if(!$result = @$db->db_query("SELECT 1 FROM addon.tbl_aqa_foebis_person"))
{

	$qry = 'CREATE TABLE addon.tbl_aqa_foebis_semester
			(
				foebis_semester_id serial primary key,
				matrikelnr char(15),
				studiengang_kz integer,
				orgform_kurzbz varchar(5),
				regelstudiendauer integer,
				ausbildungssemester integer,
				studiensemester varchar(16) references public.tbl_studiensemester,
                guthaben integer,
                gefoerdert integer,   
                maxguthaben integer,
                stud_status integer,
                meldedatum date,
                updateamum timestamp without time zone,
 				updatevon character varying(32),
 				insertamum timestamp without time zone default now(),
 				insertvon character varying(32)                             
			);
                        
            GRANT SELECT, UPDATE ON SEQUENCE addon.tbl_aqa_foebis_semester_foebis_semester_id_seq TO vilesci;
            GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_aqa_foebis_semester TO vilesci;


			CREATE TABLE addon.tbl_aqa_foebis_person
			(
				foebis_person_id serial primary key,
				matrikelnr char(15),
				studiengang_kz integer,
				orgform_kurzbz varchar(5),
				regelstudiendauer integer,
				ausbildungssemester integer,
				studiensemester varchar(16) references public.tbl_studiensemester,
                guthaben integer,
                gefoerdert integer,   
                maxguthaben integer,
                stud_status integer,
                meldedatum date,
                foerderrelevant boolean default false,
                updateamum timestamp without time zone,
 				updatevon character varying(32),
 				insertamum timestamp without time zone default now(),
 				insertvon character varying(32)                             
			);
                        
            GRANT SELECT, UPDATE ON SEQUENCE addon.tbl_aqa_foebis_person_foebis_person_id_seq TO vilesci;
            GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_aqa_foebis_person TO vilesci;
			
			CREATE TABLE addon.tbl_aqa_foebis_stg
			(
				foebis_stg_id serial primary key,
				studiengang_kz integer,
				regelstudiendauer integer,
				orgformBez varchar(5),
				stgArtBez varchar(5),
				jahr integer,
				monat integer,
				runde integer,
				studiensemester varchar(16) references public.tbl_studiensemester,
                foerdergruppe varchar(100),
				aq integer,
				npz integer,
				r1_plaetze_bezahlt integer,
				r2_plaetze_bezahlt integer,
				r3_plaetze_bezahlt integer,
				r2_foebisaktive integer,
				r3_foebisaktive integer,
				r2r1_foebisaktive_korr integer,
				r3r2_foebisaktive_korr integer,
                updateamum timestamp without time zone,
 				updatevon character varying(32),
 				insertamum timestamp without time zone default now(),
 				insertvon character varying(32)                             
			);
                        
            GRANT SELECT, UPDATE ON SEQUENCE addon.tbl_aqa_foebis_stg_foebis_stg_id_seq TO vilesci;
            GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_aqa_foebis_stg TO vilesci;

            ';

	if(!$db->db_query($qry))
		echo '<strong>addon.tbl_foebisaktiv: '.$db->db_last_error().'</strong><br>';
	else 
		echo ' addon.tbl_foebisaktiv: Tabelle addon.tbl_foebisaktiv hinzugefuegt!<br>';

}


/*
 * Umschichtungstabelle anlegen (dient als Verzeichnis; die eigentlichen 
 * Umschichtungsdaten werden als XML in allgemeiner Tabelle für 
 * Applikationsdaten gespeichert.)
 */
/* @ todo anpassen an skelett
if(!$result = @$db->db_query("SELECT 1 FROM addon.tbl_umschichtung"))
{

	$qry = 'CREATE TABLE addon.tbl_umschichtung
			(
				uv_id serial primary key,
				studiensemester varchar(16) references public.tbl_studiensemester,
                                status varchar(255),
                                status_datum date
			);
                        
                 GRANT SELECT, UPDATE ON SEQUENCE addon.tbl_umschichtung_uv_id_seq TO vilesci;
                 GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_umschichtung TO vilesci;

                ';

	if(!$db->db_query($qry))
		echo '<strong>addon.tbl_umschichtung: '.$db->db_last_error().'</strong><br>';
	else 
		echo ' addon.tbl_umschichtung: Tabelle addon.tbl_umschichtung hinzugefuegt!<br>';

}
*/

/**
 * Neue Berechtigung für das Addon hinzufügen
 * 
 * @todo diesen Teil auch zum Template hinzufügen?
 */
if($result = $db->db_query("SELECT * FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='addon/studienplatzverwaltung'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) 
				VALUES('addon/studienplatzverwaltung','Addon Studienplatzverwaltung');";

		if(!$db->db_query($qry))
			echo '<strong>Berechtigung: '.$db->db_last_error().'</strong><br>';
		else 
			echo 'Neue Berechtigung addon/studienplatzverwaltung hinzugefuegt!<br>';
	}
}


echo '<br>Aktualisierung abgeschlossen<br><br>';
echo '<h2>Gegenprüfung</h2>';


// Liste der verwendeten Tabellen / Spalten des Addons
//  @todo anpassen an klassen skelett
 $tabellen=array(
//	"addon.tbl_umschichtung"  => array("uv_id", "studiensemester","status", 
//            "status_datum"),
);


$tabs=array_keys($tabellen);
$i=0;
foreach ($tabellen AS $attribute)
{
	$sql_attr='';
	foreach($attribute AS $attr)
		$sql_attr.=$attr.',';
	$sql_attr=substr($sql_attr, 0, -1);

	if (!@$db->db_query('SELECT '.$sql_attr.' FROM '.$tabs[$i].' LIMIT 1;'))
		echo '<BR><strong>'.$tabs[$i].': '.$db->db_last_error().' </strong><BR>';
	else
		echo $tabs[$i].': OK - ';
	flush();
	$i++;
}
?>
