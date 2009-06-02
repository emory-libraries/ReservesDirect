#!/usr/local/bin/php -q
<?php
/*
	This script will load the control_key lookup table
*/	
	echo "load_control_key_lookup.php\n";

	set_include_path(get_include_path() . PATH_SEPARATOR . realpath('../..').'/');	
	require_once('secure/config.inc.php');
	
	global $dsn;	
	
	echo "excuting sql file control_keys.sql ....\n";	
	exec("mysql -u{$dsn['username']} -p{$dsn['password']} control_keys < control_keys.sql");
	echo "done\n";
//END main
?>
