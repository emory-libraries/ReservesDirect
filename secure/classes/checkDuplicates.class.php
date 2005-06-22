<?
/*******************************************************************************
searchItem.class.php
methods to search and display Items

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

Created by Kathy Washingon (kawashi@emory.edu)

Reserves Direct 2.0 is located at:
http://coursecontrol.sourceforge.net/

*******************************************************************************/

class checkDuplicates
{
	
	/**
	 * @return array of courseInstances
	 * @param int $dept 
	 * @param string $courseNumber
	 * @param int $section 
	 * @desc Checks for duplicate courseInstances in the DB
	*/
	function checkDuplicateClass($dept, $courseNumber, $section)
	{
	
		//If match on dept, #, section AND course instance is currently active, 
			//display text: "This class already exists and is active for [TERM]. The class is taught by [INSTRUCTORS]." 
			//If instructor=user logged in or user role=staff, display link "Go to class." 
			//If instructor does not = user, display "Contact your reserves staff for assistance with this class."

		//If match on dept, #, section AND course instance is NOT currently active, 
			//display text: "This class already exists and was active for [TERM]. The class was taught by [INSTRUCTORS]." 
			//If instructor=user or user role=staff, display link "Reactivate class." 
			//If instructor does not = user, display "Contact your reserves staff for assistance with this class."

		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql_checkCourse	= "SELECT course_id FROM courses WHERE department_id = ! and course_number = ?";
				$sql_checkCI	 	= "SELECT course_instance_id FROM course_aliases WHERE course_id IN ! AND section = ?";
		}

		$rs = $g_dbConn->query($sql_checkCourse, array($dept, $courseNumber));		
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		$i=0;
		$courseIDs="(";
		while ($row = $rs->fetchRow()) {		
			if ($i!=0) {
				$courseIDs .= ", ".$row[0];
			} else {
				$courseIDs .= $row[0];
			}
			$i++;
		}
		$courseIDs .= ")";
			
		
		if ($courseIDs!='()') {
			
			$duplicateCourseInstances = array();
			
			$rs2 = $g_dbConn->query($sql_checkCI, array($courseIDs, $section));		
			if (DB::isError($rs2)) { trigger_error($rs2->getMessage(), E_USER_ERROR); }
			
			while ($row2 = $rs2->fetchrow()) {
				$tempCI = new courseInstance($row2[0]);
				$tempCI->getPrimaryCourse();
				$tempCI->getInstructors();
				$duplicateCourseInstances[] = $tempCI;
				unset($tempCI);
			}
			if (!empty($duplicateCourseInstances)) {
				return $duplicateCourseInstances;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	/**
	 * @return array of courseInstances
	 * @param int $dept 
	 * @param string $courseNumber
	 * @param int $section 
	 * @param string $term
	 * @param int $year 
	 * @desc Checks for duplicate reactivation of courseInstances in the DB
	*/
	function checkDuplicateReactivation($dept, $courseNumber, $section, $term, $year)
	{
	
		//If match on dept, #, section AND course instance is currently active, 
			//display text: "This class already exists and is active for [TERM]. The class is taught by [INSTRUCTORS]." 
			//If instructor=user logged in or user role=staff, display link "Go to class." 
			//If instructor does not = user, display "Contact your reserves staff for assistance with this class."

		//If match on dept, #, section AND course instance is NOT currently active, 
			//display text: "This class already exists and was active for [TERM]. The class was taught by [INSTRUCTORS]." 
			//If instructor=user or user role=staff, display link "Reactivate class." 
			//If instructor does not = user, display "Contact your reserves staff for assistance with this class."

		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql_checkCI	 	= "SELECT course_instance_id FROM course_instances WHERE term = ? AND year = ! AND course_instance_id = !";
		}

		$tempDuplicateCIs = $this->checkDuplicateClass($dept, $courseNumber, $section);
		$duplicateReactivations = array();
		
		if ($tempDuplicateCIs) {
			for ($i=0; $i<count($tempDuplicateCIs); $i++) {
				$rs = $g_dbConn->query($sql_checkCI, array($term, $year, $tempDuplicateCIs[$i]->getCourseInstanceID()));		
				if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
				
				$row = $rs->fetchRow();
				if ($row[0]) $duplicateReactivations[] = $tempDuplicateCIs[$i];
			}
			if (!empty($duplicateReactivations)) {
				return $duplicateReactivations;
			} else {
				return false;
			}
						
		} else {
			return false;
		}
	}
	
}	
?>