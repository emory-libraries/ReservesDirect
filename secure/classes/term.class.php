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
class term
{
	public $term_id;
	public $sort_order;
	public $term_name;
	public $term_year;
	public $begin_date;
	public $end_date;

	function term($termID)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = 	"SELECT term_id, sort_order, term_name, term_year, begin_date, end_date "
				.		"FROM terms "
				.		"WHERE term_id = !"
				;
		}

		$rs = $g_dbConn->query($sql, $termID);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$row = $rs->fetchRow();
			$this->term_id 		= $row[0];
			$this->sort_order 	= $row[1];
			$this->term_name 	= $row[2];
			$this->term_year 	= $row[3];
			$this->begin_date 	= $row[4];
			$this->end_date 	= $row[5];
	}

	function getTermID() { return $this->term_id; }
	function getSortOrder() { return $this->sort_order; }
	function getTerm() { return $this->term_name . " " . $this->term_year; }
	function getTermName() { return $this->term_name; }
	function getTermYear() { return $this->term_year; }
	function getBeginDate() { return $this->begin_date; }
	function getEndDate() { return $this->end_date; }

	/**
	* @return date
	* @desc returns the date after which the term becomes modifiable
	*/
	function getModifyBeginDate()
	{
		$D = explode("-", $this->begin_date);

		$m = $D[1];
		$d = $D[2];
		$y = $D[0];

		if ($m == "01"){
			$m = "12";
			$y = $y - 1;
		} else
			$m = $m - 1;

		return "$y-$m-$d";
	}

	/**
	* @return date
	* @desc returns the date after which the term is nolonger modifiable
	*/
	function getModifyEndDate()
	{
		$D = explode("-", $this->end_date);

		$m = $D[1];
		$d = $D[2];
		$y = $D[0];

		if ($m == "12"){
			$m = "01";
			$y = $y + 1;
		} else
			$m = $m + 1;

		return "$y-$m-$d";
	}

}
?>