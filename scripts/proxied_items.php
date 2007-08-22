#!/usr/local/bin/php -q
<?php
	/*
		This script will remove ezproxy prefixes from the database 
		ezproxy will now be called dynamically by the reservesViewer
	*/

	require_once('../secure/config.inc.php');
	
	$sql = "SELECT item_id, url FROM items where url LIKE '%proxy.library.emory.edu%' ORDER BY item_id";
	$rs = $g_dbConn->query($sql);
	if(DB::isError($rs)) {
		echo '<p />'.$rs->getMessage().'<p />'.$rs->getDebugInfo().'<hr />';
		exit(-1);
	}
	
	$count_items_processed = 0;
	while($row = $rs->fetchRow(DB_FETCHMODE_ASSOC)) {
		
		$proxied_url = parse_url($row['url']);
		
		$q =  preg_match('/(url=)([\(\)])?(.*)/', $proxied_url['query'], $matches);
		$new_url = $matches[3];
		
		//update items table
		$sql = "UPDATE items SET url = ? WHERE item_id = !";
		$rs2 = $g_dbConn->query($sql, array($new_url, $row['item_id']));
		if(DB::isError($rs2)) {
			echo '<p />'.$rs2->getMessage().'<p />'.$rs2->getDebugInfo().'<hr />';
			exit(-1);
		}
		
//		echo $row['item_id'] . "\n";
//		echo $row['url'] . "\n";
//		echo $new_url . "\n";
//		echo $sql . "\n\n";
		
		$count_items_processed++;
	}
	
	echo "<p />\n\nDone... proxied $count_items_processed item records.\n\n";
?>