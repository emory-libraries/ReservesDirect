<?
/*******************************************************************************
courseInstance.class.php
Course Instance Primitive Object

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

require_once("classes/course.class.php");
require_once("classes/department.class.php");
require_once("classes/note.class.php");
require_once("classes/reserveItem.class.php");
require_once("classes/reserves.class.php");

class courseInstance
{
	//Attributes

	public $courseInstanceID;
	public $crossListings = array();			//array of courses
	public $course;						//single course
 	public $courseList = array();	//array of All courses associated with a course instance - note this publiciable was added by kawashi 11.5.2004
	public $reserveList = array();			//array of reserves
	//public $studentList = array();
	//public $proxyList = array();
	public $instructorList = array();		//array of users
	public $instructorIDs = array(); 		//array of instructor userIDs
	public $primaryCourseAliasID;
	public $term;
	public $year;
	public $activationDate;
	public $expirationDate;
	public $status;
	public $enrollment;
	public $notes;
	public $proxies = array();
	public $proxyIDs = array();
	//public $aliasID;

	
	function courseInstance($courseInstanceID = NULL)
	{		
		if (!is_null($courseInstanceID))
			$this->getCourseInstance($courseInstanceID);
		
	}
		
	function createCourseInstance()
	{
		global $g_dbConn;
		
		
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql  				= "INSERT INTO course_instances () VALUES ()";
				$sql_inserted_ci 	= "SELECT LAST_INSERT_ID() FROM course_instances";
		}

		$rs = $g_dbConn->query($sql);		
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		$rs = $g_dbConn->query($sql_inserted_ci);		
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		$row = $rs->fetchRow();
		$this->courseInstanceID = $row[0];
	}
	
	private function getCourseInstance($courseInstanceID)
	{
		global $g_dbConn;
		
		$this->courseInstanceID = $courseInstanceID;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql  = "SELECT ci.primary_course_alias_id, ci.term, ci.year, ci.activation_date, ci.expiration_date, ci.status, ci.enrollment "
					  //. "FROM course_instances as ci LEFT JOIN course_aliases as ca ON ci.course_instance_id = ca.course_instance_id "
					  . "FROM course_instances as ci "
					  . "WHERE ci.course_instance_id = !";
					 
		}

		$rs = $g_dbConn->query($sql, $this->courseInstanceID);			
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
			
		$row = $rs->fetchRow();		
			$this->primaryCourseAliasID	= $row[0];
			$this->term					= $row[1];
			$this->year					= $row[2];
			$this->activationDate		= $row[3];
			$this->expirationDate		= $row[4];
			$this->status				= $row[5];
			$this->enrollment			= $row[6];
	}
	
	function getCrossListings()
	{
		global $g_dbConn;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				//$sql  = "SELECT ca.course_id "
				$sql  = "SELECT ca.course_alias_id "
					  . "FROM course_aliases ca "
//					  . "LEFT JOIN course_instances AS ci ON ca.course_instance_id = ci.course_instance_id "					  
					  . "WHERE ca.course_instance_id = ! "
					  . "AND ca.course_alias_id <> !"; //ca.course_alias_id != ci.primary_course_alias_id
		}

		$rs = $g_dbConn->query($sql, array($this->courseInstanceID, $this->getPrimaryCourseAliasID()));		
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		unset($crossListings);	
		$crossListings = array();
		while ($row = $rs->fetchRow()) {		
			$this->crossListings[] = new course($row[0]);
		}	
	}
	
	function addCrossListing($courseID, $section="")
	{
		global $g_dbConn;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql_cpy_listing = "INSERT INTO course_aliases (course_instance_id, course_id, section) VALUES (!,!,?)";
		}

		$rs = $g_dbConn->query($sql_cpy_listing, array($this->courseInstanceID, $courseID, $section));	
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }		
		
		$this->getCrossListings();
	}
	
	function getProxies()
	{
		global $g_dbConn;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql  = "SELECT DISTINCT u.username, u.user_id "
					  . "FROM users u "
					  .	"LEFT JOIN access AS a ON a.user_id = u.user_id "
					  . "LEFT JOIN course_aliases AS ca ON ca.course_alias_id = a.alias_id "					  
					  . "WHERE ca.course_instance_id = ! "
					  . "AND a.permission_level = 2";
		}

		$rs = $g_dbConn->query($sql, $this->courseInstanceID);	

		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		unset($proxies);	
		$proxies = array();
		while ($row = $rs->fetchRow()) {		
			$this->proxies[] = new proxy($row[0]);
			$this->proxyIDs[] = $row[1];
		}	
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
					.  "FROM course_instances "						  
					.  "WHERE course_instance_id = !"
					;
		}
		
		$rs = $g_dbConn->query($sql, $requestID);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }		
	}
	
	/**
	* @return void
	* @desc getAddedCourses from DB
	*/
	function getCourses()
	{
		global $g_dbConn;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				//$sql  = "SELECT course_id FROM course_aliases WHERE course_instance_id = !";					 
				$sql  = "SELECT course_alias_id FROM course_aliases WHERE course_instance_id = !";					 
		}
		$rs = $g_dbConn->query($sql, $this->courseInstanceID);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		while ($row = $rs->fetchRow()) {		
			$this->courseList[] = new course($row[0]);
		}	
	}
	
	function getPrimaryCourse()
	{
		$this->course = new course($this->primaryCourseAliasID);
	}
	
	function getCourseForUser($userID)
	//function getCourseForUser($userID,$aliasID=null)
	{
		
		global $g_dbConn;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				
				$sql  = "SELECT DISTINCT a.alias_id "
				.  		"FROM access as a "
				.  		"  LEFT JOIN course_aliases as ca on a.alias_id = ca.course_alias_id AND a.user_id = ! "
				.	    "WHERE ca.course_instance_id = !"
				;										 
		}
		
		
		$rs = $g_dbConn->query($sql, array($userID, $this->courseInstanceID));	
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
			
		while ($row = $rs->fetchRow()) {	
			$this->course = new course($row[0]);
		}
		
		/*
		if ((!$aliasID) && (!$this->aliasID)) {
			$rs = $g_dbConn->query($sql, array($userID, $this->courseInstanceID));	
			if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
			
			while ($row = $rs->fetchRow()) {	
				$this->course = new course($row[0]);
				//$this->courseList[] = new course($row[0]);
			}
		} elseif (!$aliasID) {
			$this->course = new course($this->aliasID);
		} else {
			$this->course = new course($aliasID);
		}
		*/
	}	

	//New Method - Get Courses For Instructor
	function getCoursesForInstructor($userID)
	{
		global $g_dbConn;
	
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql  = "SELECT DISTINCT a.alias_id "
				.  		"FROM access as a "
				.  		"  LEFT JOIN course_aliases as ca on a.alias_id = ca.course_alias_id AND a.user_id = ! "
				.	    "WHERE ca.course_instance_id = !"
				;										 
		}
		
		$rs = $g_dbConn->query($sql, array($userID, $this->courseInstanceID));	
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		while ($row = $rs->fetchRow()) {	
			$this->courseList[] = new course($row[0]);
		}
	}	
	
	/* Commented out by kawashi on 11.2.2004 - Not Needed; Serves same purpose as getPrimaryCourse()
	function getCourseForInstructor($userID)  // WHY NOT USE getPrimaryCourse?  jbwhite
	{
		global $g_dbConn;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				
				$sql  = "SELECT ca.course_alias_id "
				.  		"FROM course_aliases as ca "
				.  		"  JOIN access as a on a.alias_id = ca.course_alias_id AND a.user_id = ! "
				
				//removed by jbwhite this appears to attempt to be non functional
				//Added back by kawashi, this should stay if this method is implemented; however, since we have
				//getPrimaryCourse(), I'm commenting out this entire method - kawashi 11.2.2004
				.	    "WHERE ca.course_instance_id = ! "  
				.		"AND a.alias_id = !" //a.alias_id = $ci->getPrimaryCourseAliasID()
				;					 					 
		}
		
		//$rs = $g_dbConn->query($sql, array($userID));//, $this->courseInstanceID, $this->getPrimaryCourseAliasID()));					
		$rs = $g_dbConn->query($sql, array($userID, $this->courseInstanceID, $this->getPrimaryCourseAliasID()));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		while ($row = $rs->fetchRow()) {	
			$this->course = new course($row[0]);
		}	
	}	
	*/
	
	function getPermissionForUser($userID)
	{
		global $g_dbConn;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql  = "SELECT a.permission_level, nt.permission_level "
				.  		"FROM course_aliases as ca "
				.  		"  LEFT JOIN access as a on a.alias_id = ca.course_alias_id "
				.  		"  LEFT JOIN not_trained as nt on nt.user_id = a.user_id "
				.	    "WHERE ca.course_instance_id = ! AND a.user_id = ! "
				.		"ORDER BY a.permission_level DESC "
				;					 
		}
		
		$rs = $g_dbConn->query($sql, array($this->courseInstanceID, $userID));		
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$row = $rs->fetchRow();
		return (is_null($row[1]) ? $row[0] : $row[1]);
	}	

	function setPrimaryCourse($courseID, $section="")
	{ 

		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql_primary_listing	 = "INSERT INTO course_aliases (course_id, course_instance_id, section) VALUES (!,!,?)";
				$sql_inserted_listing	 = "SELECT LAST_INSERT_ID() FROM course_aliases";
				$sql 					 = "UPDATE course_instances SET primary_course_alias_id = ! WHERE course_instance_id = !";
		}
	
		$rs = $g_dbConn->query($sql_primary_listing, array($courseID, $this->courseInstanceID, $section));	
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		$rs = $g_dbConn->query($sql_inserted_listing);		
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		$row = $rs->fetchRow();
		$this->primaryCourseAliasID = $row[0];
		
		$rs = $g_dbConn->query($sql, array($this->getPrimaryCourseAliasID(), $this->getCourseInstanceID()));		
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}
	
	
	function setPrimaryCourseAliasID($primaryCourseAliasID) 
	{ 
		global $g_dbConn;

		$this->primaryCourseAliasID = $primaryCourseAliasID; 
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE course_instances SET primary_course_alias_id = ! WHERE course_instance_id = !";
		}
		$rs = $g_dbConn->query($sql, array($primaryCourseAliasID, $this->courseInstanceID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}
	
	function setTerm($term) 
	{ 
		global $g_dbConn;

		$this->term = $term; 
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE course_instances SET term = ? WHERE course_instance_id = !";
		}
		$rs = $g_dbConn->query($sql, array($term, $this->courseInstanceID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	function setYear($year) 
	{ 
		global $g_dbConn;

		$this->Year = $year; 
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE course_instances SET year = ? WHERE course_instance_id = !";
		}
		$rs = $g_dbConn->query($sql, array($year, $this->courseInstanceID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	function setActivationDate($activationDate) 
	{ 
		global $g_dbConn;

		$this->activationDate = $activationDate; 
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE course_instances SET activation_date = ? WHERE course_instance_id = !";
		}
		$rs = $g_dbConn->query($sql, array($activationDate, $this->courseInstanceID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}	

	function setExpirationDate($expirationDate) 
	{ 
		global $g_dbConn;

		$this->expirationDate = $expirationDate; 
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE course_instances SET expiration_date = ? WHERE course_instance_id = !";
		}
		$rs = $g_dbConn->query($sql, array($expirationDate, $this->courseInstanceID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}		
	
	function setStatus($status) 
	{ 
		global $g_dbConn;

		$this->status = $status; 
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE course_instances SET status = ? WHERE course_instance_id = !";
		}
		$rs = $g_dbConn->query($sql, array($status, $this->courseInstanceID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}		
	
	function setEnrollment($enrollment) 
	{ 
		global $g_dbConn;

		$this->enrollment = $enrollment;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE course_instances SET enrollment = ? WHERE course_instance_id = !";
		}
		$rs = $g_dbConn->query($sql, array($enrollment, $this->courseInstanceID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	/**
	* @return void
	* @desc load reservseList from DB
	*/
	function getReserves($sortBy=null)
	{
		global $g_dbConn;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT r.reserve_id, r.sort_order, i.title "
					.  "FROM reserves as r "
					.  "  JOIN items as i ON r.item_id = i.item_id  "							  
					.  "WHERE course_instance_id = ! ORDER BY r.sort_order, i.title";
				$sql_author = "SELECT r.reserve_id, i.author, i.title "
					 .  "FROM reserves as r "
					 .  "  JOIN items as i ON r.item_id = i.item_id  "							  
					 .  "WHERE course_instance_id = ! "
					 .  "ORDER BY i.author, i.title"
					 ;	
				$sql_title = "SELECT r.reserve_id, i.title, i.author "
					 .  "FROM reserves as r "
					 .  "  JOIN items as i ON r.item_id = i.item_id  "							  
					 .  "WHERE course_instance_id = ! "
					 .  "ORDER BY i.title, i.author"
					 ;	
		}
		
		if (!$sortBy) {
			$rs = $g_dbConn->query($sql, $this->courseInstanceID);		
			if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		} elseif ($sortBy=='author') {
			$rs = $g_dbConn->query($sql_author, $this->courseInstanceID);		
			if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		} elseif ($sortBy=='title') {
			$rs = $g_dbConn->query($sql_title, $this->courseInstanceID);		
			if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		}
		
		$this->reserveList = array();
		while ($row = $rs->fetchRow()) {
			$this->reserveList[] = new reserve($row[0]);
		}
	}
	
	/**
	* @return void
	* @desc load reservseList from DB
	*/
	function getActiveReserves($sortBy=null)
	{
		global $g_dbConn;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT r.reserve_id, r.sort_order, i.title "
					.  "FROM reserves as r "
					.  "  JOIN items as i ON r.item_id = i.item_id  "							  
					.  "WHERE course_instance_id = ! "
					.  "AND r.status='ACTIVE' AND r.activation_date <= ? AND ? <= r.expiration "
					.  "ORDER BY r.sort_order, i.title"
					;
				$sql_author = "SELECT r.reserve_id, i.author, i.title "
					 .  "FROM reserves as r "
					 .  "  JOIN items as i ON r.item_id = i.item_id  "							  
					 .  "WHERE course_instance_id = ! "
					 .  "AND r.status='ACTIVE' AND r.activation_date <= ? AND ? <= r.expiration "
					 .  "ORDER BY i.author, i.title"
					 ;	
				$sql_title = "SELECT r.reserve_id, i.title, i.author "
					 .  "FROM reserves as r "
					 .  "  JOIN items as i ON r.item_id = i.item_id  "							  
					 .  "WHERE course_instance_id = ! "
					 .  "AND r.status='ACTIVE' AND r.activation_date <= ? AND ? <= r.expiration "
					 .  "ORDER BY i.title, i.author"
					 ;	
					
				$d = date("Y-m-d"); //get current date
		}
		
		if (!$sortBy) {
			$rs = $g_dbConn->query($sql, array($this->courseInstanceID, $d, $d));		
			if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		} elseif ($sortBy=='author') {
			$rs = $g_dbConn->query($sql_author, array($this->courseInstanceID, $d, $d));		
			if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		} elseif ($sortBy=='title') {
			$rs = $g_dbConn->query($sql_title, array($this->courseInstanceID, $d, $d));		
			if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		}

		$this->reserveList = array();
		while ($row = $rs->fetchRow()) {
			$this->reserveList[] = new reserve($row[0]);
		}		
	}
	
	function updateSortOrder($sortType=null)
	{
		$this->getReserves($sortType);
		
		for($i=0; $i<count($this->reserveList); $i++)
		{
			$this->reserveList[$i]->setSortOrder($i+1);
		}
	}
	
	/**
	* @return void
	* @desc load instructorList from DB
	*/
	function getInstructors()
	{ 
		global $g_dbConn;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT DISTINCT a.user_id "
				.	   "FROM access as a "
				.	   "LEFT JOIN course_aliases as ca on ca.course_alias_id = a.alias_id "
				.	   "WHERE ca.course_instance_id = ! AND a.permission_level = 3" //3 = instructor	
				;
		}

		$rs = $g_dbConn->query($sql, array($this->courseInstanceID));		
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }	
		
		while ($row = $rs->fetchRow()) {
			$tmpI = new instructor();
				$tmpI->getUserByID($row[0]);
			$this->instructorList[] = $tmpI;
			$this->instructorIDs[] = $row[0];
		}
	}
	
	function addInstructor($courseAliasID, $instructorID)
	{
		global $g_dbConn;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT access_id from access WHERE user_id = ! AND alias_id = ! and permission_level = 3";
				$sql2 = "INSERT INTO access (user_id, alias_id, permission_level) VALUES (!, !, !)";
		}
		
		$rs = $g_dbConn->query($sql, array($instructorID, $courseAliasID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		if ($rs->numRows() == 0) {
			$rs = $g_dbConn->query($sql2, array($instructorID, $courseAliasID, '3'));		
			if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }	
		}
	}
	
	
	function addProxy($courseAliasID, $proxyID)
	{
		global $g_dbConn;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT access_id from access WHERE user_id = ! AND alias_id = ! and permission_level = 2";
				$sql2 = "INSERT INTO access (user_id, alias_id, permission_level) VALUES (!, !, !)";
		}
		
		$rs = $g_dbConn->query($sql, array($proxyID, $courseAliasID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		if ($rs->numRows() == 0) {
			$rs = $g_dbConn->query($sql2, array($proxyID, $courseAliasID, '2'));		
			if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }	
		}
	}
	
	
	function removeInstructor($courseAliasID, $instructorID)
	{
		global $g_dbConn;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "DELETE FROM access WHERE user_id = ! AND alias_id = ! and permission_level = 3 LIMIT 1";
		}
		
		$rs = $g_dbConn->query($sql, array($instructorID, $courseAliasID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}
	
	function displayInstructors()
	{
		$retValue = "";
		for($i=0;$i<count($this->instructorList);$i++)
		{
			$retValue .=  $this->instructorList[$i]->getName() . " ";
		}
		return ($retValue == "" ? "None" : $retValue);
	}
	
	function displayCrossListings()
	{
		$retValue = "";
		for($i=0;$i<count($this->crossListings);$i++)
		{
			$retValue .=  $this->crossListings[$i]->getName() . " ";
		}
		return ($retValue == "" ? "CrossListing None" : $retValue);
	}
	
	function displayInstructorList()
	{
		$retString = "";
		for($i=0;$i<count($this->instructorList);$i++)
		{
			//if (is_a($this->instructorList[$i], "user"))
			if ($this->instructorList[$i] instanceof user)
				$retString .= $this->instructorList[$i]->getName() . " ";
		}
		return $retString;
	}
	
	
	function getCourseInstanceID() { return $this->courseInstanceID; }
	function getPrimaryCourseAliasID() {return $this->primaryCourseAliasID;}
	function getTerm() { return $this->term; }
	function getYear() { return $this->year; }
	function displayTerm() { return $this->term . " " . $this->year; }
	function getActivationDate() { return $this->activationDate; }
	function getExpirationDate() { return $this->expirationDate; }
	function getStatus() { return $this->status; }
	function getEnrollment() { return $this->enrollment; }

	function getNotes() { $this->notes = getNotesByTarget("course_instances", $this->courseInstanceID); }
	function setNote($type, $text) { $this->notes[] = common_setNote($type, $text, "course_instances", $this->courseInstanceID); }
}
?>