<?php
	//paths - MAY NEED TO BE CHANGED
	define('FEED_FILE', '-------CHANGEME------');
	define('RD_ROOT', realpath('..').'/');
	
	//get config
	//will init $g_dbConn PEAR::DB object
	require_once(RD_ROOT.'secure/config.inc.php');

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
				$sql = "INSERT INTO ils_requests (date_added, ils_request_id, barcode, user_net_id, user_ils_id, ils_course) VALUES (NOW(), '".trim($data[0])."', '".trim($data[3])."', '".trim($data[5])."', '".trim($data[6])."', '".trim($data[8])."')";		
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
	
	echo "DONE! Processed $processed_count lines of feed ($nonblank_count non-empty); added $insert_count rows to DB.";
?>