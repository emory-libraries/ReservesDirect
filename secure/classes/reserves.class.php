<?
/*******************************************************************************
reserve.class.php
Reserve Primitive Object

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
require_once("secure/common.inc.php");
require_once("secure/classes/reserveItem.class.php");

class reserve
{
	//Attributes
	public $reserveID;
	public $courseInstanceID;
	public $itemID;
	public $item;
	public $activationDate;
	public $expirationDate;
	public $sortOrder;
	public $status;
	public $creationDate;
	public $lastModDate;
	public $notes = array();
	
	/**
	* @return reserve
	* @param int $reserveID
	* @desc initalize the reserve object
	*/
	function reserve($reserveID = NULL)
	{
		if (!is_null($reserveID)){			
			$this->getReserveByID($reserveID);	
		}				
	}
	
	/**
	* @return int reserveID
	* @desc create new reserve in database
	*/
	function createNewReserve($courseInstanceID, $itemID)
	{
		global $g_dbConn;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "INSERT INTO reserves (course_instance_id, item_id, date_created, last_modified) VALUES (!, !, ?, ?)";
				$sql2 = "SELECT LAST_INSERT_ID() FROM reserves";
		
				$d = date("Y-m-d"); //get current date
		}
		
		
		$rs = $g_dbConn->query($sql, array($courseInstanceID, $itemID, $d, $d));	
		if (DB::isError($rs)) 
		{ 
			
			if ($rs->getMessage() == 'DB Error: already exists')
			{ 
				return false;
			}
			else
				trigger_error($rs->getMessage(), E_USER_ERROR); 
		}
		
		$rs = $g_dbConn->query($sql2);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		$row = $rs->fetchRow();
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }	
		
		$this->reserveID = $row[0];	
		$this->creationDate = $d;
		$this->lastModDate = $d;
		
		$this->getReserveByID($this->reserveID);
		return true;
	}
	
	/**
	* @return void
	* @param int $reserveID
	* @desc get reserve info from the database
	*/
	function getReserveByID($reserveID)
	{
		global $g_dbConn;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT reserve_id, course_instance_id, item_id, activation_date, expiration, status, sort_order, date_created, last_modified "
					.  "FROM reserves "						  
					.  "WHERE reserve_id = !"
					;
		}
		
		$rs = $g_dbConn->query($sql, $reserveID);	
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		$row = $rs->fetchRow();
			$this->reserveID 		= $row[0];
			$this->courseInstanceID	= $row[1];
			$this->itemID			= $row[2];
			$this->activationDate	= $row[3];
			$this->expirationDate	= $row[4];
			$this->status	 		= $row[5];
			$this->sortOrder		= $row[6];
			$this->creationDate		= $row[7];
			$this->lastModDate		= $row[8];
	}

	/**
	* @return void
	* @param int $course_instance_id, int item_id
	* @desc get reserve info from the database by ci and item
	*/
	function getReserveByCI_Item($course_instance_id, $item_id)
	{
		global $g_dbConn;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT reserve_id, course_instance_id, item_id, activation_date, expiration, status, sort_order, date_created, last_modified "
					.  "FROM reserves "						  
					.  "WHERE course_instance_id = ! AND item_id = !"
					;
		}
		
		$rs = $g_dbConn->query($sql, array($course_instance_id, $item_id));	
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		$row = $rs->fetchRow();
			$this->reserveID 		= $row[0];
			$this->courseInstanceID	= $row[1];
			$this->itemID			= $row[2];
			$this->activationDate	= $row[3];
			$this->expirationDate	= $row[4];
			$this->status	 		= $row[5];
			$this->sortOrder		= $row[6];
			$this->creationDate		= $row[7];
			$this->lastModDate		= $row[8];
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
					.  "FROM reserves "						  
					.  "WHERE reserve_id = ! "
					.  "LIMIT 1"
					;
		}
		
		if (!is_null($this->reserveID))
		{
			$rs = $g_dbConn->query($sql, $this->reserveID);
			if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }		
		}
	}
	
	
	/**
	* @return void
	* @param date $activationDate
	* @desc set new activationDate in database
	*/
	function setActivationDate($date)
	{
		global $g_dbConn;

		$this->activationDate = $date;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE reserves SET activation_date = ?, last_modified = ? WHERE reserve_id = !";
				$d = date("Y-m-d"); //get current date
		}
		$rs = $g_dbConn->query($sql, array($date, $d, $this->reserveID));				

		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		$this->activationDate = $date;
		$this->lastModDate = $d;
	}
	
	/**
	* @return void
	* @param date $expirationDate
	* @desc set new expirationDate in database
	*/
	function setExpirationDate($date)
	{
		global $g_dbConn;

		$this->expirationDate = $date;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE reserves SET expiration = ?, last_modified = ? WHERE reserve_id = !";
				$d = date("Y-m-d"); //get current date
		}
		$rs = $g_dbConn->query($sql, array($date, $d, $this->reserveID));		
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		$this->expirationDate = $date;
		$this->lastModDate = $d;
	}
	
		/**
	* @return void
	* @param string $status
	* @desc set new status in database
	*/
	function setStatus($status)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE reserves SET status = ?, last_modified = ? WHERE reserve_id = !";
				$d = date("Y-m-d"); //get current date
		}
		$rs = $g_dbConn->query($sql, array($status, $d, $this->reserveID));		
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		$this->status = $status;
		$this->lastModDate = $d;
	}
	
		/**
	* @return void
	* @param int $sortOrder
	* @desc set new sortOrder in database
	*/
	function setSortOrder($sortOrder)
	{
		global $g_dbConn;

		$this->sortOrder = $sortOrder;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE reserves SET sort_order = !, last_modified = ? WHERE reserve_id = !";
				$d = date("Y-m-d"); //get current date
		}
		$rs = $g_dbConn->query($sql, array($sortOrder, $d, $this->reserveID));		
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		$this->sortOrder = $sortOrder;
		$this->lastModDate = $d;
	}
	
	/**
	* @return void
	* @param int $userID
	* @desc log the users reserve view
	*/
	function addUserView($userID)
	{
		global $g_dbConn;

		$this->privateUserID = $privateUserIDID; 
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "INSERT INTO user_view_log (user_id, reserve_id, timestamp_viewed) VALUES (!, !, CURRENT_TIMESTAMP)";
		}
		$rs = $g_dbConn->query($sql, array($userID, $this->reserveID));		
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
	}
	
	/**
	* @return void
	* @desc Retrieve the item object associated with this reserve
	*/
	function getItem()
	{
		$this->item = new reserveItem($this->itemID);
	}

	function getReserveID(){ return $this->reserveID; }
	function getCourseInstanceID() { return $this->courseInstanceID; }
	function getItemID() { return $this->itemID; }
	function getActivationDate() { return $this->activationDate; }
	function getExpirationDate() { return $this->expirationDate; }
	function getStatus() { return $this->status;}
	function getSortOrder() { return $this->sortOrder; }
	function getCreationDate() { return $this->creationDate; }
	function getModificationDate() { return $this->lastModDate; }
	
	function getNotes()
	{
		//$this->notes = common_getNotesByTarget("reserves", $this->reserveID);
		return $this->notes;
	}
	
	function setNote($type, $text)
	{
		$this->notes[] = common_setNote($noteID=null, $type, $text, "reserves", $this->reserveID);
	}
	
	/**
	* @return boolean
	* @desc tests associated item if item is a heading returns true false otherwise 
	*/
	function isHeading()
	{
		if (is_a($this->item, "reserveItem")) return false;  //reserveItems are not headings
		else return true;
	}
	
}
?>