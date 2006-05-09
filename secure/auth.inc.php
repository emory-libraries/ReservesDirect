<?php
/*******************************************************************************
auth.inc.php
Authentication layer to be included in all pages with secure content

Created by Dmitriy Panteleyev (dpantel@emory.edu)

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

//if passed a username/password, assume that user is trying to log in
if(!empty($_REQUEST['username']) && !empty($_REQUEST['pwd'])) {
	//switch on authentication type
	switch($g_authenticationType) {
		/**********************
			NOT IMPLEMENTED
		***********************
		case 'NT':
			authByNTDom($_REQUEST['username'], $_REQUEST['pwd']);
		break;
		***********************/
		
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
	global $g_ldap;
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
?>