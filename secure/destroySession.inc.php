<?
/*
		THIS FILE HAS BEEN MODIFIED TO WORK WITH THE RESERVES DEMO 
		
		Authorization has been removed.  It should never be used in a full distribution.
		
*/

session_start();
$host = ".".$_SERVER['HTTP_HOST'];

$domain = ereg_replace("/secure/[A-z]*\.php", "", $_SERVER['SCRIPT_NAME']);

$authKey = (string)$_SESSION['authKey'];

setcookie($authKey, "", time() -3600, $domain, $host, 1);
   
session_unset();
if(isset($PHPSESSID)) {
	session_destroy();
}


header("Location: ../index.html"); /* Redirect browser */
exit;
?>
