<?
/*******************************************************************************
requestManager.class.php


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
require_once("secure/classes/itemAudit.class.php");
require_once("secure/classes/users.class.php");
require_once("secure/classes/zQuery.class.php");
require_once("secure/displayers/requestDisplayer.class.php");
require_once('secure/classes/note.class.php');

class requestManager
{
	public $user;
	public $displayClass;
	public $displayFunction;
	public $argList;

	function display()
	{
		//echo "attempting to call requestManager ". $this->displayClass ."->". $this->displayFunction ."<br>";

		if (is_callable(array($this->displayClass, $this->displayFunction)))
			call_user_func_array(array($this->displayClass, $this->displayFunction), $this->argList);

	}


	function requestManager($cmd, $user, $ci, $request)
	{
		global $g_permission, $page, $loc, $ci, $alertMsg, $g_documentURL, $g_notetype;

		$this->displayClass = "requestDisplayer";

		switch ($cmd)
		{
			case 'printRequest':			
				$page = "manageClasses";

				$loc  = "process request";

				$unit = (!isset($request['unit'])) ? $user->getStaffLibrary() : $request['unit'];
				
				for($i=0;$i<count($request['selectedRequest']);$i++)
				{
		 			$tmpRequest = new request($request['selectedRequest'][$i]);
					$tmpRequest->getRequestedItem();
					$tmpRequest->getRequestingUser();
					$tmpRequest->getReserve();
					$tmpRequest->getCourseInstance();
					$tmpRequest->courseInstance->getPrimaryCourse();
					$tmpRequest->courseInstance->getCrossListings();
					$tmpRequest->getHoldings();
					$requestList[] = $tmpRequest;
				}					
				
			
				for($i=0;$i<count($requestList);$i++)
				{
					$item = $requestList[$i]->requestedItem;
					$item->getPhysicalCopy();

					$requestList[$i]->courseInstance->getInstructors();
					$requestList[$i]->courseInstance->getCrossListings();								
				}

				$this->displayFunction = 'printSelectedRequest';
				$this->argList = array($requestList, $user->getLibraries(), $request, $user);				
			break;
			
			case 'deleteRequest':
				$requestObj = new request($request['request_id']);
				$reserve = new reserve($requestObj->getReserveID());
				$reserve->destroy();
				$requestObj->destroy();

			case 'displayRequest':			
				$page = "addReserve";

				$loc  = "process request";

				$unit = (!isset($request['unit']) || $request['unit'] == "") ? $user->getStaffLibrary() : $request['unit'];
				
				$requestList = $user->getRequests($unit, $request['sort']);				
							
				for($i=0;$i<count($requestList);$i++)
				{
					$requestList[$i]->requestedItem->getPhysicalCopy();
					$requestList[$i]->courseInstance->getInstructors();
					$requestList[$i]->courseInstance->getCrossListings();								
				}

				$this->displayFunction = 'displayAllRequest';
				$this->argList = array($requestList, $user->getLibraries(), $request, $user);
			break;

			case 'storeRequest':
				//initialize array/string to pass information to success screens
				$reserves = array();
				$ilsResults = '';
				
				//item_count - number of item/reserve/physical_copy records which will be added to the DB
				//physical items will have as many records as boxes checked on the form, manual/digital items will have one record
				$item_count = 0;
				if( ($request['previous_cmd'] == 'addDigitalItem') || ($request['addType']=='MANUAL') ) {	//add electronic/manual item
					//if electronic or manual, there is only one item
					$item_count = 1;
				}
				elseif( ($request['previous_cmd'] == 'addPhysicalItem') || ($request['previous_cmd'] == 'processRequest') ) {	//add physical item
					//if storing physical items, get item count
					$item_count = count($request['physical_copy']);
				}
				
				//if processing request, we need to know the reserve for which the request was originated
				//if the physical copy matching this reserve was selected, then it should be processed
				//if no physical copies match (i.e. different copy was substituted), then this reserve should be removed
				//also, reserves created for multiple copies will get this reserve's sort order and parent
				if($request['previous_cmd'] == 'processRequest') {
					$req = new request($request['request_id']);
					$originally_requested_reserve = new reserve($req->getReserveID());
					$originally_requested_reserve->processed = false;	//flag this reserve as not-processed
					unset($req);			
				}

				//loop, to process all items
				for($x=0; $x<$item_count; $x++) {
					//create new objects
					$item = new reserveItem();
					$reserve = new reserve();
					
					//get existing item/reserve, or create new; handle request if necessary

					if( ($request['previous_cmd'] == 'addDigitalItem') || ($request['addType']=='MANUAL') ) {	//add electronic/manual item
						//get existing item or create new
						if( !empty($request['item_id']) ) {
							$item->getItemByID($request['item_id']);
						}
						else {
							$item->createNewItem();
						}

						//try to find existing reserve
						if( $reserve->getReserveByCI_Item($ci->getCourseInstanceID(), $item->getItemID()) === false ) {
							//if querying old reserve returns nothing, create new
							$reserve->createNewReserve($ci->getCourseInstanceID(), $item->getItemID());
							$itemAudit = new itemAudit();
							$itemAudit->createNewItemAudit($item->getItemID(),$user->getUserID());
						}
					}
					elseif( ($request['previous_cmd'] == 'addPhysicalItem') || ($request['previous_cmd'] == 'processRequest') ) {	//physical item
						$physCopy = new physicalCopy();
						//pull physical copy info
						$phys_item = unserialize(urldecode($request['physical_copy'][$x]));

						//look for existing item based on barcode
						if( $physCopy->getByBarcode($phys_item['bar']) != null ) {
							//get item
							$item->getItemByID($physCopy->getItemID());
							//get reserve by item and course
							if( $reserve->getReserveByCI_Item($ci->getCourseInstanceID(), $item->getItemID()) !== false ) {
								//check if this matches the originally requested reserve
								if(($originally_requested_reserve instanceof reserve) && ($reserve->getReserveID()==$originally_requested_reserve->getReserveID())) {
									$originally_requested_reserve->processed = true;	//mark as processed									
								}
								
								//look for a request for this reserve item
								$requestObj = new request();
								if( $requestObj->getRequestByReserveID($reserve->getReserveID()) !== false ) {
									//process request
									$requestObj->setDateProcessed(date('Y-m-d'));									
								}
								else {
									unset($requestObj);
								}
							}
							else {	//item exists but no reserve for this course
								//create new reserve
								$reserve->createNewReserve($ci->getCourseInstanceID(), $item->getItemID());
							}
						}
						else {	//the item is not in the DB
							//create new item
							$item->createNewItem();
							//create new physical item
							$physCopy->createPhysicalCopy();
							//create new reserve
							$reserve->createNewReserve($ci->getCourseInstanceID(), $item->getItemID());
							//store some stats
							$itemAudit = new itemAudit();
							$itemAudit->createNewItemAudit($item->getItemID(),$user->getUserID());
						}					
					}

					//set item & reserve data
					
					//dates
					$reserve->setActivationDate(date('Y-m-d', strtotime($request['reserve_activation_date'])));
					$reserve->setExpirationDate(date('Y-m-d', strtotime($request['reserve_expiration_date'])));

					//if adding multiple items, check display preference
					if( ($request['selectItemsToDisplay']=='one') && ($x>0) ) {
						//if only want to show one copy and we've already been through the loop at least once, hide the item
						$reserve->setStatus('INACTIVE');
					}
					else {	//else set status to their preference
						$reserve->setStatus($request['status']);
					}

					if (isset($request['author'])) $item->setAuthor($request['author']);
					if (isset($request['controlKey'])) $item->setLocalControlKey($request['controlKey']);
					if (isset($request['performer'])) $item->setPerformer($request['performer']);
					if (isset($request['source'])) $item->setSource($request['source']);
					if (isset($request['title'])) $item->setTitle($request['title']);
					if (isset($request['volume_edition'])) $item->setVolumeEdition($request['volume_edition']);
					if (isset($request['home_library'])) $item->sethomeLibraryID($request['home_library']);
					if (isset($request['item_type'])) $item->setGroup($request['item_type']);
					if (isset($request['volume_title'])) $item->setVolumeTitle($request['volume_title']);
					if (isset($request['times_pages'])) $item->setPagesTimes($request['times_pages']);
					if (isset($request['selectedDocIcon'])) $item->setDocTypeIcon($request['selectedDocIcon']);
					//check personal item owner
					if( ($request['personal_item'] == 'yes') && !empty($request['selected_owner']) ) {
						$item->setPrivateUserID($request['selected_owner']);
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
					if(!empty($_REQUEST['new_note'])) {	//add new note
						if($_REQUEST['new_note_type'] == $g_notetype['instructor']) {
							$reserve->setNote($_REQUEST['new_note'], $_REQUEST['new_note_type']);
						}
						else {
							$item->setNote($_REQUEST['new_note'], $_REQUEST['new_note_type']);
						}
					}
					
					//if an originally-requested reserve exists, then match sort order and parent to it
					if($originally_requested_reserve instanceof reserve) {
						$reserve->setSortOrder($originally_requested_reserve->getSortOrder());
						$reserve->setParent($originally_requested_reserve->getParent());
					}
					else {
						//attempt to set a sort order for the reserve
						$reserve->insertIntoSortOrder($ci->getCourseInstanceID(), $item->getTitle(), $item->getAuthor());
					}

					//set physical item data (but not for manual entries)
					if( (($request['previous_cmd'] == 'addPhysicalItem') || ($request['previous_cmd'] == 'processRequest')) && ($request['addType']!='MANUAL') ) {
						//only set it if creating new entry						
						$phys_item_id = $physCopy->getItemID();	//have to do this, because empty() doesn't seem to work w/ object methods
						if( empty($phys_item_id) ) {
							$physCopy->setItemID($item->getItemID());
							$physCopy->setReserveID($reserve->getReserveID());
							$physCopy->setCallNumber($phys_item['callNum']);
							$physCopy->setStatus($phys_item['loc']);
							$physCopy->setItemType($phys_item['type']);
							$physCopy->setBarcode($phys_item['bar']);

							if (!is_null($item->getPrivateUserID()))
								$physCopy->setOwnerUserID($item->getPrivateUserID());

							$reserveDesk = new library($request['home_library']);
							//this should be reserveDesk
							$physCopy->setOwningLibrary($reserveDesk->getReserveDesk());							
						}

						//set euclid record

						//set up ILS record
						if( $request['euclid_record'] == 'yes' ) {
							if( isset($requestObj) ) {	//get instructor from request
								$instr = new instructor();
								$instr->getUserByID($requestObj->requestingUserID);
							}
							else {	//get instructor from course instance
								$ci->getInstructors();
								$instr = $ci->instructorList[0];
							}

							//populate fields
							$instr->getInstructorAttributes();

							//get loan periods
							$circRule = unserialize(urldecode($request['circRule']));

							//put item on reserve in ILS
							$ilsResult = $user->createILS_record($phys_item['bar'], $phys_item['copy'], $instr->getILSUserID(), $request['home_library'], $ci->getTerm(), $circRule['circRule'], $circRule['alt_circRule'], $ci->getExpirationDate());

							//store ilsResult for the future
							$ilsResults .= $ilsResult.'<br />';
						}
					}	//end physical item block
					elseif( $request['previous_cmd'] == 'addDigitalItem' ) {	//set electronic item data
						//uploading a file
						if( $request['documentType'] == 'DOCUMENT' ) {
							$file = common_storeUploaded($_FILES['userFile'], $item->getItemID());
							$item->setURL($g_documentURL.$file['name']);
							$item->setMimeTypeByFileExt($file['ext']);
						}
						else {	//adding a URL
							$item->setURL($request['url']);
						}
					}	//end electronic item block

					//store reference to reserve obj for success display
					$reserves[] =& $reserve;

					//free some memory. not sure how effective unset() is; may need destructor methods
					unset($item);
					unset($reserve);
					unset($physCopy);
					unset($requestObj);
					unset($reserveDesk);
					unset($instr);
				}	//end looping through items
				
				//tie up some loose ends
				
				//if the originally-requested reserve was not processed, then there was a substitution
				//process request, and delete the "inprocess" reserve from the class
				if(($originally_requested_reserve instanceof reserve) && !$originally_requested_reserve->processed) {
					//process request
					$requestObj = new request();
					//the reserve is tied to the request_id, so just use that (although can also get request by reserve_id)
					$requestObj->getRequestByID($request['request_id']);
					$requestObj->setDateProcessed(date('Y-m-d'));
					
					//delete the reserve
					$originally_requested_reserve->destroy();
				}					

				//show success screen

				$page = 'manageClasses';

				if($request['previous_cmd'] == 'processRequest') {
					$loc = 'process request';

					$requestList = $user->getRequests();
					$ci->getPrimaryCourse();

					$this->displayFunction = 'processSuccessful';
					$this->argList = array($ci, $ilsResults);
				}
				else {	//assume addPhysicalItem or addDigitalItem
					$loc = 'add an item';
					$this->displayFunction = 'addSuccessful';

					//duplicate links for digital/manual items?
					if( ($request['previous_cmd'] == 'addDigitalItem') || ($request['addType']=='MANUAL') || ($request['addType']=='PERSONAL') )
						$duplicate = true;

					$this->argList = array($user, $reserves, $ci, $request['selected_instr'], $duplicate, $ilsResults);
				}
			break;	//end storeRequest

			case 'processRequest':
				global $ci;

				$page = "manageClasses";
				$loc  = "process request";
				$msg = "";

				$requestObj	= new request($_REQUEST['request_id']);
				$item = new reserveItem($requestObj->requestedItemID);
				$reserve = new reserve($requestObj->reserveID);

				//set ci so it will be displayed for user
				$ci = new courseInstance($requestObj->courseInstanceID);
				$ci->getPrimaryCourse();
				if (isset($_REQUEST['searchField']) && (isset($_REQUEST['searchTerm']) && ltrim(rtrim($_REQUEST['searchTerm'])) != ""))
				{
					$zQry = new zQuery($_REQUEST['searchTerm'], $_REQUEST['searchField']);

					//$sXML = $zQry->getResults();

					//parse results into array
					$search_results = $zQry->parseToArray();
					$search_results['physicalCopy'] = $zQry->getHoldings($_REQUEST['searchField'], $_REQUEST['searchTerm']);
				} else {
					$pCopy = new physicalCopy();
					$pCopy->getByItemID($item->getItemID());

					list($qryValue, $qryField) = ($pCopy->getBarcode() != "" && !is_null($pCopy->getBarcode())) ? array($pCopy->getBarcode(), 'barcode') : array($item->getLocalControlKey(), 'control');

					$zQry = new zQuery($qryValue, $qryField);
					$search_results = $zQry->parseToArray();
					//$search_results['physicalCopy'] = $zQry->getHoldings('control', $item->getLocalControlKey());
					$search_results['physicalCopy'] = $zQry->getHoldings($qryField, $qryValue);

//				} else ($item->getLocalControlKey() <> "") {
//					$zQry = new zQuery($item->getLocalControlKey(), 'control');
//					$search_results = $zQry->parseToArray();
//					$search_results['physicalCopy'] = $zQry->getHoldings('control', $item->getLocalControlKey());
				}

				//we will pull item values from db if they exist otherwise default to searched values

				$pre_value = array('title'=>'', 'author'=>'', 'edition'=>'', 'performer'=>'', 'times_pages'=>'', 'source'=>'', 'notes'=>'', 'controlKey'=>'', 'personal_owner'=>null, 'physicalCopy'=>'');
				$pre_values['title'] = ($item->getTitle() <> "") ? $item->getTitle() : $search_results['title'];
				$pre_values['author'] = ($item->getAuthor() <> "") ? $item->getAuthor() : $search_results['author'];
				$pre_values['edition'] = ($item->getVolumeEdition() <> "") ? $item->getVolumeEdition() : $search_results['edition'];
				$pre_values['performer'] = ($item->getPerformer() <> "") ? $item->getPerformer() : $search_results['performer'];
				$pre_values['volume_title'] = ($item->getVolumeTitle() <> "") ? $item->getVolumeTitle() : $search_results['volume_title'];
				$pre_values['times_pages'] = ($item->getPagesTimes() <> "") ? $item->getPagesTimes() : $search_results['times_pages'];
				$pre_values['source'] = ($item->getSource() <> "") ? $item->getSource() : $search_results['source'];
				$pre_values['controlKey'] = ($item->getLocalControlKey() <> "") ? $item->getLocalControlKey() : $search_results['controlKey'];
				$pre_values['personal_owner'] = $item->getPrivateUserID();
				$pre_values['notes'] = $item->getNotes();

				$pre_values['physicalCopy'] = $search_results['physicalCopy'];

				$isActive = ($reserve->getStatus() == 'ACTIVE' || $reserve->getStatus() == 'IN PROCESS') ? true : false;

				//get all Libraries
				$lib_list = $user->getLibraries();

				$this->displayFunction = 'addItem';
				$this->argList = array($user, $cmd, $pre_values, $lib_list, $requestObj->requestID, $_REQUEST, array('cmd'=>$cmd, 'previous_cmd'=>$cmd, 'ci'=>$ci->getCourseInstanceID(), 'request_id'=>$requestObj->requestID), null, $isActive, 'Process Item', $msg, $reserve->getRequestedLoanPeriod());
			break;

			case 'addDigitalItem':
			case 'addPhysicalItem':
				$page = "manageClasses";
				$loc  = "add item";

				if (isset($_REQUEST['searchField']) && (isset($_REQUEST['searchTerm']) && ltrim(rtrim($_REQUEST['searchTerm'])) != ""))
				{
					$zQry = new zQuery($_REQUEST['searchTerm'], $_REQUEST['searchField']);
					//$sXML = $zQry->getResults();

					//parse results into array
					$search_results = $zQry->parseToArray();

					//look for existing item in DB
					$item = new reserveItem();
					$item->getItemByLocalControl($search_results['controlKey']);										

					if ($item->getItemID() != "")
					{
						$item_id = $item->getItemID();
						$search_results = array('title'=>'', 'author'=>'', 'edition'=>'', 'performer'=>'', 'times_pages'=>'', 'source'=>'', 'controlKey'=>'', 'personal_owner'=>null, 'notes'=>'', 'physicalCopy'=>'');
						$search_results['title'] = ($item->getTitle() <> "") ? $item->getTitle() : "";
						$search_results['author'] = ($item->getAuthor() <> "") ? $item->getAuthor() : "";
						$search_results['edition'] = ($item->getVolumeEdition() <> "") ? $item->getVolumeEdition() : "";
						$search_results['performer'] = ($item->getPerformer() <> "") ? $item->getPerformer() : "";
						$search_results['volume_title'] = ($item->getVolumeTitle() <> "") ? $item->getVolumeTitle() : "";
						$search_results['times_pages'] = ($item->getPagesTimes() <> "") ? $item->getPagesTimes() : "";
						$search_results['source'] = ($item->getSource() <> "") ? $item->getSource() : "";
						$search_results['controlKey'] = $item->getLocalControlKey();
						$search_results['personal_owner'] = $item->getPrivateUserID();		
						$search_results['notes'] = $item->getNotes();
					} else {
						$item_id = null;
					}

					$search_results['physicalCopy'] = $zQry->getHoldings($_REQUEST['searchField'], $_REQUEST['searchTerm']);
				} else {
					$search_results = null;
					$item_id = null;
				}

				//get all Libraries
				$lib_list = $user->getLibraries();
				
				if ($cmd == 'addDigitalItem')
				{
					$docTypeIcons = $user->getAllDocTypeIcons();				
					$search_results['docTypeIcon'] =  (is_a($item, 'reserveItem')) ? $item->getItemIcon() : reserveItem::getItemIcon();
				} else 
					$docTypeIcons = null;
				
				$this->displayFunction = 'addItem';			
						
				//when form is sumbitted for save cmd is set to storeRequest its ugly but it works
				$this->argList = array($user, $cmd, $search_results, $lib_list, null, $_REQUEST, array('cmd'=>$cmd, 'previous_cmd'=>$cmd, 'ci'=>$_REQUEST['ci'], 'selected_instr'=>$_REQUEST['selected_instr'], 'item_id'=>$item_id), $docTypeIcons);
			break;
		}
	}
}

?>
