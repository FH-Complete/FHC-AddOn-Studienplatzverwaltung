<?php
/* Copyright (C) 2013 Technikum-Wien
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
 * Authors: Werner Masik <werner.masik@gefi.at>
 */
/*
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
*/
error_reporting(E_ERROR);

require_once('../../../config/vilesci.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('StudienplatzverwaltungAPI.class.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('addon/studienplatzverwaltung'))
{	
	echo json_encode(array('error' => 'Sie haben keine Berechtigung für diese Seite. Benötigte Berechtigung: addon/studienplatzverwaltung'));
} else {
	dispatch();	
}


/**
 * Helper um Appdaten für die Konvertierung in JSON Response vorzubereiten.
 * Das ist notwendig, weil die Appdaten bereits JSON enthalten und das sonst
 * doppelt konvertiert werden würde. 
 * @param type $appdaten
 * @return type
 */
function prepareAppDaten($appdaten)
{
   return array(
			   'uid' => $appdaten->uid,
			   'appversion' => $appdaten->appversion,
			   'version' => $appdaten->version,
			   'bezeichnung' => $appdaten->bezeichnung,					
			   'freigabe' => $appdaten->freigabe,
			   'updateamum' => $appdaten->updateamum,
			   'updatevon' => $appdaten->updatevon,
			   'daten' => json_decode($appdaten->daten)

   );
}


function dispatch() 
{
	global $uid;
	$aktion = $_GET['endpoint'];
	$api = StudienplatzverwaltungAPI::init();
	switch ($aktion)
	{
		
		case 'getMetadata':
			// Liste der Studienjahre und der darin vorhandenen UV-Daten.
			// zum Anzeigen der Auswahl benötigt
			
			echo $api->getMetadata();
			break;
	 	
		case 'newUV':
			$studienjahr = $_GET['studienjahr'];
			$zeitraum = $_GET['zeitraum'];
			// neue Iteration mit appdaten anlegen (result == appdaten)
			$result = $api->newUV($studienjahr, $zeitraum, $uid);
			
			if ($result === false)
			{
				$response = json_encode(array('result' => 0, 'errormsg' => $api->errormsg));
			}
			else
			{
				$response = json_encode(array('result' => 1, 'appdaten' => prepareAppDaten($result)));
			}
			echo $response;
			
			break;

		case 'loadUV':
			$studienjahr = $_GET['studienjahr'];
			$version = $_GET['version'];
			$zeitraum = (isset($_GET['zeitraum']) ? $_GET['zeitraum'] : null);
			$result = $api->getUV($studienjahr, $version, $zeitraum, $uid);
			
			if ($result === false)
			{
				$response = json_encode(array('result' => 0, 'errormsg' => $api->errormsg));
			}
			else
			{
				$response = json_encode(array('result' => 1, 'appdaten' => prepareAppDaten($result)));
			}
			echo $response;
			break;
			
		case 'refreshUV':
			$studienjahr = $_GET['studienjahr'];
			$version = $_GET['version'];
			$zeitraum = (isset($_GET['zeitraum']) ? $_GET['zeitraum'] : null);
			$result = $api->refreshUV($studienjahr, $version, $zeitraum, $uid);
			
			if ($result === false)
			{
				$response = json_encode(array('result' => 0, 'errormsg' => $api->errormsg));
			}
			else
			{
				$response = json_encode(array('result' => 1, 'appdaten' => prepareAppDaten($result)));
			}
			echo $response;
			break;
			
		case 'saveUV':
            $json = json_decode(file_get_contents("php://input"), false); 
            $result = $api->saveUV($json, $uid);
	
			if ($result === false)
			{
				$response = json_encode(array('result' => 0, 'errormsg' => $api->errormsg));
			}
			else
			{
				$response = json_encode(array('result' => 1, 'appdaten' => prepareAppDaten($result)));
			}
			echo $response;

			break;
		
		case 'deleteUV':
			$studienjahr = $_GET['studienjahr'];
			$version = $_GET['version'];
			$result = $api->deleteUV($studienjahr, $version, $uid);
			if ($result === false)
			{
				$response = json_encode(array('result' => 0, 'errormsg' => $api->errormsg));
			}
			else
			{
				$response = json_encode(array('result' => 1));
			}
			echo $response;
			break;

		case 'getInfoDaten':
			$studienjahr = $_GET['studienjahr'];
			$zeitraum = (isset($_GET['zeitraum']) ? $_GET['zeitraum'] : null);
			$result = $api->getInfoDaten($studienjahr, $zeitraum);
			
			if ($result === false)
			{
				$response = json_encode(array('result' => 0, 'errormsg' => $api->errormsg));
			}
			else
			{
				$response = json_encode(array('result' => 1, 'infoDaten' => $result));
			}
			echo $response;
			break;
			
		case 'saveSetup':
            $json = json_decode(file_get_contents("php://input"), true); 
            $result = $api->saveSetup($json, $uid);
	
			if ($result === false)
			{
				$response = json_encode(array('result' => 0, 'errormsg' => $api->errormsg));
			}
			else
			{
				$response = json_encode(array('result' => 1));
			}
			echo $response;

			break;
			
		case 'deleteSetup':
            $json = json_decode(file_get_contents("php://input"), true); 
            $result = $api->deleteSetup($json, $uid);
	
			if ($result === false)
			{
				$response = json_encode(array('result' => 0, 'errormsg' => $api->errormsg));
			}
			else
			{
				$response = json_encode(array('result' => 1));
			}
			echo $response;

			break;
			
		case 'getSetupList':
			$result = $api->getSetupList($uid);
	
			if ($result === false)
			{
				$response = json_encode(array('result' => 0, 'errormsg' => $api->errormsg));
			}
			else
			{
				$response = json_encode(array('result' => 1, 'setupList' => $result));
			}
			echo $response;

			break;
		case 'getSetup':
			$bezeichnung = $_GET['bezeichnung'];
			$result = $api->getSetup($bezeichnung,$uid);
	
			if ($result === false)
			{
				$response = json_encode(array('result' => 0, 'errormsg' => $api->errormsg));
			}
			else
			{
				$response = json_encode(array('result' => 1, 'setupList' => $result));
			}
			echo $response;

			break;
			
		case 'importBD':
			$fileName = $_FILES['file']['name'];
			$fileType = $_FILES['file']['type'];
			$fileContent = file_get_contents($_FILES['file']['tmp_name']);
			$dataUrl = 'data:' . $fileType . ';base64,' . base64_encode($fileContent);

			$fileData = array(
			  'name' => $fileName,
			  'type' => $fileType,
			  'dataUrl' => $dataUrl
			);
			$result = $api->parseBasisdatenUV($_FILES['file']['tmp_name']);
			if ($result === false)
			{
				echo json_encode(array('result' => 0, 'info' => $fileData, 'errormsg' => $api->errormsg));
			}
			else
			{
				echo json_encode(array('result' => 1, 'info' => $fileData));
			}
		break;
		case 'importFOEBis':
			
			$result = $api->importFOEBis();
			
			if ($result === false)
			{
				$response = json_encode(array('result' => 0, 'errormsg' => $api->errormsg));
			}
			else
			{
				$response = json_encode(array('result' => 1));
			}
			echo $response;
			break;

		case 'exportXML':
			$studienjahr = $_GET['studienjahr'];
			$version = $_GET['version'];
			header("Content-Disposition: attachment; filename=\"UV_".substr($studienjahr,0,4)."_".substr($studienjahr,5).".xml\"");
			header("Pragma: public");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			print $api->exportXML($studienjahr,$version,$uid);
		break;

		case 'exportCSV':
			$studienjahr = $_GET['studienjahr'];
			$version = $_GET['version'];
			header("Content-Disposition: attachment; filename=\"UV_".substr($studienjahr,0,4)."_".substr($studienjahr,5).".csv\"");
			header("Pragma: public");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			print $api->exportCSV($studienjahr,$version,$uid);
			break;
		default:
			echo json_encode(array('result' => 0, 'errormsg' => 'Kein Endpoint '.$aktion));
			break;
	}
	
	
	
}



?>

