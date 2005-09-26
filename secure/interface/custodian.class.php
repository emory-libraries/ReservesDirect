<?
/*******************************************************************************
Custodian.class.php
Custodian Object

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
require_once("secure/interface/student.class.php");
require_once("secure/classes/specialUser.class.php");

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