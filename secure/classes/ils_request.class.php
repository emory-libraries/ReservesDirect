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
	private $barcode;
	private $user_net_id;
	private $user_ils_id;
	private $ils_course;	
	
	
	/**
	 * Constructor - does NOT init object from DB
	 */
	function __construct() {}
	
	
	/**
	 * Initializes object based on barcode; returns TRUE on success, FALSE on failure
	 *
	 * @param string $barcode
	 * @return boolean
	 */
	function getByBarcode($barcode) {
		global $g_dbConn;
		
		if(empty($barcode)) {
			return false;
		}
		
		$sql = "SELECT request_id, date_added, barcode, user_net_id, user_ils_id, ils_course
				FROM ils_requests WHERE barcode = ?";
		$rs = $g_dbConn->query($sql, $barcode);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		if($rs->numRows() > 0) {		
			list($this->request_id, $this->date_added, $this->barcode, $this->user_net_id, $this->user_ils_id, $this->ils_course) = $rs->fetchRow();
			return true;
		}
		else {
			return false;
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
		
		$sql = "SELECT request_id, date_added, barcode, user_net_id, user_ils_id, ils_course
				FROM ils_requests WHERE request_id = !";
		$rs = $g_dbConn->query($sql, $request_id);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		if($rs->numRows() > 0) {		
			list($this->request_id, $this->date_added, $this->barcode, $this->user_net_id, $this->user_ils_id, $this->ils_course) = $rs->fetchRow();
			return true;
		}
		else {
			return false;
		}
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
	function getILSUserID() {
		return $this->user_ils_id;
	}
}

?>