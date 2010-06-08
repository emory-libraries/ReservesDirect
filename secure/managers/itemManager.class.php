<?
/*******************************************************************************
itemManager.class.php

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
require_once("secure/displayers/itemDisplayer.class.php");
require_once("secure/displayers/requestDisplayer.class.php");
require_once("secure/managers/baseManager.class.php");
require_once("secure/managers/classManager.class.php");
require_once('secure/managers/reservesManager.class.php');
require_once("secure/managers/requestManager.class.php");
require_once('secure/classes/note.class.php');

/**
 * handle item related functions
 * processes these commands:
 * - editItem
 * - editHeading
 * - processHeading
 * - duplicateReserve
 */
class itemManager extends baseManager {

  public $user;
  /**
   * default display class to be used for displaying itemManager actions
   * @var string
   */
  public $displayClass = "itemDisplayer";
  public $displayFunction;
  public $argList;

  private $command;

  /**
   * handle item manager commands, initialize for hand-off to displayer
   * @param string $cmd command to be run, currently one of:
   * - editItem
   * - editHeading
   * - processHeading
   * - duplicateReserve
   * @param user $user currently logged in user
   * FIXME: how is $user param different from global $u user?
   */
  public function __construct($cmd, $user) {
    global $g_permission, $g_documentURL, $page, $loc, $ci, $u, $help_article;

    $this->command = $cmd;
    
    switch ($cmd)
    {
      
      // *
      // This case depends on editItem and so MUST COME IMMEDIATELY BEFORE `case 'editItem':`     
      // *      
      case 'duplicateReserve':  //duplicates reserve AND item
        if(empty($_REQUEST['reserveID']))
          break;  //error, no reserveID set
                
        //get the source reserve
        $srcReserve = new reserve($_REQUEST['reserveID']);        
        //duplicate it
        $dupReserveID = $srcReserve->duplicateReserve();        
        
        //set up some vars
        
        $selected_instr = $_REQUEST['selected_instr'];  //remember instructor
        
        $_REQUEST = array();  //clear current request
        
        $_REQUEST['reserveID'] = $dupReserveID; //set up new reserveID
        $_REQUEST['dubReserve'] = true; //set flag, to let editItem handler know this is a dupe
        $_REQUEST['selected_instr'] = $selected_instr;  //set instructor

      //use editItem
      //no break!

      case 'addDigitalItem':  // creating/editing digital items
        // override label for display page
        $loc  = "add electronic item";
        // fall through to editItem       
        
      case 'editItem':
        // initialize item and reserve (may be editing item or editing reserve+item)
        list($item, $reserve) = $this->getReserveItem();

        //form submitted - edit item meta
        if(!empty($_REQUEST['submit_edit_item_meta'])) {      
          // if editing a reserve, save reserve-specific fields?
          if ($reserve instanceof reserve) {            
            $this->saveReserve($reserve);
          }
          $invalid = $this->editItemValidation();
          if ($invalid) {
            //form is invalid; re-display edit page with error messages
            $this->prepEditItem($item);
            // add validation errors to display arguments
            $this->argList[] = $invalid;
            
          } else {
            
            // Check to see if the previous material type is BOOK_PORTION, and the new material type is not BOOK_PORTION
            // OR if the material type is BOOK_PORTION and the ISBN has changed.
            // If so, delete the rightsholder information for this (old) ISBN if it is not used by any other item.         
            if (  ($_REQUEST['material_type'] == 'BOOK_PORTION' && $item->getISBN() != $_REQUEST['ISBN']) ||  
                  ($_REQUEST['material_type'] != 'BOOK_PORTION' && $item->getMaterialType() == 'BOOK_PORTION')) {
              if ($item->countISBNUsage() == 1) {  // if there is only one item that refs the ISBN, then delete the rightsholder info.  
                $rh = $item->getRightsholder($item->getISBN()); // get the instance of the current item's rightsholder
                if ( ! is_null($rh) )  $rh->destroy();    // Delete the rightsholder from the rightsholders table.
              }
            }
                        
            // store whether or not this was a new item before updating the DB
            $new_item = (! $item->itemID);
            
            //form is valid--  set item data
            $item_id = $this->storeItem($item);
            
            //if duplicating, show a different success screen
            if(isset($_REQUEST['dubReserve']) && $_REQUEST['dubReserve']) {
              //get course instance
              $ci = new courseInstance($reserve->getCourseInstanceID());
              $ci->getPrimaryCourse();
              
              //call requestDisplayer method
              require_once("secure/displayers/requestDisplayer.class.php");
              $loc = 'add an item';
              $this->displayClass = 'requestDisplayer';
              $this->displayFunction = 'addSuccessful';
              $this->argList = array($ci, $item->getItemID(), $reserve->getReserveID(), true);
            } elseif ($new_item) {
              // when creating a brand-new item, promp user for which course instance
              // the reserve item should be added to
              
              //prefetch possible CIs
              list($all_possible_CIs, $selected_CIs,
                   $CI_request_matches) = requestManager::getCIsForItem($item_id);
              //pass the item_id and CIs to the select-course form
              $this->displayClass = "requestDisplayer";
              $this->displayFunction = 'displaySelectCIForItem';
              $this->argList = array($item_id, $all_possible_CIs,
              $selected_CIs, $CI_request_matches);

            } else {
              // get courseinstance id, if editing reserve
              $ci_id = ($reserve instanceof reserve) ? $reserve->getCourseInstanceID() : null;
              
              // display success
              $this->displayFunction = 'displayItemSuccessScreen';
              $this->argList = array($ci_id);
              if (!empty($_REQUEST['search']))
                $this->argList[] = urlencode($_REQUEST['search']);
            }
          }
        }  else {  //display edit page
          $this->prepEditItem($item, $reserve);
        }     
        break;

      case 'addPhysicalItem': // create/edit physical items and/or processing requests
        $page = "addReserve";
        $loc  = "add physical item";
       
        if(isset($_REQUEST['store_request'])) { //form submitted, process item
          
          //store item meta data
          $item_id = $this->storeItem();

          //prefetch possible CIs
          list($all_possible_CIs, $selected_CIs, $CI_request_matches) = requestManager::getCIsForItem($item_id);

          //pass on the searched-for barcode
          if(!empty($_REQUEST['searchTerm']) && ($_REQUEST['searchField'] == 'barcode')) {
            $requested_barcode = $_REQUEST['searchTerm'];
          }
          else {
            $requested_barcode = null;
          }

          $this->displayClass = "requestDisplayer";
          $this->displayFunction = 'displaySelectCIForItem';
          $this->argList = array($item_id, $all_possible_CIs, $selected_CIs,
              $CI_request_matches, $requested_barcode);
        } else {  //show edit item form
          //if searching for an item, get form pre-fill data
          $item = $this->searchItem();
          $this->prepEditItem($item);
        }
        break;
      

      case 'editHeading':
        $page = ($u->getRole() >= $g_permission['staff']) ? 'manageClasses' : 'addReserve';
        $loc = "edit heading";
        $help_article = "35";
        
        $headingID = !empty($_REQUEST['headingID']) ? $_REQUEST['headingID'] : null;
        $heading = new reserve($headingID);
        
        $this->displayFunction = 'displayEditHeadingScreen';
        $this->argList = array($_REQUEST['ci'], $heading);
      break;
      
      case 'processHeading':
        $page = "myReserves";
        $loc = "edit heading";
        $help_article = "35";

        $ci = new courseInstance($_REQUEST['ci']);
        $headingText = $_REQUEST['heading'];
        $headingID = $_REQUEST['headingID'];
        
        if(empty($headingID)) { //need to create a new item
          if ($headingText) {
            $heading = new item();        
            $heading->createNewItem();
            $heading->makeHeading();
            $reserve = new reserve();
            $reserve->createNewReserve($ci->getCourseInstanceID(), $heading->itemID);
            $reserve->setStatus('ACTIVE');
            $reserve->setActivationDate($ci->activationDate);
            $reserve->setExpirationDate($ci->expirationDate);
            $reserve->insertIntoSortOrder($ci->getCourseInstanceID(), $headingText, 'zzzzz'); //zzzz will put the heading last if author-sorted
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


  /**
   * find reserve or reserve + item depending on the request
   * - editItem can take a reserve id or an item id; determine which is in use,
   *   and initialize reserveItem, reserve, and global course instance accordingly
   * @return array of reserveItem, reserve
   */
  function getReserveItem() {
    global $ci;  
    
    //switch b/n editing item or editing reserve+item
    if(!empty($_REQUEST['reserveID'])) {      // item+reserve      
      //get reserve
      $reserve = new reserve($_REQUEST['reserveID']);
      //get item
      $item = new reserveItem($reserve->getItemID());
      //init a courseInstance to show location        
      $ci = new courseInstance($reserve->getCourseInstanceID());

    } elseif(!empty($_REQUEST['itemID'])) {     //editing item only   
      $item = new reserveItem($_REQUEST['itemID']);
      // set reserve to null, since some functions assume it is set
    } else {      
      // if no ID is set, create a new reserve item
      $item =  new reserveItem();
      // if adding digital item, initialize as electronic so correct portions of form will display
      if ($this->command == "addDigitalItem")
        $item->itemGroup = 'ELECTRONIC';
    }

    if (!isset($reserve)) $reserve = null;
        

    return array($item, $reserve);
  }

    
  /**
   * Attempts to find an item in DB and/or (if physical item) in
   * ILS; return reserveItem prefilled w/ item data (if any)
   *
   * @return reserveItem
   */
  function searchItem() {
    global $alertMsg;
    
    //decide if item info can be prefilled
    $item_id = null;
    if (isset($_REQUEST['item_id']) && !is_null($_REQUEST['item_id'])) {
      $item_id = $_REQUEST['item_id'];
    } elseif (isset($_REQUEST['reserve_id']) && !is_null($_REQUEST['reserve_id'])) {
      $reserve = new reserve($_REQUEST['reserve_id']);
      $item_id = $reserve->getItem();     
    } elseif (isset($_REQUEST['request_id']) && !is_null($_REQUEST['request_id'])) {
      $request = new request($_REQUEST['request_id']);
            $request->getRequestedItem();
      $item_id = $request->requestedItem->getItemID();
    }
    
    $item = new reserveItem($item_id);
    // if adding physical item, set item to identify as physical (place-holder value, should be overridden)
    if ($this->command = "addPhysicalItem")
      $item->itemGroup = 'MONOGRAPH';
    
    $qryField = $qryValue = null;
    
    if(!empty($_REQUEST['searchField']) && is_null($item->itemID)) {  //search info specified     
      //find item in local DB by barcode or control key
      if($_REQUEST['searchField'] == 'barcode') { //get by barcode
        $phys_item = new physicalCopy();
        if($phys_item->getByBarcode($_REQUEST['searchTerm'])) {
    $item->getItemByID($phys_item->getItemID());
        }
      } else {  //get by local control
        $item->getItemByLocalControl($_REQUEST['searchTerm']);
      }         
    }
    
    if(!empty($_REQUEST['request_id'])) { //processing request, get info out of DB
      $request = new request();
      if($request->getRequestByID($_REQUEST['request_id'])) {
        //init reserveItem object
        $request->getRequestedItem();       
        
        //alert if returned is different than item attached to request
        if (!is_null($item->itemID) && $item != $request->requestedItem)
    {
      $alertMsg = "This search has matched a different item from that requested. Before continuing please verify you are processing \"{$item->getTitle()}\".  If this is not correct please stop and contact your local admin.";
    }       
        
      }
    } 
    
    //if item controlKey is set use it to search ILS otherwise use form values
    if (!is_null($item) && $item->getLocalControlKey() <> "")   {
      //set search parameters
      $qryField = 'control';
      $qryValue = $item->getLocalControlKey();
    } else {
      $qryField = isset($_REQUEST['searchField']) ? $_REQUEST['searchField'] : "";
      $qryValue = isset($_REQUEST['searchTerm']) ? $_REQUEST['searchTerm'] : "";
    }
    //if searching for a physical item, then there may be an ILS record
    //this should return an indexed array, which may be populated w/ data
    if(($this->command == 'addPhysicalItem') && !empty($qryValue)) {
      //query ILS
      //$zQry = new zQuery($qryValue, $qryField);
      $zQry = RD_Ils::initILS();
      $search_results = $zQry->search($qryField, $qryValue)->to_a();
      
      //if still do not have an initialized item object
      //try one more time by control key pulled from ILS
      $item_id = $item->getItemID();
      if(empty($item_id)) {
        $item->getItemByLocalControl($search_results['controlKey']);
      }

      //this is not needed at the moment, b/c do not want to show holdings for addPhysicalItem/processRequest
      //but that may change, so it's here, but commented
      //      $search_results['physicalCopy'] = null;
      //get holdings    
      //$search_results['physicalCopy'] = $zQry->getHoldings($qryField, $qryValue);
    }
      
    // set item values from db if they exist otherwise default to searched values
    // this may still result in a blank item, if there was no item found
    if (isset($search_results)) {
      if ($item->getTitle() == "" )   $item->title    = $search_results['title'];
      if ($item->getAuthor() == "" )  $item->author     = $search_results['author'];
      if ($item->getVolumeEdition() == "" ) $item->volumeEdition  = $search_results['edition'];
      if ($item->getPerformer() == "" )   $item->performer  = $search_results['performer'];
      if ($item->getVolumeTitle() == "" )   $item->volumeTitle  = $search_results['volume_title'];
      if ($item->getPagesTimes() == "" )  $item->pagesTimes   = $search_results['times_pages'];
      if ($item->getSource() == "" )  $item->source     = $search_results['source'];
      if ($item->getLocalControlKey()=="")  $item->localControlKey  = $search_results['controlKey'];
      if ($item->getOCLC() == "")   $item->OCLC   = $search_results['OCLC'];
      if ($item->getISSN() == "")   $item->ISSN   = $search_results['ISSN'];
      if ($item->getISBN() == "")   $item->ISBN   = $search_results['ISBN'];
    }
    // FIXME: what is this? how to set on item?
    // $item_data['physicalCopy'] = $search_results['physicalCopy'];
    
    return $item;
  }

  

  /**
   * set up for displaying editItem page
   * - sets page, location, help article, and arguments for display function
   * @param reserveItem $item
   * @param reserve $reserve (optional)
   */
  function prepEditItem($item, $reserve = null) {
    global $page, $loc, $help_article;
    $page = "addReserve";
    if ($loc == "") $loc  = "edit item";
    $help_article = "33";
    $this->displayFunction = 'displayEditItem';

    $dub_array = array();
    if (!empty($_REQUEST['dubReserve']))
      $dub_array['dubReserve'] = $_REQUEST['dubReserve'];
    if (!empty($_REQUEST['selected_instr']))
      $dub_array['selected_instr'] = $_REQUEST['selected_instr'];

    $this->argList = array($item, $reserve, $dub_array);
    // if course id is set (e.g., adding new digital item from a class), pass on
    if (!empty($_REQUEST['ci']))
      $this->argList['ci'] = $_REQUEST['ci'];
  }

  /**
   * update reserve from submitted edit form
   *
   */
  function saveReserve($reserve) 
  {
    //set status
    $reserve->setStatus($_REQUEST['reserve_status']);
    
    //set dates, if status is ACTIVE
    if($_REQUEST['reserve_status']=='ACTIVE') {
      //if not empty, set activation and expiration dates
      if(!empty($_REQUEST['reserve_activation_date'])) {
        $reserve->setActivationDate($_REQUEST['reserve_activation_date']);
      }
      if(!empty($_REQUEST['reserve_expiration_date'])) {
        $reserve->setExpirationDate($_REQUEST['reserve_expiration_date']);
      }   
    }
    
    // set copyright status
    if(isset($_REQUEST['copyright_status']))   {
      $reserve->setCopyrightStatus($_REQUEST['copyright_status']);      
    }
    
    //set parent heading
    if(!empty($_REQUEST['heading_select'])) {
      $reserve->setParent($_REQUEST['heading_select'], true);
    } 
  }


  /**
   * Edits or creates a new item, using the data posted from the add/edit item form.
   * @param reserveItem $item item to edit; optional (if not set, creates new item)
   * @return int item id
   */
  function storeItem($item = null) {
    global $u;      
    
    //when adding a 'MANUAL' physical item, the physical-copy data is hidden, but still passed on by the form
    //make sure that we do not use it
    if(!empty($_REQUEST['addType']) && ($_REQUEST['addType'] == 'MANUAL')) {
      unset($_REQUEST['physical_copy']);
    }

    // if no reserveItem was passed in, create a new one
    if ($item == null || !$item->itemID) {
      // FIXME: should this check be added to getReserveItem ?
      //    if(empty($_REQUEST['item_id']) || !$item->getItemByID($_REQUEST['item_id']))   //If missing item_id or it is invalid
      //create item
      if ($item == null) $item = new reserveItem();
      $item->createNewItem(); 
      //audit the action
      $itemAudit = new itemAudit();
      $itemAudit->createNewItemAudit($item->getItemID(),$u->getUserID());
      unset($itemAudit);                
    }
    
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

    // not in requestManager; only applicable when updating items?
    if(isset($_REQUEST['item_status'])) $item->setStatus($_REQUEST['item_status']);
    
    if(isset($_REQUEST['publisher'])) $item->setPublisher($_REQUEST['publisher']);
    if(isset($_REQUEST['availability'])) $item->setAvailability($_REQUEST['availability']);
    
    if(isset($_REQUEST['used_times_pages'])) $item->setUsedPagesTimes($_REQUEST['used_times_pages']);
    if(isset($_REQUEST['total_times_pages'])) $item->setTotalPagesTimes($_REQUEST['total_times_pages']);        
    
    if(isset($_REQUEST['material_type'])) $item->setMaterialType($_REQUEST['material_type'],
                       $_REQUEST['material_type_other']);
    
    //this will be an ILS-assigned key for physical items, or a manually-entered barcode for electronic items
    // FIXME: is local control key only applicable to physical items?
    // was only set on physical items in itemManager/editItem; set for both on add
    if(isset($_REQUEST['local_control_key'])) $item->setLocalControlKey($_REQUEST['local_control_key']);

    $rh = $item->getRightsholder($_REQUEST['ISBN']);
    if ( ! is_null($rh) ) {
      if ($_REQUEST['material_type'] == 'BOOK_PORTION') {
        if (isset($_REQUEST['rh_name'])) $rh->setName($_REQUEST['rh_name']);
        if (isset($_REQUEST['rh_contact_name'])) $rh->setContactName($_REQUEST['rh_contact_name']);
        if (isset($_REQUEST['rh_contact_email'])) $rh->setContactEmail($_REQUEST['rh_contact_email']);
        if (isset($_REQUEST['rh_fax'])) $rh->setFax($_REQUEST['rh_fax']);
        if (isset($_REQUEST['rh_rights_url'])) $rh->setRightsUrl($_REQUEST['rh_rights_url']);
        if (isset($_REQUEST['rh_policy_limit'])) $rh->setPolicyLimit($_REQUEST['rh_policy_limit']);
        if (isset($_REQUEST['rh_post_address'])) $rh->setPostAddress($_REQUEST['rh_post_address']);
      }
    }

    //physical item data
    if($item->isPhysicalItem()) {
      if (isset($_REQUEST['home_library'])) $item->setHomeLibraryID($_REQUEST['home_library']);
      
      //physical copy data
      if($item->getPhysicalCopy()) {  //returns false if not a physical copy
        //only set these if they were part of the form
        if(isset($_REQUEST['barcode'])) $item->physicalCopy->setBarcode($_REQUEST['barcode']);
        if(isset($_REQUEST['call_num'])) $item->physicalCopy->setCallNumber($_REQUEST['call_num']);             
      }
    }

    // add a new note, if set (creating new record)
    // FIXME: came from addDigitalItem; is this included in editItem form ?
    if(!empty($_REQUEST['new_note'])) {
      $item->setNote($_REQUEST['new_note'], $_REQUEST['new_note_type']);
    }

    // process all other notes (editing existing record)
    if(!empty($_REQUEST['notes'])) {
      foreach($_REQUEST['notes'] as $note_id=>$note_text) {
        if(!empty($note_id)) {
    $note = new note($note_id);
    $note->setText($note_text);
        }
      }
    }

    
    //if adding electronic item, need to process file or link
    // if updating existing item, check for new version of file or url
    if(!$item->isPhysicalItem() && !empty($_REQUEST['documentType'])) {
      if($_REQUEST['documentType'] == 'DOCUMENT') { //uploading a file
        $file = common_storeUploaded($_FILES['userFile'], $item->getItemID());                            
        $file_loc = $file['dir'] . $file['name'] . $file['ext'];
        $item->setURL($file_loc);
        $item->setMimeTypeByFileExt($file['ext']);
        // FIXME: this block was only in editItem; redundant or problematic when creating new?
        if ($item->copyrightReviewRequired()) {
          $classes = $item->getAllCourseInstances();
          for($i=0; $i < sizeof($classes); $i++) {
            $classes[$i]->clearReviewed();
          }
        }
      }
      elseif($_REQUEST['documentType'] == 'URL') {  //adding a link
        $item->setURL($_REQUEST['url']);
      }
      //else maintaining the same link; do nothing
    }
    
    // return item id
    return $item->getItemID();  
  }
  



  /**
   * check submitted fields from edit item form for required values
   * @return array error messages for each missing required field
   */
  function editItemValidation() {
    $err = array();
    
    if(!isset($_REQUEST['material_type']) || ($_REQUEST['material_type'] == '')) {
      $err[] = "Type of material is required.";
    } elseif (($_REQUEST['material_type'] == 'OTHER') &&
        ($_REQUEST['material_type_other'] == '')) {
      $err[] = "Type of material detail is required when 'Other' is selected.";
    }

    // url/uploaded file required for NEW items
    // optional for updating existing items
    // FIXME: update text to work for update & create
    if(!empty($_REQUEST['documentType'])) {
      if (($_REQUEST["documentType"] == "URL") &&
    (!isset($_REQUEST["url"]) || ($_REQUEST["url"] == ""))) {
        $err[] = "Selected 'add a link', but no URL was specified.";
      } elseif (($_REQUEST["documentType"] == "DOCUMENT") &&
          ($_FILES["userFile"]["name"] == '')) {
        $err[] = "Selected 'upload a document', but no file was uploaded.";
      }
    }
    
    // validate required fields for selected material type
    if (isset($_REQUEST['material_type']) && $_REQUEST['material_type']) {
      $materialType_details = common_materialTypesDetails();
      foreach ($materialType_details[$_REQUEST['material_type']] as $field => $details) {
        // convert field name to form input 
        switch ($field) {
        case "work_title": $input = "volume_title"; break;
        case "edition": $input = "volume_edition"; break;
        case "year": $input = "source"; break;
        case "isbn":
        case "issn":
        case "oclc":
    $input = strtoupper($field); break;
        case "barcode": $input = "local_control_key"; break;
        default: $input = $field;
        }
        if (isset($details["required"]) && $details["required"]
      && (!isset($_REQUEST[$input]) || $_REQUEST[$input] == "")) {
    $err[] = $details["label"] . " is required.";   
        }
      }
    }

    return $err;
  }

}

?>
