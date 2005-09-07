<?
/*******************************************************************************
proxy.class.php
Proxy Interface Object

Created by Jason White (jbwhite@emory.edu)

This file is part of GNU ReservesDirect 2.1

Copyright (c) 2004-2005 Emory University, Atlanta, Georgia.

ReservesDirect 2.1 is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

ReservesDirect 2.1 is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with ReservesDirect 2.1; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

Reserves Direct 2.1 is located at:
http://www.reservesdirect.org/


*******************************************************************************/
require_once("secure/interface/student.class.php");
require_once("secure/classes/courseInstance.class.php");
require_once("secure/common.inc.php");

class proxy extends student
{
	function proxy($userName=null)
	{
		if (!is_null($userName)) $this->getUserByUserName($userName);
	}

	/**
	* @return courseInstance Array
	* @desc returns non-expired Course Instances
	*/
	/*
	function getEditableCourseInstances($aDate=null, $eDate=null)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$d = date("Y-m-d");

				$sql  = "SELECT DISTINCT ca.course_instance_id "
				.  		"FROM access as a "
				.  		"  JOIN course_aliases as ca on a.alias_id = ca.course_alias_id "
				.		"  JOIN course_instances as ci ON ca.course_instance_id = ci.course_instance_id "
				.		"WHERE a.user_id = ! AND a.permission_level >= 2 " //2 = proxy minimal edit permission
				;

				if (!is_null($aDate) && !is_null($eDate))
					$sql .= "AND '$aDate' <= ci.activation_date AND ci.expiration_date <= '$eDate' ";
				else
					$sql .= "AND '$d' <= ci.expiration_date ";  //get any current or future classes

				$sql .=	"ORDER BY ci.expiration_date ASC, ci.status DESC";
		}

		$rs = $g_dbConn->query($sql, $this->getUserID());
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		unset($this->courseInstances);  // clean array
		while ($row = $rs->fetchRow()) {
			$this->courseInstances[] = new courseInstance($row[0]);
		}
		return $this->courseInstances;
	}
	*/

	/**
	* @return array of courseInstances
	* @desc get current and active courseInstances from the access table
	*/
	/*
	function getCurrentCourseInstances()
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT DISTINCT ca.course_instance_id "
					.  "FROM course_instances AS ci "
					.  	 "LEFT  JOIN course_aliases AS ca ON ca.course_instance_id = ci.course_instance_id "
					.    "LEFT  JOIN access AS a ON a.alias_id = ca.course_alias_id "
					.  "WHERE a.user_id = ! AND ? <= ci.expiration_date"
					;

				$d = date("Y-m-d"); //get current date
		}

		$rs = $g_dbConn->query($sql, array($this->getUserID(), $d));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		unset($this->courseInstances);  // clean array
		while ($row = $rs->fetchRow()) {
			$this->courseInstances[] = new courseInstance($row[0]);
		}
	}
	*/
	function getCourseInstances($aDate=null, $eDate=null, $editableOnly=null)
	{
		global $g_dbConn, $g_permission;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$d = date("Y-m-d");

				$sql  = "SELECT DISTINCT ca.course_instance_id "
				.  		"FROM access as a "
				.  		"  JOIN course_aliases as ca on a.alias_id = ca.course_alias_id "
				.		"  JOIN course_instances as ci ON ca.course_instance_id = ci.course_instance_id "
				.		"WHERE a.user_id = ! "
				.		"AND a.permission_level >= ".$g_permission['proxy']." "
				;

		$sql_student = "SELECT DISTINCT ca.course_instance_id "
					.  "FROM access as a "
					.  	 "LEFT  JOIN course_aliases AS ca ON a.alias_id = ca.course_alias_id "
					.  	 "LEFT  JOIN course_instances AS ci ON ca.course_instance_id = ci.course_instance_id "
					.  "WHERE a.user_id = ! AND ci.activation_date <= ? AND ? <= ci.expiration_date AND ci.status = 'ACTIVE'"
					.		"AND a.permission_level < ".$g_permission['proxy']." "
					;

				if (!is_null($aDate) && !is_null($eDate))
					$sql .= "AND '$aDate' <= ci.activation_date AND ci.expiration_date <= '$eDate' ";
				else
					$sql .= "AND '$d' <= ci.expiration_date ";  //get any current or future classes

				$sql .=	"ORDER BY ci.expiration_date ASC, ci.status DESC";
		}

		$rs = $g_dbConn->query($sql, $this->getUserID());
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		unset($this->courseInstances);  // clean array
		while ($row = $rs->fetchRow()) {
			$this->courseInstances[] = new courseInstance($row[0]);
		}

		if (is_null($editableOnly)) {
			$rs = $g_dbConn->query($sql_student, array($this->getUserID(),$d,$d));
			if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

			while ($row = $rs->fetchRow()) {
				$this->courseInstances[] = new courseInstance($row[0]);
			}
		}
		return $this->courseInstances;
	}

	/**
	* @return $errorMsg
	* @desc remove a cross listing from the database
	*/
	function removeCrossListing($courseAliasID)
	{
		global $g_dbConn;

		$errorMsg = "";
		$course = new course($courseAliasID);

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql =	"SELECT CONCAT(u.last_name,', ',u.first_name) AS full_name "
					.	"FROM access a "
					.	" LEFT JOIN users u ON u.user_id = a.user_id "
					.	"WHERE a.alias_id = ! "
					.	"AND a.permission_level = 0 "
					.	"ORDER BY full_name";
				$sql2 = "DELETE FROM access "
					.	"WHERE alias_id = ! "
					.	"AND permission_level >= 2";
				$sql3 = "DELETE FROM course_aliases "
					.  "WHERE course_alias_id = !";

		}

		//Check to see if any students have added the course_alias
		$rs = $g_dbConn->query($sql, $courseAliasID);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		if ($rs->numRows() == 0) {
			//Delete entries, for Proxy or greater, from the access table
			$rs = $g_dbConn->query($sql2, $courseAliasID);
			if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

			//Delete entry from the course_alias table
			$rs = $g_dbConn->query($sql3, $courseAliasID);
			if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

			//Delete the actual course
			$course->destroy();
		} else {
			$errorMsg = "<br>The cross listed course, ".$course->displayCourseNo().", could not be deleted because the following student(s) have added the course:<br>";

			$i=0;
			while ($row = $rs->fetchRow()) {
				if ($i==0) {
					$errorMsg = $errorMsg.$row[0];
				} else {
					$errorMsg = $errorMsg."; ".$row[0];
				}
				$i++;
			}
			$errorMsg = $errorMsg."<br>Please contact the Reserves Desk for further assistance.<br>";
		}

		return $errorMsg;
	}


	/**
	* @return void
	* @desc add a cross listing to a course_instance
	*/
	function addCrossListing($ci, $dept, $courseNo, $section, $courseName)
	{
		$course = new course();
		$course->createNewCourse($ci->courseInstanceID);

		$course->setDepartmentID($dept);
		$course->setCourseNo($courseNo);
		$course->setSection($section);
		$course->setName($courseName);

		$ci->getInstructors();
		$ci->getProxies();

		//Add access to the Cross Listing for all instructors teaching the course
		for($i=0;$i<count($ci->instructorIDs);$i++) {
			$ci->addInstructor($course->courseAliasID,$ci->instructorIDs[$i]);
		}

		/* commented out by kawashi - No longer able to change primary, so this is not necessary 11.12.04
		//Add access to the Cross Listing for all proxies assigned to the course
		for($i=0;$i<count($ci->proxyIDs);$i++) {
			$ci->addProxy($course->courseAliasID,$ci->proxyIDs[$i]);
		}
		*/
	}

	/**
	* @return array of courses
	* @desc return courses taught by a given instructor
	*/
	function getCoursesByInstructor($instrID)
	{
		global $g_dbConn, $g_permission;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT DISTINCT ca.course_id "
				.	   "FROM courses as c "
				.	   "	LEFT JOIN course_aliases as ca ON c.course_id = ca.course_id "
				.	   "	LEFT JOIN access as a ON ca.course_alias_id = a.alias_id "
				.	   "WHERE a.user_id = ? AND a.permission_level = !";

		}

		$rs = $g_dbConn->query($sql, array($instrID, $g_permission['instructor']));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$tmpArray = array();
		while($row = $rs->fetchRow())
		{
			$c = new course();
			$c->getCourseByID($row[0]);
			$tmpArray[] = $c;
		}
		return $tmpArray;
	}
	
	function getAllDocTypeIcons()
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT DISTINCT mimetype_id, helper_app_name, helper_app_icon, file_extentions "
				.	   "FROM mimetypes "
				.	   "ORDER BY mimetype_id ASC"	
				;

		}
		
		
		$rs = $g_dbConn->query($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		//$tmpArray = array();
		
		//$tmpArray[0] = array ('mimetype_id' => null, 'helper_app_name' => 'Default', 'helper_app_icon' => 'images/doc_type_icons/doctype-clear.gif', 'file_extensions' => null);
		$tmpArray[0] = array ('mimetype_id' => null, 'helper_app_name' => 'Default', 'helper_app_icon' => null, 'file_extensions' => null);
		
		while($row = $rs->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$tmpArray[] = $row;
		}

		return $tmpArray;
	}

}