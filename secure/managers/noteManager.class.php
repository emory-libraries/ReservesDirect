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
require_once("secure/classes/note.class.php");
require_once("secure/displayers/noteDisplayer.class.php");
require_once("secure/managers/itemManager.class.php");
class noteManager
{
	public $user;
	public $displayClass;
	public $displayFunction;
	public $argList;
	
	function display()
	{
		if (is_callable(array($this->displayClass, $this->displayFunction)))
			call_user_func_array(array($this->displayClass, $this->displayFunction), $this->argList);
	}
	
	
	function noteManager($cmd, $user, $reserveID)
	{
		global $ci;
		$this->displayClass = "noteDisplayer";

		switch ($cmd)
		{
			default:
			case 'addNote':
				$this->displayFunction = "displayAddNoteScreen";
				$this->argList = array($user, array('cmd'=>'saveNote', 'reserve_id'=>$reserveID));							
			break;
			
			case 'saveNote':
							
				if ($_REQUEST['noteText']) 
				{
					$noteText = trim($_REQUEST['noteText']);
					if ($noteText) {
						$reserve = new reserve($reserveID);
				
						$noteType = $_REQUEST['noteType'];
						
						if ($noteType=='Content' || $noteType=='Staff' || $noteType=='Copyright') {
							$reserve->getItem();
							$reserve->item->setNote($noteType, $noteText);
							
						} elseif ($noteType=='Instructor') {
							$reserve->setNote($noteType,$noteText);
						}
					}
				}		

				$this->displayFunction = "displaySuccess";
				$this->argList = array(null);							
			break;					
		}
	}
}
?>