<?
/*******************************************************************************
specialUser.class.php
Special User Primitive Object

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
		global $g_specialUserEmail;
		
		$pwd = (is_null($pass)) ? 'A4z238h' : $pass;
		$this->response['pwd'] = $pwd;
		
		$msg = ereg_replace("\?", $username, $g_specialUserEmail['msg']);
		$msg = ereg_replace("\?", $pwd, $msg);
		
		$email="";
		
		if (!mail($email, $g_specialUserEmail['subject'], $msg, md5($pwd))) 
			$this->response['msg'] = "Special User set but email was not sent.  email:$email";
			
		return $pwd;			
	}		
	
	function resetPassword($username, $pass=null)
	{
		global $g_dbConn;
		
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
}
?>