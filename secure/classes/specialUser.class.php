<?
/*******************************************************************************
specialUser.class.php
Special User Primitive Object

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
require_once("secure/common.inc.php");
require_once("secure/classes/user.class.php");

class specialUser extends user
{
	public $response = array();

	function specialUser($userID=null)
	{
		$this->user($userID);
		$this->response['msg'] = "";
	}

	function createNewSpecialUser($username, $email, $expiration=null)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "INSERT INTO special_users (user_id, password, expiration) VALUES (!, ?, ?)";
		}

		$this->getUserByUserName($username);
		if (empty($this->userID)){
			$this->createUser($username, null, null, $email, 0);
		}
		$pwd = $this->setPassword($username);
		$this->response['msg'] = "Special User created. Password = $pwd";

		$rs = $g_dbConn->query($sql, array($this->getUserID(), md5($pwd), null));
		if (DB::isError($rs)) { $this->resetPassword($username); }

		return true;
	}

	private function setPassword($username, $pass=null)
	{
		global $g_specialUserEmail, $g_siteURL, $g_reservesEmail, $g_specialUserDefaultPwd, $u, $g_permission;

		$pwd = (is_null($pass)) ? $g_specialUserDefaultPwd : $pass;
		$this->response['pwd'] = $pwd;

		$notifyEmailSentTo = null;
		if ($u->getDefaultRole() < $g_permission['admin'])
		{
			$this->sendUserEmail($g_specialUserEmail['subject'], $g_specialUserEmail['msg'], $pwd);
			$notifyEmailSentTo = $this->getEmail();
		}
		
		$this->auditSpecialUser($u->getUserID(), $notifyEmailSentTo);
		return $pwd;
	}

	function resetPassword($username, $pass=null)
	{
		global $g_dbConn, $g_specialUserEmail;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE special_users SET password=? where user_id=!";
		}

		$this->getUserByUserName($username);
		$pwd = $this->setPassword($username, $pass);

		$rs = $g_dbConn->query($sql, array(md5($pwd), $this->getUserID()));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$this->response['msg'] = "Special User password has been reset to $pwd.";

		return true;
	}

	function destroy()
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "DELETE FROM special_users where user_id=!";
		}

		$rs = $g_dbConn->query($sql, $this->getUserID());
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	function getMsg() { return $this->response['msg']; }
	
	function auditSpecialUser($creator, $email=null)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "INSERT INTO special_users_audit (user_id, creator_user_id, email_sent_to) 
						VALUES (!, !, ?);";
		}

		$rs = $g_dbConn->query($sql, array($this->getUserID(), $creator, $email));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

	}
}
?>