<?
/*******************************************************************************
index.php
primary processing and display page

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
$load_start_time = time();
require_once("secure/config.inc.php");
require_once("secure/common.inc.php");

require_once("secure/classes/users.class.php");
require_once("secure/classes/skins.class.php");

require_once("secure/interface/student.class.php");
require_once("secure/interface/custodian.class.php");
require_once("secure/interface/proxy.class.php");
require_once("secure/interface/instructor.class.php");
require_once("secure/interface/staff.class.php");
require_once("secure/interface/admin.class.php");

include("secure/session.inc.php");

// we will do our own error handling
if (isset($_SESSION['debug']))
{
	error_reporting(E_ALL);
	print_r($_REQUEST);echo "<hr>";
} else {
	error_reporting(0);
	$old_error_handler = set_error_handler("common_ErrorHandler");
}


$userName = $_SESSION['username'];
$userClass = $_SESSION['userClass'];

//init user based on type
$usersObject = new users();
$u = $usersObject->initUser($userClass, $userName);
require_once('secure/auth.inc.php');

$adminUser = null;
if (isset($_REQUEST['u']))
{
	 $tmpUser = new user;
	 $tmpUser->getUserByID($_REQUEST['u']);

	 $adminUser = $usersObject->initUser($tmpUser->getUserClass(), $tmpUser->getUsername());

	 unset($tmpUser);
}

if (isset($_REQUEST['ci']))
if (!empty($_REQUEST['ci']))
{
	$ci = new courseInstance($_REQUEST['ci']);
	$ci->getPrimaryCourse();
} else

//set cmd default to viewCourseList
$cmd = (isset($_REQUEST['cmd'])) ? $_REQUEST['cmd'] : 'viewCourseList';
	$ci = null;


//Force user to update email address
if (($u->getEmail() == "" || $u->getLastName() == "") && $cmd!="storeUser") //direct user to edit profile
{
	 $cmd = 'newProfile';
}
}

switch ($cmd)
	case 'myReserves':
	case 'viewCourseList':  // myReserves Course List
{
	case 'viewReservesList': // myReserves Reserve List
	case 'previewReservesList':
	case 'previewStudentView';
	case 'sortReserves':
	case 'customSort':
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
	case 'editMultipleReserves':	//edit common reserve data for multiple reserves in a class
		require_once("secure/managers/reservesManager.class.php");
		$mgr = new reservesManager($cmd, $u);
	break;
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
	case 'addClass':			// myReserves - add a class as a student
	case 'removeClass':			// myReserves - remove a class you are a student in
	case 'deleteClass':
	case 'confirmDeleteClass':
	case 'viewEnrollment':		//manageClass - display enrolled students
	case 'processViewEnrollment':
	case 'deleteClassSuccess':
	case 'copyItems':
		require_once("secure/managers/classManager.class.php");		
		require_once("secure/managers/classManager.class.php");
		$request = $_REQUEST;
		$mgr = new classManager($cmd, $u, $adminUser, $_REQUEST);
	break;

	case 'manageUser':
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
	case 'removeProxy':
		require_once("secure/managers/userManager.class.php");
		$mgr = new userManager($cmd, $u, $adminUser);
	break;

	case 'editItem':
	case 'editHeading':
	case 'editReserve':
	case 'duplicateReserve';
		require_once("secure/managers/itemManager.class.php");
		$mgr = new itemManager($cmd, $u);
	
	case 'checkDuplicateClass':
	case 'checkDuplicateReactivation':
		require_once("secure/managers/checkDuplicatesManager.class.php");
		$mgr = new checkDuplicatesManager($cmd,$u);
	break;


	case 'displayRequest':
	case 'processRequest':
	case 'storeRequest':
	case 'printRequest':
		require_once("secure/managers/requestManager.class.php");
		$mgr = new requestManager($cmd, $u, $ci, $_REQUEST);
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
	break;
	break;

	case 'clearCopyClassLookup':
	case 'copyClassOptions':
	case 'copyExisting':
	case 'importClass':			//import reserves list from one ci to another
	case 'processCopyClass':
		require_once("secure/managers/copyClassManager.class.php");
		$mgr = new copyClassManager($cmd, $u, $_REQUEST);
	break;

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
	break;

	case 'searchTab':
	case 'doSearch':
	case 'addResultsToClass':
		require_once("secure/managers/searchManager.class.php");
		$mgr = new searchManager($cmd, $u, $_REQUEST);
	

	default:
		trigger_error("index.php cmd=$cmd case not defined", E_USER_ERROR);
}
if (isset($_REQUEST['no_control']))
if (isset($_REQUEST['no_control']) && $_REQUEST['no_control'] != 'false')
	include "secure/html/no_table.inc.html";
else
	include "secure/html/index.inc.html";

if (isset($_SESSION['debug']))
{
	$load_end_time = time();
	$load_time = $load_end_time - $load_start_time;
	echo "<br>this page took $load_time s to load";
}
?>

