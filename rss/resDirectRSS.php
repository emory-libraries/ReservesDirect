<?php
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
