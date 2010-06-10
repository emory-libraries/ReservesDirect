<?
/*******************************************************************************
copyrightManager.class.php


Created by Ben Ranker <branker@emory.edu>

This file is part of ReservesDirect

Copyright (c) 2004-2010 Emory University, Atlanta, Georgia.

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

Reserves Direct is located at:
http://www.reservesdirect.org/


*******************************************************************************/

require_once('secure/classes/reserves.class.php');
require_once('secure/displayers/copyrightDisplayer.class.php');
require_once('secure/managers/baseManager.class.php');

class copyrightManager extends baseManager {

  function copyrightManager($cmd, $user, $request)
  {
    global $loc;

    $this->displayClass = "copyrightDisplayer";
    $this->user = $user;

    switch ($cmd)
    {
      case 'copyrightTab':
        $loc = 'Items needing copyright review';
        $this->displayFunction = "displayCopyrightQueue";
        
        // Get the Library
        if (isset($_REQUEST['library'])) {
          $libraryID = $_REQUEST['library'];
          print "LIBRARY ID = [" . $libraryID . "]\n";
        } 
        else {
          $libraryID = 1; // default to Woodruff library
        }

        // Pagination calculations
        // total number of reserves needing review.
        $numcopyrightreserves = count(reserve::getCopyrightReviewReserves());  
        $rowsperpage = 10;  // number of rows to show per page
        $totalpages = ceil($numcopyrightreserves / $rowsperpage); // find out total pages

        // get the current page or set a default
        if (isset($_GET['currentpage']) && is_numeric($_GET['currentpage'])) {
           $currentpage = (int) $_GET['currentpage']; // cast var as int
        } else {
           $currentpage = 1;  // default page num
        } 
        
        if ($currentpage > $totalpages) { // if current page is greater than total pages...
           $currentpage = $totalpages;  // set current page to last page
        } 
        
        if ($currentpage < 1) { // if current page is less than first page...
           $currentpage = 1;  // set current page to first page
        } 

        // the offset of the list, based on current page 
        $offset = ($currentpage - 1) * $rowsperpage;
           
        // Retrieve the reserves in the copyright queue for this particular page.
        $reserves = reserve::getCopyrightReviewReserves($offset, $rowsperpage, $libraryID);
        
        // Pass these parameters to the view.
        $this->argList = array($reserves, $numcopyrightreserves, $currentpage, $totalpages, $libraryID);

        break;
    }
  }

}
