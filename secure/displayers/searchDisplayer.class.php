<?
/*******************************************************************************
Reserves Direct 2.0

Copyright (c) 2004 Emory University General Libraries

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be included
in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

Created by Kathy A. Washington (kawashi@emory.edu)

Reserves Direct 2.0 is located at:
http://coursecontrol.sourceforge.net/

*******************************************************************************/
require_once("secure/common.inc.php");
require_once("secure/managers/lookupManager.class.php");

class searchDisplayer 
{
	/**
	* @return void
	* @desc Display staff search screen
	*/
	function searchForDocuments($cmd, $hidden_fields)
	{
		echo "<form action='index.php' method='POST'>\n";
		echo "<input type='hidden' name='cmd' value='doSearch'>\n";
		
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
		
		echo "<table width='80%' border='0' cellspacing='0' cellpadding='0' align='center'>\n";
		echo "	<tr>\n";
		echo "		<td width='140%' align='left' valign='top'><img src='/images/spacer.gif' width='1' height='5'>&nbsp;</td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td>\n";
		echo "			<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
		echo "				<tr><td width='35%' height='19' class='headingCell1'>SEARCH BY:</td><td>&nbsp;</td></tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "	<td align='left' valign='top'>\n";
		echo "		<table width='80%' border='0' cellpadding='3' cellspacing='0' class='borders'>\n";
		
		$numRows = 3;
		for($i=0;$i<$numRows;$i++)
		{
			echo "			<tr valign='middle' align='left'>\n";
			echo "				<td align='left' bgcolor='#CCCCCC'>\n";
			echo "					<select name='search[$i][field]'>\n";
			echo "						<option value='title' selected>Document Title</option>\n";
			echo "						<option value='author'>Author/Composer</option>\n";
			echo "						<option value='performer'>Performer</option>\n";
			echo "						<option value='volume_title'>Book/Journal/Work Title</option>\n";
			echo "						<option value='volume_edition'>Volume/Edition</option>\n";
			echo "						<option value='pages_times'>Pages/Time</option>\n";
			echo "						<option value='url'>URL</option>\n";
			echo "						<option value='source'>Source/Year</option>\n";
			echo "						<option value='n.note'>Content Notes</option>\n";
			echo "					</select>\n";
			echo "				</td>\n";
			echo "				<td align='left' bgcolor='#CCCCCC'>\n";
			echo "					<select name='search[$i][test]'>\n";
			echo "						<option value='LIKE' selected>contains</option>\n";
			echo "						<option value='='>equals</option>\n";
			echo "						<option value='<>'>does not contain</option>\n";
			echo "					</select>\n";
			echo "				</td>\n";
			echo "				<td align='left' bgcolor='#CCCCCC'><input name='search[$i][term]' type='text' size='40'></td>\n";
			echo "			</tr>\n";
			
			if ($i < ($numRows -1))
			{			
				echo "			<tr valign='middle'>\n";
				echo "				<td align='center' bgcolor='#CCCCCC'>\n";
				echo "					<select name='search[$i][conjunct]'>\n";
				echo "						<option value='AND' selected>and</option>\n";
				echo "						<option value='OR'>or</option>\n";
				echo "					</select>\n";
				echo "				</td>\n";
				echo "				<td align='left' bgcolor='#CCCCCC'>&nbsp;</td>\n";
				echo "				<td align='center' bgcolor='#CCCCCC'>&nbsp;</td>\n";
				echo "			</tr>\n";
			}
		}
			
		echo "			<tr align='left' valign='top'>\n";
		echo "				<td align='center' valign='middle' bgcolor='#CCCCCC' class='strong'>Item Type</td>\n";
		echo "				<td bgcolor='#CCCCCC'>\n";
		echo "					<select name='item[test]'>\n";
		echo "							<option value='=' selected>equals</option>\n";
		echo "							<option value='NOT LIKE'>does not contain</option>\n";
		echo "					</select></td>\n";
		echo "					<td bgcolor='#CCCCCC'>\n";
		echo "						<select name='item[term]'>\n";
		echo "							<option value=''>ALL ITEMS</option>\n";
		echo "							<option value='MONOGRAPH'>MONOGRAPH</option>\n";
		echo "							<option value='MULTIMEDIA'>MULTIMEDIA</option>\n";
		echo "							<option value='ELECTRONIC'>ELECTRONIC</option>\n";
		echo "						</select>\n";
		echo "					</td>\n";
		echo "				</tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr><td>&nbsp;</td></tr>\n";
		echo "	<tr>\n";
		echo "		<td>\n";
		echo "			<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
		echo "				<tr><td width='35%' height='19' class='headingCell1'>LIMIT BY:</td><td>&nbsp;</td></tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		
		
		echo "	<tr>\n";
		echo "		<td align='left' valign='top'>\n";
		
		echo "			<table width='80%' border='0' cellpadding='3' cellspacing='0' class='borders'>\n";
		
		$numRows = 2;
		for($i=0;$i<$numRows;$i++)
		{
			echo "				<tr valign='middle'>\n";
			echo "					<td align='left'  bgcolor='#CCCCCC'>\n";
			echo "						<select name='limit[$i][field]'>\n";
			echo "							<option value='instructor'>Instructor</option>\n";
			echo "							<option value='department'>Department</option>\n";
			echo "							<option value='class_name' selected>Class Name</option>\n";
			echo "						</select>\n";
			echo "					</td>\n";
			echo "					<td align='left' bgcolor='#CCCCCC'>\n";
			echo "						<select name='limit[$i][test]'>\n";
			echo "							<option value='LIKE' selected>contains</option>\n";
			echo "							<option value='='>equals</option>\n";
			echo "							<option value='NOT LIKE'>does not contain</option>\n";
			echo "						</select>\n";
			echo "					</td>\n";
			echo "					<td align='center' bgcolor='#CCCCCC'><input name='limit[$i][term]' type='text' size='40'></td>\n";
			echo "				</tr>\n";

			if ($i < ($numRows -1))
			{				
				echo "				<tr valign='middle'> \n";
				echo "					<td align='center' bgcolor='#CCCCCC'>\n";
				echo "						<select name='limit[$i][conjunct]'>\n";
				echo "							<option value='AND' selected>and</option>\n";
				echo "							<option value='OR'>or</option>\n";
				echo "						</select>\n";
				echo "					</td>\n";
				echo "					<td align='left' bgcolor='#CCCCCC' colspan='2'>&nbsp;</td>\n";
				echo "				</tr>\n";
			}
				
		}
		
		echo "				<tr><td colspan='3' bgcolor='#CCCCCC' align='center'>&nbsp;</td></tr>\n";
		
		echo "				<tr><td colspan='3' bgcolor='#CCCCCC' align='center'><input type='submit' name='Search' value='Search'>&nbsp;&nbsp;<input type='reset' name='Reset' value='Clear'></td></tr>\n";
		
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr><td><img src='/images/spacer.gif' width='1' height='15'></td></tr>\n";
		echo "</table>\n";

		echo "</form>\n";
	}
	
	/**
	 * Display Staff search Results
	 *
	 * @param string $cmd - current command
	 * @param array reserveItems $itemArray
	 * @param array $hidden_fields - system parameters hidden from users
	 */
	function searchResults($cmd, $itemArray, $hidden_fields, $displayQry)
	{
		global $g_reservesViewer;
		
		echo "<script language=\"JavaScript\">\n";
		echo "	function sort(sortBy)
				{
					document.forms[0].cmd.value  = 'doSearch';
					document.forms[0].sort.value = sortBy;
					
					document.forms[0].submit();
				}\n";
		echo "</script>\n";
		
		echo "<form action=\"index.php\" method=\"POST\">\n";
		
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
		
		echo "<table width=\"80%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr><td width=\"140%\" colspan=\"2\"><img src=\"/images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>\n";
		echo "	<tr>\n";
		echo "		<td width=\"140%\" height=\"18\" align=\"left\" valign=\"top\" >\n";
		echo "			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\">\n";
		echo "				<tr>\n";
		echo "					<td colspan=\"2\" align=\"left\" valign=\"top\" class=\"strong\">\n";
		
		if ($displayQry != '') echo "						Your search for \"$displayQry\" ";
		else echo "						Your search ";			
		echo "						returned <font color=\"#CC0000\">" . count($itemArray) . "</font> results.\n";
		echo "					</td>\n";
		echo "				</tr>\n";

		echo "				<tr>\n";
		echo "					<td width=\"51%\" align=\"left\" valign=\"top\">\n";
		echo "						Sort by: [ <a href=\"javascript:sort('author');\" class=\"editlinks\">author</a> ]\n";
		echo "						[<a href=\"javascript:sort('title');\" class=\"editlinks\">title</a> ]\n";
		echo "					</td>\n";
		echo "					<td width=\"49%\" align=\"right\" valign=\"top\">\n";
		echo "						[ <a href=\"index.php?cmd=searchTab\" class=\"editlinks\">New Search</a> ] \n";
		echo "						[ <a href=\"index.php\" class=\"editlinks\">Cancel Search</a> ] \n";
		echo "					</td>\n";
		echo "				</tr>\n";
		
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
		echo "	<tr>\n";
		echo "		<td colspan=\"2\">\n";
		echo "			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		echo "				<tr align=\"left\" valign=\"top\">\n";
		echo "					<td class=\"headingCell1\" align=\"center\">SEARCH RESULTS</td>\n";
		echo "					<td width=\"75%\" align=\"right\">\n";
		//echo "						<a href=\"../addReserve/link\">&lt;&lt; Previous</a> | \n";
		//echo "						<a href=\"../addReserve/link\">Next &gt;&gt;</a>\n";
		echo "					</td>\n";
		echo "				</tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td colspan=\"2\" align=\"left\" valign=\"top\" class=\"borders\">\n";
		echo "	 		<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" class=\"displayList\">\n";
		echo "	 			<tr align=\"left\" valign=\"middle\">\n";
		echo "					<td valign=\"top\" bgcolor=\"#FFFFFF\" class=\"headingCell1\">&nbsp;</td>\n";
		echo "					<td bgcolor=\"#FFFFFF\" class=\"headingCell1\">&nbsp;</td>\n";
		echo "					<td class=\"headingCell1\">Preview</td>\n";
		echo "					<td class=\"headingCell1\">Edit</td>\n";
		echo "					<td class=\"headingCell1\">\n";
		echo "						Select <br><input type=\"checkbox\" name=\"chkAll\" onClick=\"checkAll(this.form, this.checked);\"";
		echo "					</td>\n";
		echo "				</tr>\n";

		if (!is_null($itemArray))
		{
			for($i=0;$i<count($itemArray);$i++)
			{
				$rowClass = ($i % 2) ? "evenRow" : "oddRow";	
				$item = $itemArray[$i];	

				//marks items as 'personal' if they are such
				$personal_label = $item->isPersonalCopy() ? '(Personal) ' : '' ;
				
				$previewItemURL = ($item->isPhysicalItem()) ?	$g_reservesViewer . $item->getLocalControlKey() : $item->getURL();
				
				echo "				<tr align=\"left\" valign=\"middle\">\n";
				echo "					<td width=\"4%\" valign=\"top\" class=\"$rowClass\"><img src=\"". $item->getitemIcon() ."\" width=\"24\" height=\"20\"></td>\n";
				echo "					<td width=\"72%\" valign=\"top\" class=\"$rowClass\">\n";
				echo "						<span class=\"strong\">" . $personal_label . $item->getTitle() ."</span>. <br> ". $item->getAuthor() ."\n";
				echo "					</td>\n";
				echo "					<td width=\"8%\" align=\"center\" valign=\"middle\" class=\"$rowClass\" class=\"borders\">\n";
				echo "						<a href=\"$previewItemURL\" target=\"preview\">preview</a>\n";
				echo "					</td>\n";
				echo "					<td width=\"7%\" align=\"center\" valign=\"middle\" class=\"$rowClass\" class=\"borders\">\n";
				echo "						<a href=\"index.php?cmd=editItem&itemID=" . $item->getItemID() . "&search=". urlencode($hidden_fields['search']) ."\">edit</a>\n";
				//<!--Clicking \"edit\" link should open up edit item screen with a link on it to \"Return to Search Results\" at bottom instead of the usual \"Return to Class\" link. -->
				echo "					</td>\n";
				echo "					<td width=\"9%\" align=\"center\" valign=\"middle\" class=\"$rowClass\" class=\"borders\">\n";
				echo "						<input type=\"checkbox\" name=\"itemSelect[]\" value=\"". $item->getItemID() ."\">\n";
				echo "					</td>\n";
				echo "				</tr>\n";
			}				
		} else 
			echo "				<tr align=\"left\" valign=\"middle\"><td>No Results Found</td></tr>\n";
		
		echo "				<tr align=\"left\" valign=\"middle\" class=\"headingCell1\">\n";
		echo "					<td valign=\"top\">&nbsp;</td>\n";
		echo "					<td colspan=\"4\" align=\"right\">\n";
		//<!--Form either deletes all selected items or adds all checked items to a class. If deleting items, go to confirmation screen search/staff-search-docs-delete.html If adding items to class go to search/staff-search-docs-add.html-->
		echo "						<select name=\"select\">\n";
		echo "							<option>For all selected items:\n";
		echo "							<option selected>Add items to a class\n";
		echo "						</select>\n";
		echo "						<input type=\"submit\" name=\"Submit2\" value=\"Submit\">\n";
		echo "					</td>\n";
		echo "				</tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td colspan=\"2\" align=\"right\" valign=\"top\">\n";		
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr><td colspan=\"2\"><img src=\"/images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
		echo "</table>\n";
		echo "</form>\n";
		
	}

	function addResultsToClass($cmd, $nextCmd, $u, $selectedResults, $request, $activate_date, $hidden_fields)
	{
		
		echo "<form action=\"index.php\" method=\"POST\">\n";
		
		echo "<input type=\"hidden\" name=\"cmd\" value=\"$cmd\">\n";
		echo "<input type=\"hidden\" name=\"removeItem\" value=\"\">\n";
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
		
		
		
		
		$selectClassMgr = new lookupManager('','lookupClass', $u, $request);
		$selectClassMgr->display();
		
		//split activation date for display in boxes
		list($a_y, $a_m, $a_d) = split("-", $activate_date);
		
		//set active/inactive checkbox
		if (!isset($request['currentStatus']) || $request['currentStatus'] == 'ACTIVE')
		{
			$a_checked = 'checked';
			$i_checked = '';
		} else {
			$a_checked = '';
			$i_checked = 'checked';
		}
		
		echo "<table width=\"90%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\">";


		echo "	<tr>\n";
		echo "		<td>\n";
		echo "			<span class=\"strong\">Status:</span>\n";
		echo "			<strong>Make All</strong>\n";
		echo "			<input name=\"currentStatus\" value=\"ACTIVE\" type=\"radio\" $a_checked>Active\n";
		echo "			<input name=\"currentStatus\" value=\"INACTIVE\" type=\"radio\" $i_checked>Inactive\n";
		echo "		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
		echo "			<span class=\"strong\">Hide Until:</span>\n";
		echo "			<input name=\"hide_month\" size=\"2\" maxlength=\"2\" value=\"$a_m\" type=\"text\">/\n";
		echo "			<input name=\"hide_day\" size=\"2\" maxlength=\"2\" value=\"$a_d\" type=\"text\">/\n";
		echo "			<input name=\"hide_year\" size=\"4\" maxlength=\"4\" value=\"$a_y\" type=\"text\">mm/dd/yyyy\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		
		
		
		echo "	<tr><td>&nbsp;</td></tr>\n";

		echo "	<tr>\n";
		echo "		<td align=\"left\" valign=\"top\">\n";
		echo "			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		echo "				<tr align=\"left\" valign=\"top\">\n";
		echo "					<td width=\"35%\" class=\"headingCell1\" align=\"center\">CURRENTLY SELECTED ITEMS</td>\n";
		echo "					<td width=\"75%\">&nbsp;</td>\n";
		echo "				</tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";

		echo "	<tr>\n";
		echo "		<td align=\"left\" valign=\"top\" class=\"borders\">\n";
		echo "			<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" class=\"displayList\">\n";
		echo "				<tr align=\"left\" valign=\"middle\">\n";
		echo "					<td valign=\"top\" bgcolor=\"#FFFFFF\" class=\"headingCell1\">&nbsp;</td>\n";
		echo "					<td bgcolor=\"#FFFFFF\" class=\"headingCell1\">&nbsp;</td>\n";
		echo "					<td class=\"headingCell1\">&nbsp;</td>\n";
		echo "				</tr>\n";
		
		if (count($selectedResults) < 1)
		{
			echo "				<tr align=\"left\" valign=\"middle\" class=\"oddRow\">\n";
			echo "					<td width=\"4%\">&nbsp;</td>\n";
			echo "					<td valign=\"top\" colspan=\"2\">Nothing Selected.  Please search again.</td>\n";
			echo "				</tr>\n";	
		}
		
		for($i=0;$i<count($selectedResults);$i++)
		{	
			$item = $selectedResults[$i];
				
			$rowClass = ($i % 2) ? "evenRow" : "oddRow";
			
			echo "<input type=\"hidden\" name=\"itemSelect[]\" value=\"" . $item->getItemID() . "\">\n";
			if ($item->getItemGroup() <> 'ELECTRONIC')
				echo "<input type=\"hidden\" name=\"requestItem[]\" value=\"" . $item->getItemID() . "\">\n";
			else
				echo "<input type=\"hidden\" name=\"reserveItem[]\" value=\"" . $item->getItemID() . "\">\n";
			
			echo "				<tr align=\"left\" valign=\"middle\" class=\"$rowClass\">\n";
			echo "					<td width=\"4%\" valign=\"top\"><img src=\"" . $item->getItemIcon() . "\" alt=\"text\" width=\"24\" height=\"20\"></td>\n";
			echo "					<td width=\"77%\" valign=\"top\">\n";
			echo "						<span class=\"strong\">". $item->getTitle() ."<br>". $item->getAuthor() ."\n";
			echo "					</td>\n";
			echo "					<td width=\"19%\" align=\"center\" valign=\"middle\" class=\"borders\">\n";
			echo "						<input type=\"submit\" value=\"Remove This Item\" onClick=\"this.form.removeItem.value=".$item->getItemID() . ";\">\n";
			echo "					</td>\n";
			echo "				</tr>\n";
		}
		
		echo "				<tr align=\"left\" valign=\"middle\" class=\"headingCell1\"><td colspan=\"3\" valign=\"top\">&nbsp;</td></tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr><td align=\"right\" valign=\"top\">&nbsp;</td></tr>\n";
		
		
		$submit_disabled = (isset($request['ci'])) ? '' : 'DISABLED';
		
		echo "	<tr>\n";
		echo "		<td align=\"center\">\n";
		echo "			<img src=\"/images/spacer.gif\" width=\"1\" height=\"15\">\n";
		echo "			<input type=\"submit\" name=\"submitButton\" value=\"Add Items to Class\" $submit_disabled>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		
		echo "</table>\n";

		echo "</form>\n";
	}
	
	
	function addComplete($cmd, $ci, $msg)
	{
		echo "<table width=\"60%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr><td width=\"140%\"><img src=\images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>\n";
		echo "	<tr>\n";
		echo "		<td align=\"left\" valign=\"top\">\n";

		if (isset($msg) && !is_null($msg))
			echo "			<p class=\"successText\">$msg</p>\n";

		echo "          <p>&gt;&gt;<a href=\"index.php?cmd=editClass&ci=".$ci->getCourseInstanceID()."\"> Go to class</a></p>\n";
		echo "			<p>&gt;&gt;<a href=\"index.php?cmd=searchTab\"> Search Again</a><br>\n";		
		echo "			&gt;&gt; <a href=\"index.php?cmd=addReserve\">Return to Add a Reserve home</a></p>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr><td align=\"center\"></td></tr>\n";
		echo "	<tr><td><img src=\images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
		echo "</table>\n";		
	}
}

?>
