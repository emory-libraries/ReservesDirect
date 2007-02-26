<?
/*******************************************************************************
copyClassManager.class.php

Created by Dmitriy Panteleyev (dpantel@emory.edu)

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
require_once("secure/displayers/copyClassDisplayer.class.php");
require_once("secure/displayers/classDisplayer.class.php");
require_once("secure/classes/courseInstance.class.php");
require_once("secure/classes/course.class.php");
require_once("secure/classes/department.class.php");
require_once("secure/classes/term.class.php");
require_once("secure/classes/terms.class.php");
require_once("secure/classes/reserves.class.php");
require_once("secure/classes/request.class.php");

class copyClassManager extends baseManager {

	function copyClassManager($cmd, $user, $request, $hidden_fields=null)
	{
		global $g_permission, $page, $loc, $u;

		$this->displayClass = "copyClassDisplayer";
		//set the page (tab)
		if($u->getRole() >= $g_permission['staff']) {
			$page = 'manageClasses';
		}
		else {
			$page = 'myReserves';
		}

		switch ($cmd)
		{
			case 'importClass':	
				$src_ci = !empty($_REQUEST['src_ci']) ? $_REQUEST['src_ci'] : null;
				$dst_ci = !empty($_REQUEST['dst_ci']) ? $_REQUEST['dst_ci'] : null;

				if (!empty($_REQUEST['dst_ci']))
					$dst_ci = $_REQUEST['dst_ci'];  //user selected destination
				elseif (!empty($_REQUEST['new_ci']))
					$dst_ci = $_REQUEST['new_ci'];  //user created new destination course
				else
					$dst_ci = null;
				
				if(!empty($dst_ci) && !empty($src_ci)) {	//have both source and destination, display options
					//get the source ci
					$ci = new courseInstance($_REQUEST['src_ci']);
					//get reserves as a tree + recursive iterator
					$walker = $ci->getReservesAsTreeWalker('getReserves');
					
					$loc = 'import class >> import details';
					$this->displayFunction = 'displayImportClassOptions';
					$this->argList = array($ci, $walker, $dst_ci, 'processCopyClass');
				}
				elseif(empty($src_ci)) {	//need source ci -- find it
					$class_list = $u->getCourseInstancesToImport();
				
					$loc = 'import class >> select source class';
					$this->displayFunction = 'displaySelectClass';
					//pass on destination ci, in case it is already set
					$this->argList = array('importClass', $class_list, 'Select course to import FROM:', array('dst_ci'=>$dst_ci), false, 'src_ci', null);
				}
				elseif(empty($dst_ci)) {	//need destination ci -- create it
					if ($_REQUEST['createNew']) //User has chosen to create new class 
					{
                        //assume that we already have a source and initialize it
                        $ci = new courseInstance($src_ci);
                        $ci->getCourseForUser();        //get course info
                        $ci->course->getDepartment();   //get the department
                        $ci->getInstructors();  //get instructors

                        //attempt to pre-fill create-class form by faking the $_REQUEST array
                        $_REQUEST = array(
                                'department' => $ci->course->department->getDepartmentID(),
                                'section' => $ci->course->getSection(),
                                'course_number' => $ci->course->getCourseNo(),
                                'course_name' => $ci->course->getName(),
                                'enrollment' => $ci->getEnrollment(),
                                'selected_instr' => $ci->instructorIDs[0],
                                'search_selected_instr' => $ci->instructorList[0]->getName().' -- '.$ci->instructorList[0]->getUsername()
                        );
                        //pass on the source CI id
                        $needed_info = array('src_ci' => $src_ci, 'submit' => 'Continue');

                        $loc = 'import class >> create destination class';
                        $this->displayClass = 'classDisplayer';
                        $this->displayFunction = 'displayCreateClass';
                        $this->argList = array('importClass', 'importClass', $needed_info, 'Create course to import INTO: ');
					} else {
						//display list of course to reactivate
						$class_list = $u->getAllFutureCourseInstances();
						
						//remove source class from selection
						unset($class_list[$src_ci]);
		
						//pass on the source CI id
						$needed_info = array('src_ci' => $src_ci);
						
						$loc = 'import class >> select destination class';
						$this->displayClass = 'classDisplayer';
						$this->displayFunction = 'displaySelectClass';
						$this->argList = array('importClass', $class_list, 'Select course to import INTO: <br>Select readings to reactivate on the next screen.', $needed_info, false, 'dst_ci', 'index.php?cmd=importClass&createNew=true&postproc_cmd=importClass&src_ci='.$src_ci);								

					}
				}			
			break;

			case 'copyClass':
				$class_list = $u->getCourseInstancesToEdit();
				
				$loc  = "copy course reserves list >> select source class";				
				$this->displayFunction = 'displaySelectClass';
				$this->argList = array('copyClassOptions', $class_list, 'Select Source Class:'); //Select class to copy FROM:
			break;
				
			case 'copyClassOptions':
				$sourceClass = new courseInstance($_REQUEST['ci']);
				$sourceClass->getPrimaryCourse();
				$sourceClass->getInstructors();

				$loc  = "copy course reserves list >> copy options";				
				$this->displayFunction = 'displayCopyClassOptions';
				$this->argList = array($sourceClass);				
			break;

			case 'copyExisting':				
				//propagate the info
				$needed_info = array();
				if(!empty($_REQUEST['sourceClass']))		$needed_info['sourceClass'] = $_REQUEST['sourceClass'];
				if(!empty($_REQUEST['copyReserves']))		$needed_info['copyReserves'] = $_REQUEST['copyReserves'];
				if(!empty($_REQUEST['copyCrossListings']))	$needed_info['copyCrossListings'] = $_REQUEST['copyCrossListings'];
				if(!empty($_REQUEST['copyEnrollment']))		$needed_info['copyEnrollment'] = $_REQUEST['copyEnrollment'];
				if(!empty($_REQUEST['copyInstructors']))	$needed_info['copyInstructors'] = $_REQUEST['copyInstructors'];
				if(!empty($_REQUEST['copyProxies']))		$needed_info['copyProxies'] = $_REQUEST['copyProxies'];
				if(!empty($_REQUEST['deleteSource']))		$needed_info['deleteSource'] = $_REQUEST['deleteSource'];	
				if(!empty($_REQUEST['crosslistSource']))	$needed_info['crosslistSource'] = $_REQUEST['crosslistSource'];	
				
				$class_list = $u->getCourseInstancesToEdit();

				$loc = 'copy course reserves list >> select destination class';
				$this->displayFunction = 'displaySelectClass';
				$this->argList = array('processCopyClass', $class_list, 'Select class to copy TO:', $needed_info);	
			break;

			case 'copyNew':				
				//propagate the info
				$needed_info = array();
				if(!empty($_REQUEST['sourceClass']))		$needed_info['sourceClass'] = $_REQUEST['sourceClass'];
				if(!empty($_REQUEST['copyReserves']))		$needed_info['copyReserves'] = $_REQUEST['copyReserves'];
				if(!empty($_REQUEST['copyCrossListings']))	$needed_info['copyCrossListings'] = $_REQUEST['copyCrossListings'];
				if(!empty($_REQUEST['copyEnrollment']))		$needed_info['copyEnrollment'] = $_REQUEST['copyEnrollment'];
				if(!empty($_REQUEST['copyInstructors']))	$needed_info['copyInstructors'] = $_REQUEST['copyInstructors'];
				if(!empty($_REQUEST['copyProxies']))		$needed_info['copyProxies'] = $_REQUEST['copyProxies'];
				if(!empty($_REQUEST['deleteSource']))		$needed_info['deleteSource'] = $_REQUEST['deleteSource'];
				if(!empty($_REQUEST['crosslistSource']))	$needed_info['crosslistSource'] = $_REQUEST['crosslistSource'];	
				
				$loc = 'copy course reserves list >> create destination class';
				$this->displayClass = 'classDisplayer';
				$this->displayFunction = 'displayCreateClass';
				$this->argList = array('copyNew', 'processCopyClass', $needed_info, 'Create course to copy TO:');	
			break;

			case 'processCopyClass':
				//determine if we are copying or importing
				$importing = isset($_REQUEST['importClass']) ? true : false;
				
				//init the source class
				$sourceClass = (!empty($_REQUEST['sourceClass'])) ? new courseInstance($_REQUEST['sourceClass']) : new courseInstance($_REQUEST['new_ci']);
				$sourceClass->getPrimaryCourse();
				
				//figure out the destination class ID
				if(!empty($_REQUEST['new_ci'])) {
					$dst_ci = $_REQUEST['new_ci'];	//destination class has just been created
				}else{
					$dst_ci = (!empty($_REQUEST['ci'])) ? $_REQUEST['ci'] : $_REQUEST['dst_ci'];
				}
				
				//init the destination class
				$targetClass = new courseInstance($dst_ci);
				$targetClass->getPrimaryCourse();
				
				//init an array to store progress of import/copy/merge process				
				$copyStatus = array();
				
				//make sure that user is not trying to merge the same course
				if($sourceClass->getCourseInstanceID() == $targetClass->getCourseInstanceID()) {
					$copyStatus[] = "Cannot merge a class into itself!";
					//make sure we do nothing else
					$this->displayFunction = 'displayCopySuccess';
					$this->argList = array($sourceClass, $targetClass, $copyStatus, $importing);
					break;
				}

				//split the difference b/n copying and importing
				
				if($importing) {	//importing only
					//copy reserves
					$sourceClass->copyReserves($targetClass->getCourseInstanceID(), $_REQUEST['selected_reserves'], $_REQUEST['requestedLoanPeriod']);
					$copyStatus[]="Reserves List sucessfully copied";
				}
				else {	//copying only
					if(isset($request['copyReserves'])) {
						$sourceClass->copyReserves($targetClass->getCourseInstanceID());
						$copyStatus[]="Reserves List sucessfully copied";
					}

					if (isset($request['copyProxies']))
					{
						$sourceClass->getProxies();
	
						for ($i=0; $i<count($sourceClass->proxyIDs); $i++)
						{
							$targetClass->addProxy($targetClass->getPrimaryCourseAliasID(),$sourceClass->proxyIDs[$i]);
						}
	
						$copyStatus[]="Proxies successfully copied";
					}
					
					if(isset($request['copyEnrollment'])) {
						$roll = $sourceClass->getRoll();
						$target_CA_id = $targetClass->getPrimaryCourseAliasID();
						
						foreach($roll as $status=>$students) {
							foreach($students as $student) {
								$student->joinClass($target_CA_id, $status);
							}
						}

						$copyStatus[] = "Enrollment list successfully copied";
					}
				}
				
				//both
				
				if (isset($request['copyCrossListings']))
				{
					$sourceClass->getCrossListings();

					for ($i=0; $i<count($sourceClass->crossListings); $i++)
					{
						$targetClass->addCrossListing($sourceClass->crossListings[$i]->getCourseID(),$sourceClass->crossListings[$i]->getSection());
					}

					$copyStatus[]="Crosslistings successfully copied";
				}				

				if (isset($request['copyInstructors']))
				{
					$sourceClass->getInstructors();
					$targetClass->getCrossListings();
					$targetClass->getPrimaryCourseAliasID();


					for ($i=0; $i<count($sourceClass->instructorIDs); $i++)
					{
						$targetClass->addInstructor($targetClass->getPrimaryCourseAliasID(), $sourceClass->instructorIDs[$i]);
						for ($k=0; $k<count($targetClass->crossListings); $k++)
						{
							$targetClass->addInstructor($targetClass->crossListings[$k]->getCourseAliasID(),$sourceClass->instructorIDs[$i]);
						}
					}

					$copyStatus[]="Instructors successfully copied";
				}	
				
				//delete source?
				if(!$importing && isset($request['deleteSource'])) {
					$sourceClass->destroy();
					$copyStatus[]="Source Class successfully deleted";
				}
				
				//if crosslist source
				if (isset($request['crosslistSource']))
				{
					$sourceClass->getCourses();					

					foreach ($sourceClass->courseList as $course)
					{						
						$course->bindToCourseInstance($targetClass->getCourseInstanceID());					
						$copyStatus[]= $course->displayCourseNo() . " successfully crosslisted";
					}
					$sourceClass->destroy();
					$copyStatus[]="Source Class successfully deleted";					
				}
				
				$targetClass->setStatus('ACTIVE');

				$this->displayFunction = 'displayCopySuccess';
				$this->argList = array($sourceClass, $targetClass, $copyStatus, $importing);

			break;
		}
	}
}

?>
