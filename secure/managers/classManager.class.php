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

			case 'reactivateClass':
				$page = "manageClasses";
				
				if($user->getRole() == $g_permission['instructor']) {
					$instructors 		= null;
					$courses 			= null;
					$courseInstances 	= $user->getAllCourseInstances(true);
					for($i=0;$i<count($courseInstances);$i++) { $courseInstances[$i]->getPrimaryCourse(); }
				}
				
				$loc = 'reactivate class';
				$this->displayFunction = 'displayReactivate';
				$this->argList = array($courses, $courseInstances, 'reactivateConfirm', $_REQUEST, array('cmd'=>$cmd));
			break;
			
			
			case 'reactivateConfirm':	//show destination course confirmation form
				$page = "manageClasses";
				
				//pull all the necessary info
				$ci->getPrimaryCourse();
				$ci->course->getDepartment();
				$terms = new terms();
				$dep = new department();
				
				$loc = 'reactivate class &gt;&gt; confirm reactivation';
				$this->displayFunction = 'displayReactivateConfirm';
				$this->argList = array($ci, $terms->getTerms(), $dep->getAllDepartments(), array('cmd'=>'reactivateList', 'ci'=>$ci->getCourseInstanceID(), 'course'=>$ci->course->getCourseID(), 'state'=>urlencode(serialize($_REQUEST))));			
			break;	//reactivateConfirm
			

			case 'reactivateList':
				//pull the necessary info into objects
				$ci->getPrimaryCourse();
				$ci->course->getDepartment();
				$term = new term($_REQUEST['term']);
				
				//first, determine if they wish to use the existing course as-is, or edit it
				if($_REQUEST['keep_selection'] == 'yes') {	//keeping old
					//the section is the only part that could have changed
					$section = $_REQUEST['section'];
					//the rest stays the same
					$dept_id = $ci->course->department->getDepartmentID();
					$course_number = $ci->course->getCourseNo();
					$course_name = $ci->course->getName();
				}
				elseif($_REQUEST['keep_selection'] == 'no') {	//they chose to reactivate with edited course info
					$dept_id = $_REQUEST['department'];
					$course_number = $_REQUEST['course_number'];
					$course_name = $_REQUEST['course_name'];
					$section = $_REQUEST['section2'];									
				}
		
				$term = new term($_REQUEST['term']);
				
				//then, check to see if resulting class has a duplicate active class
				//do not truly care about instructor/s, because we are looking for active course matches, no matter who teaches them
				$dupes = classManager::getDuplicates($dept_id, $course_number, $section, $term->getTermYear(), $term->getTermName());

				if(!is_null($dupes[0])) {	//found an active dupe
					//show duplicate course
					$this->displayFunction = 'displayDuplicateCourses';
					$this->argList = array($user, $dupes, $_REQUEST['state']);
				}
				else {	//no duplicate found, we can go on w/ reactivation
					//did they want to edit course info?
					if($_REQUEST['keep_selection'] == 'yes') {	//keeping old course
						//pass course_id
						$course_id = $_REQUEST['course'];
					}
					else {	//we are editing course
						//set course_id to null; the rest of the info gets carried
						$course_id = null;
					}
					
					$ci->getInstructors();
					$ci->getProxies();					
					$ci->getCrossListings();	
					$ci->course->getDepartment();
					$loan_periods = $ci->course->department->getInstructorLoanPeriods();
					
					$instructorList = null;
					if ($user->getRole() >= $g_permission['staff'])
					{
						$usersObj = new users();
						$instructorList = $usersObj->getUsersByRole('instructor');
					}
					
					//get reserves as a tree + recursive iterator
					$walker = $ci->getReservesAsTreeWalker('getReserves');
	
					$loc = 'reactivate class &gt;&gt; reserves list';
					$this->displayFunction = 'displaySelectReservesToReactivate';
					$this->argList = array($ci, $walker, $instructorList, array('cmd'=>'reactivate', 'course'=>$course_id, 'course_number'=>$course_number, 'dept_id'=>$dept_id, 'course_name'=>$course_name, 'section'=>$section, 'ci'=>$_REQUEST['ci'], 'term'=>$_REQUEST['term']), $loan_periods);				}

			break;

			case 'reactivate':
				$page = "manageClasses";
				$term = new term($_REQUEST['term']);
				$srcCI = new courseInstance($_REQUEST['ci']);
				$srcCI->getPrimaryCourse();
				$srcCI->getProxies();
				
				//handle course reuse/creation
				
				$srcCourse = new course();
				
				//check to see if reusing old course or creating new
				if(!empty($_REQUEST['course'])) {	//we found a course id, use it
					$srcCourse->getCourseByID($_REQUEST['course']);
				}
				else {	//course id is null, get one by dept course# and name
					if(is_null($srcCourse->getCourseByMatch($_REQUEST['dept_id'], $_REQUEST['course_number'], $_REQUEST['course_name']))) {	//did not find a course matching criteria
						//must make new
						$srcCourse->createNewCourse($srcCI->getCourseInstanceID());
						$srcCourse->setCourseNo($_REQUEST['course_number']);
						$srcCourse->setDepartmentID($_REQUEST['dept_id']);
						$srcCourse->setName($_REQUEST['course_name']);
					}
					//else course info is set
				}
			
				$instructorList = $_REQUEST['carryInstructor'];
				if (isset($_REQUEST['additionalInstructor']) && $_REQUEST['additionalInstructor'] != "") array_push($instructorList, $_REQUEST['additionalInstructor']);
//				$proxyList = isset($_REQUEST['carryProxy']) ? $_REQUEST['carryProxy'] : null;
				$carryXListing = (isset($_REQUEST['carryCrossListing'])) ? $_REQUEST['carryCrossListing'] : null;
				$carryReserves = (isset($_REQUEST['selected_reserves'])) ? $_REQUEST['selected_reserves'] : null;
				$requested_loan_periods = (isset($_REQUEST['requestedLoanPeriod'])) ? $_REQUEST['requestedLoanPeriod'] : null;
				
				$status = "ACTIVE";
				
				//set dates
				$begin_date = !empty($_REQUEST['course_activation_date']) ? date('Y-m-d', strtotime($_REQUEST['course_activation_date'])) : $term->getBeginDate();
				$end_date = !empty($_REQUEST['course_expiration_date']) ? date('Y-m-d', strtotime($_REQUEST['course_expiration_date'])) : $term->getEndDate();
				
				//create new class
				$newCI = $user->copyCourseInstance($srcCI, $srcCourse->getCourseID(), $_REQUEST['section'], $term->getTermName(), $term->getTermYear(), $begin_date, $end_date, $status, $instructorList, $proxyList, $carryXListing, $carryReserves, $requested_loan_periods);
				
				$this->displayFunction = 'displaySuccess';
				$this->argList = array($page, $newCI);
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

			case 'selectClass':
				if ($user->getRole() >= $g_permission['staff']) {
					$courseInstances = $user->getCourseInstances($adminUser->getUserID());
				} elseif ($user->getRole() >= $g_permission['proxy']) { //2 = proxy
					$courseInstances = $user->getCourseInstances();
				} else {
					trigger_error("Permission Denied:  Cannot add reserves. UserID=".$u->getUserID(), E_ERROR);
				}

				for($i=0;$i<count($courseInstances); $i++)
				{
					$ci = $courseInstances[$i];
					$ci->getCourseForUser($user->getUserID());
				}

				$this->displayFunction = 'displaySelectClasses';
				$this->argList = array($courseInstances);
			break;

			case 'selectInstructor':
				echo "select Instructor<BR>";
				$this->displayFunction = 'displaySelectInstructor';
				$this->argList = array($user);

				echo $this->displayFunction . " " . $this->argList . "<HR>";
			break;

			case 'addReserve':
				
				if ($user->getRole() >= $g_permission['staff']) {
					$loc = "add a reserve";
					if (is_null($adminUser))
					{
					 	$this->classManager("selectInstructor", $user, null);
					 	break;
					}
					else
						
						$courseInstances = $adminUser->getCourseInstances();

				} elseif ($user->getRole() >= $g_permission['proxy']) { //2 = proxy
					$courseInstances = $user->getCourseInstances();
				} else {
					trigger_error("Permission Denied:  Cannot add reserves. UserID=".$u->getUserID(), E_ERROR);
				}

				for($i=0;$i<count($courseInstances); $i++)
				{
					$ci = $courseInstances[$i];
					$ci->getCourseForUser($user->getUserID());
				}

				$this->displayFunction = 'displaySelectClasses';
				$this->argList = array($courseInstances);
			break;

			case 'searchForClass':
				$page = "myReserves";
				//$instructorList = common_getUsers('instructor');
				$deptList = common_getDepartments();

				$this->displayFunction = "displaySearchForClass";
				$this->argList = array($deptList, $request);
			break;

			case 'addClass':
				$page = "myReserves";
				
				$prof = $_REQUEST['selected_instr'];
				$dept = $_REQUEST['dept'];

				if ($prof) {
					$courseList = array ();
					
					$user->getCurrentClassesFor($prof, $g_permission['instructor']);
					
					for ($i=0;$i<count($user->courseInstances);$i++)
					{
						$ci = $user->courseInstances[$i];						
						$ci->getCourses();
						$courseList = array_merge($courseList, $ci->courseList);
					}
					
					$searchParam = new instructor();
					$searchParam->getUserByID($prof);		
				} elseif ($dept) {
					$user->getCoursesByDept($dept);
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
					$user->getCourseInstances();
				} else {
					$user->getCurrentClassesFor($user->getUserID());
				}
				for ($i=0;$i<count($user->courseInstances);$i++)
				{
					$ci = $user->courseInstances[$i];
					$ci->getCourseForUser($user->getUserID());  //load courses
				}

				$this->displayFunction = "displayRemoveClass";
				$this->argList = "";
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
				$this->argList = array($dept->getAllDepartments(), $terms->getTerms(), 'createNewClass', $_REQUEST, array('cmd'=>'createClass'));
			break;

			case 'createNewClass':
				$page = "manageClasses";
				$loc = "create class";
			
				$t = new term($request['term']);
				
				//check for duplicate course instances
				$dupes = classManager::getDuplicates($request['department'],$request['course_number'], $request['section'], $t->getTermYear(), $t->getTermName());

				if(is_null($dupes) || isset($_REQUEST['confirm_new'])) {
					$c  = new course(null);
					$ci = new courseInstance(null);
					
					$ci->createCourseInstance();
					
					//see if the course exists
					if( !is_null($c->getCourseByMatch($request['department'], $request['course_number'], $request['course_name'])) ) {
						//course found, reuse it
						$ci->setPrimaryCourse($c->getCourseID(), $request['section']);
					}
					else {	//no such course, create new
						$c->createNewCourse($ci->getCourseInstanceID());
						$c->setCourseNo($request['course_number']);
						$c->setDepartmentID($request['department']);
						$c->setName($request['course_name']);
						$c->setSection($request['section']);
						$ci->setPrimaryCourseAliasID($c->getCourseAliasID());
					}

					$ci->addInstructor($ci->getPrimaryCourseAliasID(), $request['selected_instr']);
					$ci->setTerm($t->getTermName());
					$ci->setYear($t->getTermYear());
					$ci->setActivationDate(date('Y-m-d', strtotime($request['activation_date'])));
					$ci->setExpirationDate(date('Y-m-d', strtotime($request['expiration_date'])));
					$ci->setEnrollment($request['enrollment']);
					$ci->setStatus('ACTIVE');
				
					$this->displayFunction = 'displaySuccess';
					$this->argList = array($page, $ci);
				}
				elseif(!is_null($dupes)) {
					$this->displayFunction = 'displayDuplicateCourses';
					$_REQUEST['cmd'] = 'createClass';	//make sure we go back to the previous screen
					$this->argList = array($user, $dupes, urlencode(serialize($_REQUEST)), $request['selected_instr']);
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
				
				$this->displayFunction = 'displayCopyItems';
				$this->argList = array($cmd,$user,$request);
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
		
	
	/**
	 * @return null if no matches, or multidimensional array of matching CIs [0]=direct clashes, [1]=clashes owned by curr instr, [2]=clashes owned by other instr
	 * @param int $dept_id Department ID
	 * @param string $courseNumber Course number
	 * @param string $section Course section
	 * @param int $u_id Current user's ID (either instructor or staff+)
	 * @param int $year Year
	 * @param string $term Term (fall, spring, summer)
	 * @desc Searches for and returns duplicate courseInstances in the DB; matches on dept_id and course_num
	*/
	function getDuplicates($dept_id, $courseNumber, $section, $year, $term) {
		global $g_dbConn, $g_permission, $u;
		
		$u_id = $u->getUserID();
		
		//result sets
		$match_clash = null;		//int - contains ci object of ci that matches on every parameter (except instr)
		$match_own = array();		//array - contains all ci objects of ci that match on instr (for reactivation)
		$match_others = array();	//array - contains all other matching ci objects
		
		//select all courses matching on dept and course number
		switch($g_dbConn->phptype) {
			default: //'mysql'
				$sql = "SELECT ci.course_instance_id, ci.year, ci.term, ca.section, a.user_id
						FROM course_instances AS ci
							JOIN course_aliases AS ca ON ca.course_alias_id = ci.primary_course_alias_id
							JOIN courses AS c ON c.course_id = ca.course_id
							JOIN access AS a ON a.alias_id = ca.course_alias_id
						WHERE c.department_id = ! AND c.course_number = ?
							AND a.permission_level = 3
						ORDER BY ci.year, ci.term, a.user_id";
		}

		//query
		$rs = $g_dbConn->query($sql, array($dept_id, $courseNumber));		
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	
		//check for matches
		while($row = $rs->fetchRow()) {
			//get a temp CI object & pull info
			$tempCI = new courseInstance($row[0]);
			$tempCI->getPrimaryCourse();
			$tempCI->getInstructors();
			
			//look for various matches
			if( ($row[3]==$section) && ($row[1]==$year) && ($row[2]==$term) ) {	//found a clash					
				$match_clash = $tempCI;
			}
			elseif($row[4]==$u_id) {	//found a reactivatable course
				$match_own[] = $tempCI;
			}
			else {	//found another match (CI owned by a different instructor)
				$match_others[] = $tempCI;
			}
			unset($tempCI);
		}
		
		//customize result to current user
		if($u->getRole() >= $g_permission['staff']) {	//if user is staff or higher, we care about _all_ matches
			if($rs->numRows() == 0) {
				return null;
			}
			else {
				return array($match_clash, $match_own, $match_others);
			}
		}	
		else {	//user is instructor and does not care about matches to other instructors
			if(empty($match_clash) && empty($match_own)) {
				return null;
			}
			else {
				return array($match_clash, $match_own);
			}
		}
	} //getDuplicates()
}

?>