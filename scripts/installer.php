<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>ReservesDirect Installer</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		
		<style type="text/css" media="all">
			body {
				background-color: #E9E9E9;
				color: black;
				font-family: sans-serif;
			}
			
			input {
				padding: 2px;
			}
			
			.error { color: red; }
			.warning { color: #FF9900; }
			.success { color: green; }
		</style>
	</head>
	
	<body>

<?php

/****************************************************************************
<pre>  
  
			No-PHP Warning
			--------------
			
If you can read this in your browser, then you do not have PHP installed.

Please revew the server requirements for ReservesDirect:

	- Apache HTTPd
	- PHP 5+ with:
		- MySQL client
		- SimpleXML functions
		- DOM functions
		- JSON functions OR PEAR Services_JSON
	- MySQL 4.1+
	- PEAR DB
	
	
Please visit http://reservesdirect.org for more information.


</pre>
*****************************************************************************/
		

	## EDIT ME ##

	//If the installer script is moved, change this to the path of ReservesDirect directory
	define('RD_ROOT', realpath('..').'/');

	##############
	
	
	//not sure if want all errors
	//probably a good idea
	error_reporting( E_ALL );
	@ini_set( "display_errors", true );
	

	//want the future DB object in global scope
	$g_dbConn = null;
	
	
	//grab step from form/url
	$step = !empty($_REQUEST['step']) ? $_REQUEST['step'] : null;
	
	//main "steps" switching point
	if($step=='two') {	//step 2 -- setting up config file
		setup_config();
	}
	elseif($step=='three') {	//step 3 -- setting up and seeding DB
		echo '<h2>Step 3: Database Setup</h2>';
		setup_db();
	}
	else {	//first step, run some checks and go to step 2
		echo '<h2>Step 1: Server check</h2>';
		//first, check PHP version
		check_php_version();	
		//now check if have mysql functionality
		check_mysql();	
		//check if PEAR::DB exists
		check_pear_db();	
		//check if json functions or PEAR::JSON exists
		check_json();
		//check if simplexml is enabled
		check_simplexml();
		//check DOM		
		check_dom();
		
		echo '<br /><hr />';		
		//go on to step 2 -- setting up config file	
		setup_config();
	}
	
	
	#=== FUNCTIONS ==#
		
	
	/**
	 * main function for setting up DB
	 * coordinates all the steps
	 */
	function setup_db() {
		global $g_dbConn;
		
		//load the config, so that we can read some values
		require_once(RD_ROOT.'config_loc.inc.php');

		if(!is_readable($xmlConfig)) {
			print_step3_error('Could not read XML configuration file at <tt>'.$xmlConfig.'</tt>.');
			die(-1);
		}
		
		//load the config
		$config = simplexml_load_file($xmlConfig);
		
		//decide what to do next
		if(isset($_REQUEST['submit_create_db'])) {	//DB creation form submitted; try to set up the db
			//check to see if using admin credentials
			//checkbox must be checked and the field be non-empty
			$db_admin_username = (isset($_REQUEST['admin_user']) && !empty($_REQUEST['db_admin_username'])) ? $_REQUEST['db_admin_username'] : null;
			$db_admin_pass = (isset($_REQUEST['admin_user']) && !empty($_REQUEST['db_admin_pass'])) ? $_REQUEST['db_admin_pass'] : null;
						
			//attempt to connect to server, but do not pick a DB
			//will die w/ message on error
			get_mysql_connection($config, $db_admin_username, $db_admin_pass, false);
			
			//check mysql server version
			//will die w/ message if < 4.1
			$mysql_version = check_mysql_server_version();
			
			//make sure that we have the bare minimum info in the config
			if(empty($config->database->dbname)) {
				print_step3_error('Database dbname is required.');
			}
			if(empty($config->database->username)) {
				print_step3_error('Database username is required.');
			}
			
			//create some sql statements based on form requests
			$queries = array();
			
			//create database?
			if(isset($_REQUEST['create_db'])) {
				$queries[] = "DROP DATABASE IF EXISTS `{$config->database->dbname}`";
				$queries[] = "CREATE DATABASE `{$config->database->dbname}`";
				
				//override request to create tables
				//if creating new database, must create new tables
				$_REQUEST['create_tables'] = true;
			}
			
			//create user?
			if(isset($_REQUEST['create_user'])) {
				//set default host to localhost
				$host = !empty($config->database->host) ? $config->database->host : 'localhost';
				
				//build create-user sql
				//grant select, insert, update, delete on <db>.* to <user>@<host> identified by '<pass>';
				$sql = "GRANT SELECT, INSERT, UPDATE, DELETE ON `{$config->database->dbname}`.* TO '{$config->database->username}'@'{$host}'";
				
				//add password if it exists, or print warning
				if(!empty($config->database->pwd)) {
					$sql .= " IDENTIFIED BY '{$config->database->pwd}'";
				}
				else {
					print_warning('The ReservesDirect MySQL user should have a password for security purposes.  Please fix this after installation is complete.');
				}
				
				//add this sql to the queries array
				$queries[] = $sql;
			}
			
			//create tables?
			if(isset($_REQUEST['create_tables'])) {
				//use the create_db SQL dump to parse out drop/create table statements
				//this requires mysql server version as an int
				$parsed_version = explode('.', $mysql_version);
				$mysql_int_version = $parsed_version[0].$parsed_version[1].$parsed_version[2];
				
				//get table queries
				//WARNING: make sure to select db before running these
				$table_queries = parse_batch_sql(file_get_contents(RD_ROOT.'scripts/create_db.sql'), $mysql_int_version);				
			}
			
			//run the first set of queries
			if(!empty($queries)) {
				//attempt to use transactions
				if($g_dbConn->provides('transactions')) {
					$g_dbConn->autoCommit(false);
				}
				
				//execute statements
				foreach($queries as $sql) {
					$rs = $g_dbConn->query($sql);
					if(DB::isError($rs)) {
						print_step3_error('Problem executing query: '.$rs->getMessage());
						if($g_dbConn->provides('transactions')) { 
							$g_dbConn->rollback();
						}
						die(-1);
					}
				}
				
				//commit this set
				if($g_dbConn->provides('transactions')) { 
					$g_dbConn->commit();
				}				
				
				//print some success messages;
				if(isset($_REQUEST['create_db'])) {
					print_success("Created <tt>{$config->database->dbname}</tt> database");
				}
				if(isset($_REQUEST['create_user'])) {
					print_success("Granted access to <tt>{$config->database->username}</tt> user to database");
				}
			}
				
			//now need to reconnect and select the db (ideally would just select DB, but no such method)
			//will die w/ message on error
			$g_dbConn->disconnect();
			get_mysql_connection($config, $db_admin_username, $db_admin_pass, true);
			
			//run table-creation queries
			//WARNING: make sure DB has been selected before executing these!
			if(!empty($table_queries)) {
				//attempt to use transactions
				if($g_dbConn->provides('transactions')) {
					$g_dbConn->autoCommit(false);
				}

				//execute statements	
				foreach($table_queries as $sql) {
					$rs = $g_dbConn->query($sql);
					if(DB::isError($rs)) {
						print_step3_error('Problem executing query: '.$rs->getMessage());
						if($g_dbConn->provides('transactions')) { 
							$g_dbConn->rollback();
						}
						die(-1);
					}
				}
				
				//commit this set
				if($g_dbConn->provides('transactions')) { 
					$g_dbConn->commit();
				}
				
				//print success message
				if(isset($_REQUEST['create_tables'])) {
					print_success("Created ReservesDirect Tables");
				}
			}
			
			//disconnect and try to connect as RD user
			//should work fine, or die w/ message if there's a problem
			$g_dbConn->disconnect();
			get_mysql_connection($config, null, null, true);
			$g_dbConn->disconnect();	//disconnect, b/c do not really need this connection
			
			//display success message
			print_success('Database setup complete');
			//display form to get RD admin user setup
?>
			<h2>Step 4: Home Stretch</h2>
			Please create an administrative user to login to ReservesDirect.
			<p />
			<form method="post" name="db_form2">
				<input type="hidden" name="step" value="three" />
				
				ReservesDirect Admin Username: <input type="text" size="40" id="rd_admin_username" name="rd_admin_username" value="admin" />
				<br />
				ReservesDirect Admin Password: <input type="password" size="40" id="rd_admin_pass" name="rd_admin_pass" />
				<p />
				<input type="submit" name="submit_rd_admin" value="Create Administrator" />
			</form>
<?php	
		}
		elseif(isset($_REQUEST['submit_rd_admin'])) {	//insert RD admin record into DB
			//connect to DB
			get_mysql_connection($config, null, null, true);
			
			//do not fill in empty username/pass
			$username = !empty($_REQUEST['rd_admin_username']) ? $_REQUEST['rd_admin_username'] : 'admin';
			$password = !empty($_REQUEST['rd_admin_pass']) ? $_REQUEST['rd_admin_pass'] : 'pFdA5gn35';	//could be a true random pass, but this is good enough
			
			//insert admin user record
			$sql = "INSERT INTO `users` (`user_id`, `username`, `first_name`, `last_name`, `email`, `dflt_permission_level`, `last_login`, `old_id`, `old_user_id`) VALUES (1, '{$username}', 'ReservesDirect', 'Administrator', NULL, 5, '0000-00-00', NULL, NULL)";
			$rs = $g_dbConn->query($sql);
			if(DB::isError($rs)) {
				print_step3_error('Could not create admin user record: '.$rs->getMessage());
				die(-1);
			}
			
			//insert override password for admin
			$sql = "INSERT INTO `special_users` (`user_id`, `password`, `expiration`) VALUES (1, '".md5($password)."', NULL)";
			$rs = $g_dbConn->query($sql);
			if(DB::isError($rs)) {
				print_step3_error('Could not create password for admin user: '.$rs->getMessage());
				die(-1);
			}
			
			//success
			print_success('Administrator record created successfully');
	
			echo '<p />You should now be able to login to ReservesDirect with as an administrator.';
			if($_REQUEST['rd_admin_username'] != $username) {	//had to create a username
				echo '<br />username: '.$username;				
			}
			if($_REQUEST['rd_admin_pass'] != $password) {
				echo '<br />password: '.$password;
			}
			
			echo '<p />For security purposes:<ol><li>DELETE THIS SCRIPT after you verify your ReservesDirect installation.</li><li>Change permissions of <tt>'.RD_ROOT.'config_loc.inc.php<tt> to read-only for the HTTPd user</li><li>Change permissions of the directory of the config file to read-only for the HTTPd user</li>';
		}
		else {	//display initial form
			//first make sure we have the SQL dump
			if(!is_readable(RD_ROOT.'scripts/create_db.sql')) {
				print_step3_error('Could not locate SQL dump file at <tt>'.RD_ROOT.'scripts/create_db.sql</tt>');
				die(-1);
			}
?>
			<script type="text/javascript">
				function check_uncheck1() {
					if(document.getElementById('create_db') && document.getElementById('create_tables')) {
						if(document.getElementById('create_db').checked) {
							document.getElementById('create_tables').checked = true;
							document.getElementById('create_tables').disabled = true;
						}
						else {
							document.getElementById('create_tables').checked = false;
							document.getElementById('create_tables').disabled = false;
						}
					}
				}
				
				function check_uncheck2() {
					if(document.getElementById('create_user') && document.getElementById('admin_user')) {
						if(document.getElementById('create_user').checked) {
							document.getElementById('admin_user').checked = true;
							check_uncheck3();
						}
					}
				}
				
				function check_uncheck3() {
					if(document.getElementById('admin_user') && document.getElementById('db_admin_username') && document.getElementById('db_admin_pass')) {
						if(document.getElementById('admin_user').checked) {
							document.getElementById('db_admin_username').disabled = false;
							document.getElementById('db_admin_pass').disabled = false;
						}
						else {
							document.getElementById('db_admin_username').disabled = true;
							document.getElementById('db_admin_pass').disabled = true;
							
							//can't create user if no admin user
							if(document.getElementById('create_user') && document.getElementById('create_user').checked) {
								document.getElementById('create_user').checked = false;						
							}
						}
				 	}					
				}

			</script>		
			<form method="post" name="db_form1">
				<input type="hidden" name="step" value="three" />
				
				<input type="checkbox" id="create_db" name="create_db" onchange="javascript:check_uncheck1();" /> Create <tt><?php echo $config->database->dbname; ?></tt> database on host <tt><?php echo $config->database->host; ?></tt>.
				<br /><span style="padding-left:25px;"><strong>WARNING:</strong> this will delete any existing database with that name!</span>
				<p />
				<input type="checkbox" id="create_tables" name="create_tables" /> Create ReservesDirect tables in <tt><?php echo $config->database->dbname; ?></tt> database.
				<br /><span style="padding-left:25px;"><strong>WARNING:</strong> this will delete any ReservesDirect tables that already exist!</span>
				<p />
				<input type="checkbox" id="create_user" name="create_user" onchange="javascript:check_uncheck2();" /> Create <tt><?php echo $config->database->username; ?></tt> user and grant usage access to <tt><?php echo $config->database->dbname; ?></tt> database.
				<br /><span style="padding-left:25px;"><strong>WARNING:</strong> After completing the installation, you should review this user's privileges to make sure they are secure!</span>
				<p />
				<input type="checkbox" id="admin_user" name="admin_user" onchange="javascript:check_uncheck3();" /> Use these credentials to perform the operations above (otherwise will attempt to use <tt><?php echo $config->database->username; ?></tt> credentials):
				<div style="padding-left: 50px;">
					DB Admin Username: <input type="text" size="40" id="db_admin_username" name="db_admin_username" value="root" disabled="disabled" />
					<br />
					DB Admin Password: <input type="password" size="40" id="db_admin_pass" name="db_admin_pass" disabled="disabled" />
				</div>
				<p />
				<input type="submit" name="submit_create_db" value="Setup Database" />
			</form>	
<?php
		}
	}

	
	/**
	 * catch-all function for setting up the configuration file;
	 * coordinates different steps
	 */
	function setup_config() {
		//set path of default config file
		$default_xmlConfig = RD_ROOT.'config.xml.example';
		//sets $xmlConfig to path of actual config xml file
		require_once(RD_ROOT.'config_loc.inc.php');
		
		//check to see if there is already a config file at specified location		
		if(is_readable($xmlConfig)) {
			//load the config file
			$config_path = $xmlConfig;
		}
		elseif(is_readable($default_xmlConfig)) {	//fall back to the example file
			//load the default config file
			$config_path = $default_xmlConfig;
		}
		else {	//can't find either one, error out
			print_error('Example config file (<tt>'.RD_ROOT.'config.xml.example</tt>) is missing or is unreadable.');
			die(-1);
		}
		
		echo '<h2>Step 2: Server configuration</h2>';
		
		//decide what to do next	
		if(isset($_REQUEST['submit_config'])) {	//handle config data
			store_config($config_path);
			
			//button to next step
?>
			<hr />
			Before continuing to the next step, please make sure that:
			<ol>
				<li>the configuration XML file exists in a secure location</li>
				<li>the HTTPd/PHP user has permissions to read this file</li>
				<li>the absolute path to this file is stored in <tt>config_loc.in.php</tt> in your ReservesDirect directory</li>
			</ol>
			<form method="post">
				<input type="hidden" name="step" value="three" />
				<input type="submit" name="manage_db" value="Configure database" />
			</form>
<?php
		}
		elseif(isset($_REQUEST['config_loc'])) {	//show config data form
?>
			<form method="post" name="config_form">
				<input type="hidden" name="step" value="two" />
				<input type="hidden" name="config_loc" value="<?php echo $_REQUEST['config_loc']; ?>" />
				
				Please see documentation at <a href="http://reservesdirect.org">ReservesDirect.org</a> for help.
				<p />
				
				<?php print_xml_as_form(simplexml_load_file($config_path)); ?>
				<input type="submit" name="submit_config" value="Save" />
			</form>
<?php
		}
		else {	//get ready to edit/create the config file
?>
			<form method="post">
				<input type="hidden" name="step" value="two" />
							
				<h3>Configuration file location:</h3>					
				<input type="text" size="40" name="config_loc" value="/path/to/secure-location/" /> <small><i>ex: /etc/reservesdirect/</i></small>
				<br />
				<small>
					This should be the path of the directory where you want to save the ReservesDirect configuration file.  It is strongly recommended to place the configuration file outside of the document root of the web application.
				</small>
				<p />
				** If you want the installer to handle file writing, please check the following:
				<ul>
					<li>The HTTPd user must have write permissions to the directory specified above</li>
					<li>The HTTPd user must have write permissions to <tt>config_loc.inc.php</tt> file in the ReservesDirect directory</li>
				</ul>
				** If you wish to edit files manually, just click the button
				<p />
				<input type="submit" name="begin_setup" value="Create Configuration File" />
			</form>
<?php
		}
	}	
	
	
	/**
	 * Checks to make sure PHP is v5+
	 * Taken from mediawiki
	 */
	function check_php_version() {
		if(!function_exists('version_compare')) {
			# version_compare was introduced in 4.1.0
			print_error("Your PHP version is much too old; 4.0.x will _not_ work. 5.0.0 or higher is required.");
			die(-1);
		}
		if(version_compare(phpversion(), '5.0.0', '<')) {
			print_error("PHP 5.0.0 or higher is required.");
			die(-1);
		}
		
		// Test for PHP bug which breaks PHP 5.0.x on 64-bit...
		// As of 1.8 this breaks lots of common operations instead
		// of just some rare ones like export.
		$borked = str_replace('a', 'b', array(-1 => -1));
		if(!isset($borked[-1])) {
			print_error("PHP 5.0.x is buggy on your 64-bit system; you must upgrade to PHP 5.1.x or higher.<br />(http://bugs.php.net/bug.php?id=34879 for details)");
			die(-1);
		}
		
		//we got this far, success
		print_success('PHP version fine -- '.phpversion());	
		return true;
	}
	
	
	/**
	 * Checks to make sure PHP has MySQL(i) functionality
	 * partially taken from mediawiki
	 * 
	 * (another quick-n-dirty way would be to just see if a mysql_ function exists)
	 */
	function check_mysql() {
		//see if have mysql/mysqli
		$supported_dbs = array('mysql', 'mysqli');
		$have_db = false;
		foreach($supported_dbs as $db) {
			if(extension_loaded($db) or dl($db.'.'.PHP_SHLIB_SUFFIX)) {
				$have_db = true;
				break;
			}
		}
		
		
		if(!$have_db) {
			print_error('MySQL/MySQLi DB client for PHP is required.');
			die(-1);
		}
		else {
			print_success('Found MySQL DB client.');
			return true;
		}
	}
	
	
	/**
	 * Attempts to init PEAR::DB object and connect to MySQL server;  Uses credentials from config file;  Will use admin user/pass if provided
	 * 
	 * Will die on failure
	 * 
	 * @param simpleXMLObject $config configuration info
	 * @param string $admin_user Username of admin user
	 * @param string $admin_pass Password of admin user
	 * @param boolean $select_db If TRUE will select the DB specified in config; if FALSE will connect to server w/o selecting DB (this may be necessary IF DB needs to be created)
	 */
	function get_mysql_connection($config, $admin_user=null, $admin_pass=null, $select_db=true) {
		global $g_dbConn;
		
		require_once('DB.php');
		
		//grab needed info from config
		$dsn = array(
		    'phptype'  => (string)$config->database->dbtype,
		    'username' => (string)$config->database->username,
		    'password' => (string)$config->database->pwd,
		    'hostspec' => (string)$config->database->host,
		    'database' => (string)$config->database->dbname
		);
		$options = array(
		    'ssl' 		=> (string)$config->database->ssl,
		    'debug'     => (string)$config->database->debug
		);

		//use Admin credentials, if provided
		if(!empty($admin_user)) {
			$dsn['username'] = $admin_user;
			$dsn['password'] = $admin_pass;
		}
		
		//may need to skip DB connection if have to create DB first
		if(!$select_db) {
			$dsn['database'] = null;
		}
		
		//open connection
		$dbConn = DB::connect($dsn, $options);
		if(DB::isError($dbConn)) {	//problem
			//default PEAR::DB error message sucks, construct a better one
			$err_msg = "Could not connect to MySQL (<tt>{$dsn['phptype']}</tt>) server as <tt>{$dsn['username']}</tt>@<tt>{$dsn['hostspect']}</tt>";
			//connecting to DB?
			$err_msg .= $select_db ? ", selecting <tt>{$dsn['database']}</tt> database: " : ": ";
			
			print_step3_error($err_msg.$dbConn->getMessage());
			die(-1);			
		}
		else {	//success
			$g_dbConn = $dbConn;
		}
	}
	
	
	/**
	 * checks to make sure MySQL server is v4+
	 * adapted from wordpress
	 * (this only works after connection has been established)
	 */
	function check_mysql_server_version() {
		global $g_dbConn;
		
		//get mysql server version
		$mysql_version = $g_dbConn->getOne('SELECT VERSION() as version');
	
		// Make sure the server has MySQL 4.1
		$mysql_version = preg_replace('|[^0-9\.]|', '', $mysql_version);
		if(version_compare($mysql_version, '4.1', '<')) {
			print_error('MySQL server 4.1 or higher is required.');
			die(-1);
		}
		else {
			print_success('MySQL server version is fine -- '.$mysql_version);
			return $mysql_version;
		}
	}
	
	
	/**
	 * Can't think of a way of checking for this, other than just trying to include it
	 */
	function check_pear_db() {		
		//see if DB script is in the include path (manually or through PEAR)
		//include should return 1 on success, and nothing + WARNING on failure
		if((@include 'DB.php')) {
			if(class_exists('DB')) {
				//just assume that it will work
				print_success('Found PEAR::DB.');
				return true;
			}
		}
		
		//no PEAR::DB
		print_error('PEAR::DB is required.');
		die(-1);
	}
	
	
	/**
	 * checks for json
	 */
	function check_json() {
		//see if json is built-in (php 5.2+)
		if(function_exists('json_encode')) {
			print_success('Found JSON.');
			return true;
		}
		
		//see if JSON script is in the include path (manually or through PEAR)
		//include should return 1 on success, and nothing + WARNING on failure
		$haveit = false;
		if(@include('JSON.php')) {
			$haveit = true;
		}
		elseif(@include('PEAR/JSON.php')) {
			$haveit = true;
		}
		
		if($haveit && class_exists('Services_JSON')) {
			//just assume that it will work
			print_success('Found JSON.');
			return true;
		}
		else {
			//no JSON
			print_error('PEAR::JSON or built-in JSON functionality is required.');
			die(-1);
		}
	}
	
	
	/**
	 * Checks for simpleXML
	 */
	function check_simplexml() {
		//should be enabled by default; make sure it has not been disabled
		if(function_exists('simplexml_load_file')) {
			print_success('Found SimpleXML.');
			return true;
		}
		else {
			print_error('SimpleXML is required.');
			die(-1);
		}
	}
	
	
	/**
	 * checks DOM functionality;  Should be built into PHP5
	 * 
	 * pretty hacky way to check this
	 */
	function check_dom() {
		if(class_exists('DOMDocument') && class_exists('DOMXPath')) {
			return true;
		}
		else {
			print_error('DOM functionality (DOMDocument, DOMXPath, etc.) is required.');
			die(-1);
		}
	}
	
	
	/**
	 * Recursive function to output data nodes in a simpleXML object as form inputs
	 *
	 * @param simpleXMLElement $sxmlObj
	 * @param string $path xpath
	 */
	function print_xml_as_form(&$sxmlObj, $path='/') {
		//propogate path for xpath
		$path .= '/'.$sxmlObj->getName();
		
		if(count($sxmlObj->children()) > 0) {
			echo '<fieldset>';
			echo '<legend>'.$sxmlObj->getName().'</legend>';
			//print the comment
			if(!empty($sxmlObj['comment'])) {
				echo '<small><i>'.$sxmlObj['comment'].'</i></small><br />';
			}
			foreach($sxmlObj->children() as $child) {
				print_xml_as_form($child, $path);
			}
			echo '</fieldset>'."\n";
		}
		else {
			echo '<br />'.$sxmlObj->getName().': ';
			if(strlen($sxmlObj) > 100) {
				echo '<br /><textarea rows="6" cols="75" name="xml['.$path.']">'.$sxmlObj.'</textarea>';
				if(!empty($sxmlObj['comment'])) {
					echo '<br />';
				}
			}
			else {
				echo '<input type="text" size="40" name="xml['.$path.']" value="'.$sxmlObj.'" />';
			}
			
			//print the comment
			if(!empty($sxmlObj['comment'])) {
				echo ' <small><i>'.$sxmlObj['comment'].'</i></small>';
			}
		}
		
		echo "\n";	//makes source easier to read
	}
	
	
	/**
	 * Compares values of $_REQUEST['xml'][node-xpath] fields to the node values of the DOM document created from config file.
	 * Updates DOM document with new values and returns new xml
	 * 
	 * @param string $config_loc Location of XML file
	 * @return string XML document
	 */
	function rebuild_xml($config_loc) {
		//load the config file
		$config = new DOMDocument('1.0', 'UTF-8');
		$config->load($config_loc);

		$xpath = new DOMXPath($config);
		
		foreach($_REQUEST['xml'] as $node_xpath=>$node_value) {
			$node = $xpath->query($node_xpath)->item(0);
			if(!is_null($node) && ($node_value != $node->nodeValue)) {
				//trim the new value
				$node->nodeValue = trim($node_value);
			}
		}
		
		return $config->saveXML();
	}
	
	
	function store_config($config_path) {
		$config_xml_string = rebuild_xml($config_path);
		
		//some error handling vars
		$had_err = false;
		$err_msg = '';
		
		//see if the provided config dir exists and server can write to it
		$config_loc = !empty($_REQUEST['config_loc']) ? $_REQUEST['config_loc'] : null;
		if(is_dir($config_loc) && is_writable($config_loc)) {
			//calling realpath will remove the extra / if config_loc has trailing slash (as well as return an absolute path)
			$new_config_path = realpath($config_loc).'/rd_config.xml';
			
			//don't overwrite file if it exists
			if(file_exists($new_config_path)) {
				//rename old file as datedbackup name.YYYYMMDDHHMMSS
				if(!rename($new_config_path, $new_config_path.'.'.date('YmdHis'))) {
					$had_err = true;
					$err_msg = 'Found old configuration file and could not save a backup.';
				}
			}
			
			//store the file -- make sure there was no error moving the old file
			if(!$had_err) {
				//write contents to file
				if(file_put_contents($new_config_path, $config_xml_string) !== false) {
					//try to change permissions to r--------
					if(!chmod($new_config_path, 0400)) {
						$had_err = true;
						$err_msg = 'Configuration file successfully written to <tt>'.$config_path.'</tt>, but installer could not change file permissions.';
					}
					else {
						print_success('Configuration file successfully written to <tt>'.$new_config_path.'</tt> and file permissions changed to 0600.');
					}
					
					//try to write config file location to config_loc.inc
					//create the file contents
					$loc_phpfile_string = 
'<?php 
	/* CHANGEME -- set this to the path of the RD configuration file; 
	it is strongly recommended to place the configuration file  somewhere 
	outside the document root of the web application, with the file
	permissions set so that the web server user can read the file */

	$xmlConfig = "'.$new_config_path.'";
?>';
					
					//write the string to file
					if(file_put_contents(RD_ROOT.'config_loc.inc.php', $loc_phpfile_string) !== false) {
						print_success('Location of configuration file successfully written to <tt>'.RD_ROOT.'config_loc.inc.php</tt>.  Make sure this file is in your ReseresDirect directory.');
					}
					else {
						print_warning('Location of configuration file could not be stored in <tt>'.RD_ROOT.'config_loc.inc.php</tt>.  Make sure to edit this file and set the location to <tt>'.$new_config_path.'</tt>');
					}
				}
				else {
					$had_err = true;
					$err_msg = 'Could not write configuration file to <tt>'.$config_loc.'</tt>';
				}
			}
		}
		else {	//dir not found, or could not write
			$had_err = true;
			$err_msg = 'Configuration directory (<tt>'.$config_loc.'</tt>) does not exist or HTTPD does not have write permissions to that directory.';
		}
		
		//if any problems encountered during writing, print this
		if($had_err) {
			//print warning instead of error, because this is not fatal.
			print_warning($err_msg);
?>
		<br />
		The installer was unable to save the configuration data.  Please save the following data in an XML file.  Do not forget to edit <tt>config_loc.inc.php</tt> file in the ReservesDirect directory to specify the configuration location.
		<br />
		<textarea rows="10" cols="100" wrap="off"><?php echo $config_xml_string; ?></textarea>
<?php				
		}
	}
	
	
	/**
	 * Parses large string from MySQL dump file into individual statements and returns statements as array.
	 * Adapted from phpMyAdmin 2.10.1 libraries/import/sql.php script
	 *
	 * @param string $buffer Contents of MySQL dump file
	 * @param int $mysql_int_version MySQL server version as an int (i.e. 5037 for 5.0.37);  Not sure this is really necessary
	 * @return array
	 */
	function parse_batch_sql($buffer, $mysql_int_version) {
		//array to store individual queries
		$queries = array();
		
		$finished = $error = $timeout_passed = false;
		$i = $start_pos = $len = 0;
		$sql = '';
		$sql_delimiter = ';';
		
		 // Current length of our buffer
		$len = strlen($buffer);
		// Grab some SQL queries out of it
		while ($i < $len) {
			$found_delimiter = false;
			// Find first interesting character, several strpos seem to be faster than simple loop in php:
			//while (($i < $len) && (strpos('\'";#-/', $buffer[$i]) === FALSE)) $i++;
			//if ($i == $len) break;
			$oi = $i;
			$p1 = strpos($buffer, '\'', $i);
			if ($p1 === FALSE) {
				$p1 = 2147483647;
			}
			$p2 = strpos($buffer, '"', $i);
			if ($p2 === FALSE) {
				$p2 = 2147483647;
			}
			$p3 = strpos($buffer, $sql_delimiter, $i);
			if ($p3 === FALSE) {
				$p3 = 2147483647;
			} else {
			$found_delimiter = true;
			}
			$p4 = strpos($buffer, '#', $i);
			if ($p4 === FALSE) {
				$p4 = 2147483647;
			}
			$p5 = strpos($buffer, '--', $i);
			if ($p5 === FALSE || $p5 >= ($len - 2) || $buffer[$p5 + 2] > ' ') {
				$p5 = 2147483647;
			}
			$p6 = strpos($buffer, '/*', $i);
			if ($p6 === FALSE) {
				$p6 = 2147483647;
			}
			$p7 = strpos($buffer, '`', $i);
			if ($p7 === FALSE) {
				$p7 = 2147483647;
			}
			$i = min ($p1, $p2, $p3, $p4, $p5, $p6, $p7);
			unset($p1, $p2, $p3, $p4, $p5, $p6, $p7);
			if ($i == 2147483647) {
				$i = $oi;
				if (!$finished) {
					break;
				}
				// at the end there might be some whitespace...
				if (trim($buffer) == '') {
					$buffer = '';
					$len = 0;
					break;
				}
				// We hit end of query, go there!
				$i = strlen($buffer) - 1;
			}

			// Grab current character
			$ch = $buffer[$i];
			
			// Quotes
			if (!(strpos('\'"`', $ch) === FALSE)) {
				$quote = $ch;
				$endq = FALSE;
				while (!$endq) {
					// Find next quote
					$pos = strpos($buffer, $quote, $i + 1);
					// No quote? Too short string
					if ($pos === FALSE) {
						// We hit end of string => unclosed quote, but we handle it as end of query
						if ($finished) {
							$endq = TRUE;
							$i = $len - 1;
						}
						break;
					}
					// Was not the quote escaped?
					$j = $pos - 1;
					while ($buffer[$j] == '\\') $j--;
					// Even count means it was not escaped
					$endq = (((($pos - 1) - $j) % 2) == 0);
					// Skip the string
					$i = $pos;
				}
				if (!$endq) {
					break;
				}
				$i++;
				// Aren't we at the end?
				if ($finished && $i == $len) {
					$i--;
				} else {
					continue;
				}
			}

			// Not enough data to decide
			if ((($i == ($len - 1) && ($ch == '-' || $ch == '/'))
				|| ($i == ($len - 2) && (($ch == '-' && $buffer[$i + 1] == '-') || ($ch == '/' && $buffer[$i + 1] == '*')))
			) && !$finished) {
				break;
			}
			
			// Comments
			if ($ch == '#'
				|| ($i < ($len - 1) && $ch == '-' && $buffer[$i + 1] == '-' && (($i < ($len - 2) && $buffer[$i + 2] <= ' ') || ($i == ($len - 1) && $finished)))
				|| ($i < ($len - 1) && $ch == '/' && $buffer[$i + 1] == '*')
				) {
				// Copy current string to SQL
				if ($start_pos != $i) {
					$sql .= substr($buffer, $start_pos, $i - $start_pos);
				}
				// Skip the rest
				$j = $i;
				$i = strpos($buffer, $ch == '/' ? '*/' : "\n", $i);
				// didn't we hit end of string?
				if ($i === FALSE) {
					if ($finished) {
						$i = $len - 1;
					} else {
						break;
					}
				}
				// Skip *
				if ($ch == '/') {
					// Check for MySQL conditional comments and include them as-is
					if ($buffer[$j + 2] == '!') {
						$comment = substr($buffer, $j + 3, $i - $j - 3);
						if (preg_match('/^[0-9]{5}/', $comment, $version)) {
							if ($version[0] <= $mysql_int_version) {
								$sql .= substr($comment, 5);
							}
						} else {
							$sql .= $comment;
						}
					}
					$i++;
				}
				// Skip last char
				$i++;
				// Next query part will start here
				$start_pos = $i;
				// Aren't we at the end?
				if ($i == $len) {
					$i--;
				} else {
					continue;
				}
			}
			
			// End of SQL
			if ($found_delimiter || ($finished && ($i == $len - 1))) {
				$tmp_sql = $sql;
				if ($start_pos < $len) {
					$length_to_grab = $i - $start_pos;
					if (!$found_delimiter) {
						$length_to_grab++;
					}
					$tmp_sql .= substr($buffer, $start_pos, $length_to_grab);
					unset($length_to_grab);
				}
				// Do not try to execute empty SQL
				if (!preg_match('/^([\s]*;)*$/', trim($tmp_sql))) {
					$sql = $tmp_sql;
			
					//store query in array
					$queries[] = $sql;
					
					$buffer = substr($buffer, $i + strlen($sql_delimiter));
					// Reset parser:
					$len = strlen($buffer);
					$sql = '';
					$i = 0;
					$start_pos = 0;
					// Any chance we will get a complete query?
					//if ((strpos($buffer, ';') === FALSE) && !$finished) {
					if ((strpos($buffer, $sql_delimiter) === FALSE) && !$finished) {
						break;
					}
				} else {
					$i++;
					$start_pos = $i;
				}
			}
		} // End of parser loop
		
		return $queries;
	}
	
	
	/**
	 * Print out messages in different colors
	 * 
	 * @param string $msg
	 */
	function print_error($msg) {
		echo '<br /><span class="error">ERROR: '.$msg.'</span><br />';
	}
	function print_warning($msg) {
		echo '<br /><span class="warning">WARNING: '.$msg.'</span><br />';
	}		
	function print_success($msg) {
		echo '<br /><span class="success">SUCCESS: '.$msg.'</span><br />';
	}
	
	/**
	 * modified error message; gives link to restart @ step 3
	 *
	 * @param string $msg
	 */
	function print_step3_error($msg) {
		print_error($msg);
?>
		<p />
		Fix this manually and <a href="<?php echo $_SERVER['PHP_SELF']; ?>?step=three">retry step 3</a>
		<br />
		or
		<br />
		<a href="<?php echo $_SERVER['PHP_SELF']; ?>">Start Over</a>			
<?php		
	}
?>

	</body>
</html>