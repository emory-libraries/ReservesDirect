<?
/*******************************************************************************
RD_Ils_Euclid
Implementation of Emory University's Localized ils EUCLID

Created by Jason White (jbwhite@emory.edu)

This file is part of ReservesDirect

Copyright (c) 2004-2008 Emory University, Atlanta, Georgia.

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

*******************************************************************************
This class extends RD_Ils_Abstract

Implementing Emory University's localized ils
*******************************************************************************/
require_once("lib/RD/Ils/Abstract.php");
require_once("lib/RD/Ils/class_xml_check.php");
require_once("lib/RD/Ils/EuclidResult.php");

class RD_Ils_Euclid extends RD_Ils_Abstract
{
  
  protected function setILSName()
  {
    $this->_ilsName = 'EUCLID';
  }
  
  protected function setReservableFormats()
  {
    $this->_reservable_formats = array('MONOGRAPH', 'MULTIMEDIA', 'ELECTRONIC');
  }
  
  public function createReserve(Array $form_vars, Reserve $reserve)
  {
    global $g_reservesViewer;
        
    $barcode       = $form_vars['barcode'];
    $copy          = $form_vars['copy'];
    $libraryID     = $form_vars['libraryID'];
    $circ          = $form_vars['circ'];
    list($circRule,$altCirc) = split('\|', $circ);    
    $borrower_user_id    = $form_vars['borrower_user_id'];
    $term          = $form_vars['term'];  
    
    $expiration    = $reserve->expiration;
        
    $lo = new LibraryObject();
    $reservesDesk = $lo->find($libraryID);
    
    try {
      $io = new InstructorObject();
      $borrower = $io->find($borrower_user_id);
      
      $borrower->getInstructorAttributes();   
      $borrowerID = $borrower->instructor_attributes->ils_user_id;
    } catch (Exception $e) {
      return new RD_Euclid_Result(RD_Euclid_Result::FAILURE_IDENTITY_NOT_FOUND, "Borrower Not Found");
    }
      
    $desk = $reservesDesk->reserve_desk;
    $course = strtoupper($reservesDesk->ils_prefix . $term);

    list($Y,$M,$D) = split("-", $expiration);
    $eDate = "$M/$D/$Y";
    if (true) { echo "$g_reservesViewer?itemID=$barcode&borrowerID=$borrowerID&courseID=$course&reserve_desk=$desk&circ_rule=$circRule&alt_circ=$altCirc&expiration=$eDate&cpy=$copy<BR>"; }

    $fp = fopen("$g_reservesViewer?itemID=$barcode&borrowerID=$borrowerID&courseID=$course&reserve_desk=$desk&circ_rule=$circRule&alt_circ=$altCirc&expiration=$eDate&cpy=$copy", "r");

    $rs = array();
    while (!feof ($fp)) {
      array_push($rs, @fgets($fp, 1024));
    }
    $returnStatus = join($rs, "");

    $returnStatus = eregi_replace("<head>.*</head>", "", $returnStatus);
    $returnStatus = ereg_replace("<[A-z]*>", "", $returnStatus);
    $returnStatus = ereg_replace("</[A-z]*>", "", $returnStatus);

    $returnStatus = ereg_replace("<!.*\">", "", $returnStatus);
    $returnStatus = ereg_replace("\n", "", $returnStatus);

    if(!ereg("outcome=OK", $returnStatus))
    {
      return new RD_Euclid_Result(RD_Euclid_Result::FAILURE_UNCATEGORIZED, "There was a problem setting the location and circ-rule in $this->_ilsName. <BR>$this->_ilsName returned:  $returnStatus.");
    } else {
      return new RD_Euclid_Result(RD_Euclid_Result::SUCCESS, "Location and circ-rule have been successfully set in $this->_ilsName");
    }
  }
  
  public function displayReserveForm()
  {
    return $this->_view->render("_EUCLID_reserve_form.phtml");
  }
  
  public function getHoldings($key, $keyType = 'barcode')
  {
    
    if(empty($key) || empty($keyType)) {
      return array();
    }

    $rs = array();
    
    $key = ereg_replace('oc[mn]','o',$key);
    $key = ereg_replace('DOBI','o',$key);
            
    $fp = fopen("$g_holdingsScript?key=" . $key . "&key_type=$keyType", "rb");
    if(!$fp) {
      throw new RD_Ils_Exception("Could not get holdings");
    }
    while (!feof ($fp)) {
      array_push($rs, @fgets($fp, 1024));
    }
    $returnStatus = join($rs, "");

    if(ereg("Outcome=OK\n", $returnStatus))
    {
      list($devnull, $holdings) = split("Outcome=OK\n", $returnStatus);

      $thisCopies = split("\n", $holdings);

      $j = 0;
      for($i = 0; $i < (count($thisCopies) - 1); $i++)
      {
        if (strpos($thisCopies[$i], '|') !== false) {       
          list($devnull, $devnull, $copy, $callnum, $loc, $type, $bar, $library, $status, $reservesDesk) = split("\|", $thisCopies[$i]);
          if ($copy != "" && $callnum != "")
          {
            $tmpArray[$j]['copy']   = trim($copy);
            $tmpArray[$j]['callNum']  = trim($callnum);
            $tmpArray[$j]['loc']    = trim($loc);
            $tmpArray[$j]['type']   = trim($type);
            $tmpArray[$j]['bar']    = trim($bar);
            $tmpArray[$j]['library']  = trim($library);
            $j++;
          }
        }
      }

      return $tmpArray;
    } else return array();
  }
    
  public function search($search_field, $search_term)
  {
    global $g_getBibRecordScript;  

    setlocale(LANG, "en_US.UTF-8");
    setlocale(LC_COLLATE, "en_US.UTF-8");
    setlocale(LC_CTYPE, "en_US.UTF-8");
    setlocale(LC_MESSAGES, "en_US.UTF-8");
    setlocale(LC_MONETARY, "en_US.UTF-8");
    setlocale(LC_NUMERIC, "en_US.UTF-8");
    setlocale(LC_TIME, "en_US.UTF-8");

    try {      
      $this->xmlResults = "";
      $qry_url = "$g_getBibRecordScript?item_id=$search_term";    
      
      $fp = fopen($qry_url, "r");
            
      if(!$fp) {
        throw new RD_Ils_Exception("Could not open $qry_url");
      }
      while(!feof($fp)) {
        $this->xmlResults.= fread($fp,1024);
      }
      fclose($fp);

      return new RD_Euclid_Result(RD_Euclid_Result::SUCCESS, "", $this->xmlResults);
    } catch (Exception $e) {
      $this->xmlResults = null;
      return new RD_Euclid_Result(RD_Euclid_Result::FAILURE, $e->getMessage(), null);
    }
  }
}
?>
