<?
/*******************************************************************************
exportManager.class.php

Created by Jason White (jbwhite@emory.edu)

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
require_once("secure/classes/courseInstance.class.php");
require_once("secure/displayers/exportDisplayer.class.php");

class exportManager extends baseManager {
	public $user;

	function exportManager($cmd) {
		global $g_permission, $page, $loc, $ci, $u;

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