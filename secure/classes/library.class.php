<?
/*******************************************************************************
library.class.php
Library Primitive Object

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

	function library($libraryID)
	{
		global $g_dbConn;

		$this->$libraryID = $libraryID;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql  = "SELECT l.library_id, l.name, l.nickname, l.ils_prefix, l.reserve_desk, l.url, l.contact_email "
					  . "FROM libraries as l "
					  . "WHERE l.library_id = !";

		}

		$rs = $g_dbConn->query($sql, $libraryID);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		list($this->libraryID, $this->library, $this->libraryNickname, $this->ilsPrefix, $this->reserveDesk, $this->libraryURL, $this->contactEmail) = $rs->fetchRow();
	}

	function getLibraryID() { return $this->libraryID; }
	function getLibrary() { return $this->library; }
	function getLibraryNickname() { return $this->libraryNickname; }
	function getILS_prefix() { return $this->ilsPrefix; }
	function getReserveDesk() { return $this->reserveDesk; }
	function getLibraryURL() { return $this->libraryURL; }
	function getContactEmail() { return $this->contactEmail; }
}
?>
