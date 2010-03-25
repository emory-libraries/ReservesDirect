<?
/*******************************************************************************
reservesManager.class.php


Created by Kathy Washington (kawashi@emory.edu)

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
require_once("secure/displayers/reservesDisplayer.class.php");
require_once("secure/classes/searchItems.class.php");
require_once("secure/classes/request.class.php");
require_once("secure/classes/faxReader.class.php");
require_once("secure/classes/itemAudit.class.php");
//require_once("classes/reserves.class.php");
require_once('secure/managers/noteManager.class.php');
require_once('secure/managers/classManager.class.php');

class reservesManager
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

  function reservesManager($cmd, $user)
  {
    global $g_permission, $page, $loc, $g_faxDirectory;
    global $g_documentDirectory, $g_documentURL, $ci, $g_notetype, $u, $help_article, $g_dbConn;

    $this->displayClass = "reservesDisplayer";
    //$this->user = $user;
    switch ($cmd)
    {
      case 'removeStudent':
        if ($_REQUEST['deleteAlias'])
        {
          $aliases = $_REQUEST['alias'];
          if (is_array($aliases) && !empty($aliases)){
            foreach($aliases as $a)
            {
              $user->detachCourseAlias($a);
            }
          }
        }
      case 'addStudent':
        if ($_REQUEST['aID']) {
          $user->attachCourseAlias($_REQUEST['aID']);
        }
      case 'myReserves':
      case 'viewCourseList':
        $page = "myReserves";
        $loc  = "home";

        $user->getCourseInstances();
        for ($i=0;$i<count($user->courseInstances);$i++)
        {
          $my_ci = $user->courseInstances[$i];
          $my_ci->getInstructors();
          $my_ci->getProxies();

          //Look at this later - should this logic be handled by ci->getCourseForUser? - kawashi 11.2.2004
          if (in_array($user->getUserID(),$my_ci->instructorIDs) || in_array($user->getUserID(),$my_ci->proxyIDs)) {
            //$my_ci->getCourseForInstructor($user->getUserID());
            $my_ci->getPrimaryCourse();
          } else {
            $my_ci->getCourseForUser($user->getUserID());  //load courses
          }
        }

        $this->displayFunction = "displayCourseList";
        $this->argList = array($user);
      break;
      
      
      case 'previewStudentView':  //see if($cmd==...) statement in previewReservesList  
      case 'previewReservesList':
        $ci = new courseInstance($_REQUEST['ci']);
        $ci->getInstructors();
        $ci->getCrossListings();
        $ci->getPrimaryCourse();
        
        //get different reserve data based on $cmd
        if($cmd=='previewStudentView') {
          $reserve_data = $ci->getActiveReserves(); 
        }
        elseif($cmd=='previewReservesList') {
          $reserve_data = $ci->getReserves();
        }
        
        //build treen and get recursive iterator
        $tree = $ci->getReservesAsTree(null, null, $reserve_data);
        $walker = new treeWalker($tree);
        
        $page = "myReserves";
        $loc  = "home";
        $this->displayFunction = "displayReserves";
        $this->argList = array($cmd, $ci, $walker, count($reserve_data[0]), $reserve_data[2], true);        
      break;
      
      
      case 'viewReservesList':
        $ci = new courseInstance($_REQUEST['ci']);
        $ci->getInstructors();
        $ci->getCrossListings();
        $ci->getCourseForUser($user->getUserID());
        
        //hide/unhide items
        if(!empty($_REQUEST['hideSelected'])) {
          //we need to fetch the whole tree (including hidden items)
          $reserve_data = $ci->getActiveReservesForUser($user->getUserID(), true);
          //build tree
          $tree = $ci->getReservesAsTree(null, null, $reserve_data);
          
          $hidden = !empty($_REQUEST['selected_reserves']) ? $_REQUEST['selected_reserves'] : array();
          
          //unhide first
          if(!empty($reserve_data[2])) {  //only bother if there were hidden items before
            $unhide = array_diff($reserve_data[2], $hidden);
        
            //are there changes?
            if(!empty($unhide)) {
              foreach($unhide as $r_id) { //must unhide element AND its children
                //unhide reserve
                $reserve = new reserve($r_id);
                $reserve->unhideReserve($user->getUserID());
                //unhide its children
                $walker = new treeWalker($tree->findDescendant($r_id));
                foreach($walker as $leaf) {
                  $reserve = new reserve($leaf->getID());
                  $reserve->unhideReserve($user->getUserID());
                }
              }
            }
          }
      
          //now hide (the same process in reverse)
          if(!empty($hidden)) { //only bother if anything was checked
            $hide = array_diff($hidden, $reserve_data[2]);

            //are there changes?
            if(!empty($hide)) {
              foreach($hide as $r_id) { //must hide element AND its children
                //hide reserve
                $reserve = new reserve($r_id);
                $reserve->hideReserve($user->getUserID());
                //hide its children
                $walker = new treeWalker($tree->findDescendant($r_id));
                foreach($walker as $leaf) {
                  $reserve = new reserve($leaf->getID());
                  $reserve->hideReserve($user->getUserID());
                }
              }
            }
          }         
        }
        
        //get array of reserves for this CI for tree-building
        $reserve_data = $ci->getActiveReservesForUser($user->getUserID(), $_REQUEST['showAll']);
        //build treen and get recursive iterator
        $tree = $ci->getReservesAsTree(null, null, $reserve_data);
        $walker = new treeWalker($tree);
        
        $page = "myReserves";
        $loc  = "home";
        $this->displayFunction = "displayReserves";
        $this->argList = array($cmd, $ci, $walker, count($reserve_data[0]), $reserve_data[2], false); 
      break;

      case 'customSort':
        $page = ($u->getRole() >= $g_permission['staff']) ? 'manageClasses' : 'myReserves';
        $loc  = "sort reserves list";
        $help_article = "34";
        
        $ci = new courseInstance($_REQUEST['ci']);
        
        if(isset($_REQUEST['saveOrder'])) { //update order
          foreach($_REQUEST['new_order'] as $r_id=>$order) {
            $reserve = new reserve($r_id);
            $reserve->setSortOrder($order);
          }
          $reserves = $ci->getSortedReserves('', $_REQUEST['parentID']);
        }
        else {
          $reserves = $ci->getSortedReserves($_REQUEST['sortBy'], $_REQUEST['parentID']);
        }
        
        $this->displayFunction = "displayCustomSort";
        $this->argList = array($ci, $reserves);
      break;
      
      case 'selectInstructor':
        $page = "addReserve";
        $progress = array ('total' => 4, 'full' => 0);
        if (($user->getRole() >= $g_permission['staff']) && $cmd=='selectInstructor') {
          //$user->selectUserForAdmin('instructor', $page, 'selectClass');
          $this->displayFunction = "displaySelectInstructor";
          $this->argList = array($user, $page, 'addReserve');
        }
      break;
      
      case 'addReserve':
        $page = "addReserve";
        $progress = array ('total' => 4, 'full' => 0);
        $loc = "add a reserve";
        $help_article = "15";

        if ($user->getRole() >= $g_permission['staff']) {
          //$courseInstances = $user->getCourseInstances($_REQUEST['u']);
          $this->displayFunction = "displayStaffAddReserve";
          $this->argList = array($_REQUEST);
          break;
        }
        elseif ($user->getRole() >= $g_permission['proxy']) { //2 = proxy
          if(empty($_REQUEST['ci'])) {  //get ci
            $courses = $u->getCourseInstancesToEdit();          
            $this->displayFunction = 'displaySelectClass';          
            $this->argList = array($cmd, $courses);           
            break;
          }
          else {
            $this->displayFunction = "displaySearchItemMenu";
            $this->argList = array($_REQUEST['ci']);
          }
        }
      break;
      
      case 'searchScreen':
        $page = "addReserve";
        $loc = "search for an item";
        $help_article = "19";
        

        $this->displayFunction = "displaySearchScreen";
        $this->argList = array($page, 'searchResults', $_REQUEST['ci']);
      break;
      case 'searchResults':
        $page = "addReserve";
        $loc = "search for an item";
        $help_article = "19";
        
        $search = new searchItems();

        if (isset($_REQUEST['f'])) {
          $f = $_REQUEST['f'];
        } else {
          $f = "";
        }

        if (isset($_REQUEST['e'])) {
          $e = $_REQUEST['e'];
        } else {
          $e = "";
        }

        $search->search($_REQUEST['field'], urldecode($_REQUEST['query']), $f, $e);

        if (isset($_REQUEST['request'])) {
          $HiddenRequests = $_REQUEST['request'];
        } else {
          $HiddenRequests = "";
        }
        if (isset($_REQUEST['reserve'])) {
          $HiddenReserves = $_REQUEST['reserve'];
        } else {
          $HiddenReserves = "";
        }
                
        if (!$ci->course instanceof course)
          $ci->getPrimaryCourse();
        
        if (!$ci->course->department instanceof department)
          $ci->course->getDepartment();
        $LoanPeriods = $ci->course->department->getInstructorLoanPeriods();

        $this->displayFunction = "displaySearchResults";
        $this->argList = array($user, $search, 'storeReserve', $_REQUEST['ci'], $HiddenRequests, $HiddenReserves, $LoanPeriods);
      break;
      case 'storeReserve':
        $page = "addReserve";
        
        //attempt to use transactions
        if($g_dbConn->provides('transactions')) {
          $g_dbConn->autoCommit(false);
        }
        try {
          $requests = (isset($_REQUEST['request'])) ? $_REQUEST['request'] : null;
          $items = (isset($_REQUEST['reserve'])) ? $_REQUEST['reserve'] : null;
  
          $ci = new courseInstance($_REQUEST['ci']);
  
          //add items to reserve
          if (is_array($items) && !empty($items)){
            foreach($items as $i_id)
            {
              $reserve = new reserve();
              if ($reserve->createNewReserve($ci->getCourseInstanceID(), $i_id))
              {
                $reserve->setActivationDate($ci->getActivationDate());
                $reserve->setExpirationDate($ci->getExpirationDate());
                //attempt to insert this reserve into order
                $reserve->getItem();
                $reserve->insertIntoSortOrder($ci->getCourseInstanceID(), $reserve->item->getTitle(), $reserve->item->getAuthor());
              }
            }
          }
  
          //make requests
          if (is_array($requests) && !empty($requests)){
            foreach($requests as $i_id)
            {
              //store reserve with status processing
              $reserve = new reserve();
              if ($reserve->createNewReserve($ci->getCourseInstanceID(), $i_id))
              {
                $reserve->setStatus("IN PROCESS");
                $reserve->setActivationDate($ci->getActivationDate());
                $reserve->setExpirationDate($ci->getExpirationDate());
                $reserve->setRequestedLoanPeriod($_REQUEST['requestedLoanPeriod_'.$i_id]);
                //attempt to insert this reserve into order
                $reserve->getItem();
                $reserve->insertIntoSortOrder($ci->getCourseInstanceID(), $reserve->item->getTitle(), $reserve->item->getAuthor());
  
                //create request
                $request = new request();
                //make sure request does not exist
                //prevent duplicate requests
                if($request->getRequestByCI_Item($ci->getCourseInstanceID(), $i_id) === false) {
                  $request->createNewRequest($ci->getCourseInstanceID(), $i_id);
                  $request->setRequestingUser($user->getUserID());
                  $request->setReserveID($reserve->getReserveID());
                }
              }
            }
          }
        } catch (Exception $e) {
          trigger_error("Error Occurred While processing StoreRequest ".$e->getMessage(), E_USER_ERROR);
          if($g_dbConn->provides('transactions')) { 
            $g_dbConn->rollback();
          }         
        }
        //commit this set
        if($g_dbConn->provides('transactions')) { 
          $g_dbConn->commit();
        }
                
        $this->displayFunction = "displayReserveAdded";
        $this->argList = array($user, null, $_REQUEST['ci']);
      break;

      /** faculty-specific uploadDocument, addURL and storeUploaded actions removed,
          consolidated with add/edit item **/
      
      case 'faxReserve':
        $page="addReserve";
        $loc = "fax a document";
        $help_article = "17";
        $this->displayFunction = "displayFaxInfo";
        $this->argList = array($_REQUEST['ci']);
      break;
      case 'getFax':
        $page="addReserve";
        $loc = "claim your fax";
        $help_article = "17";
        $faxReader = new faxReader();
        $faxReader->getFaxesFromFile($g_faxDirectory);

        $this->displayFunction = "claimFax";
        $this->argList = array($faxReader, $_REQUEST['ci']);
      break;
      case 'addFaxMetadata':
        $page="addReserve";
        $loc = "add fax document information";
        $help_article = "17";
        $faxReader = new faxReader();

        $claims =& $_REQUEST['claimFax'];

        $claimedFaxes = array();
        if (is_array($claims) && !empty($claims))
        {
          foreach ($claims as $claim)
            $claimedFaxes[] = $faxReader->parseFaxName($claim);
        }

        $this->displayFunction = "displayFaxMetadataForm";
        $this->argList = array($user, $claimedFaxes, $_REQUEST['ci']);
      break;
      case 'storeFaxMetadata':
        $page="addReserve";
        $files = array_keys($_REQUEST['file']);

        $items = array();
        foreach ($files as $file)
        {
          $ci = new courseInstance($_REQUEST['ci']);

          $item = new reserveItem();
          $item->createNewItem();

          $item->setTitle($_REQUEST[$file]['title']);
          $item->setAuthor($_REQUEST[$file]['author']);
          $item->setVolumeTitle($_REQUEST[$file]['volumetitle']);
          $item->setVolumeEdition($_REQUEST[$file]['volume']);
          
          //store the fax
          $md5 = md5_file($g_faxDirectory . $_REQUEST['file'][$file]);
                    $dst_dir = $g_documentDirectory . substr($md5,0,2);
            
          $dst_fname = "{$md5}_{$item->getItemID()}.pdf";
          if(!copy($g_faxDirectory . $_REQUEST['file'][$file], "$dst_dir/$dst_fname")) {
            trigger_error('Failed to copy file '.$g_faxDirectory . $_REQUEST['file'][$file] . ' to ' . "$dst_dir/$dst_fname", E_USER_ERROR);
          }
  
          $item->setURL(substr($md5,0,2) . "/" . $dst_fname);
          $item->setMimeType('application/pdf');

          $p = $_REQUEST[$file]['pagefrom'] . "-" . $_REQUEST[$file]['pageto'];
          if ($p != "-") $item->setPagesTimes($p);

          if ($_REQUEST[$file]['personal'] == "on") $item->setPrivateUserID($user->getUserID());

          $item->setGroup('ELECTRONIC');
          $item->setType('ITEM');

          $reserve = new reserve();

          if ($reserve->createNewReserve($ci->getCourseInstanceID(), $item->getItemID()))
          {
              if(!empty($_REQUEST[$file]['noteText']) && isset($_REQUEST[$file]['noteType'])) {
              if($_REQUEST[$file]['noteType']==$g_notetype['instructor']) {
                $reserve->setNote($_REQUEST[$file]['noteText']);
              }
              else {
                $item->setNote($_REQUEST[$file]['noteText']);
              }
            }
            
            $reserve->setActivationDate($ci->getActivationDate());
            $reserve->setExpirationDate($ci->getExpirationDate());
            //attempt to insert this reserve into order
            $reserve->getItem();
            $reserve->insertIntoSortOrder($ci->getCourseInstanceID(), $reserve->item->getTitle(), $reserve->item->getAuthor());

            $itemAudit = new itemAudit();
            $itemAudit->createNewItemAudit($item->getItemID(),$user->getUserID());
          }
        }

        $this->displayFunction = "displayReserveAdded";
        $this->argList = array($user, null, $_REQUEST['ci']);
      break;
      
      case 'editMultipleReserves':
        //determine what we are trying to do with the multiple reserves
        
        if(isset($_REQUEST['edit_multiple'])) { //want to edit some reserve info
          //show form
          $ci = new courseInstance($_REQUEST['ci']);          
          $page = 'addReserve';
          $loc = 'edit multiple reserves';  
          $help_article = "41";
          $this->displayFunction = 'displayEditMultipleReserves';
          $this->argList = array($ci, $_REQUEST['selected_reserves']);
        }
        else {  //need to perform the action (save edits / delete / copy)
          //need the CI
          $ci = new courseInstance($_REQUEST['ci']);
          
          if (isset($_REQUEST['approve_copyright']))
          {
            $ci->setReviewed($u->getUserID(), date("Y-m-d"));
          }
          //get array of selected reserve IDs
          $reserve_ids = !empty($_REQUEST['selected_reserves']) ? $_REQUEST['selected_reserves'] : array();
                  
          //copy reserves
          if(isset($_REQUEST['copy_multiple'])) {
            classManager::classManager('copyItems', $u, $adminUser, array('originalClass'=>$_REQUEST['ci'], 'reservesArray'=>$reserve_ids));
            break;  //do not go further
          }
          
          //determine if need to pull in descendants for selected headings
          //only need descendants if deleting OR editing status/dates OR setting copyright flags
          if(isset($_REQUEST['delete_multiple']) || isset($_REQUEST['submit_edit_multiple']) 
            || isset($_REQUEST['copyright_deny_class']) || isset($_REQUEST['copyright_deny_all_classes'])) {
            //get reserve tree
            $tree = $ci->getReservesAsTree('getReserves');
            
            //build a new reserve IDs array that includes all descendants
            $reserve_ids_with_desc = array();
            foreach($reserve_ids as $r_id) {
              //add the reserve
              if(!isset($reserve_ids_with_desc[$r_id])) {
                $reserve_ids_with_desc[$r_id] = $r_id;  //index by id to prevent duplicate values
                $walker = new treeWalker($tree->findDescendant($r_id)); //get the node with that ID
                foreach($walker as $leaf) {
                  $reserve_ids_with_desc[$leaf->getID()] = $leaf->getID();  //add child to array
                }
              }
            }
            
            //go through all reserves and their descendants and delete or set status/dates
            foreach($reserve_ids_with_desc as $reserve_id) {
              //init the reserve object
              $reserve = new reserve($reserve_id);
              $reserve->getItem();
              
              //delete reserve
              if(isset($_REQUEST['delete_multiple'])) {
                //delete request for physical items
                if($reserve->item->isPhysicalItem()) {
                  $request = new request();
                  $request->getRequestByReserveID($reserve_id);
                  $request->destroy();
                }
                //delete reserve
                $reserve->destroy();
              }

              if (isset($_REQUEST['copyright_deny_class']) || isset($_REQUEST['copyright_deny_all_classes']))
              {
                //physical items cant be denied copyright
                if (!$reserve->item->isPhysicalItem()) {
                                    if (isset($_REQUEST['copyright_deny_all_classes']))
                                        $reserve->item->setStatus('DENIED');
                                    else
                                        $reserve->setStatus('DENIED');
                }
                
                //if request exists and is not processed set as processed
                $request = new request();
                $request->getRequestByReserveID($reserve_id);
                if (!is_null($request->requestID) && is_null($request->processedDate))
                {
                                    //do not attempt to set if Request was not found by reserveID
                  $request->setDateProcessed(date('Y-m-d'));
                }
              }
              
              if(isset($_REQUEST['edit_status']) && isset($_REQUEST['reserve_status'])) {
                //do NOT allow anyone to change status of a physical item that is 'IN PROCESS'  
                //do NOT allow < Staff to change Copyright status 
                if ((($u->getRole() == $g_permission['proxy'] || $u->getRole() == $g_permission['instructor']) 
                  && ($reserve->getStatus() == 'ACTIVE' || $reserve->getStatus() == 'INACTIVE'))
                  || ($u->getRole() >= $g_permission['staff'] && !($reserve->item->isPhysicalItem() || $reserve->getStatus() == 'IN PROCESS' )))
                {
                  $reserve->setStatus($_REQUEST['reserve_status']);
                }
              }
              
              //edit dates
              if(isset($_REQUEST['edit_dates'])) {
                //do not change dates of a heading
                if(!$reserve->isHeading()) {
                  if(!empty($_REQUEST['reserve_activation_date'])) {
                    $reserve->setActivationDate($_REQUEST['reserve_activation_date']);
                  }
                  if(!empty($_REQUEST['reserve_expiration_date'])) {
                    $reserve->setExpirationDate($_REQUEST['reserve_expiration_date']);
                  }
                } 
              }

            } //end reserves and decendants loop  
          }
          
          //changes to parent headings and notes do not apply to descendants of a heading         
          if(isset($_REQUEST['submit_edit_multiple']) && (isset($_REQUEST['edit_heading']) || isset($_REQUEST['edit_note']))) {
            //go only through the selected reserves
            foreach($reserve_ids as $reserve_id) {
              //init reserve object
              $reserve = new reserve($reserve_id);
              
              //edit heading
              if(isset($_REQUEST['edit_heading']) && !empty($_REQUEST['heading_select'])) {
                $reserve->setParent($_REQUEST['heading_select'], true);
              }
              
              //add note
              if(isset($_REQUEST['edit_note']) && !empty($_REQUEST['note_text'])) {
                noteManager::saveNote('reserve', $reserve->getReserveID(), $_REQUEST['note_text'], $_REQUEST['note_type']);
              }             
            }
          }
          
          //go back to editClass
          $_REQUEST = array();
          $_REQUEST['ci'] = $ci->getCourseInstanceID(); //pass CI to editClass
          classManager::classManager('editClass', $u, null, null);
        }     
      break;
    }
  }
}
?>
