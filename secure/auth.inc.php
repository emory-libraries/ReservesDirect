<?php
/*******************************************************************************
auth.inc.php
Authentication layer to be included in all pages with secure content

Created by Dmitriy Panteleyev (dpantel@emory.edu)

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


/** 
 * @desc Attempt to authenticate a user who is logging in (username/pass in request) or reauthenticate user has already logged in.  On success: initializes a global user object ($u) for the proper user class.  On failure: redirects to login page.
 */


require_once('secure/classes/user.class.php');
require_once('secure/classes/users.class.php');
require_once('secure/classes/ldapAuthN.class.php');

//start or resume session
if(session_id()=='') {
	session_start();
}
if(empty($_SESSION['username'])) {
	if (!authBySecretKey($_REQUEST['authKey'])) {
		//if passed a username/password, assume that user is trying to log in
		if(!empty($_REQUEST['username']) && (!empty($_REQUEST['pwd']) || $g_authenticationType == 'DEMO')) {
			//switch on authentication type
			switch($g_authenticationType) {
				case 'DEMO':
					$permission = (isset($_REQUEST['permission'])) ? $_REQUEST['permission'] : null;
					authByDemo($_REQUEST['username'], $permission);		
				break;
				
				case 'LDAP':
					authByLDAP($_REQUEST['username'], $_REQUEST['pwd']);		
				break;
				
				case 'StandAlone':
					authByDB($_REQUEST['username'], $_REQUEST['pwd']);
				break;
				
				default:
					authByAny($_REQUEST['username'], $_REQUEST['pwd']);
			}
		}
	}
}
//assume that our sessions are done server-side and that the files are secure
//if this is not the case, then a more-robust mechanism must be used.
if(!empty($_SESSION['username']) && !empty($_SESSION['userclass'])) {
	//if the session has the username and the userclass defined,
	//then we consider this user authenticated
	//initialize the GLOBAL user object
	$usersObject = new users();
	$u = $usersObject->initUser($_SESSION['userclass'], $_SESSION['username']);	
}
else {	//user is not authenticated; show login page (+ error msg if needed)
	include("destroySession.inc.php");	//destroy any current session data (just in case)

	$login_error = !empty($_REQUEST['username']) ? true : false;	//flag as login error if user provided a username
	require_once('login.php');	//show login page
	
	exit;	//IMPORTANT - do not evaluate anything else
}


/**
 * @return void
 * @param boolean $authenticated
 * @param user obj $user (optional)
 * @desc If $authenticated is TRUE and $user is initialized, then sets the session vars that will be later used for quick re-authentication;  If $authenticated is FALSE, then unsets those session vars.
 */
function setAuthSession($authenticated, $user=null) {
	if($authenticated && ($user instanceof user)) {
		//since the user's role/auth-status is changing, change session ID (attempt to prevent session fixing)
		session_regenerate_id();
		$_SESSION['username'] = $user->getUsername();
		$_SESSION['userclass'] = $user->getUserClass();
		$user->setLastLogin();	//mark user's last login
	}
	else {	//not authenticated, unset all auth session vars
		$_SESSION['username'] = $_SESSION['userclass'] = null;
	}
}


/**
 * @return boolean
 * @param string $username Username
 * @param string $password Password
 * @desc Attempt to auth against an NT Active Domain. Returns true on success, and sets $_SESSION['username'], $_SESSION['userclass'];  Returns false on failure, and sets both session vars to null;
 */
/**********************
	NOT IMPLEMENTED
**********************/
function authByNTDom($username, $password) { 
	setAuthSession(false);
	return false;
}


/**
 * @return boolean
 * @param string $username Username
 * @param string $password Password
 * @desc Attempt to auth against LDAP. Returns true on success, and sets $_SESSION['username'], $_SESSION['userclass'];  Returns false on failure, and sets both session vars to null;
 	NOTE: requires array of ldap info as $g_ldap;
 */
function authByLDAP($username, $password) {
	global $g_lda, $g_permission;
	$ldap = new ldapAuthN();
	$user = new user();
	
	//try to authenticate against ldap
	if($ldap->auth($username, $password)) {
		//passed authentication, try to get user from DB
		if(!$user->getUserByUserName($username)) {	//if user record not found in our DB, attempt to create one
			//get directory info
			$user_info = $ldap->getUserInfo();
			//create a new record with directory info
			//(LDAP returns username in caps, so strtolower() it)
			$user->createUser(strtolower($user_info[$g_ldap['canonicalName']][0]), $user_info[$g_ldap['firstname']][0], $user_info[$g_ldap['lastname']][0], $user_info[$g_ldap['email']][0], 0);
		}

                //Turn off student access
                if($user->getRole() != $g_permission['admin']){
                    setAuthSession(false);
                    return false;
                }
				
		//user is now authenticated, set the session vars
		setAuthSession(true, $user);
		return true;
	}
	else {
		//unset these
		setAuthSession(false);
		return false;
	}
}


/**
 * @return boolean
 * @param string $username Username
 * @param string $password Password
 * @desc Attempt to auth against local database. Returns true on success, and sets $_SESSION['username'], $_SESSION['userclass'];  Returns false on failure, and sets both session vars to null;
 */
function authByDB($username, $password) {
	$user = new user();
	//attempt to authenticate user against our database
	if($user->getUserByUserName_Pwd($username, md5($password))) {
		//user is now authenticated, set the session vars
		setAuthSession(true, $user);
		return true;
	}
	else {
		//unset the session vars
		setAuthSession(false);
		return false;
	}
}


/**
 * @return boolean
 * @param string $username Username
 * @param string $password Password
 * @desc Attempt to auth against LDAP, NT Domain, & local DB (in that order). Returns true on success, and sets $_SESSION['username'], $_SESSION['userclass'];  Returns false on failure, and sets both session vars to null;
 */
function authByAny($username, $password) {
	if(authByLDAP($username, $password)) {	//try ldap
		return true;	//success
	}
	elseif(authByNTDom($username, $password)) {	//try nt domain
		return true;	//success
	}
	else {
		return authByDB($username, $password);	//try standalone db
	}
}


/**
 * @return boolean
 * @param string $qs QueryString data
 * @desc Attempt to auth from external system.  Compare passed values against secret key
 * 		$qs key values: u username, 
 * 						sys  external system identifier
 * 						t	timestamp  seconds since ‘00:00:00 1970-01-01 UTC’
 * 						key md5 of concatenation of above
 */
function authBySecretKey($qs_data) {
	if (!is_null($qs_data)) {
		global $g_trusted_systems, $g_permission;
	
	if (is_null($qs_data)) return false;
		
		parse_str(base64_decode($qs_data), $auth_data);
		
		$trusted_system_key = $g_trusted_systems[$auth_data['sys']]['secret'];
		$timeout = $g_trusted_systems[$auth_data['sys']]['timeout'];
	
		$timestamp = new DateTime($auth_data['t']);
		$expire = new DateTime(time());
		$expire->modify("+$timeout minutes");
	
		if ($timestamp >  $expire)
			return false; //encoded timestamp is too old
		else {
			$user = new user();
		
			if ($user->getUserByUserName($auth_data['u']) == false || $user->getRole() > $g_permission['instructor'])
				return false;	//do not allow privileged users access without login
		
			$verification = $auth_data['u'] . $auth_data['t'];
			
			$verification .= $auth_data['sys'];
			$verification .= $trusted_system_key;			
			
			if (hash("sha256", $verification) == $auth_data['key'])
			{
				setAuthSession(true, $user);
				return true;
			}
		}
	}
	
	return false;
}

/**
 * @return boolean
 * @param string $username Username
 * @param string $password Password
 * @desc Attempt to auth against local database. Returns true on success, and sets $_SESSION['username'], $_SESSION['userclass'];  Returns false on failure, and sets both session vars to null;
 */
function authByDemo($username, $permission) {
        global $g_permission;
        $perm = array_flip($g_permission);

        $user = new user();

        //attempt to authenticate user against our database
        if($user->getUserByUserName($username)) {
                session_regenerate_id();
                $_SESSION['username']  = $user->getUsername();
                $_SESSION['userclass'] = $user->getUserClass();
                $user->setLastLogin();  //mark user's last logi         
        }
        else {
                $user->createUser($username, '', '', '', $permission);
                $_SESSION['username']  = $username;
                $_SESSION['userclass'] = $permission;
        }
        return true;
}
?>
