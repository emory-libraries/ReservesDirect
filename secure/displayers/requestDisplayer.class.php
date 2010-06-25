<?
/*******************************************************************************
requestDisplayer.class.php


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
require_once("secure/classes/terms.class.php");
require_once("secure/classes/circRules.class.php");
require_once('secure/displayers/noteDisplayer.class.php');
require_once('secure/managers/ajaxManager.class.php');

class requestDisplayer extends noteDisplayer {
  
  function displayAllRequest($requestList, $libList, $request, $user, $msg="")
  {
    echo "<script language='JavaScript1.2'>
          var jsFunctions = new basicAJAX();
          function setRequestStatus(select, request_id, notice) 
            {       
            var status = select.options[select.selectedIndex].value;
            var u   = 'AJAX_functions.php?f=updateRequestStatus';
            var qs  = 'request_id=' + request_id + '&status=' + status;
            
            var url = u + '&rf=' + jsFunctions.base64_encode(qs);
            
            ajax_transport(url, notice);
          }
       </script>
    \n";
    
    
    echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
    //echo "  <tr><td width=\"140%\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";
    if (!is_null($msg) && $msg != "")
      echo "  <tr><td width=\"100%\" class=\"failedText\" align=\"center\">$msg<br></td></tr>\n";

    echo "  <form action=\"index.php?cmd=displayRequest\" method=\"POST\">\n";
    echo "  <tr><td valign=\"top\">\n";
    echo "    <font color=\"#666666\"><strong>Filter Unprocessed Requests: </strong></font>\n";
    echo "      <br/>\n";
    echo "      <div style='margin-left: 10px; margin-top: 5px;'>\n";
    echo "      <select name=\"unit\">\n";
    echo "        <option value=\"all\">All Libraries</option>\n";

    $currentUnit = isset($request['unit']) ? $request['unit'] : $user->getStaffLibrary();
    foreach ($libList as $lib)
    {
      $lib_select = ($currentUnit == $lib->getLibraryID()) ? " selected " : "";
      echo "        <option $lib_select value=\"" . $lib->getLibraryID() . "\">" . strtoupper($lib->getLibraryNickname()) . "</option>\n";
    }
    echo "      </select>\n";
    echo "      </div>\n";

    echo "      <div style='margin-left: 10px;'>\n";  

    $filter = (!isset($request['filter_status'])) ? "INPROCESS" : str_replace(' ', '', strtoupper($request['filter_status']));
    $$filter = ' SELECTED ';  
    echo "      <select name='filter_status'>\n";
    echo "        <option {$INPROCESS} value='IN PROCESS'>IN PROCESS</option>\n";
    echo "        <option {$COPYRIGHTREVIEW} value='COPYRIGHT REVIEW'>COPYRIGHT REVIEW</option>\n";
    echo "        <option {$PURCHASING} value='PURCHASING'>PURCHASING</option>\n";
    echo "        <option {$RECALLED} value='RECALLED'>RECALLED</option>\n";
    echo "        <option {$RESPONSENEEDED} value='RESPONSE NEEDED'>RESPONSE NEEDED</option>\n";
    echo "        <option {$SCANNING} value='SCANNING'>SCANNING</option>\n";    
    echo "        <option {$SEARCHINGSTACKS} value='SEARCHING STACKS'>SEARCHING STACKS</option>\n";
    echo "        <option {$UNAVAILABLE} value='UNAVAILABLE'>UNAVAILABLE</option>\n";
    echo "        <option {$ALL} value=\"all\">All Unprocessed Requests</option>\n";    
    echo "      </select>\n";     
            
    echo "      <input type=\"submit\" value=\"Go\">\n";
    echo "      </div>\n";
    echo "    </td>\n";
    echo "    <td bgcolor=\"#CCCCCC\" class=\"borders\"><span class=\"strong\" style='margin-left: 5px;'>";
    echo "      Sort by:</span> ";
    echo "        [ <a href=\"index.php?cmd=". $request['cmd'] . "&unit=". $request['unit'] ."&sort=date\" class=\"editlinks\">Date/ID# </a>] ";
    echo "        [ <a href=\"index.php?cmd=". $request['cmd'] . "&unit=". $request['unit'] ."&sort=class\" class=\"editlinks\">Class</a> ] ";
    echo "        [ <a href=\"index.php?cmd=". $request['cmd'] . "&unit=". $request['unit'] ."&sort=instructor\" class=\"editlinks\">Instructor</a> ] ";
    echo "    </td>\n";
        echo "  </tr>\n";
        echo "  </form>\n";
        
        echo "  <form action=\"index.php?sort=\"" . $request['sort'] . "\" method=\"POST\">\n";
        echo "  <input type=\"hidden\" name=\"cmd\" value=\"printRequest\">\n";
        echo "  <input type=\"hidden\" name=\"sort\" value=\"".$request['sort']."\">\n";
        echo "  <input type=\"hidden\" name=\"no_table\">\n";
        echo "  <input type=\"hidden\" name=\"request_id\">\n";
        echo "  <tr>\n";
        echo "    <td><font color=\"#666666\">&nbsp;</font></td>";
        echo "    <td bgcolor=\"#FFFFFF\" align=\"right\"><input type=\"button\" value=\"Print Selected Request\" onClick=\"this.form.cmd.value='printRequest'; this.form.target='printPage'; this.form.submit(); checkAll(this.form, false);\">";
    echo "  </td>\n";
    echo "</tr>\n";   

    if (count($requestList) > 0)
    {
      requestDisplayer::displayRequestList($requestList);
    } else {
      echo "<tr><td>No " . $request['filter_status'] . " Request to process for this unit.</td></tr>";
    }


    echo "      </table>\n";
    echo "    </td>\n";
    echo "  </tr>\n";
    echo "  <tr><td>&nbsp;</td></tr>\n";
    echo "  <tr>\n";
    echo "    <td align=\"right\">\n";
    echo "      <img src=\images/spacer.gif\" width=\"1\" height=\"15\">[ <a href=\"index.php\">EXIT &quot;PROCESS REQUESTS&quot;</a> ]</td>\n";
    echo "  </tr>\n";
    echo "</table>\n";    
  }

  function printSelectedRequest($requestList, $libList, $request, $user, $msg="")
  {   
    echo "<script language='JavaScript1.2'>
          var jsFunctions = new basicAJAX();          
          function markAsPulled(request_ids, notice)
          {
          var status = 'SEARCHING STACKS';
          var u   = 'AJAX_functions.php?f=updateRequestStatus';
          var qs;
          var url;
                    
            for(var i=0;i<request_ids.length;i++)             
            {               
              qs  = 'request_id=' + request_ids[i] + '&status=' + status;
              url = u + '&rf=' + jsFunctions.base64_encode(qs);
            
            ajax_transport(url, notice);                
            }
          }
        </script>
    \n";
    
    
    
    echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
    

    echo "  <tr>\n";
    echo "    <td align=\"left\">Request List</td>\n";
    echo "  </tr>\n";   
    
    echo "  <tr>\n";
    echo "    <td align=\"left\">". date('g:i A D m-d-Y') ."</td>\n";
    echo "  </tr>\n";
    
    echo "  <tr>\n";
    echo "    <td align=\"right\"><img src=\images/spacer.gif\" width=\"1\" height=\"15\">[ <a href=\"javascript:window.close();\">Close Window</a> ]</td>\n";
    echo "  </tr>\n";   
    
    echo "  <tr>\n";
    echo "    <td align=\"right\"><input type=\"button\" value=\"Print\" onClick=\"window.print();\"></td>\n";
    echo "  </tr>\n";

    echo "  <tr>\n";
    echo "    <td align=\"right\"><div id=\"marked_indicator\" style='display: inline;'><img width='16px' height='16px' src='images/spacer.gif' /></div><input type=\"button\" value=\"Mark All As PULLED\" onClick=\"markAsPulled([". $requestList->id_list() ."], 'marked_indicator');\"></td>\n";
    echo "  </tr>\n";   
    
    
    if (!is_null($msg) && $msg != "")
      echo "  <tr><td width=\"100%\" class=\"failedText\" align=\"center\">$msg<br></td></tr>\n";

    echo "      </table>\n";
    echo "    </td>\n";
    echo "  </tr>\n";

    echo "</table>\n";    
    if (count($requestList) > 0)
      requestDisplayer::displayRequestList($requestList, "true");
    else 
      echo "<p style=\"text-align: center\">No Request selected for printing.</p>";


  } 
  
  function displayRequestList($requestList, $printView=null)
  { 
    global $g_catalogName;
    
    echo "  <tr><td colspan=\"2\">&nbsp;</td></tr>\n";

    echo "  <tr>\n";
    echo "    <td colspan=\"2\">\n";
    echo "      <table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
    echo "        <tr align=\"left\" valign=\"top\"><td class=\"headingCell1\">";
?>  
    <div style="float:left;">
      <input type="checkbox" onchange="javascript: checkAll(this.form, this.checked);" />
    </div>
<?php 
    echo "REQUESTS</td><td width=\"75%\">&nbsp;</td></tr>\n";
    echo "      </table>\n";
    echo "    </td>\n";
    echo "  </tr>\n";


    $cnt = 0;
    foreach ($requestList as $r)
    {
      $item = $r->requestedItem;
      $ci = $r->courseInstance;

      $pCopy = $item->physicalCopy;

      $cnt++;

      $rowClass = ($cnt % 2) ? "evenRow" : "oddRow";

      echo "  <tr>\n";
      echo "    <td align=\"left\" valign=\"top\" class=\"borders\"  colspan=\"2\">\n";
      //echo "      <table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" class=\"displayList\">\n";
      echo "      <table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" id=\"printRequest\">\n";
      echo "          <tr align=\"left\" valign=\"middle\" class=\"$rowClass\">\n";
      echo "            <td width=\"85%\" valign=\"top\">\n";
      echo "              <table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\">\n";
      echo "              <tr>";
      echo "              <td valign=\"top\" width=\"15%\">";
      if (is_null($printView) || $printView == "false") {
        echo "                <input type=\"checkbox\" name=\"selectedRequest[]\" value=\"" . $r->requestID . "\">&nbsp;&nbsp;<br>\n";
      }
      echo "                <span class=\"strong\">Request ID: </span>".sprintf("%06s",$r->requestID)."<br/>\n";    
      echo "                {$r->getType()} Request\n";
      echo              "</td>";
      echo "              <td>";
      echo "                <table>";
      
      echo "                <tr>\n";
      echo "                  <td valign=\"top\" colspan=\"2\" class=\"strong\"><a href=\"index.php?cmd=editClass&amp;ci=".$ci->getCourseInstanceID()."\">". $ci->course->displayCourseNo() ." - ". $ci->course->getName() ."</a></td>\n";
      echo "                </tr>\n";     

      echo "                <tr>\n";
      echo "                  <td valign=\"top\" colspan=\"2\">". $ci->displayTerm() ."</td>\n";
      echo "                </tr>\n";

      if ($item->isPhysicalItem() && $item->getLocalControlKey() == '')
      {
        echo "                <tr>\n";
        echo "                  <td align=\"right\" valign=\"top\" class=\"strong\"></td>\n";
        echo "                  <td align=\"left\" valign=\"top\"><font color='red'>This item is not properly linked to $g_catalogName.  Please edit this item and update the barcode.</font></td>\n";
        echo "                </tr>\n";
      }
        
      echo "                <tr>\n";
      echo "                  <td align=\"right\" valign=\"top\" class=\"strong\">Instructors:</td>\n";
      echo "                  <td align=\"left\" valign=\"top\">". $ci->displayInstructors(true) ."</td>\n";
      echo "                </tr>\n";

      echo "                <tr>\n";
      echo "                  <td align=\"right\" valign=\"top\" class=\"strong\">Author:</td>\n";
      echo "                  <td align=\"left\" valign=\"top\">". $item->getAuthor() ."</td>\n";
      echo "                </tr>\n";     

      echo "                <tr>\n";
      echo "                  <td align=\"right\" valign=\"top\" class=\"strong\">Title:</td>\n";
      echo "                  <td align=\"left\" valign=\"top\"><a href=\"index.php?cmd=editItem&reserveID={$r->reserveID}\">". $item->getTitle() ."</a></td>\n";
      echo "                </tr>\n";
      
      echo "                <tr>\n";
      echo "                  <td align=\"right\" valign=\"top\" class=\"strong\">Volume Title:</td>\n";
      echo "                  <td align=\"left\" valign=\"top\">". $item->getVolumeTitle() ."</td>\n";
      echo "                </tr>\n";     
      
      if ($r->isScanRequest())
      {       
        echo "            <tr>\n";
        echo "              <td align=\"right\" valign=\"top\" class=\"strong\">Pages/Times:</td>\n";
        echo "              <td align=\"left\" valign=\"top\">{$item->getPagesTimes()}</td>\n";
        echo "            </tr>\n";                 
      }     
      

      echo "                <tr>\n";
      echo "                  <td align=\"right\" valign=\"top\" class=\"strong\">ISSN:</td>\n";
      echo "                  <td align=\"left\" valign=\"top\">". $item->getISSN() ."</td>\n";
      echo "                </tr>\n";

      echo "                <tr>\n";
      echo "                  <td align=\"right\" valign=\"top\" class=\"strong\">ISBN:</td>\n";
      echo "                  <td align=\"left\" valign=\"top\">". $item->getISBN() ."</td>\n";
      echo "                </tr>\n";

      echo "                <tr>\n";
      echo "                  <td align=\"right\" valign=\"top\" class=\"strong\">OCLC:</td>\n";
      echo "                  <td align=\"left\" valign=\"top\">". $item->getOCLC() ."</td>\n";
      echo "                </tr>\n";

      if(count($r->holdings) > 0)
      {     
        echo "                <tr>\n";
        echo "                  <td align=\"right\" valign=\"top\" class=\"strong\">Location:</td>\n";
        //should be able to select no ILS and then display commented code
//        echo "                  <td align=\"left\" valign=\"top\">". $pCopy->getOwningLibrary() . " " . $pCopy->getStatus() ." ". $pCopy->getCallNumber() ."</td>\n";
        echo "                  <td align=\"left\" valign=\"top\">\n";

        foreach ($r->holdings as $h)
        {
          echo $h['library'] . " " . $h['callNum'] . " " . $h['loc'] . " " . $h['type'] . "<br>";         
        }
        if(count($r->holdings) > 0 && (is_null($printView) || $printView == "false")) //on printview show all 
          echo "Additional copies are available. View details for all holdings";
      
        echo "              </td>\n";
        echo "                </tr>\n";
      }

      echo "                <tr>\n";
      echo "                  <td align=\"right\" valign=\"top\" class=\"strong\">Cross Listings:</td>\n";
      echo "                  <td align=\"left\" valign=\"top\">" . $ci->displayCrossListings() . "</td>\n";
      echo "                </tr>\n";

      echo "                <tr>\n";
      echo "                  <td align=\"right\" valign=\"top\" class=\"strong\">Activate By:</td>\n";
      echo "                  <td align=\"left\" valign=\"top\">". common_formatdate($r->getDesiredDate(), "MM-DD-YYYY") ."</td>\n";
      echo "                </tr>\n";

      echo "                <tr>\n";
      echo "                  <td align=\"right\" valign=\"top\" class=\"strong\">Date Requested:</td>\n";
      echo "                  <td align=\"left\" valign=\"top\">". common_formatdate($r->getDateRequested(), "MM-DD-YYYY") ."</td>\n";
      echo "                </tr>\n";

        echo "                <tr>\n";
      echo "                  <td align=\"right\" valign=\"top\" class=\"strong\">Date Needed:</td>\n";
      echo "                  <td align=\"left\" valign=\"top\">". common_formatdate($r->getDesiredDate(), "MM-DD-YYYY") ."</td>\n";
      echo "                </tr>\n";

      echo "                <tr>\n";
      echo "                  <td align=\"right\" valign=\"top\" class=\"strong\">Requested Loan Period:</td>\n";
      echo "                  <td align=\"left\" valign=\"top\">". $r->reserve->getRequestedLoanPeriod() ."</td>\n";
      echo "                </tr>\n";             

      $notes = $r->getNotes();
      if (!empty($notes)) {
        foreach ($notes as $note) {
          echo "                <tr>\n";
          echo "                  <td align=\"right\" valign=\"top\" class=\"strong\">{$note->getType()} Note:</td>\n";
          echo "                  <td align=\"left\" valign=\"top\">{$note->getText()}</td>\n";
          echo "                </tr>\n";       
        }
      }
      echo "              </table>\n";
      echo "            </td>\n";
      echo "            <td align=\"right\" valign=\"top\" width=\"25%\">\n";
      
      if (is_null($printView) || $printView == "false")
      {
        $selected = str_replace(' ', '', $r->getStatus());
        $$selected = ' SELECTED="true" ';
        
        $processCmd = ($r->isScanRequest()) ? "addDigitalItem" : "addPhysicalItem";
        
        echo "              <a class='requestButton' href=\"index.php?cmd={$processCmd}&request_id={$r->requestID}\">Process Request</a>&nbsp;\n";  
        echo "              <br/>\n";     
        echo "              <a class='requestButton' href=\"index.php?cmd=deleteRequest&request_id=".$r->requestID."\">Delete Request</a>&nbsp;\n"; 
        echo "              <br/>\n";             
        //echo "              <p>\n";
        echo "                <div id='notice_{$r->requestID}' style='display: inline;'><img width='16px' height='16px' src='images/spacer.gif' /></div>\n";
        echo "                <select name='{$r->requestID}_status' onChange='setRequestStatus(this, {$r->requestID}, \"notice_{$r->requestID}\");'>\n";        
        echo "                  <option {$INPROCESS} value='IN PROCESS'>IN PROCESS</option>\n";
        echo "                  <option {$COPYRIGHTREVIEW} value='COPYRIGHT REVIEW'>COPYRIGHT REVIEW</option>\n";
        echo "                  <option {$PURCHASING} value='PURCHASING'>PURCHASING</option>\n";
        echo "                  <option {$RECALLED} value='RECALLED'>RECALLED</option>\n";
        echo "                  <option {$RESPONSENEEDED} value='RESPONSE NEEDED'>RESPONSE NEEDED</option>\n";
        echo "                  <option {$SCANNING} value='SCANNING'>SCANNING</option>\n";    
        echo "                  <option {$SEARCHINGSTACKS} value='SEARCHING STACKS'>SEARCHING STACKS</option>\n";
        echo "                  <option {$UNAVAILABLE} value='UNAVAILABLE'>UNAVAILABLE</option>\n";       
        echo "                  <option {$DENIED} value='DENIED' style=\"color: rgb(255, 0, 0);\">DENY COPYRIGHT</option>\n";
        echo "                  <option {$DENIEDALL} value='DENIED_ALL' style=\"color: rgb(255, 0, 0);\">DENY COPYRIGHT FOR ALL</option>\n";
        echo "                </select>\n";       
        //echo "              </p>\n";                      
        
        $$selected = "";
        
      } 
      echo "          &nbsp;</td>\n";
      echo "        </tr>\n";

      echo " </table></td></tr>";       
      echo "      </table>\n";
//      echo "<div style=\"page-break-after: always;\"></div>\n";
// we don't need this anymore, page-break-before:always is set in the stylesheet

    }
    echo "</form>\n";   
  }
  
  function addSuccessful($ci, $item_id, $reserve_id, $duplicate_link=false, $ils_results='') {
    $ci->getCourseForUser();
?>
    <div class="borders" style="padding:15px; width:50%; margin:auto;">
      <strong>Item was successfully added to </strong><span class="successText"><?=$ci->course->displayCourseNo()?> <?=$ci->course->getName()?></span>    
<?php if(!empty($ils_results)): //show ILS record creation results ?>
        <br />
        <br />
        <div style="margin-left:20px;">
          <strong>ILS query results:</strong>
          <div style="margin-left:20px;">
            <?=$ils_results?>
          </div>
        </div>
<?php endif; ?>
      <br />
      <ul>
        <li><a href="index.php?cmd=storeRequest&amp;item_id=<?=$item_id?>">Add this item to another class</a></li>        
<?php if($duplicate_link): ?>
        <li><a href="index.php?cmd=duplicateReserve&amp;reserveID=<?=$reserve_id?>">Duplicate this item and add copy to the same class</a></li>
<?php endif; ?>       
        <li><a href="index.php?cmd=editClass&ci=<?=$ci->getCourseInstanceID()?>"> Go to class</a></li>
        <li><a href="index.php?cmd=addPhysicalItem">Add another physical item</a></li>
        <li><a href="index.php?cmd=addDigitalItem">Add another electronic item</a></li>
        <li><a href="index.php?cmd=displayRequest">Return to the Requests Queue</a></li>
      </ul> 
    </div>
<?php
  }
  
  
  /**
   * Displays list of possible CIs for the item
   *
   * @param array $all_possible_CIs = array(
             *  'rd_requests' => array(ci1-id, ci2-id, ...),
             *  'ils_requests => array(
             *    user-id1 = array(
             *      'requests' => array(ils-request-id1, ils-request-id2, ...),
             *      'ci_list' => array(ci1-id, ci2-id, ...)
             *    ),
             *    user-id2 = ...
             *  )
             * )
   * @param array $selected_CIs = array(ci1_id, ci2_id, ...)
   * @param array $CI_request_matches = array(
             *  ci1-id => array(
             *    'rd_request' => rd-req-id,
             *    'ils_requests' => array(
             *      ils-req1-id => ils-req1-period,
             *      ils-req2-id...
             *    )
             *  ),
             *  ci2-id = ...
             * )
   * @param string $requested_barcode (optional) If searched for physical item, this is the barcode matching the exact copy searched
   */
  function displaySelectCIForItem($item_id, $all_possible_CIs, $selected_CIs, $CI_request_matches, $requested_barcode=null) {
    //get holding info for physical items
    $item = new reserveItem($item_id);
    if($item->isPhysicalItem()) {
      $zQry = RD_Ils::initILS();
      $holdingInfo = $zQry->getHoldings($item->getLocalControlKey(), 'control');
      $selected_barcode = $requested_barcode;
    }
    else {
      $holdingInfo = null;
      $selected_barcode = null;
    }
        
    //circ rules
    $circRules = new circRules();
?>
    <script type="text/javascript" language="JavaScript1.2" src="secure/javascript/basicAJAX.js"></script>
    <script type="text/javascript" language="JavaScript1.2" src="secure/javascript/request_ajax.js"></script>
        
    <script type="text/javascript">
      var current_form_block_id;
      
      function toggle_request_form(block_id) {
        //hide old selection
        if(document.getElementById(current_form_block_id)) {
          document.getElementById(current_form_block_id).style.display = 'none';
        }
        //show new selection
        if(document.getElementById(block_id)) {
          document.getElementById(block_id).style.display = '';
          //save new selection
          current_form_block_id = block_id;
        }       
      }
    </script>

<?php 
    //the way possible destination courses are displayed depends on request type    
    if(!empty($all_possible_CIs)):
      foreach($all_possible_CIs as $request_type=>$ci_data):
        //for RD requests, just show a simple header
        if($request_type == 'rd_requests'):
?>
    <br />
    <div class="headingCell1" style="width:30%">ReservesDirect courses requesting this item:</div>
    
<?php     elseif($request_type == 'ils_requests'): //for ILS requests, show a different header ?>

    <br />
    <div class="headingCell1" style="width:30%">ILS requests:</div>
    
<?php     endif; ?>

      <div class="headingCell1">
        <div style="width:60px; text-align:left; float:left;">&nbsp;</div>
        <div style="width:15%; text-align:left; float:left;">Course Number</div>
        <div style="width:30%; text-align:left; float:left;">Course Name</div>
        <div style="width:25%; text-align:left; float:left;">Instructor(s)</div>
        <div style="width:14%; text-align:left; float:left;">Term</div>
        <div style="width:55px; text-align:left; float:right; padding-right:5px;">Preview</div>
        <div style="clear:both;"></div>
      </div>
  
<?php
        if($request_type == 'rd_requests') {
          //the ci-data is the array of CIs
          //show those
          $selected_CIs = array($_REQUEST['ci']);
          self::displayCoursesForRequest($item_id, $ci_data, $selected_CIs, $CI_request_matches, $circRules, $holdingInfo, $selected_barcode);
        }
        elseif($request_type == 'ils_requests') {
          foreach($ci_data as $user_id=>$request_data) {
            //get instructor's name
            $instructor = new user($user_id);
            $instructor_name = $instructor->getName(false);
            
            //get a list of ILS courses requesting this item
            $ils_courses_string = '';
            foreach($request_data['requests'] as $ils_request_id) {
              //init ils request object
              $ils_request = new ILS_Request($ils_request_id);
              
              //add name to string
              $ils_courses_string .= '"<em>'.$ils_request->getCourseName().'</em>", ';
            }
            $ils_courses_string = rtrim($ils_courses_string, ', '); //trim off the last comma
            
            //display header
?>
      <div style="padding:5px; border:1px solid black; background-color:#DFD8C6;">Item requested by <em><?=$instructor_name?></em> for <em><?=$ils_courses_string?></em></div>
<?php
            //display course list
            $selected_CIs = array($_REQUEST['ci']);
            self::displayCoursesForRequest($item_id, $request_data['ci_list'], $selected_CIs, $CI_request_matches, $circRules, $holdingInfo, $selected_barcode);
          }
        }
        
      endforeach;
?>      
    <p>
      <img src="images/astx-green.gif" alt="selected" width="15" height="15"> <span style="font-size:small;">= course requested this item</span> &nbsp;
      <img src="images/pencil.gif" width="24" height="20" /> <span style="font-size:small;">= active courses</span> &nbsp;
      <img src="images/activate.gif" width="24" height="20" /> <span style="font-size:small;">= new courses not yet in use</span> &nbsp;
      <img src="images/cancel.gif" width="24" height="20" /> <span style="font-size:small;">= courses canceled by the registrar</span> &nbsp;
    </p>
    <br />
    <br />

    <script type="text/javascript">
      request_ajaxify_forms();
    </script>
    
<?php 
    endif;
    
    //display ajax selectClass
    $mgr = new ajaxManager('lookupClass', 'storeRequest', 'addReserve', 'Continue', array('item_id'=>$item_id));
    $mgr->display();
  }
  
  
  /**
   * Displays a list of CIs, along with special forms to submit ci-item combo for request
   *
   * @param unknown_type $course_instance_ids
   * @param unknown_type $selected_CIs
   * @param unknown_type $ci_request_matches
   * @param unknown_type $propagated_data
   * @param unknown_type $circRules
   * @param unknown_type $holdingInfo
   * @param unknown_type $selected_barcode
   */
  function displayCoursesForRequest($item_id, $course_instance_ids, $selected_CIs, $ci_request_matches, $circRules, $holdingInfo=null, $selected_barcode) {
?>
    <div style="border-bottom:1px solid #666666;">    
<?php
    foreach($course_instance_ids as $ci_id):
      $ci = new courseInstance($ci_id);
      $ci->getCourseForUser();  //fetch the course object
      $ci->getInstructors();  //get a list of instructors
      
      //get crosslistings
      $crosslistings = $ci->getCrossListings();
      $crosslistings_string = '';
      foreach($crosslistings as $crosslisting) {
        $crosslistings_string .= ', '.$crosslisting->displayCourseNo().' '.$crosslisting->getName();
      }
      $crosslistings_string = ltrim($crosslistings_string, ', '); //trim off the first comma
      
      //see if there are request matches
      $requests = !empty($ci_request_matches[$ci->getCourseInstanceID()]) ? $ci_request_matches[$ci->getCourseInstanceID()] : null;
      
      //show status icon
      switch($ci->getStatus()) {
        case 'AUTOFEED':
          $edit_icon = '<img src="images/activate.gif" width="24" height="20" />';  //show the 'activate-me' icon
        break;
        case 'CANCELED':
          $edit_icon = '<img src="images/cancel.gif" alt="edit" width="24" height="20">'; //show the 'canceled' icon
        break;
        default:
          $edit_icon = '<img src="images/pencil.gif" alt="edit" width="24" height="20">'; //show the edit icon
        break;            
      }     
            
      $pre_select_ci_radio = '';
      //mark pre-selected courses
      if(in_array($ci->getCourseInstanceID(), $selected_CIs)) {
        $selected_img = '<img src="images/astx-green.gif" alt="selected" width="15" height="15">&nbsp;';
        if (sizeof($selected_CIs) == 1) 
        {
          //only one CI selected go ahead and select the radio button
          $pre_select_ci_radio = ' checked="CHECKED" ';
          $force_toggle = "<script language='JavaScript'>toggle_request_form('add_".$ci->getCourseInstanceID()."');</script>";
        }
      }
      else {
        $selected_img = '';
      }
            
      //display row
      $rowStyle = (empty($rowStyle) || ($rowStyle=='evenRow')) ? 'oddRow' : 'evenRow';  //set the style
      $rowStyle2 = (empty($rowStyle2) || ($rowStyle2=='oddRow')) ? 'evenRow' : 'oddRow';  //set the style
?>                  
      <div class="<?=$rowStyle?>" style="padding:5px;">         
        <div style="width: 30px; float:left; text-align:left;"><input id="select_ci_<?=$ci->getCourseInstanceID()?>" name="ci" type="radio" value="<?=$ci->getCourseInstanceID()?>" onclick="javascript: toggle_request_form('add_<?=$ci->getCourseInstanceID()?>');" <?= $pre_select_ci_radio ?>/></div>
        <div style="width: 50px; float:left; text-align:left"><?=$selected_img.$edit_icon?></div>
        <div style="width:15%; float:left;"><?=$ci->course->displayCourseNo()?>&nbsp;</div>
        <div style="width:30%; float:left;"><?=$ci->course->getName()?>&nbsp;</div>
        <div style="width:25%; float:left;"><?=$ci->displayInstructors()?>&nbsp;</div>
        <div style="width:14%; float:left;"><?=$ci->displayTerm()?>&nbsp;</div>
        <div style="width:55px; float:right;"><a href="javascript:openWindow('no_control=1&cmd=previewReservesList&ci=<?=$ci->getCourseInstanceID()?>','width=800,height=600');">preview</a></div>
        <div style="clear:both;"></div>
<?php   if(!empty($crosslistings_string)): ?>
        <div style=" margin-left:30px; padding-top:5px;"><em>Crosslisted As:</em> <small><?=$crosslistings_string?></small></div>
<?php   endif; ?>

        <div id="add_<?=$ci->getCourseInstanceID()?>" style="display:none;">
          <?php self::displayCreateReserveForm($ci, $item_id, $circRules, $holdingInfo, $requests, $selected_barcode, $rowStyle2) ?>
        </div>
      </div>
    
<?php endforeach; ?>
<?= $force_toggle ?>    
    </div>

<?php
  }
  
  
  /**
   * Displays create-reserve/process-request form for the given ci and item
   *
   * @param unknown_type $ci
   * @param unknown_type $item_id
   * @param unknown_type $circRules
   * @param unknown_type $holdingInfo
   * @param unknown_type $requests
     * $requests = array(
     *  ci1-id => array(
     *    'rd_request' => rd-req-id,
     *    'ils_requests' => array(
     *      ils-req1-id => ils-req1-period,
     *      ils-req2-id...
     *    )
     *  ),
     *  ci2-id = ...
     * )
   * @param unknown_type $selected_barcode
   * @param unknown_type $rowStyle
   */
  function displayCreateReserveForm($ci, $item_id, $circRules, $holdingInfo=null, $requests=null, $selected_barcode=null, $rowStyle='') {
    global $calendar;
    
    $item = new reserveItem($item_id);
?>
    <form name="create_reserve_form" method="post" action="index.php">
          <input type="hidden" name="cmd" value="storeRequest" />
          <input type="hidden" name="ci" value="<?=$ci->getCourseInstanceID()?>" />
          <input type="hidden" name="item_id" value="<?=$item_id?>" />
<?php
      //need to pass on request info (which requests are fullfilled by this item-ci combo)
      if(!empty($requests)) {
        //pass on RD request ID
        if(!empty($requests['rd_request'])) {
?>
          <input type="hidden" name="rd_request" value="<?=$requests['rd_request']?>" />
<?php
        }
        if(!empty($requests['ils_requests'])) {
          foreach($requests['ils_requests'] as $ils_request_id=>$ils_requested_loan_period) {
?>
          <input type="hidden" name="ils_requests[]" value="<?=$ils_request_id?>" />
<?php
          }
        }
      }
      if(!empty($ci_request_matches)) {
        if(!empty($ci_request_matches['rd_requests'])) {
          self::displayHiddenFields($ci_request_matches['rd_requests']);
        }
        foreach($ci_request_matches as $ci_request_match) {
          self::displayHiddenFields($ci_request_match);
        }
      }
      self::displayHiddenFields($propagated_data);
?>
          <br />
          <table width="90%" border="0" cellpadding="3" cellspacing="0" class="borders <?=$rowStyle?>" align="center">
            <tr>
              <td width="15%">&nbsp;</td>
              <td><br /><strong>Please enter reserve information for this course:</strong><br />&nbsp;</td>
            </tr>
            <tr>
              <td align="right"><strong>Set Status:</strong></td>
              <td>
                <input type="radio" name="reserve_status" id="reserve_status_active_<?=$ci->getCourseInstanceID()?>" value="ACTIVE" checked="true" />&nbsp;<span class="active">ACTIVE</span>
                <input type="radio" name="reserve_status" id="reserve_status_inactive_<?=$ci->getCourseInstanceID()?>" value="INACTIVE" />&nbsp;<span class="inactive">INACTIVE</span>
              </td>
            </tr>
            <tr>
              <td align="right"><strong>Active Dates:</strong></td>
              <td>
                <input type="text" id="reserve_activation_date_<?=$ci->getCourseInstanceID()?>" name="reserve_activation_date" size="10" maxlength="10" value="<?=$ci->getActivationDate()?>" style="margin-top:5px;" /> <?=$calendar->getWidgetAndTrigger('reserve_activation_date_'.$ci->getCourseInstanceID(), $ci->getActivationDate())?> to <input type="text" id="reserve_expiration_date_<?=$ci->getCourseInstanceID()?>" name="reserve_expiration_date" size="10" maxlength="10" value="<?=$ci->getExpirationDate()?>" />  <?=$calendar->getWidgetAndTrigger('reserve_expiration_date_'.$ci->getCourseInstanceID(), $ci->getExpirationDate())?>(YYYY-MM-DD)
              </td>
            </tr>
<?php   if($item->isPhysicalItem()): //the rest is only needed for physical items ?>            
<?php     if(!empty($holdingInfo)): //have holding info, show physical copies ?>
            <tr>
              <td>&nbsp;</td>
              <td>
                <br />
                <span class="helperText">Below is a list of copies available through EUCLID.  <u>Select copies for which you wish to create a EUCLID 'on-reserve' record.</u>  Your selection(s) will have no impact on the ReservesDirect reserves list.</span>
              </td>
            </tr>
            <tr>
              <td align="right"><strong>ILS Record:</strong></td>
              <td>
                <input type="checkbox" name="create_ils_record" value="yes" CHECKED />
                Create EUCLID Reserve Record
              </td>
            </tr>
            <tr>
              <td align="right"><strong>Loan Period:</strong></td>
              <td>
                <select id="circRule_<?=$ci->getCourseInstanceID()?>" name="circRule">
<?php   
        foreach($circRules->getCircRules() as $circRule):
          $rule = base64_encode(serialize($circRule));
          $display_rule = $circRule['circRule']." -- " . $circRule['alt_circRule'];
          $selected = $circRule['default'];
?>
                  <option value="<?=$rule?>" <?=$selected?>><?=$display_rule?></option>
<?php     endforeach; ?>
                </select>
<?php     if(!empty($requests['ils_requests'])):  //try to grab a requested loan period out of ils-requests data ?>
                &nbsp;(Requested loan period: <?=array_shift($requests['ils_requests'])?>)
<?php     endif; ?>
              </td>
            </tr>
            <tr>
              <td align="right" valign="top"><strong>Select Copy:</strong></td>
              <td>
<?php     
          foreach($holdingInfo as $phys_copy):
            $selected = ($phys_copy['bar'] == $selected_barcode) ? 'checked="checked"': '';
?>
            <input type="checkbox" name="physical_copy[]" value="<?=base64_encode(serialize($phys_copy))?>"<?=$selected?> />
            &nbsp;<?=$phys_copy['type']?> | <?=$phys_copy['library']?> | <?=$phys_copy['loc']?> | <?=$phys_copy['callNum']?> | <?=$phys_copy['bar']?>
            <br />
<?php       endforeach; ?>
              </td>
            </tr>
<?php     
        endif;
      endif;
?>
            <tr>
              <td colspan="2" align="center">
                <br />
                <input type="submit" id="submit_store_item_<?=$ci->getCourseInstanceID()?>" name="submit_store_item" value="Add Item to Class" style="margin-top:5px;" />
              </td>
            </tr>
          </table>          
        </form>
<?php
  } 
}
?>
