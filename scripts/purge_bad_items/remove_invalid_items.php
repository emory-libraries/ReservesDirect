#!/usr/local/bin/php -q
<?php
/*
	This script will remove items with empty titles and those with invalid local_control_keys
*/	
	echo "removing invalid items \n";
		
	set_include_path(get_include_path() . PATH_SEPARATOR . realpath('../..').'/');
	
	require_once("secure/config.inc.php");	
	require_once("lib/RD/Ils.php");
	
	global $dsn;  //pull global database values will need to pass username and pwd to mysql to execute sql file

	//open database connection to lookup control_keys
	$ckConn = DB::connect(array("phptype" => "mysql", "username" => $dsn['username'], 
			"password" => $dsn['password'], "hostspec" => "localhost", "database" => "control_keys"));
	
	$fp = fopen('remove_invalid_items.sql', 'w');	
	fwrite($fp, "START TRANSACTION;\n");

	$d = 0;
	$u = 0;
		
	//Find and delete invalid items
	$select_sql = "SELECT item_id, title, author, source, volume_title, content_notes, volume_edition, pages_times, performer, local_control_key, 
			url, mimetype, home_library, private_user_id, item_icon, ISBN, ISSN, OCLC, item_type, item_group FROM items";
	
	$delete_sql[] 	= "DELETE FROM notes 					WHERE target_table='items' AND target_id = ";
	$delete_sql[] 	= "DELETE FROM reserves 				WHERE item_id = ";
	$delete_sql[] 	= "DELETE FROM requests					WHERE item_id = ";
	$delete_sql[] 	= "DELETE FROM electronic_item_audit  	WHERE item_id = ";
	$delete_sql[] 	= "DELETE FROM physical_copies  		WHERE item_id = ";
	$delete_sql[]	= "DELETE FROM items 					WHERE item_id = ";	
	
	
	$ils = RD_Ils::initILS();
	
	$rs = $g_dbConn->query($select_sql);
	while($row = $rs->fetchRow(DB_FETCHMODE_ASSOC)) 
	{
		echo "\n\n\nprocessing: {$row['item_id']}\n";
		$title = str_replace("\\", "", $row['title']);		//remove slashes
		$title = preg_replace("/^[ \t]*$/", "", $title);	//make whitespace equivalent to empty string

		if (empty($title))  // if title is blank delete item
		{	
			echo "title is blank deleting\n";
			foreach ($delete_sql as $stmt) 
			{
				fwrite($fp, $stmt . $row['item_id'] .";\n");
			}
			$d++;
			fwrite($fp, "\n\n");
		} else {
			echo "title not blank cleaning\n";
			//clean all other fields and verify local_control_key against EUCLID
			$i = 0;

			$data = array_slice($row, 1, 18);
			echo "data_array  ";
			print_r($data);
			
			foreach ($data as $k => $v)
			{
				$verified_ck = null;
				$force = false;

				if ($row['item_type'] == 'HEADING' && $k != 'title')
				{
					echo "heading $k $v";
					//if heading only title and item_group should be set
					$v = ($k != 'item_type') ? NULL : 'ITEM';
					$force = true;
					echo " forced to $v\n";
				} else {
					if ($k == 'local_control_key')
					{
						if ($row['item_group'] == 'ELECTRONIC')
						{
							echo "Digital Item $k forced to NULL\n";
							//Digital items may have a barcode in the control_key field test it							
							$h = array();
							if (preg_match("/^[0-9]{1,14}$/", $v))
							{
								$h = NULL;
							} 
							if (empty($h))
							{
								$v = NULL;
								$force = true;
							}
							

						} elseif (!empty($v)) {
							echo "Physical Item testing $k -> $v  ";
							//this is a physical item so verify the control_key							
							$verified_ck = $ckConn->getOne("SELECT control_key FROM data WHERE control_key = '$v'");

							if ($v != $verified_ck)
							{
								echo "control_key not valid\n";
								$v = null;
								$force = true;
							}
						}
					}
					if ($k == 'url' && $row['item_group'] != 'ELECTRONIC')
					{
						echo "Physical Item ({$row['item_group']}) $k -> $v forced to NULL\n";
						//physical items can not have urls
						$v = null;
						$force = true;
					}
					
				}

				//build update statement and write to sql file
				$stmt = remove_slashes($k, $v, $row['item_id'], $force);
				if (!empty($stmt))
				{
					fwrite($fp, "$stmt\n");
					$i++;
				}
			}
			if ($i > 0) $u++;
		}
	}
	
	echo "$d items set for delete\n";
	echo "$u items set for update\n\n";
		
	fwrite($fp, "COMMIT;\n");
	fclose($fp);

	echo "excuting sql file remove_invalid_items.sql ....\n";	
	exec("mysql -u{$dsn['username']} -p{$dsn['password']} {$dsn['database']} < remove_invalid_items.sql");
	echo "done\n";
	
//END main


// return sql update statement to remove extra slashes from database values and replace empty strings and 0 values with NULL
function remove_slashes($k, $v, $id, $force=false)
{
echo "-----------------------\nremove_slashes(k=$k, v=$v, id=$id, force=$force)\n";

	$new_v = str_replace("\\", "", trim($v));		 //remove slashes
	$new_v = preg_replace("/^[ \t]*$/", "", $new_v); //make whitespace equivalent to empty string

echo "cleaned v=[$v]\n";

	//set defaults if not nullable field
	switch ($k)
	{
		case 'mimetype':
			if ($new_v == 0) $new_v = 7; //default to text/html
		break; 
		case 'home_library':
			if ($new_v == 0) $new_v = 1; //default to general
		break;
	}

	//build update stmt if required
	if ($force || $v != $new_v || (empty($new_v) && !is_null($v)))  //if value have been altered or if the new value is blank and the original was not null
	{
		if (empty($new_v))
		{
			$value = "NULL";  //replace empty strings with null
		} else {
			$value = "'" . addslashes($new_v) . "'";  //update new value
		}
echo "returning UPDATE items SET $k = $value WHERE item_id = $id;\n\n";
		return "UPDATE items SET $k = $value WHERE item_id = $id;";
	} 	
echo "nothing updated\n\n";
}
?>
