#!/usr/local/bin/php -q

<?
/*******************************************************************************
parse_course_feed.php
This script parses the members feed and inserts users into the access table

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
/*
Data Format is assumed to be:
1.  1 access record per line in CSV format

File contains:
LMS_GROUP_ID
net_id
permission_indicator (s = student, p = instructor)


Delete all users added in last run by 
Search for user by net_id 
if not found insert as student
compare permission_indicator to default role if needed make instructor 
and add to not trained
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

$enrollment_status = "AUTOFEED";

//set working directory
if ($argv[2] != "")
	chdir($argv[2]);
else	
	chdir("../../../");

require_once("config_loc.inc.php");
require_once("secure/config.inc.php");
require_once("secure/common.inc.php");

require_once("decode_semester.php");

$perm = array (
	's' => $g_permission['student'], 
	't' => $g_permission['instructor'],
	'p' => $g_permission['instructor']
);
//in RD teaching assistants who are instructing a class are considered instructors

//open standard out
$stdout = fopen('php://stdout', 'w');

//xml file location
$xmlfile = "scripts/registrar/OPUS/lastrun.xml";

//get last run info
$lastrunXML = simplexml_load_file($xmlfile);
$lastrun_tag = (string)$lastrunXML->term_tag;

//open file
$filename = $argv[1];
$fp = fopen($filename, 'r');
if ($fp == false)
	fwrite($stdout, "Could not open file for read: $filename\n");
	
	
//read file header
$header = fgetcsv($fp, $feedLineLength, $feedDelimiter);

//determine current run indicator
list($last_term, $last_i) = split("_", $lastrun_tag);
//echo "last_term=$last_term last_i=$last_i\n";

$current_tag = ($last_term == $header[$feed_CYYM]) ? $last_term . "_" . ((int)$last_i + 1) : $header[$feed_CYYM]."_1";

//echo "last_tag = $lastrun_tag current_tag=$current_tag\n";


//create needed sql statements
switch($g_dbConn->phptype) 
{
	default:	//mysql			
		$sql['find_old']	= "SELECT u.username, u.user_id, ca.registrar_key, a.access_id
							   FROM (SELECT access_id, alias_id, user_id FROM access WHERE autofeed_run_indicator = ?) as a
							   JOIN users as u ON u.user_id = a.user_id
							   JOIN course_aliases as ca ON ca.course_alias_id = a.alias_id";
	
		$sql['delete_old'] 	= "DELETE FROM access WHERE autofeed_run_indicator = ?";
		$sql['find_user']  	= "SELECT user_id, dflt_permission_level FROM users WHERE username=?";
		$sql['add_access']	= "INSERT INTO access (user_id, alias_id, permission_level, enrollment_status, autofeed_run_indicator) VALUES (!, !, !, ?, ?)";
		$sql['add_user']   	= "INSERT INTO users (username) VALUES (?)";
		$sql['update_user'] = "UPDATE users SET dflt_permission_level = " . $g_permission['instructor'] . " WHERE user_id = !";
		$sql['not_trained']	= "INSERT INTO not_trained (user_id) VALUES (!)";
		$sql['get_alias']	= "SELECT course_alias_id FROM course_aliases WHERE registrar_key = ?";
		$sql['update_term'] = "UPDATE access SET autofeed_run_indicator = ? WHERE access_id in (!)";
		$sql['find_access']	= "SELECT access_id FROM access WHERE user_id = ! AND alias_id =!";
}

$rs =& $g_dbConn->query($sql['find_old'], $lastrun_tag);
if (DB::isError($rs)) { 
	fwrite($stdout, "could not get old run data for run $lastrun_tag " . $rs->getMessage() . "\n");
	exit;
}

//store last run data to look up table
while($row = $rs->fetchRow(DB_FETCHMODE_ASSOC))
{
	$lastrun_data[strtoupper($row['username'])][$row['registrar_key']] = $row['access_id'];	
}
$rs->free();

//print_r($lastrun_data); //echo "\n";
//echo count($lastrun_data) . " users found from last run\n";

$g_dbConn->autoCommit(false);

$update_term_ids = "";
while(($line = fgetcsv($fp, $feedLineLength, $feedDelimiter)) != FALSE)
{	
	//skip unless current term or is an instuctor record
	if($perm[$line[$perm_indicator]] == $g_permission['instructor'] || substr($line[$registrar_key],0,4) == $header[$feed_CYYM])	
	{

		if(isset($lastrun_data[$line[$net_id]][$line[$registrar_key]]))
		{
			//record already exists update autofeed_run_indicator
			$update_term_ids .= $lastrun_data[$line[$net_id]][$line[$registrar_key]] . ",";
		} else {
			$rs =& $g_dbConn->query($sql['find_user'], $line[$net_id]);
			if (DB::isError($rs)) 
			{ 
				$g_dbConn->rollback();
				fwrite($stdout, $rs->getMessage() . "could not get user net_id=" . $line[$net_id]); 
				exit;
			}
			
			if($rs->numRows() == 0) 
			{
				//user not found add to db
				$rs =& $g_dbConn->query($sql['add_user'], $line[$net_id]);
				if (DB::isError($rs)) 
				{	
					$g_dbConn->rollback();			
					fwrite($stdout, "could not add user to db " . $line[$net_id] . "\n"); 
					exit;
				}
				
				$rs =& $g_dbConn->query($sql['find_user'], $line[$net_id]);
				if (DB::isError($rs)) 
				{
					$g_dbConn->rollback();
					fwrite($stdout, $rs->getMessage() . " net_id=" . $line[$net_id]) . "\n"; 
					exit;
				}	
				
			}
		
			//get course data
			$course_alias = $g_dbConn->getOne($sql['get_alias'], $line[$registrar_key]);
			if (DB::isError($course_alias)) 
			{ 
				$g_dbConn->rollback();
				fwrite($stdout, $rs->getMessage() . "could not find course_alias" . $line[$registrar_key])  . "\n"; 
				exit;
			}	
			
			$user = $rs->fetchRow(DB_FETCHMODE_ASSOC);
			if (DB::isError($rs)) 
			{ 
				$g_dbConn->rollback();
				fwrite($stdout, $rs->getMessage() . "could not get user net_id=" . $line[$net_id])  . "\n"; 
				exit;
			}	
			
			if ($user['dflt_permission_level'] < $perm[$line[$perm_indicator]])
			{
				//user has insufficent permissions make instructor and add to not trained
				$rs =& $g_dbConn->query($sql['update_user'], $user['user_id']);
				if (DB::isError($rs)) 
				{ 
					$g_dbConn->rollback();
					fwrite($stdout, "could not update user net_id=" . $line[$net_id]) . "\n"; 
					exit;
				}
				
				$rs =& $g_dbConn->query($sql['not_trained'], $user['user_id']);
				if (DB::isError($rs)) 
				{
					if ($rs->getCode() <> '-5') //ignore on duplicate entry errors
					{
						$g_dbConn->rollback();
						fwrite($stdout, "could not add user to not trained net_id=" . $line[$net_id]) . "\n"; 
						exit;
					}
				}
			} 
			
			//add to access table
//echo "adding " . $line[$net_id] . " " . $user['user_id']	. "\n";
			$rs = $g_dbConn->query($sql['add_access'], array($user['user_id'], $course_alias, $perm[$line[$perm_indicator]], $enrollment_status, $current_tag));	
			if (DB::isError($rs)) 
			{	
				if($rs->getCode() == '-5') //duplicate key				
				{
					$access_id =& $g_dbConn->getOne($sql['find_access'], array($user['user_id'], $course_alias));
					$update_term_ids .= "$access_id,";
				} else {
					$g_dbConn->rollback();
					fwrite($stdout, "could not add access net_id=" . $line[$net_id]) . " \n"; 
					exit;
				}
			}
		}
	}
}

//update collected access records
if ($update_term_ids != "")
{
	$rs =& $g_dbConn->query($sql['update_term'], array($current_tag, rtrim($update_term_ids, ",")));
	if (DB::isError($rs)) 
	{
		$g_dbConn->rollback();
		fwrite($stdout, "could not update existing records\n"); 
		exit;
	}
}
	
//delete old records that were not updated they have been removed from the feed
$rs =& $g_dbConn->query($sql['delete_old'], $lastrun_tag);	
if (DB::isError($rs)) 
{
	$g_dbConn->rollback();
	fwrite($stdout, "could not delete old records\n"); 
	exit;
}

$g_dbConn->commit();
$g_dbConn->disconnect();
fclose($fp);

//update last run date
$lastrunXML->term_tag = $current_tag;
$lastrunXML->run_date = date('Y-m-d H:i:s');
//write out new xml file
$xmlDOM = new DomDocument();
$xmlDOM->loadXML($lastrunXML->asXML());
$xmlDOM->save($xmlfile);

exit;

?>