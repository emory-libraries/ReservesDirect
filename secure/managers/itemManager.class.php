<?
/*******************************************************************************
itemManager.class.php

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
						if ($_REQUEST['author']) $reserve->item->setAuthor($_REQUEST['author']); else $reserve->item->setAuthor("");
						if ($_REQUEST['url']) $reserve->item->setURL($_REQUEST['url']); else $reserve->item->setURL("");
						if ($_REQUEST['performer']) $reserve->item->setPerformer($_REQUEST['performer']); else $reserve->item->setPerformer("");
						if ($_REQUEST['volumeTitle']) $reserve->item->setVolumeTitle($_REQUEST['volumeTitle']); else $reserve->item->setVolumeTitle("");
						if ($_REQUEST['volumeEdition']) $reserve->item->setVolumeEdition($_REQUEST['volumeEdition']); else $reserve->item->setVolumeEdition("");
						if ($_REQUEST['pagesTimes']) $reserve->item->setPagesTimes($_REQUEST['pagesTimes']); else $reserve->item->setPagesTimes("");
						if ($_REQUEST['source']) $reserve->item->setSource($_REQUEST['source']); else $reserve->item->setSource("");
						if ($_REQUEST['contentNotes']) $reserve->item->setContentNotes($_REQUEST['contentNotes']); else $reserve->item->setContentNotes("");

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