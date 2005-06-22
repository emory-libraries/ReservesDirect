<?
/*******************************************************************************
noteManager.class.php


Created by Kathy Washington (kawashi@emory.edu)

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