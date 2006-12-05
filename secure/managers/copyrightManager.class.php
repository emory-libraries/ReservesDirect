<?
/*******************************************************************************
copyrightManager.class.php


Created by Dmitriy Panteleyev (dpantel@emory.edu)

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
require_once('secure/classes/copyright.class.php');
require_once('secure/classes/reserveItem.class.php');

class copyrightManager {
	
	
	/**
	 * @return void
	 * @desc uses form values in $_REQUEST to set the copyright status
	 */
	public function setStatus() {
		if(empty($_REQUEST['item_id'])) {
			return false;
		}
		
		//init an empty copyright object
		$copyright = new Copyright();
		
		//handle copyright status basis
		$status_basis = null;
		if(isset($_REQUEST['copyright_status_basis_id'])) { //trying to set a basis
			if(!empty($_REQUEST['copyright_status_basis_id'])) {	//trying to pick an existing basis
				$status_basis = $_REQUEST['copyright_status_basis_id'];
			}
			elseif(!empty($_REQUEST['copyright_status_basis_new'])) {	//trying to create a new basis
				//create a new basis
				$status_basis = $copyright->createNewStatusBasis($_REQUEST['copyright_status'], $_REQUEST['copyright_status_basis_new']);
				//use it if created successfully
				$status_basis = ($status_basis!==false) ? $status_basis : null;
			}
		}

		//update copyright status						
		if($copyright->getByItemID($_REQUEST['item_id'])) {	//record found, update
			$copyright->setStatus($_REQUEST['copyright_status'], $status_basis);
		}
		else {	//no record exists, create
			$copyright->createNewRecord($_REQUEST['item_id'], $_REQUEST['copyright_status'], $status_basis);
		}
	}
	
	
	/**
	 * @return boolean
	 * @desc uses value from $_REQUEST to set contact; returns true on success
	 */
	public function setContact() {
		if(empty($_REQUEST['item_id']) || empty($_REQUEST['contact_id'])) {
			return false;
		}
	
		$copyright = new Copyright();
		if($copyright->getByItemID($_REQUEST['item_id'])) {	//record found, update
			$copyright->setContact($_REQUEST['contact_id']);
			return true;
		}
		else {
			return false;
		}
	}
	
	
	/**
	 * @return boolean
	 * @desc deletes supporting item from copyright record
	 */
	public function deleteSupportingItem() {
		$copyright = new Copyright();
		if($copyright->getByItemID($_REQUEST['item_id'])) {	//record found, update
			return $copyright->deleteSupportingItem($_REQUEST['delete_file_id']);
		}
		else {
			return false;
		}
	}
	
	
	/**
	 * @return boolean
	 * @desc adds supporting item to copyright record
	 */
	public function addSupportingItem() {
		$copyright = new Copyright();
		if($copyright->getByItemID($_REQUEST['item_id'])) {	//record found, update			
			if(!empty($_REQUEST['url']) || !empty($_FILES['userFile'])) {
				//create item
				$supp_item = new reserveItem();
				$supp_item->createNewItem();
				
				//set title
				$supp_item->setTitle($_REQUEST['file_title']);
				//set fake private user
				$supp_item->setPrivateUserID(0);
			
				//handle the file/url
				if(($_REQUEST['file_source_option'] == 'file') &&  !empty($_FILES['userFile'])) {	//uploaded file
					$file = common_storeUploaded($_FILES['userFile'], $supp_item->getItemID());
					
					$file_loc = $file['dir'] . $file['name'] . $file['ext'];
					$supp_item->setURL($file_loc);
					$supp_item->setMimeTypeByFileExt($file['ext']);
				}
				elseif(($_REQUEST['file_source_option'] == 'url') && !empty($_REQUEST['url'])) {	//link?
					$supp_item->setURL($_REQUEST['url']);
				}
			
				//add it to copyright record
				return $copyright->addSupportingItem($supp_item->getItemID());
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}
}
