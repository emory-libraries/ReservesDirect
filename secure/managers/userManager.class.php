<?
/*******************************************************************************
userManager.class.php
methods to edit and display users

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

require_once("secure/displayers/userDisplayer.class.php");
require_once('secure/managers/baseManager.class.php');
require_once('secure/classes/specialUser.class.php');
require_once('secure/interface/admin.class.php');	//this will also include all children

class userManager extends baseManager {

	function userManager($cmd, $user, $adminUser, $msg="") {
		global $page, $loc, $g_permission, $u, $alertMsg, $help_article;

		$this->displayClass = "userDisplayer";
		$page = "manageUser";
		
		//there are a many CMD cases that require a user lookup
		//catch them all here and show them the AJAX user lookup box
		$cmds_requiring_user = array('editUser', 'setPwd', 'resetPwd', 'removePwd');
		if(in_array($cmd, $cmds_requiring_user) && empty($_REQUEST['selectedUser'])) {
			$this->getUser($cmd);
			return;	//don't do anything else
		}

		switch ($cmd) {

			case 'manageUser':
				$page = "manageUser";

				if ($user->getRole() >= $g_permission['staff'])
				{
					$loc  = "manage users home";

					$this->displayFunction = 'displayStaffHome';
					$this->argList = array(null);
				} elseif ($user->getRole() == $g_permission['instructor']) {

					$loc  = "manage your proxies";
					$help_article = "14";

					$this->displayFunction = 'displayInstructorHome';
					$this->argList = "";
				} elseif ($user->getRole() == $g_permission['custodian']) {

					$loc  = "manage user passwords";

					$this->displayFunction = 'displayCustodianHome';
					$this->argList = "";
				}
			break;
			
			
			case 'editUser':
				if(isset($_REQUEST['edit_user_submit'])) {
					//attempt to set user info
					//the === is important: method returns TRUE on success, but error msg if it fails
					//(the error string would evaluate to true if using ==)
					if(($msg = $this->setUser()) === true) {	
						break;	//done, if successfull, otherwise will evaluate the rest of the block
					}
				}

				$loc = 'edit user profile';					
				
				//assume that there IS a selectedUser (this should be caught by the if statement at the top of the method.
				$tmp_user = new user($_REQUEST['selectedUser']);
				$username = $tmp_user->getUsername();
				//use the requested/updated role if it exists, stored otherwise
				//it is important to use DEFAULT role (since not-trained instructors will return a 'role' of student)
				$role = !empty($_REQUEST['default_role']) ? $_REQUEST['default_role'] : $tmp_user->getDefaultRole();
				
				//now init the user as an object of the proper class
				$users = new users();
				$userToEdit = $users->initUser($role, $username);
				
				$this->displayFunction = 'displayEditProfile';
				$this->argList = array($userToEdit, $cmd, $msg);
			break;
			
			
			case 'addUser':
				if(isset($_REQUEST['edit_user_submit'])) {
					//attempt to set user info
					//the === is important: method returns TRUE on success, but error msg if it fails
					//(the error string would evaluate to true if using ==)
					if(($msg = $this->setUser()) === true) {	
						break;	//done, if successfull, otherwise will evaluate the rest of the block
					}
				}

				$loc = 'create a new user';

				//create base user object
				$userToEdit = new user();
				
				$this->displayFunction = 'displayEditProfile';
				$this->argList = array($userToEdit, $cmd, $msg);
			break;
						
			
			case 'newProfile':
			case 'editProfile':
				if(isset($_REQUEST['edit_user_submit'])) {
					//attempt to set user info
					//the === is important: method returns TRUE on success, but error msg if it fails
					//(the error string would evaluate to true if using ==)
					if(($msg = $this->setUser()) === true) {
						break;	//done, if successfull, otherwise will evaluate the rest of the block
					}
				}

				$loc = 'edit your profile';
				$this->displayFunction = 'displayEditProfile';
				$this->argList = array($user, $cmd, $msg);
			break;
			
			
			case 'setPwd':
				$loc = 'set override password';
				
				$userToEdit = new user($_REQUEST['selectedUser']);
				
				if(isset($_REQUEST['edit_pass_submit'])) {	//set password
					//do not allow empty passwords
					if(!empty($_REQUEST['override_pass'])) {
						//make sure the password and confirmation password match
						if($_REQUEST['override_pass']==$_REQUEST['override_pass_confirm']) {
							//change password
							$sp_user = new specialUser();
							if(!$userToEdit->isSpecialUser()) {	//no password record exists, create it first
								$sp_user->createNewSpecialUser($userToEdit->getUsername(), $userToEdit->getEmail());
							}
							//set password
							$sp_user->resetPassword($userToEdit->getUsername(), $_REQUEST['override_pass']);
			
							//take user back to manage-user page
							$this->argList = array('Password succesfully changed for <u>'.$userToEdit->getName(false).'</u>');
							if ($u->getRole() == $g_permission['custodian']) {
								$this->displayFunction = 'displayCustodianHome';
							} elseif ($u->getRole() >= $g_permission['staff']) {
								$this->displayFunction = 'displayStaffHome';
							}						
						}
						else {
							$msg = 'The passwords do not match';
							$this->displayFunction = 'displayEditPassword';
							$this->argList = array($userToEdit, $cmd, $msg);
						}						
					}
					else {
						$msg = 'Cannot leave password blank';
						$this->displayFunction = 'displayEditPassword';
						$this->argList = array($userToEdit, $cmd, $msg);
					}			
				}
				else {			
					$this->displayFunction = 'displayEditPassword';
					$this->argList = array($userToEdit, $cmd);
				}
			break;
			
			
			case 'resetPwd':
				$loc = 'reset override password';
				
				//already made sure to have a user
				$userToEdit = new user($_REQUEST['selectedUser']);

				//get special user object
				$sp_user = new specialUser();
				if(!$userToEdit->isSpecialUser()) {	//no password record exists, create it first
					$sp_user->createNewSpecialUser($userToEdit->getUsername(), $userToEdit->getEmail());
				}
				//reset password
				$sp_user->resetPassword($userToEdit->getUsername());

				//take user back to manage-user page
				$this->argList = array('Password succesfully reset for <u>'.$userToEdit->getName(false).'</u>');
				if ($u->getRole() == $g_permission['custodian']) {
					$this->displayFunction = 'displayCustodianHome';
				} elseif ($u->getRole() >= $g_permission['staff']) {
					$this->displayFunction = 'displayStaffHome';
				}
			break;
			
			
			case 'removePwd':
				$loc = 'remove override password';
				
				//already made sure to have a user
				$userToEdit = new user($_REQUEST['selectedUser']);
				
				//destroy password record if it exists
				if($userToEdit->isSpecialUser()) {
					$sp_user = new specialUser($userToEdit->getUserID());
					$sp_user->destroy();
				}

				//take user back to manage-user page
				$this->argList = array('Password succesfully removed for <u>'.$userToEdit->getName(false).'</u>');
				if ($u->getRole() == $g_permission['custodian']) {
					$this->displayFunction = 'displayCustodianHome';
				} elseif ($u->getRole() >= $g_permission['staff']) {
					$this->displayFunction = 'displayStaffHome';
				}
			break;


			case "mergeUsers":				
				$page = "manageUser";
				$loc = "merge user profiles";

				$userObj = new users();
				
				if (isset($_REQUEST['userToKeep_selectedUser']) && isset($_REQUEST['userToMerge_selectedUser']) && isset($_REQUEST['subMerge']))
				{
					if ($_REQUEST['userToKeep_selectedUser'] != $_REQUEST['userToMerge_selectedUser'])
					{						
						$userObj->mergeUsers($_REQUEST['userToKeep_selectedUser'], $_REQUEST['userToMerge_selectedUser']);
						
						$alertMsg = "Users successfully merged.";
						
						$this->displayFunction = 'displayStaffHome';
						$this->argList = array(null);
					} else {
						$alertMsg = "User to keep and User to merge must be different users.";

						$this->displayFunction = 'displayMergeUser';
						$this->argList = array($_REQUEST, array('cmd'=>$cmd), $userObj, $cmd);	
					}
				} else {
					$hidden_fields = array (
						'cmd' 			=> $cmd, 
					);				
					
														
					$this->displayFunction = 'displayMergeUser';
					$this->argList = array($_REQUEST, $hidden_fields, $userObj, $cmd);			
				}
			break;

			case 'assignProxy':
			case 'assignInstr':
				//set some info
				if($cmd == 'assignInstr') {
					$next_cmd = 'editInstructors';
					$hidden = array('addInstructor'=>'true');
					$min_user_role = 3;
					$field_name = 'selected_instr';
					$user_role_label = 'instructor';
				}
				elseif($cmd == 'assignProxy') {
					$next_cmd = 'editProxies';
					$hidden = array('addProxy'=>'true');
					$min_user_role = 0;
					$field_name = 'proxy';
					$user_role_label = 'proxy';
				}
				
				$page = 'manageUser';	//set tab
				$loc = "assign $user_role_label to class >> ";
				
				//init a manager - sets the displayer
					
				if(!empty($_REQUEST[$field_name])) {	//if already selected a user, show class lookup
					$loc .= "select class";	//show where we are
					$hidden[$field_name] = $_REQUEST[$field_name];	//pass on the user id
					
					//override displayer to show ajaxDisplayer::classLookup
					$this->displayClass 	= "ajaxDisplayer";
					$this->displayFunction 	= "classLookup";
					$this->argList 			= array($next_cmd, 'Select Class', $hidden);
				}
				else {	//show user lookup
					$loc .= "select user";	//show where we are

					//override displayer to show ajaxDisplayer::userLookup
					$this->displayClass		= "ajaxDisplayer";					
					$this->displayFunction 	= "userLookup";
					$this->argList 			= array($cmd, "Select User", array('min_user_role'=>$min_user_role), true, $g_permission['student'], $field_name);
				}

			break;

			case 'addProxy':
			case 'removeProxy':
				$page = "manageUser";

				$courseInstances = $user->getCourseInstancesToEdit();

				$this->displayFunction = 'displayEditProxy';
				$this->argList = array($courseInstances,'editProxies', $cmd);
			break;
		}
	}
	
	
	/**
	 * Set user profile information in the database
	 *
	 * @return (string) error message on failure; (boolean) TRUE on success -- will also display the manage-user home on success;
	 * 	
	 * Usage Note: if(($msg = $this->setUser()) === true) { user stored successfully code } else { echo error: $msg }
	 */
	function setUser() {
		global $g_permission, $u;
		
		$userToEdit = new user();
		
		//check to make sure that all required fields have been filled
		if(empty($_REQUEST['first_name'])) {	//first name
			return "First name is required";
		}		
		if(empty($_REQUEST['last_name'])) {	//last name
			return "Last name is required";
		}
		if(empty($_REQUEST['email'])) {	//email
			return "The email is required";
		}
		//if editing password, make sure confirmation matches
		if(!empty($_REQUEST['override_pass']) && ($_REQUEST['override_pass'] != $_REQUEST['override_pass_confirm'])) {
			return "Passwords do not match";
		}
		
		if(empty($_REQUEST['selectedUser'])) {	//creating new user
			//make sure have username
			if(empty($_REQUEST['username'])) {
				return "The username is required";
			}

			//check for match to existing user
			if(!$userToEdit->getUserByUserName($_REQUEST['username'])) {
				//no match, create new basic user
				$userToEdit->createUser(trim($_REQUEST['username']));
			}
			else {
				//duplicate username
				return "This username is in use.  Please choose another";
			}			
		}
		else {	//editing existing user
			//init user object
			$userToEdit->getUserByID($_REQUEST['selectedUser']);
		}
		
		//set everything else
		if(!$userToEdit->setEmail(trim($_REQUEST['email']))) {	//email not valid
			return "The provided email is not valid";
		}
		$userToEdit->setLastName(trim($_REQUEST['last_name']));
		$userToEdit->setFirstName(trim($_REQUEST['first_name']));
		$userToEdit->setDefaultRole($_REQUEST['default_role']);
		//not-trained
		if(isset($_REQUEST['not_trained'])) {	//if checkbox was checked
			$userToEdit->addNotTrained();	//add to not-trained table
		}
		else {	//if checkbox was not checked
			$userToEdit->removeNotTrained();	//remove entry from not-trained, if it existed
		}
		//if setting instructor and have ILS info		
		if(($_REQUEST['default_role'] >= $g_permission['instructor']) && (!empty($_REQUEST['ils_user_id']) || !empty($_REQUEST['ils_user_name']))) {
			//need the object to be an instructor class
			$userToEdit = new instructor($userToEdit->getUsername());
			//now set the info
			$userToEdit->storeInstructorAttributes(trim($_REQUEST['ils_user_id']), trim($_REQUEST['ils_user_name']));
		}
		//or if setting staff and have a staff-library
		elseif(($_REQUEST['default_role'] >= $g_permission['staff']) && !empty($_REQUEST['staff_library'])) {
			//need staff object
			$userToEdit = new staff($userToEdit->getUsername());
			//set info
			$userToEdit->assignStaffLibrary($_REQUEST['staff_library']);
		}
		//override password
		//do not need to check if pass==pass_confirm, since already did it once
		if(!empty($_REQUEST['override_pass'])) {
			$sp_user = new specialUser();
			if(!$userToEdit->isSpecialUser()) {	//no password record exists, create it first
				$sp_user->createNewSpecialUser($userToEdit->getUsername(), $userToEdit->getEmail());
			}
			//set password
			$sp_user->resetPassword($userToEdit->getUsername(), $_REQUEST['override_pass']);			
		}

		//success, redirect everyone to their proper place
		if($u->getRole() >= $g_permission['staff']) {
			$this->displayFunction = 'displayStaffHome';
			$this->argList = array('Profile succesfully updated for <u>'.$userToEdit->getName(false).'</u>');
		}
		else {
			require_once("secure/managers/classManager.class.php");
			classManager::classManager('viewCourseList', $userToEdit, $u, array());
		}
		
		return true;
	}
	
	
	/**
	 * Displays ajax user-lookup screen
	 */
	function getUser($cmd) {
		$page = 'manageUser';
		$loc = 'select user';

		//override displayer to show ajaxDisplayer::userLookup
		$this->displayClass		= "ajaxDisplayer";					
		$this->displayFunction 	= "userLookup";
		$this->argList 			= array($cmd, "Select User", array('min_user_role' => $g_permission['student']), true, $g_permission['student'], 'selectedUser');		
	}
}
?>
