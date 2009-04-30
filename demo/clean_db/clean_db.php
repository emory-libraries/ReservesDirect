#!/usr/local/bin/php -q
<?php
    // This script will find example users to become the demo logins
    // It will then run a prebuild sql file to clean username, first and last name and email from the db
    // Then it will create the demo logins 
    
    
    require_once("DB.php");
    require_once("../../demo/config_loc.inc.php");
	
	if (!is_readable($xmlConfig)) { die ("Could not read configure xml file path=$xmlConfig"); }
	
	$configure = simplexml_load_file($xmlConfig);
	
	//will be moved to xmlfile and parsed here
	$dsn = array(
	    'phptype'  => (string)$configure->database->dbtype,
	    'username' => (string)$configure->database->username,
	    'password' => (string)$configure->database->pwd,
	    'hostspec' => (string)$configure->database->host,
	    'database' => (string)$configure->database->dbname,
	    'key'      => (string)$configure->database->dbkey,
	    'cert'     => (string)$configure->database->dbcert,
	    'ca'       => (string)$configure->database->dbca,
	    'capath'   => (string)$configure->database->capath,
	    'cipher'   => (string)$configure->database->cipher
	);
	
	$options = array(
	    'ssl' 		=> (string)$configure->database->ssl,
	    'debug'     => (string)$configure->database->debug
	);

	//open connection
	$g_dbConn = DB::connect($dsn, $options);
	if (DB::isError($g_dbConn)) { trigger_error($g_dbConn->getMessage(), E_USER_ERROR); }	
	
    //first find the current term
    $today = date("Y-m-d");
    $sql_term = "SELECT begin_date, end_date FROM terms WHERE begin_date < ? order by sort_order DESC LIMIT 1";
    $rs = $g_dbConn->query($sql_term, array($today));  
    list($term_beginDate, $term_endDate) = $rs->fetchRow();
    
    $uID = array();
    $admin = 5;
    $staff = 4;
    $instructor = 3;
    $proxy = 2;
    $custodian = 1;
    $student = 0;
    
    //Admin
    //always use fpici Frances Pici
    $uID[admin] = 33166;
    
    //Staff
    //always use avillar Alfredo Villar
    $uID[staff] = 1477;
    
    
    $class_sql = "SELECT primary_course_alias_id, count( r.reserve_id ) AS reserves_cnt "
			   	."	FROM course_instances AS ci "
			   	."	JOIN reserves AS r ON ci.course_instance_id = r.course_instance_id "
			   	."		AND ci.activation_date < ? AND ? < ci.expiration_date "
			   	."		AND ci.status = 'ACTIVE' "
			   	."	JOIN course_aliases AS ca ON ci.course_instance_id = ca.course_instance_id "
			   	."		AND ca.course_alias_id <> ci.primary_course_alias_id "
			   	."	GROUP BY primary_course_alias_id "
			   	."	HAVING reserves_cnt > 5 AND reserves_cnt < 25 ";
    
    //Instructor
    //find a instructor teaching a class with at least 1 crosslisting, and between 5 and 25 items on reserve
    $inst_sql = "SELECT instructor.user_id, primary_course_alias_id, count( primary_course_alias_id ) AS class_cnt "
			   ."FROM "
			   ."($class_sql) AS classes "
			   ."JOIN access AS instructor ON instructor.alias_id = classes.primary_course_alias_id "
			   ."	AND instructor.permission_level = $instructor "
			   ."GROUP BY instructor.user_id "
			   ."ORDER BY class_cnt DESC "
			;
			
			
			
    $rs = $g_dbConn->query($inst_sql, array($term_beginDate, $term_endDate));
    list($uID[instructor], $ca, $devnull) = $rs->fetchRow();
    
    //Proxy Find a proxy enrolled as a student in a current course
    $proxy_sql = "SELECT proxy.user_id "
    			."FROM ($class_sql) AS classes "	
    			."JOIN access AS proxy ON proxy.alias_id = classes.primary_course_alias_id AND proxy.permission_level = $student "
    			."JOIN users AS u ON proxy.user_id = u.user_id "
    			."WHERE u.dflt_permission_level = $proxy AND proxy.alias_id <> $ca ";  //we will add access to $ca so make sure they dont have it now


   	$rs = $g_dbConn->query($proxy_sql, array($term_beginDate, $term_endDate));
    list($uID[proxy]) = $rs->fetchRow();
        			    			
    			
    $custodian_sql = "SELECT user_id FROM users WHERE dflt_permission_level = $custodian";
    $rs = $g_dbConn->query($custodian_sql);
    list($uID[custodian]) = $rs->fetchRow();
    
    
    $student_sql = "SELECT student.user_id FROM users AS u "
    			  ."JOIN access as student ON student.user_id = u.user_id "
    			  ."WHERE dflt_permission_level = $student AND student.alias_id <> $ca ";
    
    $rs = $g_dbConn->query($student_sql);
    list($uID[student]) = $rs->fetchRow();
    			  
	exec("mysql " . $dsn[database] . " -u" . $dsn[username] . " -p" . $dsn[password] . " < update_names.sql");			

	$update_sql = "UPDATE users SET username=?, first_name=?, last_name=? WHERE user_id=!";
	foreach ($uID as $key => $u_id)
	{
		switch ($key)
		{
			case 'admin':
				$username 	= 'admin_demo';
				$lname		= 'Admin';
				$fname		= 'Demo';
				break;	
			case 'staff':
				$username 	= 'staff_demo';
				$lname		= 'Staff';
				$fname		= 'Demo';
				break;			
			case 'instructor':
				$username 	= 'instructor_demo';
				$lname		= 'Instructor';
				$fname		= 'Demo';
				break;			
			case 'proxy':
				$username 	= 'proxy_demo';
				$lname		= 'Proxy';
				$fname		= 'Demo';
				break;			
			case 'custodian':
				$username 	= 'custodian_demo';
				$lname		= 'Custodian';
				$fname		= 'Demo';
				break;			
			case 'student':
				$username 	= 'student_demo';
				$lname		= 'Student';
				$fname		= 'Demo';
				break;			
		}
				
		echo "$update_sql, $username, $fname, $lname, $u_id\n";
		$rs = $g_dbConn->query($update_sql, array($username, $fname, $lname, $u_id));
	}
	
	//Add access to $ca for proxy and student
    $access_sql = "INSERT INTO access (user_id, alias_id, permission_level, enrollment_status) VALUES (!, !, !, ?)";
    echo "$access_sql {$uID[proxy]}, $ca, $proxy, 'APPROVED'\n";
    $g_dbConn->query($access_sql, array($uID[proxy], $ca, $proxy, 'APPROVED'));
    
    echo "$access_sql {$uID[student]}, $ca, $student, 'APPROVED'\n";
    $g_dbConn->query($access_sql, array($uID[student], $ca, $student, 'APPROVED'));			  
	
	
    
    //Clean Items
	$update_pdf = "UPDATE items set url='http://www.reservesdirect.org/demo/data/DisplayDisabled.pdf' WHERE url IS NOT NULL";
	$rs = $g_dbConn->query($update_pdf);
	
	$g_dbConn->query("UPDATE items set pages_times = NULL, ISBN = NULL, ISSN = NULL, OCLC = NULL, volume_title = NULL, volume_edition = NULL, source = NULL");

	//strip titles and authors from items
	$item_sql[] = "UPDATE items set author='Author, John J.' WHERE author is not null";
	$item_sql[] = "UPDATE items set title ='Sample PDF' WHERE item_type = 'ITEM' AND (item_group = 'ELECTRONIC' OR item_group = 0) AND mimetype = 1";
	$item_sql[] = "UPDATE items set title ='Audio Sample' WHERE item_type = 'ITEM' AND (item_group = 'ELECTRONIC' OR item_group = 0) AND mimetype = 2";
	$item_sql[] = "UPDATE items set title ='Sample Video' WHERE item_type = 'ITEM' AND (item_group = 'ELECTRONIC' OR item_group = 0) AND mimetype = 3";
	$item_sql[] = "UPDATE items set title ='MS Word Sample' WHERE item_type = 'ITEM' AND (item_group = 'ELECTRONIC' OR item_group = 0) AND mimetype = 4";
	$item_sql[] = "UPDATE items set title ='MS Excel Sample' WHERE item_type = 'ITEM' AND (item_group = 'ELECTRONIC' OR item_group = 0) AND mimetype = 5";
	$item_sql[] = "UPDATE items set title ='MS PowerPoint Sample' WHERE item_type = 'ITEM' AND (item_group = 'ELECTRONIC' OR item_group = 0) AND mimetype = 6";
	$item_sql[] = "UPDATE items set title ='External Link' WHERE item_type = 'ITEM' AND (item_group = 'ELECTRONIC' OR item_group = 0) AND mimetype = 7";
	
	$item_sql[] = "UPDATE items set title ='Monograph Sample' WHERE item_type = 'ITEM' AND item_group = 'MONOGRAPH'";
	$item_sql[] = "UPDATE items set title ='Multimedia Sample' WHERE item_type = 'ITEM' AND item_group ='MULTIMEDIA'";

	foreach ($item_sql as $sql)
	{
		$g_dbConn->query($sql);
		echo "$sql\n";
	}
	
	$g_dbConn->query("UPDATE notes SET note = 'Sample Note'");
?>
