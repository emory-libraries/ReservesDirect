<?
/*******************************************************************************
requestDisplayer.class.php


Created by Jason White (jbwhite@emory.edu)

This file is part of GNU ReservesDirect 2.1

Copyright (c) 2004-2005 Emory University, Atlanta, Georgia.

ReservesDirect 2.1 is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

ReservesDirect 2.1 is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with ReservesDirect 2.1; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

Reserves Direct 2.1 is located at:
http://www.reservesdirect.org/

*******************************************************************************/
require_once("secure/common.inc.php");
require_once("secure/classes/terms.class.php");
require_once("secure/classes/circRules.class.php");

class requestDisplayer
{
	function displayAllRequest($requestList, $libList, $request, $user, $msg="")
	{

		echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
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
        echo "		<td bgcolor=\"#FFFFFF\" align=\"right\"><input type=\"button\" value=\"Print Selected Request\" onClick=\"this.form.cmd.value='printRequest'; this.form.target='printPage'; this.form.submit();\">";
		echo "	</td>\n";
		echo "</tr>\n";		

		if (is_array($requestList) && !empty($requestList))
			requestDisplayer::displayRequestList($requestList, $item, $ci);
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

		echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		

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
			requestDisplayer::displayRequestList($requestList, $item, $ci, "true");
		else 
			echo "<p style=\"text-align: center\">No Request selected for printing.</p>";


	}	
	
	function displayRequestList($requestList, $item, $ci, $printView=null)
	{	
		echo "	<tr><td colspan=\"2\">&nbsp;</td></tr>\n";

		echo "	<tr>\n";
		echo "		<td colspan=\"2\">\n";
		echo "			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		echo "				<tr align=\"left\" valign=\"top\"><td class=\"headingCell1\" align=\"center\">REQUESTS</td><td width=\"75%\">&nbsp;</td></tr>\n";
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
			echo "					      <tr>\n";				
			echo "					        <td width=\"15%\" valign=\"top\">\n";
			if (is_null($printView) || $printView == "false")
				echo "								<input type=\"checkbox\" name=\"selectedRequest[]\" value=\"" . $r->requestID . "\">&nbsp;&nbsp;<br>\n";
			echo "								<span class=\"strong\">Request ID: </span>".sprintf("%06s",$r->requestID)."\n";
			echo "							</td>\n";
			echo "					        <td width=\"50%\" valign=\"top\" class=\"strong\">". $ci->course->getName() ."</td>\n";
			echo "					        <td width=\"35%\"><span class=\"strong\">". $ci->course->displayCourseNo() ."</span> <!--| <a href=\"link\">Display All Class Requests for Print</a>--></td>\n";
			echo "					      </tr>\n";

			echo "						  <tr>";
			echo "							<td>&nbsp;</td>";
			echo "							<td colspan='2'>";
			echo "								<table>";

			echo "					      <tr>\n";
			echo "					        <td valign=\"top\">&nbsp;</td>\n";
			echo "					        <td valign=\"top\">". $ci->displayTerm() ."</td>\n";
			echo "					        <td align=\"left\" valign=\"top\"></td>\n";
			echo "					      </tr>\n";

			echo "					      <tr>\n";
			echo "					        <td valign=\"top\">&nbsp;</td>\n";
			echo "					        <td align=\"right\" valign=\"top\" class=\"strong\">Instructors:</td>\n";
			echo "					        <td align=\"left\" valign=\"top\">". $ci->displayInstructors() ."</td>\n";
			echo "					      </tr>\n";

			echo "					      <tr>\n";
			echo "					        <td valign=\"top\">&nbsp;</td>\n";
			echo "					        <td align=\"right\" valign=\"top\" class=\"strong\">Title:</td>\n";
			echo "					        <td align=\"left\" valign=\"top\">". $item->title ."</td>\n";
			echo "					      </tr>\n";

			echo "					      <tr>\n";
			echo "					        <td valign=\"top\">&nbsp;</td>\n";
			echo "					        <td align=\"right\" valign=\"top\" class=\"strong\">Author:</td>\n";
			echo "					        <td align=\"left\" valign=\"top\">". $item->author ."</td>\n";
			echo "					      </tr>\n";

			if(count($r->holdings) > 0)
			{			
				echo "					      <tr>\n";
				echo "					        <td valign=\"top\">&nbsp;</td>\n";
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
			echo "					        <td valign=\"top\">&nbsp;</td>\n";
			echo "					        <td align=\"right\" valign=\"top\" class=\"strong\">Cross Listings:</td>\n";
			echo "					        <td align=\"left\" valign=\"top\">" . $ci->displayCrossListings() . "</td>\n";
			echo "					      </tr>\n";

			/*
			echo "					      <tr>\n";
			echo "					        <td valign=\"top\">&nbsp;</td>\n";
			echo "					        <td align=\"right\" valign=\"top\" class=\"strong\">Notes:</td>\n";
			echo "					        <td align=\"left\" valign=\"top\">Not Implemeted</td>\n";
			echo "					      </tr>\n";
			*/

			echo "					      <tr>\n";
			echo "					        <td valign=\"top\">&nbsp;</td>\n";
			echo "					        <td align=\"right\" valign=\"top\" class=\"strong\">Activate By:</td>\n";
			echo "					        <td align=\"left\" valign=\"top\">". common_formatdate($r->getDesiredDate(), "MM-DD-YYYY") ."</td>\n";
			echo "					      </tr>\n";

			echo "					      <tr>\n";
			echo "					        <td valign=\"top\">&nbsp;</td>\n";
			echo "					        <td align=\"right\" valign=\"top\" class=\"strong\">Date Requested:</td>\n";
			echo "					        <td align=\"left\" valign=\"top\">". common_formatdate($r->getDateRequested(), "MM-DD-YYYY") ."</td>\n";
			echo "					      </tr>\n";

			echo "					      <tr>\n";
			echo "					        <td valign=\"top\">&nbsp;</td>\n";
			echo "					        <td align=\"right\" valign=\"top\" class=\"strong\">Requested Loan Period:</td>\n";
			echo "					        <td align=\"left\" valign=\"top\">". $r->reserve->getRequestedLoanPeriod() ."</td>\n";
			echo "					      </tr>\n";				
			
			echo "					      <tr>\n";
			echo "					        <td valign=\"top\">&nbsp;</td>\n";
			echo "					        <td valign=\"top\">&nbsp;</td>\n";
			echo "					        <td align=\"left\" valign=\"top\">&nbsp;</td>\n";
			echo "					      </tr>\n";

			echo "    					</table>\n";
			echo "    				</td>\n";
			echo "    				<td align=\"right\" valign=\"top\">\n";
			
			if (is_null($printView) || $printView == "false")
			{
				echo "							<input type=\"button\" value=\"Process this Item\" onClick=\"this.form.cmd.value='processRequest'; this.form.target=window.name; this.form.no_table.value='false'; this.form.request_id.value=".$r->requestID.";this.form.submit();\">\n";
			
				echo "						&nbsp;<a href=\"index.php?cmd=deleteRequest&request_id=".$r->requestID."\">Delete Request</a>&nbsp;";	
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
	
	
	
	function addItem($user, $cmd, $search_results, $owner_list, $lib_list, $request_id=null, $request, $hidden_fields, $docTypeIcons=null, $isActive=true, $buttonValue="Add Item", $msg="", $requestLoanPeriod=null)
	{
		global $g_documentURL, $g_permission;

		$circRules = new circRules();

		//Added by kawashi on 12.1.04
		//This is so the reserve activation date will match the course instance activation date
		if (is_array($hidden_fields)){
			$ci = new courseInstance($hidden_fields['ci']);
			list($y, $m, $d) = split("-", $ci->getActivationDate());
		}
		//End of added code section

		echo "<script languge=\"JavaScript\">\n";
		echo "	function setBarcode(frm) { if (frm.searchField.options[frm.searchField.selectedIndex] == 'barcode') { frm.barcode.value = frm.searchTerm.value; } }\n";

		if ($cmd != "addDigitalItem")
		{
			echo "	function checkForm(frm) {
						var addTypeValue;
						var copySelected = 1;

						for (i=0;i<frm.elements.length;i++){
							e = frm.elements[i];
							if (e.type == 'checkbox' && e.name=='physical_copy[]' && e.checked) {
								copySelected = 0;
							}
						}

						for (i=0;i<frm.addType.length;i++){
							if (frm.addType[i].checked==true)
								addTypeValue = frm.addType[i].value;
						}

						var alertMsg = '';
						if (frm.title.value == '') { alertMsg = alertMsg + 'Please enter a title.<br>' }
						if (frm.author.value == '') { alertMsg = alertMsg + 'Please enter an author.<br>';  }
						if (addTypeValue != 'MANUAL' && frm.euclid_record.checked && copySelected) { alertMsg = alertMsg + 'Please select a copy to place on reserve<br>'; }
						if (addTypeValue == 'PERSONAL' && frm.selected_owner.selectedIndex == '0') { alertMsg = alertMsg + 'Please select a personal owner.<br>'; }
						
						if (alertMsg == '') {
							frm.cmd.value = 'storeRequest';
							frm.submit();
						} else {
							document.getElementById('alertMsg').innerHTML = alertMsg;
						}
					}";
		} else {
			echo "	function checkForm(frm) {
						var alertMsg = '';
						if (frm.title.value == '') { alertMsg = alertMsg + 'Please enter a title.<br>';  }						
						if (frm.author.value == '') { alertMsg = alertMsg + 'Please enter an author.<br>';  }

						if (frm.documentType[0].checked && frm.userFile.value == '')
							alertMsg = alertMsg + 'File path is required.<br>'; 
						
						if (frm.documentType[1].checked && frm.url.value == '')
							alertMsg = alertMsg + 'URL is required.<br>'; 							
							
						if (alertMsg == '') {
							frm.cmd.value = 'storeRequest';
							frm.submit();
						} else {
							document.getElementById('alertMsg').innerHTML = alertMsg;
						}
					}					
			";
		}
		
		echo "</script>\n";

		echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr><td width=\"100%\"><img src=\images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>\n";

		echo "	<tr><td width=\"100%\" class=\"failedText\" align=\"center\">$msg<br></td></tr>\n";

		echo "	<tr><td align=\"left\" valign=\"top\" class=\"headingCell1\" width=\"25%\">ITEM SOURCE</td><td width=\"75%\">&nbsp;</td></tr>\n";
		echo "	<tr><td align=\"left\" valign=\"top\">\n";

		$formEncode = ($cmd == "addDigitalItem") ? "enctype=\"multipart/form-data\"" : "";

		echo "<form action=\"index.php\" method=\"POST\" $formEncode>\n";

		if (is_array($hidden_fields)){
			$keys = array_keys($hidden_fields);
			foreach($keys as $key){
				if (is_array($hidden_fields[$key])){
					foreach ($hidden_fields[$key] as $field){
						echo "<input type=\"hidden\" name=\"".$key."[]\" value=\"". $field ."\">\n";
					}
				} else {
					echo "<input type=\"hidden\" name=\"$key\" value=\"". $hidden_fields[$key] ."\">\n";
				}
			}
		}

		if ($cmd == "addDigitalItem")
		{
			echo "	<input type=\"hidden\" name=\"item_type\" value=\"ELECTRONIC\">\n";
			echo "	<input type=\"hidden\" name=\"home_library\" value=\"1\">\n"; //will not be processed so set to default of Woodruff
			echo "			<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"borders\">\n";
			echo "				<tr>\n";
			echo "					<td width=\"25%\" align=\"left\" valign=\"top\"> <p class=\"headingCell1\" >MATERIAL TYPE (Pick One):</p></td><td width=\"75%\">&nbsp;</td>\n";
			echo "				</tr>\n";
			echo "				<tr>\n";
			echo "					<td align=\"left\" valign=\"top\" colspan=\"2\">\n";
			echo "						<table width=\"100%\" border=\"0\" cellpadding=\"3\" cellspacing=\"0\" bgcolor=\"#CCCCCC\">\n";
			echo "							<tr class=\"borders\">\n";
			echo "								<td align=\"left\" valign=\"top\">\n";
			echo "									<font color=\"#FF0000\"><strong>*</strong></font><input type=\"radio\" name=\"documentType\" value=\"DOCUMENT\" checked onClick=\"this.form.userFile.disabled = !this.checked; this.form.url.disabled = this.checked; this.form.prependURL.disabled = this.checked;\">&nbsp;<span class=\"strong\">Upload&gt;&gt;</span>\n";
			echo "								</td>\n";
			echo "								<td align=\"left\" valign=\"top\"><input type=\"file\" name=\"userFile\" size=\"40\"></td>\n";
			echo "							</tr>\n";
			echo "							<tr class=\"borders\">\n";
			echo "								<td align=\"left\" valign=\"top\">\n";
			echo "									<font color=\"#FF0000\"><strong>*</strong></font>\n";
			echo "									<input type=\"radio\" name=\"documentType\" value=\"URL\" onClick=\"this.form.url.disabled = !this.checked; this.form.prependURL.disabled = !this.checked; this.form.userFile.disabled = this.checked;\">\n";
			echo "									<span class=\"strong\"> URL&gt;&gt;</span>\n";
			echo "								</td>\n";
			echo "								<td align=\"left\" valign=\"top\">\n";
			echo "									<input name=\"url\" type=\"text\" size=\"100\" DISABLED>\n";
			echo "									&nbsp; <input DISABLED type=\"checkbox\" name=\"prependURL\" onClick=\" if (this.checked) this.form.url.value = '$g_documentURL'+this.form.url.value;\">\n";
			echo "									<span class=\"small\">Prepend eReserves hostname to path.</span>\n";
			echo "								</td>\n";
			echo "							</tr>\n";
			echo "						</table>\n";
			echo "					</td>\n";
			echo "				</tr>\n";
			echo "			</table>\n";
			echo "		</td>\n";
			echo "	</tr>\n";
		} else {

			$PERSONAL = "";
			$EUCLID_ITEM = "";
			$MANUAL = "";

			$search_selector = (isset($request['addType'])) ? $request['addType'] : 'EUCLID_ITEM';
			$$search_selector = "checked";
			echo "			<table width=\"100%\" border=\"0\" cellpadding=\"3\" cellspacing=\"0\" class=\"borders\">\n";
			echo "				<tr bgcolor=\"#CCCCCC\">\n";
			echo "					<td width=\"20%\" align=\"left\" valign=\"middle\">\n";
			echo "						<input name=\"addType\" type=\"radio\" value=\"EUCLID_ITEM\" $EUCLID_ITEM  onClick=\"this.form.personal_item.value='no'; this.form.searchTerm.disabled=false; this.form.searchField.disabled=false; this.form.euclid_record.checked=false; this.form.euclid_record.disabled=false; this.form.submit();\">\n";
			echo "						<span class=\"strong\">EUCLID Item</span>\n";
			echo "					</td>\n";
			echo "					<td width=\"40%\" align=\"left\" valign=\"top\">\n";
			echo "						<input type=\"radio\" name=\"addType\" value=\"PERSONAL\" $PERSONAL onClick=\"this.form.personal_item.value='yes'; this.form.searchTerm.disabled=true; this.form.searchField.disabled=true; this.form.euclid_record.checked=true; this.form.euclid_record.disabled=true; this.form.submit();\">\n";
			echo "						<span class=\"strong\">Personal Copy (EUCLID Item Available)</span>\n";
			echo "					</td>\n";

			echo "					<td width=\"40%\" align=\"left\" valign=\"top\">\n";
			echo "						<input type=\"radio\" name=\"addType\" value=\"MANUAL\" $MANUAL onClick=\"this.form.searchTerm.disabled=true; this.form.searchField.disabled=true; this.form.euclid_record.checked=false; this.form.euclid_record.disabled=true;\">\n";
			echo "						<span class=\"strong\">Enter Item Manually (no EUCLID lookup)</span>\n";
			echo "					</td>\n";
			echo "				</tr>\n";

			$searchTerm = isset($request['searchTerm']) ? $request['searchTerm'] : "";
			echo "				<tr bgcolor=\"#CCCCCC\">\n";
			echo "					<td colspan=\"2\" align=\"left\" valign=\"middle\" bgcolor=\"#FFFFFF\">\n";
			echo "						<input name=\"searchTerm\" type=\"text\" size=\"15\" value=\"".$searchTerm."\">\n";

			//set selected
			$barcode = "";
			$local_control = "";
			$selector = (isset($request['searchField'])) ? $request['searchField'] : "barcode";
			$$selector = "selected";

			echo "						<select name=\"searchField\">\n";
			echo "							<option value=\"barcode\" $barcode>Barcode</option>\n";
			//echo "							<option value=\"isbn\">ISBN</option>\n";
			//echo "							<option value=\"issn\">ISSN</option>\n";
			echo "							<option value=\"local_control\" $local_control>Control Number</option>\n";
			echo "						</select>\n";
			echo "						&nbsp;\n";
			echo "						<input type=\"submit\" value=\"Search\">\n";
			echo "					</td>\n";
			echo "				</tr>\n";
			echo "			</table>\n";

			echo "		</td>\n";
			echo "	</tr>\n";
			echo "	<tr><td align=\"left\" valign=\"top\">&nbsp;</td></tr>\n";
			echo "	<tr><td align=\"left\" valign=\"top\" class=\"headingCell1\">RESERVE OPTIONS</td><td width=\"75%\">&nbsp;</td></tr>\n";
			echo "	<tr>\n";
			echo "		<td align=\"left\" valign=\"top\">\n";
			echo "			<table width=\"100%\" border=\"1\" cellpadding=\"3\" cellspacing=\"0\" class=\"borders\">\n";
			echo "				<tr bgcolor=\"#CCCCCC\">\n";
			echo "					<td width=\"50%\" align=\"left\" valign=\"middle\" class=\"strong\" NOWRAP>Reserve Desk:&nbsp;&nbsp;\n";

			$home_lib = (isset($request['home_library'])) ? $request['home_library'] : 1;
			echo "						<select name=\"home_library\">\n";
			foreach($lib_list as $lib)
			{
				$lib_selector = ($home_lib == $lib->getLibraryID()) ? "selected" : "";
				echo "							<option value=\"". $lib->getLibraryID() ."\" $lib_selector>". $lib->getLibrary() ."</option>\n";
			}
			echo "					    </select>\n";

			echo "					</td>\n";
			echo "					<td width=\"50%\" align=\"left\" valign=\"middle\" class=\"strong\">Loan Period:&nbsp;&nbsp;\n";
			echo "						<select name=\"circRule\">\n";

			foreach ($circRules->getCircRules() as $circRule)
			{
				$rule = $circRule['circRule'] . "::" . $circRule['alt_circRule'];
				$display_rule = $circRule['circRule']." -- " . $circRule['alt_circRule'];
				$selected = $circRule['default'];
				echo "							<option value=\"$rule\" $selected>$display_rule</option>\n";
			}

			echo "						</select>\n";
			echo "					</td>\n";
			echo "				</tr>\n";

			$MULTIMEDIA = "";
			$MONOGRAPH = "";
			$itemType_selector = (isset($request['item_type'])) ? $request['item_type'] : "MONOGRAPH";
			$$itemType_selector = "checked";
			echo "				<tr bgcolor=\"#CCCCCC\">\n";
			echo "					<td colspan=\"1\" align=\"left\" valign=\"middle\" class=\"strong\" NOWRAP>";
			echo "						<span class=\"strong\">Item Type:</span>\n";
			echo "						&nbsp;&nbsp;\n";
			echo "						<input type=\"radio\" name=\"item_type\" value=\"MONOGRAPH\" CHECKED> Monograph";
			echo "						&nbsp;";
			echo "						<input type=\"radio\" name=\"item_type\" value=\"MULTIMEDIA\" $MULTIMEDIA> Multimedia";
			echo "					</td>\n";
			echo "					<td align=\"left\" valign=\"middle\" class=\"strong\" NOWRAP>Requested Loan Period: $requestLoanPeriod</td>\n";
			echo "				</tr>\n";


			echo "				<tr bgcolor=\"#CCCCCC\">\n";
			echo "					<td colspan=\"2\" align=\"left\" valign=\"middle\">\n";
			echo "						<input type=\"checkbox\" name=\"euclid_record\" value=\"yes\" checked>\n";
			echo "						<span class=\"strong\">Create EUCLID Reserve Record</span>\n";
			echo "					</td>\n";
			echo "				</tr>\n";
			echo "			</table>\n";
			echo "		</td>\n";
			echo "	</tr>\n";

			if (is_null($search_results) && isset($request['searchTerm']))
			{
				echo "	<tr><td align=\"left\" valign=\"top\">&nbsp;</td></tr>\n";
				echo "	<tr><td align=\"center\" valign=\"top\">No Record Found. Search again or enter manually.</td></tr>\n";
			}
		} // if == addDigital
		
		echo "	<tr><td align=\"left\" valign=\"top\">&nbsp;</td></tr>\n";

		echo "	<tr>\n";
		echo "		<td align=\"left\" valign=\"top\">\n";
		echo "			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		echo "				<tr align=\"left\" valign=\"top\">\n";
		echo "					<td class=\"headingCell1\" align=\"center\">ITEM DETAILS</td>\n";
		echo "					<td width=\"75%\">&nbsp;</td>\n";
		echo "				</tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td align=\"left\" valign=\"top\" class=\"borders\">\n";
		echo "			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\">\n";
		echo "				<tr valign=\"middle\">\n";
		echo "					<td colspan=\"2\" align=\"right\" bgcolor=\"#CCCCCC\" class=\"borders\">\n";
		echo "						<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		echo "							<tr>\n";
		echo "								<td width=\"50%\" height=\"14\">\n";
		echo "									<p><span class=\"strong\">Current Status:</span><strong>";

		if ($isActive)
			echo "									<font color=\"#009900\">ACTIVE</font></strong> | <input type=\"checkbox\" name=\"currentStatus\" value=\"INACTIVE\">Deactivate?</p>\n";
		else
			echo "									<font color=\"#009900\">INACTIVE</font></strong> | <input type=\"checkbox\" name=\"currentStatus\" value=\"ACTIVE\">Activate?</p>\n";

		echo "								</td>\n";
		echo "								<td width=\"50%\">\n";
		echo "									<span class=\"strong\">Hide Until:</span>\n";
		echo "										<input name=\"hide_month\" type=\"text\" size=\"2\" maxlength=\"2\" value=\"$m\">\n";
		echo "										/\n";
		echo "										<input name=\"hide_day\" type=\"text\" size=\"2\" maxlength=\"2\" value=\"$d\">\n";
		echo "										/\n";
		echo "										<input name=\"hide_year\" type=\"text\" size=\"4\" maxlength=\"4\" value=\"$y\">\n";
		echo "										mm/dd/yyyy\n";
		echo "								</td>\n";
		echo "							</tr>\n";
		echo "						</table>\n";
		echo "					</td>\n";
		echo "				</tr>\n";
		echo "				<tr valign=\"middle\">\n";
		echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\" class=\"strong\"><font color=\"#FF0000\"><strong>*</strong></font>Title:</td>\n";
		echo "					<td align=\"left\"><input name=\"title\" type=\"text\" size=\"50\" value=\"".$search_results['title']."\"></td>\n";
		echo "				</tr>\n";
		echo "				<tr valign=\"middle\">\n";
		echo "					<td width=\"35%\" height=\"31\" align=\"right\" bgcolor=\"#CCCCCC\" class=\"strong\"><font color=\"#FF0000\"><strong>*</strong></font>Author/Composer:</td>\n";
		echo "					<td align=\"left\"><input name=\"author\" type=\"text\" size=\"50\" value=\"".$search_results['author']."\"></td>\n";
		echo "				</tr>\n";
		echo "				<tr valign=\"middle\">\n";
		echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\"><span class=\"strong\">Performer</span><span class=\"strong\">:</span></td>\n";
		echo "					<td align=\"left\"><input name=\"performer\" type=\"text\" size=\"50\" value=\"".$search_results['performer']."\"></td>\n";
		echo "				</tr>\n";

		echo "				<tr valign=\"middle\">\n";
		echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\"><span class=\"strong\">Book/Journal/Work Title:</span></td>\n";
		echo "					<td align=\"left\"><input name=\"volume_title\" type=\"text\" size=\"50\" value=\"".$search_results['volume_title']."\">\n";
		echo "				</td>\n";

		echo "				<tr valign=\"middle\">\n";
		echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\"><span class=\"strong\">Volume / Edition</span>\n";
		echo "						<span class=\"strong\">:</span>\n";
		echo "					</td>\n";
		echo "					<td align=\"left\"><input name=\"volume_edition\" type=\"text\" size=\"50\" value=\"".$search_results['edition']."\"></td>\n";
		echo "				</tr>\n";

		echo "				<tr valign=\"middle\">\n";
		echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\"><span class=\"strong\">Pages/Times:</span></td>\n";
		echo "					<td align=\"left\"><input name=\"times_pages\" type=\"text\" size=\"50\" value=\"".$search_results['times_pages']."\"></td>\n";
		echo "				</tr>\n";

		echo "				<tr valign=\"middle\">\n";
		echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\"><span class=\"strong\">Source / Year:</span></td>\n";
		echo "					<td align=\"left\"><input name=\"source\" type=\"text\" size=\"50\" value=\"".$search_results['source']."\"> </td>\n";
		echo "				</tr>\n";

		if (!is_null($docTypeIcons))
		{
			echo "				<tr valign=\"middle\">\n";
			echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\"><span class=\"strong\">Document Type Icon:</span></td>\n";
			echo "					<td align=\"left\">";
			echo "						<select name=\"selectedDocIcon\" onChange=\"document.iconImg.src = this[this.selectedIndex].value;\">\n";
					
			for ($j = 0; $j<count($docTypeIcons); $j++)
			{
				//$selectedIcon = ($search_results['docTypeIcon'] == $docTypeIcons[$j]['helper_app_icon']) ? " selected " : "";
				echo "							<option value=\"" . $docTypeIcons[$j]['helper_app_icon']  . "\" $selectedIcon>" . $docTypeIcons[$j]['helper_app_name'] . "</option>\n";
			}
				
			echo "						</select>\n";
			echo "					<img name=\"iconImg\" width=\"24\" height=\"20\" border=\"0\" src=\"".$search_results['docTypeIcon']."\">\n";
			echo "					</td>\n";
			echo "				</tr>\n";
		}	
		
		
		//echo "				<tr align=\"left\" valign=\"middle\">\n";
		//echo "					<td align=\"right\" bgcolor=\"#CCCCCC\" class=\"strong\">Call Number:</td>\n";
		//echo "					<td><input type=\"text\" size=\"30\" name=\"callNumber\" value=\"".$search_results['callNumber'][0]."\"></td>\n";
		//echo "				</tr>\n";
		echo "				<tr align=\"left\" valign=\"middle\">\n";
		
		if ($user->dfltRole >= $g_permission['staff']) {
				
				$contentChecked="";
				$instructorChecked="";
				$staffChecked="";
				$copyrightChecked="";
				
				if ($search_results['noteType'] != "" && $search_results['content_note'] != "") {
					switch ($search_results['noteType']) {
						case 'Content':
							$contentChecked="checked";
						break;
						case 'Instructor':
							$instructorChecked="checked";
						break;
						case 'Staff':
							$staffChecked="checked";
						break;
						case 'Copyright':
							$copyrightChecked="checked";
						break;
					}
				} else {
					$contentChecked="checked";
				}
				
				echo "					<td align=\"right\" valign=\"top\" bgcolor=\"#CCCCCC\" class=\"strong\">Note:</td>\n";
				echo "					<td><TEXTAREA name=\"content_note\" cols=\"50\" rows=\"3\">".$search_results['content_note']."</TEXTAREA>\n<br>\n";
	
  			echo '      			<span class="small">Note Type:';
    		echo '					<label><input type="radio" name="noteType" value="Content" '.$contentChecked.'>Content Note</label>';
    		echo '					<label><input type="radio" name="noteType" value="Instructor" '.$instructorChecked.'>Instructor Note</label>';
    		echo '					<label><input type="radio" name="noteType" value="Staff" '.$staffChecked.'>Staff Note</label>';
				echo '					<label><input type="radio" name="noteType" value="Copyright" '.$copyrightChecked.'>Copyright Note</label>';
				echo '					</span>';
			} else {
				echo "					<td align=\"right\" valign=\"top\" bgcolor=\"#CCCCCC\" class=\"strong\">Instructor Note:</td>\n";
				echo "					<td><TEXTAREA name=\"content_note\" cols=\"50\" rows=\"3\">".$search_results['content_note']."</TEXTAREA>\n<br>\n";
				echo '					<input type="hidden" name="noteType" value="Instructor">';
			}
		
		//echo "					<td align=\"right\" valign=\"top\" bgcolor=\"#CCCCCC\" class=\"strong\">Content Notes:</td>\n";
		//echo "					<td><textarea name=\"content_note\" cols=\"50\" rows=\"3\">".$search_results['content_note']."</textarea></td>\n";
		echo "				</td></tr>\n";

		$personal_item = isset($request['personal_item']) ? $request['personal_item'] : "";
		echo "				<input type=\"hidden\" name=\"personal_item\" value=\"".$personal_item."\">\n";

		if (isset($request['personal_item']) && ($request['personal_item'] == "yes") || !is_null($search_results['personal_owner']))
		{
			echo "				<tr align=\"left\" valign=\"middle\">\n";
			echo "					<td align=\"right\" bgcolor=\"#CCCCCC\" class=\"strong\">\n";
			echo "						<span class=\"strong\">Personal Copy Owner:</span>\n";
			echo "					</td>\n";


			//set selected
			$username = "";
			$last_name = "";
			$selector = (isset($request['select_owner_by'])) ? $request['select_owner_by'] : "last_name";
			$$selector = "selected";

			$owner_qryTerm = (isset($request['owner_qryTerm'])) ? $request['owner_qryTerm'] : "";

			echo "					</td>\n";
			echo "					<td>\n";
			echo "						<select name=\"select_owner_by\">\n";
			echo "							<option value=\"last_name\" $last_name>Last Name</option>\n";
			echo "							<option value=\"username\" $username>User Name</option>\n";
			echo "						</select> &nbsp; <input name=\"owner_qryTerm\" type=\"text\" value=\"".$owner_qryTerm."\" size=\"15\"  onBlur=\"this.form.submit();\">\n";
			echo "						&nbsp;\n";
			echo "						<input type=\"submit\" name=\"owner_search\" value=\"Search\">\n";
			echo "						&nbsp;\n";

			//set selected
			$inst_DISABLED = (is_null($owner_list)) ? "DISABLED" : "";

			echo "						<font color=\"#FF0000\"><strong>*</strong>\n";
			echo "						<select name=\"selected_owner\" $inst_DISABLED\">\n";
			echo "							<option value=\"null\">-- Choose Item Owner -- </option>\n";

			for($i=0;$i<count($owner_list);$i++)
			{
				$inst_selector = ($request['selected_owner'] == $owner_list[$i]->getUserID() || $search_results['personal_owner'] == $owner_list[$i]->getUserID()  ) ? "selected" : "";
				echo "							<option value=\"". $owner_list[$i]->getUserID() ."\" $owner_selector>". $owner_list[$i]->getName() ."</option>\n";
			}

			echo "						</select>\n";
			echo "					</td>\n";
			echo "				</tr>\n";
		}

		$barcode_value = (isset($barcode) && (isset($request['searchTerm']) && $request['searchTerm'] != "")) ? $request['searchTerm'] : $search_results['physicalCopy'][0]['bar'];			
		
		echo "				<tr align=\"left\" valign=\"middle\">\n";
		echo "					<td align=\"right\" bgcolor=\"#CCCCCC\" class=\"strong\">Barcode:</td>\n";
		echo "					<td><input name=\"barcode\" type=\"text\" size=\"12\" value=\"$barcode_value\"></td>\n";
		echo "				</tr>\n";

		echo "				<tr align=\"left\" valign=\"middle\">\n";
		echo "					<td align=\"right\" bgcolor=\"#CCCCCC\" class=\"strong\">Control Number:</td>\n";
		echo "					<td>".$search_results['controlKey']."<input name=\"controlKey\" type=\"hidden\" size=\"10\" value=\"".$search_results['controlKey']."\"></td>\n";
		echo "				</tr>\n";

		if (is_array($search_results['physicalCopy']))
		{
			echo "				<tr align=\"left\" valign=\"top\">\n";
			echo "					<td align=\"right\" bgcolor=\"#CCCCCC\" class=\"strong\">Select Copy:</td>\n";
			echo "					<td>\n";
			echo "						<table class=\"strong\" border=\"0\" width=\"100%\">\n";

			for ($i=0;$i<count($search_results['physicalCopy']);$i++)
			{
				$copySelect = (count($search_results['physicalCopy']) == 1 || $search_results['physicalCopy'][$i]['bar'] == $barcode_value) ? "checked" : "";
				$phyCopy = $search_results['physicalCopy'][$i];
				echo "							<tr>\n";
				echo "								<td><input type=\"checkbox\" $copySelect name=\"physical_copy[]\" value=\"".$phyCopy['type']."::".$phyCopy['library']."::".$phyCopy['callNum']."::".$phyCopy['loc']."::".$phyCopy['bar']."::".$phyCopy['copy']."\"></td>\n";
				echo "								<td>".$phyCopy['type']." ".$phyCopy['library']." ".$phyCopy['loc']." ".$phyCopy['callNum']."</td>\n";
				echo "							</tr>\n";
			}
			echo "						</table>\n";
			echo "					</td>\n";
			echo "				</tr>\n";
		} //else
			//echo "	<tr><td align=\"right\" bgcolor=\"#CCCCCC\" class=\"strong\">COULD NOT RETRIEVE HOLDING INFORMATION</td><td>". $search_results['physicalCopy']['error'] . "</td></tr>\n";

		echo "				<tr align=\"left\" valign=\"middle\">\n";
		echo "					<td align=\"right\" bgcolor=\"#CCCCCC\" class=\"strong\">&nbsp;</td>\n";
		echo "					<td>&nbsp;</td>\n";
		echo "				</tr>\n";
		//echo "				<tr valign=\"middle\">\n";
		//echo "					<td colspan=\"2\" align=\"center\" valign=\"top\" bgcolor=\"#CCCCCC\" class=\"borders\">\n";
		//echo "						<input type=\"button\" name=\"Submit2\" value=\"Add Note\" onClick=\"openWindow('&cmd=addNote&noteTargetTable=item&noteTargetID=' + this.form.request_id.value);\">\n";
		//echo "					</td>\n";
		//echo "				</tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr><td><strong><font color=\"#FF0000\">* </font></strong><span class=\"helperText\">= required fields</span></td></tr>\n";

		echo "	<tr><td align=\"center\"><input type=\"checkbox\" name=\"addDuplicate\" value=\"addDuplicate\">&nbsp;<span class=\"small\">Create Item Duplicate</span></td></tr>\n";

		echo "	<tr><td align=\"center\"><input type=\"button\" name=\"store_request\" value=\"$buttonValue\" onClick=\"checkForm(this.form);\"></td></tr>\n";
		echo "</form\n";
		echo "	<tr><td><img src=\images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
		echo "</table>\n";
	}

	function addSuccessful($user, $reserve, $ci, $selected_instr, $msg=null)
	{
		global $g_reservesViewer, $g_permission;

		echo "<table width=\"60%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr><td width=\"140%\"><img src=\images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>\n";
		echo "	<tr>\n";
		echo "		<td align=\"left\" valign=\"top\">\n";
		echo "			<p class=\"successText\">Item was successfully added to ". $ci->course->displayCourseNo() . " " . $ci->course->getName() .".</p>\n";

		if (isset($msg) && !is_null($msg))
			echo "			<p class=\"successText\">$msg</p>\n";

	  
		echo "          <p>&gt;&gt;<a href=\"index.php?cmd=editClass&ci=".$ci->getCourseInstanceID()."\"> Go to class</a></p>\n";
		echo "			<p>&gt;&gt;<a href=\"index.php?cmd=addPhysicalItem&ci=".$ci->getCourseInstanceID()."&selected_instr=$selected_instr\"> Add another physical item to this class.</a><br>\n";
		echo "			&gt;&gt;<a href=\"index.php?cmd=addDigitalItem&ci=".$ci->getCourseInstanceID()."&selected_instr=$selected_instr\"> Add another digital item to this class.</a><br>\n";
		
		echo "			&gt;&gt; <a href=\"index.php?cmd=addReserve\">Return to Add a Reserve home</a></p>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr><td align=\"center\"></td></tr>\n";
		
		if ($reserve) {
			
			$reserve->getItem();
    	
    	$viewReserveURL = "reservesViewer.php?reserve=" . $reserve->getReserveID();
			if ($reserve->item->isPhysicalItem()) {
				$reserve->item->getPhysicalCopy();
				if ($reserve->item->localControlKey)
					$viewReserveURL = $g_reservesViewer . $reserve->item->getLocalControlKey();
				else
					$viewReserveURL = null;
			}

    	$itemIcon = $reserve->item->getItemIcon();
    	$title = $reserve->item->getTitle();
			$author = $reserve->item->getAuthor();
			$url = $reserve->item->getURL();
			$performer = $reserve->item->getPerformer();
			$volTitle = $reserve->item->getVolumeTitle();
			$volEdition = $reserve->item->getVolumeEdition();
			$pagesTimes = $reserve->item->getPagesTimes();
			$source = $reserve->item->getSource();
			$contentNotes = $reserve->item->getContentNotes();
			$itemNotes = $reserve->item->getNotes();
			$instructorNotes = $reserve->getNotes();
			
    	echo "				<tr><td>&nbsp;</td></tr>\n";
    	echo "				<tr><td><strong>Review item:</strong></td></tr>\n";
    	echo "				<tr><td>&nbsp;</td></tr>\n";
    	echo '<tr><td><table border="0" cellspacing="0" cellpadding="0">';
    	echo '<tr align="left" valign="middle" class="oddRow">';
    	echo '	<td width="5%" valign="top"><img src="'.$itemIcon.'" width="24" height="20"></td>';
    	if ($viewReserveURL)
    		echo '	<td width="78%"><a href="'.$viewReserveURL.'" class="itemTitle" target="_blank">'.$title.'</a>';
    	else
    		echo '	<td width="78%"><span class="itemTitle">'.$title.'</span>';
    	if ($author)
    		echo '		<br> <span class="itemAuthor">'.$author.'</span>';
    	if ($performer)
	    	echo '<br><span class="itemMetaPre">Performed by:</span>&nbsp;<span class="itemMeta"> '.$performer.'</span>';
	    if ($volTitle)
				echo '<br><span class="itemMetaPre">From:</span>&nbsp;<span class="itemMeta"> '.$volTitle.'</span>';
	    if ($volEdition)
	    	echo '<br><span class="itemMetaPre">Volume/Edition:</span>&nbsp;<span class="itemMeta"> '.$volEdition.'</span>';
	    if ($pagesTimes)
	    	echo '<br><span class="itemMetaPre">Pages/Time:</span>&nbsp;<span class="itemMeta"> '.$pagesTimes.'</span>';
	    if ($source)
	    	echo '<br><span class="itemMetaPre">Source/Year:</span>&nbsp;<span class="itemMeta"> '.$source.'</span>';

    	if ($contentNotes)
	    {
	        echo '<br><span class="noteType">Content Note:</span>&nbsp;<span class="noteText">'.$contentNotes.'</span>';
	    }
    	if ($itemNotes) 
	    {
	    
	    	for ($n=0; $n<count($itemNotes); $n++)
	    	{
            	$type = strtolower($itemNotes[$n]->getType());
            	//if ($type == "content") {
            	if ($user->dfltRole >= $g_permission['staff'] || $type == "content") {
	            	echo '<br><span class="noteType">'.ucfirst($type).' Note:</span>&nbsp;<span class="noteText">'.$itemNotes[$n]->getText().'</span>';
            	}
	        }
	    }
	    if ($instructorNotes)
	    {
	    
	    	for ($n=0; $n<count($instructorNotes); $n++)
	        {
	        	echo '<br><span class="noteType">Instructor Note:</span>&nbsp;<span class="noteText">'.$instructorNotes[$n]->getText().'</span>';
	        }
	    }
    	echo '	</td>';
    	echo '	<td width="17%" valign="top">[ <a href="index.php?cmd=editReserve&reserveID='.$reserve->getReserveID().'" class="editlinks">edit item</a> ]</td>';
    	echo ' 	<td width="0%">&nbsp;</td>';
    	echo '</tr>';
    	echo '</table></td></tr>';
		}

		echo "	<tr><td><img src=\images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
		echo "</table>\n";
	}

	function processSuccessful($ci, $msg=null)
	{
		echo "<table width=\"60%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr><td width=\"140%\"><img src=\images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>\n";
		echo "	<tr>\n";
		echo "		<td align=\"left\" valign=\"top\">\n";
		echo "			<p class=\"successText\">Item was successfully processed for ". $ci->course->displayCourseNo() . " " . $ci->course->getName() .".</p>\n";

		if (isset($msg) && !is_null($msg))
			echo "			<p class=\"successText\">$msg</p>\n";

		echo "          <p>&gt;&gt;<a href=\"index.php?cmd=editClass&ci=".$ci->getCourseInstanceID()."\"> Go to class</a></p>\n";

		echo "			&gt;&gt; <a href=\"index.php?cmd=displayRequest\">Return to the Requests Queue</a></p>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr><td align=\"center\"></td></tr>\n";
		echo "	<tr><td><img src=\images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
		echo "</table>\n";
	}
}
?>
