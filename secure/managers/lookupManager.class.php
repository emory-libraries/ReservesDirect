<?
/*******************************************************************************
lookupManager.class.php


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
require_once("secure/common.inc.php");
require_once("secure/classes/users.class.php");
require_once("secure/displayers/lookupDisplayer.class.php");

class lookupManager
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


	function lookupManager($tableHeading="CLASS LOOKUP", $cmd, $user, $request, $hidden_fields=null)
	{
		global $g_permission, $page, $loc;

		$this->displayClass = "lookupDisplayer";

		switch ($cmd)
		{
			case 'lookupClass':
				if (isset($request['select_instr_by']) && isset($request['instr_qryTerm'])) //user is searching for an instructor
				{
					$users = new users();
					$users->search($request['select_instr_by'], $request['instr_qryTerm'], 'proxy');
					$instr_list = $users->userList;
				} else $instr_list = null;

				if (isset($request['selected_instr'])) //user has selected an instructor will override dept selection
				{
					$course_list = $user->getCoursesByInstructor($request['selected_instr']);
				} else $course_list = null;

				if (isset($request['select_course'])) // user has selected a course look of course instances
				{
					$ci_list = $user->getCourseInstancesByCourse($request['select_course'], $request['selected_instr']);
				} else $ci_list = null;

				$this->displayFunction = 'classLookup';
				$this->argList = array($tableHeading, $instr_list, $course_list, $ci_list, $request, $hidden_fields);
			break;

			case 'lookupInstructor':
				
				if (isset($request['select_instr_by']) && isset($request['instr_qryTerm'])) //user is searching for an instructor
				{
					$users = new users();
					$users->search($request['select_instr_by'], $request['instr_qryTerm'], 'proxy');
					$instr_list = $users->userList;
				} else $instr_list = null;


				$this->displayFunction = 'instructorLookup';
				$this->argList = array($instr_list, $request);
			break;
		}

	}
}

?>