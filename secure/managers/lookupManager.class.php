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

Created by Jason White (jbwhite@emory.edu)

Reserves Direct 2.0 is located at:
http://coursecontrol.sourceforge.net/

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
				$this->argList = array($instr_list, $request, $hidden_fields);
			break;
		}

	}
}

?>