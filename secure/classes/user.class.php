<?
/*******************************************************************************
user.class.php
User Primitive Object

Created by Kathy Washington (kawashi@emory.edu)

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
require_once("secure/classes/note.class.php");
require_once("secure/common.inc.php");

class user
{
	//Attributes
	public $userID;
	public $userName;
	public $firstName;
	public $lastName;
	public $email;
	public $dfltRole;
	public $lastLogin;
	public $notes = array();


	/**
	* Constructor Method
	* @return void
	* @param optional int $userID
	* @desc If userID not NULL, call getUserByID to set user object attributes w/values from DB
	*/
	function user($userID=NULL)
	{
		if (!is_null($userID))
			$this->getUserByID($userID);
		else
			$this->userID = null;
	}

	/**
	* @return void
	* @param string $userName
	* @desc alternate constructor method
	*/
	function getUserByUserName($userName)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT user_id FROM users WHERE username = ?";
		}
		$rs = $g_dbConn->query($sql, array($userName));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		if ($rs->numRows() == 1)
		{
			$row = $rs->fetchRow();
			$this->user($row[0]);
			return true;
		} else return false;

	}

	/**
	* @returns false if user already exists
	* @desc Insert new user record into the DB and return the new userID
	*/
	function createUser($userName, $firstName="", $lastName="", $email="", $dfltRole=0)
	{
		global $g_dbConn;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "INSERT INTO users (username, first_name, last_name, email, dflt_permission_level) VALUES (?,?,?,?,!)";
				$sql2 = "SELECT LAST_INSERT_ID() FROM users";
		}

		$rs = $g_dbConn->query($sql, array($userName, $firstName, $lastName, $email, $dfltRole)); //insert new row into USERS table
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$rs2 = $g_dbConn->query($sql2);
		if (DB::isError($rs2)) { trigger_error($rs2->getMessage(), E_USER_ERROR); }

		$row = $rs2->fetchRow();		//retrieve the row just inserted into the USERS table
		if (DB::isError($row)) { trigger_error($row->getMessage(), E_USER_ERROR); }

		$this->getUserByID($row[0]); //set object attributes = to newly created user
	}

	/**
	* @return void
	* @param optional int $noteID
	* @param string $type
	* @param int $userID
	* @param string $noteText
	* @param string $targetTable="users"
	* @desc Creates a note object, and calls individual set methods to set the note attributes
	*/

	function setNote($noteID=NULL, $type, $noteText)
	{
		if (is_null($noteID)){ //Set a brand new note
			$this->notes[] = common_setNote($noteID, $type, $noteText, "users", $this->userID);
		} else { //Update an existing note
			common_setNote($noteID, $type, $noteText, "users", $this->userID);
				//Change this to just update the array with the changed note value,
				//Instead of re-loading the entire notes array
			$this->notes = common_getNotesByTarget("users", $this->userID);
		}
	}

	function getUserClass()
	{

		global $g_dbConn;

		if (!is_null($this->getDefaultRole()) && !is_null($this->userID)){
			switch ($g_dbConn->phptype)
			{
				default: //'mysql'
					$sql  = "SELECT nt.permission_level "
					.  		"FROM not_trained as nt "
					.	    "WHERE nt.user_id = ! "
					;
					$sql1 = "SELECT label FROM permissions_levels WHERE permission_id = !";
			}

			$rs = $g_dbConn->query($sql, $this->userID);
			if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

			if ($row = $rs->fetchRow()) {
				$rs2 = $g_dbConn->query($sql1, $row[0]);
				if (DB::isError($rs2)) { trigger_error($rs2->getMessage(), E_USER_ERROR); }
			} else {
				$rs2 = $g_dbConn->query($sql1, $this->getDefaultRole());
				if (DB::isError($rs2)) { trigger_error($rs2->getMessage(), E_USER_ERROR); }
			}

			if ($row = $rs2->fetchRow())
				return $row[0];
		} else
			return null;

	}

	/**
	* @return void
	* @param string $userName
	* @desc Updates the user's user_name in the DB
	*/
	function setUserName($userName)
	{
		global $g_dbConn;

		$this->userName = $userName;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE users SET username = ? WHERE user_id = !";
		}
		$rs = $g_dbConn->query($sql, array($userName, $this->userID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	/**
	* @return void
	* @param string $firstName
	* @desc Update DB with user's First Name
	*/
	function setFirstName($firstName)
	{
		global $g_dbConn;

		$this->firstName = $firstName;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE users SET first_name = ? WHERE user_id = !";
		}
		$rs = $g_dbConn->query($sql, array($firstName, $this->userID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	/**
	* @return void
	* @param string $lastName
	* @desc Update DB with user's Last Name
	*/
	function setLastName($lastName)
	{
		global $g_dbConn;

		$this->lastName = $lastName;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE users SET last_name = ? WHERE user_id = !";
		}
		$rs = $g_dbConn->query($sql, array($lastName, $this->userID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	/**
	* @return void
	* @param string $email
	* @desc Updates the DB with the users's email address
	*/
	function setEmail($email)
	{
		global $g_dbConn, $g_EmailRegExp;

		// if $email valid format add email to database
		if(ereg($g_EmailRegExp, $email)){
			$this->email = $email;
			switch ($g_dbConn->phptype)
			{
				default: //'mysql'
					$sql = "UPDATE users SET email = ? WHERE user_id = !";
			}
			$rs = $g_dbConn->query($sql, array($email, $this->userID));
			if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
			return true;
		} else {
			return false;
		}
	}

	/**
	* @return void
	* @param int $dfltRole
	* @desc Updates the DB with the user's default role
	*/
	function setDefaultRole($dfltRole)
	{
		global $g_dbConn;

		$this->dfltRole = $dfltRole;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE users SET dflt_permission_level = ! WHERE user_id = !";
		}
		$rs = $g_dbConn->query($sql, array($dfltRole, $this->userID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	function setLastLogin()
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE users SET last_login = ? WHERE user_id = !";
				$d = date('Y-m-d');
		}

		$rs = $g_dbConn->query($sql, array($d, $this->userID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$this->lastLogin = $d;
	}

	function getUserID() { return $this->userID; }
	function getUsername() { return stripslashes($this->userName); }
	function getFirstName() { return stripslashes($this->firstName); }
	function getLastName() { return stripslashes($this->lastName); }
	function getName() { return stripslashes($this->lastName) . ", " . stripslashes($this->firstName); }
	function getEmail() { return stripslashes($this->email); }
	function getLastLogin() { return $this->lastLogin; }

	function isSpecialUser()
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT count(user_id) from special_users WHERE user_id = !";
		}

		$rs = $g_dbConn->query($sql, array($this->userID));
		if (DB::isError($rs)) { return false; }

		$row = $rs->fetchRow();
		return ($row[0] == 1) ? true : false;
	}

	/**
	* @return int
	* @desc Returns User's Default Role
	*/
	function getDefaultRole() {
		global $g_dbConn;

		if ($this->userID) {
			switch ($g_dbConn->phptype)
			{
				default: //'mysql'
						$sql  = "SELECT nt.permission_level "
					.  		"FROM not_trained as nt "
					.	    "WHERE nt.user_id = !"
					;
			}

			$rs = $g_dbConn->query($sql, $this->userID);
			if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

			if ($row = $rs->fetchRow()) {
				return $row[0];
			} else {
				return $this->dfltRole;
			}
		} else
			return $this->dfltRole;
	}

	/**
	* @return void
	* @desc Calls getNotesByTarget() to retrieve all notes for a user, from the DB, by userID
	*/
	function getNotes()
	{
		$this->notes = common_getNotesByTarget("users", $this->userID);
	}

	/**
	* @return void
	* @param int $userID
	* @desc Gets user record from DB by userID
	*/
	function getUserByID($userID)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT user_id, username, first_name, last_name, email, dflt_permission_level "
					.  "FROM users "
					.  "WHERE user_id = !";
		}

		$rs = $g_dbConn->query($sql, $userID);
//print_r($rs);echo"<hr>";
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		list($this->userID, $this->userName, $this->firstName, $this->lastName, $this->email, $this->dfltRole) = $rs->fetchRow();
	}

	/**
	* @return void
	* @desc destroy the database entry
	*/
	function destroy()
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "DELETE "
					.  "FROM users "
					.  "WHERE user_id = !"
					;
				$sql2 = "DELETE "
					.	"FROM notes "
					.	"WHERE target_id = ! AND target_table = 'users'"
					;
		}

		$rs = $g_dbConn->query($sql, $this->userID);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$rs = $g_dbConn->query($sql2, $this->userID);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	/**
	* @return array of all Libraries
	*/
	function getLibraries() {
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
					$sql  = "SELECT library_id "
				.  		"FROM libraries"
				;
		}

		$rs = $g_dbConn->query($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		while($row = $rs->fetchRow())
		{
			$tmpArry[] = new library($row[0]);
		}
		return $tmpArry;
	}

}
?>