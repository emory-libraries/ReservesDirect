#!/usr/local/bin/php -q
<?php
	require_once('../secure/config.inc.php');
	
	$sql = "SELECT note_id, target_id FROM notes WHERE type='Copyright' and target_table = 'reserves'";
	$rs = $g_dbConn->query($sql);
	if(DB::isError($rs)) {
		echo '<p />'.$rs->getMessage().'<p />'.$rs->getDebugInfo().'<hr />';
		exit(-1);
	}
	
	$i = 0;
	while($row = $rs->fetchRow(DB_FETCHMODE_ASSOC)) 
	{
		$sql1 = "SELECT item_id FROM reserves WHERE reserve_id = {$row['target_id']}";
		$rs1 = $g_dbConn->query($sql1);
		if(DB::isError($rs1)) {
			echo $rs1->getMessage().' '.$rs1->getDebugInfo().'\n';
			exit(-1);
		}
		
		if ($reserve = $rs1->fetchRow(DB_FETCHMODE_ASSOC))
		{			
			$sql2 = "UPDATE notes SET target_id = {$reserve['item_id']}, target_table='items' WHERE note_id = {$row['note_id']}";
			$rs2 = $g_dbConn->query($sql2);			
			if(DB::isError($rs2)) {
				echo $rs2->getMessage().' '.$rs2->getDebugInfo().'\n';
				exit(-1);
			}		
			$i++;
		}
	}
	
	echo "done.  Updated $i notes\n\n";
?>