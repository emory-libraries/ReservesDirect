<?
/*******************************************************************************
reserveItem.class.php
ReserveItem Primitive Object

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
require_once("secure/classes/item.class.php");
require_once("secure/classes/physicalCopy.class.php");
require_once("secure/classes/rightsholder.class.php");
require_once("secure/classes/user.class.php");

class reserveItem extends item
{
  //Attributes
  public $author;
  public $source;
  public $volumeTitle;
  public $volumeEdition;
  public $pagesTimes;
  public $performer;
  public $localControlKey;
  public $URL;
  public $mimeTypeID;
  public $homeLibraryID;
  public $privateUserID;
  public $privateUser;
  public $physicalCopy;
  public $itemIcon;
  public $status;
  public $publisher;
  public $availability;
  
  public $usedPagesTimes;
  public $totalPagesTimes; 

  public $ISSN;
  public $ISBN;
  public $OCLC;
  
  private $material_type;
  
  function reserveItem($itemID=NULL)
  {
    if (!is_null($itemID)){
      $this->getItemByID($itemID);
    }
  }


  /**
  * @return void
  * @param int $itemID
  * @desc get item info from the database. This is meant to replace the parent method.
  */
  function getItemByID($itemID)
  {
    global $g_dbConn;
    
    if(empty($itemID)) {
      return false; //no ID 
    }

    switch ($g_dbConn->phptype)
    {
      default: //'mysql'
        $sql = "SELECT item_id, title, item_group, last_modified,
                creation_date, item_type, author, source, volume_edition,
                performer, local_control_key, url, mimeType, home_library,
                private_user_id, volume_title, item_icon, ISBN, ISSN, OCLC,
                status, material_type, publisher, availability,
                pages_times_range, pages_times_used, pages_times_total
            FROM items
            WHERE item_id = !";
    }
    
    $rs = $g_dbConn->getRow($sql, array($itemID));
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
    
    if(empty($rs)) {
      return false;
    }
    else {
      //pull the info
      list($this->itemID, $this->title, $this->itemGroup,
           $this->lastModDate, $this->creationDate, $this->itemType,
           $this->author, $this->source, $this->volumeEdition,
           $this->performer, $this->localControlKey, $this->URL,
           $this->mimeTypeID, $this->homeLibraryID, $this->privateUserID,
           $this->volumeTitle, $this->itemIcon, $this->ISBN, $this->ISSN,
           $this->OCLC, $this->status, $this->material_type,
           $this->publisher, $this->availability, $this->pagesTimes,
           $this->usedPagesTimes, $this->totalPagesTimes) 
        = $rs;
        
      //get the notes
      $this->setupNotes('items', $this->itemID);
      $this->fetchNotes();
      
      return true;
    }
  }
  
  /**
  * @return void
  * @desc destroy the database entry and file if one exists
  * @param boolean if true will destroy even if attached to a class
  */
  function destroy($override = false)
  {
    global $g_dbConn;

    switch ($g_dbConn->phptype)
    {
      default: //'mysql'
        $sql = "SELECT count(*) FROM reserves WHERE item_id = !";
    }   
    
    if(!empty($this->itemID)) {     
      //attempt to use transactions
      if($g_dbConn->provides('transactions')) {
        $g_dbConn->autoCommit(false);
      }
      
      try {           
        $reserveCnt = $g_dbConn->getOne($sql, array($this->itemID));
        
        if ($reserveCnt == 0 || $override)
        {
          if ($this->isLocalFile())
          {
            unlink($this->URL);
          }
          
          parent::destroy();
        }
      } catch (Exception $e) {
        if($g_dbConn->provides('transactions')) { 
          $g_dbConn->rollback();
        }
        trigger_error($reserveCnt->getMessage(), E_USER_ERROR);
      }
      
      //commit this set
      if($g_dbConn->provides('transactions')) { 
        $g_dbConn->commit();
      }   
    }
  }

  /**
  * @return boolean
  * @param string localControl
  * @desc get item info from the database by localcontrolkey; return TRUE on success, FALSE otherwise
  */
  function getItemByLocalControl($local_control_key)
  {
    global $g_dbConn;
    
    if(empty($local_control_key))
      return false; //no key
    
    switch($g_dbConn->phptype) {
      default:  //mysql
        $sql = "SELECT item_id FROM items WHERE local_control_key = ?";
    }
    
    //query to get item ID
    $rs = $g_dbConn->getOne($sql, $local_control_key);
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
        
    //get item by ID
    return $this->getItemByID($rs);
  }
    

  /**
  * @return void
  * @param string $author
  * @desc set new author in database
  */
  function setAuthor($author)
  {
    global $g_dbConn;

    switch ($g_dbConn->phptype)
    {
      default: //'mysql'
        $sql = "UPDATE items SET author = ?, last_modified = ? WHERE item_id = !";
        $d = date("Y-m-d"); //get current date
    }
    $rs = $g_dbConn->query($sql, array(stripslashes($author), $d, $this->itemID));
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

    $this->author = $author;
    $this->lastModDate = $d;
  }

  /**
  * @return void
  * @param string $source
  * @desc set new source in database
  */
  function setSource($source)
  {
    global $g_dbConn;

    switch ($g_dbConn->phptype)
    {
      default: //'mysql'
        $sql = "UPDATE items SET source = ?, last_modified = ? WHERE item_id = !";
        $d = date("Y-m-d"); //get current date
    }
    $rs = $g_dbConn->query($sql, array(stripslashes($source), $d, $this->itemID));
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

    $this->source = $source;
    $this->lastModDate = $d;
  }

  /**
  * @return void
  * @param string $volumeTitle
  * @desc set new volumeTitle in database
  */
  function setVolumeTitle($volumeTitle)
  {
    global $g_dbConn;

    switch ($g_dbConn->phptype)
    {
      default: //'mysql'
        $sql = "UPDATE items SET volume_title = ?, last_modified = ? WHERE item_id = !";
        $d = date("Y-m-d"); //get current date
    }
    $rs = $g_dbConn->query($sql, array(stripslashes($volumeTitle), $d, $this->itemID));
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

    $this->volumeTitle = $volumeTitle;
    $this->lastModDate = $d;
  }


  /**
  * @return void
  * @param string $volumeEdition
  * @desc set new volumeEdition in database
  */
  function setVolumeEdition($volumeEdition)
  {
    global $g_dbConn;

    switch ($g_dbConn->phptype)
    {
      default: //'mysql'
        $sql = "UPDATE items SET volume_edition = ?, last_modified = ? WHERE item_id = !";
        $d = date("Y-m-d"); //get current date
    }
    $rs = $g_dbConn->query($sql, array(stripslashes($volumeEdition), $d, $this->itemID));
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

    $this->volumeEdition = $volumeEdition;
    $this->lastModDate = $d;
  }

  /**
  * @return void
  * @param string $pagesTimes
  * @desc set new pages_times_range in database
  */
  function setPagesTimes($pagesTimes)
  {
    global $g_dbConn;

    switch ($g_dbConn->phptype)
    {
      default: //'mysql'
        $sql = "UPDATE items SET pages_times_range = ?, last_modified = ? WHERE item_id = !";
        $d = date("Y-m-d"); //get current date
    }
    $rs = $g_dbConn->query($sql, array(stripslashes($pagesTimes), $d, $this->itemID));
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

    $this->pagesTimes = $pagesTimes;
    $this->lastModDate = $d;
  }
  /**
   * set used # of pages or time/duration for the work/volume
   * @param string $pagesTimesUsed
   */

  function setUsedPagesTimes($pagesTimesUsed) {
    global $g_dbConn;
    $this->usedPagesTimes = $pagesTimesUsed;
    switch ($g_dbConn->phptype) {
    default: //'mysql'
      $sql = "UPDATE items SET pages_times_used = ? WHERE item_id = !";
    }
    $rs = $g_dbConn->query($sql, array($this->usedPagesTimes, $this->itemID));
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
  }
  
  /**
   * set total # of pages or time/duration for the work/volume
   * @param string $pagesTimesTotal
   */

  function setTotalPagesTimes($pagesTimesTotal) {
    global $g_dbConn;
    $this->totalPagesTimes = $pagesTimesTotal;
    switch ($g_dbConn->phptype) {
    default: //'mysql'
      $sql = "UPDATE items SET pages_times_total = ? WHERE item_id = !";
    }
    $rs = $g_dbConn->query($sql, array($this->totalPagesTimes, $this->itemID));
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
  }  

  /**
  * @return void
  * @param string $performer
  * @desc set new performer in database
  */
  function setPerformer($performer)
  {
    global $g_dbConn;

    switch ($g_dbConn->phptype)
    {
      default: //'mysql'
        $sql = "UPDATE items SET performer = ?, last_modified = ? WHERE item_id = !";
        $d = date("Y-m-d"); //get current date
    }
    $rs = $g_dbConn->query($sql, array(stripslashes($performer), $d, $this->itemID));
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

    $this->performer = $performer;
    $this->lastModDate = $d;
  }

  /**
  * @return void
  * @param string $localControlKey
  * @desc set new localControlKey in database
  */
  function setLocalControlKey($localControlKey)
  {
    global $g_dbConn;

    switch ($g_dbConn->phptype)
    {
      default: //'mysql'
        $sql = "UPDATE items SET local_control_key = ?, last_modified = ? WHERE item_id = !";
        $d = date("Y-m-d"); //get current date
    }
    $rs = $g_dbConn->query($sql, array($localControlKey, $d, $this->itemID));
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

    $this->localControlKey = $localControlKey;
    $this->lastModDate = $d;
  }

  /**
  * @return void
  * @param string $URL
  * @desc set new URL in database
  */
  function setURL($URL)
  {
    global $g_dbConn;

    switch ($g_dbConn->phptype)
    {
      default: //'mysql'
        $sql = "UPDATE items SET url = ?, last_modified = ? WHERE item_id = !";
        $d = date("Y-m-d"); //get current date
    }
    $rs = $g_dbConn->query($sql, array($URL, $d, $this->itemID));
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

    $this->URL = $URL;
    $this->lastModDate = $d;
  }

  /**
  * @return void
  * @param string $mimeType
  * @desc set new mimeType in database
  */
  function setMimeType($mimeType)
  {
    global $g_dbConn;

    switch ($g_dbConn->phptype)
    {
      default: //'mysql'
        $sql1 = "SELECT mimetype_id FROM mimetypes WHERE mimetype = ?";

        $sql = "UPDATE items SET mimetype = ?, last_modified = ? WHERE item_id = !";
        $d = date("Y-m-d"); //get current date
    }
    $mimeType = (!is_null($mimeType)) ? $mimeType : "text/html";
    
    $mimeTypeID = $g_dbConn->getOne($sql1, array($mimeType));
    if (DB::isError($mimeTypeID)) { trigger_error($mimeTypeID->getMessage(), E_USER_ERROR); }

    $this->mimeTypeID = $mimeTypeID;

    $rs = $g_dbConn->query($sql, array($this->mimeTypeID, $d, $this->itemID));
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

    $this->lastModDate = $d;
  }

  function setMimeTypeByFileExt($ext)
  {
    global $g_dbConn;

        $ext = str_replace(".", "", $ext);

    switch ($g_dbConn->phptype)
    {
      default: //'mysql'
        $sql1 = "SELECT m.mimetype FROM mimetypes AS m JOIN mimetype_extensions AS me ON m.mimetype_id = me.mimetype_id WHERE file_extension = ?";
    }

    $mimetype = $g_dbConn->getOne($sql1, array($ext));
    if (DB::isError($mimetype)) { trigger_error($mimetype->getMessage(), E_USER_ERROR); }

    $this->setMimeType($mimetype);
  }
  
  function setDocTypeIcon($docTypeIcon)
  {
    global $g_dbConn;

    switch ($g_dbConn->phptype)
    {
      default: //'mysql'
        $sql = "UPDATE items SET item_icon = ?, last_modified = ? WHERE item_id = !";
        $d = date("Y-m-d"); //get current date
    }
    
    $rs = $g_dbConn->query($sql, array($docTypeIcon, $d, $this->itemID));
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

    $this->itemIcon = $docTypeIcon;
    $this->lastModDate = $d;
  }

  function getItemIcon()
  {
    global $g_dbConn;
      
    if (!isset($this))
      return 'images/doc_type_icons/doctype-clear.gif';
      
    if (is_null($this->itemIcon) || $this->itemIcon == "")  
    { 
      switch ($this->mimeTypeID)
      {
        case '7':
        case 'text/html':
        case null:
          switch ($this->itemGroup)
          {
            case 'MONOGRAPH':
              return 'images/doc_type_icons/doctype-book.gif';
            break;
            case 'MULTIMEDIA':
              return 'images/doc_type_icons/doctype-disc2.gif';
            break;
            case 'ELECTRONIC':
              return 'images/doc_type_icons/doctype-pdf.gif';
            break;
            default:
              return 'images/doc_type_icons/doctype-clear.gif';
          }
        break;
  
        case '1': // PDF
          return 'images/doc_type_icons/doctype-pdf.gif';
        break;
  
        default:
          switch ($g_dbConn->phptype)
          {
            default: //'mysql'
              $sql = "SELECT helper_app_icon FROM mimetypes WHERE mimetype_id = !";
          }
          $rs = $g_dbConn->query($sql, array($this->mimeTypeID));
          if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
  
          if ($rs->numRows() < 1)
            return 'images/doc_type_icons/doctype-clear.gif';
          else {
            $row = $rs->fetchRow();
            return $row[0];
          }
      }
    } else 
      return $this->itemIcon;
  }

  

  /**
  * @return void
  * @param string $homeLibraryID
  * @desc set new homeLibraryID in database
  */
  function setHomeLibraryID($homeLibraryID)
  {
    global $g_dbConn;

    $this->homeLibraryID = $homeLibraryID;
    switch ($g_dbConn->phptype)
    {
      default: //'mysql'
        $sql = "UPDATE items SET home_library = !, last_modified = ? WHERE item_id = !";
        $d = date("Y-m-d"); //get current date
    }
    $rs = $g_dbConn->query($sql, array($homeLibraryID, $d, $this->itemID));
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

    $this->homeLibraryID = $homeLibraryID;
    $this->lastModDate = $d;
  }

  /**
  * @return void
  * @param int $privateUserID
  * @desc set new privateUserID in database
  */
  function setPrivateUserID($privateUserID)
  {
    global $g_dbConn;

    $this->privateUserID = $privateUserID;
    switch ($g_dbConn->phptype)
    {
      default: //'mysql'
        $sql = "UPDATE items SET private_user_id = !, last_modified = ? WHERE item_id = !";
        $d = date("Y-m-d"); //get current date
    }
    $rs = $g_dbConn->query($sql, array($privateUserID, $d, $this->itemID));
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

    $this->privateUserID = $privateUserID;
    $this->lastModDate = $d;
  }
  
  /**
  * @return void
  * @param string $ISBN
  * @desc Updates the ISBN value, associated w/the item, in the DB
  */
  function setISBN($ISBN)
  {
    global $g_dbConn;

    $this->ISBN = substr(preg_replace('/[^0-9]/i', '', $ISBN), 0, 13);
    switch ($g_dbConn->phptype)
    {
      default: //'mysql'
        $sql = "UPDATE items SET ISBN = ? WHERE item_id = !";
    }
    $rs = $g_dbConn->query($sql, array($this->ISBN, $this->itemID));
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
  } 
  
  /**
  * @return void
  * @param string $ISSN
  * @desc Updates the ISSN value, associated w/the item, in the DB
  */
  function setISSN($ISSN)
  {
    global $g_dbConn;

    //$this->ISSN = substr(preg_replace('/[^0-9xX]/i', '', $ISSN), 0, 9);
    $this->ISSN = $ISSN;
    switch ($g_dbConn->phptype)
    {
      default: //'mysql'
        $sql = "UPDATE items SET ISSN = ? WHERE item_id = !";
    }
    $rs = $g_dbConn->query($sql, array($this->ISSN, $this->itemID));
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
  }   

  /**
  * @return void
  * @param string $status
  * @desc Updates the status value
  */
  function setStatus($status)
  {
    if (is_null($status) || $this->isHeading())
    {
      //do not update the status of headings
      return null;
    } else {
      global $g_dbConn;
  
      $this->status = $status;
      switch ($g_dbConn->phptype)
      {
        default: //'mysql'
          $sql = "UPDATE items SET status = ? WHERE item_id = !";
      }
      $rs = $g_dbConn->query($sql, array($status, $this->itemID));
      if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
    }
  } 

  
  /**
  * @return void
  * @param string $OCLC
  * @desc Updates the OCLC value, associated w/the item, in the DB
  */
  function setOCLC($OCLC)
  {
    global $g_dbConn;

    $this->OCLC = $OCLC;
    switch ($g_dbConn->phptype)
    {
      default: //'mysql'
        $sql = "UPDATE items SET OCLC = ? WHERE item_id = !";
    }
    $rs = $g_dbConn->query($sql, array($this->OCLC, $this->itemID));
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
  }   

  /**
   * @param string $type material type
   * @param string $other_detail material type detail, if type is 'other' (optional)
   * @desc Updates the item's material type in the DB
   */
  function setMaterialType($type, $other_detail = "") {
    global $g_dbConn;

    $this->material_type = $type;
    if ($type == "OTHER" && $other_detail != "")
      $this->material_type .= ":$other_detail";
    switch ($g_dbConn->phptype) {
      default: //'mysql'
        $sql = "UPDATE items SET material_type = ? WHERE item_id = !";
    }
    $rs = $g_dbConn->query($sql, array($this->material_type, $this->itemID));
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
  }

  
  /**
   * set the item publisher in the db
   * @param string $pub publisher
   */
  function setPublisher($pub) {
    global $g_dbConn;
    $this->publisher = $pub;
    switch ($g_dbConn->phptype) {
      default: //'mysql'
        $sql = "UPDATE items SET publisher = ? WHERE item_id = !";
    }
    $rs = $g_dbConn->query($sql, array($this->publisher, $this->itemID));
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
  }   
  
  /**
   * set availability
   * @param int $availability
   */
  function setAvailability($avail) {
    global $g_dbConn;
    $this->availability = $avail;
    switch ($g_dbConn->phptype) {
    default: //'mysql'
      $sql = "UPDATE items SET availability = ? WHERE item_id = !";
    }
    $rs = $g_dbConn->query($sql, array($this->availability, $this->itemID));
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
  }


  function getAuthor() { return htmlentities(stripslashes($this->author)); }
  function getSource() { return htmlentities(stripslashes($this->source)); }
  function getVolumeTitle() { return htmlentities(stripslashes($this->volumeTitle)); }
  function getVolumeEdition() { return htmlentities(stripslashes($this->volumeEdition)); }
  function getPagesTimes() { return htmlentities(stripslashes($this->pagesTimes)); }
  function getPerformer() { return htmlentities(stripslashes($this->performer)); }
  function getLocalControlKey() { return stripslashes($this->localControlKey); }
  function getURL() { return ($this->URL != '' && !is_null($this->URL)) ? stripslashes($this->URL) : false; }
  
  function getISBN() { return $this->ISBN; }
  function getISSN() { return $this->ISSN; }
  function getOCLC() { return $this->OCLC; }  
  
  function getStatus() { return $this->status; }

  function getPublisher() { return $this->publisher; }
  function getAvailability() { return $this->availability; }
  
  function getUsedPagesTimes() { return htmlentities(stripslashes($this->usedPagesTimes)); }
  function getTotalPagesTimes() { return htmlentities(stripslashes($this->totalPagesTimes)); }    
  
  // NOTE: Comment out for the Type of Material release
  // function getTotalPagesTimes() { return $this->totalPagesTimes; }  
  
  /**
   * return material type
   * @param string $mode optional, one of base, detail, or full (default is base); only differs for OTHER
   * @return string
   */
  function getMaterialType($mode = 'base') {
    switch ($mode) {
    case 'detail':
      if ($pos = strpos($this->material_type, ':'))
        return substr($this->material_type, $pos+1, strlen
          ($this->material_type));
      else return "";
    case 'base':
      // if material type contains :, return string before that; otherwise, fall through
      if ($pos = strpos($this->material_type, ':')) return substr($this->material_type, 0, $pos);
    case 'full':
      return $this->material_type;
    }
  }
  
  
  function getMimeType()
  {
    global $g_dbConn;

    $mimetype = "x-application";
    if (!is_null($this->mimeTypeID) && is_numeric($this->mimeTypeID)){
      switch ($g_dbConn->phptype)
      {
        default: //'mysql'
          $sql = "SELECT mimetype FROM mimetypes WHERE mimetype_id = ! LIMIT 1";
      }
      $rs = $g_dbConn->query($sql, array($this->mimeTypeID));
      if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }


      while($row = $rs->fetchRow()) { $mimetype = $row[0]; }
    }

    return $mimetype;
  }

  function getHomeLibraryID() { return $this->homeLibraryID; }

  function getPrivateUser()
  {
    $this->privateUser = new user($this->privateUserID);
  }

  function getPrivateUserID() { return (!is_null($this->privateUserID) && $this->privateUserID != "") ? $this->privateUserID : null; }

  function getPhysicalCopy()  {
    if (! isset($this->physicalCopy)) {
      $this->physicalCopy = new physicalCopy();
      // only search for physical copy of item id is set...
      if ($this->getItemID()) $this->physicalCopy->getByItemID($this->getItemID());
    }
    return $this->physicalCopy;
  }

  function getRightsholder() {
    $isbn = $this->getISBN();
    if (is_null($isbn)) {
      return null;
    }
    return new rightsholder($isbn);
  }

  function isPhysicalItem()
  {
    if ($this->itemGroup == 'MULTIMEDIA' || $this->itemGroup == 'MONOGRAPH') {
      return true;
    } else {
      return false;
    }
  }

  function isPersonalCopy()
  {
    if ($this->privateUserID != null)
      return true;
    else
      return false;
  }
  
  /**
   * @return boolean
   * @desc Attempts to guess if the file is stored locally or remotely. Returns true if local file, false otherwise
   */
  function isLocalFile() {
    if(empty($this->URL)) { //blank URLs are not local
      return false;
    }
    
    //parse the url into its component parts
    $parsed_url = parse_url($this->URL);
    
    //if the url does not contain a scheme (http, ftp, etc), assume it's local    
    return empty($parsed_url['scheme']) ? true : false;
  }
  
  
  /**
   * @return array of courseInstance Objects
   * @desc Returns array of courseInstace objects that have used this item
   */
  function getAllCourseInstances() {
    global $g_dbConn;
    
    switch ($g_dbConn->phptype) {
      default:  //mysql
        $sql = "SELECT DISTINCT r.course_instance_id 
            FROM reserves AS r
              JOIN course_instances AS ci ON ci.course_instance_id = r.course_instance_id
              JOIN course_aliases AS ca on ca.course_alias_id = ci.primary_course_alias_id
              JOIN courses AS c ON c.course_id = ca.course_id
              JOIN departments AS d ON d.department_id = c.department_id
            WHERE r.item_id = ".$this->itemID."
            ORDER BY ci.activation_date DESC, d.abbreviation ASC, c.course_number ASC, ca.section ASC";         
    }
    $rs = $g_dbConn->query($sql);
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
    
    $classes = array();
    while($row = $rs->fetchRow()) {
      $classes[] = new courseInstance($row[0]);
    }
    
    return $classes;
  }
  
  /**
  * @return boolean
  * @desc determine if adding this item to a reserves list will require staff review
  */  
  function copyrightReviewRequired()
  {
    //review is required for all documents except physical items and external links
    
    $not_heading  = !$this->isHeading();
    $local      = $this->isLocalFile();
    $not_manuscript = !($this->getItemGroup() == 'MANUSCRIPT');
    
    return ($not_heading && $local && $not_manuscript);   
  } 
  
  /**
  * @return the percentage used pages per item/isbn for course
  * @param string $ci course instance ID
  * @desc Calculcate the percentage of used pages per item/isbn for this course.
  */   
  function getOverallBookUsage($ci=null, $add=null) {
    $query = 'SELECT i.item_id, i.pages_times_used, i.pages_times_total FROM items as i
      LEFT JOIN reserves as r on r.item_id = i.item_id
      WHERE r.course_instance_id = ? and i.ISBN = ?';  
    $params = array($ci, $this->getISBN());  
    $overallBookUsage = $this->selectOverallBookUsage($query, $params, null);
    
    // If adding an item to a book, then pull add the new item here.
    if (isset($add) && $add && $this->getTotalPagesTimes() > 0) {
      $currentBookUsage = 0;      
      $currentBookUsage = intval($this->getUsedPagesTimes()/$this->getTotalPagesTimes()*100);
      $overallBookUsage += $currentBookUsage; 
    }
    return intval($overallBookUsage); 
  }
  
  /**
  * @return the total number of used pages per item/isbn for course
  * @param string $ci course instance ID
  * @desc Calculcate the total number of used pages per item/isbn for this course.
  */  
  function getOverallUsedPages($ci=null) {
    $query = 'SELECT i.item_id, i.pages_times_used, i.pages_times_total FROM items as i
      LEFT JOIN reserves as r on r.item_id = i.item_id
      WHERE r.course_instance_id = ? and i.ISBN = ?'; 
    $params = array($ci, $this->getISBN());  
    $overallUsedPages = $this->selectOverallUsedPages($query, $params, null);
    return intval($overallUsedPages); 
  }  
  
  /**
  * @return the total number of reserve item that reference this ISBN
  * @desc Calculcate the total number reserve items that reference this ISBN.
  */  
  function countISBNUsage() {
    global $g_dbConn;
    
    $query = 'select COUNT(*) from items as i, reserves as r where i.item_id = r.item_id and i.ISBN =  ?'; 
    $params = array($this->getISBN());  
    $rs = $g_dbConn->getOne($query, $params);
    if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
    return intval($rs); 
  }  
       
}
