<?php
/*
 * aqa_foebis_stg.class.php
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
require_once(__DIR__ .'/../../../include/functions.inc.php');

class aqa_foebis_stg extends basis_db
{
	/** @var $new boolean */
	private $new = true;		
	/** @var DB-Result */
	private $result;
	/** @var object */
	public $foebis = array();	

	//Tabellenspalten
	/** @var integer */
	private $foebis_stg_id;		
	/** @var integer */
	private $studiengang_kz;	
	/** @var string */
	private $orgform_kurzbz; 	
	/** @var string */
	private $studiensemester_kurzbz;
	/** @var integer */
	private $regelstudiendauer;  	
	/** @var string */
	private $stgartbez;					
	/** @var integer */
	private $jahr;     
	/** @var integer */
	private $monat;   
	/** @var integer */
	private $runde;	
	/** @var string */
	private $foerdergruppe;   
	/** @var integer */
	private $aq;   
	/** @var integer */
	private $npz;   
	/** @var integer */
	private $r1_plaetze_bezahlt;
	/** @var integer */
	private $r2_plaetze_bezahlt;
	/** @var integer */
	private $r3_plaetze_bezahlt;
	/** @var integer */
	private $r2_foebisaktive;
	/** @var integer */
	private $r3_foebisaktive;
	/** @var integer Korrekturwert für Monate 10 bis 12 der in Runde 2 relevant ist */
	private $r2r1_foebisaktive_korr;
	/** @var integer Korrekturwert für Monate 4 und 5 der in Runde 3 relevant ist */
	private $r3r2_foebisaktive_korr;
	
	
	
	/** @var timestamp */
	private $updateamum;	
	/** @var string */
	private $updatevon;		
	/** @var timestamp */
	private $insertamum;      		
	/** @var string */
	private $insertvon;      	

	/**
	 * Konstruktor
	 * @param integer ID des Studienplatz der geladen werden soll (Default=null)
	 */
	public function __construct($foebis_stg_id=null)
	{
		parent::__construct();
		
		if(!is_null($foebis_stg_id))
			$this->load($foebis_stg_id);
	}

	public function __set($name,$value)
	{
		switch ($name)
		{
			case 'studiengang_kz':
			case 'jahr':
			case 'monat': 
            case 'regelstudiendauer':			
			case 'foebis_stg_id':
			case 'aq':
			case 'runde':
			case 'npz':
			case 'r1_plaetze_bezahlt':
			case 'r2_plaetze_bezahlt':				
			case 'r3_plaetze_bezahlt':				
			case 'r2_foebisaktive':
			case 'r3_foebisaktive':
			case 'r2r1_foebisaktive_korr':
			case 'r3r2_foebisaktive_korr':
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
	 * Laedt einzelnen Studienplatz der ID $foebis_stg_id	
	 * @param integer $foebis_stg_id ID des zu ladenden Datensatzes
	 * @return boolean Description true wenn ok, false im Fehlerfall
	 */
	public function load_foebis($foebis_stg_id	)
	{
		//Pruefen ob adress_id eine gueltige Zahl ist
		if(!is_numeric($foebis_stg_id	) || $foebis_stg_id	 == '')
		{
			$this->errormsg = 'foebis_stg_id muss eine Zahl sein';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM addon.tbl_aqa_foebis_stg WHERE foebis_stg_id=".$this->db_add_param($foebis_stg_id, FHC_INTEGER, false);

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
		$target->foebis_stg_id	= $row->foebis_stg_id;
		$target->studiengang_kz 	= $row->studiengang_kz;
		$target->orgform_kurzbz	= $row->orgformbez;
		$target->studiensemester_kurzbz	= $row->studiensemester;		
		$target->stgartbez	= $row->stgartbez;
		$target->regelstudiendauer	= $row->regelstudiendauer;
		$target->jahr			= $row->jahr;
		$target->monat			= $row->monat;			
		$target->runde			= $row->runde;
		$target->foerdergruppe  = $row->foerdergruppe;
		$target->aq				= $row->aq;		
		$target->npz			= $row->npz;		
		$target->r1_plaetze_bezahlt		= $row->r1_plaetze_bezahlt;		
		$target->r2_plaetze_bezahlt		= $row->r2_plaetze_bezahlt;		
		$target->r3_plaetze_bezahlt		= $row->r3_plaetze_bezahlt;		
		$target->r2_foebisaktive		= $row->r2_foebisaktive;		
		$target->r3_foebisaktive		= $row->r3_foebisaktive;
		$target->r2r1_foebisaktive_korr	= $row->r2r1_foebisaktive_korr;		
		$target->r3r2_foebisaktive_korr	= $row->r3r2_foebisaktive_korr;	
		$target->updateamum		= $row->updateamum;
		$target->updatevon		= $row->updatevon;
		$target->insertamum		= $row->insertamum;
		$target->insertvon		= $row->insertvon;
        $target->new            = false;
	}
	
	/**
	 * Laedt alle Datensätze zu einem Semester. Ergebnis
	 * steht in result.
	 * @param string studiensemester_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load_foebis_studiensemester($studiensemester_kurzbz)
	{
		//Pruefen ob $studiengang_kz eine gueltige Zahl ist
		if(!is_numeric($foebis_stg_id) || $foebis_stg_id == '')
		{
			$this->errormsg = "foebis_stg_id muss eine gültige Zahl sein";
			return false;
		}

		//Lesen der Daten aus der Datenbank
		$qry = "SELECT * FROM addon.tbl_foebis_stg WHERE studiengang_kz=".
				$this->db_add_param($studiengang_kz, FHC_INTEGER, false);
        $this->result = array();
        if(!$this->db_query($qry))
		{			
			return false;
		}

		while($row = $this->db_fetch_object())
		{
            $rec = new aqa_foebis_stg();
            $this->mapRow($rec, $row);
			$this->result[] = $rec;		
        }
		return true;
	}

	public function load_foebis_semesterdaten($studienjahr, $zeitraum)
	{
		// Semesterliste für where klausel erzeugen:
		$startSemester= 'WS'.substr($studienjahr,0,4);		
		$semesterList = generateSemesterList($startSemester,($zeitraum*2)-1);
		$semesterList_comma_separated = "'".join("','",$semesterList)."'";

		$sql = "select distinct on (studiengang_kz,regelstudiendauer,orgformbez,studiensemester) ".
			   "studiengang_kz,regelstudiendauer,orgformbez,studiensemester,r1_plaetze_bezahlt,r2_foebisaktive,r3_foebisaktive,jahr,monat,runde ".
			   "from addon.tbl_aqa_foebis_stg ".
			   "where addon.tbl_aqa_foebis_stg.studiensemester IN ($semesterList_comma_separated) ".
			   "order by studiengang_kz,regelstudiendauer,orgformbez,studiensemester,runde desc,jahr,monat";

		if(!$this->db_query($sql))
		{		
			return false;
		}
		
		$this->result = array();
		while($row = $this->db_fetch_object())
		{
			$this->result[$row->studiensemester][$row->studiengang_kz][$row->orgformbez]['r1_plaetze_bezahlt'] = $row->r1_plaetze_bezahlt;
			$this->result[$row->studiensemester][$row->studiengang_kz][$row->orgformbez]['r2_foebisaktive'] = $row->r2_foebisaktive;
			$this->result[$row->studiensemester][$row->studiengang_kz][$row->orgformbez]['r3_foebisaktive'] = $row->r3_foebisaktive;
        }
		return true;

	}
	
	

	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		if(!is_numeric($this->foebis_stg_id) && $this->foebis_stg_id!='')
		{
			$this->errormsg='foebis_stg_id enthaelt ungueltige Zeichen';
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
		if(mb_strlen($this->stgartbez)>2)
		{
			$this->errormsg = 'stgartbez darf nicht länger als 2 Zeichen sein';
			return false;
		}
		if(!is_numeric($this->regelstudiendauer) && $this->regelstudiendauer!='')
		{
			$this->errormsg='regelstudiendauer enthaelt ungueltige Zeichen';
			return false;
		}
		if(!is_numeric($this->jahr) && $this->jahr!='')
		{
			$this->errormsg='jahr enthaelt ungueltige Zeichen';
			return false;
		}
		if(!is_numeric($this->monat) && $this->monat!='')
		{
			$this->errormsg='monat enthaelt ungueltige Zeichen';
			return false;
		}
		
		$this->errormsg = '';
		return true;
	}
	
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $foebis_stg_id aktualisiert
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
			$qry='BEGIN;INSERT INTO addon.tbl_aqa_foebis_stg ('.
				 'studiengang_kz, stgartbez, orgformbez, studiensemester, '.
				 'regelstudiendauer, jahr, monat, runde, foerdergruppe,aq, npz, '.
			     'r1_plaetze_bezahlt,r2_plaetze_bezahlt,r3_plaetze_bezahlt, '.
			     'r2_foebisaktive,r3_foebisaktive,r2r1_foebisaktive_korr, '.
				 'r3r2_foebisaktive_korr,insertamum, insertvon, '.
			     'updateamum, updatevon) VALUES('.			      
			      $this->db_add_param($this->studiengang_kz, FHC_INTEGER).', '.
			      $this->db_add_param($this->stgartbez).', '.
			      $this->db_add_param($this->orgform_kurzbz).', '.
			      $this->db_add_param($this->studiensemester_kurzbz).', '.			     
			      $this->db_add_param($this->regelstudiendauer, FHC_INTEGER).', '.				 
			      $this->db_add_param($this->jahr, FHC_INTEGER).', '.
				  $this->db_add_param($this->monat, FHC_INTEGER).', '.
				  $this->db_add_param($this->runde, FHC_INTEGER).', '.
				  $this->db_add_param($this->foerdergruppe).', '.
				  $this->db_add_param($this->aq, FHC_INTEGER).', '.
				  $this->db_add_param($this->npz, FHC_INTEGER).', '.
			      $this->db_add_param($this->r1_plaetze_bezahlt, FHC_INTEGER).', '.
			      $this->db_add_param($this->r2_plaetze_bezahlt, FHC_INTEGER).', '.
			      $this->db_add_param($this->r3_plaetze_bezahlt, FHC_INTEGER).', '.
			      $this->db_add_param($this->r2_foebisaktive, FHC_INTEGER).', '.
			      $this->db_add_param($this->r3_foebisaktive, FHC_INTEGER).', '.					
				  $this->db_add_param($this->r2r1_foebisaktive_korr, FHC_INTEGER).', '.
				  $this->db_add_param($this->r3r2_foebisaktive_korr, FHC_INTEGER).', '.
				  'now(), '.
			      $this->db_add_param($this->insertvon).', '.
			      'now(), '.
			      $this->db_add_param($this->updatevon).');';
		}
		else
		{
			//Pruefen ob studienplatz_id eine gueltige Zahl ist
			if(!is_numeric($this->foebis_stg_id))
			{
				$this->errormsg = 'foebis_stg_id muss eine gueltige Zahl sein';
				return false;
			}
			$qry='UPDATE addon.tbl_aqa_foebis_stg SET'.				
				' studiengang_kz='.$this->db_add_param($this->studiengang_kz).', '.
				' stgartbez='.$this->db_add_param($this->stgartbez).', '.
				' orgformbez='.$this->db_add_param($this->orgform_kurzbz).', '.
				' studiensemester='.$this->db_add_param($this->studiensemester_kurzbz).', '.
		      	' regelstudiendauer='.$this->db_add_param($this->regelstudiendauer, FHC_INTEGER, false).', '.				
		      	' jahr='.$this->db_add_param($this->jahr, FHC_INTEGER, false).', '.
		      	' monat='.$this->db_add_param($this->monat, FHC_INTEGER, false).', '.		     
				' runde='.$this->db_add_param($this->runde, FHC_INTEGER, false).', '.		     
				' foerdergruppe='.$this->db_add_param($this->foerdergruppe).', '.
				' aq='.$this->db_add_param($this->aq, FHC_INTEGER, false).', '.
				' npz='.$this->db_add_param($this->npz, FHC_INTEGER, false).', '.
				' r1_plaetze_bezahlt='.$this->db_add_param($this->r1_plaetze_bezahlt, FHC_INTEGER, true).', '.
				' r2_plaetze_bezahlt='.$this->db_add_param($this->r2_plaetze_bezahlt, FHC_INTEGER, true).', '.
				' r3_plaetze_bezahlt='.$this->db_add_param($this->r3_plaetze_bezahlt, FHC_INTEGER, true).', '.
				' r2_foebisaktive='.$this->db_add_param($this->r2_foebisaktive, FHC_INTEGER, true).', '.
				' r3_foebisaktive='.$this->db_add_param($this->r3_foebisaktive, FHC_INTEGER, true).', '.
				' r2r1_foebisaktive_korr='.$this->db_add_param($this->r2r1_foebisaktive_korr, FHC_INTEGER, true).', '.
				' r3r2_foebisaktive_korr='.$this->db_add_param($this->r3r2_foebisaktive_korr, FHC_INTEGER, true).', '.
		      	' updateamum= now(), '.
		      	' updatevon='.$this->db_add_param($this->updatevon).		      	
		      	' WHERE foebis_stg_id='.$this->db_add_param($this->foebis_stg_id, FHC_INTEGER, false).';';
		}
        
		if($this->db_query($qry))
		{
			if($this->new)
			{
				//naechste ID aus der Sequence holen
				$qry="SELECT currval('addon.tbl_aqa_foebis_stg_foebis_stg_id_seq') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->foebis_stg_id = $row->id;
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
		return $this->foebis_stg_id;
	}

	
	public function deleteByStgKz($studiengang_kz, $jahr)
	{		
		//loeschen des Datensatzes
		$qry="DELETE FROM addon.tbl_aqa_foebis_stg "
				. "WHERE "
				. "((jahr=".$this->db_add_param($jahr, FHC_INTEGER, false)." AND "
				. "monat>=10) OR "
				. "(jahr=".$this->db_add_param($jahr + 1, FHC_INTEGER, false)." AND "
				. "monat<10)) AND "
				. "studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER, false)
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
	 * @param $foebis_stg_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($foebis_stg_id)
	{
		//Pruefen ob studienplatz_id eine gueltige Zahl ist
		if(!is_numeric($foebis_stg_id) || $foebis_stg_id == '')
		{
			$this->errormsg = 'foebis_stg_id muss eine gültige Zahl sein'."\n";
			return false;
		}

		//loeschen des Datensatzes
		$qry="DELETE FROM addon.tbl_aqa_foebis_stg WHERE foebis_stg_id=".$this->db_add_param($foebis_stg_id, FHC_INTEGER, false).";";

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
