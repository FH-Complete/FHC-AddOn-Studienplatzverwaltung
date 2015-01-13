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
require_once(__DIR__ .'/../../../include/appdaten.class.php');
require_once(__DIR__ .'/../../../include/studiengang.class.php');
require_once(__DIR__ .'/../../../include/studienplatz.class.php');
require_once(__DIR__ .'/../../../include/prestudent.class.php');  // für bewerber und interessenten
require_once(__DIR__ .'/../../../include/statistik.class.php');   // für dropouts
require_once(__DIR__ .'/../include/functions.inc.php');
require_once(__DIR__ .'/../include/aqa_foebis_stg.class.php');  // für FÖBIS-Daten der AQ
require_once(__DIR__ .'/BasisdatenReader.class.php');
require_once(__DIR__ .'/../soap/AqaFoebisClient.php');

class StudienplatzverwaltungAPI 
{
	public $errormsg;			
	private static $instance;
	private function __construct() { }
 	public function __clone() 
	{
		trigger_error('Clone is not allowed.', E_USER_ERROR);
	}
	public static function init() {
		if (!isset(self::$instance)) 
		{
            		$c = __CLASS__;
            		self::$instance = new $c;
		}
		return self::$instance;
	}

	/**
	 * Einlesen des XML-Files vom Ministerium und aktualiseren der Daten
	 * in der Tabelle lehre.tbl_studienplatz
	 */ 
	public function parseBasisdatenUV($file) 
	{
		$reader= new BasisdatenReader();
		$result = $reader->parseBasisdatenUV($file);
		if ($result === false) 
		{
			$this->errormsg = $reader->errormsg;
			return false;
		}
		return $result;
	}

	public function exportXML($studienjahr, $version, $uid) {
		// appdaten holen
		$appdaten = $this->getAppdaten($studienjahr, $version, $uid);
		// Basisdaten dazugeben (müssen in der UV auch drin sein)
		if ($this->mergeBasisdaten($appdaten) === false)
		{
			return false;
		}
		$writer = new XMLExporter();
		$result = $writer->export($appdaten);
		return $result;
	}	

	public function exportCSV($studienjahr, $version, $uid) {
		// appdaten holen
		$appdaten = $this->getAppdaten($studienjahr, $version, $uid);
		// Basisdaten dazugeben (müssen in der UV auch drin sein)
		if ($this->mergeBasisdaten($appdaten) === false)
		{
			return false;
		}
		$writer = new UVGesamtCSVExporter();
		$result = $writer->export($appdaten);
		return $result;
	}	

	/**
	 * Bsp.:
	 * <code>
	 * [		
	 *	{
	 *		studienjahr: '2012/13', 
	 *	    	uvListe: [{nr: 1, lastupdate: '1.6.2012', notizen: '', status:'entwurf', zeitraum:4},
     *                    {nr: 2, lastupdate: '9.6.2012', notizen: '', status:'eingereicht', zeitraum:4}]
	 *	},
	 *	{
	 *		studienjahr: '2013/14', 
	 *		uvListe: [
	 *			{nr: 1, lastupdate: '1.12.2012', notizen: '', status:'entwurf', zeitraum:4},
	 *			{nr: 2, lastupdate: '9.12.2012', notizen: '', status:'eingereicht', zeitraum:4}
	 *		         ]
	 *	}	
	 *] </code>
	 * @return string als json
	 */
	public function getMetadata() 
	{
		$result = array();
		$appdaten = new appdaten();
		// alle Umschichtungsvorhaben holen (sortiert nach Studienjahr und Version)
		$uvList = $appdaten->getAllByApp('Studienplatzverwaltung');
		$lastBezeichnung = '';
		$currentBezeichnung = '';
		$iterationen = null;
		$sjCount = -1;
		for($i=0;$i<count($uvList);$i++) 
		{
			$currentBezeichnung = $uvList[$i]->bezeichnung;
			$daten = json_decode($uvList[$i]->daten, false); // @TODO depth checken
			if ($daten == NULL) 
			{
				throw new Exception('JSON aus Datenbank konnte nicht dekodiert werden: '.$uvList[$i]->daten.
						"\njson_last_error: ".  json_last_error());
			}
			if ($currentBezeichnung != $lastBezeichnung) 
			{						
								
				$result[] = array('studienjahr' => $currentBezeichnung,
						  'uvListe' => array());
				$sjCount++;
				$iterationen = &$result[$sjCount]['uvListe'];
				$lastBezeichnung = $currentBezeichnung;
			}
			$lastupdate = ($uvList[$i]->updateamum != null ?
								 $uvList[$i]->updateamum :
								$uvList[$i]->insertamum );
			if ($lastupdate != null) 
			{
				$lastupdate = new DateTime($lastupdate);
			}
			$iterationen[] = array('nr' => $uvList[$i]->version,
					       'lastupdate' =>  ($lastupdate != null ? $lastupdate->format('c') : ''),
					       'status' => $daten->status, //($uvList[$i]->freigabe?'eingereicht':'entwurf'),
					       'zeitraum' => $daten->zeitraum,
					       'notizen' => $daten->notizen
					       ) ;
			
		}
		return json_encode($result);
	}
	
	
	public function getUV($studienjahr, $version, $zeitraum, $uid) 
    {
		$appdaten = $this->getAppdaten($studienjahr, $version, $uid);
		if ($this->mergeBasisdaten($appdaten) === false)
		{
			return false;
		}
		return $appdaten;
    }
	
	/**
	 * Zeitraum von UV ändern. 
	 * @param string $studienjahr
	 * @param integer $version
	 * @param integer $zeitraum
	 * @param string $uid
	 * @return appdaten oder false bei Fehler
	 */
	public function refreshUV($studienjahr, $version, $zeitraum, $uid) 
    {
		$appdaten = $this->getAppdaten($studienjahr, $version, $uid);
		$daten = json_decode($appdaten->daten, true);
		if ($zeitraum !== null)
		{
			if (($daten['zeitraum'] + 0) > $zeitraum)
			{
				// überschüssige Studienjahre eliminieren
				$daten['zeitraum'] = $zeitraum;
				$this->removeGesamtDaten($daten, $studienjahr, $zeitraum);
			} 
			else if (($daten['zeitraum'] + 0) < $zeitraum)
			{
				// fehlende Studienjahre hinzufügen
				$gesamtDaten = $this->createDaten($studienjahr, $zeitraum);
				$daten['zeitraum'] = $zeitraum;
				$this->concatGesamtDaten($daten, $gesamtDaten);
			}
		}
		$appdaten->daten = json_encode($daten);
		$error_code = json_last_error();
		if ($error_code != JSON_ERROR_NONE)
		{
			$this->errormsg = $this->translateError($error_code);
			return false;
		}
		else 
		{
			if ($this->mergeBasisdaten($appdaten) === false)
			{
				return false;
			}
			$appdaten->save(false);
		}
		
		return $appdaten;
    }
	
	private function translateError($error_code)
	{
		switch ($error_code) 
		{
			case JSON_ERROR_NONE:
				return ' - No errors';
			
			case JSON_ERROR_DEPTH:
				return ' - Maximum stack depth exceeded';
			
			case JSON_ERROR_STATE_MISMATCH:
				return ' - Underflow or the modes mismatch';
			
			case JSON_ERROR_CTRL_CHAR:
				return ' - Unexpected control character found';
			
			case JSON_ERROR_SYNTAX:
				return ' - Syntax error, malformed JSON';
			
			case JSON_ERROR_UTF8:
				return ' - Malformed UTF-8 characters, possibly incorrectly encoded';
			
			default:
				return ' - Unknown error';
			
		}
	}
	
	
	/**
	 * Helper um Zeitraum von vorhandenen Daten zu ändern. Hier werden überflüssige
	 * Semester entfernt.
	 * @param array $daten
	 * @param string $studienjahr
	 * @param integer $zeitraum
	 */
	private function removeGesamtDaten(&$daten, $studienjahr, $zeitraum)
	{
		$gesamtDaten = &$daten['gesamtDaten'];
		foreach ($gesamtDaten as &$studiengaenge) 
		{
			$semesterDatenNeu = array();
			$jahr = substr($studienjahr,0,4) + 0;
			$currentSemester = 'WS'.$jahr;
			$semesterCount = 1;
			$studienplatz = new studienplatz();
			while ($semesterCount <= ($zeitraum*2))
			{
				// Loop Semesterdaten und Semester suchen
				foreach ($studiengaenge['studiengangDaten'] as &$stgSemesterDaten)
				{
					if ($stgSemesterDaten['studiensemester'] == $currentSemester)
					{
						// kopieren
						$semesterDatenNeu[] = $stgSemesterDaten;
						break;
					}
				}				
				$semesterCount++;
				$currentSemester = incSemester($currentSemester);
			}
			$studiengaenge['studiengangDaten'] = $semesterDatenNeu;
		}
	}
	
	/**
	 * Helper, welcher für das Ändern des Zeitraumes benögtigt wird. Hier
	 * wird das Skelett der zusätzlichen Gesamtdaten angefügt bzw. etwaige
	 * fehlende Studiengänge ergänzt.
	 * @param array $daten
	 * @param array $gesamtDaten
	 * @return boolean
	 */
	private function concatGesamtDaten(&$daten, $gesamtDatenZusatz)
	{
		//foreach ($daten['gesamtDaten'] as &$studiengaenge)
		$gesamtDaten = &$daten['gesamtDaten'];
		foreach ($gesamtDatenZusatz as &$studiengaengeZusatz)
		{
			// Studiengang suchen
			$stgVorhanden = false;
			foreach ($gesamtDaten as &$studiengaenge) 
			{
				if ($studiengaenge['stgKz'] === $studiengaengeZusatz['stgKz']
					&& $studiengaenge['stgArt'] === $studiengaengeZusatz['stgArt']
					&& $studiengaenge['orgForm'] === $studiengaengeZusatz['orgForm'])
				{
					// Studiengang vorhanden
					// -> Zusatz an vorhandene Daten dranhängen
					foreach ($studiengaengeZusatz['studiengangDaten'] as &$stgZusatzSemesterDaten)
					{
						$semesterDaten = null;
						$studiensemester = $stgZusatzSemesterDaten['studiensemester'];
						foreach ($studiengaenge['studiengangDaten'] as &$stgSemesterDaten)
						{
							if ($stgSemesterDaten['studiensemester'] == $studiensemester)
							{
								$semesterDaten = $stgSemesterDaten;
								break;
							}
						}
						if ($semesterDaten == null)
						{
							// Semester ist noch nicht vorhanden						
							$studiengaenge['studiengangDaten'][] = $stgZusatzSemesterDaten;
							/*array(
								'studiensemester' => $studiensemester,				
								'gpzUv' => '',				
								'npzUv' => ''			
							);*/
						}
					}
					$stgVorhanden = true;
					break;
				}				
			}
			// Studiengang nicht vorhanden?
			if (!$stgVorhanden)
			{
				// -> Studiengang dazuhängen
				$gesamtDaten[] = $studiengaengeZusatz;				
			} 			
			
		}
	}
	
    public function saveUV($json, $uid)
    {
    	$appdaten = $this->getAppdaten($json->studienjahr, $json->version, $json->zeitraum, $uid);
        $daten = json_decode($appdaten->daten, true);
        $daten['notizen'] = ($json->notizen?$json->notizen:'');
		$daten['status'] = $json->status;
        $asNew = $json->saveAs ? $json->saveAs : false;
        $uvListe = $json->uvListe;
        if ($json->status == 'eingereicht')
        {
            $appdaten->freigabe = true;
        }
        else 
        {
            $appdaten->freigabe = false;
        }
		if ($asNew) 
		{
			$appdaten->insertvon = $uid;
		}
		else
		{
			$appdaten->updatevon = $uid;
		}
		try {
			for($i = 0; $i < count($uvListe); $i++) 
			{
				$this->updateAppdaten($daten, $uvListe[$i]);
			}
		}  catch (Exception $e)
		{
			$this->errormsg = $e->getMessage();
			return false;
		}
		$appdaten->daten = json_encode($daten);
		if ($this->mergeBasisdaten($appdaten) === false)
		{
				return false;
		}
        $result = $appdaten->save($asNew);
        if ($result === false)
        {
            $this->errormsg = $appdaten->errormsg;
            return false;
        }
        return $appdaten;
    }

    /**
     * Helper
     */
    private function updateAppdaten(&$daten, $uvDaten)
    {
        $stgKz = $uvDaten->stgKz;
        $stgArt = $uvDaten->stgArt;
        $orgForm = $uvDaten->orgForm;
		$npzUv = $uvDaten->npzUv;
        $gpzUv = $uvDaten->gpzUv;
        $studiensemester = $uvDaten->studiensemester;
        $gesamtDaten = &$daten['gesamtDaten'];
        // Studiengang loop
        for($i = 0; $i < count($gesamtDaten); $i++)
        {
           $studiengangDaten = &$gesamtDaten[$i]['studiengangDaten'];
           if ($gesamtDaten[$i]['stgKz'] == $stgKz &&
               $gesamtDaten[$i]['stgArt'] == $stgArt &&
               $gesamtDaten[$i]['orgForm'] == $orgForm)
           {
               // Studiensemester loop
               for($j = 0; $j < count($studiengangDaten); $j++)
               {
                   if ($studiengangDaten[$j]['studiensemester'] == $studiensemester)
                   {
                       $studiengangDaten[$j]['npzUv'] = $npzUv;
                       $studiengangDaten[$j]['gpzUv'] = $gpzUv;
                       return;        
                   }
               }
			   // Semester nicht vorhanden (kann nach Zeitraumänderung vorkommen)
			   // -> anfügen
			   $studiengangDaten[] = array(
				    'studiensemester' => $studiensemester,
					'npzUv' => $npzUv,
					'gpzUv' => $gpzUv
			   );			   
           }
        }


    }

	public function deleteUV($studienjahr, $version, $uid) {
		$app = 'Studienplatzverwaltung';		
		$appversion = '1.0';	
		$appdaten = new appdaten();
		$result = $appdaten->deleteByBezeichnungVersion($app, $studienjahr, $version);
		if ($result === false)
		{
			$this->errormsg = $appdaten->errormsg;
		}
		return $result;
	}
	
	public function importFOEBis() 
    {		
		$result = $this->syncFoebisStg();
		if ($result === true)
		{
			return false;
		}
		return true;
    }
	
	/**
	 * Erzeugt JSON-Skelett mit Basisdaten und speichert es als neue
	 * Iteration. In der Datenbank werden nur die wirklich veränderlichen
	 * Daten gespeichert (gpzUV und npzUV).
	 *  
	 * @param type $studienjahr
	 * @return string JSON
	 * 
	 * BEISPIEL:
	 * <code>
	 * {
   	 *	studienjahr:"2014/15",
	 *	zeitraum:4,
	 *	status:'entwurf',
	 *	gesamtDaten:
     *               [
     *                 {"stgKz": "0227", "stgBezeichnung": "BBE", "stgArt": "b", "orgForm": "VZ", 
     *                     studiengangDaten:[
     *                         {"studiensemester": "WS2012", "gpzBd": 160, "gpzUv": 200, "npzBd": 160, "npzUv": 190},
     *                         {"studiensemester": "SS2013", "gpzBd": 160, "gpzUv": 200, "npzBd": 160, "npzUv": 190},
     *                     ]
     *                 },
     *                 {"stgKz": "0228", "stgBezeichnung": "MBE", "stgArt": "m", "orgForm": "VZ", 
     *                     studiengangDaten:[
	 *				         usw.
     *                     ]
     *                 }
 	 *	             ]
     *
     * } 
	 * </code>
	 */
    public function newUV($studienjahr,$zeitraum, $uid) 
    {
		$appdaten = $this->createAppdaten(
				$studienjahr, 
				$zeitraum,
				$this->createDaten($studienjahr, $zeitraum), 
				$uid);
		if ($this->mergeBasisdaten($appdaten) === false)
		{
			return false;
		}
		return $appdaten;
    }

    /**
     *  Daten die im Chart gebraucht werden holen. Hier werden hauptsächlich
     *  Daten der vorhergehenden Jahre geholt, damit man sich der Benutzer
     *  ein Bild vom jeweiligen Studiengang machen kann.
     *  
     */
    public function getInfoDaten($studienjahr,$zeitraum) 
    {
        // Studentenzahlen nach Status holen
		$studentenStatus = new StudentenStatus();
		if ($studentenStatus->getAll($studienjahr, $zeitraum) === false)
		{
            $this->errormsg = $studentenStatus->errormsg;
			return false;
		}
		$statusList = $studentenStatus->result ;
		// FÖBISAktive holen (AQ Daten)
		$foebis = new aqa_foebis_stg();
		if ($foebis->load_foebis_semesterdaten($studienjahr, $zeitraum) === false)
		{
			$this->errormsg = $foebis->errormsg;
            return false;
		}
		$foebisList = $foebis->result;
		// Basisdaten der UV (NPZ, GPZ)
		$studienplatz = new studienplatz();
		$basis = $studienplatz->getAnzahlAlleOrgformen($studienjahr, $zeitraum);
		if ($basis === false)
		{
            $this->errormsg = $studienplatz->errormsg;
			return false;
		}
		$basisList = &$basis;

        // Semesterliste erzeugen
        $startSemester= 'WS'.substr($studienjahr,0,4);		
		$semesterList = generateSemesterList($startSemester,($zeitraum*2)-1);

		
		// ---------------------------
		// StatusList
		// ---------------------------
		// 
        // Daten in Struktur bringen die für Darstellung im Chart praktischer ist
        $datasets = array();
        foreach ($semesterList as $semester)
        {
            $semesterDaten = $statusList[$semester];
            $stgKzList = array_keys($semesterDaten);
            foreach($stgKzList as $stgKz)
            {
                foreach($semesterDaten[$stgKz] as $orgForm => $attrList)
                {   
                    foreach($attrList as $attr => $v)
                    {
                        $datasets[$stgKz][$orgForm][$attr][$semester] = $v;
                    }
                    
                    // Basisdaten (NPZ)
                    $datasets[$stgKz][$orgForm]['npzBd'][$semester] = @$basisList[$semester][$stgKz][$orgForm]['NPZ'];
					
					// FÖBISDaten
					$anzFoeBisA_r1 = @$foebisList[$semester][$stgKz][$orgForm]['r1_plaetze_bezahlt'];
					$anzFoeBisA_r2 = @$foebisList[$semester][$stgKz][$orgForm]['r2_foebisaktive'];
					$anzFoeBisA_r3 = @$foebisList[$semester][$stgKz][$orgForm]['r3_foebisaktive'];
					$anzFoeBisA = null;
					if (isset($anzFoeBisA_r3) && is_numeric($anzFoeBisA_r3))
					{
						$anzFoeBisA = $anzFoeBisA_r3;
					} 
					else if (isset($anzFoeBisA_r2) && is_numeric($anzFoeBisA_r2))
					{
						$anzFoeBisA = $anzFoeBisA_r2;
					}
					else if (isset($anzFoeBisA_r2) && is_numeric($anzFoeBisA_r2))
					{
						$anzFoeBisA = $anzFoeBisA_r1;
					}
				
					$datasets[$stgKz][$orgForm]['foebisaktive'][$semester] = $anzFoeBisA;
					
					
					
                }
				
            }
			
        }
		
		// ------------------------------
		// foebisList
		// ------------------------------
		/*
		foreach ($semesterList as $semester)
        {

			 $semDaten = @$foebisList[$semester];
			 if ($semDaten == null) continue;
			 $stgKzList1 = array_keys($semDaten);
			
             foreach($stgKzList1 as $stgKz)
            {
               foreach($semDaten[$stgKz] as $orgForm => $attrList)
                {   
				
					$anzFoeBisA_r1 = @$foebisList[$semester][$stgKz][$orgForm]['r1_plaetze_bezahlt'];
					$anzFoeBisA_r2 = @$foebisList[$semester][$stgKz][$orgForm]['r2_foebisaktive'];
					$anzFoeBisA_r3 = @$foebisList[$semester][$stgKz][$orgForm]['r3_foebisaktive'];
					$anzFoeBisA = null;
					if (isset($anzFoeBisA_r3) && is_numeric($anzFoeBisA_r3))
					{
						$anzFoeBisA = $anzFoeBisA_r3;
					} 
					else if (isset($anzFoeBisA_r2) && is_numeric($anzFoeBisA_r2))
					{
						$anzFoeBisA = $anzFoeBisA_r2;
					}
					else if (isset($anzFoeBisA_r2) && is_numeric($anzFoeBisA_r2))
					{
						$anzFoeBisA = $anzFoeBisA_r1;
					}
				
					$datasets[$stgKz][$orgForm]['foebisaktive'][$semester] = $anzFoeBisA;
			
                }			
            }
			
        }*/
		
		
		// für jedes Semester sollte es einen Wert geben, sonst wird es im 
		// Chart falsch dargestellt. Daher werden hier alle fehlenden Werte
        // durch 0 ersetzt
		$anzahlDatensaetze = count($semesterList);
		$stgKzList = array_keys($datasets);
		foreach($stgKzList as $stgKz) 
		{
			foreach ($datasets[$stgKz] as $orgForm => $attrList ) 
			{
				foreach ($attrList as $attr => $semesterDataList) 
				{
					if (count($semesterDataList) < $anzahlDatensaetze) 
					{
						foreach ($semesterList as $semester)
						{
							if (!isset($datasets[$stgKz][$orgForm][$attr][$semester])) 
							{
								$datasets[$stgKz][$orgForm][$attr][$semester] = 0;
							}								
						}
					}
				}
			}
		}
		// Key aus assoziativem Array wieder entfernen, weil sonst die Semster
		// als Property übergeben werden und dann ist eine mühsame Sortierung
		// in Javascript notwendig
		foreach($stgKzList as $stgKz) 
		{
			foreach ($datasets[$stgKz] as $orgForm => $attrList ) 
			{
				foreach ($attrList as $attr => $semesterDataList) 
				{
					$datasets[$stgKz][$orgForm][$attr] = array_values($datasets[$stgKz][$orgForm][$attr]);
				}
			}		
		}
		return $datasets;
    }

	private function mergeBasisdaten($appdaten)
	{		
		$prestudent = new prestudent();
		$statistik = new statistik();
		$studienplatz = new studienplatz();
		$studienjahr = $appdaten->bezeichnung;
		$daten = json_decode($appdaten->daten,true);	
		$zeitraum = (int)$daten['zeitraum'];
		// Studentenzahlen nach Status holen
		$studentenStatus = new StudentenStatus();
		if ($studentenStatus->getAll($studienjahr, $zeitraum) === false)
		{
            $this->errormsg = $studentenStatus->errormsg;
			return false;
		}
		$statusList = $studentenStatus->result ;
		// FÖBISAktive holen (AQ Daten)
		$foebis = new aqa_foebis_stg();
		if ($foebis->load_foebis_semesterdaten($studienjahr, $zeitraum) === false)
		{
			$this->errormsg = $foebis->errormsg;
            return false;
		}
		$foebisList = $foebis->result;
		// Basisdaten der UV (NPZ, GPZ)
		$basis = $studienplatz->getAnzahlAlleOrgformen($studienjahr, $zeitraum);
		if ($basis === false)
		{
            $this->errormsg = $studienplatz->errormsg;
			return false;
		}
		$basisList = &$basis;
		foreach ($daten['gesamtDaten'] as &$studiengaenge)
		{
			$stgKz = $studiengaenge['stgKz'];
			$stgBezeichnung = $studiengaenge['stgBezeichnung'];
			$stgArt = $studiengaenge['stgArt'];
			$orgForm = $studiengaenge['orgForm'];
			
			foreach ($studiengaenge['studiengangDaten'] as &$stgSemesterDaten)
			{
				
				$studiensemester = $stgSemesterDaten['studiensemester'];
				
				$anzBewerber = @$statusList[$studiensemester][$stgKz][$orgForm]['bewerber'];
				$anzInteressenten = @$statusList[$studiensemester][$stgKz][$orgForm]['interessenten'];
				$anzDropout = @$statusList[$studiensemester][$stgKz][$orgForm]['abbrecher'];
				$anzStudierende = @$statusList[$studiensemester][$stgKz][$orgForm]['studenten'];
				
				$anzFoeBisA_r1 = @$foebisList[$studiensemester][$stgKz][$orgForm]['r1_plaetze_bezahlt'];
				$anzFoeBisA_r2 = @$foebisList[$studiensemester][$stgKz][$orgForm]['r2_foebisaktive'];
				$anzFoeBisA_r3 = @$foebisList[$studiensemester][$stgKz][$orgForm]['r3_foebisaktive'];
				$anzFoeBisA = null;
				if (isset($anzFoeBisA_r3) && is_numeric($anzFoeBisA_r3))
				{
					$anzFoeBisA = $anzFoeBisA_r3;
				} 
				else if (isset($anzFoeBisA_r2) && is_numeric($anzFoeBisA_r2))
				{
					$anzFoeBisA = $anzFoeBisA_r2;
				}
				else if (isset($anzFoeBisA_r2) && is_numeric($anzFoeBisA_r2))
				{
					$anzFoeBisA = $anzFoeBisA_r1;
				}
				
				/*
				if ($studienplatz->load_studiengang_studiensemester_orgform($stgKz, $studiensemester, $orgForm) === false)
				{
					$this->errormsg = $studienplatzDaten->errormsg;
					return false;
				}
				if (count($studienplatz->result) === 1)
				{
					$gpzBd = $studienplatz->result[0]->gpz;
					$npzBd = $studienplatz->result[0]->npz;
				}
				else
				{
					// keine Basisdaten gefunden
					$gpzBd = '';
					$npzBd = '';
				}*/
				$gpzBd = @$basisList[$studiensemester][$stgKz][$orgForm]['GPZ'];
				$npzBd = @$basisList[$studiensemester][$stgKz][$orgForm]['NPZ'];
				$npzBdGesamt = @$basisList[$studiensemester][$stgKz]['gesamt']['NPZ'];
				
				$stgSemesterDaten['bewerber'] = ($anzBewerber!==null ? $anzBewerber : '');
				$stgSemesterDaten['interessenten'] = ($anzInteressenten !== null ? $anzInteressenten : '');
				$stgSemesterDaten['dropout'] = ($anzDropout !== null ? $anzDropout : '');
				$stgSemesterDaten['studierende'] = $anzStudierende;
				$stgSemesterDaten['gpzBd'] = $gpzBd;
				$stgSemesterDaten['npzBd'] = $npzBd;				
				$stgSemesterDaten['npzBdGesamt'] = $npzBdGesamt;  // Gesamtsumme über alle Orgformen (wird für Berechnung der Toleranzförderplätze gebraucht)
				$stgSemesterDaten['regelstudiendauer'] = $studiengaenge['regelstudiendauer'];				
				$stgSemesterDaten['foebisAktive'] = $anzFoeBisA;
				
			}
		}
		$appdaten->daten = json_encode($daten);
		return true;
	}
	

	private function getAppdaten($studienjahr, $version, $uid) {
		$app = 'Studienplatzverwaltung';		
		$appversion = '1.0';	
		$appdaten = new appdaten();
		$appdaten->getByBezeichnungVersion($app, $studienjahr, $version);		
		return $appdaten;
	}
	
	private function createAppdaten($studienjahr, $zeitraum, $gesamtdaten, $uid) {
		$app = 'Studienplatzverwaltung';		
		$appversion = '1.0';				
		
		// neuen Datensatz anlegen
		$appdaten = new appdaten();
		$appdaten->uid = $uid;
		$appdaten->app = $app;
		$appdaten->appversion = $appversion;  
		$appdaten->bezeichnung = $studienjahr;
		$appdaten->daten = json_encode(
				array(
					'studienjahr' => $studienjahr,
					'zeitraum' => intval($zeitraum),
					'status' => 'entwurf',
					'notizen' => '',
					'gesamtDaten' => $gesamtdaten
				)
		);
		$appdaten->freigabe = false;
		$appdaten->insertvon = $uid;
		$appdaten->save(true);
		
		return $appdaten;
	}
	
	private function createDaten($studienjahr, $zeitraum) {
		
		// Liste der Studiengänge inkl. Zusatzdaten (OrgForm, Art)
		$studiengangSPV = new studiengangSPV();
		if (!$studiengangSPV->getAll($studienjahr, $zeitraum)) 
		{
			$this->errormsg = $studiengangSPV->errormsg;
			return false;
		}
		
		$gesamtdaten = array();
		$studiengangList = $studiengangSPV->result;
		foreach ($studiengangList as $stg)
		{
			$stgDaten = $this->createStgDaten($stg->studiengang_kz, $stg->orgform_kurzbz, $studienjahr, $zeitraum);
			if ($stgDaten === false)
			{
				return false;
			}
			$gesamtdaten[] = array(
				'stgKz' => $stg->studiengang_kz,
				'stgBezeichnung' => $stg->kurzbzlang,
				'stgArt' => $stg->typ,
				'orgForm' => $stg->orgform_kurzbz,
				'regelstudiendauer' => $stg->regelstudiendauer,
				'studiengangDaten' => $stgDaten
			);
		}

		return $gesamtdaten;
	}
	
	/**
	 * Basisdaten vom Ministerium
	 * @param type $studiengang_kz
	 * @param type $orgform
	 * @param type $studienjahr
	 * @param type $zeitraum
	 * @return boolean
	 * @throws Exception
	 */
	private function createStgDaten($studiengang_kz, $orgform, $studienjahr, $zeitraum) {
		$daten = array();
		$jahr = substr($studienjahr,0,4) + 0;
		$currentSemester = 'WS'.$jahr;
		$semesterCount = 1;
		$studienplatz = new studienplatz();
		while ($semesterCount <= ($zeitraum*2))
		{
			// Basisdaten laden
			// UV wird mit Basisdaten initialisiert
			
			if (!$studienplatz->load_studiengang_studiensemester_orgform($studiengang_kz, $currentSemester, $orgform))
			{			
				$this->errormsg = $studienplatz->errormsg;
				return false;
			}
			if (count($studienplatz->result) > 1)
			{
				throw new Exception('Anzahl Datensätze darf nicht größer 1 sein');
			}
			/*  keine Basisdaten vorhanden!!! Fehlermeldung? */
			if (count($studienplatz->result) == 0)
			{
				$daten[] = array(
					'studiensemester' => $currentSemester,				
					'gpzUv' => '',				
					'npzUv' => ''
				);
				
			}
			else 
			{
				$basisdaten = $studienplatz->result[0];
				$daten[] = array(
					'studiensemester' => $currentSemester,				
					'gpzUv' => ($basisdaten->gpz != null ? $basisdaten->gpz : ''),				
					'npzUv' => ($basisdaten->npz != null ? $basisdaten->npz : '')
				);
			}
			$semesterCount++;
			$currentSemester = incSemester($currentSemester);
		}
		
		return $daten;
	}
	
	/**
	 * {
		setupListe: [
				{
					bezeichnung: mysetup1,
					gridSetup: [
						{
							field: stgKz,
							visible: true,
							sortDirection: asc,
							sortPriority: 1,
							isGroupedBy: true,
							groupIndex: 1,
							width: 100
						}
					]
				},
				{
					etc.
				}
			]
		}
	 * @param  [type] $json [description]
	 * @param  [type] $uid  [description]
	 * @return [type]       [description]
	 */
	public function saveSetup($json, $uid)
    {
		$app = 'Studienplatzverwaltung';
		$appversion = '1.0';
		try {
			// aktuelle Setup Daten holen
			$appdaten = new appdaten();
			$result = $appdaten->getSetupByUid($app, $uid);
			if ($result === false)
			{
				$this->errormsg = $appdaten->errormsg;
				return false;
			}
			$daten = json_decode($appdaten->daten, true);        
			$setupListe = &$daten['setupListe'];
			$appdaten->updatevon = $uid;		
			$vorhanden = false;
			if (!$setupListe)
			{				
				$setupListe = array();
				$daten['setupListe'] = $setupListe;
			}
			for($i = 0; $i < count($setupListe); $i++) 
			{
				if ($setupListe[$i]['bezeichnung'] === $json['bezeichnung'])
				{
					// setup ersetzen
					$setupListe[$i]['gridSetup'] = $json['gridSetup'];
					$vorhanden = true;
					break;
				}
			}
			if (!$vorhanden)
			{
				// neues Setup speichern
				$setupListe[] = $json;
				$appdaten->app = $app;
				$appdaten->appversion = $appversion;
				$appdaten->insertvon = $uid;
				$appdaten->uid = $uid;
				$appdaten->bezeichnung = 'setup';
				$daten = array( 'setupListe' => $setupListe);
			}
			else {
				$appdaten->updatevon = $uid;
			}
		}  catch (Exception $e)
		{
			$this->errormsg = $e->getMessage();
			return false;
		}
		$appdaten->daten = json_encode($daten);
        $result = $appdaten->save(false);
        if ($result === false)
        {
            $this->errormsg = $appdaten->errormsg;
            return false;
        }
        return true;
    }
	
	public function deleteSetup($json, $uid)
	{
		$app = 'Studienplatzverwaltung';		
		// aktuelle Setup Daten holen
		$appdaten = new appdaten();
    	$appdaten->getSetupByUid($app, $uid);
        $daten = json_decode($appdaten->daten, true);        
        $setupListe = &$daten['setupListe'];
        $appdaten->updatevon = $uid;
		try {
			$vorhanden = false;
			if (!$setupListe)
			{				
				$setupListe = array();
				$daten['setupListe'] = $setupListe;
			}
			for($i = 0; $i < count($setupListe); $i++) 
			{
				if ($setupListe[$i]['bezeichnung'] === $json['bezeichnung'])
				{
					// setup löschen
					array_splice($setupListe, $i,1);
					$vorhanden = true;
					break;
				}
			}
			if (!$vorhanden)
			{
				$this->errormsg = 'Setup mit Bezeichnung '.$json['bezeichnung'].' nicht gefunden.';
				return false;
			}
		}  catch (Exception $e)
		{
			$this->errormsg = $e->getMessage();
			return false;
		}
		$appdaten->daten = json_encode($daten);
        $result = $appdaten->save(false);
        if ($result === false)
        {
            $this->errormsg = $appdaten->errormsg;
            return false;
        }
        return true;
	}
	
	public function getSetupList($uid)
    {
		$app = 'Studienplatzverwaltung';
		$appversion = '1.0';
		try {
			// aktuelle Setup Daten holen
			$appdaten = new appdaten();
			$result = $appdaten->getSetupByUid($app, $uid);
			if ($result === false)
			{
				$this->errormsg = $appdaten->errormsg;
				return false;
			}
			$daten = json_decode($appdaten->daten, true);        
			$setupListe = $daten['setupListe'];			
			if (!$setupListe)
			{				
				$setupListe = array();
				
			}
			$result = array();
			for($i = 0; $i < count($setupListe); $i++) 
			{
				$result[]['label'] = $setupListe[$i]['bezeichnung'];
			}
			
		}  catch (Exception $e)
		{
			$this->errormsg = $e->getMessage();
			return false;
		}		
        if ($result === false)
        {
            $this->errormsg = $appdaten->errormsg;
            return false;
        }
        return $result;
    }
	
	function getSetup($bezeichnung, $uid)
	{
		$app = 'Studienplatzverwaltung';		
		// aktuelle Setup Daten holen
		$appdaten = new appdaten();
    	$result = $appdaten->getSetupByUid($app, $uid);
        $daten = json_decode($appdaten->daten, true);        
        $setupListe = $daten['setupListe'];
        $appdaten->updatevon = $uid;
		try {
			$vorhanden = false;
			if (!$setupListe)
			{				
				$setupListe = array();
				$daten['setupListe'] = $setupListe;
			}
			for($i = 0; $i < count($setupListe); $i++) 
			{
				if ($setupListe[$i]['bezeichnung'] === $bezeichnung)
				{
					return $setupListe[$i]['gridSetup'];
				}
			}
			if (!$vorhanden)
			{
				$this->errormsg = 'Setup mit Bezeichnung '.$bezeichnung.' nicht gefunden.';
				return false;
			}
		}  catch (Exception $e)
		{
			$this->errormsg = $e->getMessage();
			return false;
		}		
        if ($result === false)
        {
            $this->errormsg = $appdaten->errormsg;
            return false;
        }
        return true;
	}
	
	public function syncFoebisStg()
	{
		$studiengangSPV = new studiengangSPV();
		$client = new AqaFoebisClient();
		// aktuelles Studienjahr lt. Aqa (bei der FÖBis-Abrechnung geht das
		// Studienjahr von Oktober bis September!)
		$jahr = date("Y"); 
		$monat = date("m"); 
		if ($monat < 10)
		{
			$jahr--;
		}
		for ($studienjahr = 2010; $studienjahr <= $jahr; $studienjahr++)
		{
			
			if (!$studiengangSPV->getAll($studienjahr, 1)) 
			{
				$this->errormsg = $studiengangSPV->errormsg;
				return false;
			}

			$studiengangList = $studiengangSPV->result;
			$runde = 3;
			if ($studiengangList != null)
			{
				foreach ($studiengangList as $stg)
				{			
					if ($studienjahr == $jahr && $monat >= 10)
					{
						$runde = 1;
					}
					else if ($studienjahr == $jahr && $monat <= 5)
					{
						$runde = 2;
					}

					$result = $client->listFoebisAbrechnungStudiengang(
						formatStudiengangKz($stg->studiengang_kz), 
						$studienjahr, 
						$runde);
					if ($result === false)
					{
						$this->errormsg = $client->errormsg;
						return false;
					}
				}
			}
		}
		return true;
	}
	
}


/**
 * Helper Klasse um den Studentenstatus für einen mit der UV korrespondierenden
 * Zeitraum (Studienjahr + Semester) zu holen. Ursprünglich wurden dafür die
 * Methoden aus der Klasse prestudent verwendet. Diese wurde jedoch zu langsam,
 * daher musste eine neue Abfrage her, wo alles in einer Abfrage geholt wird.
 */
class StudentenStatus extends basis_db {
	
	public $result;
	
	
	public function getAll($studienjahr, $zeitraum)
	{
		// Semesterliste für where klausel erzeugen:
		$startSemester= 'WS'.substr($studienjahr,0,4);		
		$semesterList = generateSemesterList($startSemester,($zeitraum*2)-1);
		$semesterList_comma_separated = "'".join("','",$semesterList)."'";
		$sql = "SELECT
					count(*) Anzahl,
					sum(case when tbl_prestudentstatus.status_kurzbz='Interessent' then 1 else 0 end) as interessenten,
					sum(case when tbl_prestudentstatus.status_kurzbz='Bewerber' then 1 else 0 end) as bewerber,
					sum(case when tbl_prestudentstatus.status_kurzbz='Abgewiesener' then 1 else 0 end) as abgewiesene,
					sum(case when tbl_prestudentstatus.status_kurzbz='Aufgenommener' then 1 else 0 end) as aufgenommener,
					sum(case when tbl_prestudentstatus.status_kurzbz='Student' then 1 else 0 end) as studenten,
					sum(case when tbl_prestudentstatus.status_kurzbz='Abbrecher' then 1 else 0 end) as abbrecher,
					sum(case when tbl_prestudentstatus.status_kurzbz='Unterbrecher' then 1 else 0 end) as unterbrecher,
					sum(case when tbl_prestudentstatus.status_kurzbz='Diplomand' then 1 else 0 end) as diplomand,
					sum(case when tbl_prestudentstatus.status_kurzbz='Wartender' then 1 else 0 end) as wartende,
					sum(case when tbl_prestudentstatus.status_kurzbz='Incoming' then 1 else 0 end) as incoming,
					sum(case when tbl_prestudentstatus.status_kurzbz='Outgoing' then 1 else 0 end) as outgoing,
					sum(case when tbl_prestudentstatus.status_kurzbz='Absolvent' then 1 else 0 end) as absolvent,
					tbl_prestudent.studiengang_kz,
					tbl_prestudentstatus.studiensemester_kurzbz,
					of.bisorgform_kurzbz as orgform_kurzbz
				FROM 
					public.tbl_prestudent
					JOIN public.tbl_prestudentstatus USING(prestudent_id)
					JOIN public.tbl_studiengang USING(studiengang_kz)
					JOIN bis.tbl_orgform as of on (COALESCE(tbl_prestudentstatus.orgform_kurzbz, tbl_studiengang.orgform_kurzbz) = of.orgform_kurzbz)
				WHERE	
					bismelden=true AND tbl_prestudentstatus.studiensemester_kurzbz IN ($semesterList_comma_separated)
				GROUP BY
					tbl_prestudent.studiengang_kz,
					tbl_prestudentstatus.studiensemester_kurzbz,
					of.bisorgform_kurzbz
				ORDER BY 
					tbl_prestudent.studiengang_kz, tbl_prestudentstatus.studiensemester_kurzbz
			";
		if(!$this->db_query($sql))
		{		
			return false;
		}
		
		$this->result = array();
		while($row = $this->db_fetch_object())
		{
			$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz]['interessenten'] = $row->interessenten;			
			$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz]['bewerber'] = $row->bewerber;			
			$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz]['abgewiesene'] = $row->abgewiesene;			
			$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz]['aufgenommener'] = $row->aufgenommener;			
			$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz]['studenten'] = $row->studenten;			
			$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz]['abbrecher'] = $row->abbrecher;			
			$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz]['unterbrecher'] = $row->unterbrecher;			
			$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz]['diplomand'] = $row->diplomand;			
			$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz]['wartende'] = $row->wartende;			
			$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz]['incoming'] = $row->incoming;			
			$this->result[$row->studiensemester_kurzbz][$row->studiengang_kz][$row->orgform_kurzbz]['outgoing'] = $row->outgoing;			
        }
		return true;
		
	}
	
		
}


/**
 * Klasse wird nur benutzt um die notwendigen Studiengänge für eine Umschichtung
 * zu laden, da es diese Abfrage sonst noch nirgends gibt. Evt. kann man sie auch 
 * woanders brauchen und in den allgemeinen FHComplete-Teil verschieben.
 */
class studiengangSPV extends basis_db {
	
	/** @var DB-Result */
	private $result;
	
	protected $regelstudiendauer;
	
	protected $studiengang_kz;		// integer
	protected $kurzbz;				// varchar(5)
	protected $kurzbzlang;			// varchar(10)
	protected $bezeichnung;		// varchar(128)
	protected $english;			// varchar(128)
	protected $typ;				// char(1)
	protected $farbe;				// char(6)
	protected $email;				// varchar(64)
	protected $max_semester;		// smallint
	protected $max_verband;		// char(1)
	protected $max_gruppe;			// char(1)
	protected $erhalter_kz;		// smallint
	protected $bescheid;			// varchar(256)
	protected $bescheidbgbl1;		// varchar(16)
	protected $bescheidbgbl2;		// varchar(16)
	protected $bescheidgz;			// varchar(16)
	protected $bescheidvom;		// Date
	protected $titelbescheidvom;	// Date
	protected $ext_id;				// bigint
	protected $orgform_kurzbz;		// varchar(3)
	protected $zusatzinfo_html;	// text
	protected $sprache;			// varchar(16)
	protected $testtool_sprachwahl;// boolean
	protected $studienplaetze;		// smallint
	protected $oe_kurzbz;			// varchar(32)

	protected $kuerzel;	// = typ + kurzbz (Bsp: BBE)
	protected $studiengang_typ_arr = array(); 	// Array mit den Studiengangstypen
	protected $kuerzel_arr = array();			// Array mit allen Kurzeln Index=studiengangs_kz
	protected $moodle;		// boolean
	protected $lgartcode;	//integer
	protected $mischform;	// boolean
	protected $projektarbeit_note_anzeige; // boolean
	protected $bezeichnung_arr = array();
    
    protected $beschreibung; 
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function __get($name)
	{
		return $this->$name;
	}
	
	/**
	 * 
	 * @param type $studienjahr
	 * @param type $zeitraum
	 * @return boolean
	 */
	public function getAll($studienjahr, $zeitraum) {
		
		if(!is_numeric($zeitraum))
		{
			$this->errormsg = 'Zeitraum muss eine gueltige Zahl sein';
			return false;
		}
		
		if(!is_numeric(substr($studienjahr,0,4)))
		{
			$this->errormsg = 'Studienjahr ungültig. Muss als String in der Form z.B: 2013/14  für Studienjahr 2013 übergeben werden.';
			return false;
		}
		
		// Semesterliste für where klausel erzeugen:
		$startSemester= 'WS'.substr($studienjahr,0,4);		
		$semesterList = generateSemesterList($startSemester,($zeitraum*2)-1);
		$semesterList_comma_separated = "'".join("','",$semesterList)."'";
		
		$qry = "select distinct "
			//	. "sem.studiensemester_kurzbz,"
				. "stg.studiengang_kz, "
				. "stg.kurzbz,"
				. "stg.kurzbzlang,"
				. "stg.bezeichnung as stg_bezeichnung,"
				. "stg.typ as stg_typ,"  // b, m, d, e, l
				. "stg.erhalter_kz,"
				. "plan.orgform_kurzbz,"
				. "plan.regelstudiendauer,"
				. "plan.version as plan_version,"
				. "ordnung.version as ordnung_version,"
				. "ordnung.ects ".			
				"from public.tbl_studiengang as stg join lehre.tbl_studienordnung as ordnung using (studiengang_kz) ".
				"join lehre.tbl_studienordnung_semester as sem using(studienordnung_id) ".
				"join ".
				// Letzte Version vom Studienplan holen. Per Konvention gibt es eine neue Studienordnung
				// wenn sich z.B. die Regelstudiendauer ändert. Daher erfolgt hier keine
				// extra Selektion über den Zeitraum. 
				"( ".
				"	select subplan.* from lehre.tbl_studienplan as subplan join ".
				"   ( ".
				"      select max(version) as v,studienordnung_id from lehre.tbl_studienplan group by studienordnung_id ".
				"	) as subsubplan on(subplan.version=subsubplan.v and subplan.studienordnung_id=subsubplan.studienordnung_id) ".
				") as plan using(studienordnung_id) where sem.studiensemester_kurzbz in ($semesterList_comma_separated) ".
				"and plan.orgform_kurzbz is not null and (plan.orgform_kurzbz='BB' or plan.orgform_kurzbz='VZ' or plan.orgform_kurzbz='VBB')".  // nicht alle Orgformen sind für die Umschichtung relevant
				"and stg.typ  in ('b','m') ".
			    "order by stg.kurzbz";
		if(!$this->db_query($qry))
		{		
			return false;
		}
		
		while($row = $this->db_fetch_object())
		{
            $rec = new studiengangSPV();
            $this->mapRow($rec, $row);
			$this->result[] = $rec;		
        }
		return true;
	}
	
	
	
	/**
	 * Helper
	 * @param type $target
	 * @param type $row
	 */
	private function mapRow($target,$row) 
	{		
		//$target->studiensemester_kurzbz=$row->studiensemester_kurzbz;
		$target->studiengang_kz=$row->studiengang_kz;		
		$target->kurzbz=$row->kurzbz;
		$target->kurzbzlang=$row->kurzbzlang;				
		$target->bezeichnung=$row->stg_bezeichnung;
		$target->typ=$row->stg_typ;
		$target->erhalter_kz=$row->erhalter_kz;
		$target->orgform_kurzbz=$row->orgform_kurzbz;
		$target->regelstudiendauer=$row->regelstudiendauer;
		$target->plan_version=$row->plan_version;		
		$target->ordnung_version=$row->ordnung_version;
		$target->ects=$row->ects;		
	}
	
}

/**
 *
 */
class UVGesamtCSVExporter
{

	public function export($appdaten)
	{
		$csv = '"StgKz","Stg","Art","OrgForm","Studienjahr", Semester","Beginn","Ende","Aufnahme","Regelstudiendauer",'.
			'"Bewerber","Abbrecher","Interessenten","Studierende","FÖBisAktive","GPZ-BD","GPZ-UV","GPZ-Diff","NPZ-BD",'.
			'"NPZ-UV","NPZ-Diff","TFP","TFP-Grenze"'."\n";
		// eigentliche UV Daten aus JSON Struktur holen
		$daten = json_decode($appdaten->daten, true);
		$gesamtDaten = $daten['gesamtDaten'];
		// Anzahl Studienjahre holen
		$zeitraum = &$daten['zeitraum'];

		for ($j=0; $j < count($gesamtDaten); $j++) 
		{ 
			$stgKz = $gesamtDaten[$j]['stgKz'];
			$stgBezeichnung = $gesamtDaten[$j]['stgBezeichnung'];
			$stgArt = $gesamtDaten[$j]['stgArt'];
			$orgForm = $gesamtDaten[$j]['orgForm'];
			$regelstudiendauer = $gesamtDaten[$j]['regelstudiendauer'];
			$beginn = "";
			$ende = "";
			$aufnahme = "";
			$stgDaten = $gesamtDaten[$j]['studiengangDaten'];
			
			for ($i=0; $i < count($stgDaten); $i++)
			{ 
				$gpzDiff = $stgDaten[$i]['gpzUv'] - $stgDaten[$i]['gpzBd'];
				$npzDiff = $stgDaten[$i]['npzUv'] - $stgDaten[$i]['npzBd'];
				$npzBdGesamt = $stgDaten[$i]['npzBdGesamt'];
				$tfp = null;

				// Toleranzförderplätze berechnen
				if ($npzBdGesamt <= 100) 
				{
					$tfp = $npzBdGesamt * 0.1;
				} 
				else if ($npzBdGesamt <= 500)
				{
					$tfp = 10.0 + ($npzBdGesamt - 100) * 0.08;
				}  
				else 
				{
					$tfp = (42.0 + ($npzBdGesamt - 500) * 0.05);				
				};
				$tfpGrenze = $npzBdGesamt - $tfp;
				
				$beginn = @$stgDaten[$i]['beginn'];
				$ende = @$stgDaten[$i]['ende'];
				$aufnahme = @$stgDaten[$i]['aufnahme'];

				// Studienjahr generieren
				$jahrSemester = substr($stgDaten[$i]['studiensemester'],2);
                if (substr($stgDaten[$i]['studiensemester'],0,2) === 'SS') $jahrSemester--;                                                    
                $studienjahr = $jahrSemester . '/' . ((++$jahrSemester)-2000);

				$csv.="\"$stgKz\",\"$stgBezeichnung\",\"$stgArt\",\"$orgForm\",\"$studienjahr\",\"{$stgDaten[$i]['studiensemester']}\",".
				"\"$beginn\",\"$ende\",\"$aufnahme\",\"$regelstudiendauer\",\"{$stgDaten[$i]['bewerber']}\",".
				"\"{$stgDaten[$i]['dropout']}\",\"{$stgDaten[$i]['interessenten']}\",\"{$stgDaten[$i]['studierende']}\",\"{$stgDaten[$i]['foebisAktive']}\",".
				"\"{$stgDaten[$i]['gpzBd']}\",\"{$stgDaten[$i]['gpzUv']}\",\"$gpzDiff\",\"{$stgDaten[$i]['npzBd']}\",\"{$stgDaten[$i]['npzUv']}\",".
				"\"$npzDiff\",\"$tfp\",\"$tfpGrenze\"\n";
			}		

		} 
		return $csv;
	}
}



class XMLExporter 
{

	private $orgformPrefix = array('VZ','BB','VBB');
	
	/**
	 * XML String entsprechend der Schema-Definition generieren.
	 * @param  string $appdaten
	 * @return string
	 */
	public function export($appdaten)
	{
		$date = date('d.m.Y h:i:s', time());
		$daten = json_decode($appdaten->daten, true);
		$xml = '<?xml version="1.0" encoding="utf-8"?>'."\n".
			   '<!--'."\n".
			   '    | Umschichtungsvorhaben'."\n".
			   '    | Studienjahr: '.$appdaten->bezeichnung."\n".
			   '    | Versionsnr. (intern): '.$appdaten->version."\n".
			   '    | Datum: '.$date."\n".
			   '    | generated by FHComplete'."\n".
			   '-->'."\n".
			   '<Erhalter ErhKz="'.UV_ERHALTER.'">'."\n".
			   $this->generateStudiengaenge($daten, $appdaten->bezeichnung)."\n".
			   '</Erhalter>';
		return $xml;
	}

	private function generateStudiengaenge($daten, $studienjahrStart) 
	{
		$xmlStg = '';
		$gesamtDaten = &$daten['gesamtDaten'];
		// Anzahl Studienjahre holen
		$zeitraum = $daten['zeitraum'];
		// Liste der Studiengänge erzeugen (gruppiert nach Kennzahl und Art)
		$stgList = $this->extractStudiengaenge($gesamtDaten);
		foreach ($stgList as $stg) {
			$xmlStg.='<Studiengang UVArt="STG" StgKz="'.$stg['stgKz'].'" StudiengangBezeichnung="" StudiengangsArt="'.$this->translateArt($stg['stgArt']).'" Beginn="" Ende="" Foerdergruppe="technisch" Foerdersatz="'.UV_FOERDERSATZ_TECHNISCH.'">'."\n";
           	// Studienjahr loop
           	$jahr = substr($studienjahrStart,0,4) + 0;
			$currentJahr = $jahr;			
			$xmlStudienjahre = '';
			while ($currentJahr <= ($jahr+$zeitraum-1))
			{
				$currentWS = 'WS'.$currentJahr;
				$currentSS = 'SS'.($currentJahr + 1);
				$xmlStudienjahre.='<Studienjahr Studienjahr="'.$currentJahr.'/'.($currentJahr-1999).'" Standort="'.UV_STANDORT.'" ';
				// Regelstudiendauer ist bei jeder jedem Semester gespeichert. Im Export-File gibt es sie jedoch nur einmal
				// pro Studienjahr.
				$regelstudiendauer = null;
				// Semesterdaten einfügen
				
				// Loop orgform
				foreach ($this->orgformPrefix as $orgFormPrefix)
				{
					//if (in_array($orgFormPrefix,$stg['orgForm']))
					if (array_key_exists($orgFormPrefix,$stg['orgForm']))
					{
						$datenWS = $this->getSemesterdaten($stg['orgForm'][$orgFormPrefix], $currentWS);
						$datenSS = $this->getSemesterdaten($stg['orgForm'][$orgFormPrefix], $currentSS);
						$xmlStudienjahre.=$orgFormPrefix.'NPZWS="'.$datenWS['npzUv'].'" '.$orgFormPrefix.'GPZWS="'.$datenWS['gpzUv'].'" '.$orgFormPrefix.'NPZSS="'.$datenSS['npzUv'].'" '.$orgFormPrefix.'GPZSS="'.$datenSS['gpzUv'].'" '.$orgFormPrefix.'Aufname="1" ';						
						if ($regelstudiendauer == NULL) $regelstudiendauer = $datenWS['regelstudiendauer'];
					}
					else
					{
						// auch im Studiengang nicht vorhandene Orgformen müssen angegeben müssen angegeben werden
						$xmlStudienjahre.=$orgFormPrefix.'NPZWS="" '.$orgFormPrefix.'GPZWS="" '.$orgFormPrefix.'NPZSS="" '.$orgFormPrefix.'GPZSS="" '.$orgFormPrefix.'Aufname="" ';
					}
				}
				
				$xmlStudienjahre.=" Regelstudiendauer=\"$regelstudiendauer\"";
				$xmlStudienjahre.=" Umschichtbar=\"1\" />\n";
				$currentJahr++;
			}
			$xmlStg.=$xmlStudienjahre;
			$xmlStg.="</Studiengang>\n";
		}
        
        return $xmlStg;
	}

	
	private function getSemesterdaten(&$studiengangdaten, $semester)
	{		
		for ($i=0; $i < count($studiengangdaten); $i++)
		{ 
			if ($studiengangdaten[$i]['studiensemester'] === $semester)
			{
				return $studiengangdaten[$i];
			}
		}
		return false;
	}
	
	private function getStudienjahrData(&$gesamtDaten, $wintersemester, $stgKz, $stgArt, $orgForm )
	{
		$result = array();
		for ($i=0; $i < count($gesamtDaten); $i++)
		{ 
			if ($gesamtDaten[$i]['stgKz'] == $stgKz 
					&& $gesamtDaten[$i]['stgArt'] == $stgArt
					&& $gesamtDaten[$i]['orgForm'] == $orgForm)	
			{
				// Studiengang gefunden
				
			}
		}
		return $result;
	}

    /**
     * Helper zum Transformieren der Daten zur Aufbereitung für den XML-Export
     */
	private function extractStudiengaenge(&$stgDaten)
	{
		$stg = array();
		for ($i=0; $i < count($stgDaten); $i++) 
		{ 
			$indexFound = $this->containsStg($stg, $stgDaten[$i]['stgKz'],$stgDaten[$i]['stgArt']);
			if ($indexFound === false)
			{
				$stg[] = array('stgKz' => $stgDaten[$i]['stgKz'], 
                               'stgArt' => $stgDaten[$i]['stgArt'], 
                               'orgForm' => array( $stgDaten[$i]['orgForm'] => $stgDaten[$i]['studiengangDaten'] ));
			}
			else
			{
				// add orgForm
                if (!array_key_exists($stgDaten[$i]['orgForm'],$stg[$indexFound]['orgForm']))
                {
                    $stg[$indexFound]['orgForm'][] = array( $stgDaten[$i]['orgForm'] => $stgDaten[$i]['studiengangDaten'] );
                }
				//$this->maybeAddOrgForm($stg[$indexFound]['orgForm'], $stgDaten[$i]['orgForm']);				
			}			
		}
		return $stg;
	}

    // @deprecated
	private function maybeAddOrgForm(&$orgFormList, $orgForm)
	{
		for ($i=0; $i < count($orgFormList); $i++)
		{ 
			if ($orgFormList[$i] == $orgForm)	
			{
				return;
			}
		}
		$orgFormList[] = $orgForm;
	}

	/**
	 * Helper um nachzusehen, ob Studiengang schon in Liste ist
	 * @param  array $stgList 
	 * @param  string $stgKz   
	 * @param  string $stgArt  
	 * @return boolean/integer false wenn Kennzahl und Art nocht nicht in Liste enthalten sind; oder index des gefundenen Datensatz
	 */
	private function containsStg(&$stgList, $stgKz, $stgArt)
	{
		for ($i=0; $i < count($stgList); $i++)
		{ 
			if ($stgList[$i]['stgKz'] == $stgKz && $stgList[$i]['stgArt'] == $stgArt)	
			{
				return $i;
			}
		}
		return false;
	}
	
	private function translateArt($art)
	{
		if ($art == 'b') return 'Ba';
		if ($art == 'm') return 'Ma';
	}

}
