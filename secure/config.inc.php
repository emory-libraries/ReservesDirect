<?
/*******************************************************************************
config.inc.php
Read config.xml 

Reserves Direct 2.0

Copyright (c) 2004 Emory University General Libraries

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be included
in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

Created by Jason White (jbwhite@emory.edu)

Reserves Direct 2.0 is located at:
http://coursecontrol.sourceforge.net/

*******************************************************************************/
	require_once("DB.php");
	
	$xmlConfig = "config.xml";
	
	if (!is_readable($xmlConfig)) { trigger_error("Could not read configure xml file path=$xmlConfig", E_USER_ERROR); }
	
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
	
	$g_error_log			= (string)$configure->error_log;
	$g_errorEmail		 	= (string)$configure->errorEmail;
	$g_adminEmail		 	= (string)$configure->adminEmail;
	$g_reservesEmail		= (string)$configure->reservesEmail;
	
	$g_faxDirectory			= (string)$configure->faxDirectory;
	$g_faxURL				= (string)$configure->faxURL;
	
	$g_documentDirectory	= (string)$configure->documentDirectory;
	$g_documentURL			= (string)$configure->documentURL;
	$g_docCover				= (string)$configure->documentCover;
	
	$g_siteURL				= (string)$configure->siteURL;

	$g_specialUserEmail['subject']  = (string)$configure->specialUserEmail->subject;
	$g_specialUserEmail['msg']  = (string)$configure->specialUserEmail->msg;
	
	$g_EmailRegExp = (string)$configure->EmailRegExp;
	
	//zWidget configuration
	$g_zhost 			= (string)$configure->catalog->zhost;
	$g_zport 			= (string)$configure->catalog->zport;
	$g_zdb	 			= (string)$configure->catalog->zdb;
	$g_zReflector		= (string)$configure->catalog->zReflector;
	$g_reserveScript	= (string)$configure->catalog->reserve_script;
	$g_holdingsScript	= (string)$configure->catalog->holdings_script;
		
	$g_no_javascript_msg = (string)$configure->no_javascript_msg;

	$g_request_notifier_lastrun = (string)$configure->request_notifier->last_run;
?>
