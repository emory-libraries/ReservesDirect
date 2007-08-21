#!/usr/bin/php
<?php
	//paths - MAY NEED TO BE CHANGED
	define('FEED_FILE', '/home/euclid/reserves_jason.txt');
	define('RD_ROOT', realpath('..').'/');
	
	//get config
	//will init $g_dbConn PEAR::DB object
	require_once(RD_ROOT.'secure/config.inc.php');

	//Remove old request over 1 month old
	$lastmonth = date("Y-m-d", mktime(0, 0, 0, date("m")-1, date("d"),   date("Y")));
	$sql = "DELETE FROM ils_requests WHERE date_added < '$lastmonth'";
	
	$rs = $g_dbConn->query($sql);
	if(DB::isError($rs)) {
		echo "<br />ERROR Could not delete old entries: {$rs->getMessage()}";
		die(-1);
	}
	
	
	//read in feed
	$processed_count = 0;
	$nonblank_count = 0;
	$insert_count = 0;
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

				//build insert statement
				//whether or not one of the fields gets populated depends on one of the variables in the feed
				if((stripos($data[1], 'WRSRV_BOTH') !== false) && !empty($data[11])) {	//should have requested loan period
					$sql = "INSERT INTO ils_requests (date_added, ils_request_id, ils_control_key, user_ils_id, user_net_id, ils_course, requested_loan_period) VALUES (NOW(), '".trim($data[0])."', '".trim($data[4])."', '".trim($data[5])."', '".trim($data[6])."', '".trim($data[8])."', '".trim($data[11])."')";	
				}
				else {	//no loan period info
					$sql = "INSERT INTO ils_requests (date_added, ils_request_id, ils_control_key, user_ils_id, user_net_id, ils_course) VALUES (NOW(), '".trim($data[0])."', '".trim($data[4])."', '".trim($data[5])."', '".trim($data[6])."', '".trim($data[8])."')";
				}
				
				//query
				$rs = $g_dbConn->query($sql);
				if(DB::isError($rs)) {
					if($rs->getMessage() == "DB Error: already exists") {
						//already inserted row with this ils_request_id
						continue;
					}
					else {
						echo "<br />ERROR: {$rs->getMessage()}";
						die(-1);
					}
				}
				
				$insert_count++;
			}
		}
	}
	
	echo "DONE! Processed $processed_count lines of feed ($nonblank_count non-empty); added $insert_count rows to DB.\n\n";
?>
