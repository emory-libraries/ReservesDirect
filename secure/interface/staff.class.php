<?
/*******************************************************************************
staff.class.php
Staff Interface

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
require_once("secure/interface/instructor.class.php");
require_once("secure/classes/request.class.php");
require_once("secure/classes/courseInstance.class.php");
require_once("secure/common.inc.php");

class staff extends instructor
{
	var $sp;

	function staff($userName=null)
	{
		if (!is_null($userName)) {
			$this->getUserByUserName($userName);
			$this->role = 4;
		}
	}


	/**
	* @return return array of classes
	* @param $courseID - courseID to retrieve course instances for
	* @param $instructorID (optional) - instructor to retrieve course instances for
	* @desc return all classes for a given course, or given course and instructor
	*/
	function getCourseInstancesByCourse($courseID,$instructorID=null)
	{
		global $g_dbConn, $g_permission;


		if (!$instructorID) {
			switch ($g_dbConn->phptype)
			{
				default: //'mysql'
					$sql = "SELECT DISTINCT course_instance_id "
					.	   "FROM course_aliases  "
					.	   "WHERE course_id = !";

			}

			$rs = $g_dbConn->query($sql, array($courseID));
			if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		} else {

			switch ($g_dbConn->phptype)
			{
				default: //'mysql'
					$sql = "SELECT DISTINCT ca.course_instance_id "
					.	   "FROM course_aliases as ca  "
					.	   "JOIN access as a ON ca.course_alias_id = a.alias_id "
					.	   "WHERE ca.course_id = ! "
					.	   "AND a.user_id = ! "
					.      "AND a.permission_level = ".$g_permission['instructor']." ";

			}

			$rs = $g_dbConn->query($sql, array($courseID, $instructorID));
			if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		}

		$tmpArray = array();
		while($row = $rs->fetchRow())
		{
			$ci = new courseInstance($row[0]);
			$ci->getPrimaryCourse();
			$ci->getInstructors();
			$tmpArray[] = $ci;
		}
		return $tmpArray;
	}

	function selectUserForAdmin($userClass, $cmd)
	{
		$subordinates = common_getUsers('instructor');

		echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tbody>\n";
		echo "		<tr><td width=\"140%\"><img src=\../images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";
		echo "		<tr>\n";
        echo "			<td align=\"left\" valign=\"top\">\n";
        echo "				<table border=\"0\" align=\"center\" cellpadding=\"10\" cellspacing=\"0\">\n";
		echo "					<tr align=\"left\" valign=\"top\" class=\"headingCell1\">\n";
        echo "						<td width=\"50%\">Search by Instructor </td><td width=\"50%\">Search by Department</td>\n";
        echo "					</tr>\n";

        echo "					<tr>\n";
        echo "						<td width=\"50%\" align=\"left\" valign=\"top\" class=\"borders\" align=\"center\" NOWRAP>\n";

        echo "							<form method=\"post\" action=\"index.php\" name=\"frmReserveItem\">\n";
        //if (!is_null($courseInstance)) echo "					<input type=\"hidden\" name=\"ci\" value=\"$courseInstance\">\n";
    	echo "								<input type=\"hidden\" name=\"cmd\" value=\"$cmd\">\n";
		echo "								<input type=\"hidden\" name=\"u\" value=\"".$this->getUserID()."\">\n";
		echo "								<input type=\"submit\" name=\"Submit2\" value=\"Admin Your Classes\">\n";
		echo "							</form>\n";
        echo "							<br>\n";
        echo "							<form method=\"post\" action=\"index.php\" name=\"frmReserveItem\">\n";
    	//if (!is_null($courseInstance)) echo "					<input type=\"hidden\" name=\"ci\" value=\"$courseInstance\">\n";
        echo "								<input type=\"hidden\" name=\"cmd\" value=\"$cmd\">\n";
		echo "								<select name=\"u\">\n";
	    echo "									<option value=\"--\" selected>Choose an Instructor\n";
        foreach($subordinates as $subordinate)
		{
			echo "									<option value=\"" . $subordinate['user_id'] . "\">" . $subordinate['full_name'] . "</option>\n";
		}

        echo "								</select>\n";
        //echo "								<br>\n";
        //echo "								<br>\n";
        echo "								<input type=\"submit\" name=\"Submit2\" value=\"Select Instructor\">\n";
        echo "							</form>\n";
        echo "						</td>\n";
        echo "						<td width=\"50%\" align=\"left\" valign=\"top\" class=\"borders\" align=\"center\" NOWRAP>&nbsp;\n";
        echo "						</td>\n";
        echo "					</tr>\n";
        echo "				</table>\n";
		echo "			</td>\n";
		echo "		</tr>\n";
		echo "	</tbody>\n";
		echo "</table>\n";

	}


	//We can not extend multiple classes so the follow are duplicated in the custodian class

	function createSpecialUser($userName, $email, $date=null)
	{
		$this->sp = new specialUser();
		return $this->sp->createNewSpecialUser($userName, $email, $date);
	}

	function resetSpecialUserPassword($userName)
	{
		$this->sp = new specialUser();
		return $this->sp->resetPassword($userName);
	}

	function getSpecialUsers()
	{
		//this function is duplicated in the custodian class
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT sp.user_id FROM special_users as sp JOIN users as u ON sp.user_id = u.user_id ORDER BY u.username";
		}
		$rs = $g_dbConn->query($sql);

		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$tmpArray = array();
		while($row = $rs->fetchRow())
		{
			$tmpArray[] = new user($row[0]);
		}
		return $tmpArray;
	}

	/**
	* @return void
	* @param unit default=all unit to get reserves for
	* @desc get all unprocessed requests
	*/
	function getRequests($unit='all', $sort=null)
	{
		global $g_dbConn, $g_permission;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'

			$sql = "SELECT DISTINCT r.request_id, d.abbreviation, c.course_number, u.last_name "
				.  "FROM requests AS r "
				.  	"JOIN items AS i ON r.item_id = i.item_id AND r.date_processed IS NULL "
				.  	"JOIN course_instances AS ci ON r.course_instance_id = ci.course_instance_id "
				.  	"JOIN course_aliases AS ca ON ci.primary_course_alias_id = ca.course_alias_id "
				.  	"JOIN courses AS c ON ca.course_id = c.course_id "
				.  	"JOIN departments AS d ON c.department_id = d.department_id AND d.status IS NULL "
				.  	"JOIN libraries AS l ON d.library_id = l.library_id "
				.	"JOIN access AS a ON ca.course_alias_id = a.alias_id "
				.	"JOIN users AS u on a.user_id = u.user_id AND a.permission_level = " . $g_permission['instructor'] . " " 
				;

			
			if ($unit != 'all')
			{
				$sql .=  "WHERE "
					 .  	"CASE "
					 .  		"WHEN i.item_group = 'MONOGRAPH'  THEN l.monograph_library_id  = $unit "
					 .  		"WHEN i.item_group = 'MULTIMEDIA' THEN l.multimedia_library_id = $unit "
					 .	"END "
				;
			}
			
			$sql .= "GROUP BY r.request_id ";
			
			switch ($sort)
			{
				case "instructor":
					$sql .= " ORDER BY u.last_name";
				break;
				
				case "class":
					$sql .= " ORDER BY d.abbreviation, c.course_number";
				break;
				
				case "date":
				default:
					$sql .= " ORDER BY r.request_id";					
			}
			
		}
		$rs = $g_dbConn->query($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$tmpArray = array();
		while ($row = $rs->fetchRow())
		{
			$tmpRequest = new request($row[0]);
				$tmpRequest->getRequestedItem();
				$tmpRequest->getRequestingUser();
				$tmpRequest->getReserve();
				$tmpRequest->getCourseInstance();
				$tmpRequest->courseInstance->getPrimaryCourse();
				$tmpRequest->courseInstance->getCrossListings();
				//$tmpRequest->getHoldings();
			$tmpArray[] = $tmpRequest;
		}
		return $tmpArray;
	}

	/**
	* @return void
	* @param $barcode, $copy, $borrowerID, $courseID, $reservesDesk, $circRule, $altCirc, $expiration
	* @desc create the ILS record
	*/
	function createILS_record($barcode, $copy, $borrowerID, $libraryID, $term, $circRule, $altCirc, $expiration)
	{
		global $g_reserveScript, $g_catalogName;

		$reservesDesk = new library($libraryID);

		$desk = $reservesDesk->getReserveDesk();
		$course = strtoupper($reservesDesk->getILS_prefix() . $term);

		list($Y,$M,$D) = split("-", $expiration);
		$eDate = "$M/$D/$Y";
	        if (isset($_SESSION['debug'])) { echo $g_reserveScript . "?itemID=$barcode&borrowerID=$borrowerID&courseID=$course&reserve_desk=$desk&circ_rule=$circRule&alt_circ=$altCirc&expiration=$eDate&cpy=$copy<BR>"; }


		$fp = fopen($g_reserveScript . "?itemID=$barcode&borrowerID=$borrowerID&courseID=$course&reserve_desk=$desk&circ_rule=$circRule&alt_circ=$altCirc&expiration=$eDate&cpy=$copy", "r");

        $rs = array();
        while (!feof ($fp)) {
        	array_push($rs, @fgets($fp, 1024));
        }
        $returnStatus = join($rs, "");

        $returnStatus = eregi_replace("<head>.*</head>", "", $returnStatus);
        $returnStatus = ereg_replace("<[A-z]*>", "", $returnStatus);
        $returnStatus = ereg_replace("</[A-z]*>", "", $returnStatus);

        $returnStatus = ereg_replace("<!.*\">", "", $returnStatus);
        $returnStatus = ereg_replace("\n", "", $returnStatus);

        if(!ereg("outcome=OK", $returnStatus)){
        	return "There was a problem setting the location and circ-rule in $g_catalogName. <BR>$g_catalogName returned:  $returnStatus.";
        } else
        	return "Location and circ-rule have been successfully set in $g_catalogName";
	}

	function getSpecialUserMsg() { return $this->sp->getMsg(); }

	function getStaffLibrary()
	{
		global $g_dbConn;
		
		if (!is_null($this->getUserID()))
		{
			switch ($g_dbConn->phptype)
			{
				default: //'mysql'
					$sql = "SELECT library_id "
						.  "FROM staff_libraries "
						.  "WHERE user_id = !"
						;
			}
	
			$rs = $g_dbConn->query($sql, $this->getUserID());		
			if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	
			$row = $rs->fetchRow();
			return $row[0];
		} else {
			return null;
		}
	}

	function assignStaffLibrary($libraryID)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql_find = "SELECT library_id "
					.  "FROM staff_libraries "
					.  "WHERE user_id = !"
					;
					
				$sql_in = "INSERT INTO staff_libraries (library_id, permission_level_id, user_id) VALUES (!, !, !)";
				$sql_up = "UPDATE staff_libraries SET library_id = !, permission_level_id = ! WHERE user_id = !";
		}

		$rs = $g_dbConn->query($sql_find, $this->getUserID());
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
			
		if ($rs->numRows() < 1)
			$rs = $g_dbConn->query($sql_in, array($libraryID, $this->getRole(), $this->getUserID()));	
		else
			$rs = $g_dbConn->query($sql_up, array($libraryID, $this->getRole(), $this->getUserID()));	 	

		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}		
}
