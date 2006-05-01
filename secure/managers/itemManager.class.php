<?
/*******************************************************************************
itemManager.class.php

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
require_once("secure/displayers/itemDisplayer.class.php");
require_once("secure/managers/classManager.class.php");
require_once("secure/managers/copyrightManager.class.php");
require_once('secure/managers/reservesManager.class.php');
require_once('secure/classes/note.class.php');

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
		global $g_permission, $g_documentURL, $page, $loc, $ci, $u;

		$this->displayClass = "itemDisplayer";
		
		switch ($cmd)
		{
			
			// *
			// This case depends on editItem and so MUST COME IMMEDIATELY BEFORE `case 'editItem':`			
			// *			
			case 'duplicateReserve':	//duplicates reserve AND item
				if(empty($_REQUEST['reserveID']))
					break;	//error, no reserveID set
								
				//get the source reserve
				$srcReserve = new reserve($_REQUEST['reserveID']);				
				//duplicate it
				$dupReserveID = $srcReserve->duplicateReserve();				
				
				//set up some vars
				
				$selected_instr = $_REQUEST['selected_instr'];	//remember instructor
				
				$_REQUEST = array();	//clear current request
				
				$_REQUEST['reserveID'] = $dupReserveID;	//set up new reserveID
				$_REQUEST['dubReserve'] = true;	//set flag, to let editItem handler know this is a dupe
				$_REQUEST['selected_instr'] = $selected_instr;	//set instructor

			//use editItem
			//no break!
						
			case 'editItem':
				//switch b/n editing item or editing reserve+item
				if(!empty($_REQUEST['reserveID'])) {	//editing item+reserve
					//get reserve
					$reserve = new reserve($_REQUEST['reserveID']);
					//get item
					$item = new reserveItem($reserve->getItemID());
					
					//init a courseInstance to show location				
					$ci = new courseInstance($reserve->getCourseInstanceID());
				}
				elseif(!empty($_REQUEST['itemID'])) {	//editing item only
					$item = new reserveItem($_REQUEST['itemID']);
				}
				else {	//no IDs set, error
					break;
				}
				
				//form submitted - edit item meta
				if(!empty($_REQUEST['submit_edit_item_meta'])) {
					//were we editing a reserve?
					if($reserve instanceof reserve) {	//set some data;
						//set status
						$reserve->setStatus($_REQUEST['reserve_status']);
						
						//set dates, if status is ACTIVE
						if($_REQUEST['reserve_status']=='ACTIVE') {
							//if not empty, set activation and expiration dates
							//try to convert dates to proper format
							if(!empty($_REQUEST['reserve_activation_date'])) {
								$reserve->setActivationDate(date('Y-m-d', strtotime($_REQUEST['reserve_activation_date'])));
							}
							if(!empty($_REQUEST['reserve_expiration_date'])) {
								$reserve->setExpirationDate(date('Y-m-d', strtotime($_REQUEST['reserve_expiration_date'])));
							}		
						}
						
						//set parent heading
						if(!empty($_REQUEST['heading_select'])) {
							$reserve->setParent($_REQUEST['heading_select']);
							
							//try to insert into sort order
							$reserve->getItem();
							$reserve->insertIntoSortOrder($ci->getCourseInstanceID(), $reserve->item->getTitle(), $reserve->item->getAuthor(), $_REQUEST['heading_select']);
						}	
					}
					
					//if editing electronic item, manage files
					if(isset($_REQUEST['documentType'])) {						
						if($_REQUEST['documentType'] == 'DOCUMENT') {	//uploaded file?
							$file = common_storeUploaded($_FILES['userFile'], $item->getItemID());
							
							$file_loc = $file['dir'] . $file['name'] . $file['ext'];
							$item->setURL($file_loc);
							$item->setMimeTypeByFileExt($file['ext']);
						}
						elseif($_REQUEST['documentType'] == 'URL') {	//link?
							$item->setURL($_REQUEST['url']);
						}
						//else maintaining the same link; do nothing
					}
					
					//set item data
					$item->setTitle($_REQUEST['title']);
					$item->setAuthor($_REQUEST['author']);
					$item->setPerformer($_REQUEST['performer']);
					$item->setDocTypeIcon($_REQUEST['selectedDocIcon']);
					$item->setVolumeTitle($_REQUEST['volumeTitle']);
					$item->setVolumeEdition($_REQUEST['volumeEdition']);
					$item->setPagesTimes($_REQUEST['pagesTimes']);
					$item->setSource($_REQUEST['source']);					
					//physical item data
					if($item->isPhysicalItem()) {
						$item->setHomeLibraryID($_REQUEST['home_library']);
						
						//physical copy data
						if($item->getPhysicalCopy()) {	//returns false if not a physical copy
							//only set these if they were part of the form
							if(isset($_REQUEST['barcode'])) $item->physicalCopy->setBarcode($_REQUEST['barcode']);
							if(isset($_REQUEST['call_num'])) $item->physicalCopy->setCallNumber($_REQUEST['call_num']);
						}
					}
					
					//personal copies
					if($_REQUEST['personal_item'] == 'no') {	//do not want a private owner
						$item->setPrivateUserID('null');
					}
					elseif($_REQUEST['personal_item'] == 'yes') {	//we want a private owner
						//if we are choosing a new private owner, set it
						if( ($_REQUEST['personal_item_owner']=='new') && !empty($_REQUEST['selected_owner']) ) {
							$item->setprivateUserID($_REQUEST['selected_owner']);
						}
						//else we are keeping old private owner, so no change necessary
					}
					
					//notes
					if(!empty($_REQUEST['notes'])) {
						foreach($_REQUEST['notes'] as $note_id=>$note_text) {
							if(!empty($note_id)) {
								$note = new note($note_id);
								$note->setText($note_text);
							}
						}
					}
					
					//if duplicating, show a different success screen
					if($_REQUEST['dubReserve']) {
						//get course instance
						$ci = new courseInstance($reserve->getCourseInstanceID());
						$ci->getPrimaryCourse();

						//call requestDisplayer method
						require_once("secure/displayers/requestDisplayer.class.php");
						$loc = 'add an item';
						$this->displayClass = 'requestDisplayer';
						$this->displayFunction = 'addSuccessful';
						//reserve needs to be in an array
						$this->argList = array($user, array($reserve), $ci, $_REQUEST['selected_instr'], true);
					}
					else {
						//get courseinstance id, if editing reserve
						$ci_id = ($reserve instanceof reserve) ? $reserve->getCourseInstanceID() : null;
						
						// display success
						$this->displayFunction = 'displayItemSuccessScreen';
						$this->argList = array($ci_id, urlencode($_REQUEST['search']));
					}
				}
				elseif(!empty($_REQUEST['submit_edit_item_copyright'])) {	//form submitted - edit item copyright
					switch($_REQUEST['form_id']) {
						case 'copyright_status':
							copyrightManager::setStatus();						
						break;
						
						case 'copyright_supporting_items_delete':
							copyrightManager::deleteSupportingItem();
						break;
						
						case 'copyright_supporting_items_add':
							copyrightManager::addSupportingItem();
						break;
					}
					
					//go back to edit item copyright screen
					$page = "addReserve";
					$loc  = "edit item";					
					$this->displayFunction = 'displayEditItem';
					$this->argList = array($item, $reserve);
				}
				else {	//display edit page
					$page = "addReserve";
					$loc  = "edit item";
					
					$this->displayFunction = 'displayEditItem';
					$this->argList = array($item, $reserve, array('dubReserve'=>$_REQUEST['dubReserve'], 'selected_instr'=>$_REQUEST['selected_instr']));
				}			
			break;

			case 'editHeading':
				$page = ($u->getRole() >= $g_permission['staff']) ? 'manageClasses' : 'addReserve';
				$loc = "edit heading";
				
				$headingID = !empty($_REQUEST['headingID']) ? $_REQUEST['headingID'] : null;
				$heading = new reserve($headingID);
				
				$this->displayFunction = 'displayEditHeadingScreen';
				$this->argList = array($_REQUEST['ci'], $heading);
			break;
			
			case 'processHeading':
				$page = "myReserves";
				$loc = "edit heading";

				$ci = new courseInstance($_REQUEST['ci']);
				$headingText = $_REQUEST['heading'];
				$headingID = $_REQUEST['headingID'];
				
				if(empty($headingID)) {	//need to create a new item
					if ($headingText) {
						$heading = new item();				
						$heading->createNewItem();
						$heading->makeHeading();
						$reserve = new reserve();
						$reserve->createNewReserve($ci->getCourseInstanceID(), $heading->itemID);
						$reserve->setStatus('ACTIVE');
						$reserve->setActivationDate($ci->activationDate);
						$reserve->setExpirationDate($ci->expirationDate);
						$reserve->insertIntoSortOrder($ci->getCourseInstanceID(), $headingText, 'zzzzz');	//zzzz will put the heading last if author-sorted
					}
				}
				else {
					$heading = new item($headingID);
				}
				
				if ($headingText)
					$heading->setTitle($headingText);
					
				//notes
				if(!empty($_REQUEST['notes'])) {
					foreach($_REQUEST['notes'] as $note_id=>$note_text) {
						if(!empty($note_id)) {
							$note = new note($note_id);
							$note->setText($note_text);
						}
					}
				}
				
				$this->displayFunction = 'displayHeadingSuccessScreen';
				$this->argList = array($_REQUEST['ci']);
			break;
		}	
	}
}

?>
