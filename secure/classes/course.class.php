<?
/*******************************************************************************
course.class.php
Course Primitive Object

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
	public $notes;		   //array of notes

	/**
	* @return course
	* @param int $courseAliasID
	* @desc construct course object
	*/
	function course($courseAliasID = NULL) //, $name, $dept, $courseNo, $managingLibrary, $section, $uniformTitle, $notes = NULL)
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
				$sql1 = "INSERT INTO courses (department_id, course_number, course_name, uniform_title) VALUES (0, NULL, NULL, 't')";
				$sql2 = "SELECT LAST_INSERT_ID() FROM courses";
				$sql3 = "INSERT INTO course_aliases (course_id, course_instance_id, section) VALUES (!, !, NULL)";
				$sql4 = "SELECT LAST_INSERT_ID() FROM course_aliases";
		}

		$rs = $g_dbConn->query($sql1);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_ERROR); }

		$rs = $g_dbConn->query($sql2);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_ERROR); }

		$row = $rs->fetchRow();
		if (DB::isError($row)) { trigger_error($row->getMessage(), E_ERROR); }
		$this->courseID = $row[0];

		$rs = $g_dbConn->query($sql3, array($this->courseID, $courseInstanceID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_ERROR); }

		$rs = $g_dbConn->query($sql4);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_ERROR); }

		$row = $rs->fetchRow();
		if (DB::isError($row)) { trigger_error($row->getMessage(), E_ERROR); }
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
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_ERROR); }
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
				$sql = "SELECT ca.course_id, c.department_id, c.course_number, c.course_name, c.uniform_title, ca.section, ca.course_alias_id "
					.  "FROM courses as c JOIN course_aliases as ca ON c.course_id = ca.course_id AND ca.course_alias_id = !";
		}

		$rs = $g_dbConn->query($sql, $courseAliasID);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_ERROR); }

		$row = $rs->fetchRow();
			$this->courseID 		= $row[0];
			$this->deptID 			= $row[1];
			$this->courseNo			= $row[2];
			$this->name	 			= $row[3];
			$this->uniformTitle 	= $row[4];
			$this->section 			= $row[5];
			$this->courseAliasID	= $row[6];
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
				$sql = "SELECT course_id, department_id, course_number, course_name, uniform_title "
					.  "FROM courses "
					.  "WHERE course_id = !"
					;
		}

		$rs = $g_dbConn->query($sql, $courseID);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_ERROR); }

		$row = $rs->fetchRow();
			$this->courseID 		= $row[0];
			$this->deptID 			= $row[1];
			$this->courseNo			= $row[2];
			$this->name	 			= $row[3];
			$this->uniformTitle 	= $row[4];
	}


	function setName($name)
	{
		global $g_dbConn;

		$this->name = stripslashes($name);

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE courses SET course_name = ? WHERE course_id = !";
		}

		$rs = $g_dbConn->query($sql, array($name, $this->courseID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_ERROR); }

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
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_ERROR); }
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
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_ERROR); }
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
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_ERROR); }
	}

	/**
	* basic get methods
	*/
	function getDepartment()
	{
		$this->department = new department($this->deptID);
	}

	function getName()
	{ return htmlentities(stripslashes(($this->name))); }

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

	function getNotes() { $this->notes = getNotesByTarget("courses", $this->courseID); }
	function setNote($type, $text) { $this->notes[] = common_setNote($type, $text, "courses", $this->courseID); }
}
?>
