<?
/*******************************************************************************
index.php
primary processing and display page

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
require_once("config.inc.php");
require_once("common.inc.php");

require_once("classes/users.class.php");

require_once("interface/student.class.php");
require_once("interface/custodian.class.php");
require_once("interface/proxy.class.php");
require_once("interface/instructor.class.php");
require_once("interface/staff.class.php");
require_once("interface/admin.class.php");

// we will do our own error handling
if (isset($_SESSION['debug']))
{
	error_reporting(E_ALL);
} else {
	error_reporting(0);
	$old_error_handler = set_error_handler("common_ErrorHandler");
}

include("session.inc.php");

$userName = $_SESSION['username'];
$userClass = $_SESSION['userClass'];

//print_r ($_REQUEST);echo "<hr>";

//init user based on type
$usersObject = new users();
$u = $usersObject->initUser($userClass, $userName);

$adminUser = null;
if (isset($_REQUEST['u']))
{	
	 $tmpUser = new user;
	 $tmpUser->getUserByID($_REQUEST['u']);
	 
	 $adminUser = $usersObject->initUser($tmpUser->getUserClass(), $tmpUser->getUsername());
	 
	 unset($tmpUser);
}

//if selected set CourseInstance
if (isset($_REQUEST['ci']))
{
	$ci = new courseInstance($_REQUEST['ci']);
	$ci->getPrimaryCourse();
} else 
	$ci = null;

//set cmd default to viewCourseList
$cmd = (isset($_REQUEST['cmd'])) ? $_REQUEST['cmd'] : 'viewCourseList';

/*
//Force user to update email address
if ($u->getEmail() == "") //direct user to edit profile
{
	 $cmd = 'editProfile';
}
*/

switch ($cmd) 
{
	case 'myReserves':
	case 'viewCourseList':  // myReserves Course List
	case 'viewReservesList': // myReserves Reserve List
	case 'previewReservesList':
	case 'sortReserves':
	case 'customSort':
	case 'selectInstructor': //addReserve Staff Interface - Search for Class by Instructor or Dept				
	case 'addReserve': //Proxy & Faculty Interface - add a reserve to a class
	case 'displaySearchItemMenu': //addReserve - How would you like to put item on reserve? screen
	case 'searchScreen': //addReserve - Search for Item
	case 'searchResults': //addReserve - Search Results Screen
	case 'storeReserve': //addReserve - Store Reserves Screen
	case 'uploadDocument': //addReserve - upload Document Screen
	case 'addURL': //addReserve - add a URL screen
	case 'storeUploaded': //addReserve page - Store uploaded document
	case 'faxReserve': //addReserve - Fax Reserve Screen
	case 'getFax': //addReserve - Claim Fax Screen
	case 'addFaxMetadata': //addReserve - Fax Meta Data Screen
	case 'storeFaxMetadata': //addReserve - Store Fax Meta Data Screen
	case 'addStudent': //myReserves - give a user student access to class
	case 'removeStudent': //myReserves - remove a students access to a class
		require_once("managers/reservesManager.class.php");		
		$mgr = new reservesManager($cmd, $u);
	break;
	
	case 'addReserve':
	
	case 'manageClasses':
	case 'editProxies':
	case 'editInstructors':
	case 'editCrossListings':
	case 'editTitle':
	case 'editClass':			// manageClass edit class
	case 'reactivateClass':		// manageClass choose class to reactivate
	case 'reactivateList':
	case 'createClass':			// manageClass create class (enter meta-data)
	case 'createNewClass':		// manageClass create class (store meta-data to DB)
	case 'reactivate':			// managerClass reactivate class
	case 'searchForClass':		// myReserves - search for a class by Instructor or Dept
	case 'addClass':			// myReserves - add a class as a student
	case 'removeClass':			// myReserves - remove a class you are a student in		
		require_once("managers/classManager.class.php");		
		$request = $_REQUEST;
		$mgr = new classManager($cmd, $u, $adminUser, $_REQUEST);	
	break;
	
	case 'manageUser':
	case 'editProfile':
	case 'storeUser':
	case 'editUser':
	case 'addUser':
	case 'assignProxy':
	case 'assignInstr':
	case 'setPwd':
	case 'resetPwd':
	case 'removePwd':
		require_once("managers/userManager.class.php");		
		$mgr = new userManager($cmd, $u, $adminUser);	
	break;
	
	case 'editItem':
		require_once("managers/itemManager.class.php");
		$mgr = new itemManager($cmd, $u);
	break;
	
	case 'displayRequest':
	case 'processRequest':	
	case 'storeRequest':
		require_once("managers/requestManager.class.php");
		$mgr = new requestManager($cmd, $u, $ci, $_REQUEST);
	break;	
	
	case 'addDigitalItem':
	case 'addPhysicalItem':
		if (!isset($_REQUEST['ci']) || !isset($_REQUEST['selected_instr']))
		{
			//display selectClass
			require_once("managers/selectClassManager.class.php");
			$mgr = new selectClassManager('lookupClass', $cmd, 'manageClass', 'Select Class', $u, $_REQUEST);
		} else {
			//add Physical Item
			require_once("managers/requestManager.class.php");
			$mgr = new requestManager($cmd, $u, $ci, $_REQUEST);
		}
	break;
	
	case 'staffEditClass':
		if (!isset($_REQUEST['ci']) || !isset($_REQUEST['selected_instr']))
		{
			//display selectClass
			require_once("managers/selectClassManager.class.php");			
			$mgr = new selectClassManager('lookupClass', $cmd, 'manageClass', 'Edit Class', $u, $_REQUEST);
		} else {
			require_once("managers/classManager.class.php");					
			$mgr = new classManager('editClass', $u, $adminUser, $_REQUEST);
		}	
	break;
	
	case 'addNote':
	case 'saveNote':
		require_once("managers/noteManager.class.php");
		$mgr = new noteManager($cmd, $u, $_REQUEST['reserve_id']);
	break;
	
	default:
		trigger_error("index.php cmd=$cmd case not defined", E_USER_ERROR);
}

if (isset($_REQUEST['no_control']))
	include "html/no_control.inc.html";
else
	include "html/index.inc.html";
?>

