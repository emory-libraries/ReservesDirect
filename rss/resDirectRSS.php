<?php
require_once("config.inc.php");

// v. 0.1


//convert old format to new and query db for 
list($course, $prof, $sem, $year) = split(":", $_REQUEST['vals']);

	switch ($g_dbConn->phptype)
	{
		default: //'mysql'
			$sql = 	"SELECT  ca.course_instance_id " .
					"FROM courses AS c " .
					"JOIN course_aliases AS ca ON ca.course_id = c.course_id AND c.old_id=! " .
					"JOIN course_instances AS ci ON ca.course_instance_id = ci.course_instance_id " .
					"WHERE ci.term = ? AND ci.year=?"
				;
	}
	
	$rs = $g_dbConn->query($sql, array($course, $sem, $year));
	$row = $rs->fetchRow();

include("rss.php?ci=" . $row[0]);

?>
