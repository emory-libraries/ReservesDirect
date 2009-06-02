#!/usr/local/bin/php -q
<?php
	require_once('../secure/config.inc.php');
	
	$sql = "SELECT i.item_id, pc.barcode FROM items AS i JOIN physical_copies AS pc ON i.item_id = pc.item_id WHERE i.local_control_key IS NULL AND pc.barcode IS NOT NULL AND pc.barcode != ''";
	$rs = $g_dbConn->query($sql);
	if(DB::isError($rs)) {
		echo '<p />'.$rs->getMessage().'<p />'.$rs->getDebugInfo().'<hr />';
		exit(-1);
	}
	
	$g_zhost = 'libcat1.cc.emory.edu';

	//open socket to EUCLID widget which will return a controlNumber
	

		$i = 0;
		while($row = $rs->fetchRow(DB_FETCHMODE_ASSOC)) 
		{
	      // echo $row['barcode'].' '.$row['item_id']."\n\n";
	
		$fp = fsockopen($g_zhost, 4321, $errno, $errstr, 60);
		if (!$fp) {
			 die ("find_by_barcode could not connect $errstr ($errno)");
		} else {	
			fwrite($fp, $row['barcode']);
			while (!feof($fp)) {
				$term =  fgets ($fp,128);
				$term = ereg_replace("[^A-z0-9]", "", $term);
			}
			fclose($fp);		
		}
			//echo "term --- ".$term."\n\n";
			if ($term !=null) {
				
				$sqlupdate = "UPDATE items SET local_control_key='{$term}' WHERE item_id={$row['item_id']}";
				$rsupdate = $g_dbConn->query($sqlupdate);
				if(DB::isError($rsupdate)) {
					echo $rsupdate->getMessage().' '.$rsupdate->getDebugInfo().'\n';
					exit(-1);
				}
				$i++;
			}
			
		}

	
	
	
	echo "done.  Updated $i notes\n\n";
?>