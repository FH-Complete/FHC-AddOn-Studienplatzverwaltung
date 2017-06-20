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
 * 
 */

require_once(__DIR__ .'/../../../include/functions.inc.php');
require_once(__DIR__ .'/../../../include/appdaten.class.php');
require_once(__DIR__ .'/../../../include/studiengang.class.php');
require_once(__DIR__ .'/../../../include/studienplatz.class.php');

/**
 *  Liest die Basisdaten vom Ministerium ein.
 *  
 *  Bsp.:
 *  <code>
 * <Erhalter ErhKz="005">
 *		<Studiengang UVArt="STG" StgKz="0227" StudiengangBezeichnung="Biomedizinisches Ingenieurwesen/Biomedical Engineering" StudiengangsArt="Ba" Beginn="2003/04" Ende="" Foerdergruppe="technisch" Foerdersatz="7940,00">
 *			<Studienjahr Studienjahr="2012/13" Standort="Wien" Regelstudiendauer="6" VZGPZWS="190" VZGPZSS="190" VZAufnahme="1" VZNPZWS="190" VZNPZSS="190" BBGPZWS="" BBGPZSS="" BBAufnahme="" BBNPZWS="0" BBNPZSS="0" Umschichtbar="1"/>
 *			<Studienjahr Studienjahr="2013/14" Standort="Wien" Regelstudiendauer="6" VZGPZWS="203" VZGPZSS="203" VZAufnahme="1" VZNPZWS="203" VZNPZSS="203" BBGPZWS="" BBGPZSS="" BBAufnahme="" BBNPZWS="0" BBNPZSS="0" Umschichtbar="1"/>
 *			<Studienjahr Studienjahr="2014/15" Standort="Wien" Regelstudiendauer="6" VZGPZWS="200" VZGPZSS="200" VZAufnahme="1" VZNPZWS="200" VZNPZSS="200" BBGPZWS="" BBGPZSS="" BBAufnahme="" BBNPZWS="0" BBNPZSS="0" Umschichtbar="1"/>
 *			<Studienjahr Studienjahr="2015/16" Standort="Wien" Regelstudiendauer="6" VZGPZWS="200" VZGPZSS="200" VZAufnahme="1" VZNPZWS="200" VZNPZSS="200" BBGPZWS="" BBGPZSS="" BBAufnahme="" BBNPZWS="0" BBNPZSS="0" Umschichtbar="1"/>
 *			<Studienjahr Studienjahr="2016/17" Standort="Wien" Regelstudiendauer="6" VZGPZWS="200" VZGPZSS="200" VZAufnahme="1" VZNPZWS="200" VZNPZSS="200" BBGPZWS="" BBGPZSS="" BBAufnahme="" BBNPZWS="0" BBNPZSS="0" Umschichtbar="1"/>
 *			<Studienjahr Studienjahr="2017/18" Standort="Wien" Regelstudiendauer="6" VZGPZWS="200" VZGPZSS="200" VZAufnahme="1" VZNPZWS="200" VZNPZSS="200" BBGPZWS="" BBGPZSS="" BBAufnahme="" BBNPZWS="0" BBNPZSS="0" Umschichtbar="1"/>
 *		</Studiengang>
 *   usw.
 * </code>
 * 
 *  =========================
 *  NEUE VARIANTE ab 2017
 *  =========================
 *  
 *  <code>
 * <Erhalter ErhKz="005">
 *		<Studiengang UVArt="STG" StgKz="0227" StudiengangBezeichnung="Biomedizinisches Ingenieurwesen/Biomedical Engineering" StudiengangsArt="Ba" Beginn="2003/04" Ende="">
			<Studienjahr Studienjahr="2016/17" Standort="Wien" Foerdergruppe="technisch" VZGPZWS="216" VZGPZSS="216" VZAufnahmeWS="1" VZAPZWS="68" VZAufnahmeSS="" VZAPZSS="" VZNPZWS="216" VZNPZSS="216" VZRegelstudiendauer="6" BBGPZWS="" BBGPZSS="" BBAufnahmeWS="" BBAPZWS="" BBAufnahmeSS="" BBAPZSS="" BBNPZWS="" BBNPZSS="" BBRegelstudiendauer="" VBBGPZWS="" VBBGPZSS="" VBBAufnahmeWS="" VBBAPZWS="" VBBAufnahmeSS="" VBBAPZSS="" VBBNPZWS="" VBBNPZSS="" VBBRegelstudiendauer="" Umschichtbar="1" />
			<Studienjahr Studienjahr="2017/18" Standort="Wien" Foerdergruppe="technisch" VZGPZWS="224" VZGPZSS="224" VZAufnahmeWS="1" VZAPZWS="65" VZAufnahmeSS="" VZAPZSS="" VZNPZWS="224" VZNPZSS="224" VZRegelstudiendauer="6" BBGPZWS="" BBGPZSS="" BBAufnahmeWS="" BBAPZWS="" BBAufnahmeSS="" BBAPZSS="" BBNPZWS="" BBNPZSS="" BBRegelstudiendauer="" VBBGPZWS="" VBBGPZSS="" VBBAufnahmeWS="" VBBAPZWS="" VBBAufnahmeSS="" VBBAPZSS="" VBBNPZWS="" VBBNPZSS="" VBBRegelstudiendauer="" Umschichtbar="1" />
			<Studienjahr Studienjahr="2018/19" Standort="Wien" Foerdergruppe="technisch" VZGPZWS="224" VZGPZSS="224" VZAufnahmeWS="1" VZAPZWS="67" VZAufnahmeSS="" VZAPZSS="" VZNPZWS="224" VZNPZSS="224" VZRegelstudiendauer="6" BBGPZWS="" BBGPZSS="" BBAufnahmeWS="" BBAPZWS="" BBAufnahmeSS="" BBAPZSS="" BBNPZWS="" BBNPZSS="" BBRegelstudiendauer="" VBBGPZWS="" VBBGPZSS="" VBBAufnahmeWS="" VBBAPZWS="" VBBAufnahmeSS="" VBBAPZSS="" VBBNPZWS="" VBBNPZSS="" VBBRegelstudiendauer="" Umschichtbar="1" />
			<Studienjahr Studienjahr="2019/20" Standort="Wien" Foerdergruppe="technisch" VZGPZWS="224" VZGPZSS="224" VZAufnahmeWS="1" VZAPZWS="67" VZAufnahmeSS="" VZAPZSS="" VZNPZWS="224" VZNPZSS="224" VZRegelstudiendauer="6" BBGPZWS="" BBGPZSS="" BBAufnahmeWS="" BBAPZWS="" BBAufnahmeSS="" BBAPZSS="" BBNPZWS="" BBNPZSS="" BBRegelstudiendauer="" VBBGPZWS="" VBBGPZSS="" VBBAufnahmeWS="" VBBAPZWS="" VBBAufnahmeSS="" VBBAPZSS="" VBBNPZWS="" VBBNPZSS="" VBBRegelstudiendauer="" Umschichtbar="1" />
			<Studienjahr Studienjahr="2020/21" Standort="Wien" Foerdergruppe="technisch" VZGPZWS="224" VZGPZSS="224" VZAufnahmeWS="1" VZAPZWS="67" VZAufnahmeSS="" VZAPZSS="" VZNPZWS="224" VZNPZSS="224" VZRegelstudiendauer="6" BBGPZWS="" BBGPZSS="" BBAufnahmeWS="" BBAPZWS="" BBAufnahmeSS="" BBAPZSS="" BBNPZWS="" BBNPZSS="" BBRegelstudiendauer="" VBBGPZWS="" VBBGPZSS="" VBBAufnahmeWS="" VBBAPZWS="" VBBAufnahmeSS="" VBBAPZSS="" VBBNPZWS="" VBBNPZSS="" VBBRegelstudiendauer="" Umschichtbar="1" />
			<Studienjahr Studienjahr="2021/22" Standort="Wien" Foerdergruppe="technisch" VZGPZWS="224" VZGPZSS="224" VZAufnahmeWS="1" VZAPZWS="67" VZAufnahmeSS="" VZAPZSS="" VZNPZWS="224" VZNPZSS="224" VZRegelstudiendauer="6" BBGPZWS="" BBGPZSS="" BBAufnahmeWS="" BBAPZWS="" BBAufnahmeSS="" BBAPZSS="" BBNPZWS="" BBNPZSS="" BBRegelstudiendauer="" VBBGPZWS="" VBBGPZSS="" VBBAufnahmeWS="" VBBAPZWS="" VBBAufnahmeSS="" VBBAPZSS="" VBBNPZWS="" VBBNPZSS="" VBBRegelstudiendauer="" Umschichtbar="1" />
		</Studiengang>
 *   usw.
 * </code>
 *  
 */
class BasisdatenReader 
{
	// Organisationsformen werden im XML-File als Teil des Attributnamens
	// verwendet. Diese korrespondieren mit orgform_kurbz aus der Tabelle
	// bis.tbl_orgform
	private $orgformPrefix = array('VZ','BB','VBB');
	public $errormsg;
	
	public function parseBasisdatenUV($file) 
	{
		if (!file_exists($file)) 
		{
			$this->errormsg = 'Datei nicht \'' + $file + '\' gefunden';
			return false;
		}
		$xml = simplexml_load_file($file);
		if (count($xml->Studiengang) > 1)
		{
			foreach($xml->Studiengang as $studiengang)
			{
				//print("StgKz=".$studiengang['StgKz'].", Beginn=".$studiengang['Beginn'].'/n');
				if (!(count($studiengang->Studienjahr)>1))
				{
					if ($this->importRow(
						(string)$studiengang['StgKz'], 
					//	(string)$studiengang['StudiengangsArt'], 
						(string)$studiengang['Beginn'],
						$studiengang->Studienjahr
						) === false)
					{
						return false;
					}
				}
				else 
				{
					foreach($studiengang->Studienjahr as $studienjahr)
					{
						if ($this->importRow(
							(string)$studiengang['StgKz'], 
						//	(string)$studiengang['StudiengangsArt'], 
							(string)$studiengang['Beginn'],
							$studienjahr
							) === false)
						{
							return false;
						}
					}
					
				}
				
				
			}
		} 
		else
		{
			$this->errormsg = 'Datei enthält keine Liste mit Studiengängen';		
			return false;
		}		
		return true;
	}	

	
	private function importRow($stgKz, $beginn, $rowData)
	{
		$studienplatz = new studienplatz();
		
		//var_dump($rowData);
		// geistreicher Weise werden im XML-File die Organisationsformen
		// als Attribute in einem einzigen XML-Element zusammengefasst,
		// daher werden diese in folgender Schleife wieder aufgesplittet
		foreach($this->orgformPrefix as $orgformPrefix)
		{			
			// Wintersemester-Jahr
			$ws_jahr = substr($rowData['Studienjahr'],0, 4);
			// Sommersemester-Jahr
			$ss_jahr = $ws_jahr + 1;
	
			// Wintersemester:
			// --------------
			
			// lade etwaigen vorhanden Datensatz
			$vorhanden = $studienplatz->load_studiengang_studiensemester_orgform(
				$stgKz, 'WS'.$ws_jahr,  $orgformPrefix, true);
				
			if ($vorhanden && $studienplatz->result != null && count($studienplatz->result) == 1)
			{
                //var_dump($studienplatz->result); 
				$this->copyData($orgformPrefix, 'WS', $rowData, $studienplatz->result[0]);
				// noch fehlende Attribute?
				// $orgformPrefixAufnahme
				// $umschichtbar
				if ($studienplatz->result[0]->save() === false)  {
					$this->errormsg = $studienplatzNeu->errormsg;
					return false;
				}
			} 
			else 
			{	$studienplatzNeu = new studienplatz();
				$this->copyData($orgformPrefix, 'WS', $rowData, $studienplatzNeu);
				$studienplatzNeu->studiengang_kz = $stgKz;
				//$studienplatzNeu->art = $this->translateArt($art);
				$studienplatzNeu->orgform_kurzbz = $orgformPrefix;
				$studienplatzNeu->studiensemester_kurzbz = 'WS'.$ws_jahr;
				if ($studienplatzNeu->save() === false) {
					$this->errormsg = $studienplatzNeu->errormsg;
					return false;
				}
			}
			
			// Sommersemester:
			// ---------------
			
			// lade etwaigen vorhanden Datensatz
			$vorhanden = $studienplatz->load_studiengang_studiensemester_orgform(
				$stgKz, 'SS'.$ss_jahr, $orgformPrefix, true); 
			
			if ($vorhanden && $studienplatz->result != null && count($studienplatz->result) == 1)
			{
				$this->copyData($orgformPrefix, 'SS', $rowData, $studienplatz->result[0]);
				// noch fehlende Attribute?
				// $orgformPrefixAufnahme
				// $umschichtbar
				if ($studienplatz->result[0]->save() === false) {
					$this->errormsg = $studienplatz->errormsg;
					return false;
				}
			} 
			else 
			{	$studienplatzNeu = new studienplatz();
				$this->copyData($orgformPrefix, 'SS', $rowData, $studienplatzNeu);
				$studienplatzNeu->studiengang_kz = $stgKz;
				//$studienplatzNeu->art = $this->translateArt($art);
				$studienplatzNeu->orgform_kurzbz = $orgformPrefix;
				$studienplatzNeu->studiensemester_kurzbz = 'SS'.$ss_jahr;
				if ($studienplatzNeu->save() === false) {
					$this->errormsg = $studienplatzNeu->errormsg;
					return false;
				}
			}
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
	
	private function copyData($orgformPrefix, $semesterPostfix, $rowData, $studienplatz) {
		$studienplatz->gpz = (string)$rowData[$orgformPrefix.'GPZ'.$semesterPostfix];
		$studienplatz->npz = (string)$rowData[$orgformPrefix.'NPZ'.$semesterPostfix];
		$studienplatz->apz = (string)$rowData[$orgformPrefix.'APZ'.$semesterPostfix];
	}
}

?>
