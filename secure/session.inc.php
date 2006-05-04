<?php
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