<?
session_start();
//session_unset();

//find AuthCookieHandler
$keys = array_keys($_REQUEST);

for($ndx=0;$ndx<count($keys);$ndx++){
	if (eregi("AuthCookieHandler", $keys[$ndx])){ 	break;	}
}
$args = explode(':', $_REQUEST[$keys[$ndx]]); //split out username

$_SESSION['authKey'] = $keys[$ndx];
$user = new user();
	
$userName = $args[0];

if (trim($userName) == "")
{
	//invalid user account direct to logout.php to destroy session and return to login
	header("Location: secure/logout.php");
	exit;
}	

if (!$user->getUserByUserName($userName))
{
	$user->createUser($userName, "", "", "", 0);  //we allow any authorized user to enter with default role of student
}
	

// Use $HTTP_SESSION_VARS with PHP 4.0.6 or less

if (!isset($_SESSION['username'])) {
	$_SESSION['username'] = $user->getUsername();
}

if (!isset($_SESSION['userClass'])) {
	$_SESSION['userClass'] = $user->getUserClass();
}

if (!isset($_SESSION['pageStack'])) {
	$_SESSION['pageStack'] = array();
}
//array_push($_SESSION['pageStack'], $_REQUEST['QUERY_STRING']);

$skins = new skins();

/* if their session doesn't have the stylesheet set, set it here.
 * $_REQUEST['skin'] will prefer the *cookie* called 'skin' over the
 * GET arguments -- if they show up with a cookie, but no session,
 * the contents of the cookie will be used to write $_SESSION['css'].
 * if they show up with no cookie and no session, the contents of
 * $_GET['skin'] will be used. if this breaks, it's probably because the
 * GPC priority has changed!
 */

if (!isset($_SESSION['css'])){
	$userSkin = isset($_REQUEST['skin']) ? $_REQUEST['skin'] : 'general';
    setcookie("skin", $userSkin);
	$_SESSION['css'] = $skins->getSkin($userSkin);
}

if (!isset($_SESSION['debug']) && isset($_REQUEST['debug'])) $_SESSION['debug'] = true; 

$user->setLastLogin();

unset($user);
?>
