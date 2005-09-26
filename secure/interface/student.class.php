<?
/*******************************************************************************
item.class.php
Item Primitive Object

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
require_once("secure/classes/user.class.php");
require_once("secure/classes/courseInstance.class.php");
require_once("secure/common.inc.php");

class student extends user
{
	//Attributes
	var $courseInstances = array();
	//var $reservesList = array();
	var $courseList = array();

	function student($userName)
	{
		if (is_null($userName)) trigger_error($userName . " has not been authorized as student", E_ERROR);
		else $this->getUserByUserName($userName);
	}

	/**
	* @return void
	* @desc Allows student to register themselves with the system
	*/
	/*
	function registerSelf()
	{
		$this->createUser();
	}
	*/
	/**
	* @return void
	* @param int $courseAliasID
	* @desc Add course alias to the user profile
	*/
	function attachCourseAlias($courseAliasID)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT access_id from access WHERE user_id = ! AND alias_id = ! and permission_level = 0";
				$sql2 = "INSERT INTO access (user_id, alias_id, permission_level) VALUES (!,!,0)";
		}

		$rs = $g_dbConn->query($sql, array($this->userID, $courseAliasID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		if ($rs->numRows() == 0) {
			$rs = $g_dbConn->query($sql2, array($this->userID, $courseAliasID));
			if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		}
	}

	function detachCourseAlias($courseAliasID)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "DELETE FROM access WHERE user_id = ! AND alias_id = ! and permission_level = 0 LIMIT 1";
		}

		$rs = $g_dbConn->query($sql, array($this->userID, $courseAliasID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	/**
	* @return array of courseInstances
	* @desc get all of the user's courseInstances from the access table
	*/
	/*
	function getMyCourseInstances()
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$d = date ('Y-m-d');

				$sql = "SELECT DISTINCT ca.course_instance_id "
					.  "FROM access AS a LEFT JOIN course_aliases AS ca "
					.  "  ON a.alias_id = ca.course_alias_id "
					.  "WHERE a.user_id = !";
		}

		//$rs = $g_dbConn->query($sql, array($targetID, $targetTable));
		$rs = $g_dbConn->query($sql, $this->userID);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$tmpArray = array();
		while ($row = $rs->fetchRow()) {
			$tmpArray[] = new courseInstance($row['course_instance_id']);
		}

		return $tmpArray;
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
					.  "WHERE a.user_id = ! AND ci.activation_date <= ? AND ? <= ci.expiration_date AND ci.status = 'ACTIVE'"
					;

				$d = date("Y-m-d"); //get current date
		}

		$rs = $g_dbConn->query($sql, array($this->getUserID(), $d, $d));

		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		unset($this->courseInstances);  // clean array
		while ($row = $rs->fetchRow()) {
			$this->courseInstances[] = new courseInstance($row[0]);
		}
	}
	*/
	function getCourseInstances()
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
			/*
			$sql = "SELECT ca.course_instance_id, a.alias_id "
					.  "FROM access as a "
					.  	 "LEFT  JOIN course_aliases AS ca ON a.alias_id = ca.course_alias_id "
					.  	 "LEFT  JOIN course_instances AS ci ON ca.course_instance_id = ci.course_instance_id "
					.  "WHERE a.user_id = ! AND ci.activation_date <= ? AND ? <= ci.expiration_date AND ci.status = 'ACTIVE'"
					;
			*/
			$sql = "SELECT DISTINCT ca.course_instance_id "
					.  "FROM access as a "
					.  	 "LEFT  JOIN course_aliases AS ca ON a.alias_id = ca.course_alias_id "
					.  	 "LEFT  JOIN course_instances AS ci ON ca.course_instance_id = ci.course_instance_id "
					.  "WHERE a.user_id = ! AND ci.activation_date <= ? AND ? <= ci.expiration_date AND ci.status = 'ACTIVE'"
					;

				$d = date("Y-m-d"); //get current date
		}

		$rs = $g_dbConn->query($sql, array($this->getUserID(), $d, $d));

		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		unset($this->courseInstances);  // clean array
		while ($row = $rs->fetchRow()) {
			//$tempCi = new courseInstance($row[0]);
			//$tempCi->aliasID = $row[1];
			//$this->courseInstances[] = $tempCi;
			$this->courseInstances[] = new courseInstance($row[0]);
		}
	}
	/**
	* @return void
	* @desc surpresses a reserve from display --Not Yet Implemented
	*/
	function hideReserve()
	{
	}

	/**
	* @return void
	* @desc unsurpresses a reserve from display --Not Yet Implemented
	*/
	function unhideReserve()
	{
	}

	/**
	* @return array of reserves
	* @param int $courseInstanceID
	* @desc getAllReserves for a course
	*/
	/*
	function getAllReserves($courseInstanceID)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT reserves_id "
					.  "FROM reserves "
					.  "WHERE course_instance_id = ! ";
		}

		$rs = $g_dbConn->query($sql, array($courseInstanceID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$tmpArray = array();
		while ($row = $rs->fetchRow()) {
			$tmpArray[] = new reserve($row['reserves_id']);
		}

		return $tmpArray;
	}
	*/
	/**
	* @return array of reserves
	* @param int $courseInstanceID
	* @desc get Reserve items hidden by user for a course
	*/
	function getHiddenReserves($courseInstanceID)
	{
	}

	/**
	* @return array of reserves
	* @param int $courseInstanceID
	* @desc get Reserve items not hidden by user for a course
	*/
	function getUnhiddenReserves($courseInstanceID)
	{
	}

	/**
	* @return array of courseInstances
	* @desc get current and active courseInstances from the access table by deptID
	*/
	function getCoursesByDept($deptID, $aDate=null, $eDate=null)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$d = date("Y-m-d"); //get current date

				$sql = "SELECT DISTINCT ca.course_alias_id "
					.  "FROM course_instances AS ci "
					.  	 "LEFT  JOIN course_aliases AS ca ON ca.course_instance_id = ci.course_instance_id "
					.    "LEFT  JOIN courses AS c ON c.course_id = ca.course_id "
					.    "LEFT  JOIN departments AS d ON d.department_id = c.department_id "
					.  "WHERE d.department_id = ! AND ci.status = 'ACTIVE' "
					;

				if (!is_null($aDate) && !is_null($eDate))
					$sql .= "AND '$aDate' <= ci.activation_date AND ci.expiration_date <= '$eDate' ";
				else
					$sql .= "AND ci.activation_date <= '$d' AND '$d' <= ci.expiration_date ";

				$sql .=	"ORDER BY ci.expiration_date ASC";
		}

		$rs = $g_dbConn->query($sql, $deptID);

		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		unset($this->courseList);  // clean array
		while ($row = $rs->fetchRow()) {
			$this->courseList[] = new course($row[0]);
		}
	}
}
?>