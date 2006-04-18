#!/usr/local/bin/php -q

<?
/*******************************************************************************
parse_course_feed.php
This script parses the course feed and inserts courses into the proper tables

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
1.  1 course record per line in CSV format

File contains:
LMS Group ID (Term_EMORY_Subject_CatalogNumber_Section)
[Cross listed LMS Group ID (Term_EMORY_Subject_CatalogNumber_Section)]
Department Abbreviation (Subject)
Course Number  (CatalogNumber)
Section
Description
Course Begin Date
Course End Date
Department Title

Data can take the following formats

Data can take the following formats

LMS_A, LMS_A, Course_A, Description_A, Begin_Date_A, End_Date_A   -- Indicates a single non-crosslisted course
LMS_A, LMS_PRIMARY, Course_A, Description_A, Begin_Date_A, End_Date_A   -- Indicates Course A xlisted with B course B info will follow
LMS_PRIMARY, LMS_PRIMARY, Course_B, Description_B, Begin_Date_B, End_Date_B   -- Indicates Course B is a primary course

The script will follow this logic

If (LMS_A == LMS_PRIMARY) //Primary Course
    look for existing course using Subject (Department), Catalog Number and Name for Course_A
	if !found
		Create new course 
		
	Look for CA based on LMS_A
	if !found
		create CI/CA

ElseIf (LMS_A != LMS_PRIMARY)	
    look for existing course using Subject (Department), Catalog Number and Name for Course_A
    if !found
    	Create new course    	    	

    Create CI for Course_A with null primary_course_alias_id
    Create CA for Course_A
    
    look for CA based on LMS_PRIMARY  -- search for primary course
    if found 
    	update CA.ci_id from LMS_PRIMARY
    else
    	create new CA for LMS_PRIMARY	with NULL course_instance_id

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
	
require_once("secure/classes/course.class.php");
require_once("secure/classes/courseInstance.class.php");
require_once("decode_semester.php");

//open standard out
$stdout = fopen('php://stdout', 'w');

//open file
$filename = $argv[1];
$fp = fopen($filename, 'r');
if ($fp == false)
	fwrite($stdout, "Could not open file for read: $filename\n");

//echo "opening $filename\n";	
	
	
//read file header
$header = fgetcsv($fp, $feedLineLength, $feedDelimiter);

//init department object for later lookups
$d = new department();

$g_dbConn->autoCommit(false);

while(($line = fgetcsv($fp, $feedLineLength, $feedDelimiter)) != FALSE)
{
	
$line[$DeptTitle] = "NEW DEPARTMENT";	
unset($Course);
//echo "\n\n\n\n\n***********************************************************************************\n";print_r($line);//echo "\n";

	$CYYM = substr($line[$LMS_A], 0, 4);
	$term = decode_semester($CYYM);

	if ($line[$LMS_A] == $line[$LMS_PRIMARY])
	{
		//This is a primary course
		//echo "match primary\n";
		
		//Look for existing course data
		//echo "looking for course\n";
		$Course = match_course($line[$LMS_A], $line[$DeptAbbr], $line[$CourseNumber], $line[$CourseTitle]);
		
		if (is_null($Course['course_id']) && !is_null($Course['course_alias_id'])) 
		{
			//echo "created by secondary linking\n";
			//CA and CI were created by secondary create course and link
			$Course['course_id'] = createCourse($line[$DeptAbbr], $line[$CourseNumber], $line[$CourseTitle], $line[$DeptTitle]);
			updateCourseAlias($Course['course_id'], $line[$CourseTitle], $line[$CourseSection], $line[$LMS_A]);
		} 
		elseif (($Course['registrar_key'] != $line[$LMS_A]) && !is_null($Course['course_id']))
		{
			//echo "reusing course creating CA/CI\n";
			//Course existed previously but not for this instance create reuse course create CA/CI
			$Course['course_instance_id'] = createCourseInstance('null', $term['semester'], $term['year'], $line[$aDate], $line[$eDate], $course_status, $course_enrollment);		
			$Course['course_alias_id'] 	  = createCourseAlias($Course['course_id'], $Course['course_instance_id'], $line[$CourseTitle], $line[$CourseSection], $line[$LMS_A]);
			updatePrimaryCourseAliasID($Course['course_instance_id'], $Course['course_alias_id'], $line[$CourseSection]);
		}
		elseif (is_null($Course['course_id']) && is_null($Course['course_instance_id']) && is_null($Course['course_alias_id']))
		{
			//echo "new creating all\n";
			//nothing exists create all
			$Course['course_id'] = createCourse($line[$DeptAbbr], $line[$CourseNumber], $line[$CourseTitle], $line[$DeptTitle]);
			$Course['course_instance_id'] = createCourseInstance('null', $term['semester'], $term['year'], $line[$aDate], $line[$eDate], $course_status, $course_enrollment);		
			$Course['course_alias_id'] 	  = createCourseAlias($Course['course_id'], $Course['course_instance_id'], $line[$CourseTitle], $line[$CourseSection], $line[$LMS_A]);
			updatePrimaryCourseAliasID($Course['course_instance_id'], $Course['course_alias_id'], $line[$CourseSection]);			
		}
				
	}
	else
	{
		//echo "match secondary\n";		
		//This is a cross listing
		//look for crosslisted course
		if (($Course = match_course($line[$LMS_A], $line[$DeptAbbr], $line[$CourseNumber], $line[$CourseTitle])) == FALSE)
		{			
		//echo "secondary course not found adding\n";			
			$Course['course_id'] = createCourse($line[$DeptAbbr], $line[$CourseNumber], $line[$CourseTitle], $line[$DeptTitle]); //cross listed course
		}
				
		//look for primary course
		//echo "searching for primary\n";		
		if (($PCourse = match_course($line[$LMS_PRIMARY], null, null, null)) == FALSE)
		{
			//primary has not been processed yet create placeholder
			//echo "creating placeholder\n";
			//$PCourse['course_id'] = createCourse(null, null, null, null);
			$PCourse['course_instance_id'] = createCourseInstance('null', $term['semester'], $term['year'], $line[$aDate], $line[$eDate], $course_status, $course_enrollment);
			$PCourse['course_alias_id'] = createCourseAlias('null', $PCourse['course_instance_id'], null, null, $line[$LMS_PRIMARY]);		
			updatePrimaryCourseAliasID($PCourse['course_instance_id'], $PCourse['course_alias_id'], null);
		}
		
		if ($Course['registrar_key'] != $line[$LMS_A])
		{
			$Course['course_alias_id'] = createCourseAlias($Course['course_id'], $PCourse['course_instance_id'], $line[$CourseTitle], $line[$CourseSection], $line[$LMS_A]);
			updatePrimaryCourseAliasID($PCourse['course_instance_id'], $PCourse['course_alias_id']);
		}
	}
	//echo "Course info:\n";print_r($Course); //echo "\n";
/*	
if ($line[$LMS_A] == '5061_EMORY_CHEM_OX_120_SEC11J')
{
 $g_dbConn->commit();
 die ("incomplete found '5061_EMORY_WS_385WR_SEC001'\n");
}
*/	
} //end while

$g_dbConn->commit();

unset($d);
$g_dbConn->disconnect();
fclose($fp);

//echo "done.\n\n";
exit;

/**
 * @return array course_id, course_instance_id
 * @param string $LMS_GROUP_ID registrar key
 * @param string $courseDept department abbr
 * @param string $courseNumber 
 * @param string $courseTitle title
 * @desc Searches DB for course data on LMS_GROUP_ID or courseNumber and Title returns false if not found
 */
function match_course($LMS_GROUP_ID, $courseDept, $courseNumber, $courseTitle)
{
	global $g_dbConn, $stdout;

	switch($g_dbConn->phptype) 
	{
		default:	//mysql
			
			$sql[1] = "SELECT course_id, course_instance_id, registrar_key, course_alias_id
					   FROM course_aliases as ca
					   WHERE registrar_key = ?";
			
			$sql[2] = "SELECT c.course_id, ca.course_instance_id, ca.registrar_key, ca.course_alias_id
					FROM courses as c						
					LEFT JOIN course_aliases as ca on c.course_id = ca.course_id
					LEFT JOIN (
							SELECT department_id, abbreviation FROM departments WHERE abbreviation = ?
						) as d on c.department_id = d.department_id
					WHERE c.course_number = ? AND c.uniform_title = ? AND d.abbreviation = ?";
	}
		

	$rs =& $g_dbConn->query($sql[1], $LMS_GROUP_ID);	
	if (DB::isError($rs)) { 
		//$g_dbConn->rollback();
		fwrite($stdout, $rs->getMessage()); 
		exit;
	}
	
//echo "search on LMS found " . 	$rs->numRows() . "\n";
	
	if($rs->numRows() > 0) 
	{	
		$rv =& $rs->fetchRow(DB_FETCHMODE_ASSOC);
		if ($rv['course_id'] == 0) $rv['course_id'] = null;
		if ($rv['course_instance_id'] == 0) $rv['course_instance_id'] = null;
		if ($rv['course_alias_id'] == 0) $rv['course_alias_id'] = null;
		return $rv;
	} else {
		$rs2 =& $g_dbConn->query($sql[2], array($courseDept, $courseNumber, $courseTitle, $courseDept));	
		if (DB::isError($rs2)) { 
			$g_dbConn->rollback();
			fwrite($stdout, $rs2->getMessage()); 
			exit;
		}
		
		if ($rs2->numRows() > 0){
			$rv = $rs2->fetchRow(DB_FETCHMODE_ASSOC);
			if ($rv['course_id'] == 0) $rv['course_id'] = null;
			if ($rv['course_instance_id'] == 0) $rv['course_instance_id'] = null;
			if ($rv['course_alias_id'] == 0) $rv['course_alias_id'] = null;
			return $rv;
		} else {
			return false;
		}
	}
}

function createCourse($DeptAbbr=null, $courseNumber=null, $courseTitle=null, $DeptTitle=null)
{
//echo "function createCourse($DeptAbbr, $courseNumber, $courseTitle, $DeptTitle)\n";	
	global $g_dbConn, $stdout, $d;
	
	switch($g_dbConn->phptype) 
	{
		default:	//mysql			
			$sql = "INSERT INTO courses 
						(department_id, course_number, uniform_title) 
					VALUES 
						(!,?,?)
			";
	}	

	$dept_id = 0;
	if (!is_null($DeptAbbr))
	{
		$dept_id = $d->getDepartmentByAbbr($DeptAbbr);
		if (is_null($dept_id))
		{
			//echo "createing new dept\n";
			//department not found create 
			$tmp_d = new department();
			$tmp_d->createDepartment($DeptTitle, $DeptAbbr, 0);
			$dept_id = $tmp_d->getDepartmentID();
			unset($tmp_d);
		}
	}

	$rs =& $g_dbConn->query($sql, array($dept_id, $courseNumber, $courseTitle));
	if (DB::isError($rs)) { 
		$g_dbConn->rollback();
		fwrite($stdout, $rs->getMessage()); 
		exit;
	}
	
	$rv =& $g_dbConn->getOne("SELECT LAST_INSERT_ID() FROM courses");
	if (DB::isError($rv)) { 
		$g_dbConn->rollback();
		fwrite($stdout, $rv->getMessage()); 
		exit;
	}

	if ($rv['course_id'] == 0) $rv['course_id'] = null;
//echo "returning [$rv]\n";
	return $rv;	
}


function updateCourse($courseID=null, $DeptAbbr=null, $courseNumber=null, $courseTitle=null, $DeptTitle=null)
{
	global $g_dbConn, $stdout, $d;
//echo "updateCourse($courseID, $DeptAbbr, $courseNumber, $courseTitle, $DeptTitle)\n";	
	
	switch($g_dbConn->phptype) 
	{
		default:	//mysql			
			$sql = "UPDATE courses
						set department_id = !, course_number = ?, uniform_title = ?
					WHERE course_id = !
			";
	}	
		
	$dept_id = $d->getDepartmentByAbbr($DeptAbbr);
	if (is_null($dept_id))
	{
		//echo "createing new dept\n";
		//department not found create 
		$tmp_d = new department();
		$tmp_d->createDepartment($DeptTitle, $DeptAbbr, 0);
		$dept_id = $tmp_d->getDepartmentID();
		unset($tmp_d);
	}

	$rs =& $g_dbConn->query($sql, array($dept_id, $courseNumber, $courseTitle, $courseID));
	if (DB::isError($rs)) {
		$g_dbConn->rollback();
		fwrite($stdout, $rs->getMessage()); 
		exit;
	}
}

function updateCourseAlias($c_id, $name, $section, $registrar_key)
{
//echo "function updateCourseAlias($c_id, $section, $registrar_key)\n";	
	global $g_dbConn, $stdout;
	
	switch($g_dbConn->phptype) 
	{
		default:	//mysql	
			$sql = "UPDATE course_aliases
						SET course_id = !, section = ?, course_name = ?
					WHERE registrar_key = ?
			";
			
	}	
	
	$rs =& $g_dbConn->query($sql, array($c_id, $section, $name, $registrar_key));
	if (DB::isError($rs)) {
		$g_dbConn->rollback();
		fwrite($stdout, $rs->getMessage()); 
		exit;
	}
}

function createCourseInstance($primary_alias_id=null, $term=null, $year=null, $aDate=null, $dDate=null, $status=null, $enrollment=null)
{
//echo "function createCourseInstance($primary_alias_id, $term, $year, $aDate, $dDate, $status, $enrollment)\n";	
	global $g_dbConn, $stdout;
	
	switch($g_dbConn->phptype) 
	{
		default:	//mysql	
			$sql = "INSERT INTO course_instances 
						(primary_course_alias_id, term, year, activation_date, expiration_date, status, enrollment) 
					VALUES 
						(!,?,?,?,?,?,?)
			";
			
			$primary_alias_id = (is_null($primary_alias_id)) ? 'null' : $primary_alias_id;
	}	
	
	$rs =& $g_dbConn->query($sql, array($primary_alias_id, $term, $year, $aDate, $dDate, $status, $enrollment));
	if (DB::isError($rs)) {
		$g_dbConn->rollback();
		fwrite($stdout, $rs->getMessage()); 
		exit;
	}
	
	$rv =& $g_dbConn->getOne("SELECT LAST_INSERT_ID() FROM course_instances");
	if (DB::isError($rv)) { 
		$g_dbConn->rollback();
		fwrite($stdout, $rv->getMessage()); 
		exit;
	}
	
	if ($rv == 0) $rv = null;
//echo "returning [$rv]\n";	
	return $rv;	
}

function createCourseAlias($course_id=null, $course_instance_id=null, $name=null,$section=null, $registrar_key=null)
{
//echo "function createCourseAlias($course_id, $course_instance_id, $section, $registrar_key)\n";	
	global $g_dbConn, $stdout;
	
	switch($g_dbConn->phptype) 
	{
		default:	//mysql	
			$sql = "INSERT INTO course_aliases
						(course_id, course_instance_id, section, registrar_key, course_name) 
					VALUES 
						(!,!,?,?,?)
			";
			
			$course_id = (is_null($course_id)) ? 'null' : $course_id;
			$course_instance_id = (is_null($course_instance_id)) ? 'null' : $course_instance_id;
	}	
	
	$rs =& $g_dbConn->query($sql, array($course_id, $course_instance_id, $section, $registrar_key, $name));
	if (DB::isError($rs)) {
		$g_dbConn->rollback();
		fwrite($stdout, $rs->getMessage()); 
		exit;
	}
	
	$rv =& $g_dbConn->getOne("SELECT LAST_INSERT_ID() FROM course_aliases");
	if (DB::isError($rv)) { 
		$g_dbConn->rollback();
		fwrite($stdout, $rv->getMessage()); 
		exit;
	}

	if ($rv == 0) $rv = null;
//echo "returning [$rv]\n";	
	return $rv;	
}

function updatePrimaryCourseAliasID($course_instance_id=null, $alias_id=null, $section = NULL)
{
//echo "function updatePrimaryCourseAliasID($course_instance_id, $alias_id, $section)\n";	
	global $g_dbConn, $stdout;
	
	switch($g_dbConn->phptype) 
	{
		default:	//mysql	
			$sql['pci'] = "UPDATE course_instances
					SET primary_course_alias_id = !
					WHERE course_instance_id = !
			";
			
			$sql['section'] = "UPDATE course_aliases
					SET section = ?
					WHERE course_alias_id = !
			";
						
	}	
	
//echo "updatePrimaryCI ". $sql['pci'].", $alias_id, $course_instance_id\n";	
	$rs =& $g_dbConn->query($sql['pci'], array($alias_id, $course_instance_id));
	if (DB::isError($rs)) {
		$g_dbConn->rollback();
		fwrite($stdout, $rs->getMessage()); 
		exit;
	}
	
	if (!is_null($section))
	{
		//echo "updatePrimaryCI_section ". $sql['section'].", $section, $alias_id\n";	
		$rs =& $g_dbConn->query($sql['section'], array($section, $alias_id));
		if (DB::isError($rs)) {
			$g_dbConn->rollback();
			fwrite($stdout, $rs->getMessage()); 
			exit;
		}	
	}
}
?>