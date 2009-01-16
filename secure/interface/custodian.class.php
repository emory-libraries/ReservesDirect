<?
/*******************************************************************************
Custodian.class.php
Custodian Object

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
require_once("secure/common.inc.php");
require_once("secure/interface/student.class.php");
require_once("secure/classes/specialUser.class.php");

class custodian extends student
{
	var $sp;

	function custodian($userName=null) {
		if(!empty($userName)) {
			$this->getUserByUserName($userName);	
		}
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
