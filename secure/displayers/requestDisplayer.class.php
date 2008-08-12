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
		//echo "	<tr><td width=\"140%\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";
		if (!is_null($msg) && $msg != "")
			echo "	<tr><td width=\"100%\" class=\"failedText\" align=\"center\">$msg<br></td></tr>\n";

		echo "	<form action=\"index.php?cmd=displayRequest\" method=\"POST\">\n";
		echo "	<tr><td valign=\"top\">\n";
		echo "		<font color=\"#666666\"><strong>View Requests for </strong></font>\n";
		echo "			<select name=\"unit\">\n";
		echo "				<option value=\"all\">Show All Requests</option>\n";

		$currentUnit = isset($request['unit']) ? $request['unit'] : $user->getStaffLibrary();
		foreach ($libList as $lib)
		{
			$lib_select = ($currentUnit == $lib->getLibraryID()) ? " selected " : "";
			echo "				<option $lib_select value=\"" . $lib->getLibraryID() . "\">" . $lib->getLibraryNickname() . "</option>\n";
		}
		echo "			</select>\n";
		echo "			<input type=\"submit\" value=\"Go\">\n";
		echo "		</td>\n";
		echo "		<td bgcolor=\"#CCCCCC\" class=\"borders\"><span class=\"strong\">";
		echo "			Sort by:</span> ";
		echo "				[ <a href=\"index.php?cmd=". $request['cmd'] . "&unit=". $request['unit'] ."&sort=date\" class=\"editlinks\">Date/ID# </a>] ";
		echo "				[ <a href=\"index.php?cmd=". $request['cmd'] . "&unit=". $request['unit'] ."&sort=class\" class=\"editlinks\">Class</a> ] ";
		echo "				[ <a href=\"index.php?cmd=". $request['cmd'] . "&unit=". $request['unit'] ."&sort=instructor\" class=\"editlinks\">Instructor</a> ] ";
		echo "		</td>\n";
        echo "	</tr>\n";
        echo "	</form>\n";
        
        echo "	<form action=\"index.php?sort=\"" . $request['sort'] . "\" method=\"POST\">\n";
        echo "	<input type=\"hidden\" name=\"cmd\" value=\"printRequest\">\n";
        echo "	<input type=\"hidden\" name=\"sort\" value=\"".$request['sort']."\">\n";
        echo "	<input type=\"hidden\" name=\"no_table\">\n";
        echo "	<input type=\"hidden\" name=\"request_id\">\n";
        echo "	<tr>\n";
        echo "		<td><font color=\"#666666\">&nbsp;</font></td>";
        echo "		<td bgcolor=\"#FFFFFF\" align=\"right\"><input type=\"button\" value=\"Print Selected Request\" onClick=\"this.form.cmd.value='printRequest'; this.form.target='printPage'; this.form.submit(); checkAll(this.form, false);\">";
		echo "	</td>\n";
		echo "</tr>\n";		

		if (is_array($requestList) && !empty($requestList))
			requestDisplayer::displayRequestList($requestList);
		else 
			echo "<tr><td>No Request to process for this unit.</td></tr>";


		echo " 			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr><td>&nbsp;</td></tr>\n";
		echo "	<tr>\n";
		echo "		<td align=\"right\">\n";
		echo "			<img src=\images/spacer.gif\" width=\"1\" height=\"15\">[ <a href=\"index.php\">EXIT &quot;PROCESS REQUESTS&quot;</a> ]</td>\n";
		echo "	</tr>\n";
		echo "</table>\n";		
	}

	function printSelectedRequest($requestList, $libList, $request, $user, $msg="")
	{

		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		

		echo "	<tr>\n";
		echo "		<td align=\"left\">Request List</td>\n";
		echo "	</tr>\n";		
		
		echo "	<tr>\n";
		echo "		<td align=\"left\">". date('g:i A D m-d-Y') ."</td>\n";
		echo "	</tr>\n";
		
		echo "	<tr>\n";
		echo "		<td align=\"right\"><img src=\images/spacer.gif\" width=\"1\" height=\"15\">[ <a href=\"javascript:window.close();\">Close Window</a> ]</td>\n";
		echo "	</tr>\n";		
		
		echo "	<tr>\n";
		echo "		<td align=\"right\"><input type=\"button\" value=\"Print\" onClick=\"window.print();\"></td>\n";
		echo "	</tr>\n";
		
		
		if (!is_null($msg) && $msg != "")
			echo "	<tr><td width=\"100%\" class=\"failedText\" align=\"center\">$msg<br></td></tr>\n";

		echo " 			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";

		echo "</table>\n";		
		if (is_array($requestList) && !empty($requestList))
			requestDisplayer::displayRequestList($requestList, "true");
		else 
			echo "<p style=\"text-align: center\">No Request selected for printing.</p>";


	}	
	
	function displayRequestList($requestList, $printView=null)
	{	
		echo "	<tr><td colspan=\"2\">&nbsp;</td></tr>\n";

		echo "	<tr>\n";
		echo "		<td colspan=\"2\">\n";
		echo "			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		echo "				<tr align=\"left\" valign=\"top\"><td class=\"headingCell1\">";
?>	
		<div style="float:left;">
			<input type="checkbox" onchange="javascript: checkAll(this.form, this.checked);" />
		</div>
<?php	
		echo "REQUESTS</td><td width=\"75%\">&nbsp;</td></tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";


		$cnt = 0;
		foreach ($requestList as $r)
		{
			$item = $r->requestedItem;
			$ci = $r->courseInstance;

			$pCopy = $item->physicalCopy;

			$cnt++;

			$rowClass = ($cnt % 2) ? "evenRow" : "oddRow";

			echo "	<tr>\n";
			echo "		<td align=\"left\" valign=\"top\" class=\"borders\"  colspan=\"2\">\n";
			//echo "			<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" class=\"displayList\">\n";
			echo "			<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" id=\"printRequest\">\n";
			echo "  				<tr align=\"left\" valign=\"middle\" class=\"$rowClass\">\n";
			echo "    				<td width=\"85%\" valign=\"top\">\n";
			echo "    					<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\">\n";
			echo "						  <tr>";
			echo "							<td valign=\"top\" width=\"15%\">";
			if (is_null($printView) || $printView == "false") {
				echo "								<input type=\"checkbox\" name=\"selectedRequest[]\" value=\"" . $r->requestID . "\">&nbsp;&nbsp;<br>\n";
			}
			echo "								<span class=\"strong\">Request ID: </span>".sprintf("%06s",$r->requestID)."<br/>\n";		
			echo "								{$r->getType()} Request\n";
			echo 							"</td>";
			echo "							<td>";
			echo "								<table>";
			
			echo "					      <tr>\n";
			echo "					        <td valign=\"top\" colspan=\"2\" class=\"strong\"><a href=\"index.php?cmd=editClass&amp;ci=".$ci->getCourseInstanceID()."\">". $ci->course->displayCourseNo() ." - ". $ci->course->getName() ."</a></td>\n";
			echo "					      </tr>\n";			

			echo "					      <tr>\n";
			echo "					        <td valign=\"top\" colspan=\"2\">". $ci->displayTerm() ."</td>\n";
			echo "					      </tr>\n";

			echo "					      <tr>\n";
			echo "					        <td align=\"right\" valign=\"top\" class=\"strong\">Instructors:</td>\n";
			echo "					        <td align=\"left\" valign=\"top\">". $ci->displayInstructors(true) ."</td>\n";
			echo "					      </tr>\n";

			echo "					      <tr>\n";
			echo "					        <td align=\"right\" valign=\"top\" class=\"strong\">Title:</td>\n";
			echo "					        <td align=\"left\" valign=\"top\">". $item->title ."</td>\n";
			echo "					      </tr>\n";

			echo "					      <tr>\n";
			echo "					        <td align=\"right\" valign=\"top\" class=\"strong\">Author:</td>\n";
			echo "					        <td align=\"left\" valign=\"top\">". $item->author ."</td>\n";
			echo "					      </tr>\n";

			echo "					      <tr>\n";
			echo "					        <td align=\"right\" valign=\"top\" class=\"strong\">ISSN:</td>\n";
			echo "					        <td align=\"left\" valign=\"top\">". $item->getISSN() ."</td>\n";
			echo "					      </tr>\n";

			echo "					      <tr>\n";
			echo "					        <td align=\"right\" valign=\"top\" class=\"strong\">ISBN:</td>\n";
			echo "					        <td align=\"left\" valign=\"top\">". $item->getISBN() ."</td>\n";
			echo "					      </tr>\n";

			echo "					      <tr>\n";
			echo "					        <td align=\"right\" valign=\"top\" class=\"strong\">OCLC:</td>\n";
			echo "					        <td align=\"left\" valign=\"top\">". $item->getOCLC() ."</td>\n";
			echo "					      </tr>\n";

			if(count($r->holdings) > 0)
			{			
				echo "					      <tr>\n";
				echo "					        <td align=\"right\" valign=\"top\" class=\"strong\">Location:</td>\n";
				//should be able to select no ILS and then display commented code
//				echo "					        <td align=\"left\" valign=\"top\">". $pCopy->getOwningLibrary() . " " . $pCopy->getStatus() ." ". $pCopy->getCallNumber() ."</td>\n";
				echo "					        <td align=\"left\" valign=\"top\">\n";

				foreach ($r->holdings as $h)
				{
					echo $h['library'] . " " . $h['callNum'] . " " . $h['loc'] . " " . $h['type'] . "<br>";					
				}
				if(count($r->holdings) > 0 && (is_null($printView) || $printView == "false")) //on printview show all 
					echo "Additional copies are available. View details for all holdings";
			
				echo "							</td>\n";
				echo "					      </tr>\n";
			}

			echo "					      <tr>\n";
			echo "					        <td align=\"right\" valign=\"top\" class=\"strong\">Cross Listings:</td>\n";
			echo "					        <td align=\"left\" valign=\"top\">" . $ci->displayCrossListings() . "</td>\n";
			echo "					      </tr>\n";

			echo "					      <tr>\n";
			echo "					        <td align=\"right\" valign=\"top\" class=\"strong\">Activate By:</td>\n";
			echo "					        <td align=\"left\" valign=\"top\">". common_formatdate($r->getDesiredDate(), "MM-DD-YYYY") ."</td>\n";
			echo "					      </tr>\n";

			echo "					      <tr>\n";
			echo "					        <td align=\"right\" valign=\"top\" class=\"strong\">Date Requested:</td>\n";
			echo "					        <td align=\"left\" valign=\"top\">". common_formatdate($r->getDateRequested(), "MM-DD-YYYY") ."</td>\n";
			echo "					      </tr>\n";

			echo "					      <tr>\n";
			echo "					        <td align=\"right\" valign=\"top\" class=\"strong\">Requested Loan Period:</td>\n";
			echo "					        <td align=\"left\" valign=\"top\">". $r->reserve->getRequestedLoanPeriod() ."</td>\n";
			echo "					      </tr>\n";				

			$notes = $r->getNotes();
			if (!empty($notes)) {
				foreach ($notes as $note) {
					echo "					      <tr>\n";
					echo "					        <td align=\"right\" valign=\"top\" class=\"strong\">{$note->getType()} Note:</td>\n";
					echo "					        <td align=\"left\" valign=\"top\">{$note->getText()}</td>\n";
					echo "					      </tr>\n";				
				}
			}
			echo "    					</table>\n";
			echo "    				</td>\n";
			echo "    				<td align=\"right\" valign=\"top\" width=\"25%\">\n";
			
			if (is_null($printView) || $printView == "false")
			{
				$selected = str_replace(' ', '', $r->getStatus());
				$$selected = ' SELECTED="true" ';
				
				$processCmd = (strtoupper($r->getType()) == 'SCAN') ? "addDigitalItem" : "addPhysicalItem";
				
				echo "							<a class='requestButton' href=\"index.php?cmd={$processCmd}&request_id={$r->requestID}\">Process Request</a>&nbsp;\n";	
				echo "							<br/>\n";			
				echo "							<a class='requestButton' href=\"index.php?cmd=deleteRequest&request_id=".$r->requestID."\">Delete Request</a>&nbsp;\n";	
				echo "							<br/>\n";							
				//echo "							<p>\n";
				echo "								<div id='notice_{$r->requestID}' style='display: inline;'><img width='16px' height='16px' src='images/spacer.gif' /></div>\n";
				echo "								<select name='{$r->requestID}_status' onChange='setRequestStatus(this, {$r->requestID}, \"notice_{$r->requestID}\");'>\n";
				echo "									<option {$INPROCESS} value='IN PROCESS'>IN PROCESS</option>\n";
				//echo "									<option {$DENIED} value='DENIED'>COPYRIGHT DENIED</option>\n";
				echo "									<option {$RUSH} value='RUSH'>RUSH</option>\n";
				echo "									<option {$PULLED} value='PULLED'>PULLED</option>\n";
				echo "									<option {$CHECKEDOUT} value='CHECKED OUT'>CHECKED OUT</option>\n";
				echo "									<option {$RECALLED} value='RECALLED'>RECALLED</option>\n";
				echo "									<option {$MISSING} value='MISSING'>MISSING</option>\n";
				echo "									<option {$PURCHASING} value='PURCHASING'>PURCHASING</option>\n";
				echo "									<option {$REQUESTED} value='REQUESTED'>REQUESTED</option>\n";
				echo "									<option {$AWAITINGREVIEW} value='AWAITING REVIEW'>AWAITING REVIEW</option>\n";
				echo "								</select>\n";				
				//echo "							</p>\n";											
				
				$$selected = "";
				
			}	
			echo "					&nbsp;</td>\n";
			echo " 				</tr>\n";

			echo " </table></td></tr>";				
			echo " 			</table>\n";
//			echo "<div style=\"page-break-after: always;\"></div>\n";
// we don't need this anymore, page-break-before:always is set in the stylesheet

		}
		echo "</form>\n";		
	}
	
	
	function addItem($cmd, $item_data, $hidden_fields=null) {
		global $u, $g_permission, $g_notetype;
		
		//for ease-of-use, define helper vars for determining digital/physical items
		$isPhysical = ($cmd=='addPhysicalItem') ? true : false;
		$isDigital = ($cmd=='addDigitalItem') ? true : false;
		
		//get array of document types/icons/helper apps (digital items only)
		$doc_types = $isDigital ? $u->getAllDocTypeIcons() : null;
		//get array of libraries (physical items only)
		$libraries = $isPhysical ? $u->getLibraries() : null;
		
		//private user
		if(!empty($item_data['selected_owner'])) {
			//get id
			$selected_owner_id = $item_data['selected_owner'];
			$tmpUser = new user($selected_owner_id);
			//get name
			$selected_owner_name = $tmpUser->getName().' ('.$tmpUser->getUsername().')';
			unset($tmpUser);
		}
		
		//deal with barcode prefills
		if(!empty($_REQUEST['searchField'])) {
			if($_REQUEST['searchField'] == 'barcode') {
				$barcode_select = ' selected = "selected"';
				$control_select = '';
				//assume that this index exists
				$barcode_value = $_REQUEST['searchTerm'];
			}
			else {
				$barcode_select = '';
				$control_select = ' selected = "selected"';
				$barcode_value = (is_array($item_data['physicalCopy']) && !empty($item_data['physicalCopy'])) ? $item_data['physicalCopy'][0]['bar'] : '';
			}
			$search_term = $_REQUEST['searchTerm'];
		}
		else {
			$barcode_select = ' selected = "selected"';
			$control_select = '';
			$search_term = '';
			$barcode_value = (is_array($item_data['physicalCopy']) && !empty($item_data['physicalCopy'])) ? $item_data['physicalCopy'][0]['bar'] : '';
		}
		
		//deal with physical item source pre-select
		$addType_select = array('euclid'=>'', 'personal'=>'');
		if($isPhysical) {
			if(!empty($_REQUEST['addType']) && ($_REQUEST['addType']=='PERSONAL')) {
				$addType_select['personal'] = ' checked="true"';				
			}
			else {
				$addType_select['euclid'] = ' checked="true"';
			}			
		}
		
		//decide if need to add form encoding type
		$form_enctype = $isDigital ? ' enctype="multipart/form-data"' : '';
?>
		<script type="text/javascript">
			//shows/hides personal item elements; marks them as required or not
			function togglePersonal(enable, req) {
				//show block or not?
				if(enable) {
					document.getElementById('personal_item_row').style.display = '';
				}
				else {
					document.getElementById('personal_item_no').checked = true;
					document.getElementById('personal_item_row').style.display = 'none';
					return;
				}
				
				//if required, show just the name search and red *
				if(req) {
					document.getElementById('personal_req_mark').style.display = '';
					document.getElementById('personal_item_choice').style.display = 'none';
					document.getElementById('personal_item_owner_block').style.display = '';
					document.getElementById('personal_item_yes').checked = true;
					togglePersonalOwnerSearch();
				}
				else {
					document.getElementById('personal_req_mark').style.display = 'none';
					document.getElementById('personal_item_choice').style.display = '';
					togglePersonalOwner();
				}
			}		
			
			//shows/hides personal item owner search fields
			function togglePersonalOwner() {
				if(document.getElementById('personal_item_no').checked) {
					document.getElementById('personal_item_owner_block').style.display = 'none';
				}
				else if(document.getElementById('personal_item_yes').checked) {
					document.getElementById('personal_item_owner_block').style.display = '';
					togglePersonalOwnerSearch();
				}	
			}	
								
			//shows/hides personal item owner search fields
			function togglePersonalOwnerSearch() {	
				//if personal owner set
				if(document.getElementById('personal_item_owner_curr').checked) {
					document.getElementById('personal_item_owner_search').style.visibility = 'hidden';
				}
				else if(document.getElementById('personal_item_owner_new').checked) {
					document.getElementById('personal_item_owner_search').style.visibility = 'visible';
				}	
			}
		</script>
		
		<form action="index.php" method="post" id="additem_form" name="additem_form"<?=$form_enctype?>>
		
			<?php self::displayHiddenFields($hidden_fields); ?>
		
<?php	if($isPhysical):	//physical items; show ILS search fields ?>

		<script type="text/javascript">
			function checkForm(frm) {
				var addTypeValue;
		
				for (i=0;i<frm.addType.length;i++) {
					if (frm.addType[i].checked==true)
						addTypeValue = frm.addType[i].value;
				}
		
				var alertMsg = '';
				if (frm.title.value == '') { alertMsg = alertMsg + 'Please enter a title.<br>' }
				if (addTypeValue == 'PERSONAL' && frm.selected_owner.value == '') { alertMsg = alertMsg + 'Please select a personal owner.<br>'; }
				
				if (alertMsg == '') {
					//submit form
					return true;
				} else {
					document.getElementById('alertMsg').innerHTML = alertMsg;				
					//do not submit form
					return false;
				}
			}
	
			//disables/enables ILS elements
			function toggleILS(enable) {
				var frm = document.getElementById('additem_form');
				var dspl;
				if(enable) {
					frm.searchTerm.disabled=false;
					frm.searchField.disabled=false;
					dspl = '';
				}
				else {
					frm.searchTerm.disabled=true;
					frm.searchField.disabled=true;				
					dspl = 'none';
				}
		
				document.getElementById('ils_search').style.display = dspl;
			}
		
			//shows/hides non-manual entry elements
			function toggleNonManual(show) {
				if(document.getElementById('nonman_local_control_row')) {
					if(show) {
						document.getElementById('nonman_local_control_row').style.display = '';
						document.getElementById('nonman_local_control_input').disabled = false;
					}
					else {
						document.getElementById('nonman_local_control_row').style.display = 'none';
						document.getElementById('nonman_local_control_input').disabled = true;
					}						
				}
				
				if(document.getElementById('man_local_control_row')) {
					if(show) {
						document.getElementById('man_local_control_row').style.display = 'none';
						document.getElementById('man_local_control_input').disabled = true;
					}
					else {
						document.getElementById('man_local_control_row').style.display = '';
						document.getElementById('man_local_control_input').disabled = false;
					}
				}
			}
		</script>
		
		<div class="headingCell1" style="width:25%; text-align:center;">Item Source</div>
		<table width="100%" border="0" cellpadding="3" cellspacing="0" class="borders">
			<tr bgcolor="#CCCCCC">
				<td width="20%" align="left" valign="middle">
					<input name="addType" type="radio" value="EUCLID_ITEM" onClick="toggleILS(1); togglePersonal(0,0); toggleNonManual(1);"<?=$addType_select['euclid']?>>
					<span class="strong">EUCLID Item</span>
				</td>
				<td width="40%" align="left" valign="top">
					<input type="radio" name="addType" value="PERSONAL" onclick="toggleILS(1); togglePersonal(1,1); toggleNonManual(1);"<?=$addType_select['personal']?>>
					<span class="strong">Personal Copy (EUCLID Item Available)</span>
				</td>
				<td width="40%" align="left" valign="top">
					<input type="radio" name="addType" value="MANUAL"  onclick="toggleILS(0); togglePersonal(1, 0); toggleNonManual(0);">
					<span class="strong">Enter Item Manually (no EUCLID lookup)</span>
				</td>
			</tr>
			<tr bgcolor="#CCCCCC" id="ils_search">
				<td colspan="2" align="left" valign="middle" bgcolor="#FFFFFF">
					<input name="searchTerm" type="text" size="15" value="<?=$search_term?>">
					<select name="searchField">
						<option value="barcode"<?=$barcode_select?>>Barcode</option>
						<option value="control"<?=$control_select?>>Control Number</option>
					</select>
					&nbsp;
					<input type="submit" value="Search" onclick="this.form.cmd.value='<?=$cmd?>';" / >
				</td>
			</tr>
		</table>
		
<?php	elseif($isDigital):	//digital item; show upload/url fields ?>

		<script type="text/javascript">
			function checkForm(frm) {
				var alertMsg = '';
				if (frm.title.value == '') { alertMsg = alertMsg + 'Please enter a title.<br>';  }						
		
				if (frm.documentType[0].checked && frm.userFile.value == '')
					alertMsg = alertMsg + 'File path is required.<br>'; 
				
				if (frm.documentType[1].checked && frm.url.value == '')
					alertMsg = alertMsg + 'URL is required.<br>'; 							
					
				if (alertMsg == '') {
					//submit form
					return true;
				} else {
					document.getElementById('alertMsg').innerHTML = alertMsg;
					//do not submit form
					return false;
				}
			}
		</script>
		
		<div class="headingCell1" style="width:25%; text-align:center;">Search</div>
		<div class="borders" style="background-color:#CCCCCC; padding:5px;">
			<input type="hidden" name="searchField" value="control" />
			<strong>Barcode:</strong>
			<input type="text" name="searchTerm" value="<?php !empty($_REQUEST['searchTerm']) ? $_REQUEST['searchTerm'] : ''; ?>" />
			&nbsp; <input type="submit" value="Search" onclick="this.form.cmd.value='<?=$cmd?>';" / >
		</div>
		<br />
		
		<div class="headingCell1" style="width:25%; text-align:center;">Item Source</div>
		
<?php		if(!empty($item_data['item_id']) && !empty($item_data['url'])):	//if editing digital item ?>
		
		<script type="text/javascript">
			var currentItemSourceOptionID;
				
			function toggleItemSourceOptions(option_id) {
				if(document.getElementById(currentItemSourceOptionID)) {
					document.getElementById(currentItemSourceOptionID).style.display = 'none';
				}
				if(document.getElementById(option_id)) {
					document.getElementById(option_id).style.display = '';
				}
				
				currentItemSourceOptionID = option_id;
			}
		</script>
		
		<div id="item_source" class="borders" style="padding:8px 8px 12px 8px; background-color:#CCCCCC;">
			<div style="overflow:auto;" class="strong">
				Current URL <small>[<a href="reservesViewer.php?item=<?=$item_data['item_id']?>" target="_blank">Preview</a>]</small>: 
<?php		if($item_data['is_local_file']): //local file ?>
				Local File &ndash; <em><?=$item_data['url']?></em>
<?php		else: //remote file - show link to everyone ?>
				<em><?=$item_data['url']?></em>
<?php		endif; ?>
			</div>
			<small>
				Please note that items stored on the ReservesDirect server are access-restricted; use the Preview link to view the item.
				<br />
				To overwrite this URL, use the options below.
			</small>
			<p />
			<div>
				<input type="radio" name="documentType" value="" checked="checked" onclick="toggleItemSourceOptions('');" /> Maintain current URL &nbsp;
				<input type="radio" name="documentType" value="DOCUMENT" onclick="toggleItemSourceOptions('item_source_upload');" /> Upload new file &nbsp;
				<input type="radio" name="documentType" value="URL" onclick="toggleItemSourceOptions('item_source_link');" /> Change URL
			</div>
			<div style="margin-left:40px;">
				<div id="item_source_upload" style="display:none;">
					<input type="file" name="userFile" size="50" />
				</div>
				<div id="item_source_link" style="display:none;">
					<input name="url" type="text" size="50" />
					<input type="button" onclick="openNewWindow(this.form.url.value, 500);" value="Preview" />
				</div>
			</div>
		</div>	

<?php		else:	//new digital item ?>		

		<table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#CCCCCC" class="borders">
			<tr>
				<td align="left" colspan="2" valign="top"> <p class="strong">MATERIAL TYPE (Pick One):</p></td>
			</tr>
			<tr>
				<td align="left" valign="top">
					<font color="#FF0000"><strong>*</strong></font><input type="radio" name="documentType" value="DOCUMENT" checked onClick="this.form.userFile.disabled = !this.checked; this.form.url.disabled = !this.checked;">&nbsp;<span class="strong">Upload &gt;&gt;</span>
				</td>
				<td align="left" valign="top"><input type="file" name="userFile" size="40"></td>
			</tr>
			<tr>
				<td align="left" valign="top">
					<font color="#FF0000"><strong>*</strong></font><input type="radio" name="documentType" value="URL" onClick="this.form.url.disabled = !this.checked; this.form.userFile.disabled = this.checked;">
					<span class="strong">URL &gt;&gt;</span>
				</td>
				<td align="left" valign="top">
					<input name="url" type="text" size="50" DISABLED>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><font size='-2'>
					   http://www.reservesdirect.org<br/>
					   http://links.jstor.org/xxxxx<br/>
					   http://dx.doi.org/10.xxxxx
					</font>
				</td>
			</tr>
		</table>

<?php		
			endif;
		endif;
?>

		<br />
		<div class="headingCell1" style="width:25%; text-align:center;">Item Details</div>
		
		<table width="100%" border="0" cellpadding="3" cellspacing="0" class="borders">
			<tr align="left" valign="top" id="personal_item_row">
				<td width="20%" align="right" bgcolor="#CCCCCC" class="strong">
					<span id="personal_req_mark" style="color:#FF0000;">*</span>
					Personal Copy Owner:
					<br />&nbsp;
				</td>				
				<td>
<?php
	$personal_item_choice = array('no'=>'', 'yes'=>'');
	if(!empty($selected_owner_id)) {
		$personal_item_choice['yes'] = ' checked="true"';
	}
	else {
		$personal_item_choice['no'] = ' checked="true"';
	}
?>
					<div id="personal_item_choice">
						<input type="radio" name="personal_item" id="personal_item_no" value="no"<?=$personal_item_choice['no']?> onclick="togglePersonalOwner();" /> No
						&nbsp;&nbsp;
						<input type="radio" name="personal_item" id="personal_item_yes" value="Yes"<?=$personal_item_choice['yes']?> onclick="togglePersonalOwner();" /> Yes
					</div>
					<div id="personal_item_owner_block">
					
<?php	if(!empty($selected_owner_id)):	//if there is an existing owner, give a choice of keeping him/her or picking a new one ?>

						<input type="radio" name="personal_item_owner" id="personal_item_owner_curr" value="old" checked="checked" onclick="togglePersonalOwnerSearch();" /> Current - <strong><?=$selected_owner_name?></strong>
						<br />
						<input type="radio" name="personal_item_owner" id="personal_item_owner_new" value="new" onclick="togglePersonalOwnerSearch();" /> New &nbsp;
						
<?php	else:	//if not, then just assume we are searching for a new one ?>

						<input type="hidden" name="personal_item_owner" id="personal_item_owner_new" value="new" />

<?php	endif; ?>

						<span id="personal_item_owner_search">
<?php
			//ajax user lookup
			$mgr = new ajaxManager('lookupUser', null, null, null, null, false, array('min_user_role'=>3, 'field_id'=>'selected_owner'));
			$mgr->display();		
?>
						</span>
					</div>
				</td>
			</tr>
			<tr valign="middle">
				<td  width="20%" align="right" bgcolor="#CCCCCC" class="strong"><font color="#FF0000"><strong>*</strong></font>Title:</td>
				<td align="left"><input name="title" type="text" size="50" value="<?=$item_data['title']?>"></td>
			</tr>
			<tr valign="middle">
				<td align="right" bgcolor="#CCCCCC" class="strong"><font color="#FF0000"></font>Author/Composer:</td>
				<td align="left"><input name="author" type="text" size="50" value="<?=$item_data['author']?>"></td>
			</tr>
			<tr valign="middle">
				<td align="right" bgcolor="#CCCCCC"><span class="strong">Performer</span><span class="strong">:</span></td>
				<td align="left"><input name="performer" type="text" size="50" value="<?=$item_data['performer']?>"></td>
			</tr>
			<tr valign="middle">
				<td align="right" bgcolor="#CCCCCC"><span class="strong">Book/Journal/Work Title:</span></td>
				<td align="left"><input name="volume_title" type="text" size="50" value="<?=$item_data['volume_title']?>">
			</td>
			<tr valign="middle">
				<td align="right" bgcolor="#CCCCCC"><div align="right"><span class="strong">Volume / Edition:</span>
				</td>
				<td align="left"><input name="volume_edition" type="text" size="50" value="<?=$item_data['edition']?>"></td>
			</tr>
			<tr valign="middle">
				<td align="right" bgcolor="#CCCCCC"><span class="strong">Pages/Times:</span></td>
				<td align="left"><input name="times_pages" type="text" size="50" value="<?=$item_data['times_pages']?>"></td>
				<? if ($isDigital) { echo "<td><small>pp. 336-371 and pp. 399-442 (78 of 719)</small></td>"; } ?>
			</tr>
			<tr valign="middle">
				<td align="right" bgcolor="#CCCCCC"><span class="strong">Source / Year:</span></td>
				<td align="left"><input name="source" type="text" size="50" value="<?=$item_data['source']?>"> </td>
			</tr>

<?php	if($isDigital && !empty($doc_types)):	//document icon/mime for digital items ?>
			<tr valign="middle">
				<td align="right" bgcolor="#CCCCCC"><span class="strong">Document Type Icon:</span></td>
				<td align="left">
					<select name="selectedDocIcon" onChange="document.iconImg.src = this[this.selectedIndex].value;">
<?php		foreach($doc_types as $doc_type_info): ?>
						<option value="<?=$doc_type_info['helper_app_icon']?>"><?=$doc_type_info['helper_app_name']?></option>
<?php		endforeach; ?>
					</select>
					<img name="iconImg" width="24" height="20" border="0" src="images/doc_type_icons/doctype-clear.gif">
				</td>
			</tr>		
<?php	endif; ?>

			<tr align="left" valign="middle">
				<td class="strong" align="right" bgcolor="#cccccc">ISBN:</td>
				<td><input name="ISBN" size="15" maxlength="15" value="<?=$item_data['ISBN']?>" type="text"></td>
			</tr>
			<tr align="left" valign="middle">
				<td class="strong" align="right" bgcolor="#cccccc">ISSN:</td>
				<td><input name="ISSN" maxlength="15" size="15" value="<?=$item_data['ISSN']?>" type="text"></td>
			</tr>
			<tr align="left" valign="middle">
				<td class="strong" align="right" bgcolor="#cccccc">OCLC:</td>
				<td><input name="OCLC" maxlength="9" size="15" value="<?=$item_data['OCLC']?>" type="text"></td>
			</tr>
			<tr id="man_local_control_row" align="left" valign="middle">
				<td class="strong" align="right" bgcolor="#cccccc">Barcode / Alternate ID:</td>
				<td><input id="man_local_control_input" name="local_control_key" size="15" value="" type="text"></td>
			</tr>			

<?php	
		if($isPhysical):
			//show control # for physical items 
			if(!empty($item_data['controlKey'])):
?>
			<tr id="nonman_local_control_row" align="left" valign="middle">
				<td class="strong" align="right" bgcolor="#cccccc">Control Number:</td>
				<td>
					<?=$item_data['controlKey']?>
					<input id="nonman_local_control_input" type="hidden" name="local_control_key" value="<?=$item_data['controlKey']?>" />
				</td>
			</tr>
			
<?php		
			endif;
			
			//show reserve-desk/home-library select box
			if(!empty($libraries)):
?>
			<tr align="left" valign="top">
				<td align="right" bgcolor="#CCCCCC" class="strong">Reserve Desk:</td>
				<td>
					<select name="home_library">				
<?php			
				foreach($libraries as $lib):
					$selected = ($lib->getLibraryID()==$item_data['home_library']) ? ' selected="selected"' : '';
?>
						<option value="<?=$lib->getLibraryID()?>"<?=$selected?>><?=$lib->getLibrary()?></option>
<?php			endforeach; ?>
					</select>
<?php
			endif;
			
			//give option to choose item type and to create euclid record
			$item_group_select = array('monograph'=>'', 'multimedia'=>'');
			if($item_data['item_group']=='MULTIMEDIA') {
				$item_group_select['multimedia'] = ' checked="true"';
			}
			else {
				$item_group_select['monograph'] = ' checked="true"';
			}
?>
			<tr align="left" valign="top">
				<td align="right" bgcolor="#CCCCCC" class="strong">Item Type:</td>
				<td>
					<input type="radio" name="item_group" value="MONOGRAPH"<?=$item_group_select['monograph']?> />Monograph
					&nbsp;<input type="radio" name="item_group" value="MULTIMEDIA"<?=$item_group_select['multimedia']?> /> Multimedia
				</td>
			</tr>
			
<?php	elseif($isDigital):	//auto-set item-group for electronic items ?>		
	
			<input type="hidden" name="item_group" value="ELECTRONIC" />
			
<?php	endif; ?>

		</table>
		
		<br />
		<div class="headingCell1" style="width:25%; text-align:center;">Item Notes</div>
		<div style="padding:8px 8px 12px 8px;" class="borders">
		
<?php	if(!empty($item_data['item_id'])):	//if editing existing item, use AJAX notes handler ?>

		<script language="JavaScript1.2" src="secure/javascript/basicAJAX.js"></script>
		<script language="JavaScript1.2" src="secure/javascript/notes_ajax.js"></script>
		
		<?php self::displayNotesBlockAJAX($item_data['notes'], 'item', $item_data['item_id'], true); ?>

<?php 	else:	//just display plain note form ?>

		<strong>Add a new note:</strong>
		<br />
        <textarea name="new_note" cols="50" rows="3"></textarea>
        <br />
        <small>Note Type:
        <label><input type="radio" name="new_note_type" value="<?=$g_notetype['content']?>" checked="true">Content Note</label>
        <label><input type="radio" name="new_note_type" value="<?=$g_notetype['staff']?>">Staff Note</label>
        <label><input type="radio" name="new_note_type" value="<?=$g_notetype['copyright']?>">Copyright Note</label>

<?php	endif; ?>
				
		</div>
				
		<br />
		<strong><font color="#FF0000">* </font></strong><span class="helperText">= required fields</span></td></tr>

		<br />
		<div style="text-align:center;"><input type="submit" name="store_request" value="Add Item" onClick="return checkForm(this.form);"></div>
		</form>
		
		<script type="text/javascript">
<?php
		//if we are adding a physical item, we need to set the proper visibility defaults, based on type of item
		//we do this w/ jscript
		if($isPhysical):
?>
		//run some code to set up the form in the beginning
		var frm = document.getElementById('additem_form');
		var addTypeValue;
		
		for (i=0;i<frm.addType.length;i++) {
			if (frm.addType[i].checked==true)
				addTypeValue = frm.addType[i].value;
		}

		if( addTypeValue == 'MANUAL' ) {
			toggleILS(0);
			togglePersonal(1, 0);
			toggleNonManual(0);
		}
		else if( addTypeValue == 'PERSONAL' ) {
			toggleILS(1);
			togglePersonal(1, 1);
			toggleNonManual(1);
		}
		else {
			toggleILS(1);
			togglePersonal(0, 0);
			toggleNonManual(1);
		}

<?php	else: ?>

		//run code to set up the form in the beginning
		togglePersonal(1, 0);

<?php	endif; ?>
	</script>
		
<?php
	}
	
	
	function addSuccessful($ci, $item_id, $reserve_id, $duplicate_link=false, $ils_results='') {
		$ci->getCourseForUser();
?>
		<div class="borders" style="padding:15px; width:50%; margin:auto;">
			<strong>Item was successfully added to </strong><span class="successText"><?=$ci->course->displayCourseNo()?> <?=$ci->course->getName()?></span>		
<?php	if(!empty($ils_results)):	//show ILS record creation results ?>
				<br />
				<br />
				<div style="margin-left:20px;">
					<strong>ILS query results:</strong>
					<div style="margin-left:20px;">
						<?=$ils_results?>
					</div>
				</div>
<?php	endif; ?>
			<br />
			<ul>
				<li><a href="index.php?cmd=storeRequest&amp;item_id=<?=$item_id?>">Add this item to another class</a></li>				
<?php	if($duplicate_link): ?>
				<li><a href="index.php?cmd=duplicateReserve&amp;reserveID=<?=$reserve_id?>">Duplicate this item and add copy to the same class</a></li>
<?php	endif; ?>				
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
						 * 	'rd_requests' => array(ci1-id, ci2-id, ...),
						 * 	'ils_requests => array(
						 * 		user-id1 = array(
						 * 			'requests' => array(ils-request-id1, ils-request-id2, ...),
						 * 			'ci_list' => array(ci1-id, ci2-id, ...)
						 * 		),
						 * 		user-id2 = ...
						 *	)
						 * )
	 * @param array $selected_CIs = array(ci1_id, ci2_id, ...)
	 * @param array $CI_request_matches = array(
						 * 	ci1-id => array(
						 * 		'rd_request' => rd-req-id,
						 * 		'ils_requests' => array(
						 * 			ils-req1-id => ils-req1-period,
						 * 			ils-req2-id...
						 * 		)
						 * 	),
						 * 	ci2-id = ...
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
		
<?php			elseif($request_type == 'ils_requests'): //for ILS requests, show a different header ?>

		<br />
		<div class="headingCell1" style="width:30%">ILS requests:</div>
		
<?php			endif; ?>

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
						$ils_courses_string = rtrim($ils_courses_string, ', ');	//trim off the last comma
						
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
			$ci->getCourseForUser();	//fetch the course object
			$ci->getInstructors();	//get a list of instructors
			
			//get crosslistings
			$crosslistings = $ci->getCrossListings();
			$crosslistings_string = '';
			foreach($crosslistings as $crosslisting) {
				$crosslistings_string .= ', '.$crosslisting->displayCourseNo().' '.$crosslisting->getName();
			}
			$crosslistings_string = ltrim($crosslistings_string, ', ');	//trim off the first comma
			
			//see if there are request matches
			$requests = !empty($ci_request_matches[$ci->getCourseInstanceID()]) ? $ci_request_matches[$ci->getCourseInstanceID()] : null;
			
			//show status icon
			switch($ci->getStatus()) {
				case 'AUTOFEED':
					$edit_icon = '<img src="images/activate.gif" width="24" height="20" />';	//show the 'activate-me' icon
				break;
				case 'CANCELED':
					$edit_icon = '<img src="images/cancel.gif" alt="edit" width="24" height="20">';	//show the 'canceled' icon
				break;
				default:
					$edit_icon = '<img src="images/pencil.gif" alt="edit" width="24" height="20">';	//show the edit icon
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
			$rowStyle = (empty($rowStyle) || ($rowStyle=='evenRow')) ? 'oddRow' : 'evenRow';	//set the style
			$rowStyle2 = (empty($rowStyle2) || ($rowStyle2=='oddRow')) ? 'evenRow' : 'oddRow';	//set the style
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
<?php		if(!empty($crosslistings_string)): ?>
				<div style=" margin-left:30px; padding-top:5px;"><em>Crosslisted As:</em> <small><?=$crosslistings_string?></small></div>
<?php		endif; ?>

				<div id="add_<?=$ci->getCourseInstanceID()?>" style="display:none;">
					<?php self::displayCreateReserveForm($ci, $item_id, $circRules, $holdingInfo, $requests, $selected_barcode, $rowStyle2) ?>
				</div>
			</div>
		
<?php	endforeach; ?>
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
		 * 	ci1-id => array(
		 * 		'rd_request' => rd-req-id,
		 * 		'ils_requests' => array(
		 * 			ils-req1-id => ils-req1-period,
		 * 			ils-req2-id...
		 * 		)
		 * 	),
		 * 	ci2-id = ...
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
<?php		if($item->isPhysicalItem()): //the rest is only needed for physical items ?>						
<?php			if(!empty($holdingInfo)):	//have holding info, show physical copies ?>
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
<?php			endforeach; ?>
								</select>
<?php			if(!empty($requests['ils_requests'])):	//try to grab a requested loan period out of ils-requests data ?>
								&nbsp;(Requested loan period: <?=array_shift($requests['ils_requests'])?>)
<?php			endif; ?>
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
<?php				endforeach; ?>
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
