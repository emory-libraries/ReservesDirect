<?
/*******************************************************************************
requestManager.class.php


Created by Jason White (jbwhite@emory.edu)

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
require_once("secure/common.inc.php");
require_once("secure/classes/itemAudit.class.php");
require_once("secure/classes/ils_request.class.php");
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
		global $g_permission, $page, $loc, $ci, $alertMsg, $g_documentURL, $g_notetype, $u;

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
			
			case 'storeRequest':	//last step: creating reserves and processing requests
				$page = 'addReserve';
				$loc = 'add item to class';
				
				$ci_id = !empty($_REQUEST['ci']) ? $_REQUEST['ci'] : null;
				$item_id = !empty($_REQUEST['item_id']) ? $_REQUEST['item_id'] : null;
				
				//at the very least should have CI and item
				if(!empty($ci_id) && !empty($item_id)) {
					if(isset($_REQUEST['submit_store_item'])) {	//submitted info needed to create reserve
						//get an unitialized reserves object
						$reserve = new reserve();
						$item = new reserveItem($item_id);
						
						//attempt to find a reserve
						if(!empty($_REQUEST['rd_request'])) {	//if there is a request, grab reserve from request
							$rd_request = new request($_REQUEST['rd_request']);
							$reserve->getReserveByID($rd_request->getReserveID());
							
							//set the request as processed
							$rd_request->setDateProcessed(date('Y-m-d'));
							
							//done with RD request - free memory
							unset($rd_request);
						}
						elseif($reserve->getReserveByCI_Item($ci_id, $item_id) === false) {	//if not, try to find existing reserve
							//if querying old reserve returns nothing, create new
							$reserve->createNewReserve($ci_id, $item_id);
							
							//attempt to set a sort order for the reserve
							//only need to do this for a newly created reserve
							$reserve->insertIntoSortOrder($ci->getCourseInstanceID(), $item->getTitle(), $item->getAuthor());
						}
						
						//set dates
						$reserve->setActivationDate($_REQUEST['reserve_activation_date']);
						$reserve->setExpirationDate($_REQUEST['reserve_expiration_date']);
						//set status
						$reserve->setStatus($_REQUEST['reserve_status']);
						
						//done for digital items
						//the rest is for physical items
						
						//init a few vars so that php does not complain about uninitialized variables
						$instructor = $ils_results = null;
						
						//create physical copy records if needed
						//set up ILS records if needed
						if(isset($_REQUEST['create_ils_record']) && !empty($_REQUEST['physical_copy'])) {
							//need an instructor object
							$ci = new courseInstance($ci_id);
							$ci->getInstructors();
							$instructor = $ci->instructorList[0];
														
							//grab instructor ILS info
							$instructor->getInstructorAttributes();
							
							//get selected loan period
							$circRule = unserialize(urldecode($_REQUEST['circRule']));
							
							$ils_results = '<ul>';	//store results of ils queries
												
							//go through physical copies
							foreach($_REQUEST['physical_copy'] as $phys_copy_raw_data) {								
								//the raw data is serialized and urlencoded, reverse the process
								$phys_copy_raw_data = unserialize(urldecode($phys_copy_raw_data));
								
								//get an object
								$physCopy = new physicalCopy();
								//make sure the record does not already exist
								if(!$physCopy->getByBarcode($phys_copy_raw_data['bar'])) {
									//create a new record
									$physCopy->createPhysicalCopy();
									
									//add data
									$physCopy->setItemID($item->getItemID());
									$physCopy->setBarcode($phys_copy_raw_data['bar']);
									$physCopy->setCallNumber($phys_copy_raw_data['callNum']);
									$physCopy->setStatus($phys_copy_raw_data['loc']);
									$physCopy->setItemType($phys_copy_raw_data['type']);
									$physCopy->setOwningLibrary($phys_copy_raw_data['library']);
									
									//check personal item owner
									$private_owner_id = $item->getPrivateUserID();
									if(!empty($private_owner_id)) {
										$physCopy->setOwnerUserID($private_owner_id);
									}
								}
								unset($physCopy);
								
								//create ILS record
								$ilsResult = $user->createILS_record($phys_copy_raw_data['bar'], $phys_copy_raw_data['copy'], $instructor->getILSUserID(), $item->getHomeLibraryID(), $ci->getTerm(), $circRule['circRule'], $circRule['alt_circRule'], $ci->getExpirationDate());
								//store ilsResult for the future
								$ils_results .= '<li>'.$ilsResult.'</li>';
							}
							$ils_results .= '</ul>';						
						}
						
						//process ILS requests (basically delete the ones that have been satisfied)
						if(!empty($_REQUEST['ils_requests'])) {
							foreach($_REQUEST['ils_requests'] as $ils_request_id) {
								$ils_request = new ILS_Request($ils_request_id);
								$ils_request->deleteRow();								
							}
						}
						
						//done, show success screen

						//duplicate links for digital items
						$duplicate = !$item->isPhysicalItem() ? $duplicate = true : false;
						
						$this->displayFunction = 'addSuccessful';
						$this->argList = array($ci, $item->getItemID(), $reserve->getReserveID(), $duplicate, $ils_results);					
					}
					else {	//have item-id and ci-id, but nothing else -- just did classLookup and need to show the create-reserve form
						//need to pre-fetch some data

						//get holding info for physical items
						$item = new reserveItem($item_id);
						if($item->isPhysicalItem()) {
							$zQry = new zQuery('');
							$holdingInfo = $zQry->getHoldings('control', $item->getLocalControlKey());
							$selected_barcode = $propagated_data['requested_barcode'];
						}
						else {
							$holdingInfo = null;
							$selected_barcode = null;
						}
						
						$this->displayFunction = 'displayCreateReserveForm';
						$this->argList = array(new courseInstance($ci_id), $item_id, new circRules(), $holdingInfo, null, $selected_barcode);				
					}					
				}
				elseif(!empty($item_id)) {	//only have item ID, show selectCIforItem
					//prefetch possible CIs
					list($all_possible_CIs, $selected_CIs, $CI_request_matches) = $this->getCIsForItem($item_id);
					
					//pass the item_id to the select-course form
					$this->displayFunction = 'displaySelectCIForItem';
					$this->argList = array($item_id, $all_possible_CIs, $selected_CIs, $CI_request_matches);
				}
				//else: nothing
			break;

			
			case 'addPhysicalItem':	//this case is for creating/editing physical items and/or processing requests
				$page = "addReserve";
				$loc  = "add physical item";
				
				if(isset($_REQUEST['store_request'])) {	//form submitted, process item
					//store item meta data
					$item_id = $this->storeItem();
					
					//prefetch possible CIs
					list($all_possible_CIs, $selected_CIs, $CI_request_matches) = $this->getCIsForItem($item_id);
					
					//pass on the searched-for barcode
					if(!empty($_REQUEST['searchTerm']) && ($_REQUEST['searchField'] == 'barcode')) {
						$requested_barcode = $_REQUEST['searchTerm'];
					}
					else {
						$requested_barcode = null;
					}
					
					$this->displayFunction = 'displaySelectCIForItem';
					$this->argList = array($item_id, $all_possible_CIs, $selected_CIs, $CI_request_matches, $requested_barcode);
				}
				else {	//show edit-item form
					//if searching for an item, get form pre-fill data
					$item_data = $this->searchItem($cmd);
					
					//pass on some extra info
					$propagated_data = array();
					$propagated_data['cmd'] = $cmd;
					if(!empty($_REQUEST['request_id'])) {
						$propagated_data['request_id'] = $_REQUEST['request_id'];	//need this to process request later
					}
					if(!empty($item_data['item_id'])) {
						$propagated_data['item_id'] = $item_data['item_id'];	//pass on the item_id if it exists
					}
										
					$this->displayFunction = 'addItem';
					$this->argList = array($cmd, $item_data, $propagated_data);
				}
			break;
			
			case 'addDigitalItem':	//this case is for creating/editing digital items
				$page = "addReserve";
				$loc  = "add electronic item";
				
				if(isset($_REQUEST['store_request'])) {	//form submitted, process item
					//store item meta data
					$item_id = $this->storeItem();
					
					//prefetch possible CIs
					list($all_possible_CIs, $selected_CIs, $CI_request_matches) = $this->getCIsForItem($item_id);
					
					//pass the item_id to the select-course form
					$this->displayFunction = 'displaySelectCIForItem';
					$this->argList = array($item_id, $all_possible_CIs, $selected_CIs, $CI_request_matches);
				}
				else {	//show edit-item form
					//if searching for an item, get form pre-fill data
					$item_data = $this->searchItem($cmd);
										
					//pass on some info
					$propagated_data = array();					
					$propagated_data['cmd'] = $cmd;
					if(!empty($item_data['item_id'])) {
						$propagated_data['item_id'] = $item_data['item_id'];	//pass on the item_id if it exists
					}
					
					$this->displayFunction = 'addItem';
					$this->argList = array($cmd, $item_data, $propagated_data);
				}
			break;
		}
	}
	
	
	/**
	 * Searches through RD and ILS requests, attempting to pre-fetch and pre-select potential destination courses for the given item_id
	 *
	 * @param int $item_id
	 * @return array (multidimentional) -- array(all-possible-courses, selected-courses, course-request-matches)
	 * 
	 * 	
		 * $all_possible_CIs = array(
		 * 	'rd_requests' => array(ci1-id, ci2-id, ...),
		 * 	'ils_requests => array(
		 * 		user-id1 = array(
		 * 			'requests' => array(ils-request-id1, ils-request-id2, ...),
		 * 			'ci_list' => array(ci1-id, ci2-id, ...)
		 * 		),
		 * 		user-id2 = ...
		 *	)
		 * )
	 *
	 * 
		 * $selected_CIs = array(ci1_id, ci2_id, ...)
	 *
	 * 
		 * $CI_request_matches = array(
		 * 	ci1-id => array(
		 * 		'rd_request' => rd-req-id,
		 * 		'ils_requests' => array(
		 * 			ils-req1-id => ils-req1-period,
		 * 			ils-req2-id...
		 * 		)
		 * 	),
		 * 	ci2-id = ...
		 * )
	 * 
	 */
	function getCIsForItem($item_id) {
		//need the item object (only because need the control key for ILS requests)
		$item = new reserveItem($item_id);
				
		//need to create a list of possible destination CIs for this item
		
		/**
		 * $all_possible_CIs = array(
		 * 	'rd_requests' => array(ci1-id, ci2-id, ...),
		 * 	'ils_requests => array(
		 * 		user-id1 = array(
		 * 			'requests' => array(ils-request-id1, ils-request-id2, ...),
		 * 			'ci_list' => array(ci1-id, ci2-id, ...)
		 * 		),
		 * 		user-id2 = ...
		 *	)
		 * )
		 */
		$all_possible_CIs = array();	//array of CI objects
		/**
		 * $selected_CIs = array(ci1_id, ci2_id, ...)
		 */
		$selected_CIs = array();	//array of CI IDs
		/**
		 * $CI_request_matches = array(
		 * 	ci1-id => array(
		 * 		'rd_request' => rd-req-id,
		 * 		'ils_requests' => array(
		 * 			ils-req1-id => ils-req1-period,
		 * 			ils-req2-id...
		 * 		)
		 * 	),
		 * 	ci2-id = ...
		 * )
		 */
		$CI_request_matches = array();	//keep track of which requests link to which CI
		
		//ignore duplicate requests
		$processed_request_ids = array();
		
		//if processing request, add requested CI to list
		//this is truly not needed, as the next block would catch this request
		//the only reason to do this separately, is to make sure this CI is first on the list
		if(!empty($_REQUEST['request_id'])) {
			$request = new request();
			if($request->getRequestByID($_REQUEST['request_id'])) {
				$all_possible_CIs['rd_requests'][] = $request->getCourseInstanceID();
				$selected_CIs[] = $request->getCourseInstanceID();
				//match request to CI
				$CI_request_matches[$request->getCourseInstanceID()]['rd_request'] = $request->getRequestID();
				//add to list of processed requests
				$processed_request_ids[] = $request->getRequestID();
			}
			unset($request);
		}
		
		//may not be processing requests explicitly, but still working on an existing item that has requests pending
		//attempt to find those
		foreach(request::getRequestsByItem($item_id) as $request) {
			if(!in_array($request->getRequestID(), $processed_request_ids)) {	//ignore duplicates
				$all_possible_CIs['rd_requests'][] = $request->getCourseInstanceID();
				$selected_CIs[] = $request->getCourseInstanceID();
				//match request to CI
				$CI_request_matches[$request->getCourseInstanceID()]['rd_request'] = $request->getRequestID();
				//add to list of processed requests
				$processed_request_ids[] = $request->getRequestID();
			}
		}
		
		//see if there are ILS requests for this item
		//this is a little more involved and involves retrieving CI lists by users
		
		//keep track of processed user IDs
		$processed_instructor_net_ids = array();		
		
		foreach(ILS_Request::getRequestsByControlKey($item->getLocalControlKey()) as $ils_request) {
			//init instructor object
			$instructor = new instructor();
			if($instructor->getUserByUserName($ils_request->getUserNetID()) || $instructor->getByILSUserID($ils_request->getUserILSID())) {	//found valid user
				//fetch CIs taught by this instructor
				$instructor_CIs = $instructor->getCourseInstancesToEdit();
				
				//if we have not already done so, add these CIs to the list
				if(!in_array($ils_request->getUserNetID(), $processed_instructor_net_ids) && !empty($instructor_CIs)) {
					//$instructor->getCourseInstancesToEdit() returns array of CI objects indexed by their respective ci-id
					//just need those IDs
					$all_possible_CIs['ils_requests'][$instructor->getUserID()]['ci_list'] = array_keys($instructor_CIs);
				}
				
				//add this request ID to list
				$all_possible_CIs['ils_requests'][$instructor->getUserID()]['requests'][] = $ils_request->getRequestID();
														
				//attempt to find the CI matching the requesting course
				foreach($instructor_CIs as $instructor_CI) {
					if($ils_request->doesCourseMatch($instructor_CI->getCourseInstanceID())) {
						//add to list of selected CIs
						$selected_CIs[] = $instructor_CI->getCourseInstanceID();
					}
					
					//match request to CI; these also have requested loan periods
					$CI_request_matches[$instructor_CI->getCourseInstanceID()]['ils_requests'][$ils_request->getRequestID()] = $ils_request->getRequestedLoanPeriod();
				}
				//remember this user net id
				$processed_instructor_net_ids[] = $ils_request->getUserNetID();
				unset($instructor_CIs);												
			}
			unset($instructor);
		}
		
		//return an array of the findings
		return array($all_possible_CIs, $selected_CIs, $CI_request_matches);		
	}
	
	
	/**
	 * Attempts to find an item in DB and/or (if physical item) in ILS; return array prefilled w/ item data or empty array w/ proper indeces
	 *
	 * @param string $cmd Current cmd
	 * @return array
	 */
	function searchItem($cmd) {
		//create a blank array with all the needed indeces
		$item_data = array('title'=>'', 'author'=>'', 'edition'=>'', 'performer'=>'', 'times_pages'=>'', 'volume_title'=>'', 'source'=>'', 'controlKey'=>'', 'selected_owner'=>null, 'physicalCopy'=>null, 'OCLC'=>'', 'ISSN'=>'', 'ISBN'=>'', 'item_group'=>null, 'notes'=>null, 'home_library'=>null, 'url'=>'', 'is_local_file'=>false);
				
		//decide if item info can be prefilled
		$item = new reserveItem();
		$qryField = $qryValue = null;
		
		if(!empty($_REQUEST['searchTerm'])) {	//search info specified
			//save the search parameters for querying DB/ILS
			$qryField = $_REQUEST['searchField'];
			$qryValue = $_REQUEST['searchTerm'];
			
			//init a new item
			if($qryField == 'barcode') {	//get by barcode
				$phys_item = new physicalCopy();
				if($phys_item->getByBarcode($qryValue)) {
					$item->getItemByID($phys_item->getItemID());
				}
			}
			else {	//get by local control
				$item->getItemByLocalControl($qryValue);
			}					
		}
		elseif(!empty($_REQUEST['request_id'])) {	//processing request, get info out of DB
			$request = new request();
			if($request->getRequestByID($_REQUEST['request_id'])) {
				//init reserveItem object
				$request->getRequestedItem();
				$item = $request->requestedItem;
				
				//set search parameters
				$qryField = 'control';
				$qryValue = $item->getLocalControlKey();
			}
		}
		
		//if searching for a physical item, then there may be an ILS record
		//this should return an indexed array, which may be populated w/ data
		if(($cmd=='addPhysicalItem') && !empty($qryValue)) {
			//query ILS
			$zQry = new zQuery($qryValue, $qryField);
			//parse results into array
			$search_results = $zQry->parseToArray();
		
			//if still do not have an initialized item object
			//try one more time by control key pulled from ILS
			$item_id = $item->getItemID();
			if(empty($item_id)) {
				$item->getItemByLocalControl($search_results['controlKey']);
			}

			//this is not needed at the moment, b/c do not want to show holdings for addPhysicalItem/processRequest
			//but that may change, so it's here, but commented
			$search_results['physicalCopy'] = null;
			//get holdings		
			//$search_results['physicalCopy'] = $zQry->getHoldings($qryField, $qryValue);
		}
		else {
			//otherwise just get a blank $search_results array w/ proper indeces, to avoid "no such index" notices
			$search_results = $item_data;
		}
			
		//pull item values from db if they exist otherwise default to searched values
		//this may still result in a blank initialized array, if there was no item
		$item_data['title'] = ($item->getTitle() <> "") ? $item->getTitle() : $search_results['title'];
		$item_data['author'] = ($item->getAuthor() <> "") ? $item->getAuthor() : $search_results['author'];
		$item_data['edition'] = ($item->getVolumeEdition() <> "") ? $item->getVolumeEdition() : $search_results['edition'];
		$item_data['performer'] = ($item->getPerformer() <> "") ? $item->getPerformer() : $search_results['performer'];
		$item_data['volume_title'] = ($item->getVolumeTitle() <> "") ? $item->getVolumeTitle() : $search_results['volume_title'];
		$item_data['times_pages'] = ($item->getPagesTimes() <> "") ? $item->getPagesTimes() : $search_results['times_pages'];
		$item_data['source'] = ($item->getSource() <> "") ? $item->getSource() : $search_results['source'];
		$item_data['controlKey'] = ($item->getLocalControlKey() <> "") ? $item->getLocalControlKey() : $search_results['controlKey'];
		$item_data['OCLC'] = ($item->getOCLC() <> "") ? $item->getOCLC() : $search_results['OCLC'];
		$item_data['ISSN'] = ($item->getISSN() <> "") ? $item->getISSN() : $search_results['ISSN'];
		$item_data['ISBN'] = ($item->getISBN() <> "") ? $item->getISBN() : $search_results['ISBN'];
		$item_data['item_group'] = $item->getItemGroup();
		$item_data['home_library'] = $item->getHomeLibraryID();
		$item_data['selected_owner'] = $item->getPrivateUserID();
		$item_data['notes'] = $item->getNotes();
		$item_data['url'] = $item->getURL();
		$item_data['is_local_file'] = $item->isLocalFile();
		$item_data['physicalCopy'] = $search_results['physicalCopy'];
		
		//pass on the item_id in case there was a valid DB record
		$item_data['item_id'] = $item->getItemID();
		
		return $item_data;
	}
	
	
	/**
	 * Edits or creates a new item, using the addDigital/addPhysical item form ($_REQUEST); Returns item-id
	 *
	 * @return int
	 */
	function storeItem() {
		//when adding a 'MANUAL' physical item, the physical-copy data is hidden, but still passed on by the form
		//make sure that we do not use it
		if($_REQUEST['addType'] == 'MANUAL') {
			unset($_REQUEST['physical_copy']);
		}
		
		//determine if editing item or creating new
		//get a valid object in either case
		$item = new reserveItem();
		if(empty($_REQUEST['item_id']) || !$item->getItemByID($_REQUEST['item_id'])) {	//If missing item_id or it is invalid
			//try to get item by local_control_key
			if(empty($_REQUEST['local_control_key']) || !$item->getItemByLocalControl($_REQUEST['local_control_key'])) {	//no key, or it is invalid
				//have to create item
				$item->createNewItem();	
				//audit the action
				$itemAudit = new itemAudit();
				$itemAudit->createNewItemAudit($item->getItemID(),$u->getUserID());
				unset($itemAudit);					
			}	//else object has been initialized successfully						
		}	//else object has been initialized successfully
		
		//add/edit data
		if(isset($_REQUEST['title'])) $item->setTitle($_REQUEST['title']);
		if(isset($_REQUEST['author'])) $item->setAuthor($_REQUEST['author']);
		if(isset($_REQUEST['performer'])) $item->setPerformer($_REQUEST['performer']);
		if(isset($_REQUEST['source'])) $item->setSource($_REQUEST['source']);				
		if(isset($_REQUEST['volume_edition'])) $item->setVolumeEdition($_REQUEST['volume_edition']);
		if(isset($_REQUEST['home_library'])) $item->sethomeLibraryID($_REQUEST['home_library']);
		if(isset($_REQUEST['item_group'])) $item->setGroup($_REQUEST['item_group']);
		if(isset($_REQUEST['volume_title'])) $item->setVolumeTitle($_REQUEST['volume_title']);
		if(isset($_REQUEST['times_pages'])) $item->setPagesTimes($_REQUEST['times_pages']);
		if(isset($_REQUEST['selectedDocIcon'])) $item->setDocTypeIcon($_REQUEST['selectedDocIcon']);				
		if(isset($_REQUEST['ISBN'])) $item->setISBN($_REQUEST['ISBN']);
		if(isset($_REQUEST['ISSN'])) $item->setISSN($_REQUEST['ISSN']);
		if(isset($_REQUEST['OCLC'])) $item->setOCLC($_REQUEST['OCLC']);
		//this will be an ILS-assigned key for physical items, or a manually-entered barcode for electronic items
		if(isset($_REQUEST['local_control_key'])) $item->setLocalControlKey($_REQUEST['local_control_key']);
		
		//check personal item owner
		if(($_REQUEST['personal_item'] == 'yes') && ($_REQUEST['personal_item_owner'] == 'new') && !empty($_REQUEST['selected_owner']) ) {
			$item->setPrivateUserID($_REQUEST['selected_owner']);
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
		
		//if adding electronic item, need to process file or link
		if(!$item->isPhysicalItem() && !empty($_REQUEST['documentType'])) {
			if($_REQUEST['documentType'] == 'DOCUMENT') {	//uploading a file
				$file = common_storeUploaded($_FILES['userFile'], $item->getItemID());														
				$file_loc = $file['dir'] . $file['name'] . $file['ext'];
				$item->setURL($file_loc);
				$item->setMimeTypeByFileExt($file['ext']);
			}
			elseif($_REQUEST['documentType'] == 'URL') {	//adding a link
				$item->setURL($_REQUEST['url']);
			}
			//else maintaining the same link; do nothing
		}
		
		//return id of item
		return $item->getItemID();	
	}
}

?>