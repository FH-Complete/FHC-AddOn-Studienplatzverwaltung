<?php

/* Copyright (C) 2014 Technikum-Wien
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
 * Authors: Werner Masik <werner@gefi.at>
 *
 */

/**
 * Studienjahr in Spezialcode der Aqa für Webservice umwandeln.
 * 2013/14 = 20, 2014/15 = 21, usw.
 * @param integer $code Jahr des Wintersemesters
 */
function translateStudienjahr2AqaCode($studienjahr)
{
	return $studienjahr - 1993;
}

/**
 * 
 * @param integer $code   20 = 2013/14, 21 = 2014/15
 * @return integer Jahr des Wintersemesters
 */
function translateAqaCode2Studienjahr($code)
{
	return $code + 1993;
}

/**
 * SimpleXML ist tw. sehr kritisch was den Namespace betrifft und weigert sich
 * z.B. relative Namespace-Pfade zu akzeptieren. Mit dieser Funktion kann man
 * den Namespace entfernen, damit man das XML trotzdem einlesen kann.
 * @param string $xml_string
 * @return string xml ohne Namespace
 */
function removeNamespace($xml_string) {
		// Gets rid of all namespace definitions 
		$xml_string = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $xml_string);

		// Gets rid of all namespace references
		$xml_string = preg_replace('/[a-zA-Z]+:([a-zA-Z]+[=>])/', '$1', $xml_string);
		return $xml_string;
}

/**
 * Studiengangskennzahl in String mit Format 0000 umwandeln
 * @param type $kz
 */
function formatStudiengangKz($kz)
{
	return str_pad($kz, 4, "0", STR_PAD_LEFT);
}

/**
 * @param string $semester z.B. WS2013
 * @return string Meldedatum als String z.B. '15.11.2013'
 */
function semester2BISDatum($semester)
{
	$datumStr = '';
	if (!isset($semester)) return false;
	$jahr = substr($semester, 2) + 0;
	if (substr($semester,0,2) == 'WS')
	{
		$datumStr = '15.11.'.$jahr;
	}	
	else
	{
		$datumStr = '15.04.'.$jahr;
	}
	return $datumStr;
}

/**
 * @param date BIS-Meldedatum als UNIX-Datum
 * @return string Semester z.B. 'WS2013'
 */
function bisDatum2Semester($datum)
{
	$semester = '';
	$jahr = date("Y", $datum);
	$monat = date("m", $datum);
	if ($monat == 11) $semester = 'WS';
	else $semester = 'SS';
	$semester.=$jahr;
	return $semester;
}
