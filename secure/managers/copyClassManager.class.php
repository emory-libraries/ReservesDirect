<?
/*******************************************************************************
copyClassManager.class.php

Created by Dmitriy Panteleyev (dpantel@emory.edu)

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
				$src_ci = !empty($_REQUEST['ci']) ? $_REQUEST['ci'] : null;
				$dst_ci = !empty($_REQUEST['new_ci']) ? $_REQUEST['new_ci'] : null;
				
				if(!empty($dst_ci) && !empty($src_ci)) {	//have both source and destination, display options
					//get the source ci
					$ci = new courseInstance($_REQUEST['ci']);
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
					$this->argList = array('importClass', $class_list, 'Select course to import FROM:', array('new_ci'=>$dst_ci));
				}
				elseif(empty($dst_ci)) {	//need destination ci -- create it
					//assume that we already have a source and initialize it
					$ci = new courseInstance($_REQUEST['ci']);
					$ci->getCourseForUser();	//get course info
					$ci->course->getDepartment();	//get the department

					//attempt to pre-fill create-class form by faking the $_REQUEST array	
					$_REQUEST = array(
						'department' => $ci->course->department->getDepartmentID(),
						'section' => $ci->course->getSection(),
						'course_number' => $ci->course->getCourseNo(),
						'course_name' => $ci->course->getName(),
						'enrollment' => $ci->getEnrollment()
					);
					//pass on the source CI id
					$needed_info = array('ci' => $src_ci);
					
					$loc = 'import class >> create destination class';
					$this->displayClass = 'classDisplayer';
					$this->displayFunction = 'displayCreateClass';
					$this->argList = array('importClass', 'importClass', $needed_info, 'Create course to import INTO:');								
				}			
			break;

			case 'copyClass':
				$class_list = $u->getCourseInstancesToEdit();
				
				$loc  = "copy course reserves list >> select source class";				
				$this->displayFunction = 'displaySelectClass';
				$this->argList = array('copyClassOptions', $class_list, 'Select class to copy FROM:');
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
				if(!empty($_REQUEST['sourceClass']))	$needed_info['sourceClass'] = $_REQUEST['sourceClass'];
				if(!empty($_REQUEST['copyReserves']))	$needed_info['copyReserves'] = $_REQUEST['copyReserves'];
				if(!empty($_REQUEST['copyCrossListings']))	$needed_info['copyCrossListings'] = $_REQUEST['copyCrossListings'];
				if(!empty($_REQUEST['copyEnrollment']))	$needed_info['copyEnrollment'] = $_REQUEST['copyEnrollment'];
				if(!empty($_REQUEST['copyInstructors']))	$needed_info['copyInstructors'] = $_REQUEST['copyInstructors'];
				if(!empty($_REQUEST['copyProxies']))	$needed_info['copyProxies'] = $_REQUEST['copyProxies'];
				if(!empty($_REQUEST['deleteSource']))	$needed_info['deleteSource'] = $_REQUEST['deleteSource'];	
				
				$class_list = $u->getCourseInstancesToEdit();

				$loc = 'copy course reserves list >> select destination class';
				$this->displayFunction = 'displaySelectClass';
				$this->argList = array('processCopyClass', $class_list, 'Select class to copy TO:', $needed_info);	
			break;

			case 'copyNew':				
				//propagate the info
				$needed_info = array();
				if(!empty($_REQUEST['sourceClass']))	$needed_info['sourceClass'] = $_REQUEST['sourceClass'];
				if(!empty($_REQUEST['copyReserves']))	$needed_info['copyReserves'] = $_REQUEST['copyReserves'];
				if(!empty($_REQUEST['copyCrossListings']))	$needed_info['copyCrossListings'] = $_REQUEST['copyCrossListings'];
				if(!empty($_REQUEST['copyEnrollment']))	$needed_info['copyEnrollment'] = $_REQUEST['copyEnrollment'];
				if(!empty($_REQUEST['copyInstructors']))	$needed_info['copyInstructors'] = $_REQUEST['copyInstructors'];
				if(!empty($_REQUEST['copyProxies']))	$needed_info['copyProxies'] = $_REQUEST['copyProxies'];
				if(!empty($_REQUEST['deleteSource']))	$needed_info['deleteSource'] = $_REQUEST['deleteSource'];

				$loc = 'copy course reserves list >> create destination class';
				$this->displayClass = 'classDisplayer';
				$this->displayFunction = 'displayCreateClass';
				$this->argList = array('copyNew', 'processCopyClass', $needed_info, 'Create course to copy TO:');	
			break;

			case 'processCopyClass':
				//determine if we are copying or importing
				$importing = isset($_REQUEST['importClass']) ? true : false;
				
				//init the source class
				if(!empty($_REQUEST['sourceClass'])) {
					$sourceClass = new courseInstance($_REQUEST['sourceClass']);
					$sourceClass->getPrimaryCourse();
				}
				//figure out the destination class ID
				if(!empty($_REQUEST['ci'])) {
					$dst_ci = $_REQUEST['ci'];	//destination class selected from existing classes
				}
				elseif(!empty($_REQUEST['new_ci'])) {
					$dst_ci = $_REQUEST['new_ci'];	//destination class has just been created
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

				$this->displayFunction = 'displayCopySuccess';
				$this->argList = array($sourceClass, $targetClass, $copyStatus, $importing);

			break;
		}
	}
}

?>
