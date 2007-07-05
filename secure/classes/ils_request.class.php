<?php
/*******************************************************************************
ils_request.class.php
Manipulates ILS-request data

Created by Dmitriy Panteleyev (dpantel@gmail.com)

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

class ILS_Request {
	private $request_id;
	private $date_added;
	private $ils_control_key;
	private $user_net_id;
	private $user_ils_id;
	private $ils_course;
	private $requested_loan_period;	
	
	
	/**
	 * Constructor - Initializes object by request_id, if provided
	 * 
	 * @param int $request_id (optional)
	 */
	function __construct($request_id=null) {
		if(!empty($request_id)) {
			$this->getByID($request_id);
		}
	}
	
	
	/**
	 * Initializes object based on id; returns TRUE on success, FALSE on failure
	 *
	 * @param string $request_id
	 * @return boolean
	 */
	function getByID($request_id) {
		global $g_dbConn;
		
		if(empty($request_id)) {
			return false;
		}
		
		$sql = "SELECT request_id, date_added, ils_control_key, user_net_id, user_ils_id, ils_course, requested_loan_period
				FROM ils_requests WHERE request_id = !";
		$rs = $g_dbConn->query($sql, $request_id);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		if($rs->numRows() > 0) {
			list($this->request_id, $this->date_added, $this->ils_control_key, $this->user_net_id, $this->user_ils_id, $this->ils_course, $this->requested_loan_period) = $rs->fetchRow();
			return true;
		}
		else {
			return false;
		}
	}
	
	
	/**
	 * Retrieves rows matching on control key; Returns array of ILS_Request objects
	 *
	 * @param string $control_key
	 * @return array
	 */
	function getRequestsByControlKey($control_key) {
		global $g_dbConn;
		
		if(empty($control_key)) {
			return array();
		}
		
		//most control keys in DB have 'ocm' prefix, but the ils keys have 'o' prefix
		$control_key = eregi_replace('ocm', 'o', trim($control_key));
		
		$sql = "SELECT request_id
				FROM ils_requests WHERE ils_control_key = ?";
		$rs = $g_dbConn->query($sql, $control_key);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$ils_requests = array();
		while($row = $rs->fetchRow()) {
			$ils_requests[] = new ILS_Request($row[0]);
		}
		
		return $ils_requests;
	}

	
	/**
	 * Checks whether dept + course# provided in the feed match any aliases of the supplied CI
	 *
	 * @param int $ci_id
	 * @return boolean
	 */
	function doesCourseMatch($ci_id) {
		global $g_dbConn;

		if(empty($ci_id)) {
			return false;
		}
		
		$sql = "SELECT d.abbreviation, c.course_number
				FROM course_aliases AS ca
					JOIN courses AS c ON c.course_id = ca.course_id
					JOIN departments AS d ON d.department_id = c.department_id
				WHERE ca.course_instance_id = !";
		$rs = $g_dbConn->query($sql, $ci_id);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		while($row = $rs->fetchRow()) {
			//if ILS course contains the course number and department abbreviation, then it's a match
			if((stripos($this->ils_course, $row[0]) !== false) && (strpos($this->ils_course, $row[1]) !== false)) {
				return true;
			}
		}
		
		//no match found
		return false;
	}
	
	
	/**
	 * Deletes row matching this object from DB
	 */
	function deleteRow() {
		global $g_dbConn;
		
		if(!empty($this->request_id)) {
			$sql = "DELETE FROM ils_requests WHERE request_id = !";
			$rs = $g_dbConn->query($sql, $this->request_id);
			if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		}
	}
	
	
	/**
	 * Returns request ID (row ID)
	 *
	 * @return int
	 */
	function getRequestID() {
		return $this->request_id;
	}
	
	/**
	 * returns user ILS id
	 *
	 * @return string
	 */
	function getUserILSID() {
		return $this->user_ils_id;
	}
	
	
	function getUserNetID() {
		return $this->user_net_id;
	}
	
	function getCourseName() {
		return $this->ils_course;
	}
	
	/**
	 * Returns requested loan period
	 *
	 * @return string
	 */
	function getRequestedLoanPeriod() {
		return $this->requested_loan_period;
	}
}

?>