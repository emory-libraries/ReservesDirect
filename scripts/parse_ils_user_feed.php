#!/usr/bin/php
<?php
	//paths - MAY NEED TO BE CHANGED
	define('FEED_FILE', '/home/jbwhite/data/user_keys_jason.txt');
	define('RD_ROOT', realpath('..').'/');
	
	require_once(RD_ROOT."/secure/classes/user.class.php");
	
	//get config
	//will init $g_dbConn PEAR::DB object
	require_once(RD_ROOT.'secure/config.inc.php');

	//File pointers
	$u_key 	= 0;
	$net_id	= 1;
	
	
	//read in feed
	$processed_count = 0;
	$unmatched_count = 0;
	$matched_count = 0;
	
	if(is_readable(FEED_FILE)) {	//make sure that file is readable
		if(($feed = file(FEED_FILE)) !== false) {	//read file into array, where each element is a single line
			foreach($feed as $line) {
				$processed_count++;

				//skip empty lines
				if($line == "\n") {
					continue;
				}
				
				$nonblank_count++;		
				
				//values are separated by |, split them
				$data = explode('|', $line);
				
				//search db and return user role
				
				$user = new User();
				if ($user->getUserByUsername($data[$net_id]) == FALSE)
				{
					//echo $data[$net_id] . " did not match \n";
					$unmatched_count++;
				} else {
					$user->setExternalUserKey($data[$u_key]);
					$matched_count++;
				}				
			}
		}
	}
	
	echo " unmatched $unmatched_count matched $matched_count total processed $processed_count \n";
?>
