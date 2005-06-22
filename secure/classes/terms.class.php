<?
/*******************************************************************************
term.class.php
term object handles term table

Created by Jason White (jbwhite@emory.edu)

This file is part of GNU ReservesDirect 2.1

Copyright (c) 2004-2005 Emory University, Atlanta, Georgia.

ReservesDirect 2.1 is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

ReservesDirect 2.1 is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with ReservesDirect 2.1; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

Reserves Direct 2.1 is located at:
http://www.reservesdirect.org/

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
				.		"ORDER BY sort_order LIMIT 4"
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