<?
/*******************************************************************************
copyrightManager.class.php


Created by Dmitriy Panteleyev (dpantel@emory.edu)

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