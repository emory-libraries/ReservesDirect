<?
/*******************************************************************************
term.class.php
term object handles term table

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
require_once("secure/classes/term.class.php");

class terms
{
	function terms() {}
	
	function getTerms()
	{
		global $g_dbConn;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = 	"SELECT term_id "
				.		"FROM terms "
				.		"WHERE end_date >= ? "
				.		"ORDER BY sort_order"
				;			
				
				$d = date("Y-m-d");
		}
		
		$rs = $g_dbConn->query($sql, $d);		
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		unset($tmpArray);
		while($rows = $rs->fetchRow()){
			$tmpArray[] = new term($rows[0]);
		}
		return $tmpArray;
	}
	
	function getCurrentTerm()
	{
		global $g_dbConn;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = 	"SELECT term_id "
				.		"FROM terms "
				.		"WHERE begin_date <= ? AND ? <= end_date "
				.		"LIMIT 1"
				;			
				
				$d = date("Y-m-d");
		}
		
		$rs = $g_dbConn->query($sql, array($d, $d));					
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		$rows = $rs->fetchRow();
		return new term($rows[0]);	
	}
}
?>