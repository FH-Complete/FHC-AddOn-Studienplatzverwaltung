<?php
/*
 * aqa_foebis_semester.class.php
 * 
 * Copyright 2014 Technikum-Wien
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty oferr
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 *
 * Authors: Werner Masik <werner@gefi.at>
 */
require_once(dirname(__FILE__).'/../../../include/basis_db.class.php');
require_once(dirname(__FILE__).'/../../../include/datum.class.php');
require_once(__DIR__ .'/../../../include/functions.inc.php');

class aqa_foebis_semester extends basis_db
{
	/** @var $new boolean */
	private $new = true;		
	/** @var DB-Result */
	private $result;
	/** @var object */
	public $foebis = array();	

	//Tabellenspalten
	/** @var integer */
	private $foebis_semester_id;	
	/** @var string */
	private $matrikelnr;	
	/** @var integer */
	private $studiengang_kz;	
	/** @var string */
	private $orgform_kurzbz; 	
	/** @var string */
	private $studiensemester_kurzbz;
	/** @var integer */
	private $regelstudiendauer;  	
	/** @var integer */
 	private $guthaben;
 	/** @var integer */
 	private $gefoerdert;
 	/** @var integer */
 	private $maxguthaben;
 	/** @var integer */
 	private $stud_status;
 	/** @var date */
 	private $meldedatum;
	
	
	/** @var timestamp */
	private $updateamum;	
	/** @var string */
	private $updatevon;		
	/** @var timestamp */
	private $insertamum;      		
	/** @var string */
	private $insertvon;     	

	private $datum_obj;

	/**
	 * Konstruktor
	 * @param integer ID des Studienplatz der geladen werden soll (Default=null)
	 */
	public function __construct($foebis_semester_id=null)
	{
		parent::__construct();
		
		$this->datum_obj = new datum();
		if(!is_null($foebis_semester_id))
			$this->load($foebis_semester_id);
	}

	public function __set($name,$value)
	{
		switch ($name)
		{
			case 'studiengang_kz':
			case 'guthaben': 
            case 'regelstudiendauer':			
			case 'foebis_semester_id':
			case 'gefoerdert':
			case 'maxguthaben':
			case 'stud_status':
				if ($value != null && !is_numeric($value))
					throw new Exception("Attribute $name must be numeric! ($value)");
				$this->$name=$value;
				break;			
			default:
				$this->$name=$value;
		}
	}

	public function __get($name)
	{
		return $this->$name;
	}
	
	
	/**
	 * Laedt einzelne Personendaten der ID $foebis_semester_id	
	 * @param integer $foebis_semester_id ID des zu ladenden Datensatzes
	 * @return boolean Description true wenn ok, false im Fehlerfall
	 */
	public function load_foebis($foebis_semester_id	)
	{
		//Pruefen ob adress_id eine gueltige Zahl ist
		if(!is_numeric($foebis_semester_id	) || $foebis_semester_id	 == '')
		{
			$this->errormsg = 'foebis_semester_id muss eine Zahl sein';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM addon.tbl_aqa_foebis_semester WHERE foebis_semester_id=".$this->db_add_param($foebis_semester_id, FHC_INTEGER, false);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->mapRow($this, $row);
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}

		return true;
	}

	/**
	 * Helper
	 * @param type $target
	 * @param type $row
	 */
	private function mapRow($target,$row) {		
		$target->foebis_semester_id	= $row->foebis_semester_id;
		$target->matrikelnr			= $row->matrikelnr;
		$target->studiengang_kz 	= $row->studiengang_kz;
		$target->orgform_kurzbz		= $row->orgform_kurzbz;
		$target->studiensemester_kurzbz	= $row->studiensemester;		
		$target->regelstudiendauer	= $row->regelstudiendauer;
		$target->ausbildungssemester= $row->ausbildungssemester;
		$target->guthaben			= $row->guthaben;			
		$target->gefoerdert			= $row->gefoerdert;
		$target->maxguthaben	    = $row->maxguthaben	;
		$target->stud_status		= $row->stud_status;
		$target->meldedatum			= $this->datum_obj->mktime_fromdate($row->meldedatum);		
		$target->updateamum		= $row->updateamum;
		$target->updatevon		= $row->updatevon;
		$target->insertamum		= $row->insertamum;
		$target->insertvon		= $row->insertvon;
        $target->new            = false;
	}
	
	/**
	 * Laedt alle Datensätze zu einer Person. Ergebnis
	 * steht in result.
	 * @param string studiensemester_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load_foebis_person($matrikelnr)
	{
		//Pruefen ob $studiengang_kz eine gueltige Zahl ist
		if(!is_numeric($foebis_semester_id) || $foebis_semester_id == '')
		{
			$this->errormsg = "foebis_semester_id muss eine gültige Zahl sein";
			return false;
		}

		//Lesen der Daten aus der Datenbank
		$qry = "SELECT * FROM addon.tbl_foebis_person WHERE matrikelnr=".
				$this->db_add_param($matrikelnr, FHC_INTEGER, false).' '.
				"ORDER BY meldedatum";
        $this->result = array();
        if(!$this->db_query($qry))
		{			
			return false;
		}

		while($row = $this->db_fetch_object())
		{
            $rec = new aqa_foebis_person();
            $this->mapRow($rec, $row);
			$this->result[] = $rec;		
        }
		return true;
	}


	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		if(!is_numeric($this->foebis_semester_id) && $this->foebis_semester_id!='')
		{
			$this->errormsg='foebis_semester_id enthaelt ungueltige Zeichen';
			return false;
		}
		if(!is_numeric($this->studiengang_kz) && $this->studiengang_kz!='')
		{
			$this->errormsg='studiengang_kz enthaelt ungueltige Zeichen';
			return false;
		}
		/*
		if(mb_strlen($this->orgform_kurzbz)>3)
		{
			$this->errormsg = 'orgform_kurzbz darf nicht länger als 3 Zeichen sein';
			return false;
		}*/
		if(mb_strlen($this->studiensemester_kurzbz)>16)
		{
			$this->errormsg = 'studiensemester_kurzbz darf nicht länger als 16 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->matrikelnr)>15)
		{
			$this->errormsg = 'matrikelnr darf nicht länger als 16 Zeichen sein';
			return false;
		}
		if(!is_numeric($this->regelstudiendauer) && $this->regelstudiendauer!='')
		{
			$this->errormsg='regelstudiendauer enthaelt ungueltige Zeichen';
			return false;
		}
		if(!is_numeric($this->guthaben) && $this->guthaben!='')
		{
			$this->errormsg='guthaben enthaelt ungueltige Zeichen';
			return false;
		}
		if(!is_numeric($this->maxguthaben) && $this->maxguthaben!='')
		{
			$this->errormsg='maxguthaben enthaelt ungueltige Zeichen';
			return false;
		}
		if(!is_numeric($this->ausbildungssemester) && $this->ausbildungssemester!='')
		{
			$this->errormsg='ausbildungssemester enthaelt ungueltige Zeichen';
			return false;
		}
		if(!is_numeric($this->gefoerdert) && $this->gefoerdert!='')
		{
			$this->errormsg='gefoerdert enthaelt ungueltige Zeichen';
			return false;
		}
		if(!is_numeric($this->stud_status) && $this->stud_status!='')
		{
			$this->errormsg='stud_status enthaelt ungueltige Zeichen';
			return false;
		}
		
		
		$this->errormsg = '';
		return true;
	}
	
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $foebis_semester_id aktualisiert
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO addon.tbl_aqa_foebis_semester ('.
				 'matrikelnr, studiengang_kz, orgform_kurzbz, '.
				 'studiensemester, regelstudiendauer, ausbildungssemester, guthaben, gefoerdert,maxguthaben, stud_status, '.
			     'meldedatum,insertamum, insertvon, '.
			     'updateamum, updatevon) VALUES('.		
			      $this->db_add_param($this->matrikelnr).', '.	      
			      $this->db_add_param($this->studiengang_kz, FHC_INTEGER).', '.
			      $this->db_add_param($this->orgform_kurzbz).', '.
			      $this->db_add_param($this->studiensemester_kurzbz).', '.			     
			      $this->db_add_param($this->regelstudiendauer, FHC_INTEGER).', '.				 
			      $this->db_add_param($this->ausbildungssemester, FHC_INTEGER).', '.
				  $this->db_add_param($this->guthaben, FHC_INTEGER).', '.
				  $this->db_add_param($this->gefoerdert, FHC_INTEGER).', '.
				  $this->db_add_param($this->maxguthaben, FHC_INTEGER).', '.
				  $this->db_add_param($this->stud_status, FHC_INTEGER).', '.
				  $this->db_add_param(date("Y-m-d",$this->meldedatum)).', '.
				  'now(), '.
			      $this->db_add_param($this->insertvon).', '.
			      'now(), '.
			      $this->db_add_param($this->updatevon).');';
		}
		else
		{
			//Pruefen ob foebis_semester_id eine gueltige Zahl ist
			if(!is_numeric($this->foebis_semester_id))
			{
				$this->errormsg = 'foebis_semester_id muss eine gueltige Zahl sein';
				return false;
			}
			$qry='UPDATE addon.tbl_aqa_foebis_semester SET'.				
				' matrikelnr='.$this->db_add_param($this->matrikelnr).', '.
				' studiengang_kz='.$this->db_add_param($this->studiengang_kz, FHC_INTEGER).', '.
				' orgform_kurzbz='.$this->db_add_param($this->orgform_kurzbz).', '.
				' studiensemester='.$this->db_add_param($this->studiensemester_kurzbz).', '.
		      	' regelstudiendauer='.$this->db_add_param($this->regelstudiendauer, FHC_INTEGER, false).', '.				
		      	' ausbildungssemester='.$this->db_add_param($this->ausbildungssemester, FHC_INTEGER, false).', '.
		      	' guthaben='.$this->db_add_param($this->guthaben, FHC_INTEGER, false).', '.		     
				' gefoerdert='.$this->db_add_param($this->gefoerdert, FHC_INTEGER, false).', '.		     
				' maxguthaben='.$this->db_add_param($this->maxguthaben, FHC_INTEGER, false).', '.
				' stud_status='.$this->db_add_param($this->stud_status, FHC_INTEGER, false).', '.
				' meldedatum='.$this->db_add_param(date("Y-m-d",$this->meldedatum)).', '.
		      	' updateamum= now(), '.
		      	' updatevon='.$this->db_add_param($this->updatevon).		      	
		      	' WHERE foebis_semester_id='.$this->db_add_param($this->foebis_semester_id, FHC_INTEGER, false).';';
		}
        
		if($this->db_query($qry))
		{
			if($this->new)
			{
				//naechste ID aus der Sequence holen
				$qry="SELECT currval('addon.tbl_aqa_foebis_semester_foebis_semester_id_seq') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->foebis_semester_id = $row->id;
						$this->db_query('COMMIT');
					}
					else
					{
						$this->db_query('ROLLBACK');
						$this->errormsg = "Fehler beim Auslesen der Sequence";
						return false;
					}
				}
				else
				{
					$this->db_query('ROLLBACK');
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					return false;
				}
			}

		}
		else
		{			
			return false;
		}
		return $this->foebis_semester_id;
	}

	
	public function deleteByStgKz($studiengang_kz,$semester)
	{		
		if(!is_numeric($studiengang_kz) || $studiengang_kz == '')
		{
			$this->errormsg = 'studiengang_kz muss eine gültige Zahl sein'."\n";
			return false;
		}

		//loeschen des Datensatzes
		$qry="DELETE FROM addon.tbl_aqa_foebis_semester "
				. "WHERE "
				. "studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER, false)." "
				. "AND studiensemester=".$this->db_add_param($semester);

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{			
			return false;
		}
	}

	public function deleteByPersonKz($personKz)
	{		
		
		//loeschen des Datensatzes
		$qry="DELETE FROM addon.tbl_aqa_foebis_semester "
				. "WHERE "
				. "matrikelnr=".$this->db_add_param($personKz)
				. ";";

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{			
			return false;
		}
	}
	
	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $foebis_semester_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($foebis_semester_id)
	{
		//Pruefen ob studienplatz_id eine gueltige Zahl ist
		if(!is_numeric($foebis_semester_id) || $foebis_semester_id == '')
		{
			$this->errormsg = 'foebis_semester_id muss eine gültige Zahl sein'."\n";
			return false;
		}

		//loeschen des Datensatzes
		$qry="DELETE FROM addon.tbl_aqa_foebis_semester WHERE foebis_semester_id=".$this->db_add_param($foebis_semester_id, FHC_INTEGER, false).";";

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{			
			return false;
		}
	}
}
?>
