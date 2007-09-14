<?
/*******************************************************************************
users.class.php
user container

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
require_once("secure/classes/user.class.php");
require_once("secure/classes/courseInstance.class.php");
//require the admin interface
//this will require all the other interfaces (cascade)
require_once('secure/interface/admin.class.php');
//except custodian which must be included separately
require_once('secure/interface/custodian.class.php');

class users
{
	public $userList = array();

	function users() { $userList = null; }

	function initUser($userClass, $userName)
	{	
		//When comparing a string and an int, PHP will convert the string to an int/float.
		//Any string not containing a digit will become 0 (zero) --> ex: intval('admin') => 0)
		//Because of this the numeric cases of the switch below must be expressed as strings --> ex: '0'
		//This works if $userClass is a string --> ('admin' == '0') => false
		//And if $userClass is an int --> (5 == '5') => (5 == 5) => true		
		switch ($userClass)
		{
			case '0':
			case 'student':
				$u = new student($userName);
			break;

			case '1':
			case 'custodian':
				$u = new custodian($userName);
			break;

			case '2':
			case 'proxy':
				$u = new proxy($userName);
			break;

			case '3':
			case 'instructor':
				$u = new instructor($userName);
			break;

			case '4':
			case 'staff':
				$u = new staff($userName);
			break;

			case '5':
			case 'admin':
				$u = new admin($userName);
			break;

			default:
				trigger_error("userClass not valid", E_ERROR);
		}
		
		return $u;
	}

	function search($term, $qry, $role=null)
	{
		if ($qry != "")
			switch ($term)
			{
				case 'last_name':
					$this->getUsersByLastName($qry, $role);
					break;
				case 'username':
				case 'user_name':
					$this->getUsersByUserName($qry, $role);
					break;
				default:
					$this->getUsersByNameOrUserName($qry, $role);
			}
	}

	function getUsersByLastName($lastName, $role=null)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = 	"SELECT user_id, last_name, first_name, username "
				.		"FROM users "
				.		"WHERE last_name LIKE ? ";

				if (!is_null($role)) $sql .= "AND dflt_permission_level >= " . $role . " ";

				$sql .=	"ORDER BY last_name, first_name, username";// LIMIT 30";
				
				$lName = "%$lastName%";
		}

		$rs = $g_dbConn->query($sql, array($lName));

		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		unset ($this->userList);
		while($row = $rs->fetchRow())
			$this->userList[] = new user($row[0]);			
	}

	function getUsersByNameOrUserName($qry, $role=null)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				list($name1, $name2) = preg_split("/\s|(,\s)/", $qry);
				$name1 = ($name1 != "") ? "%".trim($name1) . "%" : "%";
				$name2 = ($name2 != "") ? "%".trim($name2) . "%" : "%";

				$sql = 	"SELECT user_id, last_name, first_name, username "
				.		"FROM users "
				.		"WHERE 1 ";

				if (!is_null($role)) $sql .= "AND dflt_permission_level >= " . $role . " ";
				
				$sql .= "AND ((first_name LIKE \"$name1\" AND last_name LIKE \"$name2\") ";
				$sql .= "OR (first_name LIKE \"$name2\" AND last_name LIKE \"$name1\") ";
				
				if($name2 == "%") //if search by 2 names we can exclude username search which cannot contain spaces
					$sql .= "OR (username LIKE '$name1') ";
				$sql .= ") ";					
					
				$sql .=	"ORDER BY last_name, first_name, username LIMIT 30";				
		}

		$rs = $g_dbConn->query($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		unset ($this->userList);
		while($row = $rs->fetchRow())
			$this->userList[] = new user($row[0]);			
	}	
	
	function getUsersByUsername($username, $role=null)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = 	"SELECT user_id, last_name, first_name, username "
				.		"FROM users "
				.		"WHERE username LIKE '%$username%' ";

				if (!is_null($role)) $sql .= "AND dflt_permission_level >= " . $role . " ";

				$sql .=	"ORDER BY last_name, first_name, username"// LIMIT 30"
				;
		}

		$rs = $g_dbConn->query($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		unset($this->userList);
		while($row = $rs->fetchRow())
			$this->userList[] = new user($row[0]);
	}

	function mergeUsers($keep, $merge)
	{
		global $g_dbConn, $g_permission;
		
		//select values from access table
		//merge access records maintaining highest permission_level
		//delete merged user
		
		$accessArray = array();
		
		switch ($g_dbConn->phptype)
		{

			/* By joining users and access we can get staff and admin who have registered in any class at the given level */

			default: //'mysql'
				$sql_find_user			= "SELECT  user_id, dflt_permission_level FROM users WHERE user_id in (!,!)";
				$sql_find 				= "SELECT alias_id, permission_level FROM access WHERE user_id in (!,!) ORDER BY alias_id";
				$sql_delete_access 		= "DELETE FROM access WHERE user_id in (!,!)";
				$sql_delete_user   		= "DELETE FROM users WHERE user_id = !";
				$sql_update_user		= "UPDATE users set dflt_permission_level = ! WHERE user_id = !";

				$sql_find_staff_lib		= "SELECT user_id FROM staff_libraries WHERE user_id in (!,!)";				
				$sql_update_staff_lib	= "UPDATE staff_libraries SET user_id = ! WHERE user_id = !";
				$sql_delete_staff_lib	= "DELETE FROM staff_libraries WHERE user_id = !";

				$sql_find_instr_attr	= "SELECT user_id FROM instructor_attributes WHERE user_id in (!,!)";				
				$sql_update_instr_attr	= "UPDATE instructor_attributes SET user_id = ! WHERE user_id = !";
				$sql_delete_instr_attr	= "DELETE FROM instructor_attributes WHERE user_id = !";
				
				$sql_find_not_trained	= "SELECT user_id FROM not_trained WHERE user_id in (!,!)";
				$sql_update_not_trained	= "UPDATE not_trained SET user_id = ! WHERE user_id = !";
				$sql_delete_not_trained	= "DELETE FROM not_trained WHERE user_id = !";
				
				$sql_update_personal_copies	= "UPDATE items set private_user_id = ! WHERE private_user_id = !";
				$sql_update_user_view_log	= "UPDATE user_view_log set user_id = ! WHERE user_id = !";
				$sql_update_special_users	= "UPDATE special_users set user_id = ! WHERE user_id = !";
		}

		$rs = $g_dbConn->query($sql_find_user, array($keep, $merge));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		while($row =  $rs->fetchRow(DB_FETCHMODE_ASSOC))
			$userData[$row['user_id']] = $row;
		
		$rs = $g_dbConn->query($sql_find, array($keep, $merge));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		while($row =  $rs->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$key = $row['alias_id'];
			if (key_exists($key, $accessArray))			
				//replace permission level with new value only if it is greater
				$accessArray[$key] = ($accessArray[$key] < $row['permission_level']) ? $row['permission_level'] : $accessArray[$key];
			else 
				$accessArray[$key] = $row['permission_level'];
			
		}
		
		//delete existing records
		$rs = $g_dbConn->query($sql_delete_access, array($keep, $merge));

		$rs = $g_dbConn->query($sql_delete_user, $merge);

		//merge Access Records
		if(count($accessArray) > 0)
		{
			//replace with condensed values
			switch ($g_dbConn->phptype)
			{
	
				/* By joining users and access we can get staff and admin who have registered in any class at the given level */
	
				default: //'mysql'
					$sql = "INSERT INTO access (user_id, alias_id, permission_level) VALUES ";
					
					$cnt = 0;
					foreach ($accessArray as $alias_id => $permission)
					{
						if ($cnt > 0) 
							$sql_values .= ", ";
							
						$sql_values .= "($keep, $alias_id, $permission)";
						$cnt++;
					}									
			}
			
			$rs = $g_dbConn->query($sql . $sql_values);
		}
			
		//create new user
		$userToKeep = new user($keep);
		//maintain highest permission level
		$dflt_permission_lvl = ($userData[$keep]['dflt_permission_level'] < $userData[$merge]['dflt_permission_level']) ? $userData[$merge]['dflt_permission_level'] : $userData[$keep]['dflt_permission_level'];
		$userToKeep->setDefaultRole($dflt_permission_lvl);
		unset($userToKeep);
		
		//Staff libraries
		//if none exist do nothing
		//if more than 1 entry exists simple delete the one to not keep
		//if only 1 exists update so user_id = keep
		$rs = $g_dbConn->query($sql_find_staff_lib, array($keep, $merge));
		if ($rs->numRows() > 1)
			$g_dbConn->query($sql_delete_staff_lib, array($merge));	
		else 
			$g_dbConn->query($sql_update_staff_lib, array($keep,$merge));	

		//instructor_attributes
		//if none exist do nothing
		//if more than 1 entry exists simple delete the one to not keep
		//if only 1 exists update so user_id = keep

		$rs = $g_dbConn->query($sql_find_instr_attr, array($keep, $merge));
		if ($rs->numRows() > 1)
			$g_dbConn->query($sql_delete_instr_attr, array($merge));	
		else 
			$g_dbConn->query($sql_update_instr_attr, array($keep,$merge));					

		//not_trained
		//if none exist do nothing
		//if more than 1 entry exists simple delete the one to not keep
		//if only 1 exists update so user_id = keep
		$rs = $g_dbConn->query($sql_find_not_trained, array($keep, $merge));
		if ($rs->numRows() > 1)
			$g_dbConn->query($sql_delete_not_trained, array($merge));	
		else 
			$g_dbConn->query($sql_update_not_trained, array($keep,$merge));								
			
		//personal_copies
		$g_dbConn->query($sql_update_personal_copies, array($keep,$merge));								
		
		//special_users
		$g_dbConn->query($sql_update_special_users, array($keep,$merge));								
		
		//user_view_log
		$g_dbConn->query($sql_update_user_view_log, array($keep,$merge));								
	}
	
	function getUsersByRole($strRole)
	{
		global $g_dbConn, $g_permission;


		switch ($g_dbConn->phptype)
		{

			/* By joining users and access we can get staff and admin who have registered in any class at the given level */

			default: //'mysql'
				$sql = "SELECT DISTINCT u.user_id, CONCAT(u.last_name,', ', u.first_name) AS full_name, ia.ils_name, u.username "
					.  "FROM users as u "
					.  " LEFT JOIN access as a ON a.user_id = u.user_id AND a.permission_level = ? "
					.  " LEFT JOIN instructor_attributes as ia ON u.user_id = ia.user_id "
					.  "WHERE u.dflt_permission_level >= ? AND u.last_name IS NOT NULL AND u.last_name <> '' "
					.  "ORDER BY u.last_name"
					;
		}

		$rs = $g_dbConn->query($sql, array($g_permission[$strRole], $g_permission[$strRole]));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$tmpArray = array();
		while ($row = $rs->fetchRow()) {

			$name = is_null($row[1]) ? $row[2] : $row[1];
			$tmpArray[] = array('user_id' => $row[0], 'full_name' => stripslashes($name), 'username' => $row[3]);
		}

		return $tmpArray;
	}
	
	
	function searchForCI($instructor_id, $dept_id, $course_num, $course_name, $term_id)
	{
		global $g_dbConn, $g_permission;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "
					SELECT DISTINCT ci.course_instance_id
					FROM course_aliases as ca 
						LEFT JOIN access as a on a.alias_id = ca.course_alias_id
						JOIN course_instances as ci on ci.course_instance_id = ca.course_instance_id
						JOIN courses as c on ca.course_id = c.course_id
						JOIN departments AS d ON d.department_id = c.department_id
						LEFT JOIN terms as t on t.term_year = ci.year AND t.term_name = ci.term 
					WHERE 1 ";
				
				if(!empty($instructor_id))
					$sql .= "AND (a.permission_level = " . $g_permission['instructor'] . ") AND a.user_id = '$instructor_id' ";
				if (!empty($dept_id))
					$sql .= "AND c.department_id = $dept_id ";
				if (!empty($course_num))
					$sql .= "AND c.course_number = '$course_num' ";
				if (!empty($course_name))
					$sql .= "AND (c.uniform_title = '$course_name' OR ca.course_name = '$course_name') ";
				if(!empty($term_id))
					$sql .= "AND t.term_id = $term_id ";
					
				$sql .= "ORDER BY t.term_id DESC, ci.year DESC, d.abbreviation ASC, c.course_number ASC, ca.section ASC";
		}
		
		$rs = $g_dbConn->query($sql);	
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
							
		$tmpArray = array();
		while ($row = $rs->fetchRow())
		{
			$tmpCI = new courseInstance($row[0]);
			$tmpCI->getPrimaryCourse();
			$tmpCI->getInstructors();
			$tmpArray[$row[0]] = $tmpCI;
		}
			
		return $tmpArray;
	}
	
	
	/**
	 * @return Array of arrays
	 * @param string $qry
	 * @desc Searches for a $qry in either course_number, uniform_title, or course_name; returns array of subarrays, which are indexed by 'num' and 'name' (['num'] may not exist if query is not searching for a number)
	 */
	function searchForCourses($qry) {
		global $g_dbConn;

		//parse the query
		$pieces = explode(' ', $qry);	//separate query by space
		if((int) $pieces[0] != 0) {	//if the first "word" is a number ([(int) "string"] always equals 0)
			//then we are going to try matching on course number
			$number = (int) $pieces[0];	//convert the first element to a number
		}
		else {
			$number = null;	//no number
		}
		$query = implode(' ', $pieces);	//put the string back together
		
		switch($g_dbConn->phptype) {
			default:	//mysql
				if(!empty($number)) {	//non-empty number, build pieces of query that deal w/ course number
					$num_select = " c.course_number, ";
					$num_where = " OR c.course_number LIKE '$number%'";
					$num_order = " c.course_number, ";
				}
				else {	//ignore course number
					$num_select = $num_where = $num_order = '';					
				}
				
				$sql = "SELECT DISTINCT $num_select c.uniform_title, ca.course_name
						FROM course_aliases AS ca
							JOIN courses AS c ON c.course_id = ca.course_id
						WHERE c.uniform_title LIKE '%$query%' OR ca.course_name LIKE '%$query%' $num_where
						ORDER BY $num_order c.uniform_title, ca.course_name";
		}
			
		$rs = $g_dbConn->query($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		$results = array();
		while($row = $rs->fetchRow()) {
			$tmp = array();
			
			if(sizeof($row) == 3) {	//fetching number as well as title/name
				$tmp['num'] = $row[0];	//store the number
				$u_title = $row[1];
				$name = $row[2];
			}
			else {	//only fetching the title/name
				$title = $row[0];
				$name = $row[1];
			}
			
			//figure out the title/name
			if(strcasecmp($name, $u_title) == 0) {	//instructor-give name and uniform title match
				$tmp['name'] = $u_title;	//store the name
				$results[] = $tmp;	//add tupple to results			
			}
			else {	//title and name do not match
				//if the instructor-given name is not blank and matches the query
				if(!empty($name) && (stripos($name, $query) !== false)) {
					//then add the name to the tuple and results
					$tmp['name'] = $name;
					$results[] = $tmp;					
				}
				
				//repeat the same for the uniform title and create/add another tupple
				//this may result in 2 "results" per database hit, but only when titles do not match
				if(!empty($u_title) && (stripos($u_title, $query) !== false)) {
					//create and add another tupple
					$tmp['name'] = $u_title;
					$results[] = $tmp;			
				}
			}
		}
		
		return $results;
	}



	function getAllUsers()
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = 	"SELECT user_id, last_name, first_name, username "
				.		"FROM users "
				.		"ORDER BY last_name, first_name, username "
				;
		}

		$rs = $g_dbConn->query($sql, $lastName);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$tmpArray = array();
		while($rows = $rs->fetchRow())
			$tmpArray[] = new user($row[0]);

		return $tmpArray;
	}

	function displayUserSearch($cmd, $msg="", $label="", $selection_list, $allowAddUser=false, $request)
	{	
		echo "<form action=\"index.php\" method=\"POST\">\n";
		echo "<input type=\"hidden\" name=\"cmd\" value=\"$cmd\">\n";
	
		$this->displayUserSelect($cmd, $msg, $label, $selection_list, $allowAddUser, $request, "");

		echo "</form>\n";
	}
	
	function displayUserSelect($cmd, $msg="", $label="", $selection_list, $allowAddUser=false, $request, $elementPrefix)
	{
		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr><td width=\"100%\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>\n";
		echo "	<tr><td align=\"center\" valign=\"top\" class=\"helperText\">$msg&nbsp;</td></tr>\n";
		echo "	<tr><td width=\"100%\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>\n";

		if ($allowAddUser)
			echo "	<tr><td align=\"left\" valign=\"top\" class=\"helperText\"><a href=\"index.php?page=manageUser&subpage=addUser\">Create New User</a>&nbsp;</td></tr>\n";

		//echo "	<tr align=\"left\" valign=\"top\" bgcolor=\"#CCCCCC\"><td width=\"27%\" class=\"strong\">$label:</td></tr>\n";
		echo "	<tr>\n";
		echo "		<td>\n";
		echo "			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		echo "				<tr align=\"left\" valign=\"top\"><td class=\"headingCell1\" align=\"center\">$label:</td><td width=\"75%\">&nbsp;</td></tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";


		echo "	<tr bgcolor=\"#CCCCCC\"><td colspan=\"2\">&nbsp;</td></tr>\n";
		echo "	<tr bgcolor=\"#CCCCCC\">\n";
		echo "		<td>&nbsp;&nbsp;\n";

		//set selected
		$last_name = "";
		$username = "";
		$selector = (isset($request[$elementPrefix.'select_user_by'])) ? $request[$elementPrefix.'select_user_by'] : "last_name";
		$$selector = "selected";

		$qryTerm = isset($request[$elementPrefix.'user_qryTerm']) ? stripcslashes($request[$elementPrefix.'user_qryTerm']) : "";

		echo "		<select name=\"".$elementPrefix."select_user_by\">\n";
		echo "			<option value=\"last_name\" $last_name>Last Name</option>\n";
		echo "			<option value=\"username\" $username>User Name</option>\n";
		echo "		</select> &nbsp; <input name=\"".$elementPrefix."user_qryTerm\" type=\"text\" value=\"".$qryTerm."\" size=\"15\"  onBlur=\"this.form.submit();\">\n";
		echo "		&nbsp;\n";
		echo "		<input type=\"submit\" name=\"".$elementPrefix."user_search\" value=\"Search\" onClick=\"this.form.".$elementPrefix."select_course.selectedIndex=-1; this.form.".$elementPrefix."selected_user.selectedIndex=-1;\">\n"; //by setting selectedIndex to -1 we can clear the selectbox or previous values
		echo "		&nbsp;\n";

		if (is_array($selection_list) && !empty($selection_list))
		{
			echo "		<select name=\"".$elementPrefix."selectedUser\" onClick=\"this.form.".$elementPrefix."butSubmit.disabled=false;\">\n";
			for($i=0;$i<count($selection_list);$i++)
			{
				$selector = (isset($request[$elementPrefix.'selectedUser']) && $request[$elementPrefix.'selectedUser'] == $selection_list[$i]->getUserID()) ? "selected" : "";
				echo "		<option $selector value=\"". $selection_list[$i]->getUserID() ."\"> ". $selection_list[$i]->getName() . " - " . $selection_list[$i]->getUsername() ."</option>";
			}
			echo "		</select>\n";
			echo "	</td></tr>\n";
		}
		echo "	<tr bgcolor=\"#CCCCCC\"><td colspan=\"2\">&nbsp;</td></tr>\n";

		$disabled = (isset($request[$elementPrefix.'selectedUser'])) ? "" : "DISABLED";
		echo "	<tr bgcolor=\"#CCCCCC\"><td colspan=\"2\" align=\"center\"><input type=\"submit\" name=\"".$elementPrefix."butSubmit\" value=\"Select User\" $disabled></td></tr>\n";
		echo "	<tr bgcolor=\"#CCCCCC\"><td colspan=\"2\">&nbsp;</td></tr>\n";
		echo "</table>\n";		
	}


	function displaySearchResults($page, $nextSubpage, $submitValue, $hidden_fields=null)
	{
		echo "<form name=\"proxyMgr\" action=\"index.php\">\n";
		echo "<input type=\"hidden\" name=\"page\" value=\"$page\">\n";
		echo "<input type=\"hidden\" name=\"subpage\" value=\"$nextSubpage\">\n";

		if (is_array($hidden_fields)){
			$keys = array_keys($hidden_fields);
			foreach($keys as $key){
				if (is_array($hidden_fields[$key])){
					foreach ($hidden_fields[$key] as $field){
						echo "<input type=\"hidden\" name=\"".$key."[]\" value=\"". $field ."\">\n";
					}
				} else {
					echo "<input type=\"hidden\" name=\"$key\" value=\"". $hidden_fields[$key] ."\">\n";
				}
			}
		}

		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr><td width=\"100%\"><img src=images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";
		echo "	<tr>\n";
		echo "		<td align=\"left\" valign=\"top\">\n";
		echo "			<p><span class=\"helperText\">The following registered users matched your search. If you do not see the user you are looking for, you may search again or contact the Reserves Desk for assistance.</span> </p>\n";
		//echo "			<p>[ <a href=\"link\">Search again</a> ]</p>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr><td height=\"14\"><div align=\"right\"><input type=\"submit\" name=\"Submit\" value=\"$submitValue\"></div></td></tr>\n";
		echo "	<tr>\n";
		echo "		<td height=\"14\">\n";
		echo "			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		echo "				<tr align=\"left\" valign=\"top\">\n";
		echo "					<td height=\"14\" class=\"headingCell1\"><div align=\"center\">SEARCH RESULTS</div></td>\n";
		echo "					<td width=\"75%\"><div align=\"center\"></div></td>\n";
		echo "				</tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td align=\"left\" valign=\"top\" class=\"borders\">\n";
		echo "			<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" class=\"displayList\">\n";

		if (is_array($this->userList) && !empty($this->userList))
		{

			echo "				<tr align=\"left\" valign=\"middle\">\n";
			echo "					<td bgcolor=\"#FFFFFF\" class=\"headingCell1\" align=\"left\">Select</td><td bgcolor=\"#FFFFFF\" class=\"headingCell1\">&nbsp;</td>\n";
			echo "				</tr>\n";

			$i = 0;
			foreach ($this->userList as $aUser) {
				$rowClass = ($i++ % 2) ? "evenRow" : "oddRow";

				echo "				<tr align=\"left\" valign=\"middle\" class=\"$rowClass\">\n";
				echo "					<td width=\"5%\" class=\"borders\" align=\"center\">\n";
				echo "						<input type=\"radio\" name=\"selectedUser\" value=\"". $aUser->getUserID() ."\">\n";
				echo "					</td>\n";
				echo "					<td width=\"100%\"><span class=\"strong\">&nbsp;&nbsp;" . $aUser->getName() ."</span> (". $aUser->getUsername() .")</td>\n";
				echo "				</tr>\n";
			}
			echo "				<tr align=\"left\" valign=\"middle\" class=\"headingCell1\"><td>&nbsp;</td><td valign=\"top\">&nbsp;</td></tr>\n";
		} else {
			echo "				<tr align=\"left\" valign=\"middle\" class=\"headingCell1\"><td>No Users Found for this Query.</td><td valign=\"top\">&nbsp;</td></tr>\n";
		}

		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr><td align=\"left\" valign=\"top\">&nbsp;</td></tr>\n";

		echo "	<tr><td align=\"right\" valign=\"top\" align=\"right\"><input type=\"submit\" name=\"Submit2\" value=\"$submitValue\"></td></tr>\n";

		echo "</table>\n";
		echo "</form>\n";
	}
}
?>
