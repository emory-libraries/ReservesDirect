<?
/*******************************************************************************
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

Created by Kathy A. Washington (kawashi@emory.edu)

Reserves Direct 2.0 is located at:
http://coursecontrol.sourceforge.net/

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
        echo 		'into Reserves Direct at least once to register in the database.</td>';
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
	
	function displayEditUser($cmd, $nextCmd, $userToEdit, $user, $msg=null, $usersObj=null, $request)
	{
		global $g_permission;

		if (!is_null($usersObj))
			$usersObj->displayUserSearch($cmd, $msg, 'Select a User to Edit', $usersObj->userList, false, $request);
		
		if (!is_null($userToEdit))
		{
			echo "<form action=\"index.php\" method=\"post\" name=\"editUser\">\n";
	    	echo "<input type=\"hidden\" name=\"cmd\" value=\"$cmd\">\n";
	    	echo "<input type=\"hidden\" name=\"previous_cmd\" value=\"$cmd\">\n";
			echo "<input type=\"hidden\" name=\"user[userID]\" value=\"". $userToEdit->getUserID() ."\">\n";    			
			echo "<input type=\"hidden\" name=\"selectedUser\" value=\"" . $userToEdit->getUserID() . "\">";
	    	
			echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
			echo "	<tr><td width=\"140%\"><img src=\images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>\n";
	
			echo "	<tr><td align=\"center\" valign=\"top\" class=\"helperText\">$msg&nbsp;</td></tr>\n";
	
			echo "	<tr>\n";
			echo "		<td align=\"left\" valign=\"top\">\n";
			echo "			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
			
			if ($cmd == "addUser")
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
				echo "						<select name=\"user[defaultRole]\" onChange=\"this.form.submit();\">\n";
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
					echo "				<tr>\n";
					echo "					<td class=\"strong\" align=\"right\">Password:</td>\n";
					echo "					<td><input name=\"user[pwd]\" type=\"text\" size=\"40\" value=\"\"> (use only if user cannot login normally)</td>\n";
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
			echo "	<tr><td>&nbsp;</td></tr><tr><td align=\"center\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\" onClick=\"this.form.cmd.value='$nextCmd';\"></td></tr>\n";
			echo "	<tr><td><img src=\images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
			echo "</table>\n";
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
		echo "				<tr class=\"headingCell1\"><td width=\"33%\">Create</td><td width=\"33%\">Edit</td><td width=\"33%\">Assign</td></tr>\n";
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
		echo "							<br>\n";
		echo "							<li><a href=\"index.php?cmd=editProfile\">Edit my profile</a></li>\n";
		echo "						</ul>\n";
		echo "					</td>\n";
		
		echo "					<td width=\"33%\" class=\"borders\">\n";
		echo "						<ul>\n";
		echo "							<li><a href=\"index.php?cmd=assignProxy\">Assign a Proxy to a Class</a></li>\n";
		echo "							<li><a href=\"index.php?cmd=assignInstr\">Assign an Instructor to a Class</a></li>\n";
		echo "						</ul>\n";
		echo "					</td>\n";
		
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
		
}