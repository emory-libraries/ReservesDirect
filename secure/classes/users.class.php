<?
/*******************************************************************************
users.class.php
user container

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
require_once("classes/user.class.php");

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
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_SQL_ERROR); }
		
		$tmpArray = array();
		while ($row = $rs->fetchRow()) {
			
			$name = is_null($row[1]) ? $row[2] : $row[1];
			$tmpArray[] = array('user_id' => $row[0], 'full_name' => $name, 'username' => $row[3]);
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
		
		echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr><td width=\"140%\"><img src=\"../images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>\n";
		echo "	<tr><td align=\"center\" valign=\"top\" class=\"helperText\">$msg&nbsp;</td></tr>\n";
		echo "	<tr><td width=\"140%\"><img src=\"../images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>\n";

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
		$selector = (isset($request['select_user_by'])) ? $request['select_user_by'] : "last_name";
		$$selector = "selected";
		
		$qryTerm = isset($request['user_qryTerm']) ? $request['user_qryTerm'] : "";
		
		echo "		<select name=\"select_user_by\">\n";
		echo "			<option value=\"last_name\" $last_name>Last Name</option>\n";
		echo "			<option value=\"username\" $username>User Name</option>\n";
		echo "		</select> &nbsp; <input name=\"user_qryTerm\" type=\"text\" value=\"".$qryTerm."\" size=\"15\"  onBlur=\"this.form.submit();\">\n";
		echo "		&nbsp;\n";
		echo "		<input type=\"submit\" name=\"user_search\" value=\"Search\" onClick=\"this.form.select_course.selectedIndex=-1; this.form.selected_user.selectedIndex=-1;\">\n"; //by setting selectedIndex to -1 we can clear the selectbox or previous values
		echo "		&nbsp;\n";
			
		if (is_array($selection_list) && !empty($selection_list))
		{			
			echo "		<select name=\"selectedUser\" onClick=\"this.form.butSubmit.disabled=false;\">\n";			
			for($i=0;$i<count($selection_list);$i++)
			{
				$selector = (isset($request['selectedUser']) && $request['selectedUser'] == $selection_list[$i]->getUserID()) ? "selected" : "";
				echo "		<option $selector value=\"". $selection_list[$i]->getUserID() ."\"> ". $selection_list[$i]->getName() . " - " . $selection_list[$i]->getUsername() ."</option>";
			}
			echo "		</select>\n";
			echo "	</td></tr>\n";
		}
		echo "	<tr bgcolor=\"#CCCCCC\"><td colspan=\"2\">&nbsp;</td></tr>\n";
		
		$disabled = (isset($request['selectedUser'])) ? "" : "DISABLED";
		echo "	<tr bgcolor=\"#CCCCCC\"><td colspan=\"2\" align=\"center\"><input type=\"submit\" name=\"butSubmit\" value=\"Select User\" $disabled></td></tr>\n";
		echo "	<tr bgcolor=\"#CCCCCC\"><td colspan=\"2\">&nbsp;</td></tr>\n";
		echo "</table>\n";
		echo "</form>\n";
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
		echo "	<tr><td width=\"140%\"><img src=\"/images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";
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