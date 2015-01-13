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
require_once(__DIR__ .'/../../../include/prestudent.class.php');  // für bewerber und interessenten
require_once(__DIR__ .'/../../../include/statistik.class.php');   // für dropouts


/*
 select  distinct on (prestudent_id,matrikelnr,vorname,nachname,s.studiengang_kz) prestudent_id, matrikelnr,vorname,nachname,s.studiengang_kz,sg.bezeichnung,ss.studiensemester_kurzbz,ss.start ,ausbildungssemester
 from 
 public.tbl_student s
 join public.tbl_studiengang sg using(studiengang_kz)
 JOIN public.tbl_benutzer ON(student_uid=uid)
 JOIN public.tbl_person USING (person_id)
 JOIN public.tbl_prestudentstatus ps USING (prestudent_id)
 JOIN public.tbl_prestudent USING (prestudent_id)
 JOIN public.tbl_studiensemester ss using (studiensemester_kurzbz) 
 where status_kurzbz='Student' and (sg.typ='m' or sg.typ='b')
 order by prestudent_id, matrikelnr,vorname,nachname,s.studiengang_kz,ss.start asc;





<ListFoebisAbrechnungStudiengangResult xmlns="https://www.aq.ac.at/BISWS/FOEBIS/WebServices/Services/ErhalterService">
  <Report xmlns="FOEBISAbrechnungStudiengang" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" Name="FOEBISAbrechnungStudiengang" xsi:schemaLocation="FOEBISAbrechnungStudiengang https://www.aq.ac.at/ReportServer?%2FApplikationen%2FFOEBIS%2FWebservice%2FFOEBISAbrechnungStudiengang&amp;rs%3AFormat=XML&amp;rc%3ASchema=True">
    <Tablix1>
      <Group1_Collection>
        <Group1 StgKz2="0227 Biomedizinisches Ingenieurwesen/Biomedical Engineering" Textbox101="396999.99">
          <Details_Collection>
            <Details AQ="18" FoerderGruppeBez="technisch" Foerdersatz="7940.00" Jahr="2013" Monatlang1="10" NPZ="200" OrgFormBez="VZ" R1FoerderbetragGesamtTatsaechlich="132333.33" R1PlaetzeBezahlt="200" R2FoebisAktiveGesamt="230" R2FoerderbetragGesamt="132333.33" R2PlaetzeBezahltGesamt="200" R2R1FoebisAktiveKorrGesamt="0" R2R1FoerderbetragKorrGesamt="0.00" RStd="6" Runde="1" Semester="WS" StgArtBez="Ba" StgKz="0227"/>
            <Details AQ="18" FoerderGruppeBez="technisch" Foerdersatz="7940.00" Jahr="2013" Monatlang1="11" NPZ="200" OrgFormBez="VZ" R1FoerderbetragGesamtTatsaechlich="132333.33" R1PlaetzeBezahlt="200" R2FoebisAktiveGesamt="230" R2FoerderbetragGesamt="132333.33" R2PlaetzeBezahltGesamt="200" R2R1FoebisAktiveKorrGesamt="0" R2R1FoerderbetragKorrGesamt="0.00" RStd="6" Runde="1" Semester="WS" StgArtBez="Ba" StgKz="0227"/>
            <Details AQ="18" FoerderGruppeBez="technisch" Foerdersatz="7940.00" Jahr="2013" Monatlang1="12" NPZ="200" OrgFormBez="VZ" R1FoerderbetragGesamtTatsaechlich="132333.33" R1PlaetzeBezahlt="200" R2FoebisAktiveGesamt="230" R2FoerderbetragGesamt="132333.33" R2PlaetzeBezahltGesamt="200" R2R1FoebisAktiveKorrGesamt="0" R2R1FoerderbetragKorrGesamt="0.00" RStd="6" Runde="1" Semester="WS" StgArtBez="Ba" StgKz="0227"/>
          </Details_Collection>
        </Group1>
      </Group1_Collection>
    </Tablix1>
  </Report>
</ListFoebisAbrechnungStudiengangResult>

 
*/
/**
 * Offene Fragen: 
 * - Sind Incoming aktive Studenten und werden gefördert?
 * - Was ist mit Outgoing
 * - Was passiert wenn ein Student 2 oder mehr Studiengänge gleichzeitig besucht:
 *   => es wird nur 1 Studium gefördert
 *
 *
 *
 *
 * 
 */
class FoerderungBerechnung 
{
	public $errormsg;			
	const STG_ART_BACHELOR = 'B';
	const STG_ART_MASTER = 'M';
	
	
	public function berechneFoerderguthaben($stgKz,$stgArt,$stgOrgForm,$student,$studiensemester) 
	{
		
		$result = $reader->parseBasisdatenUV($file);
		if ($result === false) 
		{
			$this->errormsg = $reader->errormsg;
			return false;
		}
		return $result;
	}

	/**
	 * Maximales Foerderguthaben für eine Person berechnen
	 * @param  int $rstd Regelstudiendauer in Monaten
	 * @param  int $ausbildungssemesterEinstieg Ausbildungssemester bei erstmaliger BIS-Meldung
	 * @param  string $stgArt FoerderungBerechnung::STG_ART_BACHELOR oder FoerderungBerechnung::STG_ART_MASTER
	 * @return int maximales Förderguthaben in Monaten
	 */
	public function maxFoerderguthaben($rstd, $ausbildungssemesterEinstieg, $stgArt)
	{
		$K = 10;
		if ($stgArt === self::STG_ART_MASTER)
		{
			$stgArt = 6;
		} 
		else if ($stgArt != self::STG_ART_BACHELOR)
		{
			throw new Exception('$stgArt muss B (=Bachelor) oder M (=Master) sein');
		}
		$max = ($rstd -($ausbildungssemesterEinstieg - 1)) * 6 + $K;
		return $max;
	}

	public function getAusbildungssemesterEinstieg($student)
	{

	}

	

}
