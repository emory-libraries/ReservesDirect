<?
/*******************************************************************************
itemAudit.class.php
itemAudit Primitive Object

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

Created by Kathy Washington (kawashi@emory.edu)

Reserves Direct 2.0 is located at:
http://coursecontrol.sourceforge.net/

*******************************************************************************/
require_once("secure/classes/item.class.php");
require_once("secure/classes/reserveItem.class.php");

class itemAudit
{
	//Attributes
	var $auditID;
	var $itemID;
	var $dateAdded;
	var $addedBy;
	var $reviewDate;
	var $reviewedBy;
	
	function itemAudit($itemID=NULL)
	{
		if (!is_null($itemID)){
			$this->getItemAuditByID($itemID);
		}		
	}
	
	/**
	* @return int reserveID
	* @desc create new item_audit record in database
	*/
	function createNewItemAudit($itemID, $addedBy)
	{
		global $g_dbConn;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "INSERT INTO electronic_item_audit (item_id, date_added, added_by) VALUES (!, ?, ?)";
				$sql2 = "SELECT LAST_INSERT_ID() FROM electronic_item_audit";
		
				$d = date("Y-m-d"); //get current date
		}
		
		
		$rs = $g_dbConn->query($sql, array($itemID, $d, $addedBy));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		$rs = $g_dbConn->query($sql2);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		$row = $rs->fetchRow();
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }	
		
		$this->auditID = $row[0];
		$this->itemID = $itemID;
		$this->dateAdded = $d;
		$this->addedBy = $addedBy;
	}
	
	/**
	* @return void
	* @param int $itemID
	* @desc get itemAudit info from the database
	*/
	function getItemAuditByID($itemID)
	{
		global $g_dbConn;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT audit_id, item_id, date_added, added_by, date_reviewed, reviewed_by "
					.  "FROM electronic_item_audit "						  
					.  "WHERE item_id = !";
		}
		
		$rs = $g_dbConn->query($sql, $itemID);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
				
		$row = $rs->fetchRow();
			
		$this->auditID		= $row[0];
		$this->itemID		= $row[1];
		$this->dateAdded	= $row[2];
		$this->addedBy		= $row[3];
		$this->reviewDate	= $row[4];
		$this->reviewedBy	= $row[5];
	}	
	
	/**
	* @return void
	* @param date $reviewDate
	* @desc set reviewDate in database
	*/
	function setReviewDate($reviewDate)
	{
		global $g_dbConn;

		$this->reviewDate = $reviewDate;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE electronic_item_audit SET review_date = ? WHERE audit_id = !";
				$d = date("Y-m-d"); //get current date
		}
		$rs = $g_dbConn->query($sql, array($d, $this->auditID));		
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}
	
	/**
	* @return void
	* @param string $reviewedBy
	* @desc set reviewedBy in database
	*/
	function setReviewedBy($reviewedBy)
	{
		global $g_dbConn;

		$this->reviewedBy = $reviewedBy;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE electronic_item_audit SET reviewed_by = ? WHERE audit_id = !";
		}

		$rs = $g_dbConn->query($sql, array($reviewedBy, $this->auditID));		
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	function getAuditID() { return $this->auditID; }
	function getItemID() { return $this->itemID; }
	function getDateAdded() { return $this->dateAdded; }
	function getAddedBy() { return htmlentities(stripslashes($this->addedBy)); }
	function getReviewDate() { return $this->reviewDate; }
	function getReviewedBy() { return htmlentities(stripslashes($this->reviewedBy)); }

}