<?
/*******************************************************************************
library.class.php
Library Primitive Object

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

class library
{
	//Attributes
	public $libraryID;
	public $library;
	public $libraryNickname;
	public $ilsPrefix;
	public $reserveDesk;
	public $libraryURL;
	public $contactEmail;
	private $monograph_library_id;
	private $multimedia_library_id;
	private $copyrightLibraryID;

	function library($libraryID)
	{
		global $g_dbConn;

		if (!is_null($libraryID))
		{
			$this->$libraryID = $libraryID;
	
			switch ($g_dbConn->phptype)
			{
				default: //'mysql'
					$sql  = "SELECT l.library_id, l.name, l.nickname, l.ils_prefix, l.reserve_desk, l.url, l.contact_email, l.monograph_library_id, l.multimedia_library_id, l.copyright_library_id "
						  . "FROM libraries as l "
						  . "WHERE l.library_id = !";
	
			}
	
			$rs = $g_dbConn->query($sql, $libraryID);
			if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	
			list($this->libraryID, $this->library, $this->libraryNickname, $this->ilsPrefix, $this->reserveDesk, $this->libraryURL, $this->contactEmail, $this->monograph_library_id, $this->multimedia_library_id, $this->copyrightLibraryID) = $rs->fetchRow();
		}
	}
	
	function createNew($name, $nickname, $ils_prefix, $reserveDesk, $url, $contactEmail, $monograph_library_id, $multimedia_library_id, $copyright_library_id)
	{
		global $g_dbConn;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql  = "INSERT INTO libraries (name, nickname, ils_prefix, reserve_desk, url, contact_email, monograph_library_id, multimedia_library_id, copyright_library_id) VALUES (?,?,?,?,?,?,!,!,!)";

		}

		$rs = $g_dbConn->query($sql, array($name, $nickname, $ils_prefix, $reserveDesk, $url, $contactEmail, $monograph_library_id, $multimedia_library_id, $copyright_library_id));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	function update()
	{
		global $g_dbConn;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql  = "UPDATE libraries set name = ?, nickname = ?, ils_prefix =?, reserve_desk = ?, url = ?, contact_email = ?, monograph_library_id = !, multimedia_library_id = !, copyright_library_id = ! WHERE library_id = !";

		}

		$rs = $g_dbConn->query($sql, array($this->getLibrary(), $this->getLibraryNickname(), $this->getILS_prefix(), $this->getReserveDesk(), $this->getContactEmail(), $this->getLibraryID(), $this->getMonograph_library_id(), $this->getMultimedia_library_id(), $this->copyrightLibraryID, $this->getLibraryID()));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}	

	function getLibraryID() { return $this->libraryID; }
	function getLibrary() { return $this->library; }
	function getLibraryNickname() { return $this->libraryNickname; }
	function getILS_prefix() { return $this->ilsPrefix; }
	function getReserveDesk() { return $this->reserveDesk; }
	function getLibraryURL() { return $this->libraryURL; }
	function getContactEmail() { return $this->contactEmail; }
	function getMonograph_library_id() { return $this->monograph_library_id; }
	function getMultimedia_library_id() { return $this->multimedia_library_id; }
	function getCopyrightLibraryID() { return $this->copyrightLibraryID; }
		
	function setLibrary($name) { $this->library = stripslashes($name); }
	function setLibraryNickname($nickname) { $this->libraryNickname = stripslashes($nickname); }
	function setILS_prefix($prefix) { $this->ilsPrefix = stripslashes($prefix); }
	function setReserveDesk($desk) { $this->reserveDesk = stripslashes($desk); }
	function setLibraryURL($url) { $this->libraryURL = stripslashes($url); }
	function setContactEmail($email) { $this->contactEmail = $email; }	
	function setMonograph_library_id($library_id) { $this->monograph_library_id = $library_id; }
	function setMultimedia_library_id($library_id) { $this->multimedia_library_id = $library_id; }	
}
?>
