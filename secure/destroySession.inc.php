<?
session_start();
$host = ".".$_SERVER['HTTP_HOST'];

$domain = ereg_replace("/secure/[A-z]*\.php", "", $_SERVER['SCRIPT_NAME']);

$authKey = (string)$_SESSION['authKey'];

setcookie($authKey, "", time() -3600, $domain, $host, 1);
   
session_unset();
if(isset($PHPSESSID)) {
	session_destroy();
}

?>
