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
require_once("secure/displayers/itemDisplayer.class.php");
require_once("secure/managers/classManager.class.php");
require_once("secure/managers/noteManager.class.php");

class itemManager
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
	
	
	function itemManager($cmd, $user)
	{
		global $g_permission, $page, $loc, $ci;
		
		$this->displayClass = "itemDisplayer";

		switch ($cmd)
		{
			case 'editItem':

				if (!isset($_REQUEST["Submit"]))		
				{
					$page = "manageClasses";
					$loc  = "home";
					
					if (isset($_REQUEST['deleteNote'])) {
						$note = new note($_REQUEST['deleteNote']);
						if ($note->getID()) {
							$note->destroy();
						}
					}
					
					$reserveID = $_REQUEST['reserveID'];
					
					$reserve = new reserve($reserveID);				
					$reserve->getItem();
					
					$this->displayFunction = 'displayEditItemScreen';
					$this->argList = array($reserve, $user);
				} else {
					if ($_REQUEST['rID']) {
						$reserve = new reserve($_REQUEST['rID']);
						$reserve->getItem();
						if ($_REQUEST['deactivateReserve']) $reserve->setStatus('INACTIVE');					
						if ($_REQUEST['activateReserve']) $reserve->setStatus('ACTIVE');
						if ($_REQUEST['month'] || $_REQUEST['day'] || $_REQUEST['year']) $reserve->setActivationDate($_REQUEST['year'] . '-' . $_REQUEST['month'] . '-' . $_REQUEST['day']);							
						if ($_REQUEST['title']) $reserve->item->setTitle($_REQUEST['title']);							
						if ($_REQUEST['author']) $reserve->item->setAuthor($_REQUEST['author']);
						if ($_REQUEST['url']) $reserve->item->setURL($_REQUEST['url']);
						if ($_REQUEST['performer']) $reserve->item->setPerformer($_REQUEST['performer']);							
						if ($_REQUEST['volumeTitle']) $reserve->item->setVolumeTitle($_REQUEST['volumeTitle']);
						if ($_REQUEST['volumeEdition']) $reserve->item->setVolumeEdition($_REQUEST['volumeEdition']);
						if ($_REQUEST['pagesTimes']) $reserve->item->setPagesTimes($_REQUEST['pagesTimes']);
						if ($_REQUEST['source']) $reserve->item->setSource($_REQUEST['source']);
						if ($_REQUEST['contentNotes']) $reserve->item->setContentNotes($_REQUEST['contentNotes']);

						if ($_REQUEST['itemNotes']) {
							$itemNotes = array_keys($_REQUEST['itemNotes']);
							foreach ($itemNotes as $itemNote)
							{
									$note = new note($itemNote);
								$note->setText($_REQUEST['itemNotes'][$itemNote]);
							}
						}
						
						if ($_REQUEST['instructorNotes']) {
							$instructorNotes = array_keys($_REQUEST['instructorNotes']);
							foreach ($instructorNotes as $instructorNote)
							{
									$note = new note($instructorNote);
									$note->setText($_REQUEST['instructorNotes'][$instructorNote]);
							}
						}
					} 
					// goto edit class
					classManager::classManager("editClass", $user, $adminUser=null, $_REQUEST);
					break;
				}
			break;
		}	
	}
}

?>