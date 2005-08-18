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
require_once("secure/managers/reservesManager.class.php");

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
				$itemID = $_REQUEST['itemID'];
				$item = new reserveItem($itemID);
			
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
									
					$this->displayFunction = 'displayEditItemScreen';
					$this->argList = array($item, $user, $_REQUEST['sql']);
				} else {
					if ($_REQUEST['title']) $item->setTitle($_REQUEST['title']);
					if ($_REQUEST['author']) $item->setAuthor($_REQUEST['author']); else $item->setAuthor("");					
					if ($_REQUEST['performer']) $item->setPerformer($_REQUEST['performer']); else $item->setPerformer("");
					if ($_REQUEST['volumeTitle']) $item->setVolumeTitle($_REQUEST['volumeTitle']); else $item->setVolumeTitle("");
					if ($_REQUEST['volumeEdition']) $item->setVolumeEdition($_REQUEST['volumeEdition']); else $item->setVolumeEdition("");
					if ($_REQUEST['pagesTimes']) $item->setPagesTimes($_REQUEST['pagesTimes']); else $item->setPagesTimes("");
					if ($_REQUEST['source']) $item->setSource($_REQUEST['source']); else $item->setSource("");
					if ($_REQUEST['contentNotes']) $item->setContentNotes($_REQUEST['contentNotes']); else $item->setContentNotes("");
					
					$item->setDocTypeIcon($_REQUEST['selectedDocIcon']);
					
					// Check to see if this was a valid file they submitted
					if ($_REQUEST['documentType'] == 'DOCUMENT')
					{
						if ($_FILES['userFile']['error'])
							trigger_error("Possible file upload attack. Filename: " . $_FILES['userFile']['name'] . "If you are trying to load a very large file (> 10 MB) contact Reserves to add the file.", E_USER_ERROR);

						list($filename, $type) = split("\.", $_FILES['userFile']['name']);
						//move file set permissions and store location
						//position uploaded file so that common_move and move it
						move_uploaded_file($_FILES['userFile']['tmp_name'], $_FILES['userFile']['tmp_name'] . "." . $type);
						chmod($_FILES['userFile']['tmp_name'] . "." . $type, 0644);

						$newFileName = ereg_replace('[^A-Za-z0-9]*','',$filename); //strip any non A-z or 0-9 characters

						//$newFileName = str_replace("&", "_", $filename); 											//remove & in filenames
						//$newFileName = str_replace(".", "", $newFileName); 											//remove . in filenames
						$newFileName = $item->getItemID() ."-". str_replace(" ", "_", $newFileName . "." . $type); 	//remove spaces in filenames
						common_moveFile($_FILES['userFile']['tmp_name'] . "." . $type,  $newFileName );
						$item->setURL($g_documentURL . $newFileName);
						$item->setMimeTypeByFileExt($type);
					} else {
						if ($_REQUEST['url']) $item->setURL($_REQUEST['url']); else $item->setURL("");
					}			
					
					if ($_REQUEST['itemNotes']) {
						$itemNotes = array_keys($_REQUEST['itemNotes']);
						foreach ($itemNotes as $itemNote)
						{
								$note = new note($itemNote);
								$note->setText($_REQUEST['itemNotes'][$itemNote]);
						}
					}
					
					// display success
					$this->displayFunction = 'displayItemSuccessScreen';
					$this->argList = array($_REQUEST['sql'], $user);
					break;
				}			
			break;
			
			case 'editReserve':
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
					
					$docTypeIcons = $user->getAllDocTypeIcons();
					
					$this->displayFunction = 'displayEditReserveScreen';
					$this->argList = array($reserve, $user, $docTypeIcons);
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
						
						$reserve->item->setDocTypeIcon($_REQUEST['selectedDocIcon']);
						
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
			
			case 'editHeading':
				$page = "myReserves";
				$loc = "edit heading";
				
				if (isset($_REQUEST['deleteNote'])) {
						$note = new note($_REQUEST['deleteNote']);
						if ($note->getID()) {
							$note->destroy();
						}
					}
				
				$ci = $_REQUEST['ci'];
				if (isset($_REQUEST['headingID']) && $_REQUEST['headingID']!="" && $_REQUEST['headingID']!=null)
					$headingID = $_REQUEST['headingID'];
				else 
					$headingID = null;
					
				$heading = new reserve($headingID);
				
				$this->displayFunction = 'displayEditHeadingScreen';
				$this->argList = array($ci, $heading);
			break;
			
			case 'processHeading':
				$page = "myReserves";
				$loc = "edit heading";
				
				
				$ci = new courseInstance($_REQUEST['ci']);
				$nextAction = $_REQUEST['nextAction'];
				$headingText = $_REQUEST['heading'];
				$headingID = $_REQUEST['headingID'];
				
				if ($headingID="" || $headingID==null) {
					if ($headingText) {
						$heading = new item($headingID);
						$heading->createNewItem();
						$heading->makeHeading();
						$reserve = new reserve();
						$reserve->createNewReserve($ci->courseInstanceID, $heading->itemID);
						$reserve->setStatus('ACTIVE');
						$reserve->setActivationDate($ci->activationDate);
						$reserve->setExpirationDate($ci->expirationDate);
					}
				} else {
					$heading = new item($_REQUEST['headingID']);
				}
				
				if ($headingText)
					$heading->setTitle($headingText);

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
				
				switch ($nextAction)
				{
					case 'editClass':
						classManager::classManager("editClass", $user, $adminUser=null, $_REQUEST);
					break;
					
					case 'editHeading':
						$this->displayFunction = 'displayEditHeadingScreen';
						$heading = new reserve();
						$this->argList = array($ci->courseInstanceID, $heading);
					break;
					
					case 'customSort':
						reservesManager::reservesManager("customSort", $user);
					break;
				}

			break;
		}	
	}
}

?>