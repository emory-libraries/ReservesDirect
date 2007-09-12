<?
/*******************************************************************************
index.php
primary processing and display page

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

// workaround for workaround for ie's idiotic caching policy handling
header("Cache-Control: no-cache");
header("Pragma: no-cache");

$load_start_time = time();
require_once("secure/config.inc.php");
require_once("secure/common.inc.php");

require_once("secure/classes/calendar.class.php");
require_once("secure/classes/users.class.php");
require_once("secure/classes/skins.class.php");
require_once("secure/classes/news.class.php");

require_once("secure/interface/student.class.php");
require_once("secure/interface/custodian.class.php");
require_once("secure/interface/proxy.class.php");
require_once("secure/interface/instructor.class.php");
require_once("secure/interface/staff.class.php");
require_once("secure/interface/admin.class.php");

require_once("secure/managers/ajaxManager.class.php");
require_once("secure/managers/noteManager.class.php");

require_once("secure/functional_permissions.inc.php");

//set up error-handling/debugging, skins, etc.
require_once("secure/session.inc.php");

//authenticate user
//if user is valid, then initializes global user object as $u
//else shows login page

//*******************************************************
//$_SESSION['debug'] = 'debug';
//$_SESSION['username'] = 'jbwhite';
//$_SESSION['userclass'] = 'admin';
//*******************************************************

require_once('secure/auth.inc.php');

//read cmd
$cmd = $_REQUEST['cmd'];

//test user permissions
if (!key_exists($cmd, $functional_permissions) || ($u->getRole() < $functional_permissions[$cmd])) //user does not have permission kick to default page
{
	$cmd = "viewCourseList";
	if ($_SESSION['debug'])
		echo "<B>Failed Permission check</B><hr>\n";
}

$adminUser = null;
if (isset($_REQUEST['u']))
{
	 $tmpUser = new user;
	 $tmpUser->getUserByID($_REQUEST['u']);

	 $adminUser = $usersObject->initUser($tmpUser->getUserClass(), $tmpUser->getUsername());

	 unset($tmpUser);
}

//if selected set CourseInstance
if (!empty($_REQUEST['ci']))
{
	$ci = new courseInstance($_REQUEST['ci']);
	$ci->getPrimaryCourse();
} else
	$ci = null;


//Force user to update email address
if (($u->getEmail() == "" || $u->getLastName() == "") && !isset($_REQUEST['edit_user_submit'])) //direct user to edit profile
{
	 $cmd = 'newProfile';
}

//initiate calendar object, since some files must be included in the <head> by one of the html includes
//this object should be global and used by all files (no need to create a new obj)
$calendar = new Calendar();


//if there is a command to delete a note, do it
if(!empty($_REQUEST['deleteNote'])) {
	noteManager::deleteNote($_REQUEST['deleteNote']);
}

if ($_SESSION['debug'])
	echo "cmd=$cmd<hr>";

switch ($cmd)
{
	case 'viewReservesList': // myReserves Reserve List
	case 'previewReservesList':
	case 'previewStudentView';
	case 'sortReserves':
	case 'customSort':
	case 'selectInstructor': //addReserve Staff Interface - Search for Class by Instructor or Dept
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
	case 'storeFaxMetadata': //addReserve - Store Fax Meta Data Screen
	case 'editMultipleReserves':	//edit common reserve data for multiple reserves in a class
		require_once("secure/managers/reservesManager.class.php");
		$mgr = new reservesManager($cmd, $u);
	break;


	case 'myReserves':
	case 'viewCourseList':
	case 'activateClass':
	case 'deactivateClass':
	case 'manageClasses':
	case 'editProxies':
	case 'editInstructors':
	case 'editCrossListings':
	case 'editTitle':
	case 'editClass':			// manageClass edit class
	case 'createClass':			// manageClass create class (enter meta-data)
	case 'createNewClass':		// manageClass create class (store meta-data to DB)
	case 'addClass':			// myReserves - add a class as a student
	case 'removeClass':			// myReserves - remove a class you are a student in
	case 'deleteClass':
	case 'confirmDeleteClass':
	case 'deleteClassSuccess':
	case 'copyItems':
	case 'processCopyItems':
		require_once("secure/managers/classManager.class.php");
		$request = $_REQUEST;
		$mgr = new classManager($cmd, $u, $adminUser, $_REQUEST);
	break;

	case 'manageUser':
	case 'newProfile':
	case 'editProfile':
	case 'editUser':
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
	case 'processHeading':
	case 'duplicateReserve';
		require_once("secure/managers/itemManager.class.php");
		$mgr = new itemManager($cmd, $u);
	break;

	case 'displayRequest':
	case 'storeRequest':
	case 'deleteRequest':
	case 'printRequest':
	case 'addDigitalItem':
	case 'addPhysicalItem':
		require_once("secure/managers/requestManager.class.php");
		$mgr = new requestManager($cmd, $u, $ci, $_REQUEST);
	break;

	case 'copyClass':
	case 'copyClassOptions':
	case 'copyExisting':
	case 'copyNew':
	case 'importClass':			//import reserves list from one ci to another
	case 'processCopyClass':
		require_once("secure/managers/copyClassManager.class.php");
		$mgr = new copyClassManager($cmd, $u, $_REQUEST);
	break;

	case 'addNote':
	case 'saveNote':
		require_once("secure/managers/noteManager.class.php");
		$mgr = new noteManager($cmd, $u);
	break;

	case 'exportClass':
	case 'generateBB':
		require_once("secure/managers/exportManager.class.php");
		$mgr = new exportManager($cmd);
	break;

	case 'searchTab':
	case 'doSearch':
	case 'addResultsToClass':
		require_once("secure/managers/searchManager.class.php");
		$mgr = new searchManager($cmd, $u, $_REQUEST);
	break;

	case 'reportsTab':
	case 'viewReport':
		$page = "reports";
		require_once("secure/managers/reportManager.class.php");
		$mgr = new reportManager($cmd, $u, $_REQUEST);
	break;

	case 'admin':
		$page = 'admin';
		require_once("secure/managers/adminManager.class.php");
		$mgr = new adminManager($cmd, $u, $_REQUEST);
	break;

	case 'help':
	case 'helpViewArticle':
	case 'helpEditArticle':
	case 'helpViewCategory':
	case 'helpEditCategory':
	case 'helpViewTag':
	case 'helpSearch':	
	case 'helpSetRelated':
		$page = 'help';
		require_once('secure/managers/helpManager.class.php');
		$mgr = new helpManager($cmd);
	break;

	default:
		trigger_error("index.php cmd=$cmd case not defined", E_USER_ERROR);
}

if (isset($_REQUEST['no_control']) && $_REQUEST['no_control'] != 'false')
	include "secure/html/no_control.inc.html";
elseif (isset($_REQUEST['no_table']) && $_REQUEST['no_table'] != 'false')
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
