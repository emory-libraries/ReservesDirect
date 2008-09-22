#!/usr/local/bin/php -q
<?php
    // get contents of a file into a string
	$filename = "fakenames.txt";
	
	$handle = fopen($filename, "r");
	$contents = fread($handle, filesize($filename));
	
	fclose($handle);
	
	$line = split("\n", $contents);
	unset($contents);
	
	$out = fopen("update_names.sql", "w");

	fwrite($out,  "ALTER TABLE `users` DROP INDEX `username`;");	
	for ($i=0;$i<count($line);$i++)
	{
		list($username, $fname, $lname) = split(" ", $line[$i]);
		$uID = $i + 1;
		$update = "UPDATE users SET username='$username', first_name='$fname', last_name='$lname', email='demo@reservesdirect.org' WHERE user_id = $uID;\n";
		if (fwrite($out, $update))
			echo "$update\n";
	}
	fwrite($out, "ALTER TABLE `users` ADD UNIQUE `username` (`username`);");
	fclose($out);
?>
