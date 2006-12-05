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
class term
{
	public $term_id;
	public $sort_order;
	public $term_name;
	public $term_year;
	public $begin_date;
	public $end_date;
	
	
	function __construct($term_id=null) {
		if(!empty($term_id)) {
			$this->getTermByID($term_id);
		}
	}
	
	
	/**
	 * @return boolean
	 * @param int $term_id Term ID
	 * @desc Fetches the object, based on term ID. Returns true on success, false otherwise;
	 */
	function getTermByID($term_id) {
		global $g_dbConn;
		
		if(empty($term_id)) {
			return false;
		}
		
		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql = "SELECT term_id, sort_order, term_name, term_year, begin_date, end_date FROM terms WHERE term_id = $term_id LIMIT 1";
		}
		$rs = $g_dbConn->query($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		if($rs->numRows()==1) {
			list($this->term_id, $this->sort_order, $this->term_name, $this->term_year, $this->begin_date, $this->end_date) = $rs->fetchRow();
			return true;
		}
		else {
			return false;
		}
	}

	
	/**
	 * @return boolean
	 * @param string $date The date; format: YYYY-MM-DD
	 * @desc If possible, sets this object to the term spanning date and returns true; otherwise returns false;
	 */
	public function getTermByDate($date) {
		global $g_dbConn;
		
		if(empty($date)) {
			$date = date("Y-m-d");
		}

		switch ($g_dbConn->phptype) {
			default:	//mysql
				$sql = "SELECT term_id FROM terms WHERE begin_date <= '$date' AND '$date' <= end_date LIMIT 1";
		}

		$rs = $g_dbConn->getOne($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		if(empty($rs)) {
			return false;
		}
		else {
			$this->getTermByID($rs);
			return true;
		}
	}
	
	
	/**
	 * @return boolean
	 * @param string $name Term name - spring/summer/fall/etc
	 * @param int $year Term year - YYYY
	 * @desc If possible, sets this object to the term matching name/year and returns true; otherwise returns false;
	 */
	public function getTermByName($name, $year) {
		global $g_dbConn;
		
		if(empty($name) || empty($year)) {
			return false;
		}
		
		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql = "SELECT term_id FROM terms WHERE term_name = '$name' AND term_year = $year LIMIT 1";
		}
		
		$rs = $g_dbConn->getOne($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		if(empty($rs)) {
			return false;
		}
		else {
			$this->getTermByID($rs);
			return true;
		}
	}
	

	function getTermID() { return $this->term_id; }
	function getSortOrder() { return $this->sort_order; }
	function getTerm() { return $this->term_name . " " . $this->term_year; }
	function getTermName() { return $this->term_name; }
	function getTermYear() { return $this->term_year; }
	function getBeginDate() { return $this->begin_date; }
	function getEndDate() { return $this->end_date; }
}
?>
