<?
/*******************************************************************************

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

Created by Kathy A. Washington (kawashi@emory.edu)

Reserves Direct 2.0 is located at:
http://coursecontrol.sourceforge.net/

*******************************************************************************/
require_once("common.inc.php");
require_once("classes/users.class.php");
require_once("displayers/classDisplayer.class.php");

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
		global $g_permission, $page, $loc, $ci;
		
//echo "classManager($cmd, $user, $adminUser)<P>";
		
		$this->displayClass = "classDisplayer";

		switch ($cmd)
		{
			case 'manageClasses':
				$page = "manageClasses";
				
				if ($user->getDefaultRole() >= $g_permission['staff'])
				{
					$loc  = "home";
					
					$this->displayFunction = 'displayStaffHome';
					$this->argList = array($user);
				} else {
					$loc  = "home";
					
					$this->displayFunction = 'displayInstructorHome';
					$this->argList = "";
				}
			break;

			case 'reactivateClass':
				$page = "manageClasses";
				
				if ($user->getUserClass() == 'instructor')
				{
					$instructors 		= null;
					$courses 			= null;
					$courseInstances 	= $user->getAllCourseInstances(true);
					for($i=0;$i<count($courseInstances);$i++) { $courseInstances[$i]->getPrimaryCourse(); }
				} else {
					$usersObject = new users();
					$instructors = $usersObject->getUsersByRole('instructor');
	
					if (isset($request['instructor']))
					{
						$i = new instructor();
						$i->getUserByID($request['instructor']);
						$courses = $i->getAllCourses(true);
					} else 
						$courses = null;
				
					if (isset($request['course']))
					{
						$courseInstances = $i->getCourseInstancesByCourseID($request['course']);
						for($i=0;$i<count($courseInstances);$i++) { $courseInstances[$i]->getPrimaryCourse(); }
					} else
						$courseInstances = null;
				}
				$this->displayFunction = 'displayReactivate';
				$this->argList = array($instructors, $courses, $courseInstances, 'reactivateList', $_REQUEST, array('cmd'=>$cmd));
			break;

			case 'reactivateList':
				$ci->getPrimaryCourse();
				$ci->getCrossListings();
				$ci->getInstructors();
				$ci->getReserves();
				
				$instructorList = null;
				if ($user->getDefaultRole() >= $g_permission['staff'])
				{
					$usersObj = new users();
					$instructorList = $usersObj->getUsersByRole('instructor');
				}
			
				$this->displayFunction = 'displaySelectReservesToReactivate';
				$this->argList = array($ci, $user, $instructorList, array('cmd'=>'reactivate', 'instructor'=>$_REQUEST['instructor'], 'course'=>$_REQUEST['course'], 'ci'=>$_REQUEST['ci'], 'term'=>$_REQUEST['term']));
			break;
			
			case 'reactivate':			
				$page = "manageClasses";
					$term = new term($_REQUEST['term']);
					$srcCI = new courseInstance($_REQUEST['ci']);
					$srcCI->getPrimaryCourse();
					$srcCI->getProxies();
					
					$proxyList = (isset($request['restoreProxies']) && $request['restoreProxies'] == "on") ? $srcCI->proxyIDs : null;
					
					$instructorList = $_REQUEST['carryInstructor'];
					if (isset($_REQUEST['additionalInstructor']) && $_REQUEST['additionalInstructor'] != "") array_push($instructorList, $_REQUEST['additionalInstructor']);			

					$carryXListing = (isset($_REQUEST['carryCrossListing'])) ? $_REQUEST['carryCrossListing'] : null;
					$carryReserves = (isset($_REQUEST['carryReserve'])) ? $_REQUEST['carryReserve'] : null;
					
					$newCI = $user->copyCourseInstance($srcCI, $term->getTermName(), $term->getTermYear(), $term->getBeginDate(), $term->getEndDate(), $srcCI->getStatus(), $srcCI->course->getSection(), $instructorList, $proxyList, $carryXListing, $carryReserves);
			
					$this->displayFunction = 'displaySuccess';
					$this->argList = array($page, $newCI);
			break;		
			
			case 'editClass':			
				$page = "manageClasses";
				$loc  = "home";
				
				$reserves = (isset($_REQUEST['reserve'])) ? $_REQUEST['reserve'] : null;
				
				if (isset($_REQUEST['reserveListAction']))
					switch ($_REQUEST['reserveListAction'])
					{
						case 'deleteAll':
							if (is_array($reserves) && !empty($reserves)){
								foreach($reserves as $r)
								{
									$reserve = new reserve($r);
									$reserve->destroy();
								}
							}
						break;
						
						case 'activateAll':
							if (is_array($reserves) && !empty($reserves)){
								foreach($reserves as $r)
								{
									$reserve = new reserve($r);
									$reserve->setStatus('ACTIVE');
								}
							}	
						break;
						
						case 'deactivateAll':
							if (is_array($reserves) && !empty($reserves)){
								foreach($reserves as $r)
								{
									$reserve = new reserve($r);
									$reserve->setStatus('INACTIVE');
								}
							}	
						break;					
					}			
									
				$ci = new courseInstance($_REQUEST['ci']);
				//$ci->getCourseForInstructor($user->getUserID());
				$ci->getReserves();
				$ci->getInstructors();
				$ci->getCrossListings();
				$ci->getProxies();
				$ci->getPrimaryCourse();
				$ci->course->getDepartment();
		
				$this->displayFunction = 'displayEditClass';
				$this->argList = array($user, $ci);
			break;
			
			case 'editTitle':
			case 'editCrossListings':
				
				$ci = new courseInstance($_REQUEST['ci']);
			
				if ($_REQUEST['deleteCrossListings']) 
				{
					$courses = $_REQUEST['deleteCrossListing'];
					if (is_array($courses) && !empty($courses)){
						foreach($courses as $c)
						{
							$errorMsg = $user->removeCrossListing($c);
							echo '<span class="helpertext">'.$errorMsg.'</span>';
						}
					}
				}
				
				
				if ($_REQUEST['addCrossListing']) 
				{
					
					$dept = $_REQUEST['newDept'];
					$courseNo = $_REQUEST['newCourseNo'];
					$section = $_REQUEST['newSection'];
					$courseName = $_REQUEST['newCourseName'];
		
					if ($dept==NULL || $courseNo==NULL || $section==NULL || $courseName==NULL) {
						echo '<br><span class="helpertext">'
						.	'Please supply a Department, Course#, Section, and Title before adding the Cross Listing.'
						.	'</span>';
					} else {
						$user->addCrossListing($ci, $dept, $courseNo, $section, $courseName);
					}
					
				}
				
				if ($_REQUEST['updateCrossListing']) {
					/* commented out by kawashi on 11.12.04 - No longer able to change primary course
					$oldPrimaryCourse = new course($_REQUEST['oldPrimaryCourse']);
					$oldPrimaryCourse->setDepartmentID($_REQUEST[primaryDept]);
					$oldPrimaryCourse->setCourseNo($_REQUEST[primaryCourseNo]);
					$oldPrimaryCourse->setSection($_REQUEST[primarySection]);
					$oldPrimaryCourse->setName($_REQUEST[primaryCourseName]);
			
					//Set New Primary Course
					$ci->setPrimaryCourseAliasID($_REQUEST['primaryCourse']);
					*/
					
					if ($_REQUEST[cross_listings])
					{
						$cross_listings = array_keys($_REQUEST[cross_listings]);
						foreach ($cross_listings as $cross_listing)
						{
							$updateCourse = new course($cross_listing);
							$updateCourse->setDepartmentID($_REQUEST[cross_listings][$cross_listing]['dept']);
							$updateCourse->setCourseNo($_REQUEST[cross_listings][$cross_listing]['courseNo']);
							$updateCourse->setSection($_REQUEST[cross_listings][$cross_listing]['section']);
							$updateCourse->setName($_REQUEST[cross_listings][$cross_listing]['courseName']);
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
				$ci = new courseInstance($_REQUEST['ci']);
				$ci->getCrossListings();  //load cross listings

				if ($_REQUEST['addInstructor']) {
					$ci->addInstructor($ci->primaryCourseAliasID,$_REQUEST['prof']); //Add instructor to primary course alias
					for ($i=0; $i<count($ci->crossListings); $i++) {
						$ci->addInstructor($ci->crossListings[$i]->courseAliasID, $_REQUEST['prof']); // add instructor to the Xlistings
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
				$instructorList = common_getUsers('instructor'); //get instructors to populate drop down box				
				$this->displayFunction = 'displayEditInstructors';
				$this->argList = array($ci, $instructorList, 'ADD AN INSTRUCTOR', 'Choose an Instructor', 'Instructor', 'CURRENT INSTRUCTORS', 'Remove Selected Instructors');
			break;
			
			case 'editProxies':				
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
				if ($user->getDefaultRole() >= $g_permission['staff']) {
					$courseInstances = $user->getCourseInstances($adminUser->getUserID());
				} elseif ($user->getDefaultRole() >= $g_permission['proxy']) { //2 = proxy
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
				if ($user->getDefaultRole() >= $g_permission['staff']) {
					if (is_null($adminUser))
					{
					 	$this->classManager("selectInstructor", $user, null);
					 	break;
					}
					else 
						$courseInstances = $adminUser->getCourseInstances();		
					
				} elseif ($user->getDefaultRole() >= $g_permission['proxy']) { //2 = proxy
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
				$instructorList = common_getUsers('instructor');
				$deptList = common_getDepartments();
			
				$this->displayFunction = "displaySearchForClass";
				$this->argList = array($instructorList, $deptList);
			break;
			
			case 'addClass':
				$page = "myReserves";
				
				$prof = $_REQUEST['prof'];
				$dept = $_REQUEST['dept'];
		
				if ($prof) {
					$instructor = new instructor($prof);
					$instructor->getCurrentCourseInstancesByRole($g_permission['instructor']); 
					
					$courseList = array ();

					for ($i=0;$i<count($instructor->courseInstances);$i++)	
					{ 
						$ci = $instructor->courseInstances[$i];
				
						//PROBLEM - What if instructor is teaching a crosslisted course
						//this query won't suffice
						//$ci->getCourseForUser($instructor->getUserID());
						$ci->getCoursesForInstructor($instructor->getUserID());
												
						for ($j=0; $j<count($ci->courseList); $j++)
						{
							$courseList[] = $ci->courseList[$j];
						}
						
					}
					$searchParam = $instructor;
				} elseif ($dept) {
					$user->getCoursesByDept($dept);
					$courseList = $user->courseList;
					$department = new department($dept);

					$searchParam = $department;
				} else {
					echo ("<br><span class=helpertext>Error - You must choose either an Instructor Name or a Department</span>");
					return;
				}
		
				$this->displayFunction = "displayAddClass";
				$this->argList = array($courseList, $searchParam);
			break;
			
			case 'removeClass':
				$page = "myReserves";
				
				if ($user->getDefaultRole() < $g_permission['proxy']) {
					$user->getCourseInstances();
				} else {
					$user->getCurrentCourseInstancesByRole($g_permission['student']);		
				}
				for ($i=0;$i<count($user->courseInstances);$i++)	
				{ 
					$ci = $user->courseInstances[$i];
					$ci->getCourseForUser($user->getUserID());  //load courses 
				} 

				$this->displayFunction = "displayRemoveClass";
				$this->argList = "";
			break;
			
			case 'createClass':
				$page = "manageClasses";

				$usersObject = new users();		
				$dept = new department();
				$terms = new terms();
		
				$this->displayFunction = 'displayCreateClass';
				$this->argList = array($usersObject->getUsersByRole('instructor'), $dept->getAllDepartments(), $terms->getTerms(), array('cmd'=>'createNewClass'));
			break;

			case 'createNewClass':
				$t = new term($request['term']);
		
				$c  = new course(null);
				$ci = new courseInstance(null);

				$ci->createCourseInstance();
				$c->createNewCourse($ci->getCourseInstanceID());

				$ci->addInstructor($c->getCourseAliasID(), $request['instructor']);
				
				$c->setCourseNo($request['course_number']);
				$c->setDepartmentID($request['department']);
				$c->setName($request['course_name']);
				$c->setSection($request['section']);
				$ci->setPrimaryCourseAliasID($c->getCourseAliasID());
				$ci->setTerm($t->getTermName());
				$ci->setYear($t->getTermYear());
				$ci->setActivationDate($request['activation_date']);
				$ci->setExpirationDate($request['expiration_date']);
				$ci->setEnrollment($request['enrollment']);
				$ci->setStatus('ACTIVE');
				
				$this->displayFunction = 'displaySuccess';
				$this->argList = array($page, $ci);
		}	
	}
}

?>