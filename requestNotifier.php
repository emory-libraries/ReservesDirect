#!/usr/local/bin/php -q

<?
/*******************************************************************************
requestNotifier.php
send email when new requests are generated

Reserves Direct 2.0

Copyright (c) 2004 Emory University General Libraries

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be included
in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

Created by Jason White (jbwhite@emory.edu)

Reserves Direct 2.0 is located at:
http://coursecontrol.sourceforge.net/

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
			.  "WHERE r.date_requested >= ? AND r.date_processed is null "
			.  	"CASE "
			.  		"WHEN i.item_group = 'MONOGRAPH'  THEN l.monograph_library_id  = ! "
			.  		"WHEN i.item_group = 'MULTIMEDIA' THEN l.multimedia_library_id = ! "
			.	"END"
			;
}

foreach ($libraries as $library)
{
	$rs = $g_dbConn->query($sql, array($g_request_notifier_lastrun, $library->getLibraryID(), $library->getLibraryID()));		

	if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

	$tmpArray = array();
	while ($row = $rs->fetchRow())
	{
		if ($row[0] > 0)
		{
			$msg = "There are " . $row[0] . " new request(s) generated for " .$library->getLibrary(). " since $g_request_notifier_lastrun".
			mail($library->getContactEmail(), 'Reserves Direct Requests Notification', $msg);
		}
	}
	
	//update last run date
	$configure->request_notifier->last_run = $d;
	
	//write out new xml file
	$xmlDOM = new DomDocument();
	$xmlDOM->loadXML($configure->asXML());	 
	$xmlDOM->save($xmlConfig);
}	
?>