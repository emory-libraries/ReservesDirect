<?
/*******************************************************************************
userManager.class.php
methods to edit and display users

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
require_once("secure/common.inc.php");
require_once("secure/displayers/userDisplayer.class.php");

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
		global $page, $loc, $g_permission, $ci;
		
		$this->displayClass = "userDisplayer";

		switch ($cmd)
		{
			
			case 'manageUser':
				
				$page = "manageUser";
				
				if ($user->getDefaultRole() >= $g_permission['staff'])
				{
					$loc  = "home";
					
					$this->displayFunction = 'displayStaffHome';
					$this->argList = array(null);
				} elseif ($user->getDefaultRole() == $g_permission['instructor']) {

					$loc  = "home";

					$this->displayFunction = 'displayInstructorHome';
					$this->argList = "";
				} elseif ($user->getDefaultRole() == $g_permission['custodian']) {

					$loc  = "home";

					$this->displayFunction = 'displayCustodianHome';
					$this->argList = "";
				}
			break;
			
			case "editProfile":
				$page = "manageUser";				
							
				if ($user->getDefaultRole() >= $g_permission['instructor'])
					$user->getInstructorAttributes();
				
				$this->displayFunction = 'displayEditUser';
				$this->argList = array($cmd, 'storeUser', $user, $user, null, null, $_REQUEST);
			break;
			
			case 'addUser':
				$page = "manageUser";				
				
				if ($_REQUEST['user']['defaultRole'] >= $g_permission['instructor']) //need to have access to intructor_attributes
					$userToEdit = new instructor();	
				else
					$userToEdit = new user();	
					
				if (isset($_REQUEST['user']))  // we do not want to store this to the db yet but should populate the object for display to the form
				{				
					$userToEdit->userName  = $_REQUEST['user']['username'];
					$userToEdit->firstName = $_REQUEST['user']['first_name'];
					$userToEdit->lastName  = $_REQUEST['user']['last_name'];
					$userToEdit->dfltRole  = $_REQUEST['user']['defaultRole'];
					$userToEdit->email	   = $_REQUEST['user']['email'];
					
					if ($userToEdit->dfltRole >= $g_permission['instructor'] && isset($_REQUEST['user']['ils_user_name']))
					{
						$userToEdit->ils_user_id = $_REQUEST['user']['ils_user_id'];
						$userToEdit->ils_name = $_REQUEST['user']['ils_user_name'];
					}
						
				}
				
				$this->displayFunction = 'displayEditUser';
				$this->argList = array($cmd, 'storeUser', $userToEdit, $user, null, null, $_REQUEST);
			break;
					
			case 'resetPwd':
				$page = "manageUser";				

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
						
					if ($user->getDefaultRole() == $g_permission['custodian']) {
						$this->displayFunction = 'displayCustodianHome';
						$this->argList = array('Override Password Reset');
					} else {
						$this->displayFunction = 'displayStaffHome';
						$this->argList = array('Override Password Reset');						
					}
				} else {				
					$this->displayFunction = 'displayEditUser';
					$this->argList = array($cmd, 'storeUser', $userToEdit, $user, null, $users, $_REQUEST);
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

				$users = new users();				

				if (isset($_REQUEST['select_user_by']) && isset($_REQUEST['user_qryTerm']))
					$users->search($_REQUEST['select_user_by'], $_REQUEST['user_qryTerm']);	

				if (!isset($userToEdit))
				{
					if (isset($_REQUEST['selectedUser']))
					{
						$userToEdit = new instructor();		
						$userToEdit->getUserByID($_REQUEST['selectedUser']);
					} else 
						$userToEdit = null;
				}
						
				if (!is_null($userToEdit) && $userToEdit->getDefaultRole() >= $g_permission['instructor'])
				{
					//recreate as instructor
					$userToEdit = new instructor($userToEdit->getUsername());
					$userToEdit->getInstructorAttributes();					
				}
				
				$this->displayFunction = 'displayEditUser';
				$this->argList = array($cmd, 'storeUser', $userToEdit, $user, null, $users, $_REQUEST);
				
			break;
			
			case 'assignProxy':
			case 'assignInstr':
				$page = "manageUser";				

				$ci = (isset($_REQUEST['ci'])) ? new courseInstance($_REQUEST['ci']) : null;
						
				$this->displayFunction = 'displayEditUser';
				$this->argList = array($cmd, 'storeUser', $userToEdit, $user, null, $users, $_REQUEST);
				
				if (is_null($ci)) { // user has been seleced so choose class
					require_once("managers/selectClassManager.class.php");			
					selectClassManager::selectClassManager('lookupClass', $cmd, $cmd, 'Assign User', $user, $_REQUEST, null);
				} else { // show proxy screen
					require_once("managers/classManager.class.php");
					
					if ($cmd == 'assignProxy')
						classManager::classManager('editProxies', $user, $adminUser, $_REQUEST);
					else
						classManager::classManager('editInstructors', $user, $adminUser, $_REQUEST); 
				}
				
			break;
			
			case 'storeUser':			
				$editUser = new user();
				if ($_REQUEST['previous_cmd'] == 'addUser')							
				{
					$tmpUser = new user();
					if (!$tmpUser->getUserByUserName($_REQUEST['user']['username']))
					{
						$editUser->createUser($_REQUEST['user']['username'], '', '', '', 0);
					} else {
						//$editUser->setEmail($_REQUEST['user']['email']);
						//$editUser->setFirstName($_REQUEST['user']['first_name']);
						//$editUser->setLastName($_REQUEST['user']['last_name']);					
						//$editUser->setDefaultRole($_REQUEST['user']['defaultRole']);						
						
						$this->displayFunction = 'displayEditUser';
						$this->argList = array('addUser', 'storeUser', $editUser, $user, "This username is in use.  Please choose another.", $users, $_REQUEST);
						return;
					}
				} else 
					$editUser->getUserByID($_REQUEST['user']['userID']);

				if ($editUser->setEmail($_REQUEST['user']['email']))
				{
					$editUser->setFirstName($_REQUEST['user']['first_name']);
					$editUser->setLastName($_REQUEST['user']['last_name']);					
					$editUser->setDefaultRole($_REQUEST['user']['defaultRole']);
					
					if ($editUser->isSpecialUser() && isset($_REQUEST['user']['pwd']))
					{
						$sp = new specialUser($editUser->getUserID());
						$sp->resetPassword($editUser->getUsername(), $_REQUEST['user']['pwd']);
					}	
					
					if ($editUser->getDefaultRole() >= $g_permission['instructor'] && isset($_REQUEST['user']['ils_user_id']))
					{
						$editUser = new instructor($editUser->getUsername());  //recreate as instructor						
						$editUser->storeInstructorAttributes($_REQUEST['user']['ils_user_id'], $_REQUEST['user']['ils_user_name']);
					}
					
					$msg = "User Record Successfully Saved";
				} else {
					$msg = "Invalid Email Format";
				}
				
				if ($user->getDefaultRole() == $g_permission['custodian']) {
					$this->displayFunction = 'displayCustodianHome';
					$this->argList = array("User Password Successfully Changed");
				} else {
					$this->displayFunction = 'displayStaffHome';
					$this->argList = array($msg);
				}
			break;
			
			case 'addProxy':
			case 'removeProxy':
				$page = "manageUser";				

				$courseInstances = $user->getCourseInstances($aDate=null,$eDate=null,$editableOnly=true);

				$this->displayFunction = 'displayEditProxy';
				$this->argList = array($courseInstances,'editProxies');
			break;
			
			case 'removePwd':
				$page = "manageUser";				

				$users = new users();				

				if (isset($_REQUEST['select_user_by']) && isset($_REQUEST['user_qryTerm']))
					$users->search($_REQUEST['select_user_by'], $_REQUEST['user_qryTerm']);	
													
				$userToEdit = (isset($_REQUEST['selectedUser'])) ? new user($_REQUEST['selectedUser']) : null;
				
				if (!is_null($userToEdit))
				{	
					if ($userToEdit->isSpecialUser())
					{
						$sp = new specialUser($userToEdit->getUserID());
						$sp->destroy();
					}	
													
					if ($user->getDefaultRole() == $g_permission['custodian']) {
						$this->displayFunction = 'displayCustodianHome';
						$this->argList = array('Override Password Removed');
					} else {
						$this->displayFunction = 'displayStaffHome';
						$this->argList = array('Override Password Removed');				
					}
				} else {
					$this->displayFunction = 'displayAssignUser';
					$this->argList = array($cmd, $nextCmd, $userToEdit, null, $users, 'Remove Override Password', $_REQUEST);
				}
			break;
			
		}
	}
}
?>