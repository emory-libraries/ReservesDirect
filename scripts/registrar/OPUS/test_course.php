#!/usr/local/bin/php -q

<?
/*******************************************************************************
parse_course_feed.php
This script recreates the data file to test for proper parsing

Created by Jason White (jbwhite@emory.edu)

This file is part of ReservesDirect

Copyright (c) 2004-2005 Emory University, Atlanta, Georgia.

ReservesDirect is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

ReservesDirect is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with ReservesDirect; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

ReservesDirect is located at:
http://www.reservesdirect.org/

*******************************************************************************/
//delimiter
$feedDelimiter = ",";
$feedLineLength = 1000;

//file header order
$feed_filename 	= 0;
$feed_date 		= 1;
$feed_time		= 2;
$feed_CYYM		= 3;

//field order
$LMS_A 			= 0;
$LMS_PRIMARY 	= 1;
$DeptAbbr		= 2;
$CourseNumber 	= 3;
$CourseSection	= 4;
$CourseTitle	= 5;
$aDate			= 6;
$eDate			= 7;
$DeptTitle		= 8;

$course_status     = 'AUTOFEED';
$course_enrollment = 'OPEN';


//set working directory
if ($argv[2] != "")
	chdir($argv[2]);
else	
	chdir("../../../");

require_once("config_loc.inc.php");
require_once("secure/config.inc.php");
	
//open standard out
$stdout = fopen('php://stdout', 'w');

//open file
$filename = $argv[1];
$fp = fopen($filename, 'r');
if ($fp == false)
	fwrite($stderr, "Could not open file for read: $filename\n");
	
	
//read file header
$header = fgets($fp,$feedLineLength);

$sql = "
	SELECT ca.registrar_key, cap.registrar_key, d.abbreviation, c.course_number, ca.section, c.uniform_title, ci.activation_date, ci.expiration_date
	FROM
		(SELECT course_id, course_instance_id, section, registrar_key FROM course_aliases WHERE registrar_key = ?) as ca
		JOIN course_instances as ci ON ca.course_instance_id = ci.course_instance_id
		JOIN course_aliases as cap ON cap.course_alias_id = ci.primary_course_alias_id
		JOIN courses as c ON c.course_id = ca.course_id
		JOIN (SELECT department_id, abbreviation FROM departments WHERE abbreviation = ?) as d ON c.department_id = d.department_id
";

while(($line = fgetcsv($fp, $feedLineLength, $feedDelimiter)) != FALSE)
{
	unset($Course);
	
	$row = $g_dbConn->getAll($sql, array($line[$LMS_A], $line[$DeptAbbr]));

	$l_test = "";	
	$l = '';
	
	foreach($row[0] as $field)
	{
		$l .= trim($field) . ",";
	} 
	$l = rtrim($l, ',');
	
	$l_test = join($line, ',');
	
	if (strcasecmp($l, $l_test) != 0)
	{
		fwrite($stdout, "$l\n$l_test\n");
	}
		
} //end while

unset($d);
$g_dbConn->disconnect();
fclose($fp);

exit;
?>