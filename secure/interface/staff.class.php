<?
/*******************************************************************************
staff.class.php
Staff Interface

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
		while ($row = $rs->fetchRow()) {	
			$this->courseInstances[] = new courseInstance($row[0]);
		}	
		return $this->courseInstances;
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
	function getRequests($unit='all')
	{
		global $g_dbConn;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
			
			if ($unit == 'all')
			{
				$sql = "SELECT request_id "
					.  "FROM requests "						  
					.  "WHERE date_processed is null"
					;
			} else {
				$sql = "SELECT r.request_id "
					.  "FROM requests AS r "
					.  	"JOIN items AS i ON r.item_id = i.item_id AND r.date_processed IS NULL "
					.  	"JOIN course_instances AS ci ON r.course_instance_id = ci.course_instance_id "
					.  	"JOIN course_aliases AS ca ON ci.primary_course_alias_id = ca.course_alias_id "
					.  	"JOIN courses AS c ON ca.course_id = c.course_id "
					.  	"JOIN departments AS d ON c.department_id = d.department_id AND d.status IS NULL "
					.  	"JOIN libraries AS l ON d.library_id = l.library_id "
					.  "WHERE "
					.  	"CASE "
					.  		"WHEN i.item_group = 'MONOGRAPH'  THEN l.monograph_library_id  = $unit "
					.  		"WHEN i.item_group = 'MULTIMEDIA' THEN l.multimedia_library_id = $unit "
					.	"END"
					;
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
				
			$tmpArray[] = $tmpRequest;
		}
		return $tmpArray;
	}
	
	/**
	* @return void
	* @param $barcode, $copy, $borrowerID, $courseID, $reservesDesk, $circRule, $altCirc, $expiration
	* @desc create the OPAC record
	*/
	function createOPAC_record($barcode, $copy, $borrowerID, $desk, $term, $circRule, $altCirc, $expiration)
	{
		global $g_reserveScript, $g_desks;
		
		$course = strtoupper($g_desks[$desk] . $term);
		
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
        	return "There was a problem setting the location and circ-rule in EUCLID. <BR>EUCLID returned:  $returnStatus.";
        } else 
        	return "Location and circ-rule have been successfully set in EUCLID";
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