<?php

/* Copyright (C) 2014 fhcomplete.org
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

// SOAP-Schnittstelle der AQ Austria für FÖBIS-Abrechnung
// Der Benutzer muss in der Applikation der AQA die Berechtigung für die Applikation
// 'Erhalterportal' haben

// Achtung! Das ist nur der aktuelle Testlink.
define("AQA_ERHALTERSERVICE", "https://www.aq.ac.at/BISWS_TEST/FOEBIS/ErhalterService.asmx?WSDL");
define("AQA_USERNAME", "");
define("AQA_PASSWORD", "");
define("UV_ERHALTER", "005");
define("UV_STANDORT", "Wien");
define("UV_FOERDERSATZ_TECHNISCH", "7940,00");