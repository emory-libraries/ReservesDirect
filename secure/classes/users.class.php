<?
/*******************************************************************************
users.class.php
user container

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
require_once("secure/classes/user.class.php");

class users
{
	public $userList = array();

	function users() { $userList = null; }

	function initUser($userClass, $userName)
	{
		switch ($userClass)
		{
			case 'student':
				$u = new student($userName);
			break;

			case 'custodian':
				$u = new custodian($userName);
			break;

			case 'proxy':
				$u = new proxy($userName);
			break;

			case 'instructor':
				$u = new instructor($userName);
			break;

			case 'staff':
				$u = new staff($userName);
			break;

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
		if ($term != "" && $qry != "")
			switch ($term)
			{
				case 'last_name':
					$this->getUsersByLastName($qry, $role);
					break;
				case 'username':
				case 'user_name':
					$this->getUsersByUserName($qry, $role);
					break;
			}
	}

	function getUsersByLastName($lastName, $role=null)
	{
		global $g_dbConn, $g_permission;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = 	"SELECT user_id, last_name, first_name, username "
				.		"FROM users "
				.		"WHERE last_name LIKE '%$lastName%' ";

				if (!is_null($role)) $sql .= "AND dflt_permission_level >= " . $g_permission[$role] . " ";

				$sql .=	"ORDER BY last_name, first_name, username "
				;
		}

		$rs = $g_dbConn->query($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		unset ($this->userList);
		while($row = $rs->fetchRow())
			$this->userList[] = new user($row[0]);			
	}

	function getUsersByUsername($username, $role=null)
	{
		global $g_dbConn, $g_permission;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = 	"SELECT user_id, last_name, first_name, username "
				.		"FROM users "
				.		"WHERE username LIKE '%$username%' ";

				if (!is_null($role)) $sql .= "AND dflt_permission_level >= " . $g_permission[$role] . " ";

				$sql .=	"ORDER BY last_name, first_name, username "
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
				$sql_find 	= "SELECT alias_id, permission_level FROM access WHERE user_id in (!,!) ORDER BY alias_id";
				$sql_delete_access = "DELETE FROM access WHERE user_id in (!,!)";
				$sql_delete_user   = "DELETE FROM users WHERE user_id = !";
		}

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
					.  "WHERE u.dflt_permission_level >= ? "
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
		echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr><td width=\"140%\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>\n";
		echo "	<tr><td align=\"center\" valign=\"top\" class=\"helperText\">$msg&nbsp;</td></tr>\n";
		echo "	<tr><td width=\"140%\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>\n";

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

		echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr><td width=\"140%\"><img src=images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";
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