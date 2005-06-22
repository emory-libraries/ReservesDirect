<?
/*******************************************************************************
exportManager.class.php

Created by Jason White (jbwhite@emory.edu)

This file is part of GNU ReservesDirect 2.1

Copyright (c) 2004-2005 Emory University, Atlanta, Georgia.

ReservesDirect 2.1 is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

ReservesDirect 2.1 is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with ReservesDirect 2.1; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

Reserves Direct 2.1 is located at:
http://www.reservesdirect.org/

*******************************************************************************/
require_once("secure/common.inc.php");
require_once("secure/displayers/exportDisplayer.class.php");

class exportManager
{
	public $user;
	public $displayClass;
	public $displayFunction;
	public $argList;

	function display()
	{
		//echo "attempting to call ". $this->displayClass ."->". $this->displayFunction . "<br>"; print_r($this->argList); echo "<br>";

		if (is_callable(array($this->displayClass, $this->displayFunction)))
			call_user_func_array(array($this->displayClass, $this->displayFunction), $this->argList);

	}

	function exportManager($cmd, $u, $request)
	{
		global $g_permission, $page, $loc, $ci;

		$this->displayClass = "exportDisplayer";
		$loc = "export class";
		$page = "manageClasses";


		if (!isset($request['course_ware']) || !isset($request['ci']))
		{
			$classList = null;
			if ($u instanceof instructor)
			{
				$classList = $u->getAllCourseInstances();

				for($i=0;$i<count($classList);$i++)
					$classList[$i]->getPrimaryCourse();
			}

			$this->displayFunction = 'displayExportSelectClass';
			$this->argList = array($classList, array('cmd'=>'exportClass', 'selected_instr'=>$request['selected_instr']));
		} else {
			$this->displayFunction = 'displayExportInstructions_' . $request['course_ware'];
			$this->argList = array($ci);
		}

	}

}