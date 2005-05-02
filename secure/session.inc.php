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
	
if (!$user->getUserByUserName($args[0]))
{
	$user->createUser($args[0], "", "", "", 0);  //we allow any authorized user to enter with default role of student
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


if (!isset($_SESSION['skin'])){
	$_SESSION['skin'] = isset($_REQUEST['skin']) ? $_REQUEST['skin'] : 'general';
	$_SESSION['css'] = common_getSkin($_SESSION['skin']);
}

if (!isset($_SESSION['debug']) && isset($_REQUEST['debug'])) $_SESSION['debug'] = true; 

$user->setLastLogin();

unset($user);
?>
