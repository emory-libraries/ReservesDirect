<?
/*******************************************************************************
proxy.class.php
Proxy Interface

Reserves Direct 2.0

Copyright (c) 2004 Emory University General Libraries

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be included
in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

Created by Jason White (jbwhite@emory.edu)

Reserves Direct 2.0 is located at:
http://coursecontrol.sourceforge.net/

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
	* @return array of courseInstances
	* @desc return all the class this user has ever instructed
	*/
	function getAllCourseInstances($PastOnly=false)
	{
		global $g_dbConn, $g_permission;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$d = date('Y-m-d');
			
				$sql = 	"SELECT DISTINCT ci.course_instance_id "
				.		"FROM access as a "
				.		"  JOIN course_aliases as ca ON a.alias_id = ca.course_alias_id "
				.		"  JOIN course_instances as ci ON ca.course_instance_id = ci.course_instance_id "	
				.		"  WHERE a.permission_level = " . $g_permission['instructor'] . " AND a.user_id = ! "
				;
				
				if ($PastOnly == true)
					$sql .=	"  AND ci.expiration_date <= '$d' ";	
				
				$sql .=	"  ORDER BY ci.activation_date ASC ";
				
		}
		$rs = $g_dbConn->query($sql, $this->getUserID());				
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		$tmpArray = array();
		while($row = $rs->fetchRow())
		{		
			$tmpArray[] = new courseInstance($row[0]);
		}		
		return $tmpArray;		
	}

	/**
	* @return array of courses
	* @desc return all the courses this user has ever instructed
	*/
	function getAllCourses()
	{
		global $g_dbConn, $g_permission;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = 	"SELECT DISTINCT c.course_id "
				.		"FROM access as a "
				.		"  JOIN course_aliases as ca ON a.alias_id = ca.course_alias_id AND a.permission_level = " . $g_permission['instructor'] . " AND a.user_id = ! "
				.		"  JOIN courses as c ON c.course_id = ca.course_id "
				.		"  JOIN departments as d ON c.department_id = d.department_id "
				.		"  ORDER BY d.abbreviation, c.course_number "
				;
				
		}
		$rs = $g_dbConn->query($sql, $this->getUserID());			
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		$tmpArray = array();
		while($row = $rs->fetchRow())
		{		
			$tmpC = new course();
			$tmpC->getCourseByID($row[0]);
			$tmpArray[] = $tmpC;
		}		
		return $tmpArray;		
	}
	
	function getCourseInstancesByCourseID($courseID)
	{
		global $g_dbConn;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				/*
				$sql  = "SELECT ci.course_instance_id "
					  . "FROM  course_aliases as ca "
					  . "	JOIN course_instances as ci ON ca.course_instance_id = ci.course_instance_id "
					  . "WHERE ca.course_id = !"
					  ;
				*/
				$sql  = "SELECT ca.course_instance_id "
					  . "FROM  course_aliases as ca "
					  . "WHERE ca.course_id = !"
					  ;
		}

		$rs = $g_dbConn->query($sql, $courseID);			
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
			
		$tmpArray = array();
		while ($row = $rs->fetchRow())
			$tmpArray[] = new courseInstance($row[0]);
		
		return $tmpArray;
	}
	
	/**
	* @return courseInstance
	* @param courseInstance $oldCI
	* @param string $newTerm
	* @param string $newYear
	* @param date $newActivation
	* @param date $newExpiration
	* @param string $status
	* @param string $section
	* @desc creates a new courseInstance in the database with crosslistings and instructor from old courseInstance
	*/
	function copyCourseInstance($oldCI, $newTerm, $newYear, $newActivation, $newExpiration, $status="ACTIVE", $section="", $instructorList, $proxyList, $crossList, $reserveList)
	{
		global $g_dbConn, $g_permission;
				
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'			
				$sql_add_access			 = "INSERT INTO access (user_id, alias_id, permission_level) VALUES (!,!,!)";
		}
		
		
		//create new CI in db
		$newCI = new courseInstance();
		$newCI->createCourseInstance();
		
		//set new CI values	
		$newCI->setTerm($newTerm);
		$newCI->setYear($newYear);
		$newCI->setActivationDate($newActivation);
		$newCI->setExpirationDate($newExpiration);
		$newCI->setStatus($status);
		$newCI->setEnrollment($oldCI->getEnrollment());
		
		//set Primary Course Info
		$oldCI->getPrimaryCourse();			
		$newCI->setPrimaryCourse($oldCI->course->getCourseID(), $section);
	
		foreach($instructorList as $instr)
		{
			//set Access
			$rs = $g_dbConn->query($sql_add_access, array($instr, $newCI->primaryCourseAliasID, $g_permission['instructor']));							
			if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		}
			
		if(is_array($proxyList) && !empty($proxyList))
		{
			foreach($proxyList as $p)
			{
				$rs = $g_dbConn->query($sql_add_access, array($p, $newCI->primaryCourseAliasID, $g_permission['proxy']));			
				if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }			
			}
		}
		
		//copy crosslistings
		//if (is_array($crossListing) && !empty($crossListing))
		if (is_array($crossList) && !empty($crossList)) //added by kawashi 11.12.2004 - fixed apparent bug?
		{
			foreach($crossList as $xListing)
			{
				//Create new cross listing
				$newCI->addCrossListing($xListing->getCourseID(), $xListing->getSection());
				
				/* commented out by kawashi on 11.12.2004 - no longer adding proxies to cross listings
				//copy proxies for each crossListing
				foreach($proxyList as $p)
				{
					$rs = $g_dbConn->query($sql_add_access, array($p, $xListing->courseAliasID, $g_permission['proxy']));			
					if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }			
				}
				*/
				
				//This for loop added by kawashi on 11.12.2004 - apparent oversight?
				//copy proxies for each crossListing
				foreach($instructorList as $instr)
				{
					$rs = $g_dbConn->query($sql_add_access, array($instr, $xListing->courseAliasID, $g_permission['instructor']));			
					if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }			
				}
			}
		}
				
		//copy reserves
		if (is_array($reserveList) && !empty($reserveList))
		{
			foreach($reserveList as $srcReserve)
			{
				$rListing = new reserve($srcReserve);
				
				$r = new reserve();
				$r->createNewReserve($newCI->getCourseInstanceID(), $rListing->getItemID());
				$r->setActivationDate($newActivation);
				$r->setExpirationDate($newExpiration);
				$r->setSortOrder($rListing->getSortOrder());
				//$r->setStatus($rListing->getStatus()); //commented out by kawashi 12.1.2004 - replaced with logic below
				
				//When reactivating a class, the default status of the reserve should be "IN PROCESS" if physical item
				//and "ACTIVE" if an electronic item

				$rListing->getItem();				
				if ($rListing->item->getItemGroup() == 'MULTIMEDIA' || $rListing->item->getItemGroup() == 'MONOGRAPH') {
					$r->setStatus('IN PROCESS');
				} else {
					$r->setStatus('ACTIVE');
				}
				//End of changes made by kawashi on 12.1.2004
				
				$r->getItem();

				if ($r->item->getItemGroup() != 'ELECTRONIC')
				{
					$req = new request();
					$req->createNewRequest($newCI->getCourseInstanceID(), $r->getItemID());
					$req->setDateRequested(date('Y-m-d'));
					$req->setRequestingUser($instructorList[0]);
					$req->setReserveID($r->getReserveID());
					
					$r->setStatus("IN PROCESS");
				}
			}
		}
						
		return $newCI;
	}

}