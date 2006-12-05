<?
/*******************************************************************************
lookupManager.class.php


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
			case 'lookupInstructor':
				
				if (isset($request['select_instr_by']) && isset($request['instr_qryTerm'])) //user is searching for an instructor
				{
					$users = new users();
					$users->search($request['select_instr_by'], $request['instr_qryTerm'], $g_permission['proxy']);
					$instr_list = $users->userList;
				} else $instr_list = null;


				$this->displayFunction = 'instructorLookup';
				$this->argList = array($instr_list, $request);
			break;
		}

	}
}

?>
