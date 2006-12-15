<?php
/*******************************************************************************
session.inc.php

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
require_once("secure/classes/skins.class.php");

session_start();

//
// Error Handling & Debugging
//

//if trying to initiate debugging mode, save that request to session
if(!isset($_SESSION['debug']) && isset($_REQUEST['debug'])) $_SESSION['debug'] = true; 
//if debug-mode is set in session
if(isset($_SESSION['debug'])) {
	error_reporting(E_ALL);	//report all errors
	//and print them to screen
	echo "<pre>";
	print_r($_REQUEST);
	echo "</pre><hr />";
} else {	//no debug-mode set
	error_reporting(0);	//do not report any errors that are not explicitly thrown
	$old_error_handler = set_error_handler("common_ErrorHandler");	//set up a custom error handler
}


//
// Skins
//

$skins = new skins();

/* if their session doesn't have the stylesheet set, set it here.
 * first we look for a cookie that we may have set in a previous and
 * now-expired session. if that's not there, pull it from the get args.
 * it's okay if it's blank, we just get the default skin from getSkin()
 * in that case.
 */

if (!isset($_SESSION['css'])){
	$userSkin = isset($_COOKIE['skin']) ? $_COOKIE['skin'] : $_GET['skin'];
    setcookie("skin", $userSkin);
	$_SESSION['css'] = $skins->getSkin($userSkin);
}
?>
