<?
/*******************************************************************************
physicalCopy.class.php
User Primitive Object

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
require_once("secure/classes/note.class.php");
require_once("secure/common.inc.php");

class physicalCopy
{
	//Attributes
	public $physicalCopyID;
	public $reserveID;
	public $itemID;
	public $status;
	public $callNumber;
	public $barcode;
	public $owningLibrary;
	public $itemType;
	public $ownerUserID;
	public $notes = array();
	
	
	/**
	* Constructor Method
	* @return void
	* @param optional int $physicalCopyID
	* @desc If physicalCopyID=NULL, call createPhysicalCopy to insert new record in the DB
	* @desc If physicalCopy not NULL, call getCopyByID to set object attributes w/values from the DB
	*/
	function physicalCopy($physicalCopyID=NULL)
	{
		if (!is_null($physicalCopyID)){
			$this->getCopyByID($physicalCopyID);
		}
	}
	
	/**
	* @return int physicalCopyID
	* @desc Insert new physical copy record into the DB and return the new physicalCopyID
	*/
	function createPhysicalCopy()
	{
		global $g_dbConn;		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "INSERT INTO physical_copies (reserve_id) VALUES (0)";
				$sql2 = "SELECT LAST_INSERT_ID() FROM physical_copies";
		}

		$rs = $g_dbConn->query($sql); //insert new row into PHSICAL_COPIES table
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		$rs2 = $g_dbConn->query($sql2);
		if (DB::isError($rs2)) { trigger_error($rs2->getMessage(), E_USER_ERROR); }
		
		$row = $rs2->fetchRow();		//retrieve the row just inserted into the PHYSICAL_COPIES table
		if (DB::isError($row)) { trigger_error($row->getMessage(), E_USER_ERROR); }
		
		$this->physicalCopyID = $row[0]; //return physical_copy_id of newly created record
	}
	
	function getByItemID($itemID)
	{
		global $g_dbConn;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT physical_copy_id, reserve_id, item_id, status, call_number, barcode, owning_library, item_type, owner_user_id "
					.  "FROM physical_copies "						  
					.  "WHERE item_id = ! ORDER BY physical_copy_id DESC";
		}
		
		$rs = $g_dbConn->query($sql, $itemID);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		if ($row = $rs->fetchRow())
		{
			list($this->physicalCopyID, $this->reserveID, $this->itemID, $this->status, $this->callNumber, $this->barcode, $this->owningLibrary, $this->itemType, $this->ownerUserID) = $row;
			return true;
		} else 
			return false;
	}

	
	/**
	* @return void
	* @param optional int $noteID
	* @param string $type
	* @param int $userID
	* @param string $noteText
	* @param string $targetTable="physical_copies"
	* @desc Creates a note object, and calls individual set methods to set the note attributes
	*/
	function setNote($noteID=NULL, $type, $noteText)
	{
		if (is_null($noteID)){ //Set a brand new note
			$this->notes[] = common_setNote($noteID, $type, $noteText, "physical_copies", $this->physicalCopyID);
		} else { //Update an existing note
			common_setNote($noteID, $type, $noteText, "physical_copies", $this->physicalCopyID);
				//Change this to just update the array with the changed note value,
				//Instead of re-loading the entire notes array
			$this->notes = common_getNotesByTarget("physical_copies", $this->physicalCopyID);
		}
	}
	/**
	* @return void
	* @param int $reserveID
	* @desc Updates the reserveID, associated w/the physicalCopy, in the DB
	*/
	function setReserveID($reserveID) 
	{ 
		global $g_dbConn;

		$this->reserveID = $reserveID;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE physical_copies SET reserve_id = ! WHERE physical_copy_id = !";
		}
		$rs = $g_dbConn->query($sql, array($reserveID, $this->physicalCopyID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}
	
	/**
	* @return void
	* @param int $itemID
	* @desc Updates the itemID, associated w/the physicalCopy, in the DB
	*/
	function setItemID($itemID) 
	{ 
		global $g_dbConn;

		$this->itemID = $itemID;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE physical_copies SET item_id = ! WHERE physical_copy_id = !";
		}
		$rs = $g_dbConn->query($sql, array($itemID, $this->physicalCopyID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	/**
	* @return void
	* @param string $status
	* @desc Updates the status, associated w/the physicalCopy, in the DB
	*/
	function setStatus($status) 
	{ 
		global $g_dbConn;

		$this->status = $status;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE physical_copies SET status = ? WHERE physical_copy_id = !";
		}
		$rs = $g_dbConn->query($sql, array($status, $this->physicalCopyID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	/**
	* @return void
	* @param string $callNumber
	* @desc Updates the callNumber, associated w/the physicalCopy, in the DB
	*/
	function setCallNumber($callNumber) 
	{ 
		global $g_dbConn;

		$this->callNumber = $callNumber;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE physical_copies SET call_number = ? WHERE physical_copy_id = !";
		}
		$rs = $g_dbConn->query($sql, array($callNumber, $this->physicalCopyID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	/**
	* @return void
	* @param string $barcode
	* @desc Updates the barcode, associated w/the physicalCopy, in the DB
	*/
	function setBarcode($barcode)
	{ 
		global $g_dbConn;

		$this->barcode = $barcode;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE physical_copies SET barcode = ? WHERE physical_copy_id = !";
		}
		$rs = $g_dbConn->query($sql, array($barcode, $this->physicalCopyID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}
	
	/**
	* @return void
	* @param int $owningLibrary
	* @desc Updates the Owning Library, associated w/the physicalCopy, in the DB
	*/
	function setOwningLibrary($owningLibrary)
	{ 
		global $g_dbConn;

		$this->owningLibrary = $owningLibrary;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE physical_copies SET owning_library = ? WHERE physical_copy_id = !";
		}

		$rs = $g_dbConn->query($sql, array($owningLibrary, $this->physicalCopyID));		
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}
	
	/**
	* @return void
	* @param string $itemType
	* @desc Updates the item type, associated w/the physicalCopy, in the DB
	*/
	function setItemType($itemType)
	{ 
		global $g_dbConn;

		$this->itemType = $itemType;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE physical_copies SET item_type = ? WHERE physical_copy_id = !";
		}
		$rs = $g_dbConn->query($sql, array($itemType, $this->physicalCopyID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	/**
	* @return void
	* @param int $ownerUserID
	* @desc Updates the owner_user_id value, associated w/the physicalCopy, in the DB
	*/
	function setOwnerUserID($ownerUserID)
	{ 
		global $g_dbConn;

		$this->ownerUserID = $ownerUserID;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE physical_copies SET owner_user_id = ! WHERE physical_copy_id = !";
		}
		$rs = $g_dbConn->query($sql, array($ownerUserID, $this->physicalCopyID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	function getPhysicalCopyID() { return $this->physicalCopyID; }
	function getReserveID() { return $this->reserveID; }
	function getItemID() { return $this->itemID; }
	function getStatus() { return $this->status; }
	function getCallNumber() { return $this->callNumber; }
	function getBarcode() { return $this->barcode; }
	function getOwningLibrary() { return $this->owningLibrary; }
	function getItemType() { return $this->itemType; }
	function getOwnerUserID() { return $this->ownerUserID; }

	/**
	* @return void
	* @desc Calls getNotesByTarget() to retrieve all notes for a physical copy, from the DB, by physicalCopyID
	*/
	function getNotes()	
	{ 
		$this->notes = common_getNotesByTarget("physical_copies", $this->physicalCopyID);
	}

	/**
	* @return void
	* @param int $physicalCopyID
	* @desc Gets physical copies record from DB by physicalCopyID
	*/
	function getCopyByID($physicalCopyID)
	{
		global $g_dbConn;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT physical_copy_id, reserve_id, item_id, status, call_number, barcode, owning_library, item_type, owner_user_id "
					.  "FROM physical_copies "						  
					.  "WHERE physical_copy_id = !";
		}
		
		$rs = $g_dbConn->query($sql, $physicalCopyID);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		list($this->physicalCopyID, $this->reserveID, $this->itemID, $this->status, $this->callNumber, $this->barcode, $this->owningLibrary, $this->itemType, $this->ownerUserID) = $rs->fetchRow();
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
					.  "FROM physical_copies "						  
					.  "WHERE physical_copy_id = !"
					;
				$sql2 = "DELETE "
					.	"FROM notes "
					.	"WHERE target_id = ! AND target_table = 'physical_copies'"
					;
		}
		
		$rs = $g_dbConn->query($sql, $this->physicalCopyID);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }		
		
		$rs = $g_dbConn->query($sql2, $this->physicalCopyID);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }		
	}
}

?>
