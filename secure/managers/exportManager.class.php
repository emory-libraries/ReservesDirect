<?
/*******************************************************************************
exportManager.class.php

Created by Jason White (jbwhite@emory.edu)

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
require_once("secure/classes/courseInstance.class.php");
require_once("secure/displayers/exportDisplayer.class.php");

class exportManager extends baseManager {
	public $user;

	function exportManager($cmd) {
		global $g_permission, $g_siteURL, $page, $loc, $ci, $u;

		switch ($cmd) {
			case 'generateBB':
				$ci = new courseInstance($_REQUEST['ci']);
				$ci->getPrimaryCourse();

				$filename = 'blackboard_' . $ci->course->displayCourseNo() . '.html';
				$filename = ereg_replace(" ", "_", $filename);
				
				$data = "<HTML>\n";
				$data .= "	<HEAD>\n";		
				$data .= "		<script src=\"". $g_siteURL."/reservelist.php?style=reserves&ci=". $ci->getCourseInstanceID() ."\"></script>\n";		
				$data .= "	</HEAD>\n";
				$data .= "</HTML>\n";		
				
				
				
				ob_clean();
				
				header("Content-Type: text/plain");
				header("Content-Disposition: attachment; filename=\"$filename\"");
				
				echo $data;
				exit;
			break;		
				
			default:
				$this->displayClass = "exportDisplayer";
				$loc = "export class";
				//set the page (tab)
				if($u->getRole() >= $g_permission['staff']) {
					$page = 'manageclasses';
				}
				else {
					$page = 'myReserves';
				}
				
				if(empty($_REQUEST['ci'])) {	//get ci
					//get array of CIs (ignored for staff)
					$courses = $u->getCourseInstancesToEdit();
					
					$this->displayFunction = 'displaySelectClass';
					$this->argList = array($cmd, $courses, 'Select class to export');
				}
				elseif(empty($_REQUEST['course_ware'])) {	//get export option
					$course = new courseInstance($_REQUEST['ci']);
					
					$this->displayFunction = 'displaySelectExportOption';
					$this->argList = array($course);
				}
				else {
					$course = new courseInstance($_REQUEST['ci']);
					$course->getCourseForUser();
					
					$this->displayFunction = 'displayExportInstructions_'.$_REQUEST['course_ware'];
					$this->argList = array($course);
				}
		}
	}
	
}
