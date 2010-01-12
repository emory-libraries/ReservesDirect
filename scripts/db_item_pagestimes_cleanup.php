<?php
    //require_once('../scripts/classes/pagestimes_cleanup.class.php');
	require_once('../secure/config.inc.php');
	
	$sql = "SELECT item_id, pages_times, total_pages_times, used_pages_times
			FROM items";
	$rs = $g_dbConn->query($sql);
	if(DB::isError($rs)) {
		echo '<p />'.$rs->getMessage().'<p />'.$rs->getDebugInfo().'<hr />';
		exit(-1);
	}
    
	$count_items_processed = 0;
	while($row = $rs->fetchRow(DB_FETCHMODE_ASSOC)) {	
        
        //TODO using scripts/classes/pagestimes_cleanup.php	
		$count_items_processed++;
	}
	
	echo "<p />\n\nDone... processed $count_items_processed item records.";
?>
