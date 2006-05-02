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
		global $g_permission, $page, $loc, $ci, $alertMsg, $u;
		
//echo "classManager($cmd, $user, $adminUser)<P>"; //classManager

		$this->displayClass = "classDisplayer";
		
		//set the page (tab)
		if($u->getRole() >= $g_permission['staff']) {
			$page = 'manageClasses';
		}
		else {
			$page = 'myReserves';
		}

		switch ($cmd)
		{
			case 'manageClasses':
				$loc  = "manage classes home";
				$this->displayFunction = 'displayStaffHome';
				$this->argList = array($user);
			break;
			
			case 'myReserves':
			case 'viewCourseList':
				$page = "myReserves";
				$loc  = "home";
				
				$ciList_instructor = array();
				$ciList_proxy = array();
				$ciList_student = array();
				
				//get editable CIs for proxy or better
				//use getDefaultRole (instead of getRole) to account for not-trained instructors
				if($user->getDefaultRole() >= $g_permission['proxy']) {
					//get current courses, or those that will start within a year
					//do not get expired courses
					$activation_date = date('Y-m-d', strtotime('+1 year'));
					$expiration_date = date('Y-m-d');
								
					//get CIs where user is an instructor
					$ciList_instructor = $user->fetchCourseInstances('instructor', $activation_date, $expiration_date);
					//get CIs where user is a proxy
					$ciList_proxy = $user->fetchCourseInstances('proxy', $activation_date, $expiration_date);					
				}
								
				//get viewable CIs for everyone
				$ciList_student = $user->getCourseInstances();

				$this->displayFunction = "displayCourseList";
				$this->argList = array($ciList_student, $ciList_instructor, $ciList_proxy);
			break;
			
	
			case 'addClass':
				$ci_id = !empty($_REQUEST['ci']) ? $_REQUEST['ci'] : null;
				$instructor_id = !empty($_REQUEST['selected_instr']) ? $_REQUEST['selected_instr'] : null;
				$department_id = !empty($_REQUEST['department']) ? $_REQUEST['department'] : null;
				
				if($ci_id) {	//we have a class
					$ci = new courseInstance($ci_id);
					$ci->getCourseForUser();
					
					if($ci->getEnrollment() == 'OPEN') {	//if class has open enrollment, automatically approve the student
						$user->joinClass($ci->course->getCourseAliasID(), 'APPROVED');
					}
					else {	//else, add the student as pending
						$user->joinClass($ci->course->getCourseAliasID(), 'PENDING');
					}
					
					//go to course listing
					classManager::classManager('viewCourseList', $user, $adminUser, null);
				}
				else {	//no class, find one
					if($user->getRole() >= $g_permission['staff']) {	//if staff, show ajax lookup
						$this->displayFunction = 'displaySelectClass';
						$this->argList = array('addClass', null, 'Select a class to add:');
					}
					elseif($instructor_id || $department_id) {	//searching by instructor or department
						//grab the courses			
						if($instructor_id) {	//search for class by instructor
							$course_instances = $user->getCourseInstancesByInstr($instructor_id);
						}
						elseif($department_id) {	//search by department
							$termsObj = new terms();	
							$current_term = $termsObj->getCurrentTerm();
							$usersObj = new users();
												
							$course_instances = $usersObj->searchForCI(null, $department_id, null, $current_term->getTermID());
						}
							
						//do not display courses with closed enrollment
						for($x=0; $x<sizeof($course_instances); $x++) {
							if($course_instances[$x]->getEnrollment() == 'CLOSED') {
								unset($course_instances[$x]);
							}
						}
						
						$msg = 'Select a class to add:';							
						$this->displayFunction = 'displaySelectClass';
						$this->argList = array('addClass', $course_instances, $msg);
					}
					else {	//need either instructor or dept					
						$this->displayFunction = 'displaySelectDept_Instr';
						$this->argList = array();
					}
				}
			break;
			
			case 'removeClass':
				if(!empty($_REQUEST['ci'])) {	//user selected class
					$ci = new courseInstance($_REQUEST['ci']);
					$ci->getCourseForUser();
					$user->leaveClass($ci->course->getCourseAliasID());
					
					//go to course listing
					classManager::classManager('viewCourseList', $user, $adminUser, null);
				}
				else {	//show class list
					$course_instances = array();
					
					//get all CIs in subarrays indexed by enrollment status
					$course_instance_arrays = $user->getCourseInstances();
					//cannot remove self from autofeed courses, so do not display those
					unset($course_instance_arrays['AUTOFEED']);
					//now merge all the remaining CIs					
					foreach($course_instance_arrays as $courses) {
						$course_instances = array_merge($course_instances, $courses);
					}
					
					$msg = 'Select a class to remove:';
						
					$this->displayFunction = 'displaySelectClass';
					$this->argList = array('removeClass', $course_instances, $msg);
				}				
			break;
	
			
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
				$ci->setStatus('AUTOFEED');
				
				//go to course listing
				classManager::classManager('viewCourseList', $u, $adminUser, null);
			break;	
			
			case 'editClass':
				$loc  = "edit class";
				
				if(empty($_REQUEST['ci'])) {	//get ci
					//get array of CIs (ignored for staff)
					$courses = $u->getCourseInstancesToEdit();					
					$this->displayFunction = 'displaySelectClass';					
					$this->argList = array($cmd, $courses, 'Select class to edit', $_REQUEST);
					
					break;
				}

				$ci = new courseInstance($_REQUEST['ci']);
				$reserves = (isset($_REQUEST['selected_reserves'])) ? $_REQUEST['selected_reserves'] : null;
				
				
				//move items to folder
				if(!empty($_REQUEST['heading_select']) && !empty($reserves)) {
					foreach($reserves as $r_id) {
						$reserve = new reserve($r_id);
											
						$reserve->setParent($_REQUEST['heading_select']);
						
						//try to insert into sort order
						$reserve->getItem();
						$reserve->insertIntoSortOrder($ci->getCourseInstanceID(), $reserve->item->getTitle(), $reserve->item->getAuthor(), $_REQUEST['heading_select']);
					}
				}

				//perform reserve list action
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
							break 2;	//break out of this switch AND the big switch
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
								
								//do not allow instructors to change status for a physical item
								$reserve->getItem();
								if(!$reserve->item->isPhysicalItem() || ($u->getRole() >= $g_permission['staff'])) {
									$reserve->setStatus('ACTIVE');
								}
							}
						break;

						case 'deactivateAll':
							foreach($reserves as $r) {
								$reserve = new reserve($r);
								
								//do not allow instructors to change status for a physical item
								$reserve->getItem();
								if(!$reserve->item->isPhysicalItem() || ($u->getRole() >= $g_permission['staff'])) {
									//Headings always have a status of active
									if (!$reserve->isHeading()) {
										$reserve->setStatus('INACTIVE');
									}
								}									
							}
						break;
					}
				}
					
				//perform other actions
				
				//update class dates
				if(isset($_REQUEST['updateClassDates'])) {
					//if not empty, set activation and expiration dates
					//try to convert dates to proper format
					if(!empty($_REQUEST['activation'])) {
						$ci->setActivationDate($_REQUEST['activation']);
					}
					if(!empty($_REQUEST['expiration'])) {
						$ci->setExpirationDate($_REQUEST['expiration']);
					}
				}
				//change enrollment type
				if(isset($_REQUEST['setEnrollment'])) {
					$ci->setEnrollment($_REQUEST['enrollment']);					
				}
				//add/remove/approve/deny enrollment for student
				if(isset($_REQUEST['rollAction'])) {
					$user->editClassRoll($ci->getCourseInstanceID(), $_REQUEST['student_id'], $_REQUEST['rollAction']);
				}
				
				//get the tab to show
				$tab = !empty($_REQUEST['tab']) ? $_REQUEST['tab'] : null;

				//show screen
				$this->displayFunction = 'displayEditClass';
				$this->argList = array($ci, $cmd, $tab);
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
							$user->leaveClass($c);
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

				$deptID = $ci->course->department->getDepartmentID();

				$this->displayFunction = 'displayEditTitle';
				$this->argList = array($ci, $deptID);
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

			case 'createClass':
				$loc = "create new class";

//				$msg = 'Create your class below.  You will have a chance to reactivate readings from previous courses on the next screen.';
				$this->displayFunction = 'displayCreateClass';
				$this->argList = array('createClass', null, null, $msg);
			break;

			case 'createNewClass':
				$loc = "create class";
			
				$t = new term($request['term']);
				
				//need a CI object
				$ci = new courseInstance(null);
								
				//attempt to create the course instance
				if($ci->createCourseInstance($request['department'], $request['course_number'], $request['course_name'], $request['section'], $t->getTermYear(), $t->getTermName())) {	//course created successfully, insert data
					$ci->addInstructor($ci->getPrimaryCourseAliasID(), $request['selected_instr']);
					$ci->setTerm($t->getTermName());
					$ci->setYear($t->getTermYear());
					$ci->setActivationDate($request['activation_date']);
					$ci->setExpirationDate($request['expiration_date']);
					$ci->setEnrollment($request['enrollment']);
					$ci->setStatus('ACTIVE');
					
					$new_ci = $ci->getCourseInstanceID();
					
					//course is now complete, decide what to do next
					$postproc_cmd = !empty($_REQUEST['postproc_cmd']) ? $_REQUEST['postproc_cmd'] : '';					
					switch($postproc_cmd) {
						case 'importClass':	//turn control over to a different manager
						case 'processCopyClass':
							require_once("secure/managers/copyClassManager.class.php");
							$_REQUEST['new_ci'] = $new_ci;
							copyClassManager::copyClassManager($postproc_cmd, $u, $_REQUEST);
						break;

						default:	//do not need to do any post-processing
							//show success screen
							$this->displayFunction = 'displayCreateSuccess';
							$this->argList = array($new_ci);
					}
				}
				else {	//could not create course -- the CI must be a duplicate
					//display duplicate info
					$this->displayFunction = 'displayDuplicateCourse';
					//make sure we go back to the previous screen
					$preproc_cmd = !empty($_REQUEST['preproc_cmd']) ? $_REQUEST['preproc_cmd'] : 'createClass';
					$_REQUEST['cmd'] = $preproc_cmd;
					$this->argList = array($ci, urlencode(serialize($_REQUEST)));
				}
			break;

			case 'deleteClass':
				$loc = "delete class";

				if ($user->getRole() >= $g_permission['staff'])
				{
					$this->displayFunction = 'displayDeleteClass';
					$this->argList = array($cmd, $user, $request);
				}
			break;

			case 'confirmDeleteClass':
				$loc = "confirm delete class";

				if (isset($request['ci']))
				{
					$courseInstance = new courseInstance($request['ci']);
					$courseInstance->getPrimaryCourse();
					$courseInstance->getInstructors();

					$this->displayFunction = 'displayConfirmDelete';
					$this->argList = array($courseInstance);
				}
			break;

			case 'deleteClassSuccess':
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
				$loc = "copy reserve items to another class";
				
				$class_list = $user->getCourseInstancesToEdit();
				$this->displayFunction = 'displaySelectClass';
				$this->argList = array('processCopyItems', $class_list, 'Select class to copy TO:', $request);
			break;
			
			case 'processCopyItems':
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