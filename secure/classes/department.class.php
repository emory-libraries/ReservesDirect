<?
/*******************************************************************************
department.class.php
Department Primitive Object

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
require_once("secure/classes/library.class.php");

class department extends library 
{
	//Attributes
	public $deptID;
	public $name;
	public $abbr;
	
	function department($deptID=null)
	{
		global $g_dbConn;
		
		if (!is_null($deptID))
		{
			$this->deptID = $deptID;
			
			switch ($g_dbConn->phptype)
			{
				default: //'mysql'
					$sql  = "SELECT d.name, d.abbreviation, l.library_id, l.name, l.nickname, l.url "
						  . "FROM departments as d LEFT JOIN libraries as l ON d.library_id = l.library_id "
						  . "WHERE d.department_id = !";
						 
			}
	
			$rs = $g_dbConn->query($sql, $this->deptID);
			if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
				
			$row = $rs->fetchRow();
				$this->name 			= $row[0];
				$this->abbr 			= $row[1];
				$this->libraryID 		= $row[2];
				$this->library 			= $row[3];
				$this->libraryNickname 	= $row[4];
				$this->libraryURL 		= $row[5];
		}

	}
	
	
	/**
	* @return department recordset
	* @desc returns all departments
	*/
	function getAllDepartments()
	{
		global $g_dbConn;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql  = "SELECT d.department_id, d.abbreviation, d.name "
					  . "FROM departments d "
					  . "ORDER BY d.abbreviation"
					  ;
					 
		}

		$rs = $g_dbConn->query($sql);	
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
			
		$tmpArray = array();
		while ($row = $rs->fetchRow()) {		
			$tmpArray[] = $row;
		}	
		return $tmpArray;
	}
	
	function getDepartmentID() { return $this->deptID; }
	function getName() { return $this->name; }
	function getAbbr() { return $this->abbr; }

}	
?>