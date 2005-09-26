<?
/*******************************************************************************

ReservesDirect

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

Created by Kathy A. Washington (kawashi@emory.edu)

ReservesDirect is located at:
http://coursecontrol.sourceforge.net/

*******************************************************************************/
require_once("secure/common.inc.php");
require_once("secure/classes/users.class.php");
require_once("secure/displayers/checkDuplicatesDisplayer.class.php");

class checkDuplicatesManager
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
	
	
	function checkDuplicatesManager($cmd, $user, $duplicatesArray)
	{
		global $g_permission, $page, $loc;
		
		
		$this->displayClass = "checkDuplicatesDisplayer";

		switch ($cmd)
		{
			case 'checkDuplicateClass':
				$page = "manageClasses";
				$loc = "create class";
				
				$errorType='courseInstance';
				
				$this->displayFunction = 'displayDuplicateError';
				$this->argList = array($user, $errorType, $duplicatesArray);

			break;
			
			case 'checkDuplicateReactivation':
				$page = "manageClasses";
				$loc = "reactivate class";
				
				$errorType='reactivation';
				
				$this->displayFunction = 'displayDuplicateError';
				$this->argList = array($user, $errorType, $duplicatesArray);
			
			break;

		}	
	}
}

?>