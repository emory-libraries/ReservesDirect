<?
/*******************************************************************************
Custodian.class.php
Custodian Object

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
require_once("common.inc.php");
require_once("interface/student.class.php");
require_once("classes/specialUser.class.php");

class custodian extends student 
{
	var $sp;
	
	function custodian($userName)
	{
		$this->getUserByUserName($userName);
		
		if ($this->getUserClass() != "custodian") trigger_error($userName . " has not been authorized as custodian", E_ERROR);
	}
	
	function createSpecialUser($userName, $email, $date=null)
	{
		//this function is duplicated in the staff class
		$sp = new specialUser();
		return (string) $sp->createNewSpecialUser($userName, $email, $date);	
	}
	
	function resetSpecialUserPassword($userName)
	{
		//this function is duplicated in the staff class
		$this->sp = new specialUser();
		$this->sp->resetPassword($userName);
	}
	
	function getSpecialUsers()
	{
		//this function is duplicated in the custodian class
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT sp.user_id FROM special_users as sp JOIN users as u ON sp.user_id = u.user_id ORDER BY u.username";
		}		
		$rs = $g_dbConn->query($sql);	
			
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		$tmpArray = array();
		while($row = $rs->fetchRow())
		{
			$tmpArray[] = new user($row[0]);
		}		
		return $tmpArray;
	}
	
	function getSpecialUserMsg() { return $this->sp->getMsg(); }
}
?>