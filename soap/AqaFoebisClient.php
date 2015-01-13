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

require_once(__DIR__ .'/../../../include/functions.inc.php');
require_once(__DIR__ .'/../../../addons/studienplatzverwaltung/include/functions.inc.php');
require_once(__DIR__ .'/../../../addons/studienplatzverwaltung/include/aqa_foebis_stg.class.php');
require_once(__DIR__ .'/../../../addons/studienplatzverwaltung/vilesci/AqaFoebisStgReader.class.php');
require_once(__DIR__ .'/../../../addons/studienplatzverwaltung/vilesci/AqaFoebisPersonReader.class.php');
require_once(__DIR__ .'/../../../addons/studienplatzverwaltung/vilesci/AqaFoebisSemesterReader.class.php');
require_once(__DIR__ .'/../../../addons/studienplatzverwaltung/studienplatzverwaltung.config.inc.php');

class AqaFoebisClient
{
	
	public $errormsg;


	public function listFoebisAbrechnungPerson($persKz)
	{
		$params = array(
			'userName' => AQA_USERNAME,
        	'passWord' => AQA_PASSWORD,
        	'persKz' => $persKz);
        
        try {
			$client = new SoapClient(AQA_ERHALTERSERVICE,array( 
						'trace'          => 1,
						'exceptions'     => 0
			));
			$foebisResult = $client->ListFoebisAbrechnungPerson($params);
		} catch (SoapFault $fault) {
			$this->errormsg = "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})";
			return false;
		}
		
		$reader = new AqaFoebisPersonReader();
		$result = $reader->parse($persKz, $foebisResult->ListFoebisAbrechnungPersonResult->any);
        if ($result === false)
		{
			$this->errormsg = $reader->errormsg;
			return false;
		}
        return true;
	}


	public function listFoebisAbrechnungSemester($stgKz,$semester)
	{
		$params = array(
			'userName' => AQA_USERNAME,
        	'passWord' => AQA_PASSWORD,
        	'stgkz' => formatStudiengangKz($stgKz), 
        	'bisMeldedatum' => semester2BISDatum($semester));
        


        try {
			$client = new SoapClient(AQA_ERHALTERSERVICE,array( 
						'trace'          => 1,
						'exceptions'     => 0
			));
			$foebisResult = $client->ListFoebisAbrechnungSemester($params);
			var_dump($foebisResult);
		} catch (SoapFault $fault) {
			$this->errormsg = "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})";
			return false;
		}
		
		$reader = new AqaFoebisSemesterReader();
		$result = $reader->parse($stgKz, $semester, $foebisResult->ListFoebisAbrechnungSemesterResult->any);
        if ($result === false)
		{
			$this->errormsg = $reader->errormsg;
			return false;
		}
        return true;
	}

	/**
	 * Ruft Webservice für Förderabrechnung auf. Daraus extrahierte Daten werden in addon.tbl_aqa_foebis_stg
	 * gespeichert. Hauptsächlich wird hier die Anzahl der sog. FÖBisAktiven Studenten pro Studiengang und
	 * Semester ermittelt.
	 * @param string $stgKz Studiengangkennzahl mit führender Null z.B. '0227', sonst geht es nicht
	 * @param integer $studjahr Jahr des Wintersemesters des gewünschten Studienjahrs (z.B: 2013 für 2013/14)
	 * @param integer $runde  Eine der 3 Runden. Die Daten die zurückkommen sind bei jeder Runde etwas anders.
	 */
    public function listFoebisAbrechnungStudiengang($stgKz,$studienjahr,$runde) {
        $params = array(
        	'userName' => AQA_USERNAME,
        	'passWord' => AQA_PASSWORD,
        	'stgKz' => $stgKz,
        	'studJahrCode' => translateStudienjahr2AqaCode($studienjahr),
        	'runde' => $runde);

		try {
			$client = new SoapClient(AQA_ERHALTERSERVICE,array( 
						'trace'          => 1,
						'exceptions'     => 0
			));
			$foebisResult = $client->ListFoebisAbrechnungStudiengang($params);
		} catch (SoapFault $fault) {
			$this->errormsg = "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})";
			return false;
		}
		
		$reader = new AqaFoebisStgReader();
		$result = $reader->parse($stgKz, $studienjahr, $foebisResult->ListFoebisAbrechnungStudiengangResult->any);
        if ($result === false)
		{
			$this->errormsg = $reader->errormsg;
			return false;
		}
        return true;
    }
	
}
