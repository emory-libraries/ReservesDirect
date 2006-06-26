<?
/*******************************************************************************
user.class.php
User Primitive Object

Created by Kathy Washington (kawashi@emory.edu)

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

class user
{
	//Attributes
	public $userID;
	public $userName;
	public $firstName;
	public $lastName;
	public $email;
	public $dfltRole;
	private $dfltClass;
	protected $role;
	public $lastLogin;
	private $userClass;
	private $not_trained;

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
	* @return void
	* @param string $userName, string encrypted pwd
	* @desc retrieve user by username and encrypted pwd
	*/
	function getUserByUserName_Pwd($userName, $pwd)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT u.user_id 
						FROM users as u 
							JOIN special_users as sp ON u.user_id = sp.user_id 
						WHERE u.username = ? AND sp.password = ? AND (expiration <= ? OR expiration IS NULL)
					   ";
				$d = date("Y-m-d");
		}		
		$rs = $g_dbConn->query($sql, array($userName, $pwd, $d));		

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
		$rs = $g_dbConn->query($sql, array($userName, $this->getUserID()));
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
		$rs = $g_dbConn->query($sql, array($firstName, $this->getUserID()));
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
		$rs = $g_dbConn->query($sql, array($lastName, $this->getUserID()));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	/**
	* @return void
	* @param string $email
	* @desc Updates the DB with the users's email address
	*/
	function setEmail($email)
	{
		global $g_dbConn, $g_EmailRegExp, $g_newUserEmail;

		if ($this->email != $email)
		{
			// if $email valid format add email to database
			if(ereg($g_EmailRegExp, $email)){
				$this->email = $email;
				switch ($g_dbConn->phptype)
				{
					default: //'mysql'
						$sql = "UPDATE users SET email = ? WHERE user_id = !";
				}
				$rs = $g_dbConn->query($sql, array($email, $this->getUserID()));
				if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	
				$this->sendUserEmail($g_newUserEmail['subject'], $g_newUserEmail['msg']);			
				return true;
			} else {
				return false;
			}
		}
		return true;
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
		$rs = $g_dbConn->query($sql, array($dfltRole, $this->getUserID()));
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

		$rs = $g_dbConn->query($sql, array($d, $this->getUserID()));

		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$this->lastLogin = $d;
	}
	
	
	/**
	 * @return string
	 * @param boolean $last_name_first If true, will return "LAST, FIRST" else will return "FIRST LAST"
	 * @desc returns the user's name
	 */	
	function getName($last_name_first=true) {
		if($last_name_first) {
			return stripslashes($this->lastName) . ", " . stripslashes($this->firstName);
		}
		else {
			return stripslashes($this->firstName).' '.stripslashes($this->lastName);
		}
	}

	function getUserID() { return $this->userID; }
	function getUsername() { return stripslashes($this->userName); }
	function getFirstName() { return stripslashes($this->firstName); }
	function getLastName() { return stripslashes($this->lastName); }
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

		$rs = $g_dbConn->query($sql, $this->userID);
		if (DB::isError($rs)) { return false; }

		$row = $rs->fetchRow();
		return ($row[0] == 1) ? true : false;		
	}

	/**
	* @return int
	* @desc Returns User's Default Role as specified in the users table
	*/
	function getDefaultRole() {
		return $this->dfltRole;
	}
	
	/**
	* @return string
	* @desc Returns User's Class as specified in the users table
	*/
	function getDefaultClass() {
		return $this->dfltClass;
	}
	
	/**
	* @return string
	* @desc Returns User's  Class as specified in the users table may be overwritten by not_trained
	*/
	function getUserClass()
	{
			return $this->userClass;
	}	
	
	/**
	* @return int
	* @desc Returns User's Role may be overwritten by not_trained table
	*/
	function getRole() {
		return $this->role;
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
				$sql = "
						SELECT u.user_id, u.username, u.first_name, u.last_name, u.email, u.dflt_permission_level, p.label,
							CASE WHEN nt.user_id IS NOT NULL THEN nt.permission_level
								ELSE u.dflt_permission_level END as permission_level, 
							CASE WHEN nt.user_id IS NOT NULL THEN nt_p.label
								ELSE p.label END as userclass,
							CASE WHEN nt.user_id IS NOT NULL THEN 1
								ELSE 0 END as not_trained
					    FROM users as u 
					    	LEFT JOIN not_trained as nt on u.user_id = nt.user_id					    	 
							JOIN permissions_levels as p ON p.permission_id = u.dflt_permission_level
							LEFT JOIN permissions_levels as nt_p ON nt_p.permission_id = nt.permission_level  
						WHERE u.user_id = !
				";
		}

		$rs = $g_dbConn->query($sql, $userID);

		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$row = $rs->fetchRow();
			$this->userID 		= $row[0];
			$this->userName 	= $row[1];
			$this->firstName	= $row[2];
			$this->lastName		= $row[3];
			$this->email		= $row[4];
			$this->dfltRole		= $row[5];
			$this->dfltClass	= $row[6];
			$this->role			= $row[7];
			$this->userClass	= $row[8];
			$this->not_trained	= $row[9];
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
		}

		$rs = $g_dbConn->query($sql, $this->getUserID());
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

	function sendUserEmail($subject, $baseMsg, $pwd='')
	{
		global $g_siteURL, $g_reservesEmail;

		$msg = ereg_replace("\?url", $g_siteURL, $baseMsg);
		$msg = ereg_replace("\?username", $this->getUsername(), $msg);
		$msg = ereg_replace("\?password", $pwd, $msg);
		$msg = ereg_replace("\?deskemail", $g_reservesEmail, $msg);

		
		$to      = $this->getEmail();
		$headers = "From: $g_reservesEmail" . "\r\n" .
		   "Reply-To: $g_reservesEmail" . "\r\n" .
		   "X-Mailer: PHP/" . phpversion();
		
		mail($to, $subject, $msg, $headers);
	}
		
	
	function addNotTrained()
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "INSERT INTO not_trained (user_id, permission_level) VALUES (!, 0)";					
		}

		$rs = $g_dbConn->query($sql, $this->getUserID());

		//reload user object
		$this->getUserByID($this->getUserID());	
	}
	
	function removeNotTrained()
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "DELETE FROM not_trained WHERE user_id = !";					
		}
		
		$rs = $g_dbConn->query($sql, $this->getUserID());

		//reload user object
		$this->getUserByID($this->getUserID());		
	}
	
	function isNotTrained() { return $this->not_trained; }
	
	
	/**
	 * @return array - Array of CourseInstances
	 * @param string $access_level (optional) Level of access to CIs (student/proxy/instructor/etc)
	 * @param string $act_date (optional) CIs activated on or after this date
	 * @param string $exp_date (optional) CIs expiring before or on this date
	 * @param string $ci_status (optional) CIs with this status
	 * @param string $enrollment_status (optional) Enrollment status of this user [only really matters for students (access_level=0)]	
	 * @param int $dept_id (optional) CIs in this department
	 * @desc Returns an array of CI objects for this user with the given qualifications. If a parameter is not specified, no restriction is placed.  This is the catch-all logic to get CIs to be used by public methods with selective criteria. 
	 */
	public function fetchCourseInstances($access_level=null, $act_date=null, $exp_date=null, $ci_status=null, $enrollment_status=null, $dept_id=null) {
		global $g_dbConn, $g_permission;
		
		//format access - if trying to set the access level, but provided an improper level, then unset it
		if(!empty($access_level) && !in_array($access_level, $g_permission)) {
			$access_level = null;	//not a valid access level, do not restrict
		}
		//format dates
		if(!empty($act_date)) { 
			$act_date = date("Y-m-d", strtotime($act_date));
		}
		if(!empty($exp_date)) { 
			$exp_date = date("Y-m-d", strtotime($exp_date));
		}

		//build query
		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql = "SELECT DISTINCT ca.course_instance_id
						FROM course_aliases AS ca
							JOIN access AS a ON a.alias_id = ca.course_alias_id
							JOIN course_instances AS ci ON ci.course_instance_id = ca.course_instance_id
							JOIN courses AS c ON c.course_id = ca.course_id
							JOIN departments AS d ON d.department_id = c.department_id
						WHERE a.user_id = ".$this->userID;
				
				//add restrictions
				if(!empty($access_level)) {
					$sql .=	" AND a.permission_level = ".$g_permission[$access_level];
				}
				if(!empty($enrollment_status)) {
					$sql .= " AND a.enrollment_status = '$enrollment_status'";
				}
				if(!empty($ci_status)) {
					$sql .= " AND ci.status = '$ci_status'";
				}
				if(!empty($act_date)) {
					$sql .= " AND ci.activation_date <= '$act_date'";
				}
				if(!empty($exp_date)) {
					$sql .= " AND ci.expiration_date >= '$exp_date'";
				}
				if(!empty($dept_id)) {
					$sql .= " AND d.department_id = '$dept_id'";
				}
				
				//finish off with sorting
				$sql .= " ORDER BY d.abbreviation ASC, c.course_number ASC, ca.section ASC, ci.year DESC, ci.activation_date DESC";				
		}

		//query
		$rs = $g_dbConn->query($sql);
		if(DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
					
		$course_instances = array();
		while($row = $rs->fetchRow()) {
			$course_instances[$row[0]] = new courseInstance($row[0]);
		}
		
		return $course_instances;
	}
}
?>