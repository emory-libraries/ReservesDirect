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
require_once("secure/config.inc.php");
require_once("secure/common.inc.php");

require_once("secure/classes/users.class.php");

require_once("secure/interface/student.class.php");
require_once("secure/interface/custodian.class.php");
require_once("secure/interface/proxy.class.php");
require_once("secure/interface/instructor.class.php");
require_once("secure/interface/staff.class.php");
require_once("secure/interface/admin.class.php");

include("secure/skins_config.inc");
include("secure/session.inc.php");

// we will do our own error handling
if (isset($_SESSION['debug']))
{
	error_reporting(E_ALL);
} else {
	error_reporting(0);
	$old_error_handler = set_error_handler("common_ErrorHandler");
}


$userName = $_SESSION['username'];
$userClass = $_SESSION['userClass'];

//print_r ($_REQUEST);echo "<hr>";

//init user based on type
$usersObject = new users();
$u = $usersObject->initUser($userClass, $userName);
require_once('secure/auth.inc.php');

$adminUser = null;
{	
{
	 $tmpUser = new user;
	 

	 

	 unset($tmpUser);
}

if (isset($_REQUEST['ci']))
if (!empty($_REQUEST['ci']))
{
	$ci = new courseInstance($_REQUEST['ci']);
} else 
} else

//set cmd default to viewCourseList
$cmd = (isset($_REQUEST['cmd'])) ? $_REQUEST['cmd'] : 'viewCourseList';
	$ci = null;


//Force user to update email address
if (($u->getEmail() == "" || $u->getLastName() == "") && $cmd!="storeUser") //direct user to edit profile
	 $cmd = 'editProfile';
	 $cmd = 'newProfile';
}
}
switch ($cmd) 
switch ($cmd)
	case 'myReserves':
	case 'viewCourseList':  // myReserves Course List
{
	case 'viewReservesList': // myReserves Reserve List
	case 'previewStudentView';
	case 'sortReserves':
	case 'selectInstructor': //addReserve Staff Interface - Search for Class by Instructor or Dept				
	case 'addReserve': //Proxy & Faculty Interface - add a reserve to a class
	case 'displaySearchItemMenu': //addReserve - How would you like to put item on reserve? screen
	case 'addReserve': //add a reserve to a class
	case 'searchScreen': //addReserve - Search for Item
	case 'searchResults': //addReserve - Search Results Screen
	case 'storeReserve': //addReserve - Store Reserves Screen
	case 'uploadDocument': //addReserve - upload Document Screen
	case 'addURL': //addReserve - add a URL screen
	case 'storeUploaded': //addReserve page - Store uploaded document
	case 'faxReserve': //addReserve - Fax Reserve Screen
	case 'getFax': //addReserve - Claim Fax Screen
	case 'addFaxMetadata': //addReserve - Fax Meta Data Screen
	case 'addStudent': //myReserves - give a user student access to class
	case 'removeStudent': //myReserves - remove a students access to a class
		require_once("secure/managers/reservesManager.class.php");		
		require_once("secure/managers/reservesManager.class.php");
		$mgr = new reservesManager($cmd, $u);
	
	case 'addReserve':
	
	case 'deactivateClass':
	case 'manageClasses':
	case 'editProxies':
	case 'editInstructors':
	case 'editCrossListings':
	case 'editTitle':
	case 'reactivateClass':		// manageClass choose class to reactivate
	case 'reactivateList':
	case 'editClass':			// manageClass edit class
	case 'createClass':			// manageClass create class (enter meta-data)
	case 'reactivate':			// managerClass reactivate class
	case 'searchForClass':		// myReserves - search for a class by Instructor or Dept
	case 'createNewClass':		// manageClass create class (store meta-data to DB)
	case 'removeClass':			// myReserves - remove a class you are a student in		
		require_once("secure/managers/classManager.class.php");		
		require_once("secure/managers/classManager.class.php");
		$mgr = new classManager($cmd, $u, $adminUser, $_REQUEST);	
		$mgr = new classManager($cmd, $u, $adminUser, $_REQUEST);
	

	case 'newProfile':
	case 'editProfile':
	case 'storeUser':
	case 'mergeUsers':
	case 'addUser':
	case 'assignProxy':
	case 'assignInstr':
	case 'setPwd':
	case 'resetPwd':
	case 'removePwd':
	case 'addProxy':
		require_once("secure/managers/userManager.class.php");		
		$mgr = new userManager($cmd, $u, $adminUser);	
		$mgr = new userManager($cmd, $u, $adminUser);
	

	case 'duplicateReserve';
		require_once("secure/managers/itemManager.class.php");
	break;
	

	case 'processRequest':	
	case 'processRequest':
	case 'storeRequest':
	case 'printRequest':
		require_once("secure/managers/requestManager.class.php");
	break;	
	

	case 'addDigitalItem':
		if (!isset($_REQUEST['ci']) || !isset($_REQUEST['selected_instr']))
		if (!isset($_REQUEST['ci']))
			//display selectClass
			require_once("secure/managers/selectClassManager.class.php");
			$mgr = new selectClassManager('lookupClass', $cmd, 'manageClass', 'Select Class', $u, $_REQUEST);
			$mgr->lookup('lookupClass', $cmd, 'addReserve', 'Select Class');
		} else {
			//add Physical Item
			require_once("secure/managers/requestManager.class.php");
			$mgr = new requestManager($cmd, $u, $ci, $_REQUEST);
		}
	
	case 'staffEditClass':
		if (!isset($_REQUEST['ci']) || !isset($_REQUEST['selected_instr']))
		{
			//display selectClass
			require_once("secure/managers/selectClassManager.class.php");			
			$mgr = new selectClassManager('lookupClass', $cmd, 'manageClass', 'Edit Class', $u, $_REQUEST);
		} else {
			require_once("secure/managers/classManager.class.php");					
			$mgr = new classManager('editClass', $u, $adminUser, $_REQUEST);
		}	
		$mgr = new copyClassManager($cmd, $u, $_REQUEST);
	

	case 'addNote':
	case 'saveNote':
		$mgr = new noteManager($cmd, $u, $_REQUEST['reserve_id']);
		$mgr = new noteManager($cmd, $u);
	

		if ($u->getDefaultRole() >= $g_permission['staff'] && (!isset($_REQUEST['ci']) || !isset($_REQUEST['selected_instr'])))
		{
			require_once("secure/managers/selectClassManager.class.php");			
			$mgr = new selectClassManager('lookupClass', $cmd, 'manageClass', 'Export Class', $u, $_REQUEST);
		} else {
			require_once("secure/managers/exportManager.class.php");
			$mgr = new exportManager($cmd, $u, $_REQUEST);		
		}
		$mgr = new exportManager($cmd);
	

	default:
		trigger_error("index.php cmd=$cmd case not defined", E_USER_ERROR);
}
if (isset($_REQUEST['no_control']))
if (isset($_REQUEST['no_control']) && $_REQUEST['no_control'] != 'false')
	include "secure/html/no_table.inc.html";
else
}
?>

