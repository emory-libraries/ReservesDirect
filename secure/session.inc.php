<?
session_start();
//session_unset();

$user = new user();

switch ($g_authenticationType)
{
	case 'AuthCookie':
		//find AuthCookieHandler
		$keys = array_keys($_REQUEST);
		
		for($ndx=0;$ndx<count($keys);$ndx++){
			if (eregi("AuthCookieHandler", $keys[$ndx])){ 	break;	}
		}
		$args = explode(':', $_REQUEST[$keys[$ndx]]); //split out username
		
		$_SESSION['authKey'] = $keys[$ndx];
		
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
	break;
	
	case 'StandAlone':
	default:
		$userName = (isset($_REQUEST['username'])) ? $_REQUEST['username'] : $_SESSION['username'];
		$pwd	  = (isset($_REQUEST['pwd'])) ? md5($_REQUEST['pwd']) : $_SESSION['pwd'];

		if (!isset($_SESSION['pwd'])) {
			$_SESSION['pwd'] = $pwd;
		}
		
		if (!$user->getUserByUserName_Pwd($userName, $pwd))
		{
			if (isset($_REQUEST['username']))
				$error = "?1";
			
			header("Location: login.php$error");
			exit;
		}	
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

if (!isset($_SESSION['debug']) && isset($_REQUEST['debug']) && $user->getDefaultRole() == $g_permission['admin']) $_SESSION['debug'] = true; 

$user->setLastLogin();

unset($user);
?>
