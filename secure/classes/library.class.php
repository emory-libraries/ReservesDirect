<?
/*******************************************************************************
library.class.php
Library Primitive Object

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

class library
{
	//Attributes
	public $libraryID;
	public $library;
	public $libraryNickname;
	public $libraryURL;
	public $contactEmail;
	
	function library($libraryID)
	{
		global $g_dbConn;
		
		$this->$libraryID = $libraryID;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql  = "SELECT l.library_id, l.name, l.nickname, l.url, l.contact_email "
					  . "FROM libraries as l "
					  . "WHERE l.library_id = !";
					 
		}

		$rs = $g_dbConn->query($sql, $libraryID);		
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
			
		list($this->libraryID, $this->library, $this->libraryNickname, $this->libraryURL, $this->contactEmail) = $rs->fetchRow();
	}
/*	
	function getAllLibraries()
	{
		global $g_dbConn;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql  = "SELECT l.library_id, l.name, l.nickname, l.url, l.contact_email FROM libraries as l";					 
		}

		$rs = $g_dbConn->query($sql);	
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
			
		$tmpArray = array();
		while ($row = $rs->fetchRow()) {		
			$tmpArray[] = $row;
		}	
		return $tmpArray;
	}
*/	
	function getLibraryID() { return $this->libraryID; }
	function getLibrary() { return $this->library; }
	function getLibraryNickname() { return $this->libraryNickname; }
	function getLibraryURL() { return $this->libraryURL; }
	function getContactEmail() { return $this->contactEmail; }
}	
?>
