<?
/*******************************************************************************
proxy.class.php
Proxy Interface Object

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
require_once("secure/interface/student.class.php");
require_once("secure/classes/courseInstance.class.php");
require_once("secure/common.inc.php");

class proxy extends student
{
	function proxy($userName=null)
	{
		if (!is_null($userName)) {
			$this->getUserByUserName($userName);
			$this->role = 2;
		}
	}

	
	/**
	 * @return array
	 * @desc Returns an array of current and future CIs this user can edit
	 */
	public function getCourseInstancesToEdit() {
		//show current courses, or those that will start within a year
		//do not show expired courses
		$activation_date = date('Y-m-d', strtotime('+1 year'));
		$expiration_date = date('Y-m-d');
		
		//now query
		return array_merge(
			$this->fetchCourseInstances('proxy', $activation_date, $expiration_date, 'ACTIVE'),
			$this->fetchCourseInstances('proxy', $activation_date, $expiration_date, 'AUTOFEED')
		);
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
	function addCrossListing($ci, $dept, $courseNo, $section, $courseName, $ca=null)
	{

		$course = new course();
		if (is_null($ca))
			$course->createNewCourse($ci->courseInstanceID);
		else
		{
			$course->course($ca);
			$course->bindToCourseInstance($ci->courseInstanceID);
		}
	
		
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

		$tmpArray = array();
		
		//$tmpArray[0] = array ('mimetype_id' => null, 'helper_app_name' => 'Default', 'helper_app_icon' => 'images/doc_type_icons/doctype-clear.gif', 'file_extensions' => null);
		$tmpArray[0] = array ('mimetype_id' => null, 'helper_app_name' => 'Default', 'helper_app_icon' => null, 'file_extensions' => null);
		
		while($row = $rs->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$tmpArray[] = $row;
		}

		return $tmpArray;
	}
	
	
	/**
	 * @return boolean
	 * @param int $ci_id CourseInstance ID
	 * @param array $student_IDs Array of student userIDs
	 * @param string $roll_action Roll action to perform (add/remove/deny)
	 * @desc Adds, removes, etc students to/from class
	 */
	function editClassRoll($ci_id, $student_IDs, $roll_action) {
		global $u, $g_permission;
		
		//make sure we have all the info
		if(empty($ci_id) || empty($student_IDs)) {
			return false;
		}
		//for compatibility, make sure $student_IDs is an array
		if(!is_array($student_IDs)) {
			$student_IDs = array($student_IDs);	//make it an array
		}
		
		$ci = new courseInstance($ci_id);	//init CI
		//only allow instructors and proxies for THIS class to manipulate roll (or staff+)					
		$ci->getInstructors();
		$ci->getProxies();					
		if(in_array($u->getUserID(), $ci->instructorIDs) || in_array($u->getUserID(), $ci->proxyIDs) || ($u->getRole() >= $g_permission['staff'])) {
			foreach($student_IDs as $student_id) {	
				if(empty($student_id)) {
					continue;	//skip blank IDs
				}
				
				//get the student
				//some limitations -- cannot create a new student object by user ID
				$student = new user($student_id);	//init a generic user object
				$student = new student($student->getUsername());	//now init a student object by username
				
				//get the primary course for this user
				$ci->getCourseForUser($student->getUserID());
				
				//perform action
				switch($roll_action) {
					case 'add':
						$student->joinClass($ci->course->getCourseAliasID(), 'APPROVED');
					break;				
					case 'remove':
						$student->leaveClass($ci->course->getCourseAliasID());
					break;				
					case 'deny':
						$student->joinClass($ci->course->getCourseAliasID(), 'DENIED');
					break;
				}
			}
		}
	}

}
