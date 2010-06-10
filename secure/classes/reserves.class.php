<?
/*******************************************************************************
reserve.class.php
Reserve Primitive Object

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
require_once("secure/classes/reserveItem.class.php");
require_once('secure/classes/notes.class.php');

class reserve extends Notes {
  
  //Attributes
  public $reserveID;
  public $courseInstanceID;
  public $itemID;
  public $parentID;
  public $item;
  public $activationDate;
  public $expirationDate;
  public $sortOrder;
  public $status;
  public $copyrightStatus;
  public $creationDate;
  public $lastModDate;
  public $hidden = false;
  public $requested_loan_period;
  
  /**
  * @return reserve
  * @param int $reserveID
  * @desc initalize the reserve object
  */
  function reserve($reserveID = NULL)
  {
    if (!is_null($reserveID)){
      $this->getReserveByID($reserveID);
    }
  }

  /**
  * @return int reserveID
  * @desc create new reserve in database
  */
  function createNewReserve($courseInstanceID, $itemID)
  {
    global $g_dbConn;
    switch ($g_dbConn->phptype)
    {
      default: //'mysql'
        $sql = "INSERT INTO reserves (course_instance_id, item_id, date_created, last_modified) VALUES (!, !, ?, ?)";
        $sql2 = "SELECT LAST_INSERT_ID() FROM reserves";

        $d = date("Y-m-d"); //get current date
    }


    $rs = $g_dbConn->query($sql, array($courseInstanceID, $itemID, $d, $d));
    if (DB::isError($rs))
    {

      if ($rs->getMessage() == 'DB Error: already exists')
      {
        return false;
      }
      else
        trigger_error($rs->getMessage(), E_USER_ERROR);
    }

    $rs = $g_dbConn->query($sql2);
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

    $row = $rs->fetchRow();
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

    $this->reserveID = $row[0];
    $this->creationDate = $d;
    $this->lastModDate = $d;
    
    $ci = new courseInstance($courseInstanceID);
    
    $this->getReserveByID($this->reserveID);
    $this->getItem();
    
    if($this->item->copyrightReviewRequired())
    { 
      $ci->clearReviewed(); //adding reserves requires review
    }   
    return true;
  }

  /**
  * @return void
  * @param int $reserveID
  * @desc get reserve info from the database
  */
  function getReserveByID($reserveID)
  {
    global $g_dbConn, $g_notetype;

    switch ($g_dbConn->phptype)
    {
      default: //'mysql'
        $sql = "SELECT reserve_id, course_instance_id, item_id,
              activation_date, expiration, status,
              copyright_status, sort_order, date_created,
              last_modified, requested_loan_period, parent_id,
              copyright_status
            FROM reserves
            WHERE reserve_id = ! ";
    }

    $rs = $g_dbConn->getRow($sql, array($reserveID));
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
    
    //if no row, return
    if(count($rs) == 0) {
      return;
    }

    list($this->reserveID, $this->courseInstanceID, $this->itemID,
         $this->activationDate, $this->expirationDate, $this->status,
         $this->copyrightStatus, $this->sortOrder, $this->creationDate,
         $this->lastModDate, $this->requested_loan_period,
         $this->parentID, $this->copyrightStatus) = $rs;

    //get instructor notes
    $this->setupNotes('reserves', $this->reserveID, $g_notetype['instructor']);
    $this->fetchNotesByType();
    
    //get copyright notes
    $this->setupNotes('reserves', $this->reserveID, $g_notetype['copyright']);
    $this ->fetchNotesByType();   
  }

  /**
  * @return itemID on success or null on failure
  * @param int $course_instance_id, int item_id
  * @desc get reserve info from the database by ci and item
  */
  function getReserveByCI_Item($course_instance_id, $item_id) {
    global $g_dbConn;
    
    switch($g_dbConn->phptype) {
      default:  //mysql
        $sql = "SELECT reserve_id FROM reserves WHERE course_instance_id = ".$course_instance_id." AND item_id = ".$item_id;
    }
    
    $r_id = $g_dbConn->getOne($sql);
    if (DB::isError($r_id)) { trigger_error($r_id->getMessage(), E_USER_ERROR); }
    if(empty($r_id)) {    
      return false;
    }
    else {     
      return $this->getReserveByID($r_id);
    }
  }

  /**
  * @return void
  * @desc destroy the database entry
  */
  function destroy()
  {
    global $g_dbConn;

    switch ($g_dbConn->phptype)
    {
      default: //'mysql'
        $sql = "DELETE "
          .  "FROM reserves "
          .  "WHERE reserve_id = ! "
          .  "LIMIT 1"
          ;
    }

    if (!isset($this->item) || is_null($this->item))
      $this->getItem();
    
    if (!is_null($this->reserveID))
    {
      $rs = $g_dbConn->query($sql, $this->reserveID);
      if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
      
      //delete the notes too
      $this->destroyNotes();
    }
    
    $this->item->destroy(false);
  }

  
  /**
   * @return int new reserve ID
   * @desc Duplicates the item AND the reserve for the same CI and returns new reserveID
   */
  function duplicateReserve() {
    global $g_dbConn;
    
    //vars
    $new_item_id = null;
    $new_reserve_id = null;
    $new_pc_id = null;
    
    //SQL
    switch ($g_dbConn->phptype) {
      default:  //'mysql'
        //insert item data
        $sql_item = "INSERT INTO items (title, author, source, volume_title, volume_edition, pages_times, performer, local_control_key, creation_date, last_modified, url, mimetype, home_library, private_user_id, item_group, item_type, item_icon) VALUES (?, ?, ?, ?, ?, ?, ?, NULL, NOW(), NOW(), NULL, ?, ?, ?, ?, ?, ?)";
      
        //insert reserve data
        $sql_reserve = "INSERT INTO reserves (course_instance_id, item_id, activation_date, expiration, status, sort_order, date_created, last_modified, requested_loan_period, parent_id, copyright_status) VALUES (!, !, ?, ?, 'INACTIVE', ?, NOW(), NOW(), ?, ?, ?)";
        
        //insert physical copy data
        $sql_pc = "INSERT INTO physical_copies (reserve_id, item_id, status, call_number, barcode, owning_library, item_type, owner_user_id) VALUES (!, !, ?, ?, ?, ?, ?, ?)";
        
        //get the id of last insert       
        $sql_last_insert_id = "SELECT LAST_INSERT_ID() FROM reserves";    
    }
    
    
    //create new item
    
    $this->getItem(); //fetch data
    //build array of data to insert
    //do not copy URL and local_control_key as an attempt to keep down the number of duplicate items
    $data = array(
          $this->item->title.' (Duplicate)',
          $this->item->author,
          $this->item->source,
          $this->item->volumeTitle,
          $this->item->volumeEdition,
          $this->item->pagesTimes,
          $this->item->performer,
          $this->item->mimeTypeID,
          $this->item->homeLibraryID,
          $this->item->privateUserID,
          $this->item->itemGroup,
          $this->item->itemType,  
          $this->item->itemIcon
        );
      
    //query
    $rs = $g_dbConn->query($sql_item, $data);
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
    
    //get last insert id
    $rs = $g_dbConn->getOne($sql_last_insert_id);
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
    //assign it
    $new_item_id = $rs;
    
    //create new reserve
    
    //build array of data
    $data = array(
          $this->courseInstanceID,
          $new_item_id,
          $this->activationDate,
          $this->expirationDate,
          $this->sortOrder,
          $this->requested_loan_period,
          $this->parentID,
          $this->copyrightStatus          
        );
    
    //query
    $rs = $g_dbConn->query($sql_reserve, $data);
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
    
    //get last insert id
    $rs = $g_dbConn->getOne($sql_last_insert_id);
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
    //assign it
    $new_reserve_id = $rs;
    
    //create a new physical item (if one exists)
    
    if($this->item->getPhysicalCopy()) {  //if physical copy exists
      $data = array(
            $new_reserve_id,
            $new_item_id,
            $this->item->physicalCopy->status,
            $this->item->physicalCopy->callNumber,
            $this->item->physicalCopy->barcode,
            $this->item->physicalCopy->owningLibrary,
            $this->item->physicalCopy->itemType,
            $this->item->physicalCopy->ownerUserID
          );
      
      //query
      $rs = $g_dbConn->query($sql_pc, $data);
      if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
      
      //get last insert id
      $rs = $g_dbConn->getOne($sql_last_insert_id);
      if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
      //assign it
      $new_pc_id = $rs;           
    }
    
    //duplicate notes   
    $this->fetchNotesByType();  //re-fetch notes for the reserve
    $this->duplicateNotes($new_reserve_id); //duplicate notes for the reserve
    $this->item->fetchNotes();  //re-fetch notes for the item
    $this->item->duplicateNotes($this_item_id); //duplicate notes for the item
    
    //return the new reserve's ID
    return $new_reserve_id;
  }
  

  /**
   * @return boolean
   * @param int $parent_id New parent reserve's ID
   * @param boolean $autosort If true, will attempt to insert the reserve into proper sort order in new folder
   * @desc sets $parent_id as this reserve's parent; return true on success, false on failure
   */
  function setParent($parent_id, $autosort=false) {
    global $g_dbConn;
    
    //handle 'null' or 'root' parent
    //PEAR DB chokes on literal null values, so make them 'NULL'
    $parent_id = (empty($parent_id) || ($parent_id=='root')) ? 'NULL' : intval($parent_id);
    
    //setting parent_id to self breaks things
    if($parent_id == $this->reserveID) {
      return false;
    }
    
    //do nothing if trying to set the same parent
    $current_parent_id = !empty($this->parentID) ? $this->parentID : 'NULL';
    if($parent_id == $current_parent_id) {
      return true;
    }
    
    switch ($g_dbConn->phptype) {
      default:  //mysql
        $sql = "UPDATE reserves SET parent_id = !, last_modified = ? WHERE reserve_id = !";
        $d = date("Y-m-d"); //get current date
    }
        
    $rs = $g_dbConn->query($sql, array($parent_id, $d, $this->reserveID));
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

    $this->parentID = $parent_id;
    $this->lastModDate = $d;  
    
    //try to insert into sort order
    if($autosort) {
      $this->getItem();
      $this->insertIntoSortOrder($this->getCourseInstanceID(), $this->item->getTitle(), $this->item->getAuthor(), $parent_id);
    }
    
    return true;  
  }
  
  
  /**
  * @return void
  * @param date $activationDate
  * @desc set new activationDate in database
  */
  function setActivationDate($date)
  {
    global $g_dbConn;
    
    //attempt to parse input date that may be in non-standard formats
    $date = date('Y-m-d', strtotime($date));

    switch ($g_dbConn->phptype)
    {
      default: //'mysql'
        $sql = "UPDATE reserves SET activation_date = ?, last_modified = ? WHERE reserve_id = !";
        $d = date("Y-m-d"); //get current date
    }
    $rs = $g_dbConn->query($sql, array($date, $d, $this->reserveID));
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

    $this->activationDate = $date;
    $this->lastModDate = $d;
  }

  /**
  * @return void
  * @param date $expirationDate
  * @desc set new expirationDate in database
  */
  function setExpirationDate($date)
  {
    global $g_dbConn;
    
    //attempt to parse input date that may be in non-standard formats
    $date = date('Y-m-d', strtotime($date));

    switch ($g_dbConn->phptype)
    {
      default: //'mysql'
        $sql = "UPDATE reserves SET expiration = ?, last_modified = ? WHERE reserve_id = !";
        $d = date("Y-m-d"); //get current date
    }
    $rs = $g_dbConn->query($sql, array($date, $d, $this->reserveID));
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

    $this->expirationDate = $date;
    $this->lastModDate = $d;
  }

    /**
  * @return void
  * @param string $status
  * @desc set new status in database
  */
  function setStatus($status)
  {
    if (is_null($status))
    {
      return null;
    }
    //do NOT allow anyone to change status of a heading
    //do NOT allow anyone to change status of a physical item that is 'IN PROCESS'
    if (!isset($this->item) || is_null($this->item))
      $this->getItem();
      
    if(!$this->item->isHeading())  
    {
      global $g_dbConn;
  
      if (strtoupper($status) == 'DENIED_ALL')
      {
        $this->item->setStatus('DENIED');
        $status = "DENIED"; //denied all is not a valid reserve status and is used to indicate denial at the item level.  Change to denied to update reserve
      }
      
      switch ($g_dbConn->phptype)
      {
        default: //'mysql'
          $sql = "UPDATE reserves SET status = ?, last_modified = ? WHERE reserve_id = !";
          $d = date("Y-m-d"); //get current date
      }
      $rs = $g_dbConn->query($sql, array($status, $d, $this->reserveID));
      if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
  
      $this->status = $status;
      $this->lastModDate = $d;
    }
  }

  /**
   * @return void
   * @param string $copyrightStatus
   * @desc Updates the copyright status value
   */
  function setCopyrightStatus($copyrightStatus)
  {
    if (is_null($copyrightStatus) || $this->isHeading()) {
      // Headings have no copyright concerns.
      return null;
    } else {
      global $g_dbConn;

      $this->copyrightStatus = $copyrightStatus;
      switch ($g_dbConn->phptype)
      {
        default: //'mysql'
          $sql = "UPDATE reserves SET copyright_status = ? WHERE reserve_id = !";
      }
      $rs = $g_dbConn->query($sql, array($copyrightStatus, $this->reserveID));
      if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
    }
  } 
  

    /**
  * @return void
  * @param int $sortOrder
  * @desc set new sortOrder in database
  */
  function setSortOrder($sortOrder)
  {
    global $g_dbConn;

    switch ($g_dbConn->phptype)
    {
      default: //'mysql'
        $sql = "UPDATE reserves SET sort_order = !, last_modified = ? WHERE reserve_id = !";
        $d = date("Y-m-d"); //get current date
    }
    $rs = $g_dbConn->query($sql, array($sortOrder, $d, $this->reserveID));
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

    $this->sortOrder = $sortOrder;
    $this->lastModDate = $d;
  }
  
  
  /**
   * @return void
   * @param int $ci_id CourseInstance ID
   * @param string $r_title Title of this reserve item
   * @param string $r_author Author of this reserve item
   * @param int $folder_id Look at the sort order in this folder only
   * @desc Attempt to determine the sort order for the course instance and insert this object into the sequence
   */
  function insertIntoSortOrder($ci_id, $r_title, $r_author, $folder_id=null) {
    global $g_dbConn;
    //this reserve's ID
    $r_id = $this->getReserveID();
    
    switch($g_dbConn->phptype) {
      //this reserve has already been inserted into DB, but w/ a random sort order, so it must be ignored in all queries    
      default:  //mysql
        //format folder query substring
        $and_parent = empty($folder_id) ? " AND (parent_id IS NULL OR parent_id = '')" : " AND parent_id = ".intval($folder_id);
        
        //1. count the number or distinct course order values for all the reserves for this CI (if default/unsorted, count will = 1)
        $sql = "SELECT COUNT(DISTINCT sort_order)
            FROM reserves
            WHERE course_instance_id = ! AND reserve_id <> !".$and_parent;
        
        //2. get the list of reserve IDs and associated order #s, sorted in different ways
        $select_title_author = "SELECT i.title, i.author";
        $select_title = "SELECT i.title";
        $select_author = "SELECT i.author";
        $sql2 = " FROM items AS i
              JOIN reserves AS r ON r.item_id = i.item_id
              WHERE r.course_instance_id = ! AND r.reserve_id <> !".$and_parent;
        $order_current = " ORDER BY r.sort_order, i.title"; 
        $order_author = " ORDER BY i.author, i.title";
        $order_title = " ORDER BY i.title, i.author";
        
        //3. after this reserve position is set, shift everything following it down
        $sql_shift = "UPDATE reserves
                SET sort_order = (sort_order+1)
                WHERE course_instance_id = !
                AND sort_order >= !".$and_parent;
        
        //4. get the last sort order currently in the list
        $sql_max = "SELECT MAX(sort_order)
              FROM reserves
              WHERE course_instance_id = ! AND reserve_id <> !".$and_parent;
    }
    
    //is the list custom-sorted?    
    $rs = $g_dbConn->getOne($sql, array($ci_id, $r_id));
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
    
    if($rs > 1) { //using custom sort
      //atempt to figure out if items are sorted by title or author
      //basically, get list sorted by each and compare to list sorted by custom sort
      
      $current_order = array();
      $test_array = array();
      $new_reserve_position = 0;  //the position of this new reserve in the sorted order; default to top of the list
      
      //get the current (custom_sorted) array     
      $rs = $g_dbConn->query($select_title_author.$sql2.$order_current, array($ci_id, $r_id));
      if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
      //fetch data into an array
      unset($row);
      while($row = $rs->fetchRow()) {
        $current_order[] = array($row[0], $row[1]); //theoretically, this should be equivalent to Array[sort_order] = info
      }
      //get the size of the array
      $curr_size = count($current_order);
      
      //fetch title test array      
      $rs = $g_dbConn->query($select_title.$sql2.$order_title, array($ci_id, $r_id));
      if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
      //fetch data into array
      unset($row);
      while($row = $rs->fetchRow()) {
        $test_array[] = $row[0];
      }

      //now compare the test TITLE array against the current order
      //also keep track of where the new reserve would fit into this array
      $test_passed = true;    
      for($x=0; $x<$curr_size; $x++) {
        if(strcmp($current_order[$x][0], $test_array[$x]) != 0) { //current order is NOT by title
          $test_passed = false; //fail the test
          $new_reserve_position = 0;  //reset new reserve's position
          break;  //break from the loop 
        }
        else {  //title order still matches current order
          if(strnatcasecmp($r_title, $current_order[$x][0]) >= 0) { //if the new reserve title is >= than current entry, the new reserve should follow the current one in the list
            $new_reserve_position = $x+1;
          }
        }
      }
      
      if(!$test_passed) { //current order is NOT by title, try by author
        $test_passed = true;  //reset test var
        
        //fetch asuthor test array      
        $rs = $g_dbConn->query($select_author.$sql2.$order_author, array($ci_id, $r_id));
        if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
        //fetch data into array
        unset($row);
        $test_array = array();
        while($row = $rs->fetchRow()) {
          $test_array[] = $row[0];
        }

        //now compare the test AUTHOR array against the current order
        //also keep track of where the new reserve would fit into this array
        for($x=0; $x<$curr_size; $x++) {
          if(strcmp($current_order[$x][1], $test_array[$x]) != 0) { //current order is NOT by author
            $test_passed = false; //fail the test
            $new_reserve_position = 0;  //reset new reserve's position
            break;  //break from the loop 
          }
          else {  //author order still matches current order
            if(strnatcasecmp($r_author, $current_order[$x][1]) >= 0) {  //if the new reserve author is >= than current entry, the new reserve should follow the current one in the list
              $new_reserve_position = $x+1;
            }
          }
        }
      }
      
      //set the new orders
      
      if($test_passed) {  //if we passed the test, then new_res_pos is correct... use it    
        //in the DB order starts at 1, not 0, so add one to the position var
        $new_reserve_position++;
        //shift elements that fall behind the new reserve down to make room     
        $rs = $g_dbConn->query($sql_shift, array($ci_id, $new_reserve_position));
        if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
        //insert the new reserve at its proper position
        $this->setSortOrder($new_reserve_position);
      }
      else {  //failed test, add this reserve to the end of the list
        //get the max sort_order
        $rs = $g_dbConn->getOne($sql_max, array($ci_id, $r_id));
        if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
        //set this one to max+1
        $this->setSortOrder($rs+1);
      }       
    }
    else {  //no sort order set; add this reserve to the end of the list
      //get the max sort_order
      $rs = $g_dbConn->getOne($sql_max, array($ci_id, $r_id));
      if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
      //set this one to max+1
      $this->setSortOrder($rs+1);
    }
  } //insertIntoSortOrder()
  

  function setRequestedLoanPeriod($lp)
  {
    global $g_dbConn;

    switch ($g_dbConn->phptype)
    {
      default: //'mysql'
        $sql = "UPDATE reserves SET requested_loan_period = ? WHERE reserve_id = !";
    }

    $rs = $g_dbConn->query($sql, array($lp, $this->reserveID));
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
    
    $this->requested_loan_period = $lp;
  } 
  
  /**
  * @return void
  * @param int $userID
  * @desc log the users reserve view
  */
  function addUserView($userID)
  {
    global $g_dbConn;

    switch ($g_dbConn->phptype)
    {
      default: //'mysql'
        $sql = "INSERT INTO user_view_log (user_id, reserve_id, timestamp_viewed) VALUES (!, !, CURRENT_TIMESTAMP)";
    }
    $rs = $g_dbConn->query($sql, array($userID, $this->reserveID));
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

  }

  /**
  * @return void
  * @desc Retrieve the item object associated with this reserve
  */
  function getItem()
  {
    $this->item = new reserveItem($this->itemID);
  }
  
  /**
   * @return boolean true on access
   * @desc Retrieve item object associated with this reserve if user has access
   *
   * @param int $userID
   */
  function getItemForUser($user)
  {
    global $g_dbConn, $g_permission;

    if ($user->getRole() >= $g_permission['staff'])
    {
      $this->getItem();
      return true;
    } elseif ($this->getStatus() != 'DENIED' && $this->getStatus() != 'DENIED ALL') {   
      switch ($g_dbConn->phptype)
      {
        default: //'mysql'
          $sql = "SELECT DISTINCT a.permission_level, ci.activation_date, ci.expiration_date, ci.status, a.enrollment_status
              FROM reserves as r
                JOIN course_aliases as ca ON r.course_instance_id = ca.course_instance_id
                JOIN course_instances as ci ON r.course_instance_id = ci.course_instance_id
                JOIN access as a ON ca.course_alias_id = a.alias_id
              WHERE a.user_id = ".$user->getUserID()." 
                AND r.reserve_id = ".$this->reserveID;
        
          $d = date("Y-m-d"); //get current date
      }
      
      $rs = $g_dbConn->query($sql);
      if (DB::isError($rs)) { return false; }
  
      if($row = $rs->fetchRow(DB_FETCHMODE_ASSOC)) {
        if($row['permission_level'] < $g_permission['proxy']) { //if the user is below proxy (student, custodian)
          //add restrictions - the CI must be current and active; the student must be on the approved roll
          if(($row['activation_date'] <= $d) && ($row['expiration_date'] >= $d) && ($row['status'] == 'ACTIVE') && (($row['enrollment_status'] == 'AUTOFEED') || ($row['enrollment_status'] == 'APPROVED'))) {
            //fetch the reserveItem object
            $this->getItem();
            return true;
          }
          else {  //student did not pass restrictions
            return false;
          }               
        }
        else {  //if proxy or better, do not need restrictions
          $this->getItem();
          return true;
        }
      }
      else {  //no access
        return false;
      }
    } else {
      return false;
    }
  }

  /**
   * @return String reserve status
   * @desc Return reserve status unless item copyright has been denied for the item
   *     in which case item status will override  
   */
  function getStatus() { 
    if (! $this->item instanceof reserveItem )
      $this->getItem();
    
    if ($this->item->getStatus() == "ACTIVE")
    {
      return $this->status; 
    } else {
      return "DENIED ALL";
    }
  } 

  function getCopyrightStatus() { return $this->copyrightStatus; }
  function getReserveID(){ return $this->reserveID; }
  function getCourseInstanceID() { return $this->courseInstanceID; }
  function getItemID() { return $this->itemID; }
  function getActivationDate() { return $this->activationDate; }
  function getExpirationDate() { return $this->expirationDate; }  
  function getSortOrder() { return $this->sortOrder; }
  function getCreationDate() { return $this->creationDate; }
  function getModificationDate() { return $this->lastModDate; }
  function getParent() { return $this->parentID; }
  
  function getRequestedLoanPeriod() 
  {
    if (!is_null($this->requested_loan_period))
      return $this->requested_loan_period;
    else
      return "";
  }
  

  /**
  * @return boolean
  * @desc tests associated item if item is a heading returns true false otherwise
  */
  function isHeading()
  {
    /*
    if (is_a($this->item, "reserveItem")) return false;  //reserveItems are not headings
    else return true;
    */
    
    if (!is_a($this->item, "reserveItem"))
      $this->getItem();
    if ($this->item->itemType == 'HEADING')
      return true;
    else
      return false;
    
  }
  
  function hideReserve($userID)
  {
    global $g_dbConn;
    switch ($g_dbConn->phptype)
    {
      default: //'mysql'
        $sql = "INSERT INTO hidden_readings (user_id, reserve_id) VALUES (!, !)";
    }


    $rs = $g_dbConn->query($sql, array($userID, $this->reserveID));
    if (DB::isError($rs))
    {

      if ($rs->getMessage() == 'DB Error: already exists')
      {
        return false;
      }
      else
        trigger_error($rs->getMessage(), E_USER_ERROR);
    }

    $this->hidden=true;
    return true;
  }
  
  function unhideReserve($userID)
  {
    global $g_dbConn;
    switch ($g_dbConn->phptype)
    {
      default: //'mysql'
        $sql = "DELETE FROM hidden_readings WHERE user_id = ! AND reserve_id = !";
    }

    $rs = $g_dbConn->query($sql, array($userID, $this->reserveID));
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

  }
  
  
  /**
   * @return array of Note objects
   * @param boolean $include_item_notes (optional) If true, will also fetch notes for the reserveItem
   * @desc Overwrites the parent method to allow for inclusion of reserveItem notes. If param is true, will return merged array of reserve and item notes.
   */
  public function getNotes($include_item_notes=false) {
    $notes = parent::getNotes();
    
    if($include_item_notes) { //include reserveItem notes
      $this->getItem(); //fetch the reserveItem object
      $notes = array_merge($notes, $this->item->getNotes());  //merge reserve notes and item notes
    }
    
    return $notes;
  }

  /**
   * @return a list of reserves in the copyright queue.
   * @param $offset pagination offset
   * @param $rowsperpage pagination rows return to page.
   * @desc Get a list of reserves whose copyright limit exceeds the limit,
   * and has a copyright_status of either 'NEW' or 'PENDING'.
   */  
  static function getCopyrightReviewReserves($offset=null, $rowsperpage=null, $libraryID=1)
  {
    global $g_dbConn;
    global $g_copyrightLimit;

    switch ($g_dbConn->phptype)
    {
      default:
        /* Given each of the books used for a course, we want to know all
         * of the reserves for that book/course combination, but only if
         * the total page usage is greater than some configurable percent.
         * 
         *  * Join reserves, items, and course_instances to get,
         *    essentially, all of the items used for each ci.
         *  * Use an outer join to augment that with the aggregate percent
         *    used (from a view) wherever we were able to calculate it. This
         *    means that when we have ISBN and pages_times info in the main
         *    query, we should now also have aggregate percent used data
         *    from the subquery. Where ISBN or pages_times are missing or
         *    invalid, aggregate percent used data will be NULL.
         *  * Filter the results: We only care about BOOK_PORTION items
         *    and ACTIVE course instances.
         *  * Filter it further: We only care about items whose aggregate
         *    percent used is above the threshold or unavailable.
         *    Remember: The outer join means that the usage information is
         *    NULL unless we had enough information to calculate it in the
         *    subquery.
         *  * Yield the reserves that match our criteria, ordered by
         *    course instance and item id to ease human browsing.
         */

        $sql = "SELECT DISTINCT r.reserve_id
                FROM reserves r
                  JOIN items i ON r.item_id = i.item_id
                  JOIN course_instances ci ON r.course_instance_id = ci.course_instance_id
                  LEFT JOIN course_book_usage tot ON 
                      i.ISBN = tot.ISBN AND
                      ci.course_instance_id = tot.course_instance_id
                WHERE r.copyright_status NOT IN ('ACCEPTED', 'DENIED') AND
                      i.material_type = 'BOOK_PORTION' AND 
                      ci.status = 'ACTIVE' AND
                      (tot.percent_used IS NULL OR
                       tot.percent_used >= ?)
                ORDER BY ci.course_instance_id, i.item_id";
    }
    $params = array((int)$g_copyrightLimit);
    
    // include any pagination requests that limit the result set.
    if (isset($offset) && isset($rowsperpage)) {
      $sql .= " LIMIT ?, ?";
      $params = array((int)$g_copyrightLimit, $offset, $rowsperpage);
    }  
    
    $rs = $g_dbConn->query($sql, $params);
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

    $results = array();
    while ($row = $rs->fetchRow())
    {
      $results[] = new reserve($row[0]);
    }
    return $results;    
  }
    
  /**
   * @return a list of reserve ids for a given course instance.
   * @param $ci course instance
   * @desc Get a list of course for a course instance.
   */    
    static function getReservesForCourse($ci)
    {
      global $g_dbConn;

      switch ($g_dbConn->phptype)
      {
        default:
          $sql = "SELECT reserve_id
                  FROM reserves
                  WHERE course_instance_id = ?";
      }
      $rs = $g_dbConn->query($sql, array($ci));
      if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

      $results = array();
      while ($row = $rs->fetchRow())
      {
        $results[] = $row[0];
      }
      return $results;
    }
    
  /**
   * @return html code chunk for Copyright Status Display
   * @param $u user checked for permission to edit copyright display
   * @param $reserveLimit the percentage of book usage for this reserve.
   * @desc Get the html code to display the copyright status display if needed.
   */
  public function getCopyrightStatusDisplay($u, $reserveLimit=0) {
    global $g_copyrightLimit, $g_permission;
    
    $copyright_status_display = "";
    
    // These are the choices for copyright_status in reserves table.
    $copyrightStatusChoices = array('NEW', 'PENDING', 'ACCEPTED', 'DENIED');

    // Only display if the user role is greater than or equal to staff  
    if ($u->getRole() >= $g_permission['staff']) {
    
      // Only display if copyright percentage is over the copyright limit.
      if ($reserveLimit > $g_copyrightLimit) {
        // Set the table row and label for the Copyright Status.
        $copyright_status_display .= '<tr id="copyright_status"><th>Copyright Status:</th><td>';
        // Initial the dropdown menu code for the Copyright Status.
        $copyright_status_display .= '<select id="copyrightstatus" name="copyright_status" >';
        
        // Loop through all the copyright status values, loading them into dropdown.
        foreach($copyrightStatusChoices as $copyright_status) {
          // if this choice is the current value, then mark it as selected.
          $selected = ($this->getCopyrightStatus() == $copyright_status) ? ' selected="selected"' : '';       
          $copyright_status_display .= '<option value="' . $copyright_status. '"' . $selected . '>' . $copyright_status . '</option>'; 
        }
        // Close the dropdown menu code.
        $copyright_status_display .= '</select></td></tr>';
      }
    }

    return $copyright_status_display;
  }    
}
?>
