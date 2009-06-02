#!/usr/local/bin/php -q
<?php
/*
	This script will generate a sql file to alter the item_group field item_group should be a set containing ('ELECTRONIC', 'MONOGRAPH', 'MULTIMEDIA' or 'HEADING')
	new default will be ELECTRONIC 
*/	
	echo "alter_item_group.php\n";

	set_include_path(get_include_path() . PATH_SEPARATOR . realpath('../..').'/');	
	require_once('secure/config.inc.php');
	
	global $dsn;

	$fp = fopen('alter_item_group.sql', 'w');	
	fwrite($fp, "START TRANSACTION;");
		
	//Find and delete invalid items
	$select_sql = "SELECT item_id, url, item_type, local_control_key   
				   FROM items 
				   WHERE (item_group = '0' OR item_group IS NULL) AND item_type <> 'HEADING'";
	
	$rs = $g_dbConn->query($select_sql);
	while($row = $rs->fetchRow(DB_FETCHMODE_ASSOC)) 
	{
		//Physical Items should have no url and a valid local_control_key default to MONOGRAPH
		if (empty($row['url']) && !empty($row['local_control_key']))
		{
			fwrite($fp, "UPDATE items SET item_group = 'MONOGRAPH' WHERE item_id = " . $row['item_id'] .";\n");	
		} else {
			fwrite($fp, "UPDATE items SET item_group = 'ELECTRONIC' WHERE item_id = " . $row['item_id'] .";\n");	
		}		
	}
	
	fwrite($fp, "UPDATE items SET item_group = 'HEADING' WHERE item_type = 'HEADING';\n");
	
	fwrite($fp, " ALTER TABLE `items` CHANGE `item_group` `item_group` SET('MONOGRAPH', 'MULTIMEDIA', 'ELECTRONIC', 'HEADING') 
					CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;\n");	
	
	fwrite($fp, "COMMIT;\n");
	fclose($fp);
	
	echo "excuting sql file alter_item_group.sql ....\n";	
	exec("mysql -u{$dsn['username']} -p{$dsn['password']} {$dsn['database']} < alter_item_group.sql");
	echo "done\n";
//END main
?>
