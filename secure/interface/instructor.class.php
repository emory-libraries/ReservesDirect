<?
/*******************************************************************************
proxy.class.php
Proxy Interface

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
require_once("secure/interface/proxy.class.php");
require_once("secure/classes/courseInstance.class.php");
require_once("secure/common.inc.php");

class instructor extends proxy
{
	//Attributes
	var $ils_user_id;
	var $ils_name;
	var $organization_status;


	function instructor($userName=null)
	{
		if (!is_null($userName)) $this->getUserByUserName($userName);
	}


	/**
	* @return void
	* @get intructor attributes from table
	*/
	function getInstructorAttributes()
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$d = date('Y-m-d');

				$sql = 	"SELECT ils_user_id, ils_name, organizational_status "
				.		"FROM instructor_attributes "
				.		"  WHERE user_id = ! "
				;

		}
		$rs = $g_dbConn->query($sql, $this->getUserID());
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		list($this->ils_user_id, $this->ils_name, $this->organization_status) = $rs->fetchRow();
	}

	/**
	* @return void
	* @get intructor attributes from table
	*/
	function storeInstructorAttributes($ILS_userID, $ILS_name, $orgStatus="")
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$d = date('Y-m-d');

				$sql = 	"SELECT count(user_id) "
				.		"FROM instructor_attributes "
				.		"WHERE user_id = ! "
				;

		}
		$rs = $g_dbConn->query($sql, $this->getUserID());
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$cnt = $rs->fetchRow();

		if ($cnt[0] == 0)
		{
			$sql = "INSERT INTO instructor_attributes "
				 . "(user_id, ils_user_id, ils_name, organizational_status ) "
				 . "VALUES (!,?,?,?)"
				 ;
			$values = array($this->getUserID(), $ILS_userID, $ILS_name, $orgStatus);
		} else {
			$sql = "UPDATE instructor_attributes "
				 . "SET ils_user_id=?, ils_name=?, organizational_status=? "
				 . "WHERE user_id=!"
				 ;
			$values = array($ILS_userID, $ILS_name, $orgStatus, $this->getUserID());
		}

		$rs = $g_dbConn->query($sql, $values);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	function getILSUserID() { return ($this->ils_user_id != "") ? $this->ils_user_id : null;}
	function getILSName()	{ return $this->ils_name; }
	function getOrgStatus() { return $this->organizational_status; }


	/**
	* @return void
	* @desc return array of proxies for instructors current and future classes
	*/
	/*
	function getProxies()
	{
		global $g_dbConn, $g_permission;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = 	"SELECT DISTINCT proxy.user_id "
				.		"FROM access as proxy "
				.		"  JOIN access as a ON proxy.alias_id = a.alias_id AND proxy.permission_level = " . $g_permission['proxy'] . " AND a.user_id = ! "
				.		"  JOIN course_aliases as ca ON a.alias_id = ca.course_alias_id "
				.		"  JOIN course_instances as ci ON ca.course_instance_id = ci.course_instance_id "
				.		"  WHERE ci.expiration_date > ?"
				;

				$d = date('Y-m-d');
		}
		$rs = $g_dbConn->query($sql, array($this->getUserID(), $d));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$tmpArray = array();
		while($row = $rs->fetchRow())
		{
			$p = new proxy();
			$p->getUserByID($row[0]);

			$tmpArray[] = $p;
		}
		return $tmpArray;
	}
	*/
	function removeProxy($proxyID, $courseInstanceID)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = 	"SELECT a.access_id "
				.		"FROM access as a "
				.		"  JOIN course_aliases as ca ON a.alias_id = ca.course_alias_id "
				.		"WHERE a.user_id = ! AND ca.course_instance_id = !"
				;

				$sql1 =	"DELETE "
				.		"FROM access "
				.		"  WHERE access_id = !"
				;
		}
		$rs = $g_dbConn->query($sql, array($proxyID, $courseInstanceID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		while($row = $rs->fetchRow())
		{
			$aliasID = $row[0];

			$rs2 = $g_dbConn->query($sql1, $aliasID);
			if (DB::isError($rs2)) { trigger_error($rs2->getMessage(), E_USER_ERROR); }
		}
	}

	/**
	* @return void
	* @param int $proxyID
	* @param int $courseInstanceID
	* @desc update users table if necessary and insert into access table to make the specified user a proxy for the specified class
	*/
	function makeProxy($proxyID, $courseInstanceID)
	{
		global $g_dbConn, $g_permission;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				//This statement is only updating record if permission level < proxy
				$sql = 	"UPDATE users SET dflt_permission_level = " . $g_permission['proxy'] . " "
				.		"WHERE user_id = ! AND dflt_permission_level < " . $g_permission['proxy']
				;

				/* commented out by kawashi on 11.12.04 - No longer adding proxies to cross listings
				$sql1 = "SELECT ca.course_alias_id "
				.		"FROM course_aliases as ca "
				.		"WHERE ca.course_instance_id = !"
				;
				*/

				//This SQL statement added by kawashi on 11.12.04 - We are now just adding proxies to the primary course
				$sql1 = "SELECT ci.primary_course_alias_id "
				.		"FROM course_instances as ci "
				.		"WHERE ci.course_instance_id = !"
				;

				$sql2 =	"SELECT access_id FROM access WHERE user_id = ! AND alias_id = ! AND permission_level = !";

				$sql3 =	"INSERT INTO access (user_id, alias_id, permission_level) VALUES (!, !, !)";
		}

		//Update default permission to proxy if current permssion level < proxy
		$rs = $g_dbConn->query($sql, array($proxyID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR);}

		/* commented out by kawashi on 11.12.2004 - No longer adding proxies to crosslistings, just to primary course
		//Get all course aliases for the given course instance id
		$rs = $g_dbConn->query($sql1, array($courseInstanceID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR);}

		//Loop through all course aliases
		while($row = $rs->fetchRow())
		{
			$aliasID = $row[0];

			//Check to see if proxy already has access to this alias in the access table
			$rs2 = $g_dbConn->query($sql2, array($proxyID, $aliasID, $g_permission['proxy']));
			if (DB::isError($rs2)) { trigger_error($rs2->getMessage(), E_USER_ERROR);}

			//If proxy doesn't have access...
			if ($rs2->numRows() == 0) {

				//Execute query to grant access for the proxy, to the couse alias, in the access table
				$rs3 = $g_dbConn->query($sql3, array($proxyID, $aliasID, $g_permission['proxy']));
				if (DB::isError($rs3)) { trigger_error($rs3->getMessage(), E_USER_ERROR);}

			}
		}
		*/

		//Code below was added by kawashi on 11.12.2004 - Now only adding proxies to primary course

		//Get primary course alias for the given course instance id
		$rs = $g_dbConn->query($sql1, array($courseInstanceID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR);}

		$row = $rs->fetchRow();
		$aliasID = $row[0];

		//Check to see if proxy already has access to this alias in the access table
		$rs2 = $g_dbConn->query($sql2, array($proxyID, $aliasID, $g_permission['proxy']));
		if (DB::isError($rs2)) { trigger_error($rs2->getMessage(), E_USER_ERROR);}

		//If proxy doesn't have access...
		if ($rs2->numRows() == 0) {
			//Execute query to grant access for the proxy, to the couse alias, in the access table
			$rs3 = $g_dbConn->query($sql3, array($proxyID, $aliasID, $g_permission['proxy']));
			if (DB::isError($rs3)) { trigger_error($rs3->getMessage(), E_USER_ERROR);}
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
	
		//get CIs where user is an instructor
		$intructor_CIs = $this->fetchCourseInstances('instructor', $activation_date, $expiration_date);
		//get CIs where user is a proxy
		$proxy_CIs = $this->fetchCourseInstances('proxy', $activation_date, $expiration_date);
		
		//return the combined list
		return array_merge($intructor_CIs, $proxy_CIs);
	}
	
	
	/**
	 * @return array
	 * @desc Returns an array of current and past CIs this user can edit
	 */
	public function getCourseInstancesToImport() {
		//show current courses, or those that have already expired
		$activation_date = date('Y-m-d');
			
		//return list of CIs
		return $this->fetchCourseInstances('instructor', $activation_date);
	}


    /**
     * Generate a list of available reports
     *
     * @return array containing report_id, title, sql, parameters
     */
    function getReportList()
    {
            global $g_dbConn, $g_permission;

            switch ($g_dbConn->phptype)
            {
                    default: //'mysql'
                            $sql = "SELECT report_id, title, sql, parameters "
                                    .  "FROM reports "
                                    .  "WHERE min_permissions <= " . $this->getRole() . " "
                                    .  "ORDER BY sort_order";
                                    ;
            }

            $rs = $g_dbConn->query($sql);
            if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
			
            $tmpArray = null;
            while ($row = $rs->fetchRow(DB_FETCHMODE_ASSOC))
                    $tmpArray[] = $row;

            return $tmpArray;
    }
}