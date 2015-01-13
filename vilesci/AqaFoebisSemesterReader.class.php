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
require_once(__DIR__ .'/../include/aqa_foebis_semester.class.php');
require_once(__DIR__ .'/../include/functions.inc.php');

/**
 *  Liest die FÖBISDaten für ein Semester eines Studiengangs vom Ministerium ein. Die Daten werden
 *  über ein Webservice bereitgestellt. Dieses basiert offensichtlich auf den
 *  Reports die auch in der Online Applikation zu 'Erhalterportal' finden sind.
 *  
 *  Bsp.:
 *  <code>
        <ListFoebisAbrechnungSemesterResult>
            <Report xsi:schemaLocation="FOEBISAbrechnungSemester https://www.aq.ac.at/ReportServer?%2FApplikationen%2FFOEBIS%2FWebservice%2FFOEBISAbrechnungSemester&amp;rs%3AFormat=XML&amp;rc%3ASchema=True" Name="FOEBISAbrechnungSemester" xmlns="FOEBISAbrechnungSemester">
               <Tablix1 Textbox18="verbrauchtes Förderguthaben im WS (Monate)">
                  <Details_Collection>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1210256080" OrgFormCode="1" StudStatusCode="1" BIS_RegelStDauer="6" Ausbildungssemester="1" MaxFoerderguthaben="46" GefoerderteMonate="6" Foerderguthaben="40"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1210256081" OrgFormCode="1" StudStatusCode="1" BIS_RegelStDauer="6" Ausbildungssemester="1" MaxFoerderguthaben="46" GefoerderteMonate="6" Foerderguthaben="40"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1210256001" OrgFormCode="2" StudStatusCode="1" BIS_RegelStDauer="6" Ausbildungssemester="1" MaxFoerderguthaben="46" GefoerderteMonate="6" Foerderguthaben="40"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1210256002" OrgFormCode="2" StudStatusCode="1" BIS_RegelStDauer="6" Ausbildungssemester="1" MaxFoerderguthaben="46" GefoerderteMonate="6" Foerderguthaben="40"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1210256003" OrgFormCode="2" StudStatusCode="1" BIS_RegelStDauer="6" Ausbildungssemester="1" MaxFoerderguthaben="46" GefoerderteMonate="6" Foerderguthaben="40"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1210256004" OrgFormCode="2" StudStatusCode="1" BIS_RegelStDauer="6" Ausbildungssemester="1" MaxFoerderguthaben="46" GefoerderteMonate="6" Foerderguthaben="40"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1210256005" OrgFormCode="1" StudStatusCode="1" BIS_RegelStDauer="6" Ausbildungssemester="1" MaxFoerderguthaben="46" GefoerderteMonate="6" Foerderguthaben="40"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1210256006" OrgFormCode="2" StudStatusCode="1" BIS_RegelStDauer="6" Ausbildungssemester="1" MaxFoerderguthaben="46" GefoerderteMonate="6" Foerderguthaben="40"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1210256007" OrgFormCode="1" StudStatusCode="1" BIS_RegelStDauer="6" Ausbildungssemester="1" MaxFoerderguthaben="46" GefoerderteMonate="6" Foerderguthaben="40"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="0910256073" OrgFormCode="2" StudStatusCode="3" BIS_RegelStDauer="6" Ausbildungssemester="6" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="0910256074" OrgFormCode="1" StudStatusCode="3" BIS_RegelStDauer="6" Ausbildungssemester="6" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="0910256075" OrgFormCode="2" StudStatusCode="3" BIS_RegelStDauer="6" Ausbildungssemester="6" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="0920256001" OrgFormCode="1" StudStatusCode="3" BIS_RegelStDauer="6" Ausbildungssemester="6" MaxFoerderguthaben="40"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1010256067" OrgFormCode="2" StudStatusCode="3" BIS_RegelStDauer="6" Ausbildungssemester="6" MaxFoerderguthaben="34"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1010256055" OrgFormCode="1" StudStatusCode="3" BIS_RegelStDauer="6" Ausbildungssemester="6" MaxFoerderguthaben="34"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="0920256003" OrgFormCode="1" StudStatusCode="3" BIS_RegelStDauer="6" Ausbildungssemester="6" MaxFoerderguthaben="40"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1010256092" OrgFormCode="2" StudStatusCode="3" BIS_RegelStDauer="6" Ausbildungssemester="6" MaxFoerderguthaben="34"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="0910256061" OrgFormCode="1" StudStatusCode="3" BIS_RegelStDauer="6" Ausbildungssemester="50" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="0910256058" OrgFormCode="2" StudStatusCode="3" BIS_RegelStDauer="6" Ausbildungssemester="50" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="0910256043" OrgFormCode="2" StudStatusCode="3" BIS_RegelStDauer="6" Ausbildungssemester="50" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="0810256032" OrgFormCode="2" StudStatusCode="3" BIS_RegelStDauer="6" Ausbildungssemester="50" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="0910256026" OrgFormCode="1" StudStatusCode="3" BIS_RegelStDauer="6" Ausbildungssemester="50" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="0810256030" OrgFormCode="2" StudStatusCode="3" BIS_RegelStDauer="6" Ausbildungssemester="60" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1010256089" OrgFormCode="2" StudStatusCode="4" BIS_RegelStDauer="6" Ausbildungssemester="1" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1010256103" OrgFormCode="2" StudStatusCode="4" BIS_RegelStDauer="6" Ausbildungssemester="1" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1010256044" OrgFormCode="2" StudStatusCode="4" BIS_RegelStDauer="6" Ausbildungssemester="1" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1110256101" OrgFormCode="2" StudStatusCode="4" BIS_RegelStDauer="6" Ausbildungssemester="1" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1110256087" OrgFormCode="2" StudStatusCode="4" BIS_RegelStDauer="6" Ausbildungssemester="1" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1110256088" OrgFormCode="2" StudStatusCode="4" BIS_RegelStDauer="6" Ausbildungssemester="2" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1110256039" OrgFormCode="1" StudStatusCode="4" BIS_RegelStDauer="6" Ausbildungssemester="2" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1110256026" OrgFormCode="1" StudStatusCode="4" BIS_RegelStDauer="6" Ausbildungssemester="2" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1110256144" OrgFormCode="1" StudStatusCode="4" BIS_RegelStDauer="6" Ausbildungssemester="2" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1110256148" OrgFormCode="1" StudStatusCode="4" BIS_RegelStDauer="6" Ausbildungssemester="2" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1110256152" OrgFormCode="1" StudStatusCode="4" BIS_RegelStDauer="6" Ausbildungssemester="2" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1110256154" OrgFormCode="1" StudStatusCode="4" BIS_RegelStDauer="6" Ausbildungssemester="2" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1010256064" OrgFormCode="2" StudStatusCode="4" BIS_RegelStDauer="6" Ausbildungssemester="2" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1010256104" OrgFormCode="2" StudStatusCode="4" BIS_RegelStDauer="6" Ausbildungssemester="2" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1010256112" OrgFormCode="2" StudStatusCode="4" BIS_RegelStDauer="6" Ausbildungssemester="2" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1010256099" OrgFormCode="2" StudStatusCode="4" BIS_RegelStDauer="6" Ausbildungssemester="2" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1010256097" OrgFormCode="2" StudStatusCode="4" BIS_RegelStDauer="6" Ausbildungssemester="2" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1110256127" OrgFormCode="2" StudStatusCode="4" BIS_RegelStDauer="6" Ausbildungssemester="3" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1110256001" OrgFormCode="2" StudStatusCode="4" BIS_RegelStDauer="6" Ausbildungssemester="3" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1110256106" OrgFormCode="2" StudStatusCode="4" BIS_RegelStDauer="6" Ausbildungssemester="3" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1120256004" OrgFormCode="2" StudStatusCode="4" BIS_RegelStDauer="6" Ausbildungssemester="3" MaxFoerderguthaben="40"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1010256078" OrgFormCode="1" StudStatusCode="4" BIS_RegelStDauer="6" Ausbildungssemester="4" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="0920256008" OrgFormCode="2" StudStatusCode="4" BIS_RegelStDauer="6" Ausbildungssemester="4" MaxFoerderguthaben="40"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="0810256047" OrgFormCode="1" StudStatusCode="4" BIS_RegelStDauer="6" Ausbildungssemester="4" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="0810256013" OrgFormCode="2" StudStatusCode="4" BIS_RegelStDauer="6" Ausbildungssemester="4" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="0710256034" OrgFormCode="1" StudStatusCode="4" BIS_RegelStDauer="6" Ausbildungssemester="6" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="0910256016" OrgFormCode="2" StudStatusCode="4" BIS_RegelStDauer="6" Ausbildungssemester="6" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="0810256048" OrgFormCode="1" StudStatusCode="4" BIS_RegelStDauer="6" Ausbildungssemester="50" MaxFoerderguthaben="46"/>
                     <Details StrMeldedatum="15.11.2012" StgKz="0256" PersKz="1110256155" OrgFormCode="1" StudStatusCode="4" BIS_RegelStDauer="6" Ausbildungssemester="50" MaxFoerderguthaben="22"/>
                  </Details_Collection>
               </Tablix1>
            </Report>
         </ListFoebisAbrechnungSemesterResult>

 *  
 */
class AqaFoebisSemesterReader 
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
	}


	public function parse($stgKz,$semester,$xml) 
	{		
		$simpleXml = simplexml_load_string(removeNamespace($xml));
		// Daten leer?
		if (!isset($simpleXml->Tablix1->Details_Collection)) 
		{
			return true;
		}
		$details = $simpleXml->Tablix1->Details_Collection; //->Details;		
		
		$aqa_foebis_semester = new aqa_foebis_semester();	
		$aqa_foebis_semester->deleteByStgKz($stgKz,$semester);
		
		foreach ($details->Details as $detail)
		{
			if ($this->importSemesterRow($stgKz,$detail) === false)
			{
				return false;
			}
		}				
		
		return true;		
	}	

	private function importSemesterRow($stgKz, $rowData)
	{			
		var_dump($rowData);
		$aqa_foebis_semester = new aqa_foebis_semester();	
		$aqa_foebis_semester->meldedatum = 
			$this->datum->mktime_datum((string)$rowData['StrMeldedatum']);
		$studiensemester_kurzbz = bisDatum2Semester($aqa_foebis_semester->meldedatum);
		
		$aqa_foebis_semester->matrikelnr =  (string)$rowData['PersKz'];;
		$aqa_foebis_semester->studiengang_kz = (int)$rowData['StgKz'];
		$aqa_foebis_semester->studiensemester_kurzbz = $studiensemester_kurzbz;
		
		$orgform_kurzbz = $this->translateOrgformCode((int)$rowData['OrgFormCode']);
		//print "OrgFormCode: ".$rowData['OrgFormCode']."; Kurzbz:".$orgform_kurzbz."\n";
		
		$aqa_foebis_semester->orgform_kurzbz = $orgform_kurzbz;
		$aqa_foebis_semester->regelstudiendauer = (int)$rowData['BIS_RegelStDauer'];
		$aqa_foebis_semester->ausbildungssemester = (int)$rowData['Ausbildungssemester'];
		$aqa_foebis_semester->guthaben = (int)$rowData['Foerderguthaben'];
		$aqa_foebis_semester->gefoerdert = (int)$rowData['GefoerderteMonate'];
		$aqa_foebis_semester->maxguthaben = (int)$rowData['MaxFoerderguthaben'];
		$aqa_foebis_semester->stud_status = (int)$rowData['StudStatusCode'];
		
				
		if ($aqa_foebis_semester->save() === false)
		{
			$this->errormsg = $aqa_foebis_semester->errormsg;
			return false;
		}		
		return true;	
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
		if ($this->bisorgformList != null && $this->bisorgformList !== false)
		{
			foreach ($this->bisorgformList as &$orgform) {
				if ($orgform->code == $code)
				{
					return $orgform->bisorgform_kurzbz;
				}
			}
		}
		return false;
	}
		
	
}

?>
