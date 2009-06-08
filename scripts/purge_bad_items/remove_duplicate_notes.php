#!/usr/local/bin/php -q
<?php
/*
	This script will remove exact duplicates from the notes table created when remove_duplicate_items updated the table
*/	
	echo "remove_duplicate_notes.php\n";

	set_include_path(get_include_path() . PATH_SEPARATOR . realpath('../..').'/');	
	require_once('secure/config.inc.php');
	
	global $dsn;
			
	$fp  = fopen('remove_duplicate_notes.sql', 'w');
	
	
	fwrite($fp, "START TRANSACTION;\n");
	
	//remove dups inserted into the reserves table
	$sql_rv 	= "SELECT note_id, type, target_id, target_table, note FROM notes";
	$sql_dup	= "SELECT note_id FROM notes WHERE note_id <> ? AND type = ? AND target_id = ? AND target_table = ? AND note = ?";
	$processed = array();
	$i = 0;
	
	$keeps = $g_dbConn->query($sql_rv);
	while($keep = $keeps->fetchRow(DB_FETCHMODE_ASSOC)) 
	{		
		if(!in_array($keep['note_id'], $processed))
		{						
			$dups = $g_dbConn->query($sql_dup, $keep);
			while($dup = $dups->fetchRow(DB_FETCHMODE_ASSOC)) 		
			{				
				fwrite($fp, "DELETE FROM notes WHERE note_id = {$dup['note_id']};\n");
				$processed[] = $dup['note_id'];
			}
            $processed[] = $keep['note_id'];

		} else {
			echo "already processed. skipping\n";				
		}
	}
	
	
	fwrite($fp, "COMMIT;\n");	
	fclose($fp);
	
	
	echo "excuting sql file remove_duplicate_reserves.sql ....\n";	
	exec("mysql -u{$dsn['username']} -p{$dsn['password']} {$dsn['database']} < remove_duplicate_notes.sql");
	echo "done\n";

//END MAIN	
?>