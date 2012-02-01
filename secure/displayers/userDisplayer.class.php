<?
/*******************************************************************************
userDisplayer.class.php


Created by Kathy Washington (kawashi@emory.edu)

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
require_once('secure/managers/ajaxManager.class.php');
require_once('secure/displayers/baseDisplayer.class.php');

class userDisplayer extends baseDisplayer {
	/**
	* @return void
	* @param
	* @desc Display Screens to Manage Users
	*/

	function displayInstructorHome()
	{
		echo"<table width=\"60%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">";
		echo"	<tr> ";
		echo"		<td width=\"140%\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"></td>";
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
		echo"		<td><img src=\"images/spacer.gif\" width=\"1\" height=\"15\"></td>";
		echo"	</tr>";
		echo"</table>";
	}

	function displayCustodianHome($msg=null)
	{
		echo"<table width=\"60%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">";
		echo"	<tr> ";
		echo"		<td width=\"140%\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"></td>";
		echo"	</tr>";
		echo "	<tr><td align=\"center\" valign=\"top\" class=\"successText\">$msg&nbsp;</td></tr>\n";
		echo "	<tr><td colspan=2>&nbsp;</td></tr>\n";
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
		echo"		<td><img src=\"images/spacer.gif\" width=\"1\" height=\"15\"></td>";
		echo"	</tr>";
		echo"</table>";
	}

	function displayEditProxy($courseInstances,$nextCmd, $cmd)
	{	
		echo "<form action=\"index.php\" method=\"post\" name=\"editUser\">\n";
	    echo "<input type=\"hidden\" name=\"cmd\" value=\"$nextCmd\">\n";
		echo '<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">';
        echo 	'<tr>';
        echo 		'<td width="140%"><img src="images/spacer.gif" width="1" height="5"> </td>';
        echo 	'</tr>';
        echo 	'<tr>';
        if($cmd=='addProxy') {	//adding proxy
	        echo 	'  <td align="left" valign="top" class="helperText">Select which classes ';
	        echo 		'to add your proxy to. Note that you may only add one individual at ';
	        echo 		'a time, but you may add them to ';
	        echo 		'as many classes as you wish. You may only add users who have logged ';
	        echo 		'into ReservesDirect at least once to register in the database.</td>';
        }
        else {	//removing proxy
 ?>
 		<td align="left" valign="top" class="helperText">Select from which classes to remove your proxy.</td>
 <?php
        }
        
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
        
        if(empty($courseInstances)) {
?>
		<tr><td>
			<div class="borders" style="padding:10px;">
				You are currently not teaching any courses.
			</div>
		</td></tr>
		</table>
<?php
    		return;
        }
        
        echo 	'<tr>';
        echo 		'<td align="left" valign="top" class="borders">';
        echo 			'<table width="100%" border="0" cellpadding="5" cellspacing="0" class="displayList">';
        echo 				'<tr align="left" valign="middle" bgcolor="#CCCCCC" class="headingCell1">';
        echo 					'<td width="15%">&nbsp;</td>';
        echo 					'<td width="65%">&nbsp;</td>';
        echo 					'<td>&nbsp;</td>';
        echo 					'<td width="10%">Select</td>';
        echo				'</tr>';

        if(!empty($courseInstances))
        {
        	$rowNumber = 0;
	        foreach ($courseInstances as $ci)
	        {
	        	$rowClass = ($rowNumber++ % 2) ? $rowClass = "evenRow" : "oddRow";
	        	$ci->getPrimaryCourse();
	
	        echo				'<tr align="left" valign="middle" bgcolor="#CCCCCC" class="'.$rowClass.'">';
	        echo 					'<td width="15%">'.$ci->course->displayCourseNo().'</td>';
	        echo 					'<td width="65%">'.$ci->course->getName().'</td>';
	        echo 					'<td width="20%">'.$ci->displayTerm().'</td>';
	        echo 					'<td width="10%" align="center"><input type="radio" name="ci" value="'.$ci->getCourseInstanceID().'"></td>';
	        echo 				'</tr>';
	
	        }
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
	
	
	/**
	 * Displays form input fields for override password; 
	 *
	 * @param user $userObject user object (or subclass)
	 * @param boolean $hide_on_load	if TRUE will hide the input fields and display link to show them.
	 * 
	 * Assumes that will be inserted into <table></table>; outputs <tr><td></td></tr> blocks
	 * Assumes that will be inserted into <form></form>; outputs 2 input fields
	 */
	function displayPasswordFields($userObject, $hide_on_load=false) {
		global $g_permission, $u;
		
		//determine if updating own 
		$editing_self = ($u->getUserID() == $userObject->getUserID()) ? true : false;
		
		//show password fields only if
		//edited by authorized personel (custodian/staff+) or user editing own password
		if(($u->getRole() == $g_permission['custodian']) || ($u->getRole() >= $g_permission['staff']) || ($editing_self && $userObject->isSpecialUser())):
			//text of link to create/edit pass
			$link_text = $userObject->isSpecialUser() ? 'Edit' : 'Create';
			//hide password blocks w/ style.display
			$style_display = $hide_on_load ? 'none' : '';		
?>

<?php		if($hide_on_load): ?>
				<tr id="op_link_show">
					<td align="right">[ <a href="#" onclick="javascript: toggle_password_vis(1); return false;"><?php echo $link_text; ?> Password</a> ]</td>
					<td>&nbsp;</td>
				</tr>
				<tr id="op_link_hide" style="display:none;">
					<td align="right">[ <a href="#" onclick="javascript: toggle_password_vis(0); return false;">Cancel <?php echo $link_text; ?> Password</a> ]</td>
					<td><span class="helperText">Warning: password will appear on the screen as you type it.</span></td>
				</tr>
<?php		else: ?>
				<tr >
					<td>&nbsp;</td>
					<td><span class="helperText">Warning: password will appear on the screen as you type it.</span></td>
				</tr>
<?php		endif; ?>
				<tr id="op_pass1" style="display:<?php echo $style_display; ?>;">
					<td class="strong" align="right">Password:</td>
					<td><input type="text" name="override_pass" size="40" /> </td>
				</tr>
				<tr id="op_pass2" style="display:<?php echo $style_display; ?>;">
					<td class="strong" align="right">Confirm Password:</td>
					<td><input type="text" name="override_pass_confirm" size="40" /></td>
				</tr>
				
<?php		if($hide_on_load): ?>
		<script type="text/javascript" language="javascript">
			function toggle_password_vis(show) {
				if(show) {
					document.getElementById('op_link_show').style.display = 'none';
					document.getElementById('op_link_hide').style.display = '';
					document.getElementById('op_pass1').style.display = '';
					document.getElementById('op_pass2').style.display = '';
				}
				else {
					document.getElementById('op_link_show').style.display = '';
					document.getElementById('op_link_hide').style.display = 'none';
					document.getElementById('op_pass1').style.display = 'none';
					document.getElementById('op_pass2').style.display = 'none';
				}
			}		
		</script>
<?php		endif; ?>
<?php	
		endif;
	}
	
	
	/**
	 * Form for creating/editing password
	 *
	 * @param user $userObject
	 * @param string $cmd Current command
	 * @param string $msg Alerts/errors
	 */
	function displayEditPassword($userObject, $cmd, $msg=null) {
?>
		<div style="text-align:right;"><strong>[ <a href="index.php?cmd=manageUser">Exit</a> ]</strong></div>
		
<?php	if(!empty($msg)):	//display message, if it exists ?>
		<span class="helperText"><?php echo $msg; ?></span>
		<p />
<?php	endif; ?>

		<form action="index.php" method="post" name="editUser">
			<input type="hidden" name="selectedUser" value="<?php echo $userObject->getUserId(); ?>" />
			<input type="hidden" name="cmd" value="<?php echo $cmd; ?>" />
			
			<div class="headingCell1" style="width:25%; text-align:center;">
				Password for <?php echo $userObject->getName(false); ?>
			</div>
			<div class="borders" style="padding:10px;">	
				<table>
					<?php self::displayPasswordFields($userObject, false); ?>
				</table>
			</div>
			<br />			
			<input type="submit" name="edit_pass_submit" value="Set Override Password" />
		</form>
<?php
	}
	
	
	/**
	 * form for creating/editing a user profile
	 *
	 * @param user $userObject
	 * @param string $cmd Current command
	 * @param string $msg Alerts and/or errors
	 */
	function displayEditProfile($userObject, $cmd, $msg=null) {
		global $g_permission, $u;
		
		//grab data from $_REQUEST (if it's set) or from object
		$username = !empty($_REQUEST['username']) ? $_REQUEST['username'] : $userObject->getUsername();
		$first_name = !empty($_REQUEST['first_name']) ? $_REQUEST['first_name'] : $userObject->getFirstName();
		$last_name = !empty($_REQUEST['last_name']) ? $_REQUEST['last_name'] : $userObject->getLastName();
		$email = !empty($_REQUEST['email']) ? $_REQUEST['email'] : $userObject->getEmail();
		$default_role = !empty($_REQUEST['default_role']) ? $_REQUEST['default_role'] : $userObject->getDefaultRole();
		$not_trained = !empty($_REQUEST['not_trained']) ? $_REQUEST['not_trained'] : $userObject->isNotTrained();
		//get a list of libraries, in case editing staff
		$libraries = $userObject->getLibraries();

		//get ILS data if user object is instructor or better
		if(is_a($userObject, 'instructor')) {
			//grab ILS info from DB
			$userObject->getInstructorAttributes();
			//override w/ request data, if available
			$ils_user_id = !empty($_REQUEST['ils_user_id']) ? $_REQUEST['ils_user_id'] : $userObject->getILSUserID();
			$ils_user_name = !empty($_REQUEST['ils_user_name']) ? $_REQUEST['ils_user_name'] : $userObject->getILSName();
		}
		else {
			$ils_user_id = $ils_user_name = '';
		}
		
		//get library assignment for staff or better
		if(is_a($userObject, 'staff')) {
			$staff_library = !empty($_REQUEST['staff_library']) ? $_REQUEST['staff_library'] : $userObject->getStaffLibrary();
		}
		
		//determine if updating own 
		$editing_self = ($u->getUserID() == $userObject->getUserID()) ? true : false;
?>

		<div style="text-align:right;"><strong>[ <a href="index.php?cmd=manageUser">Exit</a> ]</strong></div>
		
<?php	if(!empty($msg)):	//display message, if it exists ?>
		<span class="helperText"><?php echo $msg; ?></span>
		<p />
<?php	endif; ?>
		
		<form action="index.php" method="post" name="editUser">
			<input type="hidden" name="selectedUser" value="<?php echo $userObject->getUserId(); ?>" />
			<input type="hidden" name="cmd" value="<?php echo $cmd; ?>" />
			
		<div class="headingCell1" style="width:25%; text-align:center;">
<?php
		//display proper header
		if($cmd == 'addUser') {
			echo 'CREATE NEW USER';
		}
		else {
			echo 'EDIT USER - '.$userObject->getUsername().' - '.$userObject->getName(false);
		}
?>
		</div>
		<div class="borders" style="padding:10px;">
			<table border="0" cellpadding="3" cellspacing="0" width="100%">
				<tr>
					<td class="strong" align="right" width="15%">User Name:</td>
					<td>
<?php	if($cmd == 'addUser'):	//creating new user, input new username ?>
						<input type="text" name="username" value="<?php echo $username; ?>" size="40" />
<?php	else:	//existing user, do not allow to change username ?>
						<strong><font color="#666666"><?php echo $username; ?></font></strong>
<?php	endif; ?>
					</td>
				</tr>

				<tr>
					<td class="strong" align="right" width="15%">First Name:</td>
					<td><input type="text" name="first_name" size="40" type="text" value="<?php echo $first_name; ?>" /></td>
				</tr>
				<tr>
					<td class="strong" align="right" width="15%">Last Name:</td>
					<td><input type="text" name="last_name" size="40" value="<?php echo $last_name; ?>" /></td>
				</tr>

				<tr>
					<td class="strong" align="right">Email:</td>
					<td><input type="text" name="email" size="40" value="<?php echo $email; ?>" /></td>
				</tr>
				<tr>
					<td class="strong" align="right">Default Role:</td>
					<td>
<?php	if(($u->getRole() >= $g_permission['staff']) && !$editing_self):	//allow to change role only if STAFF editing someone other than self ?>
						<select name="default_role" onchange="this.form.submit();">
<?php		
			foreach($g_permission as $perm_class=>$perm_level):
				if($perm_level > $u->getRole()) {
					continue;	//do not allow staff to assign admin level 
				}
			
				//pre-select current level
				$selected = ($default_role == $perm_level) ? ' selected="selected"' : '';
?>
							<option value="<?php echo $perm_level; ?>"<?php echo $selected; ?>><?php echo strtoupper($perm_class); ?></option>
<?php		endforeach; ?>
						</select>
<?php	else: ?>
						<strong><font color="#666666"><?php echo strtoupper($userObject->getDefaultClass()); ?></font></strong>
						<input type="hidden" name="default_role" value="<?php echo $default_role; ?>" />
<?php	endif; ?>

					</td>
				</tr>
				
<?php	
		//allow to change "not trained" only if STAFF editing someone other than self
		//only bother with this if user is instructor
		if(($default_role == $g_permission['instructor']) && ($u->getRole() >= $g_permission['staff']) && !$editing_self): 
			$selected = $not_trained ? 'checked="checked"' : '';
?>
				<tr>
					<td>&nbsp;</td>
					<td><input type="checkbox" name="not_trained" value="not_trained" <?php echo $selected; ?> /> Not Trained &nbsp;&nbsp;&nbsp;<span class="helperText">Allow only student level access.</span></td>
				</tr>			
<?php	endif; ?>

<?php	if(($default_role >= $g_permission['instructor']) && (!$editing_self || !$not_trained)):	//if editing instructor or better, show ILS fields ?>
				<tr>
					<td class="strong" align="right">ILS User ID:</td>
					<td><input type="text" name="ils_user_id" size="20" value="<?php echo $ils_user_id; ?>" /></td>
				</tr>
				<tr>
					<td class="strong" align="right">ILS User Name:</td>
					<td><input type="text" name="ils_user_name" size="20" value="<?php echo $ils_user_name; ?>" /></td>
				</tr>
<?php	endif; ?>

<?php	if($default_role >= $g_permission['staff']):	//if editing staff, show library assignment ?>
				<tr>
					<td class="strong" align="right">Primary Library:</td>
					<td>
						<select name="staff_library">
<?php	
			foreach($libraries as $lib):
				$selected = ($staff_library == $lib->getLibraryID()) ? ' selected="selected"' : '';
?>
							<option value="<?php echo $lib->getLibraryID(); ?>"<?php echo $selected; ?>><?php echo $lib->getLibraryNickname(); ?></option>
<?php		endforeach; ?>
						</select>
					</td>
				</tr>
<?php	endif; ?>

<?php	self::displayPasswordFields($userObject, true);	//password editing stuff ?>

			</table>
		</div>
		<br />
		<div style="text-align:center;"><input type="submit" name="edit_user_submit" value="Save Changes" /></div>

		</form>
<?php
				
	}


	function displayStaffHome($msg=null)
	{
		echo "<table width=\"66%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr><td width=\"140%\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>\n";
		echo "	<tr><td align=\"center\" valign=\"top\" class=\"helperText\">$msg&nbsp;</td></tr>\n";
		echo "	<tr><td colspan=2>&nbsp;</td></tr>\n";
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
		echo "							<li><a href=\"index.php?cmd=mergeUsers\">Merge Users</a></li>\n";
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
		echo "	<tr><td><img src=\"images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
		echo "</table>\n";
	}

	function displayAssignUser($cmd, $nextCmd, $userToAssign, $msg, $usersObj, $label, $request)
	{
		if(empty($userToEdit)) {
			//ajax user lookup
			$mgr = new ajaxManager('lookupUser', $cmd, 'manageUsers', 'Select User', null, true, array('min_user_role'=>0, 'field_id'=>'selectedUser'));
			$mgr->display();
		}
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