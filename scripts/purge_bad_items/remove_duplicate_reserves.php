#!/usr/local/bin/php -q
<?php
/*
	This script will remove duplicates from the reserves table created when remove_duplicate_items updated the table
	and restore the unique item_id, course_instance_id constraint
*/	
	echo "remove_duplicate_reserves.php\n";

	set_include_path(get_include_path() . PATH_SEPARATOR . realpath('../..').'/');	
	require_once('secure/config.inc.php');
	
	global $dsn;
			
	$fp  = fopen('remove_duplicate_reserves.sql', 'w');	
	
	
	fwrite($fp, "START TRANSACTION;\n");
	
	//remove dups inserted into the reserves table
	$sql_rv 	= "SELECT reserve_id, course_instance_id, item_id FROM reserves";
	$sql_dup	= "SELECT reserve_id FROM reserves WHERE reserve_id <> ! AND course_instance_id = ! AND item_id = !";
	$processed = array();
	$i = 0;
	
	$keeps = $g_dbConn->query($sql_rv);
	while($keep = $keeps->fetchRow(DB_FETCHMODE_ASSOC)) 
	{		
echo "process reserve {$keep['reserve_id']} ci: {$keep['course_instance_id']} i: {$keep['item_id']}\n";
		if(!in_array($keep['reserve_id'], $processed))
		{						
			$j = 0;
			$dups = $g_dbConn->query($sql_dup, $keep);
			while($dup = $dups->fetchRow(DB_FETCHMODE_ASSOC)) 		
			{				
echo "found dup.  {$dup['reserve_id']}\n";
				fwrite($fp, "DELETE FROM reserves WHERE reserve_id = {$dup['reserve_id']};\n");
				fwrite($fp, "UPDATE user_view_log SET reserve_id = {$keep['reserve_id']} WHERE reserve_id = {$dup['reserve_id']};\n");
				$processed[] = $dup['reserve_id'];
				$j++;
			}

		} else {
			echo "already processed. skipping\n";				
		}
	}
	
	
	fwrite($fp, "ALTER TABLE reserves ADD UNIQUE unique_constraint (course_instance_id, item_id);\n");	
	fwrite($fp, "COMMIT;\n");
	
	fclose($fp);
	
	echo "deleted $j rows\n";
	
	echo "excuting sql file remove_duplicate_reserves.sql ....\n";	
	exec("mysql -u{$dsn['username']} -p{$dsn['password']} {$dsn['database']} < remove_duplicate_reserves.sql");
	echo "done\n";

//END MAIN	
?>