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
		
        // if they have an invalid authcookie, bounce them to the login page.
        // base64_encode the requested URL and send that with them (we'll
        // decode it later when they get to the index and send them on their way)
		if (trim($userName) == "")
		{
            $req_url = base64_encode($_SERVER['REQUEST_URI']);
            include("destroySession.inc.php");
			header("Location: index.php?redirect=$req_url");
			exit;
		}	
		
		if (!$user->getUserByUserName($userName))
		{
			$user->createUser($userName, "", "", "", 0);  //we allow any authorized user to enter with default role of student
		}
	break;

	case 'LDAP':

        $userName = (isset($_REQUEST['username'])) ? $_REQUEST['username'] : $_SESSION['username'];
        $pwd      = (isset($_REQUEST['pwd'])) ? ($_REQUEST['pwd']) : $_SESSION['pwd'];
        // user attributes to seed RD profile with
	    $attributes = array($ldap["email"], $ldap["canonicalName"], $ldap["lastname"], $ldap["firstname"]);
        // filter to use when binding to ldap
	    $filter="(" . $ldap["canonicalName"] . "=" . $userName. ")";

    	// Connect to LDAP 
	    $connect = ldap_connect($ldap["host"], $ldap["port"])
	       or die("Couldn't connect to LDAP!");

        // Set ldap protocol version
    	ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, $ldap["version"])
            or die("Failed to set LDAP protocol version to" . $ldap["version"]);

        // Bind to LDAP using credentials from login screen
    	$bind = ldap_bind($connect, $ldap["canonicalName"] . "=" . $userName . "," . $ldap["basedn"],"$pwd")
    	   or die("Couldn't bind to LDAP!");

        if (trim($userName) == "")  
        {
            //invalid user account direct to logout.php to destroy session and return to login
            header("Location: secure/logout.php");
            exit;
        }

        if (!$user->getUserByUserName($userName)) 
        {
            // retrieve user ldap attributes for new user profile
        	$result = ldap_search($connect, $ldap["basedn"], $filter, $attributes);
        	$info = ldap_get_entries($connect, $result);
            $user->createUser($userName, $info[0][$ldap["firstname"]][0], $info[0][$ldap["lastname"]][0], $info[0][$ldap["email"]][0], 0);  
                    //authorized users get student privileges by default
        }

	    // Close the ldap connection 
	    ldap_unbind($connect);

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

if(!isset($_SESSION['userID'])) {
    $_SESSION['userID'] = $user->userID;
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

if (!isset($_SESSION['debug']) && isset($_REQUEST['debug'])) $_SESSION['debug'] = true; 

$user->setLastLogin();

unset($user);
?>
