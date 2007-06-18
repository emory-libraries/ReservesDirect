<?
/*********************************************************
item.class.php
Item Primitive Object

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

require_once('secure/classes/notes.class.php');

class item extends Notes {
	
	//Attributes
	public $itemID;
	public $title;
	public $itemGroup;
	public $creationDate;
	public $lastModDate;
	public $itemType;

	/**
	* @return void
	* @param int $itemID (optional)
	* @desc Initalize the item object
	*/
	function item($itemID = NULL)
	{
		if (!is_null($itemID)){
			$this->getItemByID($itemID);
		}
	}

	/**
	* @return int itemID
	* @desc create new item in database
	*/
	function createNewItem()
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "INSERT INTO items (creation_date, last_modified) VALUES (?, ?)";
				$sql2 = "SELECT LAST_INSERT_ID() FROM items";

				$d = date("Y-m-d"); //get current date
		}


		$rs = $g_dbConn->query($sql, array($d, $d));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$rs = $g_dbConn->query($sql2);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$row = $rs->fetchRow();
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$this->itemID = $row[0];
		$this->creationDate = $d;
		$this->lastModDate = $d;
		
		$this->getItemByID($this->itemID);
		return $this->itemID;
	}

	/**
	* @return void
	* @param int $itemID
	* @desc get item info from the database
	*/
	function getItemByID($itemID)
	{
		global $g_dbConn;
			
		if(empty($itemID)) {
			return false;
		}

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT i.item_id, i.title, i.item_group, i.last_modified, i.creation_date, i.item_type "
					.  "FROM items as i "
					.  "WHERE item_id = !"
					;
		}

		$rs = $g_dbConn->getRow($sql, array($itemID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		if(empty($rs)) {
			return false;
		}
		else {
			list($this->itemID, $this->title, $this->itemGroup, $this->lastModDate, $this->creationDate, $this->itemType) = $rs;

			//get the notes
			$this->setupNotes('items', $this->itemID);
			$this->fetchNotes();
			
			return true;
		}
	}

	/**
	* @return void
	* @param string $title
	* @desc set new Title in database
	*/
	function setTitle($title)
	{
		global $g_dbConn;

		$this->title = $title;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE items SET title = ?, last_modified = ? WHERE item_id = !";
				$d = date("Y-m-d"); //get current date
		}
		$rs = $g_dbConn->query($sql, array(stripslashes($title), $d, $this->itemID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$this->title = $title;
		$this->lastModDate = $d;
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
					.  "FROM items "
					.  "WHERE item_id = !"
					;
		}

		$rs = $g_dbConn->query($sql, $requestID);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	/**
	* @return void
	* @param string $type
	* @desc set new type in database
	*/
	function setType($type)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE items SET item_type = ?, last_modified = ? WHERE item_id = !";
				$d = date("Y-m-d"); //get current date
		}
		$rs = $g_dbConn->query($sql, array(stripslashes($type), $d, $this->itemID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$this->itemType = $type;
		$this->lastModDate = $d;
	}

	/**
	* @return void
	* @param string $title
	* @desc set new Title in database
	*/
	function setGroup($group)
	{
		global $g_dbConn;

		$this->itemGroup = $group;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE items SET item_group = ?, last_modified = ? WHERE item_id = !";
				$d = date("Y-m-d"); //get current date
		}
		$rs = $g_dbConn->query($sql, array(stripslashes($group), $d, $this->itemID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$this->itemGroup = $group;
		$this->lastModDate = $d;
	}

	function getTitle(){ return htmlentities(stripslashes($this->title)); }
	function getItemID(){ return htmlentities(stripslashes($this->itemID)); }
	function getItemGroup() { return htmlentities(stripslashes($this->itemGroup)); }
	function getLastModifiedDate() { return htmlentities(stripslashes($this->lastModDate)); }
	function getCreationDate() { return htmlentities(stripslashes($this->creationDate)); }
	function getType() { return htmlentities(stripslashes($this->itemType)); }
	function isHeading() { return $this->itemType == "HEADING"; }
	function makeHeading() { $this->setType("HEADING"); }
	
}
?>
