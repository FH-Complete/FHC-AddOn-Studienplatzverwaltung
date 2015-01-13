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
require_once(__DIR__ .'/../../../include/bisorgform.class.php');
require_once(__DIR__ .'/../../../include/datum.class.php');
require_once(__DIR__ .'/../include/aqa_foebis_stg.class.php');
require_once(__DIR__ .'/../include/aqa_foebis_person.class.php');
require_once(__DIR__ .'/../include/functions.inc.php');

/**
 *  Liest die FÖBISDaten für einen Person vom Ministerium ein. Die Daten werden
 *  über ein Webservice bereitgestellt. Dieses basiert offensichtlich auf den
 *  Reports die auch in der Online Applikation zu 'Erhalterportal' finden sind.
 *  
 *  Bsp.:
 *  <code>
 * <ListFoebisAbrechnungPersonResult>
            <Report xsi:schemaLocation="FOEBISAbrechnungPerson https://www.aq.ac.at/ReportServer?%2FApplikationen%2FFOEBIS%2FWebservice%2FFOEBISAbrechnungPerson&amp;rs%3AFormat=XML&amp;rc%3ASchema=True" Name="FOEBISAbrechnungPerson" xmlns="FOEBISAbrechnungPerson">
               <Tablix1>
                  <Details_Collection>
                     <Details MeldeDatum="15.11.2005" StgKz="0254" PersKz="0510254050" OrgFormCode="1" StudStatusCode="1" BIS_RegelStDauer="6" Ausbildungssemester="1" FOEBIS_MaxFoeGuthaben="46" FOEBIS_FoeGuthaben="40"/>
                     <Details MeldeDatum="15.04.2006" StgKz="0254" PersKz="0510254050" OrgFormCode="1" StudStatusCode="2" BIS_RegelStDauer="6" Ausbildungssemester="1" FOEBIS_MaxFoeGuthaben="46" FOEBIS_FoeGuthaben="40"/>
                     <Details MeldeDatum="15.11.2006" StgKz="0254" PersKz="0510254050" OrgFormCode="1" StudStatusCode="2" BIS_RegelStDauer="6" Ausbildungssemester="1" FOEBIS_MaxFoeGuthaben="46" FOEBIS_FoeGuthaben="40"/>
                     <Details MeldeDatum="15.04.2007" StgKz="0254" PersKz="0510254050" OrgFormCode="1" StudStatusCode="2" BIS_RegelStDauer="6" Ausbildungssemester="1" FOEBIS_MaxFoeGuthaben="46" FOEBIS_FoeGuthaben="40"/>
                     <Details MeldeDatum="15.11.2007" StgKz="0254" PersKz="0510254050" OrgFormCode="1" StudStatusCode="2" BIS_RegelStDauer="6" Ausbildungssemester="1" FOEBIS_MaxFoeGuthaben="46" FOEBIS_FoeGuthaben="40"/>
                     <Details MeldeDatum="15.04.2008" StgKz="0254" PersKz="0510254050" OrgFormCode="1" StudStatusCode="2" BIS_RegelStDauer="6" Ausbildungssemester="1" FOEBIS_MaxFoeGuthaben="46" FOEBIS_FoeGuthaben="40"/>
                     <Details MeldeDatum="15.11.2011" StgKz="0254" PersKz="0510254050" OrgFormCode="1" StudStatusCode="4" BIS_RegelStDauer="6" Ausbildungssemester="1" FOEBIS_MaxFoeGuthaben="46" FOEBIS_FoeGuthaben="0"/>
                     <Details MeldeDatum="15.11.2008" StgKz="0254" PersKz="0510254050" OrgFormCode="1" StudStatusCode="2" BIS_RegelStDauer="6" Ausbildungssemester="1" FOEBIS_MaxFoeGuthaben="46" FOEBIS_FoeGuthaben="40"/>
                     <Details MeldeDatum="15.04.2009" StgKz="0254" PersKz="0510254050" OrgFormCode="1" StudStatusCode="2" BIS_RegelStDauer="6" Ausbildungssemester="1" FOEBIS_MaxFoeGuthaben="46" FOEBIS_FoeGuthaben="40"/>
                     <Details MeldeDatum="15.11.2009" StgKz="0254" PersKz="0510254050" OrgFormCode="1" StudStatusCode="2" BIS_RegelStDauer="6" Ausbildungssemester="1" FOEBIS_MaxFoeGuthaben="46" FOEBIS_FoeGuthaben="40"/>
                     <Details MeldeDatum="15.04.2010" StgKz="0254" PersKz="0510254050" OrgFormCode="1" StudStatusCode="2" BIS_RegelStDauer="6" Ausbildungssemester="1" FOEBIS_MaxFoeGuthaben="46" FOEBIS_FoeGuthaben="40"/>
                     <Details MeldeDatum="15.11.2010" StgKz="0254" PersKz="0510254050" OrgFormCode="1" StudStatusCode="2" BIS_RegelStDauer="6" Ausbildungssemester="1" FOEBIS_MaxFoeGuthaben="46" FOEBIS_FoeGuthaben="40"/>
                     <Details MeldeDatum="15.04.2011" StgKz="0254" PersKz="0510254050" OrgFormCode="1" StudStatusCode="2" BIS_RegelStDauer="6" Ausbildungssemester="1" FOEBIS_MaxFoeGuthaben="46" FOEBIS_FoeGuthaben="40"/>
                  </Details_Collection>
               </Tablix1>
            </Report>
         </ListFoebisAbrechnungPersonResult>
 *  
 */
class AqaFoebisPersonReader 
{
	public $errormsg;
	private $datum;
	private $bisorgformList;
	
	public function __construct()
	{
		// init helper class
		$this->datum = new datum();
		// init orgformen
		$bisorgform = new bisorgform();
		$this->bisorgformList = $bisorgform->getList();
		var_dump($this->bisorgformList);
	}


	public function parse($personKz,$xml) 
	{		
		$simpleXml = simplexml_load_string(removeNamespace($xml));
		// Daten leer?
		if (!isset($simpleXml->Tablix1->Details_Collection)) 
		{
			return true;
		}
		$details = $simpleXml->Tablix1->Details_Collection; //->Details;		
		
		$aqa_foebis_person = new aqa_foebis_person();	
		$aqa_foebis_person->deleteByPersonKz($personKz);
		
		foreach ($details->Details as $detail)
		{
			if ($this->importPersonRow($personKz,$detail) === false)
			{
				return false;
			}
		}				
		
		return true;		
	}	

	private function importPersonRow($personKz, $rowData)
	{
			
		//var_dump($rowData);
		$aqa_foebis_person = new aqa_foebis_person();	
		$aqa_foebis_person->meldedatum = 
			$this->datum->mktime_datum((string)$rowData['MeldeDatum']);
		$studiensemester_kurzbz = bisDatum2Semester($aqa_foebis_semester->meldedatum);
		
		$aqa_foebis_person->matrikelnr = $personKz;
		$aqa_foebis_person->studiengang_kz = (int)$rowData['StgKz'];
		$aqa_foebis_person->studiensemester_kurzbz = $studiensemester_kurzbz;
		
		$orgform_kurzbz = $this->translateOrgformCode((int)$rowData['OrgFormCode']);
		//print "OrgFormCode: ".$rowData['OrgFormCode']."; Kurzbz:".$orgform_kurzbz."\n";
		
		$aqa_foebis_person->orgform_kurzbz = $orgform_kurzbz;
		$aqa_foebis_person->regelstudiendauer = (int)$rowData['BIS_RegelStDauer'];
		$aqa_foebis_person->ausbildungssemester = (int)$rowData['Ausbildungssemester'];
		$aqa_foebis_person->guthaben = (int)$rowData['FOEBIS_FoeGuthaben'];
		//$aqa_foebis_person->gefoerdert = (int)$rowData['gefoerdert'];
		$aqa_foebis_person->maxguthaben = (int)$rowData['FOEBIS_MaxFoeGuthaben'];
		$aqa_foebis_person->stud_status = (int)$rowData['StudStatusCode'];

		$aqa_foebis_person->foerderrelevant = ((string)$rowData['BMWFfoerderrelevant']=='J'?true:false);
				
		if ($aqa_foebis_person->save() === false)
		{
			$this->errormsg = $aqa_foebis_person->errormsg;
			return false;
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

	private function translateOrgformCode($code)
	{
		if (!is_numeric($code)) return false;
		print "numeric\n";
		if ($this->bisorgformList != null && $this->bisorgformList !== false)
		{
			foreach ($this->bisorgformList as &$orgform) {
				print "orgform->code={$orgform->code} == $code\n";
				if ($orgform->code == $code)
				{
					print "GEFUNDEN\n\n";
					return $orgform->bisorgform_kurzbz;
				}
			}
		}
		print "NICHT GEFUNDEN\n\n";
		return false;
	}
		
	
}

?>
