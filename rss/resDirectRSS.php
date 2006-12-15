<?php
/*******************************************************************************
resDirectRSS.php

Created by Jason White (jbwhite@emory.edu)

This file is part of ReservesDirect

Copyright (c) 2004-2006 Emory University, Atlanta, Georgia.

Licensed under the ReservesDirect License, Version 1.0 (the "License");      
you may not use this file except in compliance with the License.     
You may obtain a copy of the full License at                              
http://www.reservesdirect.org/licenses/LICENSE-1.0

ReservesDirect is distributed in the hope that it will be useful,
but is distributed "AS IS" and WITHOUT ANY WARRANTY, without even the
implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
PURPOSE, and without any warranty as to non-infringement of any third
party's rights.  See the License for the specific language governing         
permissions and limitations under the License.

ReservesDirect is located at:
http://www.reservesdirect.org/

*******************************************************************************/
error_reporting(0);
require_once("../secure/config.inc.php");

// v. 0.1

//convert old format to new and query db for 
list($course, $prof, $sem, $year) = split(":", $_REQUEST['vals']);

	switch ($g_dbConn->phptype)
	{
		default: //'mysql'
			$sql = 	"SELECT  ca.course_instance_id " .
					"FROM courses AS c " .
					"JOIN course_aliases AS ca ON ca.course_id = c.course_id AND c.old_id = ! " .
					"JOIN course_instances AS ci ON ca.course_instance_id = ci.course_instance_id " .
					"WHERE ci.term = ? AND ci.year = ?"
				;
	}
	
	$rs = $g_dbConn->query($sql, array($course, $sem, $year));
	//print_r($rs);
	if (DB::isError($rs))
	{
		flush;
		echo "<?xml version=\"1.0\"?>\n";
		echo "<!DOCTYPE rss ["; include('rss/ansel_unicode.ent');  echo "]>\n";
    	echo "<rss version=\"2.0\">\n";
    	echo "	<channel>\n";
    	echo "		<error>Data could not be retrieved from resDirectRSS.php for " . $_REQUEST['vals'] . " Please contact the systems administrator.</error>";
    	echo "	</channel>\n";
    	echo "</rss>\n";
    	exit;
	}
	
	$row = $rs->fetchRow();
	
	$xmlSrc = "http://".$_SERVER['SERVER_NAME'] . ereg_replace('rss/resDirectRSS.php', "rss.php?ci=", $_SERVER['PHP_SELF']) . $row[0];
	flush();
	readfile($xmlSrc);
?>
