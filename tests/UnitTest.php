<?
/*******************************************************************************
UnitTest.php
Base UnitTest Class

Created by Jason White (jbwhite@emory.edu)

This file is part of ReservesDirect

Copyright (c) 2004-2009 Emory University, Atlanta, Georgia.

Licensed under the ReservesDirect License, Version 1.0 (the "License");      
you may not use this file except in compliance with the License.     
You may obtain a copy of the full License at                              
http://www.reservesdirect.org/licenses/LICENSE-1.0

ReservesDirect is distributed in the hope that it will be useful,
but is distributed "AS IS" and WITHOUT ANY WARRANTY, without even the
implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
PURPOSE, and without any warranty as to non-infringement of any third
party's rights.  See the License for the specific language governing         
permissions and limitations under the License.

ReservesDirect is located at:
http://www.reservesdirect.org/

*******************************************************************************/

if (! defined('SIMPLE_TEST')) {
    define('SIMPLE_TEST', 'simpletest/');
}
require_once(SIMPLE_TEST . 'unit_tester.php');
require_once(SIMPLE_TEST . 'reporter.php');

require_once("bootstrap.inc.php");

class UnitTest extends UnitTestCase
{

	/**
	 * @return void
	 * @param string sql file path
	 * @desc reads sqlFile and executes each statement against the database
	**/
	protected function loadDB($sqlFile)
	{
		global $g_dbConn;	
		
		//strip comments
		$file_contents = preg_replace("/^--.*/m", "", file_get_contents($sqlFile));
		
		//
		//	PEAR::DB query does not like multiple statements
		//		splitting sqlFile on ;\n should break out statements which can be executed individually
		//
		$pattern = "/;\n/";
		$sql_statements = preg_split($pattern, $file_contents);		
		
		foreach ($sql_statements as $sql)
		{
			if (!empty($sql)) $rs = $g_dbConn->query($sql);
		}
		
		if (DB::isError($rs)) {
			echo ("ERROR loading Fixture  " . $rs->getMessage() . "<BR>"); 
		}
		
	}	
}