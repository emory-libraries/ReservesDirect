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
$registrar_key	= 0;
$net_id	 		= 1;
$perm_indicator	= 2;


//set working directory
if ($argv[2] != "")
	chdir($argv[2]);
else	
	chdir("../../../");

require_once("config_loc.inc.php");
require_once("secure/config.inc.php");
require_once("secure/common.inc.php");
	
//open standard out
$stdout = fopen('php://stdout', 'w');

//open file
$filename = $argv[1];
$fp = fopen($filename, 'r');
if ($fp == false)
	fwrite($stderr, "Could not open file for read: $filename\n");
	
	
$perm = array (
	's' => $g_permission['student'], 
	't' => $g_permission['instructor'],
	'p' => $g_permission['instructor']
);
//in RD teaching assistants who are instructing a class are considered instructors

//read header
$header = fgetcsv($fp, $feedLineLength, $feedDelimiter);

$sql = "
	SELECT ca.registrar_key, u.username, a.permission_level
	FROM
		(SELECT user_id, alias_id, permission_level FROM access WHERE autofeed_run_indicator LIKE '!%') as a
		JOIN users as u ON a.user_id = u.user_id
		JOIN course_aliases as ca ON a.alias_id = ca.course_alias_id
		LEFT JOIN not_trained as nt ON nt.user_id = a.user_id
";

$rs =& $g_dbConn->query($sql, $header[$feed_CYYM]);

if (DB::isError($rs)) 
{
	fwrite($stdout, "could not read data from db " . $rs->getMessage() . "\n"); 
	exit;
}

$stored_data = null;
while($row = $rs->fetchRow(DB_FETCHMODE_ASSOC))
{
	$stored_data[$row['registrar_key']][strtoupper($row['username'])]['permission'] = $row['permission_level'];
	$stored_data[$row['registrar_key']][strtoupper($row['username'])]['checked'] = FALSE;
}


$p = null;
$i = 0;

while(($line = fgetcsv($fp, $feedLineLength, $feedDelimiter)) != FALSE)
{
	if($perm[$line[$perm_indicator]] == $g_permission['instructor'] || substr($line[$registrar_key],0,4) == $header[$feed_CYYM])		
	{		
		//In RD teaching assistances designated as permission type 't' in the course feed are stored as instructors 
		//this makes a simple line by line comparision impossible and we will instead compare fields
		$p = $stored_data[$line[$registrar_key]][$line[$net_id]]['permission'];		
		
		if ($perm[$line[$perm_indicator]] != $p)
		{
			fwrite($stdout, join($feedDelimiter, $line) . "\n");				
		} else {
			$stored_data[$line[$registrar_key]][$line[$net_id]]['checked'] = TRUE;
		}
		
		$p = null;
		$i++;
	}
	
} //end while

foreach($stored_data as $key => $value)
{
	foreach($value as $k => $v)
	{
		if ($stored_data[$key][$k]['checked'] == FALSE)
		{
			echo "[".$stored_data[$key][$k]['checked']."] ";
			echo "$key,$k,".$stored_data[$key][$k]['permission']."\n";
		}
	}
}

$g_dbConn->disconnect();
fclose($fp);

exit;
?>