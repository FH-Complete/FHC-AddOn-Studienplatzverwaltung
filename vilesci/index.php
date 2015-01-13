<?php
/* Copyright (C) 2013 FH Technikum-Wien
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
 */
require_once('../../../config/vilesci.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);


echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">
	<title>Template</title>
</head>
<body>
<h1>Studienplatzverwaltung</h1>';


if(!$rechte->isBerechtigt('addon/studienplatzverwaltung'))
{
	echo '<h2>Sie haben keine Berechtigung für diese Seite. Benötigte Berechtigung: addon/studienplatzverwaltung [uid='.$uid.']</h2>';
} else {
    echo 'Addon Studienplatzverwaltung <a href="app/index.html" target="_blank"> starten </a>';
}
?>
</body>
</html>