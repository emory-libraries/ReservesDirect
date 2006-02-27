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
		global $g_permission, $page, $loc;

		$this->displayClass = "copyClassDisplayer";
		$page = 'manageClasses';

		switch ($cmd)
		{
			case 'importClass':			
				if(!empty($_REQUEST['dst_ci']) && !empty($_REQUEST['ci'])) { //display import options
					//get the source ci
					$ci = new courseInstance($_REQUEST['ci']);
					//get reserves as a tree + recursive iterator
					$walker = $ci->getReservesAsTreeWalker('getReserves');
					
					$loc = 'import class >> import details';
					$this->displayFunction = 'displayImportClassOptions';
					$this->argList = array($ci, $walker, $_REQUEST['dst_ci'], 'processCopyClass');
				}
				elseif(empty($_REQUEST['ci'])) {	//need source class 
					$loc = 'import class >> select source class';
					$this->displayFunction = 'displaySelectClass';
					$this->argList = array('importClass', 'Select course to import FROM:', array('dst_ci'=>$_REQUEST['dst_ci']));
				}				
			break;

			case 'copyClass':
				$loc  = "copy course reserves list >> select source class";				
				$this->displayFunction = 'displaySelectClass';
				$this->argList = array('copyClassOptions', 'Select class to copy FROM:');
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
				$loc = 'copy course reserves list >> select destination class';
				
				//propogate the info
				$needed_info = array();
				if(!empty($_REQUEST['sourceClass']))	$needed_info['sourceClass'] = $_REQUEST['sourceClass'];
				if(!empty($_REQUEST['copyReserves']))	$needed_info['copyReserves'] = $_REQUEST['copyReserves'];
				if(!empty($_REQUEST['copyCrossListings']))	$needed_info['copyCrossListings'] = $_REQUEST['copyCrossListings'];
				if(!empty($_REQUEST['copyEnrollment']))	$needed_info['copyEnrollment'] = $_REQUEST['copyEnrollment'];
				if(!empty($_REQUEST['copyInstructors']))	$needed_info['copyInstructors'] = $_REQUEST['copyInstructors'];
				if(!empty($_REQUEST['copyProxies']))	$needed_info['copyProxies'] = $_REQUEST['copyProxies'];
				if(!empty($_REQUEST['deleteSource']))	$needed_info['deleteSource'] = $_REQUEST['deleteSource'];	

				$this->displayFunction = 'displaySelectClass';
				$this->argList = array('processCopyClass', 'Select class to copy TO:', $needed_info);	
			break;

			case 'copyNew':
				$loc = 'copy course reserves list >> create destination class';
				
				$dept = new department();
				$terms = new terms();
				
				//propogate the info
				$needed_info = array();
				if(!empty($_REQUEST['sourceClass']))	$needed_info['sourceClass'] = $_REQUEST['sourceClass'];
				if(!empty($_REQUEST['copyReserves']))	$needed_info['copyReserves'] = $_REQUEST['copyReserves'];
				if(!empty($_REQUEST['copyCrossListings']))	$needed_info['copyCrossListings'] = $_REQUEST['copyCrossListings'];
				if(!empty($_REQUEST['copyEnrollment']))	$needed_info['copyEnrollment'] = $_REQUEST['copyEnrollment'];
				if(!empty($_REQUEST['copyInstructors']))	$needed_info['copyInstructors'] = $_REQUEST['copyInstructors'];
				if(!empty($_REQUEST['copyProxies']))	$needed_info['copyProxies'] = $_REQUEST['copyProxies'];
				if(!empty($_REQUEST['deleteSource']))	$needed_info['deleteSource'] = $_REQUEST['deleteSource'];
				$needed_info['cmd'] = 'copyNew';
				$needed_info['copyNew'] = 'copyNew';

				$this->displayClass = 'classDisplayer';
				$this->displayFunction = 'displayCreateClass';
				$this->argList = array('processCopyClass', $needed_info);
			break;

			case 'processCopyClass':
				//determine if we are copying or importing
				$importing = isset($_REQUEST['importClass']) ? true : false;

				if(isset($request['copyNew'])) {
					$t = new term($request['term']);
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
						
						$request['ci']=$ci->getCourseInstanceID();	//this will be picked up as $targetClass after this block
						unset($ci);						
					}
					else {	//could not create course -- the CI must be a duplicate
						//display duplicate info
						$this->displayClass = 'classDisplayer';
						$this->displayFunction = 'displayDuplicateCourse';						
						$_REQUEST['cmd'] = 'copyNew';	//leave a trail to return back here
						$this->argList = array($ci, urlencode(serialize($_REQUEST)));
						
						//break out of the case if we hit a dupe
						break;
					}
				}

				$copyStatus = array();
				
				//make sure that not trying to merge the same course
				if($sourceClass->getCourseInstanceID() == $targetClass->getCourseInstanceID()) {
					$copyStatus[] = "Cannot merge a class into itself!";
					//make sure we do nothing else
					$this->displayFunction = 'displayCopySuccess';
					$this->argList = array($sourceClass, $targetClass, $copyStatus, $importing);
					break;
				}

				if (isset($request['sourceClass'])) {
					$sourceClass = new courseInstance($request['sourceClass']);
					$sourceClass->getPrimaryCourse();
				}

				if (isset($request['ci'])) {
					$targetClass = new courseInstance($request['ci']);
					$targetClass->getPrimaryCourse();
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
	
					if (isset($request['copyEnrollment']) || isset($request['deleteSource']))
					{
						$sourceClass->getStudents();
	
						for ($i=0; $i<count($sourceClass->students); $i++)
						{
							//KAW: Do We want the students to be added to the same crossListing from the original/source class?
							$sourceClass->students[$i]->attachCourseAlias($targetClass->getPrimaryCourseAliasID());
						}
	
						$copyStatus[]="Enrollment List successfully copied";
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