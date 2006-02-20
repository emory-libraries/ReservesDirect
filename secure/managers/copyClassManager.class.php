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

		switch ($cmd)
		{

			case 'copyClass':
				$page = 'manageClasses';
				$loc  = "copy course reserves list >> select source class";
				
				$this->displayFunction = 'displaySelectClass';
				$this->argList = array('copyClassOptions', 'Select class to copy FROM:');
			break;
				
			case 'copyClassOptions':
				$sourceClass = new courseInstance($_REQUEST['ci']);
				$sourceClass->getPrimaryCourse();
				$sourceClass->getInstructors();

				$page = 'manageClasses';
				$loc  = "copy course reserves list >> copy options";
				
				$this->displayFunction = 'displayCopyClassOptions';
				$this->argList = array($sourceClass);				
			break;

			case 'copyExisting':
				$page = 'manageClasses';
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
				$page = 'manageClasses';
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
				$this->argList = array($dept->getAllDepartments(), $terms->getTerms(), 'processCopyClass', $_REQUEST, $needed_info);
			break;

			case 'processCopyClass':
				$page = 'manageClasses';
				$loc = 'copy course';

				if(isset($request['copyNew']))
				{
					$t = new term($request['term']);
					
					//first, check to see if resulting class has a duplicate active class
					//do not care about instructor(s), because we are looking for active course matches, no matter who teaches them
					require_once("secure/managers/classManager.class.php");
					$dupes = classManager::getDuplicates($request['department'], $request['course_number'], $request['section'], $t->getTermYear(), $t->getTermName());
					if(!is_null($dupes[0])) {	//found an active dupe
						require_once("secure/displayers/classDisplayer.class.php");
						$this->displayClass = 'classDisplayer';
						$this->displayFunction = 'displayDuplicateCourses';
						//leave a trail to return
						$_REQUEST['cmd'] = 'copyNew';
						$this->argList = array($user, $dupes, urlencode(serialize($_REQUEST)));
						//break out of the case if we hit a dupe
						break;
					}
					else {	
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
						$ci->setActivationDate($request['activation_date']);
						$ci->setExpirationDate($request['expiration_date']);
						$ci->setEnrollment($request['enrollment']);
						$ci->setStatus('ACTIVE');
				
						$request['ci']=$ci->getCourseInstanceID();
						unset($ci);
					}
				}

				$copyStatus = array();

				if (isset($request['sourceClass'])) {
					$sourceClass = new courseInstance($request['sourceClass']);
					$sourceClass->getPrimaryCourse();
				}

				if (isset($request['ci'])) {
					$targetClass = new courseInstance($request['ci']);
					$targetClass->getPrimaryCourse();
				}

				if(isset($request['copyReserves'])) {
					$sourceClass->copyReserves($targetClass->getCourseInstanceID());
					$copyStatus[]="Reserves List sucessfully copied";
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

				if (isset($request['copyCrossListings']))
				{
					$sourceClass->getCrossListings();

					for ($i=0; $i<count($sourceClass->crossListings); $i++)
					{
						//KAW: Add check for duplicate to the addCrossListing Method in the courseInstance class
						$targetClass->addCrossListing($sourceClass->crossListings[$i]->getCourseID(),$sourceClass->crossListings[$i]->getSection());
					}

					$copyStatus[]="Crosslistings successfully copied";
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

				if (isset($request['deleteSource']))
				{
					$sourceClass->destroy();
					$copyStatus[]="Source Class successfully deleted";
				}

				$this->displayFunction = 'displayCopySuccess';
				$this->argList = array($sourceClass, $targetClass, $copyStatus);

			break;
		}
	}
}

?>