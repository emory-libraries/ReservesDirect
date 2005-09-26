<?
/*******************************************************************************
userDisplayer.class.php


Created by Kathy Washington (kawashi@emory.edu)

This file is part of ReservesDirect

Copyright (c) 2004-2005 Emory University, Atlanta, Georgia.

ReservesDirect is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

ReservesDirect is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with ReservesDirect; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

ReservesDirect is located at:
http://www.reservesdirect.org/

*******************************************************************************/
require_once("secure/common.inc.php");

class userDisplayer
{
	/**
	* @return void
	* @param
	* @desc Display Screens to Manage Users
	*/

	function displayInstructorHome()
	{
		echo"<table width=\"60%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">";
		echo"	<tr> ";
		echo"		<td width=\"140%\"><img src=\images/spacer.gif\" width=\"1\" height=\"5\"></td>";
		echo"	</tr>";
		echo"	<tr> ";
		echo"		<td align=\"left\" valign=\"top\">";
		echo"			<p><a href=\"index.php?cmd=editProfile\" class=\"titlelink\">Edit My Profile</a><br>";
		echo"           Edit your name and email address</p>";
		echo"           <p><a href=\"index.php?cmd=addProxy\" class=\"titlelink\">Add a Proxy</a><br>";
		echo"           Add a proxy to one of your classes. Proxies:</p>";
		echo"           <ul>";
		echo"           	<li> <span class=\"small\">Must have signed in to ReservesDirect at ";
		echo"               	least once for you to be able to add them</span></li>";
		echo"              	<li class=\"small\">Are able to manage every aspect of the class that ";
		echo"               	you assign them to (add, delete or edit reserve items, add crosslistings, ";
		echo"                	sort items, etc.). </li>";
		echo"              	<li class=\"small\">Only have access to the specific class that you ";
		echo"               	assign them to</li>";
		echo"              	<li class=\"small\">Expire at the end of the semester, or when you ";
		echo"               	remove them manually, whichever comes first</li>";
		echo"			</ul>";
		echo"           <p><a href=\"index.php?cmd=removeProxy\" class=\"titlelink\">Delete a Proxy</a><br>";
		echo"           <!--This link should take the user to a list of their current and future classes, ask them to select one, then present them with the \"Edit Proxies\" screen. -->";
		echo"           Remove a proxy from one of your classes.</p></td>";
		echo"	</tr>";
		echo"	<tr> ";
		echo"		<td><img src=\images/spacer.gif\" width=\"1\" height=\"15\"></td>";
		echo"	</tr>";
		echo"</table>";
	}

	function displayCustodianHome($msg=null)
	{
		echo"<table width=\"60%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">";
		echo"	<tr> ";
		echo"		<td width=\"140%\"><img src=\images/spacer.gif\" width=\"1\" height=\"5\"></td>";
		echo"	</tr>";
		echo "	<tr><td align=\"center\" valign=\"top\" class=\"successText\">$msg&nbsp;</td></tr>\n";
		echo"	<tr> ";
		echo"		<td align=\"left\" valign=\"top\">";
		echo"			<p><a href=\"index.php?cmd=editProfile\" class=\"titlelink\">Edit My Profile</a><br>";
		echo"           Edit your name and email address</p>";
		echo"           <p><a href=\"index.php?cmd=setPwd\" class=\"titlelink\">Create an override password</a><br>";
		echo"           Create a temporary user passwrod, for example, for someone who has forgotten their Emory Network password or who is having trouble with their Emory NetID or GBSNet login.</p>";
		echo"           <p><a href=\"index.php?cmd=resetPwd\" class=\"titlelink\">Reset an Override Password</a><br>";
		echo"           Resets a user's override password to the system default</p>";
        echo"           <p><a href=\"index.php?cmd=removePwd\" class=\"titlelink\">Remove an Override Password</a><br>";
		echo"           Deletes a user's override password so that they log in using their regular Emory NetID password or GBSNet password</p></td>";
		echo"	</tr>";
		echo"	<tr> ";
		echo"		<td><img src=\images/spacer.gif\" width=\"1\" height=\"15\"></td>";
		echo"	</tr>";
		echo"</table>";
	}

	function displayEditProxy($courseInstances,$nextCmd)
	{
		echo "<form action=\"index.php\" method=\"post\" name=\"editUser\">\n";
	    echo "<input type=\"hidden\" name=\"cmd\" value=\"$nextCmd\">\n";
		echo '<table width="90%" border="0" cellspacing="0" cellpadding="0" align="center">';
        echo 	'<tr>';
        echo 		'<td width="140%"><img src="images/spacer.gif" width="1" height="5"> </td>';
        echo 	'</tr>';
        echo 	'<tr>';
        echo 	'  <td align="left" valign="top" class="helperText">Select which classes ';
        echo 		'to add your proxy to. Note that you may only add one individual at ';
        echo 		'a time, but you may add them to ';
        echo 		'as many classes as you wish. You may only add users who have logged ';
        echo 		'into ReservesDirect at least once to register in the database.</td>';
        echo 	'</tr>';
        echo 	'<tr>';
        echo 		'<td height="14">&nbsp;</td>';
        echo 	'</tr>';
        echo 	'<tr>';
        echo 		'<td height="14">';
        echo 			'<table width="100%" border="0" cellspacing="0" cellpadding="0">';
        echo 				'<tr align="left" valign="top">';
        echo 					'<td height="14" class="headingCell1"><div align="center">YOUR CLASSES</div></td>';
        echo 					'<td width="75%"><div align="center"></div></td>';
        echo 				'</tr>';
        echo 			'</table>';
        echo 		'</td>';
        echo 	'</tr>';
        echo 	'<tr>';
        echo 		'<td align="left" valign="top" class="borders">';
        echo 			'<table width="100%" border="0" cellpadding="5" cellspacing="0" class="displayList">';
        echo 				'<tr align="left" valign="middle" bgcolor="#CCCCCC" class="headingCell1">';
        echo 					'<td width="15%">&nbsp;</td>';
        echo 					'<td width="65%">&nbsp;</td>';
        echo 					'<td>&nbsp;</td>';
        echo 					'<td width="10%">Select</td>';
        echo				'</tr>';

        $rowNumber = 0;
        for ($i=0; $i<count($courseInstances); $i++)
        {
        	$rowClass = ($rowNumber++ % 2) ? $rowClass = "evenRow" : "oddRow";
        	$courseInstances[$i]->getPrimaryCourse();

        echo				'<tr align="left" valign="middle" bgcolor="#CCCCCC" class="'.$rowClass.'">';
        echo 					'<td width="15%">'.$courseInstances[$i]->course->displayCourseNo().'</td>';
        echo 					'<td width="65%">'.$courseInstances[$i]->course->getName().'</td>';
        echo 					'<td width="20%">'.$courseInstances[$i]->displayTerm().'</td>';
        echo 					'<td width="10%" align="center"><input type="radio" name="ci" value="'.$courseInstances[$i]->getCourseInstanceID().'"></td>';
        echo 				'</tr>';

        }

        echo 				'<tr align="left" valign="middle" bgcolor="#CCCCCC" class="headingCell1">';
        echo 					'<td width="15%">&nbsp;</td>';
        echo 					'<td width="65%">&nbsp;</td>';
        echo 					'<td>&nbsp;</td>';
        echo 					'<td width="10%">&nbsp;</td>';
        echo 				'</tr>';
        echo 			'</table>';
        echo		'</td>';
        echo 	'</tr>';
        echo 	'<tr>';
        echo 		'<td align="left" valign="top">&nbsp;</td>';
        echo 	'</tr>';
        echo 	'<tr>';
        echo 		'<td align="left" valign="top"><div align="center"><input type="submit" name="Submit" value="Continue"></div></td>';
        echo 	'</tr>';
        echo 	'<tr>';
        echo 		'<td align="left" valign="top"><img src="images/spacer.gif" width="1" height="15"></td>';
        echo 	'</tr>';
      	echo '</table>';
	}

	function displayEditUser($userToEdit, $user, $msg=null, $request, $usersObj=null, $hidden_fields=null)
	{
		global $g_permission;

		if (!is_null($usersObj))
			$usersObj->displayUserSearch($hidden_fields['previous_cmd'], $msg, 'Select a User to Edit', $usersObj->userList, false, $request);

		if (!is_null($userToEdit))
		{
			echo "<form action=\"index.php\" method=\"post\" name=\"editUser\">\n";
			echo "<input type=\"hidden\" name=\"user[userID]\" value=\"". $userToEdit->getUserID() ."\">\n";
			echo "<input type=\"hidden\" name=\"selectedUser\" value=\"" . $userToEdit->getUserID() . "\">";

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
			//echo "	<tr><td width=\"100%\"><img src=\images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>\n";
			echo "	<tr><td align=\"center\" valign=\"top\" class=\"helperText\">$msg&nbsp;</td></tr>\n";

			echo "	<tr>\n";
			echo "		<td align=\"left\" valign=\"top\">\n";
			echo "			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
			echo "			<tr><td colspan=\"3\" align=\"right\">[ <a href=\"index.php?cmd=manageUser\">Exit</a> ]</div></td></tr>\n";


			if ($hidden_fields['cmd'] == "addUser")
				echo "				<tr align=\"left\" valign=\"top\"><td height=\"14\" class=\"headingCell1\" align=\"center\">CREATE NEW USER</td><td width=\"75%\">&nbsp;</td></tr>\n";
			else
				echo "				<tr align=\"left\" valign=\"top\"><td height=\"14\" class=\"headingCell1\" align=\"center\">USER PROFILE - " . $userToEdit->getUsername() . " - " . $userToEdit->getName() ."</td><td width=\"75%\">&nbsp;</td></tr>\n";

			echo "			</table>\n";
			echo "		</td>\n";
			echo "	</tr>\n";

			echo "	<tr>\n";
			echo "		<td align=\"left\" valign=\"top\" class=\"borders\">\n";
			echo "			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\">\n";
			echo "				<tr>\n";
			echo "					<td width=\"15%\" class=\"strong\" align=\"right\">User Name:</td>\n";

			if (is_null($userToEdit->getUserID()))
				echo "					<td width=\"100%\" align=\"left\"><input type=\"text\" value=\"" . $userToEdit->getUsername() ."\" name=\"user[username]\" size=\"40\" onBlur=\"if (this.form.addPwd && this.value != '') this.form.addPwd.disabled=false;\"></td>\n";
			else
				echo "					<td width=\"100%\" align=\"left\"><strong><font color=\"#666666\">" . $userToEdit->getUsername() ."</font></strong></td>\n";

			echo "				</tr>\n";
			echo "				<tr>\n";
			echo "					<td width=\"15%\" class=\"strong\" align=\"right\">First Name:</td>\n";
			echo "					<td width=\"100%\"><input name=\"user[first_name]\" type=\"text\" size=\"40\" value=\"" . $userToEdit->getFirstName() ."\"></td>\n";
			echo "				</tr>\n";
			echo "				<tr>\n";
			echo "					<td width=\"15%\" class=\"strong\" align=\"right\">Last Name:</td>\n";
			echo "					<td width=\"100%\"><input name=\"user[last_name]\" type=\"text\" size=\"40\" value=\"" . $userToEdit->getLastName() ."\"></td>\n";
			echo "				</tr>\n";
			echo "				<tr>\n";
			echo "					<td class=\"strong\" align=\"right\">Email:</td>\n";
			echo "					<td><input name=\"user[email]\" type=\"text\" size=\"40\" value=\"" . $userToEdit->getEmail() ."\"></td>\n";
			echo "				</tr>\n";

			echo "				<tr>\n";
			echo "					<td class=\"strong\" align=\"right\">Default Role:</td>\n";

			if ($user->getUserID() != $userToEdit->getUserID() && $user->getDefaultRole() >= $g_permission['staff'])
			{
				$SELECT_0 = "";
				$SELECT_1 = "";
				$SELECT_2 = "";
				$SELECT_3 = "";
				$SELECT_4 = "";
				$SELECT_5 = "";

				$select = (isset($request['user']['defaultRole'])) ? "SELECT_" . $request['user']['defaultRole'] : "SELECT_" . $userToEdit->getDefaultRole();
				$$select = " SELECTED ";

				echo "					<td>\n";
				echo "						<select name=\"user[defaultRole]\" onChange=\"this.form.cmd.value='".$hidden_fields['previous_cmd']."'; this.form.submit();\">\n";
				echo "							<option value=\"0\" $SELECT_0>STUDENT</option>\n";
				echo "							<option value=\"1\" $SELECT_1>CUSTODIAN</option>\n";
				echo "							<option value=\"2\" $SELECT_2>PROXY</option>\n";
				echo "							<option value=\"3\" $SELECT_3>INSTRUCTOR</option>\n";
				echo "							<option value=\"4\" $SELECT_4>STAFF</option>\n";
				if ($userToEdit->getDefaultRole() == $g_permission['admin']) echo "							<option value=\"5\" $SELECT_5>ADMIN</option>\n";
				echo "						</select>\n";
				echo "					</td>\n";
			}else{
				echo "					<input type=\"hidden\" name=\"user[defaultRole]\" value=\"". $userToEdit->getDefaultRole() ."\">\n";
				echo "					<td>". strtoupper($userToEdit->getUserClass()) ."</td>\n";
			}
			echo "				</tr>\n";

			if (($user->getDefaultRole() >= $g_permission['staff'] && $userToEdit->getDefaultRole() >= $g_permission['instructor']) ||
				(isset($request['user']['defaultRole']) && $request['user']['defaultRole'] >= $g_permission['instructor']))
			{
				echo "				<tr>\n";
				echo "					<td class=\"strong\" align=\"right\">ILS User ID:</td>\n";
				echo "					<td><input type=\"text\" name=\"user[ils_user_id]\" size=\"20\" value=\"" . $userToEdit->getILSUserID() ."\"></td>\n";
				echo "				</tr>\n";

				echo "				<tr>\n";
				echo "					<td class=\"strong\" align=\"right\">ILS User Name:</td>\n";
				echo "					<td><input type=\"text\" name=\"user[ils_user_name]\" size=\"20\" value=\"" . $userToEdit->getILSName() ."\"></td>\n";
				echo "				</tr>\n";
			}


			echo "				<tr><td colspan=\"2\">&nbsp;</td></tr>\n";


			//edit password
			//If special user, allow user to override password, otherwise give them a button
			if ($userToEdit->isSpecialUser())
			{
				if ($user->getDefaultRole() >= $g_permission['staff'] || $user->getUserID() == $userToEdit->getUserID() || $user->getDefaultRole() == $g_permission['custodian'])
				{
					$warningText = ($user->getUserID() != $userToEdit->getUserID()) ? "(use only if user cannot login normally)" : "";
					
					echo "				<tr>\n";
					echo "					<td class=\"strong\" align=\"right\">Password:</td>\n";
					echo "					<td><input name=\"user[pwd]\" type=\"text\" size=\"40\" value=\"\"> $warningText</td>\n";
					echo "				</tr>\n";
					echo "				<tr>\n";
					echo "					<td class=\"strong\" align=\"right\">Confirm Password:</td>\n";
					echo "					<td><input name=\"user[confirm_pwd]\" type=\"text\" size=\"40\" value=\"\"></td>\n";
					echo "				</tr>\n";
				}
			}
			elseif (($user->getDefaultRole() >= $g_permission['staff']  || $user->getDefaultRole() == $g_permission['custodian'])&& $user->getUserID() != $userToEdit->getUserID())
			{

				$addPwd_disabled = (is_null($userToEdit->getUserID())) ? "disabled" : "";

				echo "				<tr>\n";
				echo "					<td class=\"strong\" align=\"center\">&nbsp</td>\n";
				echo "					<td>";
				echo "						<input type=\"submit\" name=\"addPwd\" value=\"Set Override Password\" $addPwd_disabled onClick=\"this.form.cmd.value='setPwd';\">";
				echo "					</td>\n";
				echo "				</tr>\n";
			}

			echo "			</table>\n";
			echo "		</td>\n";
			echo "	</tr>\n";
			echo "	<tr><td>&nbsp;</td></tr><tr><td align=\"center\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\" onClick=\"javascript:return validate(document.forms.editUser);\"></td></tr>\n";
			echo "	<tr><td><img src=\images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
			echo "</table>\n";
			echo "<script language=\"javaScript\">
			function trim(strText) {
    			// this will get rid of leading spaces
    			while (strText.substring(0,1) == ' ')
        			strText = strText.substring(1, strText.length);

    			// this will get rid of trailing spaces
    			while (strText.substring(strText.length-1,strText.length) == ' ')
        			strText = strText.substring(0, strText.length-1);

   				return strText;
			}

			function validate(form)
			{
				var lastName;
				var email;

				for (var i=0; i < form.elements.length; i++) {
					if (form.elements[i].name == 'user[last_name]') {
						lastName = trim(form.elements[i].value);
					}
					if (form.elements[i].name == 'user[email]') {
						email = trim(form.elements[i].value);
					}
				}

				var errorMsg = '';
				if (lastName == '' || email == '')
					errorMsg = 'You are required to enter your last name and a valid e-mail address';

				if (errorMsg) {
					document.getElementById('alertMsg').innerHTML = errorMsg;
					return false;
				} 
			}
		</script>";

			echo "</form>\n";
		}
	}

	function displayStaffHome($msg=null)
	{
		echo "<table width=\"66%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr><td width=\"140%\"><img src=\images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>\n";
		echo "	<tr><td align=\"center\" valign=\"top\" class=\"helperText\">$msg&nbsp;</td></tr>\n";
		echo "	<tr>\n";
		echo "		<td align=\"center\" valign=\"top\" class=\"borders\">\n";
		echo "			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\">\n";
		echo "				<tr class=\"headingCell1\"><td width=\"33%\">Create</td><td width=\"33%\">Edit</td><!--<td width=\"33%\">Assign</td>--></tr>\n";
		echo "				<tr align=\"left\" valign=\"top\">\n";

		echo "					<td width=\"33%\" class=\"borders\">\n";
		echo "						<ul>\n";
		echo "							<li><a href=\"index.php?cmd=addUser\">Create a new user</a></li>\n";
		echo "							<li><a href=\"index.php?cmd=setPwd\">Set Override password</a></li>\n";
		echo "						</ul>\n";
		echo "					</td>\n";

		echo "					<td width=\"33%\" class=\"borders\">\n";
		echo "						<ul>\n";
		echo "							<li><a href=\"index.php?cmd=editUser\">Edit a user profile</a></li>\n";
		echo "							<li><a href=\"index.php?cmd=resetPwd\">Reset Override Password</a></li>\n";
		echo "							<li><a href=\"index.php?cmd=removePwd\">Remove Override Password</a></li>\n";
		echo "							<li><a href=\"index.php?cmd=mergeUsers\">Merge Users</a></li>\n";
		echo "							<br>\n";
		echo "							<li><a href=\"index.php?cmd=editProfile\">Edit my profile</a></li>\n";
		echo "						</ul>\n";
		echo "					</td>\n";

		echo "					<!--<td width=\"33%\" class=\"borders\">\n";
		echo "						<ul>\n";
		echo "							<li><a href=\"index.php?cmd=assignProxy\">Assign a Proxy to a Class</a></li>\n";
		echo "							<li><a href=\"index.php?cmd=assignInstr\">Assign an Instructor to a Class</a></li>\n";
		echo "						</ul>\n";
		echo "					</td>-->\n";

		echo "				</tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr><td><img src=\images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
		echo "</table>\n";
	}

	function displayAssignUser($cmd, $nextCmd, $userToAssign, $msg, $usersObj, $label, $request)
	{
		if (!is_null($usersObj))
			$usersObj->displayUserSearch($cmd, $msg, $label, $usersObj->userList, false, $request);
	}

	
	function displayMergeUser($request, $hidden_fields, $userObj, $cmd)
	{
		echo "<form action=\"index.php\" method=\"post\">\n";
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
			
			if (isset($_REQUEST['userToKeep_'.'select_user_by']) && isset($_REQUEST['userToKeep_'.'user_qryTerm']))
				$userObj->search($_REQUEST['userToKeep_'.'select_user_by'], $_REQUEST['userToKeep_'.'user_qryTerm']);

			$userObj->displayUserSelect($cmd, "", "Select User to Keep", $userObj->userList, false, $request, "userToKeep_", false);
			
			$userObj->userList = null;
			
			if (isset($_REQUEST['userToMerge_'.'select_user_by']) && isset($_REQUEST['userToMerge_'.'user_qryTerm']))
				$userObj->search($_REQUEST['userToMerge_'.'select_user_by'], $_REQUEST['userToMerge_'.'user_qryTerm']);
			
			$userObj->displayUserSelect($cmd, "", "Select User to Merge", $userObj->userList, false, $request, "userToMerge_", false);

			echo "<center><input type=\"submit\" name=\"subMerge\" value=\"Merge Users\"></center>\n";
					
		echo "</form>\n";
	}
}