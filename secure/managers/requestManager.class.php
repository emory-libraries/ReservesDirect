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
require_once("secure/classes/itemAudit.class.php");
require_once("secure/classes/users.class.php");
require_once("secure/classes/zQuery.class.php");
require_once("secure/displayers/requestDisplayer.class.php");

class requestManager
{
	public $user;
	public $displayClass;
	public $displayFunction;
	public $argList;
	
	function display()
	{
		//echo "attempting to call ". $this->displayClass ."->". $this->displayFunction ."<br>";
		
		if (is_callable(array($this->displayClass, $this->displayFunction)))
			call_user_func_array(array($this->displayClass, $this->displayFunction), $this->argList);
			
	}
	
	
	function requestManager($cmd, $user, $ci, $request)
	{
		global $g_permission, $page, $loc, $ci;
			
		$this->displayClass = "requestDisplayer";

		switch ($cmd)
		{
			case 'deleteRequest':
				$requestObj = new request($request['request_id']);
				$reserve = new reserve($requestObj->getReserveID());
				$reserve->destroy();
				$requestObj->destroy();
			
			case 'displayRequest':			
				$page = "manageClasses";
				
				$loc  = "process request";
				
				$unit = (!isset($request['unit'])) ? $user->getStaffLibrary() : $request['unit'];
				$requestList = $user->getRequests($unit);

				for($i=0;$i<count($requestList);$i++)
				{
					$item = $requestList[$i]->requestedItem;
					$item->getPhysicalCopy();
					
					$requestList[$i]->courseInstance->getInstructors();
					$requestList[$i]->courseInstance->getCrossListings();
				}			
				
				$this->displayFunction = 'displayAllRequest';
				$this->argList = array($requestList, $user->getLibraries(), $request, $user);
			break;
						
			case 'storeRequest':
				global $g_documentURL;				
			
				$item 		= new reserveItem();
				$reserve	= new reserve();		

				if (!isset($request['request_id']))
				{
					$item->createNewItem();				
					$reserve->createNewReserve($ci->getCourseInstanceID(), $item->getItemID());	
					
					$itemAudit = new itemAudit();
					$itemAudit->createNewItemAudit($item->getItemID(),$user->getUserID());				
				} else {
					$requestObj	= new request($request['request_id']);
					$item->getItemByID($requestObj->requestedItemID);
					$reserve->getReserveByID($requestObj->getReserveID());
									
					$requestObj->setDateProcessed(date('Y-m-d'));
					//set ci so it will be displayed for user
					$ci = new courseInstance($reserve->getCourseInstanceID());
				}
				
				$reserve->setActivationDate($request['hide_year'].'-'.$request['hide_month'].'-'.$request['hide_day']);
				$reserve->setExpirationDate($ci->getExpirationDate());
				
				$status = (isset($request['currentStatus'])) ? $request['currentStatus'] : "ACTIVE";
				$reserve->setStatus($status);
			
				if (isset($request['author'])) $item->setAuthor($request['author']);
				if (isset($request['content_note'])) $item->setContentNotes($request['content_note']);
				if (isset($request['controlKey'])) $item->setLocalControlKey($request['controlKey']);
				if (isset($request['performer'])) $item->setPerformer($request['performer']);
				if (isset($request['source'])) $item->setSource($request['source']);
				if (isset($request['title'])) $item->setTitle($request['title']);
				if (isset($request['volume_edition'])) $item->setvolumeEdition($request['volume_edition']);
				if (isset($request['home_library'])) $item->sethomeLibraryID($request['home_library']);
				if (isset($request['item_type'])) $item->setGroup($request['item_type']);
				if (isset($request['volume_title'])) $item->setVolumeTitle($request['volume_title']);
				if (isset($request['pages_times'])) $item->setPagesTimes($request['pages_times']);

				if ($request['personal_item'] == "yes")
					$item->setprivateUserID($request['selected_owner']);			

				if ($request['previous_cmd'] == 'addPhysicalItem' || $request['previous_cmd'] == 'processRequest')
				{
					if ($request['physical_copy'] > 0)
					{
						for($i=0;$i<count($request['physical_copy']);$i++)
						{
							$phyCopy = $request['physical_copy'][$i];
							$pCopy = new physicalCopy();
							//if (!$pCopy->getByItemID($item->getItemID()))
							if ($pCopy->itemID != $item->getItemID())
								$pCopy->createPhysicalCopy();
							
							list($type,$lib,$callNumber,$location,$barcode,$copyNo) = explode("::", $phyCopy);
							$pCopy->setItemID($item->getItemID());
							$pCopy->setReserveID($reserve->getReserveID());
							$pCopy->setCallNumber($callNumber);
							$pCopy->setStatus($location);
							$pCopy->setItemType($type);
							
							$reserveDesk = new library($request['home_library']);
							//this should be reserveDesk							
							$pCopy->setOwningLibrary($reserveDesk->getReserveDesk());
							
							$pCopy->setBarcode($barcode);
							
							if (!is_null($item->getPrivateUserID()))
								$pCopy->setOwnerUserID($item->getPrivateUserID());
						}
					} else {
						$pCopy = new physicalCopy();
						//if (!$pCopy->getByItemID($item->getItemID()))
						if ($pCopy->itemID != $item->getItemID())
							$pCopy->createPhysicalCopy();						
							
						$pCopy->setBarcode($request['barcode']);
						$pCopy->setReserveID($reserve->getReserveID());
						$pCopy->setItemID($item->getItemID());
						
						if (!is_null($item->getPrivateUserID()))
								$pCopy->setOwnerUserID($item->getPrivateUserID());						
					}

					$ilsResult = "";
					if (isset($request['euclid_record']) && $request['euclid_record'] == 'yes') 
					{
						if (isset($requestObj) && $requestObj instanceof request) //get instructor from request
						{
							$instr =  new instructor();
							$instr->getUserByID($requestObj->requestingUserID);
						} else {//get instructor from the course instance
							$ci->getInstructors();
							$instr = $ci->instructorList[0];
						}
																	
						$instr->getInstructorAttributes();
									
						for($i=0;$i<count($request['physical_copy']);$i++)
						{	
							list($type, $library, $callNumber, $location, $barcode, $copyNo) = split("::", $request['physical_copy'][$i]);														
							
							list($cRule, $alt_cRule) = split("::", $request['circRule']);
							
							$ilsResult = $user->createILS_record($barcode,$copyNo,$instr->getILSUserID(), $request['home_library'], $ci->getTerm(), $cRule, $alt_cRule, $ci->getExpirationDate());
						}
					}
				} else {
					// Check to see if this was a valid file they submitted
					if ($request['documentType'] == 'DOCUMENT')
					{
						if ($_FILES['userFile']['error'])
							trigger_error("Possible file upload attack. Filename: " . $_FILES['userFile']['name'] . "If you are trying to load a very large file (> 10 MB) contact Reserves to add the file.", E_USER_ERROR);
																	
						list($filename, $type) = split("\.", $_FILES['userFile']['name']);
						//move file set permissions and store location
						//position uploaded file so that common_move and move it
						move_uploaded_file($_FILES['userFile']['tmp_name'], $_FILES['userFile']['tmp_name'] . "." . $type);
						chmod($_FILES['userFile']['tmp_name'] . "." . $type, 0644);   	       	     	
					
						$newFileName = str_replace("&", "_", $filename); 											//remove & in filenames
						$newFileName = $item->getItemID() ."-". str_replace(" ", "_", $newFileName . "." . $type); 	//remove spaces in filenames
						common_moveFile($_FILES['userFile']['tmp_name'] . "." . $type,  $newFileName );
						$item->setURL($g_documentURL . $newFileName);
						$item->setMimeTypeByFileExt($type);
					} else {
						$item->setURL($_REQUEST['url']);
					}	
				}

				$page = "manageClasses";			
				if ($request['previous_cmd'] == 'processRequest')
				{
					$loc  = "process request";
				
					$requestList = $user->getRequests();

					$ci->getPrimaryCourse();
					
					$this->displayFunction = 'processSuccessful';				
					$this->argList = array($ci, $ilsResult);
					break;					
				} else {					
				
					$loc  = "add an item";				
					$this->displayFunction = 'addSuccessful';				
					$this->argList = array($ci, $request['selected_instr'], $ilsResult);
				}
				break;
			
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
					$search_results['physicalCopy'] = $zQry->getHoldings('control', $item->getLocalControlKey());					
					
//				} else ($item->getLocalControlKey() <> "") {
//					$zQry = new zQuery($item->getLocalControlKey(), 'control');
//					$search_results = $zQry->parseToArray();
//					$search_results['physicalCopy'] = $zQry->getHoldings('control', $item->getLocalControlKey());
				}

				//we will pull item values from db if they exist otherwise default to searched values
			
				$pre_value = array('title'=>'', 'author'=>'', 'edition'=>'', 'performer'=>'', 'times_pages'=>'', 'source'=>'', 'content_note'=>'', 'controlKey'=>'', 'personal_owner'=>null, 'physicalCopy'=>'');
				$pre_values['title'] = ($item->getTitle() <> "") ? $item->getTitle() : $search_results['title'];
				$pre_values['author'] = ($item->getAuthor() <> "") ? $item->getAuthor() : $search_results['author'];
				$pre_values['edition'] = ($item->getVolumeEdition() <> "") ? $item->getVolumeEdition() : $search_results['edition'];
				$pre_values['performer'] = ($item->getPerformer() <> "") ? $item->getPerformer() : $search_results['performer'];
				$pre_values['volume_title'] = ($item->getVolumeTitle() <> "") ? $item->getVolumeTitle() : $search_results['volume_title'];
				$pre_values['times_pages'] = ($item->getPagesTimes() <> "") ? $item->getPagesTimes() : $search_results['times_pages'];
				$pre_values['source'] = ($item->getSource() <> "") ? $item->getSource() : $search_results['source'];
				$pre_values['content_note'] = ($item->getContentNotes() <> "") ? $item->getContentNotes() : "";
				$pre_values['controlKey'] = ($item->getLocalControlKey() <> "") ? $item->getLocalControlKey() : $search_results['controlKey'];
				$pre_values['personal_owner'] = $item->getPrivateUserID();
				
				$pre_values['physicalCopy'] = $search_results['physicalCopy'];				

				//populate personal owners
				if (isset($pre_values['personal_owner']) || ((isset($_REQUEST['personal_item']) && $_REQUEST['personal_item'] == "yes") && (isset($_REQUEST['select_owner_by']) && isset($_REQUEST['owner_qryTerm'])))) //user is searching for an owner
				{				
					$users = new users();
					$users->search($_REQUEST['select_owner_by'], $_REQUEST['owner_qryTerm'], 'student'); //any registered user could own an item
					$owner_list = $users->userList;
				} else $owner_list = null;		

				$isActive = ($reserve->getStatus() == 'ACTIVE' || $reserve->getStatus() == 'IN PROCESS') ? true : false;
				
				//get all Libraries
				$lib_list = $user->getLibraries();

				$this->displayFunction = 'addItem';
				$this->argList = array($user, $cmd, $pre_values, $owner_list, $lib_list, $requestObj->requestID, $_REQUEST, array('cmd'=>$cmd, 'previous_cmd'=>$cmd, 'ci'=>$ci->getCourseInstanceID(), 'request_id'=>$requestObj->requestID), $isActive, 'Process Item', $msg);			
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
					$search_results['physicalCopy'] = $zQry->getHoldings($_REQUEST['searchField'], $_REQUEST['searchTerm']);					
				} else $search_results = null;
				
				//populate personal owners
				if (isset($_REQUEST['personal_item']) && ($_REQUEST['personal_item'] == "yes") && (isset($_REQUEST['select_owner_by']) && isset($_REQUEST['owner_qryTerm']))) //user is searching for an owner
				{				
					$users = new users();
					$users->search($_REQUEST['select_owner_by'], $_REQUEST['owner_qryTerm'], 'student'); //any registered user could own an item
					$owner_list = $users->userList;
				} else $owner_list = null;		
				
				//get all Libraries
				$lib_list = $user->getLibraries();
				$this->displayFunction = 'addItem';
				$this->argList = array($user, $cmd, $search_results, $owner_list, $lib_list, null, $_REQUEST, array('cmd'=>$cmd, 'previous_cmd'=>$cmd, 'ci'=>$_REQUEST['ci'], 'selected_instr'=>$_REQUEST['selected_instr']));
			break;
		}
	}
}

?>