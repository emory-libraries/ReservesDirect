<?php
/*******************************************************************************
ldapAuthN.class.php
Methods to allow ldap authentication

Created by Kyle Fenton (kyle.fenton@emory.edu)

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

class ldapAuthN	{
	/**
	 * Declaration
	 */
	private $conn;	//LDAP resource link identifier
	private $username;	//username
	private $password;	//password
	private $user_info;	//a subset of the user's LDAP record
	private $user_authed;	//boolean stores the authentication result
	
	
	/**
	 * @return void
	 * @desc constructor
	 */
	public function ldapAuthN() {
		$this->conn = $this->user_info = $this->username = $this->password = null;
		$this->user_authed = false;
	}

	
	/**
	 * @return boolean
	 * @param string $user Username
	 * @param string $pass Password
	 * @desc Attempts to authenticate the user against LDAP. Returns true/false
	 */
	public function auth($user, $pass) {
		//check if authentication has already been run
		if(($user==$this->username) && ($pass==$this->password)) {	//username and password match
			return $this->user_authed;	//return previous result
		}
		
		//if the username is new, make sure it's not blank
		//do not require the pass, in case some systems allow non-passworded accounts (i have no idea why they would)
		if(empty($user) || empty($pass)) {
			return false;	//return false if it is
		}
		else {	//else store the user and pass
			$this->username = $user;
			$this->password = $pass;
		}
		
		//establish a connection
		if(!$this->connect()) {
//
//not sure if should trigger error
//or try another auth method
//
			trigger_error('LDAP: connection failed.', E_USER_ERROR);
		}
		
		//search for the user in the directory
		if(!$this->search()) {	//user not found in directory
			return false;
		}
		
		//if user is found in dir, attempt to authenticate w/ provided password
		$this->user_authed = ldap_bind($this->conn, $this->user_info['dn'], $this->password);
		
		//close connection and clean up
		$this->disconnect();
		
		return $this->user_authed;
	}
	
	
	/**
	 * @return array
	 * @desc returns the array of user info gathered from the directory
	 */
	public function getUserInfo() {
		return $this->user_info;
	}	
	
	
	/**
	 * @return boolean
	 * @desc Attempt to connect to LDAP server
	 */
	protected function connect() {
		global $g_ldap;
		
		//determine if trying to go through SSL
		//this is a bit of a hack, b/c we're just checking the port #
		//	if the port matches "secure ldap" port, prepend "ldaps://" to hostname
		$host = ($g_ldap['port'] == '636') ? 'ldaps://'.$g_ldap['host'] : $g_ldap['host'];

		//connect	
		$conn = ldap_connect($host, $g_ldap['port']);		
		if($conn !== false) {
			ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, $g_ldap['version']);	//set version
			$this->conn = $conn;	//save resource link
			return true;
		}
		//else
		return false;
	}
	
	
	/**
	 * @return void
	 * @desc disconnect from server and do a little cleanup
	 */
	protected function disconnect() {
		ldap_close($this->conn);
		unset($this->conn);
	}
	
	
	/**
	 * @return boolean
	 * @desc Searches the user in the directory. Set the info, if user is found
	 */
	protected function search() {
		global $g_ldap;
		
		$this->user_info = null;	//clean out any previous info
		
		//bind to the LDAP w/ search credentials
		if(ldap_bind($this->conn, $g_ldap['searchdn'], $g_ldap['searchpw'])) {	//if bound successfully, search for the user			
			//set up some search criteria
			$filter = $g_ldap['canonicalName'].'='.$this->username;	//search only for a person with the given username
			//ask for the dn, uid, first & last name, and email (dn is fetched automatically, but might as well be complete)
			$fetch_attribs = array('dn', $g_ldap['canonicalName'], $g_ldap['firstname'], $g_ldap['lastname'], $g_ldap['email']);
						
			//search
			$result = ldap_search($this->conn, $g_ldap['basedn'], $filter, $fetch_attribs);			
			if($result !== false) {
				$info = ldap_get_entries($this->conn, $result);	//get the info
				ldap_free_result($result);	//free memory
				
				if($info['count'] == 1) {	//if only one record returned, then successfully found the user
					$this->user_info = $info[0];	//grab the first record
					return true;
				}
				elseif ($info['count'] == 0) {
					echo $this->username . " not found in LDAP";
					return false;
				}
			}
		}
		return false;
	}
}
?>
