<?
/*******************************************************************************
staff.class.php
Staff Interface

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
require_once("secure/interface/instructor.class.php");
require_once("secure/classes/request.class.php");
require_once("secure/classes/courseInstance.class.php");
require_once("secure/common.inc.php");

class staff extends instructor
{
	var $sp;

	function staff($userName)
	{
		if (is_null($userName)) trigger_error($userName . " has not been authorized as staff", E_ERROR);
		else $this->getUserByUserName($userName);
	}

	/**
	* @return user object
	* @param user $user will be set
	* @desc accepts the userid of the user to be administered return appropriate user object
	*/
	/*
	function setAdministeredUser($userID)
	{
		$u = new user($userID);
		switch ($u->getUserClass())
		{
			case 'staff':
				$r_user = new staff($u->getUsername());
			break;
			case 'instructor':
				$r_user = new instructor($u->getUsername());
			break;
			case 'proxy':
				$r_user = new proxy($u->getUsername());
			break;
			case 'custodian':
				$r_user = new custodian($u->getUsername());
			break;
			default:
				$r_user = new student($u->getUsername());
		}

		unset ($u);
		return $r_user;
	}
	*/
	/*
	function getEditableCourseInstances($userID)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$d = date("Y-m-d");

				$sql  = "SELECT DISTINCT ci.course_instance_id "
				.  		"FROM access as a "
				.  		"  JOIN course_aliases as ca on a.alias_id = ca.course_alias_id "
				.		"  JOIN course_instances as ci ON ca.course_instance_id = ci.course_instance_id "
				.		"WHERE a.user_id = ! AND a.permission_level >= 2 " //2 = proxy minimal edit permission
				;

				$sql .= "AND '$d' <= ci.expiration_date ";  //get any current or future classes

				$sql .=	"ORDER BY ci.expiration_date ASC, ci.status DESC";
		}
		$rs = $g_dbConn->query($sql, $userID);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		unset($this->courseInstances);
		while ($row = $rs->fetchRow()) {
			$this->courseInstances[] = new courseInstance($row[0]);
		}
		return $this->courseInstances;
	}
	*/
	function getCourseInstances($userID=null)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$d = date("Y-m-d");

				$sql  = "SELECT DISTINCT ci.course_instance_id "
				.  		"FROM access as a "
				.  		"  JOIN course_aliases as ca on a.alias_id = ca.course_alias_id "
				.		"  JOIN course_instances as ci ON ca.course_instance_id = ci.course_instance_id "
				.		"WHERE a.user_id = ! "
				;

				if (!is_null($userID))
					$sql .= "AND a.permission_level >= 2 "; //2 = proxy minimal edit permission
				$sql .= "AND '$d' <= ci.expiration_date ";  //get any current or future classes

				$sql .=	"ORDER BY ci.expiration_date ASC, ci.status DESC";
		}

		if (!is_null($userID))
			$rs = $g_dbConn->query($sql, $userID);
		else
			$rs = $g_dbConn->query($sql, $this->userID);

		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		unset($this->courseInstances);
		$this->courseInstances = array();
		while ($row = $rs->fetchRow()) {
			$this->courseInstances[] = new courseInstance($row[0]);
		}
		return $this->courseInstances;
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
		//echo $g_reserveScript . "?itemID=$barcode&borrowerID=$borrowerID&courseID=$course&reserve_desk=$desk&circ_rule=$circRule&alt_circ=$altCirc&expiration=$eDate&cpy=$copy<BR>";


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

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT library_id "
					.  "FROM staff_libraries "
					.  "WHERE user_id = !"
					;
		}

		$rs = $g_dbConn->query($sql, $this->userID);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$row = $rs->fetchRow();
		return $row[0];
	}
}
