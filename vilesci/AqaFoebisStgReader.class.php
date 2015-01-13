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
 */

require_once(__DIR__ .'/../../../include/functions.inc.php');
require_once(__DIR__ .'/../../../include/appdaten.class.php');
require_once(__DIR__ .'/../../../include/studiengang.class.php');
require_once(__DIR__ .'/../include/aqa_foebis_stg.class.php');
require_once(__DIR__ .'/../include/functions.inc.php');

/**
 *  Liest die FÖBISDaten für einen Studiengang vom Ministerium ein. Die Daten werden
 *  über ein Webservice bereitgestellt. Dieses basiert offensichtlich auf den
 *  Reports die auch in der Online Applikation zu 'Erhalterportal' finden sind,
 *  daher ist die XML-Struktur auch so (seltsam) wie sie ist.
 *  
 *  Bsp.:
 *  <code>
 * <ListFoebisAbrechnungStudiengangResult xmlns="https://www.aq.ac.at/BISWS/FOEBIS/WebServices/Services/ErhalterService">
 *  <Report xmlns="FOEBISAbrechnungStudiengang" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" Name="FOEBISAbrechnungStudiengang" xsi:schemaLocation="FOEBISAbrechnungStudiengang https://www.aq.ac.at/ReportServer?%2FApplikationen%2FFOEBIS%2FWebservice%2FFOEBISAbrechnungStudiengang&amp;rs%3AFormat=XML&amp;rc%3ASchema=True">
 *    <Tablix1>
 *      <Group1_Collection>
 *        <Group1 R3FoerderbetragGesamtTatsaechlich1="529333.32" StgKz2="0227 Biomedizinisches Ingenieurwesen/Biomedical Engineering" Textbox101="396999.99" Textbox393="661666.65">
 *          <Details_Collection>
 *            <Details AQ="18" FoerderGruppeBez="technisch" Foerdersatz="7940.00" Jahr="2013" Monatlang1="10" NPZ="200" OrgFormBez="VZ" R1FoerderbetragGesamtTatsaechlich="132333.33" R1PlaetzeBezahlt="200" R2FoebisAktiveGesamt="230" R2FoerderbetragGesamt="132333.33" R2PlaetzeBezahltGesamt="200" R2R1FoebisAktiveKorrGesamt="0" R2R1FoerderbetragKorrGesamt="0.00" RStd="6" Runde="1" Semester="WS" StgArtBez="Ba" StgKz="0227"/>
 *            <Details AQ="18" FoerderGruppeBez="technisch" Foerdersatz="7940.00" Jahr="2013" Monatlang1="11" NPZ="200" OrgFormBez="VZ" R1FoerderbetragGesamtTatsaechlich="132333.33" R1PlaetzeBezahlt="200" R2FoebisAktiveGesamt="230" R2FoerderbetragGesamt="132333.33" R2PlaetzeBezahltGesamt="200" R2R1FoebisAktiveKorrGesamt="0" R2R1FoerderbetragKorrGesamt="0.00" RStd="6" Runde="1" Semester="WS" StgArtBez="Ba" StgKz="0227"/>
 *            <Details AQ="18" FoerderGruppeBez="technisch" Foerdersatz="7940.00" Jahr="2013" Monatlang1="12" NPZ="200" OrgFormBez="VZ" R1FoerderbetragGesamtTatsaechlich="132333.33" R1PlaetzeBezahlt="200" R2FoebisAktiveGesamt="230" R2FoerderbetragGesamt="132333.33" R2PlaetzeBezahltGesamt="200" R2R1FoebisAktiveKorrGesamt="0" R2R1FoerderbetragKorrGesamt="0.00" RStd="6" Runde="1" Semester="WS" StgArtBez="Ba" StgKz="0227"/>
 *            <Details AQ="18" FoerderGruppeBez="technisch" Foerdersatz="7940.00" Jahr="2014" Monatlang1="01" NPZ="200" OrgFormBez="VZ" R1PlaetzeBezahlt="200" R2FoebisAktiveGesamt="230" R2FoerderbetragGesamt="132333.33" R2FoerderbetragGesamtTatsaechlich="132333.33" R2PlaetzeBezahltGesamt="200" RStd="6" Runde="2" Semester="WS" StgArtBez="Ba" StgKz="0227"/>
 *            <Details AQ="18" FoerderGruppeBez="technisch" Foerdersatz="7940.00" Jahr="2014" Monatlang1="02" NPZ="200" OrgFormBez="VZ" R1PlaetzeBezahlt="200" R2FoebisAktiveGesamt="230" R2FoerderbetragGesamt="132333.33" R2FoerderbetragGesamtTatsaechlich="132333.33" R2PlaetzeBezahltGesamt="200" RStd="6" Runde="2" Semester="WS" StgArtBez="Ba" StgKz="0227"/>
 *            <Details AQ="18" FoerderGruppeBez="technisch" Foerdersatz="7940.00" Jahr="2014" Monatlang1="03" NPZ="200" OrgFormBez="VZ" R1PlaetzeBezahlt="200" R2FoebisAktiveGesamt="230" R2FoerderbetragGesamt="132333.33" R2FoerderbetragGesamtTatsaechlich="132333.33" R2PlaetzeBezahltGesamt="200" RStd="6" Runde="2" Semester="WS" StgArtBez="Ba" StgKz="0227"/>
 * 
 *   usw.
 * </code>
 *  
 */
class AqaFoebisStgReader 
{
	public $errormsg;
	
	public function parse($stgKz,$studienjahr,$xml) 
	{		
		$simpleXml = simplexml_load_string(removeNamespace($xml));
		// Daten leer?
		if (!isset($simpleXml->Tablix1->Group1_Collection)) 
		{
			return true;
		}
		$details = $simpleXml->Tablix1->Group1_Collection->Group1->Details_Collection; //->Details;		
		
		$aqa_foebis_stg = new aqa_foebis_stg();	
		$aqa_foebis_stg->deleteByStgKz($stgKz, $studienjahr);
		
		foreach ($details->Details as $detail)
		{
			if ($this->importRow($stgKz,$detail) === false)
			{
				return false;
			}
		}				
		
		return true;		
	}	

	
	/**
	 * Erzeugt aus Jahr und Semesterbezeichnung den String für die 
	 * Studiensemesterbezeichnung wie sie in der Datenbank steht.<br/>
	 * z.B: Jahr=2014, Monat=1 => WS2013</br>
	 * <p>Anm.: Das Studiensemester bei der FÖBIS-Abrechnung beginnt im
	 * Oktober und nicht im September! Das wird hier jedoch nicht berücksichtigt.
	 * Die Zuordnung wird nur anhand der Attribute Studienjahr und Semester (WS, SS)
	 * gemacht.
	 * </p>
	 * @param integer $jahr Jahr lt. Kalender; nicht Studienjahr o.ä.
	 * @param integer $monat Monat
	 * @return string Studiensemester Kurzbezeichnung z.B. WS2014
	 */
	private function getStudienemesterKurzbz($jahr, $monat, $semesterBez)
	{
		// bei der FÖBis-Abrechnung geht das Semester bis März!
		if ($monat != null && $semesterBez == 'WS' && $monat <= 3)
		{
			return 'WS'.($jahr-1);
		}
		return (string)$semesterBez.(string)$jahr;
	}
	
	private function importRow($stgKz, $rowData)
	{
			
		$studiensemester_kurzbz = $this->getStudienemesterKurzbz(
				$rowData['Jahr'], $rowData['Monatlang1'], $rowData['Semester']);
		
		$aqa_foebis_stg = new aqa_foebis_stg();	
		$aqa_foebis_stg->studiengang_kz = $stgKz;
		$aqa_foebis_stg->jahr = (int)$rowData['Jahr'];
		$aqa_foebis_stg->monat = (int)$rowData['Monatlang1'];
		$aqa_foebis_stg->runde = (int)$rowData['Runde'];
		$aqa_foebis_stg->studiensemester_kurzbz = $studiensemester_kurzbz;
		$aqa_foebis_stg->stgartbez = $this->translateArt($rowData['StgArtBez']);
		$aqa_foebis_stg->orgform_kurzbz = $rowData['OrgFormBez'];
		$aqa_foebis_stg->regelstudiendauer = (int)$rowData['RStd'];
		$aqa_foebis_stg->foerdergruppe = $rowData['FoerderGruppeBez'];
		$aqa_foebis_stg->npz = (int)$rowData['NPZ'];
		$aqa_foebis_stg->aq = (int)$rowData['AQ'];
		$aqa_foebis_stg->r1_plaetze_bezahlt = isset($rowData['R1PlaetzeBezahlt']) ? (int)$rowData['R1PlaetzeBezahlt'] : null;
		$aqa_foebis_stg->r2_plaetze_bezahlt = isset($rowData['R2PlaetzeBezahltGesamt'])?(int)$rowData['R2PlaetzeBezahltGesamt'] : null;
		$aqa_foebis_stg->r3_plaetze_bezahlt = isset($rowData['R3PlaetzeBezahltGesamt']) ? (int)$rowData['R3PlaetzeBezahltGesamt'] : null;
		$aqa_foebis_stg->r2_foebisaktive = isset($rowData['R2FoebisAktiveGesamt']) ? (int)$rowData['R2FoebisAktiveGesamt'] : null;
		$aqa_foebis_stg->r3_foebisaktive = isset($rowData['R3FoebisAktiveGesamt']) ? (int)$rowData['R3FoebisAktiveGesamt'] : null;
		$aqa_foebis_stg->r2r1_foebisaktive_korr = isset($rowData['R2R1FoerderbetragKorrGesamt']) ? (int)$rowData['R2R1FoerderbetragKorrGesamt'] : null;
		$aqa_foebis_stg->r3r2_foebisaktive_korr = isset($rowData['R3R2FoebisAktiveKorrGesamt']) ? (int)$rowData['R3R2FoebisAktiveKorrGesamt'] : null;
				
		if ($aqa_foebis_stg->save() === false)
		{
			$this->errormsg = $aqa_foebis_stg->errormsg;
			return false;
		}		
		return true;	
	}
	
	private function getFoebis($rowData)
	{
		$monat = (int)$rowData['Monat'];
		if ($monat>=10 && $monat <=12)
		{
			// In Runde 1 wird die Förderung nur von der NPZ berechnet,
			// da die BIS-Meldung erst im November erfolgt. Wenn man in
			// den Monaten 10, 11, 12 ein Abfrage macht, kann man nur die
			// Runde 1 abfragen. Wenn man jedoch historische Daten holt,
			// kann man die Auswertung für die Runde 2 befragen, welche
			// dann
			if (isset($rowData['R2PlaetzeBezahltGesamt']))
			{
				return (int)$rowData['R2PlaetzeBezahltGesamt'];
			}
			else 
			{
				return (int)$rowData['R1PlaetzeBezahlt'];
			}
		}
		else if ($rowData['Runde'] == '2')
		{
			
		}
	}
	
	/**
	 * Helper der Studiengangsart von der Notation der AQ zum FHComplete
	 * übersetzt. 
	 * @param type $art
	 * @return string
	 */
	private function translateArt($art)
	{
		if ($art == 'Ba') return 'b';
		if ($art == 'Ma') return 'm';
	}	
		
	
}

?>
