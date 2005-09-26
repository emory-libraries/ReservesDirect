<?
/*******************************************************************************
department.class.php
Department Primitive Object

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
require_once("secure/classes/library.class.php");

class department extends library
{
	//Attributes
	public $deptID;
	public $name;
	public $abbr;

	function department($deptID=null)
	{
		global $g_dbConn;

		if (!is_null($deptID))
		{
			$this->deptID = $deptID;

			switch ($g_dbConn->phptype)
			{
				default: //'mysql'
					$sql  = "SELECT d.name, d.abbreviation, l.library_id, l.name, l.nickname, l.url "
						  . "FROM departments as d LEFT JOIN libraries as l ON d.library_id = l.library_id "
						  . "WHERE d.department_id = !";

			}

			$rs = $g_dbConn->query($sql, $this->deptID);
			if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

			$row = $rs->fetchRow();
				$this->name 			= $row[0];
				$this->abbr 			= $row[1];
				$this->libraryID 		= $row[2];
				$this->library 			= $row[3];
				$this->libraryNickname 	= $row[4];
				$this->libraryURL 		= $row[5];
		}

	}


	/**
	* @return department recordset
	* @desc returns all departments
	*/
	function getAllDepartments()
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql  = "SELECT d.department_id, d.abbreviation, d.name "
					  . "FROM departments d "
					  .	"WHERE d.name IS NOT NULL "
					  . "ORDER BY d.abbreviation"
					  ;

		}

		$rs = $g_dbConn->query($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$tmpArray = array();
		while ($row = $rs->fetchRow()) {
			$tmpArray[] = $row;
		}
		return $tmpArray;
	}

	function getDepartmentID() { return $this->deptID; }
	function getName() { return $this->name; }
	function getAbbr() { return $this->abbr; }
	
	/**
	 * Return an array of human readable loan periods from the db
	 */
	function getInstructorLoanPeriods()
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql  = "SELECT lp.loan_period, lpi.default "
					  . "FROM inst_loan_periods as lp "
					  . " JOIN inst_loan_periods_libraries as lpi ON lp.loan_period_id = lpi.loan_period_id "
					  .	"WHERE lpi.library_id = ! "
					  . "ORDER BY lp.loan_period_id"
					  ;

		}

		$rs = $g_dbConn->query($sql, $this->libraryID);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$tmpArray = null;
		while ($row = $rs->fetchRow(DB_FETCHMODE_ASSOC)) {
			$tmpArray[] = $row;
		}
		return $tmpArray;		
	}

}
?>