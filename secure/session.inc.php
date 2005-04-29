<?
/*
		THIS FILE HAS BEEN MODIFIED TO WORK WITH THE RESERVES DEMO 
		
		Authorization has been removed.  It should never be used in a full distribution.
		
*/
session_start();

//find AuthCookieHandler
$authKey = "ReservesDirectDemo";
setcookie($authKey, "", null, $domain, $host, 1);

$_SESSION['authKey'] = $authKey;
$user = new user();

$username = (isset($_REQUEST['user'])) ? $_REQUEST['user'] : $_SESSION['username'];
	
if (!$user->getUserByUserName($username))
{
	$user->createUser($_REQUEST['user'], "", "", "", $_REQUEST['permission']);  //we allow any authorized user to enter with default role of student
}
//else 
	//if (isset($_REQUEST['permission']))
		//$user->setDefaultRole($_REQUEST['permission']);

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


if (!isset($_SESSION['skin'])){
	$_SESSION['skin'] = isset($_REQUEST['skin']) ? $_REQUEST['skin'] : 'general';
	$_SESSION['css'] = common_getSkin($_SESSION['skin']);
}

if (!isset($_SESSION['debug']) && isset($_REQUEST['debug'])) $_SESSION['debug'] = true; 

$user->setLastLogin();

unset($user);
?>
