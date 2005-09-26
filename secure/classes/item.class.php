<?
/*******************************************************($this->itemGroup); }
**
item.class.php
Item Primitive Object

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
require_once("secure/common.inc.php");

class item
{
	//Attributes
	public $itemID;
	public $title;
	public $itemGroup;
	public $creationDate;
	public $lastModDate;
	public $itemType;
	public $notes = array();
	public $contentNotes;

	/**
	* @return item
	* @param int $itemID
	* @desc initalize the item object
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
	}

	/**
	* @return void
	* @param int $itemID
	* @desc get item info from the database
	*/
	function getItemByID($itemID)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT i.item_id, i.title, i.item_group, i.last_modified, i.creation_date, i.item_type, i.content_notes, n.note_id "
					.  "FROM items as i "
					.  "LEFT JOIN notes as n on n.target_table='items' AND n.target_id = i.item_id "
					.  "WHERE item_id = !"
					;
		}

		$rs = $g_dbConn->query($sql, $itemID);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$row = $rs->fetchRow();
			$this->itemID 		= $row[0];
			$this->title		= $row[1];
			$this->itemGroup	= $row[2];
			$this->lastModDate	= $row[3];
			$this->creationDate = $row[4];
			$this->itemType		= $row[5];
			$this->contentNotes	= $row[6];
			//$this->notes[] = new note($row[7]); This won't work if there are multiple notes... replaced with logic below ... kaw 8.4.05
			
			
			if (!is_null($row[7]))
				$this->notes[] = new note($row[7]);
				
			while ($row = $rs->fetchRow()) //get additional notes
				if (!is_null($row[7]))
					$this->notes[] = new note($row[7]);
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

	function getNotes()
	{
		return $this->notes;
	}

	function getContentNotes()
	{
		return htmlentities(stripslashes($this->contentNotes));
	}

	function setNote($type, $text)
	{
		$this->notes[] = common_setNote($noteID=null, $type, $text, "items", $this->itemID);
	}
}
?>
