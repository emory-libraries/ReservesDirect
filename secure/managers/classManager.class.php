<?
/*******************************************************************************
classManager.class.php


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
require_once("secure/classes/users.class.php");
require_once("secure/displayers/classDisplayer.class.php");

class classManager
{
	public $user;
	public $displayClass;
	public $displayFunction;
	public $argList;

	function display()
	{
		//echo "attempting to call ". $this->displayClass ."->". $this->displayFunction ."<br>";

		if (is_callable(array($this->displayClass, $this->displayFunction)))
			call_user_func_array(array($this->displayClass, $this->displayFunction), $this->argList);

	}
	

	function classManager($cmd, $user, $adminUser, $request)
	{
		global $g_permission, $page, $loc, $ci, $alertMsg;
		
//echo "classManager($cmd, $user, $adminUser)<P>"; //classManager

		$this->displayClass = "classDisplayer";
		$page = 'manageclasses';

		switch ($cmd)
		{
			case 'manageClasses':
				$page = "manageClasses";

				if ($user->getRole() >= $g_permission['staff'])
				{
					$loc  = "manage classes home";

					$this->displayFunction = 'displayStaffHome';
					$this->argList = array($user);
				} else {
					$loc  = "manage classes home";

					$this->displayFunction = 'displayInstructorHome';
					$this->argList = "";
				}
			break;
			
			case 'myReserves':
			case 'viewCourseList':
				$page = "myReserves";
				$loc  = "home";
				
				//get editable CIs for proxy or better
				if($user->getRole() >= $g_permission['proxy']) {
					//get current courses, or those that will start within a year
					//do not get expired courses
					$activation_date = date('Y-m-d', strtotime('+1 year'));
					$expiration_date = date('Y-m-d');
				
					//get CIs where user is an instructor
					$ciList_instructor = $user->fetchCourseInstances('instructor', null, $activation_date, $expiration_date);
					//get CIs where user is a proxy
					$ciList_proxy = $user->fetchCourseInstances('proxy', null, $activation_date, $expiration_date);					
				}
				
				//get viewable CIs for everyone
				$ciList_student = $user->getCourseInstances();

				$this->displayFunction = "displayCourseList";
				$this->argList = array($ciList_student, $ciList_instructor, $ciList_proxy);
			break;
			
/*******************
	###### redo ##########
	
	
			case 'addClass':
				$page = "myReserves";
				
				$prof = $_REQUEST['selected_instr'];
				$dept = $_REQUEST['dept'];

				if ($prof) {
					$courseList = array ();
					
					$user->get Current Classes For($prof, $g_permission['instructor']);
					
					for ($i=0;$i<count($user->courseInstances);$i++)
					{
						$ci = $user->courseInstances[$i];						
						$ci->getCourses();
						$courseList = array_merge($courseList, $ci->courseList);
					}
					
					$searchParam = new instructor();
					$searchParam->getUserByID($prof);		
				} elseif ($dept) {
					$user->get Courses By Dept($dept);
					$courseList = $user->courseList;

					$searchParam = new department($dept);
				} else {
					$alertMsg = "You must choose either an Instructor Name or a Department";
					return;
				}

				$this->displayFunction = "displayAddClass";
				$this->argList = array($courseList, $searchParam);
			break;

			case 'removeClass':
				$page = "myReserves";

				if ($user->getRole() < $g_permission['proxy']) {
					$user->get Course Instances();
				} else {
					$user->get Current Classes For($user->getUserID());
				}
				for ($i=0;$i<count($user->courseInstances);$i++)
				{
					$ci = $user->courseInstances[$i];
					$ci->getCourseForUser($user->getUserID());  //load courses
				}

				$this->displayFunction = "displayRemoveClass";
				$this->argList = "";
			break;

*****************************/
			
			case 'activateClass':
				if(empty($_REQUEST['ci'])) {
					return false;
				}
				
				//just need to change the class status to active
				$ci = new courseInstance($_REQUEST['ci']);
				$ci->setStatus('ACTIVE');
				
				//show success screen
				$this->displayFunction = 'displayActivateSuccess';
				$this->argList = array($ci->getCourseInstanceID());			
			break;
			
			case 'deactivateClass':
				global $u, $adminUser;
				
				if(empty($_REQUEST['ci'])) {
					return false;
				}
				
				//just need to change the class status to active
				$ci = new courseInstance($_REQUEST['ci']);
				$ci->setStatus('INACTIVE');
				
				//go to course listing
				classManager::classManager('viewCourseList', $u, $adminUser, null);
			break;	
			
			case 'editClass':
				$page = "manageClasses";
				$loc  = "edit class";

				$ci = new courseInstance($_REQUEST['ci']);
				$reserves = (isset($_REQUEST['selected_reserves'])) ? $_REQUEST['selected_reserves'] : null;
				
				
				//move items to folder
				if(!empty($_REQUEST['heading_select']) && !empty($reserves)) {
					foreach($reserves as $r_id) {
						$reserve = new reserve($r_id);
						
						//setting parent_id to self breaks things
						if($_REQUEST['heading_select'] == $r_id) {
							continue;
						}						
						//handle 'null' parent
						$parent = ($_REQUEST['heading_select'] == 'root') ? null : $_REQUEST['heading_select'];
											
						$reserve->setParent($parent);
						
						//try to insert into sort order
						$reserve->getItem();
						$reserve->insertIntoSortOrder($ci->getCourseInstanceID(), $reserve->item->getTitle(), $reserve->item->getAuthor(), $parent);
					}
				}

				//perform other action
				if(isset($_REQUEST['reserveListAction']) && !empty($reserves)) {
					//make sure the action is performed on all the children too
					
					//get reserves data	as tree
					$tree = $ci->getReservesAsTree('getReserves');
					
					$tmp_res = array();
					foreach($reserves as $r_id) {
						//add the reserve
						if(!isset($tmp_res[$r_id])) {
							$tmp_res[$r_id] = $r_id;	//index by id to prevent duplicate values
							$walker = new treeWalker($tree->findDescendant($r_id));	//get the node with that ID
							foreach($walker as $leaf) {
								$tmp_res[$leaf->getID()] = $leaf->getID();	//add child to array
							}
						}
					}
					$reserves = $tmp_res;

					//atcion switch
					switch($_REQUEST['reserveListAction']) {
						case 'copyAll':
							classManager::classManager('copyItems', $user, $adminUser, array('originalClass'=>$_REQUEST['ci'], 'reservesArray'=>$reserves));
						break;
						
						case 'deleteAll':
							foreach($reserves as $r) {
								$reserve = new reserve($r);
								$reserve->getItem();
								if ($reserve->item->isPhysicalItem()) {
									$reqst = new request();
									$reqst->getRequestByReserveID($r);
									$reqst->destroy();
								}
								$reserve->destroy();
							}
						break;
						
						case 'activateAll':
							foreach($reserves as $r) {
								$reserve = new reserve($r);
								$reserve->setStatus('ACTIVE');
							}
						break;

						case 'deactivateAll':
							foreach($reserves as $r) {
								$reserve = new reserve($r);
								//Headings always have a status of active
								if (!$reserve->isHeading())
									$reserve->setStatus('INACTIVE');
							}
						break;
					}
				}
									
				if($_REQUEST['reserveListAction'] != 'copyAll') {
					if (isset($_REQUEST['updateClassDates'])) {
						//if not empty, set activation and expiration dates
						//try to convert dates to proper format
						if(!empty($_REQUEST['activation'])) {
							$ci->setActivationDate(date('Y-m-d', strtotime($_REQUEST['activation'])));
						}
						if(!empty($_REQUEST['expiration'])) {
							$ci->setExpirationDate(date('Y-m-d', strtotime($_REQUEST['expiration'])));
						}
					}
					$ci->getInstructors();
					$ci->getCrossListings();
					$ci->getProxies();
					$ci->getPrimaryCourse();
					$ci->course->getDepartment();
					
					//get reserves as a tree + recursive iterator
					$walker = $ci->getReservesAsTreeWalker('getReserves');

					//show screen
					$this->displayFunction = 'displayEditClass';
					$this->argList = array($cmd, $ci, $walker);
				}
			break;

			case 'editTitle':
			case 'editCrossListings':
				$loc ="edit title and crosslistings";
				$ci = new courseInstance($_REQUEST['ci']);

				if (isset($_REQUEST['deleteCrossListings']))
				{
					$courses = $_REQUEST['deleteCrossListing'];
					if (is_array($courses) && !empty($courses)){
						foreach($courses as $c)
						{
							$alertMsg = $user->removeCrossListing($c);
						}
					}
				}


				if (isset($_REQUEST['addCrossListing']))
				{

					$dept = $_REQUEST['newDept'];
					$courseNo = $_REQUEST['newCourseNo'];
					$section = $_REQUEST['newSection'];
					$courseName = $_REQUEST['newCourseName'];

					if ($dept==NULL || $courseNo==NULL || $courseName==NULL)	
						$alertMsg =	'Please supply a Department, Course#, Section, and Title before adding the Cross Listing.';
					else 
						$user->addCrossListing($ci, $dept, $courseNo, $section, $courseName);
					

				}

				if (isset($_REQUEST['updateCrossListing'])) {
					/* commented out by kawashi on 11.12.04 - No longer able to change primary course
					$oldPrimaryCourse = new course($_REQUEST['oldPrimaryCourse']);
					$oldPrimaryCourse->setDepartmentID($_REQUEST['primaryDept']);
					$oldPrimaryCourse->setCourseNo($_REQUEST['primaryCourseNo']);
					$oldPrimaryCourse->setSection($_REQUEST['primarySection']);
					$oldPrimaryCourse->setName($_REQUEST['primaryCourseName']);

					//Set New Primary Course
					$ci->setPrimaryCourseAliasID($_REQUEST['primaryCourse']);
					*/

					$primaryCourse = new course($_REQUEST['primaryCourse']);
					$primaryCourse->setDepartmentID($_REQUEST['primaryDept']);
					$primaryCourse->setCourseNo($_REQUEST['primaryCourseNo']);
					$primaryCourse->setSection($_REQUEST['primarySection']);
					$primaryCourse->setName($_REQUEST['primaryCourseName']);

					if ($_REQUEST['cross_listings'])
					{
						$cross_listings = array_keys($_REQUEST['cross_listings']);
						foreach ($cross_listings as $cross_listing)
						{
							$updateCourse = new course($cross_listing);
							$updateCourse->setDepartmentID($_REQUEST['cross_listings'][$cross_listing]['dept']);
							$updateCourse->setCourseNo($_REQUEST['cross_listings'][$cross_listing]['courseNo']);
							$updateCourse->setSection($_REQUEST['cross_listings'][$cross_listing]['section']);
							$updateCourse->setName($_REQUEST['cross_listings'][$cross_listing]['courseName']);
						}
					}
				}

				//$ci->getCourseForInstructor($user->getUserID());
				$ci->getPrimaryCourse();
				$ci->course->getDepartment();   //$this->department = new department($this->deptID);
				$ci->getCrossListings();

				$deptList = $ci->course->department->getAllDepartments();
				$deptID = $ci->course->department->getDepartmentID();

				$this->displayFunction = 'displayEditTitle';
				$this->argList = array($ci, $deptList, $deptID);
			break;

			case 'editInstructors':
				$loc = "edit instructors";
				$ci = new courseInstance($_REQUEST['ci']);
				$ci->getCrossListings();  //load cross listings 

				if ($_REQUEST['addInstructor']) {
					$ci->addInstructor($ci->primaryCourseAliasID,$_REQUEST['selected_instr']); //Add instructor to primary course alias
					for ($i=0; $i<count($ci->crossListings); $i++) {
						$ci->addInstructor($ci->crossListings[$i]->courseAliasID, $_REQUEST['selected_instr']); // add instructor to the Xlistings
					}		
					
				}

				if ($_REQUEST['removeInstructor']) {
					//Problem - Should there be a stipulation that you can't remove the last instructor?
					$instructors = $_REQUEST['Instructor'];

					if (is_array($instructors) && !empty($instructors)){
						foreach($instructors as $instructorID)
						{
							$ci->removeInstructor($ci->primaryCourseAliasID,$instructorID); //remove instructor from primary course alias
							for ($i=0; $i<count($ci->crossListings); $i++) {
								$ci->removeInstructor($ci->crossListings[$i]->courseAliasID, $instructorID); // remove instructor from the Xlistings
							}
						}
					}
				}

				$ci->getInstructors(); //load current instructors
				//$ci->getCourseForInstructor($user->getUserID());
				$ci->getPrimaryCourse();
				//$instructorList = common_getUsers('instructor'); //get instructors to populate drop down box				
				$this->displayFunction = 'displayEditInstructors';
				$this->argList = array($ci, 'ADD AN INSTRUCTOR', 'Choose an Instructor', 'Instructor', 'CURRENT INSTRUCTORS', 'Remove Selected Instructors', $request);
			break;

			case 'editProxies':
				$loc = "edit proxies";
				$ci = new courseInstance($_REQUEST['ci']);
				$ci->getCrossListings();  //load cross listings

				if (isset($_REQUEST['addProxy'])) {
					$user->makeProxy($_REQUEST['proxy'],$ci->courseInstanceID);
					/*
					$ci->addProxy($ci->primaryCourseAliasID,$_REQUEST['prof']); //Add proxy to primary course alias
					for ($i=0; $i<count($ci->crossListings); $i++) {
						$ci->addProxy($ci->crossListings[$i]->courseAliasID, $_REQUEST['prof']); // add proxy to the Xlistings
					}
					*/
				}

				if (isset($_REQUEST['removeProxy'])) {
					$proxies = $_REQUEST['proxies'];

					if (is_array($proxies) && !empty($proxies)){
						foreach($proxies as $proxyID)
						{
							$user->removeProxy($proxyID, $ci->courseInstanceID);
							/*
							$ci->removeProxy($ci->primaryCourseAliasID,$proxyID); //remove proxy from primary course alias
							for ($i=0; $i<count($ci->crossListings); $i++) {
								$ci->removeProxy($ci->crossListings[$i]->courseAliasID, $proxyID); // remove proxy from the Xlistings
							}
							*/
						}
					}
				}

				$ci->getProxies(); //load current proxies
				$ci->getPrimaryCourse();

				if (isset($_REQUEST['queryText']) &&  $_REQUEST['queryText'] != "")
				{
					$usersObj = new users();
					$usersObj->search($_REQUEST['queryTerm'], $_REQUEST['queryText']);  //populate userList
				}

				//$ci->getCourseForInstructor($user->getUserID());
				//$instructorList = common_getUsers('proxy'); //get instructors to populate drop down box

				$this->displayFunction = 'displayEditProxies';
				$this->argList = array($ci, $usersObj->userList, $_REQUEST);
			break;

			case 'searchForClass':
				$page = "myReserves";
				//$instructorList = common_getUsers('instructor');
				$deptList = common_getDepartments();

				$this->displayFunction = "displaySearchForClass";
				$this->argList = array($deptList, $request);
			break;


			case 'viewEnrollment':
				$page = "manageClasses";
				$loc = "enrolled students";
				
				if(!empty($_REQUEST['ci'])) {	//if already looked up a class
					$ci = new courseInstance($_REQUEST['ci']);
					$ci->getStudents();
				}

				$this->displayFunction = 'displayClassEnrollment';
				$this->argList = array($ci);
			break;

			case 'createClass':
				$page = "manageClasses";
				$loc = "create new class";

				$dept = new department();
				$terms = new terms();

				$this->displayFunction = 'displayCreateClass';
				$this->argList = array('createNewClass', array('cmd'=>'createClass'));
			break;

			case 'createNewClass':
				$page = "manageClasses";
				$loc = "create class";
			
				$t = new term($request['term']);
				
				//need a CI object
				$ci = new courseInstance(null);
								
				//attempt to create the course instance
				if($ci->createCourseInstance($request['department'], $request['course_number'], $request['course_name'], $request['section'], $t->getTermYear(), $t->getTermName())) {	//course created successfully, insert data
					$ci->addInstructor($ci->getPrimaryCourseAliasID(), $request['selected_instr']);
					$ci->setTerm($t->getTermName());
					$ci->setYear($t->getTermYear());
					$ci->setActivationDate(date('Y-m-d', strtotime($request['activation_date'])));
					$ci->setExpirationDate(date('Y-m-d', strtotime($request['expiration_date'])));
					$ci->setEnrollment($request['enrollment']);
					$ci->setStatus('ACTIVE');
					
					//show success screen
					$this->displayFunction = 'displayCreateSuccess';
					$this->argList = array($ci->getCourseInstanceID());
				}
				else {	//could not create course -- the CI must be a duplicate
					//display duplicate info
					$this->displayFunction = 'displayDuplicateCourse';
					$_REQUEST['cmd'] = 'createClass';	//make sure we go back to the previous screen
					$this->argList = array($ci, urlencode(serialize($_REQUEST)));
				}
			break;

			case 'deleteClass':
				$page = "manageClasses";
				$loc = "delete class";

				if ($user->getRole() >= $g_permission['staff'])
				{
					$this->displayFunction = 'displayDeleteClass';
					$this->argList = array($cmd, $user, $request);
				}
			break;

			case 'confirmDeleteClass':
				$page = "manageClasses";
				$loc = "confirm delete class";

				if (isset($request['ci']))
				{
					$courseInstance = new courseInstance($request['ci']);
					$courseInstance->getPrimaryCourse();
					$courseInstance->getStudents();
					$courseInstance->getInstructors();

					$this->displayFunction = 'displayConfirmDelete';
					$this->argList = array($courseInstance);
				}
			break;

			case 'deleteClassSuccess':
				$page = "manageClasses";
				$loc = "delete class";

				if (isset($request['ci']))
				{
					$courseInstance = new courseInstance($request['ci']);
					$courseInstance->getPrimaryCourse();
					$courseInstance->destroy();

					$this->displayFunction = 'displayDeleteSuccess';
					$this->argList = array($courseInstance);
				}
			break;
			
			case 'copyItems':
				$page = "manageClasses";
				$loc = "copy reserve items to another class";
				
				$class_list = $user->getCourseInstancesToEdit();
				$this->displayFunction = 'displaySelectClass';
				$this->argList = array('processCopyItems', $class_list, 'Select class to copy TO:', $request);
			break;
			
			case 'processCopyItems':
				$page = "manageClasses";
				$loc = "copy reserve items to another class";

				$srcCI = new courseInstance($_REQUEST['originalClass']);
				//copy reserves
				if(is_array($_REQUEST['reservesArray']) && !empty($_REQUEST['reservesArray'])) {
					$srcCI->copyReserves($_REQUEST['ci'], $_REQUEST['reservesArray']);
				}
				
				$dstCI = new courseInstance($_REQUEST['ci']);
				$srcCI->getPrimaryCourse();
				$dstCI->getPrimaryCourse();
			
				$this->displayFunction = 'displayCopyItemsSuccess';
				$this->argList = array($dstCI,$srcCI,count($_REQUEST['reservesArray']));
			break;
				
		}	
	}
}

?>