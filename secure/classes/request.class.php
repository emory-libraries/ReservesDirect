<?
/*******************************************************************************
request.class.php
request Primitive Object

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

require_once("secure/classes/item.class.php");
require_once('secure/classes/notes.class.php');
require_once("secure/classes/user.class.php");

class request extends Notes
{
	//Attributes
	public $requestID;
	public $reserveID;
	public $requestedDate;
	public $processedDate;
	public $requestedItemID;
	public $requestedItem;
	public $requestingUserID;
	public $requestingUser;
	public $reserve;
	public $desiredDate;
	public $priority;
	public $courseInstanceID;
	public $maxEnrollment;
	public $courseInstance;
	public $type;
	public $holdings = array();
	
	private $_ils;

	/**
	* @return request
	* @param int $requestID;
	* @desc initalize the request object
	*/
	function request($requestID = NULL)
	{
		if (!is_null($requestID)){
			$this->getRequestByID($requestID);
		}
		
		$this->_ils = RD_Ils::initILS();
	}

	/**
	* @return int requestID
	* @param int $courseInstanceID;
	* @desc create new request in database
	*/
	function createNewRequest($courseInstanceID, $itemID)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "INSERT INTO requests (course_instance_id, item_id, date_requested) VALUES (!,!,?)";
				$sql2 = "SELECT LAST_INSERT_ID() FROM requests";

				$d = date("Y-m-d");
		}

		$rs = $g_dbConn->query($sql, array($courseInstanceID, $itemID, $d));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$rs = $g_dbConn->query($sql2);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$row = $rs->fetchRow();
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$this->requestID = $row[0];

		$this->getRequestByID($this->requestID);  // get values from DB
	}

	/**
	* @return boolean TRUE on success; FALSE on failure
	* @param id $requestID
	* @desc get data by id
	*/
	function getRequestByID($requestID)
	{
		global $g_dbConn, $g_notetype;

		if(empty($requestID)) {
			return false;
		}
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT request_id, reserve_id, item_id, user_id, date_requested, date_processed, date_desired, priority, course_instance_id, max_enrollment, type "
					.  "FROM requests "
					.  "WHERE request_id = !"
					;
		}

		$rs = $g_dbConn->query($sql, $requestID);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		if($rs->numRows()==0) {
			return false;
		}
		else {
			list($this->requestID, $this->reserveID, $this->requestedItemID, $this->requestingUserID, $this->requestedDate, $this->processedDate, $this->desiredDate, $this->priority, $this->courseInstanceID, $this->maxEnrollment, $this->type) = $rs->fetchRow();			
		}
		
		//get instructor notes
		$this->setupNotes('requests', $this->requestID, $g_notetype['instructor']);
		$this->fetchNotesByType();
		
		//get copyright notes
		$this->setupNotes('requests', $this->requestID, $g_notetype['staff']);
		$this->fetchNotesByType();
		return true;
	}

	/**
	* @return requestID on success or null on failure
	* @param id $reserveID
	* @desc get data by id
	*/
	function getRequestByReserveID($reserveID)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT request_id "
					.  "FROM requests "
					.  "WHERE reserve_id = !"
					;
		}

		$rs = $g_dbConn->getRow($sql, array($reserveID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		if(empty($rs)) {
			return false;
		}
		else {
			$row = $rs->fetchRow();
			$this->requestID = $row[0];
			$this->getRequestByID($this->requestID);  // get values from DB
		}
	}


	/**
	 * Initializes request object, based on CI ID and Item ID
	 * 
	 * @param int $ci_id CI ID
	 * @param int $item_id Item ID
	 * @return boolean TRUE on success, FALSE otherwise
	 */
	function getRequestByCI_Item($ci_id, $item_id) {
		global $g_dbConn;

		if(empty($ci_id) || empty($item_id)) {
			return false;
		}

		$sql = "SELECT request_id FROM requests WHERE course_instance_id=? AND item_id=?";
		$rs = $g_dbConn->getOne($sql, array($ci_id, $item_id));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		if(empty($rs)) {
			return false;
		}
		else {
			return $this->getRequestByID($rs);
		}
	}
	
	
	/**
	 * Retrieves all requests matching the item ID and returns as array of request objects
	 *
	 * @param int $item_id Item ID to match
	 * @return array
	 */
	function getRequestsByItem($item_id) {
		global $g_dbConn;
		
		if(empty($item_id)) {
			return array();
		}
		
		$sql = "SELECT request_id FROM requests WHERE item_id = ? AND date_processed IS NULL";
		$rs = $g_dbConn->query($sql, $item_id);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		$requests = array();
		while($row = $rs->fetchRow()) {
			$requests[] = new request($row[0]);
		}
		
		return $requests;
	}
	
	
	function getHoldings()
	{
		$item = new reserveItem($this->requestedItemID);
				
		$this->holdings = $this->_ils->getHoldings($item->getLocalControlKey(), 'control');
	}

	function destroy()
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "DELETE "
					.  "FROM requests "
					.  "WHERE request_id = !"
					;
		}

		if (!is_null($this->requestID))
		{
			$rs = $g_dbConn->query($sql, $this->requestID);
			if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		}
	}

	/**
	* @return void
	* @param $dateRequested
	* @desc set the request date
	*/
	function setDateRequested($date)
	{
		global $g_dbConn;

		$this->requestedDate = $date;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE requests SET date_requested = ? WHERE request_id = !";
		}

		$rs = $g_dbConn->query($sql, array($date, $this->requestID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	/**
	* @return void
	* @param $dateProcessed
	* @desc set the processed date
	*/
	function setDateProcessed($date)
	{
		global $g_dbConn;

		$this->processedDate = $date;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE requests SET date_processed = ? WHERE request_id = !";
		}

		$rs = $g_dbConn->query($sql, array($date, $this->requestID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	/**
	* @return void
	* @param $itemID
	* @desc set the request item
	*/
	function setRequestedItemID($itemID)
	{
		global $g_dbConn;

		$this->requestedItemID = $itemID;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE requests SET item_id = ? WHERE request_id = !";
		}

		$rs = $g_dbConn->query($sql, array($itemID, $this->requestID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	/**
	* @return void
	* @param int $reserveID
	* @desc set the reserveID
	*/
	function setReserveID($reserveID)
	{
		global $g_dbConn;

		$this->reserveID = $reserveID;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE requests SET reserve_id = ! WHERE request_id = !";
		}

		$rs = $g_dbConn->query($sql, array($reserveID, $this->requestID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	/**
	* @return void
	* @param $userID
	* @desc set the requesting User
	*/
	function setRequestingUser($userID)
	{
		global $g_dbConn;

		$this->requestingUserID = $userID;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE requests SET user_id = ? WHERE request_id = !";
		}

		$rs = $g_dbConn->query($sql, array($userID, $this->requestID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	/**
	* @return void
	* @param $desiredDate
	* @desc set the desired date
	*/
	function setDateDesired($date)
	{
		global $g_dbConn;

		$this->desiredDate = $date;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE requests SET date_desired = ? WHERE request_id = !";
		}

		$rs = $g_dbConn->query($sql, array($date, $this->requestID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	/**
	* @return void
	* @param $priority
	* @desc set the priority
	*/
	function setPriority($priority)
	{
		global $g_dbConn;

		$this->priority = $priority;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE requests SET priority = ? WHERE request_id = !";
		}

		$rs = $g_dbConn->query($sql, array($priority, $this->requestID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}
	
	/**
	* @return void
	* @param $max_enrollment
	* @desc set the max enrollment
	*/
	function setMaxEnrollment($max_enrollment)
	{
		global $g_dbConn;		

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE requests SET max_enrollment = ! WHERE request_id = !";
		}

		$rs = $g_dbConn->query($sql, array($max_enrollment, $this->requestID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		$this->maxEnrollment = $max_enrollment;
	}	

	/**
	* @return void
	* @param $type
	* @desc set the request type
	*/
	function setType($type)
	{
		global $g_dbConn;		

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE requests SET type = ? WHERE request_id = !";
		}

		$rs = $g_dbConn->query($sql, array($type, $this->requestID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		$this->type = $type;
	}		

	/**
	* @return boolean
	* @param $status 
	* @desc updates status
	*/	
	public function setStatus($status)
	{		
		//we'll store the request status in the reserve table
		//this will allow seemless display to instructors/staff
		if (!isset($this->reserve))
			$this->getReserve();
		
		$this->reserve->setStatus($status);
		
		return $this->reserve->getStatus() == $status;	
	}
	
	public function getStatus()
	{
		if (!isset($this->reserve))
			$this->getReserve();
			
		return $this->reserve->getStatus();				
	}
	
	function getRequestID() { return $this->requestID; }
	function getReserveID()		{ return $this->reserveID; }
	function getDateRequested() { return $this->requestedDate; }
	function getProcessedDate() { return $this->processedDate; }
	function getDesiredDate() { return $this->desiredDate; }
	function getRequestedItem()  { $this->requestedItem = new reserveItem($this->requestedItemID); }
	function getRequestingUser() { $this->requestingUser = new user($this->requestingUserID); }
	function getCourseInstance() { 
		$this->courseInstance = new courseInstance($this->courseInstanceID); 
		return $this->courseInstance;
	}
	function getCourseInstanceID() { return $this->courseInstanceID; }
	function getReserve(){ $this->reserve = new reserve($this->reserveID); }
	function getPrority() { $this->priority; }
	function getMaxEnrollment() { $this->maxEnrollment; }
	function getType() { return $this->type; }
}
?>