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
require_once("secure/displayers/requestDisplayer.class.php");
require_once("secure/classes/note.class.php");

require_once("lib/RD/Ils.php");
require_once("secure/classes/requestCollection.class.php");

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
        
        $requestList = new requestCollection();
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

        $requestList->sort('call_number');
        
        $this->displayFunction = 'printSelectedRequest';
        $this->argList = array($requestList, $user->getLibraries(), $request, $user);       
      break;
      
      case 'deleteRequest':
        $requestObj = new request($request['request_id']);
        $requestObj->destroy();

      case 'displayRequest':      
        $page = "addReserve";

        $loc  = "process request";

        $unit = (empty($request['unit'])) ? $user->getStaffLibrary() : $request['unit'];
                
                $filter_status = (!isset($request['filter_status'])) ? "IN PROCESS" : $request['filter_status'];
        
        $requestList = $user->getRequests($unit, $filter_status, $request['sort']);       
              
//        for($i=0;$i<count($requestList);$i++)
//        {
//          $requestList[$i]->requestedItem->getPhysicalCopy();
//          $requestList[$i]->courseInstance->getInstructors();
//          $requestList[$i]->courseInstance->getCrossListings();               
//        }

        $this->displayFunction = 'displayAllRequest';
        $this->argList = array($requestList, $user->getLibraries(), $request, $user);
      break;
      
      case 'storeRequest':  //last step: creating reserves and processing requests
        $page = 'addReserve';
        $loc = 'add item to class';
        
        $ci_id = !empty($_REQUEST['ci']) ? $_REQUEST['ci'] : null;
        $item_id = !empty($_REQUEST['item_id']) ? $_REQUEST['item_id'] : null;
        
        //at the very least should have CI and item
        if(!empty($ci_id) && !empty($item_id)) {
          if(isset($_REQUEST['submit_store_item'])) { //submitted info needed to create reserve
            //create the reserve
            if(($data = $this->storeReserve()) !== false) {
              $reserve = new reserve($data['reserve_id']);
              $reserve->getItem();

              //allow "duplicate" links for digital items
              $duplicate = !$reserve->item->isPhysicalItem() ? $duplicate = true : false;
            
              //done, show success screen
              $this->displayFunction = 'addSuccessful';
              $this->argList = array(new courseInstance($ci_id), $reserve->item->getItemID(), $reserve->getReserveID(), $duplicate, $data['ils_results']);
            }
          }
          else {  //have item-id and ci-id, but nothing else -- just did classLookup and need to show the create-reserve form
            //need to pre-fetch some data

            //get holding info for physical items
            $item = new reserveItem($item_id);
            if($item->isPhysicalItem()) {
              $zQry = RD_Ils::initILS();
              $holdingInfo = null;
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
        elseif(!empty($item_id)) {  //only have item ID, show selectCIforItem
          //prefetch possible CIs
          list($all_possible_CIs, $selected_CIs, $CI_request_matches) = $this->getCIsForItem($item_id);
          
          //pass the item_id to the select-course form
          $this->displayFunction = 'displaySelectCIForItem';
          $this->argList = array($item_id, $all_possible_CIs, $selected_CIs, $CI_request_matches);
        }
        //else: nothing
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
     *  'rd_requests' => array(ci1-id, ci2-id, ...),
     *  'ils_requests => array(
     *    user-id1 = array(
     *      'requests' => array(ils-request-id1, ils-request-id2, ...),
     *      'ci_list' => array(ci1-id, ci2-id, ...)
     *    ),
     *    user-id2 = ...
     *  )
     * )
   *
   * 
     * $selected_CIs = array(ci1_id, ci2_id, ...)
   *
   * 
     * $CI_request_matches = array(
     *  ci1-id => array(
     *    'rd_request' => rd-req-id,
     *    'ils_requests' => array(
     *      ils-req1-id => ils-req1-period,
     *      ils-req2-id...
     *    )
     *  ),
     *  ci2-id = ...
     * )
   * 
   */
  static function getCIsForItem($item_id) {
    //need the item object (only because need the control key for ILS requests)
    $item = new reserveItem($item_id);
        
    //need to create a list of possible destination CIs for this item
    
    /**
     * $all_possible_CIs = array(
     *  'rd_requests' => array(ci1-id, ci2-id, ...),
     *  'ils_requests => array(
     *    user-id1 = array(
     *      'requests' => array(ils-request-id1, ils-request-id2, ...),
     *      'ci_list' => array(ci1-id, ci2-id, ...)
     *    ),
     *    user-id2 = ...
     *  )
     * )
     */
    $all_possible_CIs = array();  //array of CI objects
    /**
     * $selected_CIs = array(ci1_id, ci2_id, ...)
     */
    $selected_CIs = array();  //array of CI IDs
    /**
     * $CI_request_matches = array(
     *  ci1-id => array(
     *    'rd_request' => rd-req-id,
     *    'ils_requests' => array(
     *      ils-req1-id => ils-req1-period,
     *      ils-req2-id...
     *    )
     *  ),
     *  ci2-id = ...
     * )
     */
    $CI_request_matches = array();  //keep track of which requests link to which CI
    
    //ignore duplicate requests
    $processed_request_ids = array();
  
    //if CI already selected (using 'add item' link from edit-class screen)
    //add it to the list
    if(!empty($_REQUEST['ci'])) {
      $all_possible_CIs['rd_requests'][] = $_REQUEST['ci'];
      $selected_CIs[] = $_REQUEST['ci'];
    }
    
    //if processing request, add requested CI to list
    //this is truly not needed, as the next block would catch this request
    //the only reason to do this separately, is to make sure this CI is first on the list
    if(!empty($_REQUEST['request_id'])) {
      $request = new request();
      if($request->getRequestByID($_REQUEST['request_id'])) {
        if(!in_array($request->getCourseInstanceID(), $all_possible_CIs['rd_requests'])) {  //ignore duplicates
          $all_possible_CIs['rd_requests'][] = $request->getCourseInstanceID();
          $selected_CIs[] = $request->getCourseInstanceID();
          //match request to CI
          $CI_request_matches[$request->getCourseInstanceID()]['rd_request'] = $request->getRequestID();
          //add to list of processed requests
          $processed_request_ids[] = $request->getRequestID();
        }
      }
      unset($request);
    }
    
    //may not be processing requests explicitly, but still working on an existing item that has requests pending
    //attempt to find those
    foreach(request::getRequestsByItem($item_id) as $request) {
      if(!in_array($request->getRequestID(), $processed_request_ids) && !in_array($request->getCourseInstanceID(), $all_possible_CIs['rd_requests'])) { //ignore duplicates
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
      if($instructor->getUserByUserName($ils_request->getUserNetID()) || $instructor->getByILSUserID($ils_request->getUserILSID())) { //found valid user
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
   * Uses create_reserve_form data from $_REQUEST to create reserve and process requests
   *
   * @return mixed - Returns array('reserve_id'=>reserve-id, 'ils_results'=>ils-results) on success; (boolean) FALSE on failure - use strict (===) checking
   *    Usage note: if(($reserve_id = storeReserve()) !== false) { ... }
   */
  function storeReserve() {
    global $u;
    
    //need ci-id and item-id
    $ci_id = !empty($_REQUEST['ci']) ? $_REQUEST['ci'] : null;
    $item_id = !empty($_REQUEST['item_id']) ? $_REQUEST['item_id'] : null;
    
    if(empty($ci_id) || empty($item_id)) {
      return false;
    }
    
    //get a ci object
    $ci = new courseInstance($ci_id);
        
    //get an unitialized reserves object
    $reserve = new reserve();
    $item = new reserveItem($item_id);
    
    //attempt to find a reserve
    if(!empty($_REQUEST['rd_request'])) { //if there is a request, grab reserve from request
      $rd_request = new request($_REQUEST['rd_request']);
      $reserve->getReserveByID($rd_request->getReserveID());
      
      //set the request as processed
      $rd_request->setDateProcessed(date('Y-m-d'));
      
      //done with RD request - free memory
      unset($rd_request);
    }
    elseif($reserve->getReserveByCI_Item($ci_id, $item_id) === false) { //if not, try to find existing reserve
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
      $circRule = unserialize(base64_decode($_REQUEST['circRule']));
      
      $ils_results = '<ul>';  //store results of ils queries
                
      //go through physical copies
      foreach($_REQUEST['physical_copy'] as $phys_copy_raw_data) {  
        //the raw data is serialized and base64_encoded, reverse the process
        $phys_copy_raw_data = unserialize(base64_decode($phys_copy_raw_data));

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
      }
    }
    
    //process ILS requests (basically delete the ones that have been satisfied)
    if(!empty($_REQUEST['ils_requests'])) {
      foreach($_REQUEST['ils_requests'] as $ils_request_id) {
        $ils_request = new ILS_Request($ils_request_id);
        $ils_request->markAsProcessed();                
      }
    }
    
    //need to pass a couple of things back
    $return_data = array(
      'reserve_id' => $reserve->getReserveID(),
      'ils_results' => $ils_results
    );
    
    return $return_data;
  }
  

}

?>
