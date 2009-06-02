#!/usr/local/bin/php -q
<?php
/*
	This script will run the new_reserves_ndx.sql file and remove bad indexes and add good ones
*/	
	echo "new_reserves_ndx.php\n";
	
	set_include_path(get_include_path() . PATH_SEPARATOR . realpath('../..').'/');	
	require_once('secure/config.inc.php');
	
	global $dsn;	
	
	echo "excuting sql file new_reserves_ndx.sql ....\n";	
	exec("mysql -u{$dsn['username']} -p{$dsn['password']} {$dsn['database']} < new_reserves_ndx.sql");
	echo "done\n";
//END main
?>
