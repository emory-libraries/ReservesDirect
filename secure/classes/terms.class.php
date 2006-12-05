<?
/*******************************************************************************
term.class.php
term object handles term table

Created by Jason White (jbwhite@emory.edu)

This file is part of ReservesDirect

Copyright (c) 2004-2006 Emory University, Atlanta, Georgia.

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
require_once("secure/classes/term.class.php");

class terms
{
	function terms() {}

	function getTerms($getAll=false)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = 	"SELECT term_id "
				.		"FROM terms "
				.		"WHERE end_date >= ? "
				.		"ORDER BY sort_order ASC LIMIT 4"
				;
				$d = date("Y-m-d");
				
				if ($getAll)
				{
					$sql = 	"SELECT term_id "
					.		"FROM terms "
					.		"ORDER BY sort_order ASC"
					;
					$d = null;
				}
				
		}

		$rs = $g_dbConn->query($sql, $d);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		unset($tmpArray);
		while($rows = $rs->fetchRow()){
			$tmpArray[] = new term($rows[0]);
		}

		return $tmpArray;
	}

	function getCurrentTerm() {
		$term = new term();
		$term->getTermByDate(date("Y-m-d"));
		return $term;
	}
}
?>
