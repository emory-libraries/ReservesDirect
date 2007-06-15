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
						}					
					}

					//set item & reserve data
					
					//dates
					$reserve->setActivationDate($request['reserve_activation_date']);
					$reserve->setExpirationDate($request['reserve_expiration_date']);

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
					
					if (isset($request['ISBN'])) $item->setISBN($request['ISBN']);
					if (isset($request['ISSN'])) $item->setISSN($request['ISSN']);
					if (isset($request['OCLC'])) $item->setOCLC($request['OCLC']);
					
					
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
							$file_loc = $file['dir'] . $file['name'] . $file['ext'];
							$item->setURL($file_loc);
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

				
				//if a request came through ILS request feed AND we have processed it, delete it
				if(!empty($_REQUEST['ils_request_id'])) {
					$ils_request = new ILS_Request();
					if($ils_request->getByID($_REQUEST['ils_request_id'])) {
						$ils_request->deleteRow();
					}
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

				$page = "addReserve";
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
				$page = "addReserve";
				$loc  = "add item";
				
				//if the main form has been submitted, then it's time to look up a class for the item
				//should only be here if adding physical item 
				if(isset($_REQUEST['store_request'])) {					
					//array to store all CI objects that have matching instructors
					$course_instances = array();
					//not sure if this is necessary, but ignore duplicate users
					$processed_ids = array();
					//store CI_id-ils_request_id pairs for all matching courses
					$requests_matching_CIs = array();
										
					foreach($_REQUEST['physical_copy'] as $phys_copy_raw_data) {
						//pull physical copy info
						$phys_item = unserialize(urldecode($phys_copy_raw_data));
						
						//init an ILS_Request object
						//used to look up ils-user-IDs by barcode
						$ils_request = new ILS_Request();
						if($ils_request->getByBarcode($phys_item['bar'])) {
							//not sure if this is necessary, but ignore duplicate users
							$ils_user_id = $ils_request->getILSUserID();
							if(!empty($ils_user_id) && !in_array($ils_user_id, $processed_ids)) {	//have not processed this one before
								//init instructor object
								$instructor = new instructor();
								if($instructor->getByILSUserID($ils_user_id)) {	//make sure we actually have a user
									//fetch and store CIs taught by this instructor
									$instructor_CIs = $instructor->getCourseInstancesToEdit();
									$course_instances = array_merge($course_instances, $instructor_CIs);
																		
									//attempt to find the CI matching the requesting course
									foreach($instructor_CIs as $instructor_CI) {
										if($ils_request->doesCourseMatch($instructor_CI->getCourseInstanceID())) {
											//store ILS request ID matching this CI
											//need this to remove request from DB later
											$requests_matching_CIs[$instructor_CI->getCourseInstanceID()] = $ils_request->getRequestID();
										}
									}
								}
								
								//remember this ils-user-id
								$processed_ids[] = $ils_user_id;
								//free up memory
								unset($instructor);
								unset($instructor_CIs);
							}
						}
						unset($ils_request);
					}
					
					//also, if CI-id is already defined (i.e. "add another item to this class")
					//then add that CI to the list
					if(!empty($_REQUEST['ci']) && !array_key_exists($_REQUEST['ci'], $course_instances)) {
						$course_instances[] = new courseInstance($_REQUEST['ci']);
					}

					//it's very important to pass on the current $_REQUEST
					//otherwise all the reserve info will be lost
					//however, we do not want the CMD var in there, because of clashing
					unset($_REQUEST['cmd']);
						
					//display this list of CIs + lookup box
					$this->displayFunction = 'displayCoursesForRequest';
					$this->argList = array($course_instances, $_REQUEST, $requests_matching_CIs);
					
					//do not do any further processing
					break;
				}
				

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
						$search_results['ISBN']	= $item->getISBN();
						$search_results['ISSN']	= $item->getISSN();
						$search_results['OCLC']	= $item->getOCLC();
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
				
				$hidden_fields = array('cmd'=>$cmd, 'previous_cmd'=>$cmd, 'selected_instr'=>$_REQUEST['selected_instr'], 'item_id'=>$item_id);
				if(!empty($_REQUEST['ci'])) {
					$hidden_fields['ci'] = $_REQUEST['ci'];
				}
						
				//when form is sumbitted for save cmd is set to storeRequest its ugly but it works
				$this->argList = array($user, $cmd, $search_results, $lib_list, null, $_REQUEST, $hidden_fields, $docTypeIcons);
			break;
		}
	}
}

?>
