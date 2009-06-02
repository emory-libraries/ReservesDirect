#!/usr/local/bin/php -q
<?php
/*
	This script will remove duplicates from the items table
	Duplicates are:	
		matches on local_control_key
		matches on title, author, volume_title, pages_times, performer, home_library, private_user_id, item_group, item_type and url
	Kept Item is updated with data which exists in matches but not in kept record before update

	Outputs report of duplicates without comparing url for additional analysis
	
	*****CHANGES ARE MADE DIRECTLY TO THE DB.  SQL OUTPUT IS SIMPLY A LOG OF WHAT HAPPENS***********
*/	
	echo "remove_duplicate_items.php\n";

	set_include_path(get_include_path() . PATH_SEPARATOR . realpath('../..').'/');	
	require_once('secure/config.inc.php');
	
	global $dsn;
	
	//order by control key ASC will process all null control_keys first matching by title, author etc, then when non-null control_keys are processed we can pick up any
	//duplicates which have non exact title, author etc matches.  Items linked to the ils typically have more complete values
	$sql = "SELECT item_id, ISBN, ISSN, OCLC, status, local_control_key, content_notes, source, 
			title, author, volume_title, pages_times, performer, home_library, private_user_id, 
			item_group, item_type, url, volume_edition
		FROM items WHERE item_type <> 'HEADING' ORDER BY local_control_key";
	
	$match_dup_control_key = "SELECT * FROM items WHERE item_id <> ! AND local_control_key = ? AND ( private_user_id = ? OR private_user_id IS NULL) AND item_type = ?";
	
	$match_dup_sql	= "SELECT * FROM items WHERE item_id <> ! 
						AND title = ? AND author = ? AND local_control_key = ? AND volume_title = ? AND pages_times = ? AND performer = ?
						AND home_library = ? AND private_user_id = ? AND item_group = ? AND item_type = ? AND url = ?";

	$match_dup_wo_url	= "SELECT item_id FROM items WHERE item_id <> ! 
							AND title = ? AND author = ? AND volume_title = ? AND pages_times = ? AND performer = ?
							AND home_library = ? AND private_user_id = ? AND item_group = ? AND item_type = ?";	
	
	//open output files
	$fp  = fopen('remove_duplicate_items_sql.out', 'w');	
	$fp2 = fopen('duplicates_wo_url.txt', 'w');	

	//will hold matched duplicates so we do not process them (if a matches b we only want to process a and will skip b)
	$processed = array();	
	$i = 0;  //simple counter

	fwrite($fp, "START TRANSACTION\n");
	fwrite($fp, "ALTER TABLE reserves DROP INDEX unique_constraint\n");	//drop constraint so we can update reserves table will restore after cleaning reserves table
		
	$g_dbConn->query("START TRANSACTION");
	$g_dbConn->query("ALTER TABLE reserves DROP INDEX unique_constraint");	//drop constraint so we can update reserves table will restore after cleaning reserves table
	
	$rs = $g_dbConn->query($sql);
	while($row = $rs->fetchRow(DB_FETCHMODE_ASSOC)) 
	{
echo "\n\n**********************************\nprocessing item: {$row['item_id']} "; 
		$i++;
		if (in_array($row['item_id'], $processed)) 
		{
			echo "has been processed  skipping\n";
			continue;
		} 
		
		//break up data for passing to prepQuery
		$item_id = array_slice($row, 0, 1);
		$updates = array_slice($row, 1, 18);
		$matches = array_slice($row, 8, 18);
		

		//select dups without testing url for report
		$stmt = prepQuery($match_dup_wo_url, array_merge($item_id, $matches));			
		$rs_dups = $g_dbConn->query($stmt);
		if(DB::isError($rs_dups)) {
			die($rs_dups->getMessage().' '.$rs_dups->getDebugInfo().'\n');
		}		
		if ($rs_dups->numRows() > 0)
		{
			fwrite($fp2, "{$item_id['item_id']}: matched: {$rs_dups->numRows()} ignoring url\n");
		}
		
		//if local_control_key exists we will match on it exclusively
		$stmt = (!empty($row['local_control_key'])) ?  prepQuery($match_dup_control_key, $row, false) : prepQuery($match_dup_sql, $row, true);
echo "checking for dups with\n$stmt\n";				
		$rs_dups = $g_dbConn->query($stmt);
		if(DB::isError($rs_dups)) {
			die($rs_dups->getMessage().' '.$rs_dups->getDebugInfo().'\n');
		}

		//report to std out whats going on
		if ($rs_dups->numRows() == 0)
		{
			echo " no dups found. \n";
			continue;
		} else {
			echo " found {$rs_dups->numRows()} duplicates \n";				

		}
		
		$dup_ids = array();
		while($dup = $rs_dups->fetchRow(DB_FETCHMODE_ASSOC)) 
		{
			$updates = combine($updates, $dup);
			$processed[] = $dup['item_id']; // add to processed list to avoid processing again
			$dup_ids[]   = $dup['item_id']; // add to array so we can delete these
		}
echo "removing ". join($dup_ids, ",") . "\n";
echo "updates /n"; print_r($updates);
		
		foreach(build_sql($row['item_id'], $dup_ids, $updates) as $stmt)
		{
echo "executing $stmt\n";
			$g_dbConn->query($stmt);
		}
		//write update/delete statements to sql file
		fwrite($fp, join(";\n", build_sql($row['item_id'], $dup_ids, $updates)) . ";\n");
		fwrite($fp, "\n");
			
	}
	
	//fwrite($fp, "COMMIT;\n");
	$g_dbConn->query("COMMIT");
	
	fclose($fp);
	fclose($fp2);
	
	echo "processed $i rows\n";

//END MAIN	
	
	// PEAR::DB prepare does not properly match null values so we will prepare the queries manually
	function prepQuery($stmt, $values, $matchBlank=false)
	{
		$stmt = preg_replace("/item_id <> !/", "item_id <> {$values['item_id']}", $stmt, 1);
		foreach ($values as $k => $v) {
			
			if (empty($v))	{
				$r = " $k IS NULL";
			} else {
				$r = " ($k = '".addslashes($v)."'";
				if ($matchBlank) $r .=  " OR $k IS NULL";
				$r .= ")";
			}			
			
			$p = "/ $k = \?/";
			$stmt = preg_replace($p, $r, $stmt, 1);
		}
		
		return $stmt;  
	}

	function prepQueryUpdate($stmt, $values)
	{
		$stmt = preg_replace("/item_id <> !/", "item_id <> {$values['item_id']}", $stmt, 1);
		foreach ($values as $k => $v) {
			
			if (empty($v))	{
				$r = " $k = NULL";
			} else {
				$r = " $k = '".addslashes($v)."'";
			}			
			
			$p = "/ $k = \?/";
			$stmt = preg_replace($p, $r, $stmt, 1);
		}
		
		return $stmt;  
	}
	
	//update associated tables
	function build_sql($set_id, $dups, $update_values)
	{
		$sql[] 	= "UPDATE notes SET target_id = $set_id WHERE target_table = 'items' AND target_id in (". join(",",$dups) .")";
		$sql[] 	= "UPDATE reserves SET item_id = $set_id WHERE item_id in (". join(",",$dups) .")";
		$sql[] 	= "UPDATE requests SET item_id = $set_id WHERE item_id in (". join(",",$dups) .")";
		$sql[] 	= "UPDATE electronic_item_audit SET item_id = $set_id WHERE item_id in (". join(",",$dups) .") ";
		$sql[] 	= "UPDATE physical_copies SET item_id = $set_id WHERE item_id in (". join(",",$dups) .") ";
		
		$sql[]	= "DELETE FROM items WHERE item_id in (". join(",",$dups) .") ";
		
		if (!empty($update_values['content_notes']))
		{
			$sql[]	= "INSERT INTO notes (type, target_table, target_id, note) 
					   VALUES ('Content', 'items', $set_id, '". addslashes($update_values['content_notes']) ."')";
		}
		
		$sql[] = prepQueryUpdate("UPDATE items SET source = ?, volume_title = ?, volume_edition = ?, ISBN = ?, ISSN = ?, OCLC = ?, status = ?, local_control_key = ? WHERE item_id = $set_id", $update_values);
		
		return $sql;
	}
	
	//condense two arrays with matching keys, if values in b are empty take the value from a
	//returns a single array
	function combine($a, $b)
	{
		$t = array();
		foreach ($b as $k => $v)
		{			
			if (is_null($v) || $v == '' || $v == '0')
			{
				//array b has a null entry fill it from array a
				$v = $a[$k];
			} 
		
			// status = DENIED and item_group = MULTIMEDIA have precedence
			if ($k == 'status' && ($a[$k] == 'DENIED' || $b[$k] == 'DENIED'))  //if copyright has been denied carry forward
			{
				$t[$k] = 'DENIED';
			} elseif ($k = 'item_group' && ($a[$k] == 'MULTIMEDIA' || $b[$k] == 'MULTIMEDIA')) {
				$t[$k] = 'MULTIMEDIA';
			} else {
				$t[$k] = $v;
			}
		}
		return $t;
	}
?>
