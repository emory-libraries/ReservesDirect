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
	 * @return array
	 * @desc fetches all CIs that have status of ACTIVE, that this user is enrolled in, and whose date range includes today
	 */
	public function getCourseInstances() {
		$today = date('Y-m-d');
		return $this->fetchCourseInstances('student', 'ACTIVE', $today, $today);
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
}
?>