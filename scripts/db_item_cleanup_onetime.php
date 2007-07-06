<?php
	require_once('../secure/config.inc.php');
	
	$sql = "SELECT item_id, local_control_key
			FROM items
			WHERE local_control_key IS NOT NULL
				AND local_control_key <> ''
			ORDER BY local_control_key ASC, last_modified DESC";
	$rs = $g_dbConn->query($sql);
	if(DB::isError($rs)) {
		echo '<p />'.$rs->getMessage().'<p />'.$rs->getDebugInfo().'<hr />';
		exit(-1);
	}
	
	$previous_item_id = null;
	$previous_control_num = null;
	$count_items_processed = 0;
	$count_items_deleted = 0;
	while($row = $rs->fetchRow(DB_FETCHMODE_ASSOC)) {
		if($row['local_control_key'] == $previous_control_num) {	//repeat item
			//update reserves table
			$sql = "UPDATE reserves SET item_id = ! WHERE item_id = !";
			$rs2 = $g_dbConn->query($sql, array($previous_item_id, $row['item_id']));
			if(DB::isError($rs2)) {
				echo '<p />'.$rs2->getMessage().'<p />'.$rs2->getDebugInfo().'<hr />';
				exit(-1);
			}

			//update notes table
			$sql = "UPDATE notes SET item_id = ! WHERE target_table = 'items' AND target_id = !";			
			$rs2 = $g_dbConn->query($sql, array($previous_item_id, $row['item_id']));
			if(DB::isError($rs2)) {
				echo '<p />'.$rs2->getMessage().'<p />'.$rs2->getDebugInfo().'<hr />';
				exit(-1);
			}

			//update requests
			$sql = "UPDATE requests SET item_id = ! WHERE item_id = !";
			$rs2 = $g_dbConn->query($sql, array($previous_item_id, $row['item_id']));
			if(DB::isError($rs2)) {
				echo '<p />'.$rs2->getMessage().'<p />'.$rs2->getDebugInfo().'<hr />';
				exit(-1);
			}

			//remove duplicate items from  electronic-item-audit
			$sql = "DELETE FROM electronic_item_audit WHERE item_id = ! LIMIT 1";
			$rs2 = $g_dbConn->query($sql, array($row['item_id']));
			if(DB::isError($rs2)) {
				echo '<p />'.$rs2->getMessage().'<p />'.$rs2->getDebugInfo().'<hr />';
				exit(-1);
			}
			
			//delete repeat item			
			$sql = "DELETE FROM items WHERE item_id = ! LIMIT 1";
			$rs2 = $g_dbConn->query($sql, $row['item_id']);
			if(DB::isError($rs2)) {
				echo '<p />'.$rs2->getMessage().'<p />'.$rs2->getDebugInfo().'<hr />';
				exit(-1);
			}
			
			$count_items_deleted++;
		}
		else {	//control key different
			//update control key and item id
			$previous_control_num = $row['local_control_key'];
			$previous_item_id = $row['item_id'];
		}
		
		$count_items_processed++;
	}

	//truncate physical_copies
	$sql = "TRUNCATE TABLE physical_copies";
	$rs = $g_dbConn->query($sql);
	if(DB::isError($rs)) {
		echo '<p />'.$rs->getMessage().'<p />'.$rs->getDebugInfo().'<hr />';
		exit(-1);
	}
	
	echo "<p />\n\nDone... processed $count_items_processed item records; deleted $count_items_deleted.";
?>
