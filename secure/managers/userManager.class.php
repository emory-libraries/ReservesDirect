<?
/*******************************************************************************
userManager.class.php
methods to edit and display users

Created by Jason White (jbwhite@emory.edu)

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
require_once("secure/displayers/userDisplayer.class.php");
require_once("secure/managers/ajaxManager.class.php");

class userManager
{
	public $user;
	public $displayClass;
	public $displayFunction;
	public $argList;

	function display()
	{
		if (is_callable(array($this->displayClass, $this->displayFunction)))
			call_user_func_array(array($this->displayClass, $this->displayFunction), $this->argList);
	}

	function userManager($cmd, $user, $adminUser, $msg="")
	{
		global $page, $loc, $g_permission, $ci, $alertMsg;

		$this->displayClass = "userDisplayer";

		switch ($cmd)
		{

			case 'manageUser':

				$page = "manageUser";

				if ($user->getRole() >= $g_permission['staff'])
				{
					$loc  = "manage users home";

					$this->displayFunction = 'displayStaffHome';
					$this->argList = array(null);
				} elseif ($user->getRole() == $g_permission['instructor']) {

					$loc  = "manage your proxies";

					$this->displayFunction = 'displayInstructorHome';
					$this->argList = "";
				} elseif ($user->getRole() == $g_permission['custodian']) {

					$loc  = "manage user passwords";

					$this->displayFunction = 'displayCustodianHome';
					$this->argList = "";
				}
			break;

			case "newProfile":				
			case "editProfile":
				$page = "manageUser";
				$loc = "edit your profile";

				$newUser = ($cmd == "newProfile") ? true : false;
				
				if ($user->getRole() >= $g_permission['instructor'] 
					|| (isset($request['user']['defaultRole']) && $request['user']['defaultRole'] >= $g_permission['staff']))
					$user->getInstructorAttributes();
				
				$hidden_fields = array (
					'cmd' 			=> 'storeUser', 
					'previous_cmd'	=> $cmd,
					'newUser'		=> $newUser
				);
				
				$this->displayFunction = 'displayEditUser';
				$this->argList = array($user, $user, null, $_REQUEST, null, $hidden_fields, $user->getLibraries());
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

			case 'addUser':
				$page = "manageUser";
				$loc = "create user";

				switch ($_REQUEST['user']['defaultRole'])
				{
					case $g_permission['admin']:
						$userToEdit = new admin();
						break;
					case $g_permission['staff']:
						$userToEdit = new staff();
						break;	
					case $g_permission['instructor']: //need to have access to intructor_attributes
						$userToEdit = new instructor();
						break;
					case $g_permission['proxy']:
						$userToEdit = new proxy();
						break;
					case $g_permission['custodian']:
						$userToEdit = new custodian();
						break;
					case $g_permission['custodian']:
					default:
						$userToEdit = new student();
						break;											
				}

				if (isset($_REQUEST['user']))  // we do not want to store this to the db yet but should populate the object for display to the form
				{
					$userToEdit->userName  = $_REQUEST['user']['username'];
					$userToEdit->firstName = $_REQUEST['user']['first_name'];
					$userToEdit->lastName  = $_REQUEST['user']['last_name'];
					$userToEdit->dfltRole  = $_REQUEST['user']['defaultRole'];
					$userToEdit->email	   = $_REQUEST['user']['email'];

					if ($userToEdit->getRole() >= $g_permission['instructor'] && isset($_REQUEST['user']['ils_user_name']))
					{
						$userToEdit->ils_user_id = $_REQUEST['user']['ils_user_id'];
						$userToEdit->ils_name = $_REQUEST['user']['ils_user_name'];
					}

				}

				$hidden_fields = array (
					'cmd' 			=> 'storeUser', 
					'previous_cmd'	=> $cmd,
					'newUser'		=> false
				);
				
				$this->displayFunction = 'displayEditUser';				
				$this->argList = array($userToEdit, $user, null, $_REQUEST, null, $hidden_fields, $user->getLibraries());
			break;

			case 'resetPwd':
				$page = "manageUser";
				$loc = "reset user password";

				$users = new users();

				if (isset($_REQUEST['select_user_by']) && isset($_REQUEST['user_qryTerm']))
					$users->search($_REQUEST['select_user_by'], $_REQUEST['user_qryTerm']);

				$userToEdit = (isset($_REQUEST['selectedUser'])) ? new user($_REQUEST['selectedUser']) : null;

				$sp = new specialUser();

				if (!is_null($userToEdit))
				{
					if (!$userToEdit->isSpecialUser())
						$sp->createNewSpecialUser($userToEdit->getUsername(), $userToEdit->getEmail(), null);
					else
						$sp->resetPassword($userToEdit->getUsername());

					if ($user->getRole() == $g_permission['custodian']) {
						$this->displayFunction = 'displayCustodianHome';
						$this->argList = array('Override Password Reset');
					} else {
						$this->displayFunction = 'displayStaffHome';
						$this->argList = array('Override Password Reset');
					}
				} else {
					$hidden_fields = array (
						'cmd' 			=> 'storeUser', 
						'previous_cmd'	=> $cmd,
						'newUser'		=> false
					);
				//($userToEdit, $user, $msg=null, $request, $usersObj=null, $hidden_fields=null)
					$this->displayFunction = 'displayEditUser';
					$this->argList = array($userToEdit, $user, null, $_REQUEST, $users, $hidden_fields, $user->getLibraries());
				}

			break;


			case 'setPwd':
				if (isset($_REQUEST['selectedUser']) && $_REQUEST['selectedUser'] == "")
				{
					$userToEdit = new user();

					$userToEdit->createUser($_REQUEST['user']['username'], $_REQUEST['user']['first_name'], $_REQUEST['user']['last_name'], $_REQUEST['user']['email'], $_REQUEST['user']['defaultRole']);
				} else
					$userToEdit = (isset($_REQUEST['selectedUser'])) ? new user($_REQUEST['selectedUser']) : null;

				if (!is_null($userToEdit) && !$userToEdit->isSpecialUser())
				{
					$sp = new specialUser();
					$sp->createNewSpecialUser($userToEdit->getUsername(), $userToEdit->getEmail(), null);
				}

			case 'editUser':
				$page = "manageUser";
				$loc = "edit user profile";

				$users = new users();

				//if user to be editted has been selected create object and get data
				if (!isset($userToEdit))
				{
					if (isset($_REQUEST['selectedUser']))
					{						
						$tmpUser = new user($_REQUEST['selectedUser']);
						
						$role = (isset($_REQUEST['user']['defaultRole'])) ? $_REQUEST['user']['defaultRole'] : $tmpUser->getDefaultClass();
										
						$userToEdit = $users->initUser($role, $tmpUser->getUsername());
						$userToEdit->getUserByID($_REQUEST['selectedUser']);		
						unset($tmpUser);
						
						if ($role >= 'instructor')
							$userToEdit->getInstructorAttributes();
						
					} else
						$userToEdit = null;
				}
				

				$hidden_fields = array (
					'cmd' 			=> 'storeUser', 
					'previous_cmd'	=> $cmd,
					'newUser'		=> false
				);
			
				$this->displayFunction = 'displayEditUser';
				$this->argList = array($userToEdit, $user, null, $_REQUEST, $users, $hidden_fields, $user->getLibraries());

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
				ajaxManager::ajaxManager();			
					
				if(!empty($_REQUEST[$field_name])) {	//if already selected a user, show class lookup
					$loc .= "select class";	//show where we are
					$hidden[$field_name] = $_REQUEST[$field_name];	//pass on the user id
					ajaxManager::lookup('lookupClass', $next_cmd, 'manageUser', 'Select Class', $hidden);
				}
				else {	//show user lookup
					$loc .= "select user";	//show where we are
					ajaxManager::lookup('lookupUser', $cmd, 'manageUser', "Select User", null, true, array('min_user_role'=>$min_user_role, 'field_id'=>$field_name));
				}

			break;

			case 'storeUser':
				$editUser = new user();
				

				
				
				if ($_REQUEST['previous_cmd'] == 'addUser')
				{
					$tmpUser = new user();
					if (!$tmpUser->getUserByUserName($_REQUEST['user']['username']))
					{
						$editUser->createUser($_REQUEST['user']['username'], '', '', '', $_REQUEST['user']['defaultRole']);
					} else {
						$hidden_fields = array (
							'cmd' 			=> 'addUser', 
							'previous_cmd'	=> 'storeUser',
							'newUser'		=> false
						);
						
						$this->displayFunction = 'displayEditUser';
						$this->argList = array($editUser, $user, "This username is in use.  Please choose another.", $_REQUEST, $users, $hidden_fields, $user->getLibraries());
						return;
					}
				} else
					$editUser->getUserByID($_REQUEST['user']['userID']);

					
				$usersObject = new users();
				//$editUser->getRole()
				$editUser = $usersObject->initUser($_REQUEST['user']['defaultRole'], $editUser->getUsername());	
					
				if ($editUser->setEmail($_REQUEST['user']['email']))
				{
					$editUser->setFirstName($_REQUEST['user']['first_name']);
					$editUser->setLastName($_REQUEST['user']['last_name']);
					$editUser->setDefaultRole($_REQUEST['user']['defaultRole']);

					if ($editUser->isSpecialUser() && isset($_REQUEST['user']['pwd']) && $_REQUEST['user']['pwd'] != "")
					{
						$sp = new specialUser($editUser->getUserID());
						$sp->resetPassword($editUser->getUsername(), $_REQUEST['user']['pwd']);
					}

					if (isset($_REQUEST['user']['ils_user_id']))
					{
						$editUser->storeInstructorAttributes($_REQUEST['user']['ils_user_id'], $_REQUEST['user']['ils_user_name']);
					}

					if ($editUser->getRole() >= $g_permission['instructor'] && isset($_REQUEST['user']['staff_library']))
						$editUser->assignStaffLibrary($_REQUEST['user']['staff_library']);
						
					if (isset($_REQUEST['user']['not_trained']) && $_REQUEST['user']['not_trained'] == 'not_trained')
						$editUser->addNotTrained();
					else
						$editUser->removeNotTrained();
						
					$msg = "User Record Successfully Saved";
				} else {
					$msg = "Invalid Email Format - Changes Not Saved";
				}

				if ($user->getRole() == $g_permission['custodian']) {
					$this->displayFunction = 'displayCustodianHome';
					$this->argList = array("User Password Successfully Changed");
				} elseif ($user->getRole() >= $g_permission['staff']) {
					$this->displayFunction = 'displayStaffHome';
					$this->argList = array($msg);
				} else {
					require_once("secure/managers/reservesManager.class.php");
					reservesManager::reservesManager('viewCourseList', $user);
					break;
				}
			break;

			case 'addProxy':
			case 'removeProxy':
				$page = "manageUser";

				$courseInstances = $user->getCourseInstancesToEdit();

				$this->displayFunction = 'displayEditProxy';
				$this->argList = array($courseInstances,'editProxies');
			break;

			case 'removePwd':
				$page = "manageUser";
				$loc = "remove user password";

				$users = new users();
				
				$userToEdit = (isset($_REQUEST['selectedUser'])) ? new user($_REQUEST['selectedUser']) : null;

				if (!is_null($userToEdit))
				{
					if ($userToEdit->isSpecialUser())
					{
						$sp = new specialUser($userToEdit->getUserID());
						$sp->destroy();
					}

					if ($user->getRole() == $g_permission['custodian']) {
						$this->displayFunction = 'displayCustodianHome';
						$this->argList = array('Override Password Removed');
					} else {
						$this->displayFunction = 'displayStaffHome';
						$this->argList = array('Override Password Removed');
					}
				} else {
					$this->displayFunction = 'displayAssignUser';
					$this->argList = array($cmd, $cmd, $userToEdit, null, $users, 'Remove Override Password', $_REQUEST);
				}
			break;

		}
	}
}
?>