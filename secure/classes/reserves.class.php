<?
/*******************************************************************************
reserve.class.php
Reserve Primitive Object

Created by Jason White (jbwhite@emory.edu)

This file is part of GNU ReservesDirect 2.1

Copyright (c) 2004-2005 Emory University, Atlanta, Georgia.

ReservesDirect 2.1 is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

ReservesDirect 2.1 is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with ReservesDirect 2.1; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

Reserves Direct 2.1 is located at:
http://www.reservesdirect.org/

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
	public $hidden = false;
	public $requested_loan_period;
	
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
				$sql = "SELECT reserve_id, course_instance_id, item_id, activation_date, expiration, status, sort_order, date_created, last_modified, n.note_id, requested_loan_period "
					.  "FROM reserves as r "
					.  "LEFT JOIN notes as n ON n.target_table='reserves' and r.reserve_id = n.target_id "
					.  "WHERE reserve_id = ! "
					.  "ORDER BY n.type, n.note_id"
					;
		}

		$rs = $g_dbConn->query($sql, $reserveID);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$row = $rs->fetchRow();
			$this->reserveID 			 = $row[0];
			$this->courseInstanceID		 = $row[1];
			$this->itemID				 = $row[2];
			$this->activationDate		 = $row[3];
			$this->expirationDate		 = $row[4];
			$this->status	 			 = $row[5];
			$this->sortOrder			 = $row[6];
			$this->creationDate			 = $row[7];
			$this->lastModDate			 = $row[8];
			$this->requested_loan_period = $row[10];


			if (!is_null($row[9]))
				$this->notes[] = new note($row[9]);

			while ($row = $rs->fetchRow()) //get additional notes
				if (!is_null($row[9]))
					$this->notes[] = new note($row[9]);
	}

	/**
	* @return itemID on success or null on failure
	* @param int $course_instance_id, int item_id
	* @desc get reserve info from the database by ci and item
	*/
	function getReserveByCI_Item($course_instance_id, $item_id)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT reserve_id, course_instance_id, item_id, activation_date, expiration, status, sort_order, date_created, last_modified, requested_loan_period "
					.  "FROM reserves "
					.  "WHERE course_instance_id = ! AND item_id = !"
					;
		}

		$rs = $g_dbConn->query($sql, array($course_instance_id, $item_id));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$row = $rs->fetchRow();

		if($row==null) {	//no reserve found
			return null;
		}
		else {
			$this->reserveID 		= $row[0];
			$this->courseInstanceID	= $row[1];
			$this->itemID			= $row[2];
			$this->activationDate	= $row[3];
			$this->expirationDate	= $row[4];
			$this->status	 		= $row[5];
			$this->sortOrder		= $row[6];
			$this->creationDate		= $row[7];
			$this->lastModDate		= $row[8];
			$this->requested_loan_period = $row[9];

			return $this->reserveID;
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

	function setRequestedLoanPeriod($lp)
	{
		global $g_dbConn;

		$this->requested_loan_period = $lp;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE reserves SET requested_loan_period = ? WHERE reserve_id = !";
		}

		$rs = $g_dbConn->query($sql, array($lp, $this->reserveID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }		
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
	
	function getRequestedLoanPeriod() 
	{
		if (!is_null($this->requested_loan_period))
			return $this->requested_loan_period;
		else
			return "";
	}
	
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
		/*
		if (is_a($this->item, "reserveItem")) return false;  //reserveItems are not headings
		else return true;
		*/
		
		if (!is_a($this->item, "reserveItem"))
			$this->getItem();
		if ($this->item->itemType == 'HEADING')
			return true;
		else
			return false;
		
	}
	
	function hideReserve($userID)
	{
		global $g_dbConn;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "INSERT INTO hidden_readings (user_id, reserve_id) VALUES (!, !)";
		}


		$rs = $g_dbConn->query($sql, array($userID, $this->reserveID));
		if (DB::isError($rs))
		{

			if ($rs->getMessage() == 'DB Error: already exists')
			{
				return false;
			}
			else
				trigger_error($rs->getMessage(), E_USER_ERROR);
		}

		$this->hidden=true;
		return true;
	}
	
	function unhideReserve($userID)
	{
		global $g_dbConn;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "DELETE FROM hidden_readings WHERE user_id = ! AND reserve_id = !";
		}

		$rs = $g_dbConn->query($sql, array($userID, $this->reserveID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

	}

}
?>