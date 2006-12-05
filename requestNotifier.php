#!/usr/local/bin/php -q

<?
/*******************************************************************************
requestNotifier.php
send email when new requests are generated

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
require_once("secure/config.inc.php");
require_once("secure/common.inc.php");
require_once("secure/classes/user.class.php");
require_once("secure/classes/library.class.php");


global $g_dbConn, $g_request_notifier_lastrun, $configure, $xmlConfig;

$libraries = user::getLibraries();

switch ($g_dbConn->phptype)
{
	default: //'mysql'
		$d   = date('Y-m-d');
		$sql = "SELECT count(r.request_id) "
			.  "FROM requests AS r "
			.  	"JOIN items AS i ON r.item_id = i.item_id AND r.date_processed IS NULL "
			.  	"JOIN course_instances AS ci ON r.course_instance_id = ci.course_instance_id "
			.  	"JOIN course_aliases AS ca ON ci.primary_course_alias_id = ca.course_alias_id "
			.  	"JOIN courses AS c ON ca.course_id = c.course_id "
			.  	"JOIN departments AS d ON c.department_id = d.department_id AND d.status IS NULL "
			.  	"JOIN libraries AS l ON d.library_id = l.library_id "
			.  "WHERE r.date_requested >= '".$g_request_notifier_lastrun."' AND r.date_processed is null AND "
			.  	"CASE "
			.  		"WHEN i.item_group = 'MONOGRAPH'  THEN l.monograph_library_id  = ! "
			.  		"WHEN i.item_group = 'MULTIMEDIA' THEN l.multimedia_library_id = ! "
			.	"END"
			;
}

foreach ($libraries as $library)
{
	echo "process ".$library->getLibraryID()." .... ";
	$rs = $g_dbConn->query($sql, array($library->getLibraryID(), $library->getLibraryID()));

	if (DB::isError($rs)) {
		report_error($sql . " arg[" . implode("] arg[", array($library->getLibraryID(), $library->getLibraryID()))."]");
		exit;
	}

	$tmpArray = array();
	while ($row = $rs->fetchRow())
	{
		if ($row[0] > 0) //if count of requests is greater than 0
		{
			$msg = "There are " . $row[0] . " new request(s) generated for " .$library->getLibrary(). " since $g_request_notifier_lastrun\n";
			$msg .= "Please login to ReservesDirect and check your requests queue <a href=\"$g_siteURL/index.php\">$g_siteURL/index.php</a>";

			if (!mail($library->getContactEmail(), 'ReservesDirect Requests Notification', $msg))
			{
				$err = "Notification Email not sent for " . $library->getContactEmail() . "\n";
				report_error($err);
			}
		}
	}
	echo " done\n\n";
}

//update last run date
$configure->request_notifier->last_run = $d;

//write out new xml file
$xmlDOM = new DomDocument();
$xmlDOM->loadXML($configure->asXML());
$xmlDOM->save($xmlConfig);

function report_error($err)
{
	global $g_error_log, $g_errorEmail;
	error_log($err, 3, $g_error_log);
	mail($g_errorEmail, "ReservesDirect Notifications Error", $err);
}

?>
