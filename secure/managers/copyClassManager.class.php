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

Created by Kathy Washington (kawashi@emory.edu)

Reserves Direct 2.0 is located at:
http://coursecontrol.sourceforge.net/

*******************************************************************************/
require_once("secure/common.inc.php");
require_once("secure/classes/users.class.php");
require_once("secure/displayers/copyClassDisplayer.class.php");

class copyClassManager
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
	
	
	function copyClassManager($cmd, $user, $request, $hidden_fields=null)
	{
		global $g_permission, $page, $loc;
			
		$this->displayClass = "copyClassDisplayer";
				
		switch ($cmd)
		{
			case 'copyClass':			
				$page = 'manageClasses';				
				$loc  = "copy course reserves list";
								
				$this->displayFunction = 'displayCopyClass';
				$this->argList = array($cmd, $user, $request);
			break;
			
			case 'clearCopyClassLookup':
				$ci = $request['ci'];
				$cmd = $request['copyAction'];
				unset($request);
				$request['sourceClass']=$ci;
				copyClassManager::copyClassManager($cmd,$user,$request);
			break;
			
			case 'copyExisting':
				$page = 'manageClasses';
				$loc = 'copy course';
				
				$sourceClass=new courseInstance($request['sourceClass']);
				$sourceClass->getPrimaryCourse();
				$sourceClass->getInstructors();
				
				$this->displayFunction = 'displayCopyExisting';
				$this->argList = array($cmd, $user, $sourceClass, $request);
			break;
			
			case 'copyNew':
				$page = 'manageClasses';
				$loc = 'copy course';
				
				$sourceClass=new courseInstance($request['sourceClass']);
				$sourceClass->getPrimaryCourse();
				$sourceClass->getInstructors();
				
				$terms = new terms();
				$termsArray = $terms->getTerms();
		
				$department = new department();
				$departments = $department->getAllDepartments();
				
				$this->displayFunction = 'displayCopyNew';
				$this->argList = array($cmd, $user, $sourceClass, $termsArray, $departments, $request);
			break;
			
			case 'processCopyClass':
				$page = 'manageClasses';
				$loc = 'copy course';
				
				if(isset($request['copyNew']))
				{
					$t = new term($request['term']);
		
					$c  = new course(null);
					$ci = new courseInstance(null);

					$ci->createCourseInstance();
					$c->createNewCourse($ci->getCourseInstanceID());

					$ci->addInstructor($c->getCourseAliasID(), $request['selected_instr']);
				
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
					
					$request['ci']=$ci->getCourseInstanceID();
					unset($ci);
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
				
				if (isset($request['copyReserves']))
				{
					$sourceClass->getReserves();
					$targetClass->getReserves();
					
					for ($i=0;$i<count($sourceClass->reserveList);$i++)
					{
						$sourceClass->reserveList[$i]->getItem();
						
						if (!$sourceClass->reserveList[$i]->item->isPhysicalItem())
						{
							$reserve = new reserve();
							if ($reserve->createNewReserve($targetClass->getCourseInstanceID(), $sourceClass->reserveList[$i]->itemID))
							{
								$reserve->setActivationDate($targetClass->getActivationDate());	
								$reserve->setExpirationDate($targetClass->getExpirationDate());	
							}

						} else {
							//store reserve for physical items with status = IN PROCESS	
							$reserve = new reserve();
							
							if($reserve->createNewReserve($targetClass->getCourseInstanceID(), $sourceClass->reserveList[$i]->itemID)) {
								$reserve->setStatus("IN PROCESS");
								$reserve->setActivationDate($targetClass->getActivationDate());	
								$reserve->setExpirationDate($targetClass->getExpirationDate());	
								
								//create request
								$requst = new request();				
								$requst->createNewRequest($targetClass->getCourseInstanceID(), $sourceClass->reserveList[$i]->itemID);
								$requst->setRequestingUser($user->getUserID());
								$requst->setReserveID($reserve->getReserveID());
							}
							
						}
					}
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