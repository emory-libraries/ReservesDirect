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