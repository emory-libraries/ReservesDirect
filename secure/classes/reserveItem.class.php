<?
/*******************************************************************************
reserveItem.class.php
ReserveItem Primitive Object

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
require_once("secure/classes/item.class.php");
require_once("secure/classes/physicalCopy.class.php");
require_once("secure/classes/user.class.php");

class reserveItem extends item
{
	//Attributes
	public $author;
	public $source;
	public $volumeTitle;
	public $contentNotes;
	public $volumeEdition;
	public $pagesTimes;
	public $performer;
	public $localControlKey;
	public $URL;
	public $mimeTypeID;
	public $homeLibraryID;
	//public $homeLibrary;
	public $privateUserID;
	public $privateUser;
	public $copies = array();
	public $physicalCopy;
	public $itemIcon;
	
	function reserveItem($itemID=NULL)
	{
		if (!is_null($itemID)){
			$this->itemID = $itemID;
			$this->getItemByID($itemID);
		}
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
				$sql = "SELECT i.item_id, i.title, i.item_group, i.author, i.source, i.content_notes, i.volume_edition, i.pages_times, i.performer, i.local_control_key, "
					.     "i.creation_date, i.last_modified, i.url, i.mimeType, i.home_library, i.private_user_id, i.item_type, i.volume_title, i.item_icon, n.note_id "
					.  "FROM items as i "
					.  "  LEFT JOIN notes as n ON n.target_table='items' and i.item_id = n.target_id "
					.  "WHERE item_id = ! "
					.  "ORDER BY n.type, n.note_id";
		}

		$rs = $g_dbConn->query($sql, $itemID);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$row = $rs->fetchRow();
			$this->itemID			= $row[0];
			$this->title			= $row[1];
			$this->itemGroup		= $row[2];
			$this->author			= $row[3];
			$this->source			= $row[4];
			$this->contentNotes		= $row[5];
			$this->volumeEdition	= $row[6];
			$this->pagesTimes		= $row[7];
			$this->performer		= $row[8];
			$this->localControlKey	= $row[9];
			$this->creationDate		= $row[10];
			$this->lastModDate		= $row[11];
			$this->URL				= $row[12];
			$this->mimeTypeID		= $row[13];
			$this->homeLibraryID	= $row[14];
			$this->privateUserID	= $row[15];
			$this->itemType			= $row[16];
			$this->volumeTitle		= $row[17];
			$this->itemIcon			= $row[18];
			
			if (!is_null($row[19]))
				$this->notes[] = new note($row[19]);

			while ($row = $rs->fetchRow()) //get additional notes
				if (!is_null($row[19]))
					$this->notes[] = new note($row[19]);

	}

	/**
	* @return void
	* @param string localControl
	* @desc get item info from the database by localcontrolkey
	*/
	function getItemByLocalControl($local_control_key)
	{
		global $g_dbConn;
		
		if (!is_null($local_control_key) && $local_control_key != '')
		{
			switch ($g_dbConn->phptype)
			{
				default: //'mysql'
					$sql = "SELECT i.item_id, i.title, i.item_group, i.author, i.source, i.content_notes, i.volume_edition, i.pages_times, i.performer, i.local_control_key, "
					.     "i.creation_date, i.last_modified, i.url, i.mimeType, i.home_library, i.private_user_id, i.item_type, i.volume_title, i.item_icon, n.note_id "
					.  	  "FROM items as i "
					.     "WHERE i.local_control_key = ?";
			}
	
			$rs = $g_dbConn->query($sql, $local_control_key);
			if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
				
			while ($row = $rs->fetchRow())
			{
				$this->itemID			= $row[0];
				$this->title			= $row[1];
				$this->itemGroup		= $row[2];	
				$this->author			= $row[3];
				$this->source			= $row[4];
				$this->contentNotes		= $row[5];
				$this->volumeEdition	= $row[6];
				$this->pagesTimes		= $row[7];
				$this->performer		= $row[8];
				$this->localControlKey	= $row[9];
				$this->creationDate		= $row[10];
				$this->lastModDate		= $row[11];
				$this->URL				= $row[12];
				$this->mimeTypeID		= $row[13];
				$this->homeLibraryID	= $row[14];
				$this->privateUserID	= $row[15];
				$this->itemType			= $row[16];
				$this->volumeTitle		= $row[17];
				$this->itemIcon			= $row[18];
				
				if (!is_null($row[19]))
					$this->notes[] = new note($row[19]);
	
				while ($row = $rs->fetchRow()) //get additional notes
					if (!is_null($row[19]))
						$this->notes[] = new note($row[19]);
				} 
		}

	}

	/**
	* @return void
	* @param string $author
	* @desc set new author in database
	*/
	function setAuthor($author)
	{
		global $g_dbConn;

		$this->author = $author;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE items SET author = ?, last_modified = ? WHERE item_id = !";
				$d = date("Y-m-d"); //get current date
		}
		$rs = $g_dbConn->query($sql, array(stripslashes($author), $d, $this->itemID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$this->author = $author;
		$this->lastModDate = $d;
	}

	/**
	* @return void
	* @param string $source
	* @desc set new source in database
	*/
	function setSource($source)
	{
		global $g_dbConn;

		$this->source = $source;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE items SET source = ?, last_modified = ? WHERE item_id = !";
				$d = date("Y-m-d"); //get current date
		}
		$rs = $g_dbConn->query($sql, array(stripslashes($source), $d, $this->itemID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$this->source = $source;
		$this->lastModDate = $d;
	}

	/**
	* @return void
	* @param string $volumeTitle
	* @desc set new volumeTitle in database
	*/
	function setVolumeTitle($volumeTitle)
	{
		global $g_dbConn;

		$this->source = $volumeTitle;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE items SET volume_title = ?, last_modified = ? WHERE item_id = !";
				$d = date("Y-m-d"); //get current date
		}
		$rs = $g_dbConn->query($sql, array(stripslashes($volumeTitle), $d, $this->itemID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$this->volumeTitle = $volumeTitle;
		$this->lastModDate = $d;
	}

	/**
	* @return void
	* @param string $contentNotes
	* @desc set new contentNotes in database
	*/
	function setContentNotes($contentNotes)
	{
		global $g_dbConn;

		$this->contentNotes = $contentNotes;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE items SET content_notes = ?, last_modified = ? WHERE item_id = !";
				$d = date("Y-m-d"); //get current date
		}
		$rs = $g_dbConn->query($sql, array(stripslashes($contentNotes), $d, $this->itemID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$this->contentNotes = $contentNotes;
		$this->lastModDate = $d;
	}

	/**
	* @return void
	* @param string $volumeEdition
	* @desc set new volumeEdition in database
	*/
	function setvolumeEdition($volumeEdition)
	{
		global $g_dbConn;

		$this->volumeEdition = $volumeEdition;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE items SET volume_edition = ?, last_modified = ? WHERE item_id = !";
				$d = date("Y-m-d"); //get current date
		}
		$rs = $g_dbConn->query($sql, array(stripslashes($volumeEdition), $d, $this->itemID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$this->volumeEdition = $volumeEdition;
		$this->lastModDate = $d;
	}

	/**
	* @return void
	* @param string $pagesTimes
	* @desc set new pagesTimes in database
	*/
	function setPagesTimes($pagesTimes)
	{
		global $g_dbConn;

		$this->pagesTimes = $pagesTimes;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE items SET pages_times = ?, last_modified = ? WHERE item_id = !";
				$d = date("Y-m-d"); //get current date
		}
		$rs = $g_dbConn->query($sql, array(stripslashes($pagesTimes), $d, $this->itemID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$this->pagesTimes = $pagesTimes;
		$this->lastModDate = $d;
	}

	/**
	* @return void
	* @param string $performer
	* @desc set new performer in database
	*/
	function setPerformer($performer)
	{
		global $g_dbConn;

		$this->performer = $performer;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE items SET performer = ?, last_modified = ? WHERE item_id = !";
				$d = date("Y-m-d"); //get current date
		}
		$rs = $g_dbConn->query($sql, array(stripslashes($performer), $d, $this->itemID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$this->performer = $performer;
		$this->lastModDate = $d;
	}

	/**
	* @return void
	* @param string $localControlKey
	* @desc set new localControlKey in database
	*/
	function setLocalControlKey($localControlKey)
	{
		global $g_dbConn;

		$this->localControlKey = $localControlKey;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE items SET local_control_key = ?, last_modified = ? WHERE item_id = !";
				$d = date("Y-m-d"); //get current date
		}
		$rs = $g_dbConn->query($sql, array($localControlKey, $d, $this->itemID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$this->localControlKey = $localControlKey;
		$this->lastModDate = $d;
	}

	/**
	* @return void
	* @param string $URL
	* @desc set new URL in database
	*/
	function setURL($URL)
	{
		global $g_dbConn;

		$this->URL = $URL;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE items SET url = ?, last_modified = ? WHERE item_id = !";
				$d = date("Y-m-d"); //get current date
		}
		$rs = $g_dbConn->query($sql, array($URL, $d, $this->itemID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$this->URL = $URL;
		$this->lastModDate = $d;
	}

	/**
	* @return void
	* @param string $mimeType
	* @desc set new mimeType in database
	*/
	function setMimeType($mimeType)
	{
		global $g_dbConn;


		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql1 = "SELECT mimetype_id FROM mimetypes WHERE mimetype = ?";

				$sql = "UPDATE items SET mimetype = ?, last_modified = ? WHERE item_id = !";
				$d = date("Y-m-d"); //get current date
		}
		$mimeType = (!is_null($mimeType)) ? $mimeType : "text/html";
		$rs = $g_dbConn->query($sql1, array($mimeType));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$this->mimeTypeID = null;
		while ($row = $rs->fetchRow()) { $this->mimeTypeID = $row[0]; }

		$rs = $g_dbConn->query($sql, array($this->mimeTypeID, $d, $this->itemID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$this->lastModDate = $d;
	}

	function setMimeTypeByFileExt($ext)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql1 = "SELECT mimetype_id FROM mimetypes WHERE  file_extentions = ?";

				$sql = "UPDATE items SET mimetype = ?, last_modified = ? WHERE item_id = !";
				$d = date("Y-m-d"); //get current date
		}

		$rs = $g_dbConn->query($sql1, array($ext));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$this->mimeTypeID = null;
		while ($row = $rs->fetchRow()) { $this->mimeTypeID = $row[0]; }

		$rs = $g_dbConn->query($sql, array($this->mimeTypeID, $d, $this->itemID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$this->lastModDate = $d;
	}
	
	function setDocTypeIcon($docTypeIcon)
	{
		global $g_dbConn;

		$this->volumeEdition = $volumeEdition;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE items SET item_icon = ?, last_modified = ? WHERE item_id = !";
				$d = date("Y-m-d"); //get current date
		}
		
		$rs = $g_dbConn->query($sql, array($docTypeIcon, $d, $this->itemID));	
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$this->itemIcon = $docTypeIcon;
		$this->lastModDate = $d;
	}

	function getItemIcon()
	{
		global $g_dbConn;
			
		if (!isset($this))
			return 'images/doc_type_icons/doctype-clear.gif';
			
		if (is_null($this->itemIcon))	
		{	
			switch ($this->mimeTypeID)
			{
				case '7':
				case 'text/html':
				case null:
					switch ($this->itemGroup)
					{
						case 'MONOGRAPH':
							return 'images/doc_type_icons/doctype-book.gif';
						break;
						case 'MULTIMEDIA':
							return 'images/doc_type_icons/doctype-disc2.gif';
						break;
						case 'ELECTRONIC':
							return 'images/doc_type_icons/doctype-link.gif';
						break;
						default:
							return 'images/doc_type_icons/doctype-clear.gif';
					}
				break;
	
				case '1': // PDF
					return 'images/doc_type_icons/doctype-pdf.gif';
				break;
	
				default:
					switch ($g_dbConn->phptype)
					{
						default: //'mysql'
							$sql = "SELECT helper_app_icon FROM mimetypes WHERE mimetype_id = !";
					}
					$rs = $g_dbConn->query($sql, array($this->mimeTypeID));
					if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	
					if ($rs->numRows() < 1)
						return 'images/doc_type_icons/doctype-clear.gif';
					else {
						$row = $rs->fetchRow();
						return $row[0];
					}
			}
		} else 
			return $this->itemIcon;
	}

	

	/**
	* @return void
	* @param string $homeLibraryID
	* @desc set new homeLibraryID in database
	*/
	function setHomeLibraryID($homeLibraryID)
	{
		global $g_dbConn;

		$this->homeLibraryID = $homeLibraryID;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE items SET home_library = !, last_modified = ? WHERE item_id = !";
				$d = date("Y-m-d"); //get current date
		}
		$rs = $g_dbConn->query($sql, array($homeLibraryID, $d, $this->itemID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$this->homeLibraryID = $homeLibraryID;
		$this->lastModDate = $d;
	}

	/**
	* @return void
	* @param int $privateUserID
	* @desc set new privateUserID in database
	*/
	function setPrivateUserID($privateUserID)
	{
		global $g_dbConn;

		$this->privateUserID = $privateUserID;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE items SET private_user_id = !, last_modified = ? WHERE item_id = !";
				$d = date("Y-m-d"); //get current date
		}
		$rs = $g_dbConn->query($sql, array($privateUserID, $d, $this->itemID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$this->privateUserID = $privateUserID;
		$this->lastModDate = $d;
	}

	function getAuthor() { return htmlentities(stripslashes($this->author)); }
	function getSource() { return htmlentities(stripslashes($this->source)); }
	function getVolumeTitle() { return htmlentities(stripslashes($this->volumeTitle)); }
	function getContentNotes() { return htmlentities(stripslashes($this->contentNotes)); }
	function getVolumeEdition() { return htmlentities(stripslashes($this->volumeEdition)); }
	function getPagesTimes() { return htmlentities(stripslashes($this->pagesTimes)); }
	function getPerformer() { return htmlentities(stripslashes($this->performer)); }
	function getLocalControlKey() { return stripslashes($this->localControlKey); }
	function getURL() { return stripslashes($this->URL); }

	function getMimeType()
	{
		global $g_dbConn;

		$mimetype = "x-application";
		if (!is_null($this->mimeTypeID) && is_numeric($this->mimeTypeID)){
			switch ($g_dbConn->phptype)
			{
				default: //'mysql'
					$sql = "SELECT mimetype FROM mimetypes WHERE mimetype_id = ! LIMIT 1";
			}
			$rs = $g_dbConn->query($sql, array($this->mimeTypeID));
			if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }


			while($row = $rs->fetchRow()) { $mimetype = $row[0]; }
		}

		return $mimetype;
	}

	function getHomeLibrary() { return $this->homeLibrary; }

	function getPrivateUser()
	{
		$this->privateUser = new user($this->privateUserID);
	}

	function getPrivateUserID() { return (!is_null($this->privateUserID) && $this->privateUserID != "") ? $this->privateUserID : null; }

	function getPhysicalCopy()
	{
		$this->physicalCopy = new physicalCopy();
		$this->physicalCopy->getByItemID($this->getItemID());
	}

	function isPhysicalItem()
	{
		if ($this->itemGroup == 'MULTIMEDIA' || $this->itemGroup == 'MONOGRAPH') {
			return true;
		} else {
			return false;
		}
	}

	function isPersonalCopy()
	{
		if ($this->privateUserID != null)
			return true;
		else
			return false;
	}
}