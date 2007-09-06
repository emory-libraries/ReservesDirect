<?
/*******************************************************************************
course.class.php
Course Primitive Object

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

require_once("secure/classes/department.class.php");

class course
{
	//Attributes
	public $courseID;
	public $courseAliasID;
	public $name;
	public $deptID;
	public $department;
	public $courseNo;
	public $section;
	public $uniformTitle;
	public $registrarKey;

	/**
	* @return course
	* @param int $courseAliasID
	* @desc construct course object
	*/
	function course($courseAliasID = NULL)
	{
		if (!is_null($courseAliasID)){
			$this->getCourse($courseAliasID);
		}
	}

	function createNewCourse($courseInstanceID)
	{
		global $g_dbConn;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql1 = "INSERT INTO courses (uniform_title) VALUES ('')";
				$sql2 = "SELECT LAST_INSERT_ID() FROM courses";
				$sql3 = "INSERT INTO course_aliases (course_id, course_instance_id, course_name, section) VALUES (!, !, NULL, NULL)";
				$sql4 = "SELECT LAST_INSERT_ID() FROM course_aliases";
		}

		$rs = $g_dbConn->query($sql1);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$rs = $g_dbConn->query($sql2);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$row = $rs->fetchRow();
		if (DB::isError($row)) { trigger_error($row->getMessage(), E_USER_ERROR); }
		$this->courseID = $row[0];

		$rs = $g_dbConn->query($sql3, array($this->courseID, $courseInstanceID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$rs = $g_dbConn->query($sql4);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$row = $rs->fetchRow();
		if (DB::isError($row)) { trigger_error($row->getMessage(), E_USER_ERROR); }
		$this->courseAliasID = $row[0];

	}

	/**
	* @return void
	* @desc destroy the database entry
	*/
	function destroy()
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "DELETE "
					.  "FROM courses "
					.  "WHERE course_id = !"
					;
		}

		$rs = $g_dbConn->query($sql, $this->courseID);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	/**
	* @return void
	* @param int $courseID
	* @desc PRIVATE get the course date from the DB
	*/
	private function getCourse($courseAliasID)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT ca.course_id, c.department_id, c.course_number, ca.course_name, c.uniform_title, ca.section, ca.course_alias_id, ca.registrar_key "
					.  "FROM courses as c JOIN course_aliases as ca ON c.course_id = ca.course_id AND ca.course_alias_id = !";
		}

		$rs = $g_dbConn->query($sql, $courseAliasID);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$row = $rs->fetchRow();
			$this->courseID 		= $row[0];
			$this->deptID 			= $row[1];
			$this->courseNo			= $row[2];
			$this->name	 			= $row[3];
			$this->uniformTitle 	= $row[4];
			$this->section 			= $row[5];
			$this->courseAliasID	= $row[6];
			$this->registrarKey		= $row[7];
	}

	/**
	* @return course
	* @param int $courseID
	* @desc load object from db
	*/
	function getCourseByID($courseID)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT course_id, department_id, course_number, uniform_title "
					.  "FROM courses "
					.  "WHERE course_id = !"
					;
		}

		$rs = $g_dbConn->query($sql, $courseID);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$row = $rs->fetchRow();
			$this->courseID 		= $row[0];
			$this->deptID 			= $row[1];
			$this->courseNo			= $row[2];
			$this->uniformTitle 	= $row[3];
	}

	/**
	 * @return boolean
	 * @param int $dept_id Department ID
	 * @param string $course_number Course number
	 * @param string $name Course name
	 * @desc Searches for a match on dept, course number, and name; loads object on success and return TRUE, else FALSE
	 */
	function getCourseByMatch($dept_id, $course_number, $uniform_title) {
		global $g_dbConn;
		
		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql = "SELECT course_id
						FROM courses
						WHERE department_id = ! AND course_number = ? AND uniform_title = ?";
		}
		
		$rs = $g_dbConn->query($sql, array($dept_id, $course_number, stripslashes($uniform_title)));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		if($rs->numRows() == 0) {
			return false;
		}
		else {
			$row = $rs->fetchRow();
			$this->getCourseByID($row[0]);
			return true;
		}
	}
	
	
	function setName($name)
	{
		global $g_dbConn;

		$this->name = stripslashes($name);

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE course_aliases SET course_name = ? WHERE course_alias_id = !";
		}

		$rs = $g_dbConn->query($sql, array($name, $this->courseAliasID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

	}

	function setUniformTitle($title)
	{
		global $g_dbConn;

		$this->uniformTitle = stripslashes($title);

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE courses SET uniform_title = ? WHERE course_id = !";
		}

		$rs = $g_dbConn->query($sql, array($title, $this->courseID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

	}

	function setRegistrarKey($key)
	{
		global $g_dbConn;

		$this->registrarKey = stripslashes($key);

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE course_aliases SET registrar_key = ? WHERE course_alias_id = !";
		}

		$rs = $g_dbConn->query($sql, array($key, $this->courseAliasID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

	}	
	
	function setCourseNo($courseNo)
	{
		global $g_dbConn;

		$this->courseNo = $courseNo;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE courses SET course_number = ? WHERE course_id = !";
		}

		$rs = $g_dbConn->query($sql, array($courseNo, $this->courseID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	function setSection($section)
	{
		global $g_dbConn;

		$this->section = $section;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = 	"UPDATE course_aliases SET section = ? "
				.		"WHERE course_alias_id = !";
		}

		$rs = $g_dbConn->query($sql, array($section, $this->courseAliasID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	function setDepartmentID($deptID)
	{
		global $g_dbConn;

		$this->deptID = $deptID;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE courses SET department_id = ! WHERE course_id = !";
		}

		$rs = $g_dbConn->query($sql, array($deptID, $this->courseID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	/**
	* basic get methods
	*/
	function getDepartment()
	{
		$this->department = new department($this->deptID);
	}

	function getName() { 
		//return name if it is not blank; otherwise return uniform title
		return htmlentities(stripslashes(!empty($this->name) ? $this->name : $this->uniformTitle));
	}

	function getCourseNo()
	{ return $this->courseNo; }

	function getCourseAliasID()
	{ return $this->courseAliasID; }

	function displayCourseNo()
	{
		//if (!is_a($this->department, "department")) $this->getDepartment();
		if (!($this->department instanceof department)) $this->getDepartment();
		$s = $this->department->getAbbr() . " " . $this->courseNo;
		if (!is_null($this->section) && $this->section != "") $s .= "-" . $this->section;
		return htmlentities(stripslashes($s));
	}

	function getSection()
	{ return htmlentities(stripslashes($this->section)); }

	function getUniformTitle()
	{ return $this->uniformTitle; }

	function getCourseID() { return $this->courseID; }
	
	function getRegistrarKey() { return $this->registrarKey; }

	function bindToCourseInstance($course_instance_id)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'  
				$sql = "UPDATE course_aliases SET course_instance_id = ! WHERE course_alias_id = !";
		}

		$rs = $g_dbConn->query($sql, array($course_instance_id, $this->courseAliasID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }		
	}
}
?>
