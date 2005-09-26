<?
/*******************************************************************************
request.class.php
request Primitive Object

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

require_once("secure/classes/item.class.php");
require_once("secure/classes/user.class.php");
require_once("secure/classes/zQuery.class.php");

class request
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
	public $courseInstance;
	public $notes = array();
	public $holdings = array();
	
	private $zQry;

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
		$this->zQry = new zQuery(null);
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
	* @return void
	* @param id $requestID
	* @desc get data by id
	*/
	function getRequestByID($requestID)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT request_id, reserve_id, item_id, user_id, date_requested, date_processed, date_desired, priority, course_instance_id "
					.  "FROM requests "
					.  "WHERE request_id = !"
					;
		}

		$rs = $g_dbConn->query($sql, $requestID);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		list($this->requestID, $this->reserveID, $this->requestedItemID, $this->requestingUserID, $this->requestedDate, $this->processedDate, $this->desiredDate, $this->priority, $this->courseInstanceID) = $rs->fetchRow();
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
				$sql = "SELECT request_id, reserve_id, item_id, user_id, date_requested, date_processed, date_desired, priority, course_instance_id "
					.  "FROM requests "
					.  "WHERE reserve_id = !"
					;
		}

		$rs = $g_dbConn->query($sql, $reserveID);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		if( ($row = $rs->fetchRow()) != null ) {
			list($this->requestID, $this->reserveID, $this->requestedItemID, $this->requestingUserID, $this->requestedDate, $this->processedDate, $this->desiredDate, $this->priority, $this->courseInstanceID) = $rs->fetchRow();
			
			return $this->requestID;
		}
		else
			return null;
	}
	
	function getHoldings()
	{
		$item = new reserveItem($this->requestedItemID);
				
		$this->holdings = $this->zQry->getHoldings('control', $item->getLocalControlKey());
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
	
	function getReserveID()		{ return $this->reserveID; }
	function getDateRequested() { return $this->requestedDate; }
	function getProcessedDate() { return $this->processedDate; }
	function getDesiredDate() { return $this->desiredDate; }
	function getRequestedItem()  { $this->requestedItem = new reserveItem($this->requestedItemID); }
	function getRequestingUser() { $this->requestingUser = new user($this->requestingUserID); }
	function getCourseInstance() { $this->courseInstance = new courseInstance($this->courseInstanceID); }
	function getReserve(){ $this->reserve = new reserve($this->reserveID); }
	function getPrority() { $this->priority; }

	function getNotes() { $this->notes = getNotesByTarget("requests", $this->requestID); }
	function setNote($type, $text) { $this->notes[] = common_setNote($type, $text, "requests", $this->requestID); }
}
?>
