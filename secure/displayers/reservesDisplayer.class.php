<?
/*******************************************************************************
reservesDisplayer.class.php


Created by Kathy Washington (kawashi@emory.edu)

This file is part of ReservesDirect

Copyright (c) 2004-2005 Emory University, Atlanta, Georgia.

ReservesDirect is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

ReservesDirect is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with ReservesDirect; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

ReservesDirect is located at:
http://www.reservesdirect.org/

*******************************************************************************/
require_once("secure/common.inc.php");
require_once('secure/displayers/noteDisplayer.class.php');
require_once('secure/classes/tree.class.php');


class reservesDisplayer extends noteDisplayer {

	function displayReserves($cmd, &$ci, &$tree_walker, $reserve_count, &$hidden_reserves=null, $preview_only=false) {
        
		if(!($ci->course instanceof course)) {
			$ci->getPrimaryCourse();
		}
        
        // announce rss feed to capable browsers
        echo "<link rel=\"alternate\" title=\"{$ci->course->department->name} {$ci->course->courseNo} {$ci->term} {$ci->year}\" href=\"rss.php?ci={$ci->courseInstanceID}\" type=\"application/rss+xml\"/>\n";
		
		$exit_class_link = $preview_only ? '<a href="javascript:window.close();">Close Window</a>' : '<a href="index.php">Exit class</a>' ;		
?>

		<div>		
			<div style="text-align:right;"><strong><?=$exit_class_link?></strong></div>	
			<div class="courseTitle"><?=$ci->course->displayCourseNo() . " " . $ci->course->getName()?></div>			
			<div class="courseHeaders"><span class="label"><?=$ci->displayTerm()?></span></div>			
			<div class="courseHeaders">
				<span class="label">Instructor(s):</span>
							
<?php 
		for($i=0;$i<count($ci->instructorList);$i++) {
			if ($i!=0) echo ',&nbsp;';
			echo '<a href="mailto:'.$ci->instructorList[$i]->getEmail().'">'.$ci->instructorList[$i]->getFirstName().'&nbsp;'.$ci->instructorList[$i]->getLastName().'</a>';
		}
?>		
	
			</div>
			<div class="courseHeaders">
				<span class="label">Crosslstings:</span>	
						
<?php
		if (count($ci->crossListings)==0) {
			echo 'None';
		}
		else {
			for ($i=0; $i<count($ci->crossListings); $i++) {
				if ($i>0) echo',&nbsp;';
				echo $ci->crossListings[$i]->displayCourseNo();
			}
		}
?>

			</div>
			<p />
			<small><strong>Helper Applications:</strong> <a href="http://www.adobe.com/products/acrobat/readstep2.html" target="_new">Adobe Acrobat</a>, <a href="http://www.real.com" target="_new">RealPlayer</a>, <a href="http://www.apple.com/quicktime/download/" target="_new">QuickTime</a>, <a href="http://office.microsoft.com/Assistance/9798/viewerscvt.aspx" target="_new">Microsoft Word</a></small>		
		</div>
		
				
		<form method="post" name="editReserves" action="index.php">
		
			<input type="hidden" name="cmd" value="<?=$cmd?>" />
			<input type="hidden" name="ci" value="<?=$ci->getCourseInstanceID()?>" />
			<input type="hidden" name="hideSelected" value="" />
			<input type="hidden" name="showAll" value="" />

		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
			<tr align="left" valign="middle">
				<td class="headingCell1">COURSE MATERIALS</td>
				<td width="75%" align="right">
<?php	if(!$preview_only): ?>
					<input type="submit" name="hideSelected" value="Hide Selected" />
					<input type="submit" name="showAll" value="Show All" />
<?php	endif; ?>
				</td>
			</tr>
			<tr valign="middle">
				<td class="headingCell1" align="center" colspan="2">
					<?php echo $reserve_count; ?> Item(s) On Reserve
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<ul style="list-style:none; padding-left:0px; margin:0px;">
			
<?php
		//begin displaying individual reserves
		//loop
		$prev_depth = 0;
		foreach($tree_walker as $leaf) {
			//close list tags if backing out of a sublist
			if($prev_depth > $tree_walker->getDepth()) {
				echo str_repeat('</ul></li>', ($prev_depth-$tree_walker->getDepth()));
			}
			
		
			$reserve = new reserve($leaf->getID());	//init a reserve object

			//is this item hidden?
			$reserve->hidden = in_array($leaf->getID(), $hidden_reserves) ?	true : false;
			
			$rowStyle = ($rowStyle=='oddRow') ? 'evenRow' : 'oddRow';	//set the style

			//display the info
			echo '<li>';
			if($preview_only) {
				self::displayReserveRowPreview($reserve, 'class="'.$rowStyle.'"');
			}
			else {
				self::displayReserveRowView($reserve, 'class="'.$rowStyle.'"');
			}
			
			//start sublist or close list-item?
			echo ($leaf->hasChildren()) ? '<ul style="list-style:none;">' : '</li>';
			
			$prev_depth = $tree_walker->getDepth();
		}
		echo str_repeat('</ul></li>', ($prev_depth));	//close all lists
?>

					</ul>
				</td>
			</tr>
			<tr valign="middle">
				<td class="headingCell1" align="center" colspan="2">
					&nbsp;
				</td>
			</tr>
		</table>
		</form>
		
		<p />
		<div style="margin-left:5%; margin-right:5%; text-align:right;"><strong><?=$exit_class_link?></strong></div>

<?php
	}
	
	function displayStaffAddReserve($request=null)
	{
		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr><td width=\"100%\"><img src=\images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>\n";
		echo "	<tr>\n";
		echo "		<td align=\"center\" valign=\"top\">\n";
		//echo "			<table width=\"40%\" border=\"0\" cellspacing=\"0\" cellpadding=\"8\">\n";
		echo '			<table width="40%" border="0" cellspacing="0" cellpadding="8" class="borders">';
		echo "				<tr class=\"headingCell1\"><td width=\"40%\">Add / Process Materials</td></tr>\n";
		echo "				<tr align=\"left\" valign=\"top\">\n";
		echo "					<td width=\"40%\">\n";
		echo "						<ul>\n";
		echo "							<li><a href=\"index.php?cmd=displayRequest\" align=\"center\">Process Requests</a></li>\n";
		if (!isset($request['ci']) || (!isset($request['selected_instr'])))
			echo "							<li><a href=\"index.php?cmd=addDigitalItem\" align=\"center\">Add an Electronic Item</a></li>\n";
		else if ($request['ci'] && $request['selected_instr'])
			echo "							<li><a href=\"index.php?cmd=addDigitalItem&ci=".$request['ci']."&selected_instr=".$request['selected_instr']."\" align=\"center\">Add an Electronic Item</a></li>\n";
		if (!isset($request['ci']) || !isset($request['selected_instr']))
			echo "							<li><a href=\"index.php?cmd=addPhysicalItem\">Add a Physical Item</a></li>\n";
		else if ($request['ci'] && $request['selected_instr']) {
			echo "							<li><a href=\"index.php?cmd=addPhysicalItem&ci=".$request['ci']."&selected_instr=".$request['selected_instr']."\">Add a Physical Item</a></li>\n";
			echo "							<li><a href=\"index.php?cmd=faxReserve&ci=".$request['ci']."&selected_instr=".$request['selected_instr']."\">Fax a Document</a></li>\n";
			echo "							<li><a href=\"index.php?cmd=searchScreen&ci=".$request['ci']."\">Search for the Item</a></li>\n";
		}
		echo "							<!--<li><a href=\"index.php?cmd=physicalItemXListing\">Physical Item Cross-listings </a>--><!--Goes to staff-mngClass-phys-XList1.html --></li>\n";
		echo "						</ul>\n";
		echo "					</td>\n";
		echo "				</tr>\n";
		//echo "<tr><td>&nbsp;</td></tr>";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr><td>&nbsp;</td></tr>\n";
		echo "</table>\n";		
	}
	
	
	function displaySelectInstructor($user, $page, $cmd)
	{
		$subordinates = common_getUsers('instructor');

		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tbody>\n";
		echo "		<tr><td width=\"100%\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";
		echo "		<tr>\n";
        echo "			<td align=\"left\" valign=\"top\">\n";
        echo "				<table border=\"0\" align=\"center\" cellpadding=\"10\" cellspacing=\"0\">\n";
		echo "					<tr align=\"left\" valign=\"top\" class=\"headingCell1\">\n";
        echo "						<td width=\"50%\">Search by Instructor </td><td width=\"50%\">Search by Department</td>\n";
        echo "					</tr>\n";

        echo "					<tr>\n";
        echo "						<td width=\"50%\" align=\"left\" valign=\"top\" class=\"borders\" align=\"center\" NOWRAP>\n";

        echo "							<form method=\"post\" action=\"index.php\" name=\"frmReserveItem\">\n";
        //if (!is_null($courseInstance)) echo "					<input type=\"hidden\" name=\"ci\" value=\"$courseInstance\">\n";
    	echo "								<input type=\"hidden\" name=\"page\" value=\"$page\">\n";
		echo "								<input type=\"hidden\" name=\"cmd\" value=\"$cmd\">\n";
		echo "								<input type=\"hidden\" name=\"u\" value=\"".$user->getUserID()."\">\n";
		echo "								<input type=\"submit\" name=\"Submit2\" value=\"Admin Your Classes\">\n";
		echo "							</form>\n";
        echo "							<br>\n";
        echo "							<form method=\"post\" action=\"index.php\" name=\"frmReserveItem\">\n";
    	//if (!is_null($courseInstance)) echo "					<input type=\"hidden\" name=\"ci\" value=\"$courseInstance\">\n";
        echo "								<input type=\"hidden\" name=\"page\" value=\"$page\">\n";
		echo "								<input type=\"hidden\" name=\"cmd\" value=\"$cmd\">\n";
		echo "								<select name=\"u\">\n";
	    echo "									<option value=\"--\" selected>Choose an Instructor\n";
        foreach($subordinates as $subordinate)
		{
			echo "									<option value=\"" . $subordinate['user_id'] . "\">" . $subordinate['full_name'] . "</option>\n";
		}

        echo "								</select>\n";
        //echo "								<br>\n";
        //echo "								<br>\n";
        echo "								<input type=\"submit\" name=\"Submit2\" value=\"Select Instructor\">\n";
        echo "							</form>\n";
        echo "						</td>\n";
        echo "						<td width=\"50%\" align=\"left\" valign=\"top\" class=\"borders\" align=\"center\" NOWRAP>&nbsp;\n";
        echo "						</td>\n";
        echo "					</tr>\n";
        echo "				</table>\n";
		echo "			</td>\n";
		echo "		</tr>\n";
		echo "	</tbody>\n";
		echo "</table>\n";

	}

	/**
 * @return void
 * @param int $ci -- user selected course_instance selected for DisplaySelectClass
 * @desc Allows user to determine how they would like to add Reserves
 * 		expected next steps
 *			searchItems::searchScreen
 *			searchItems::uploadDocument
 *			searchItems::addURL
 *			searchItems::faxReserve
*/
function displaySearchItemMenu($ci)
{
	global $g_copyrightNoticeURL;
		
	echo "<div style=\"border:1px solid #333333; padding:8px 8px 8px 40px; width:40%;float:left;\">\n"
	.	 "					<p><strong>How would you like to add an item to your class?</strong></p>\n"
	.	 "						<ul><li><a href=\"index.php?cmd=searchScreen&ci=$ci\">Search for the Item</a></li>\n"
	.	 "							<li><a href=\"index.php?cmd=uploadDocument&ci=$ci\">Upload a Document</a></li>\n"
	.	 "							<li><a href=\"index.php?cmd=addURL&ci=$ci\">Add a URL</a></li>\n"
	.	 "							<li><a href=\"index.php?cmd=faxReserve&ci=$ci\">Fax a Document</a></li>\n"
	.	 "						</ul>\n"
	.	 "</div>\n"
	.    "<div style=\"float:right; width:40%; margin-top:25px; padding:10px; text-align:center; border:1px solid #666666; background-color:#CCCCCC;\">\n"
	.	 "<strong><a href=\"$g_copyrightNoticeURL\" target=\"blank\">Copyright policy</a><strong> for adding materials to ReservesDirect.\n"
	.	 "</div>\n"
	;
}

	/**
	 * @return void
	 * @param string $page 	  -- the current page selector
	 * @param string $subpage -- subpage selector
	 * @param string $courseInstance -- user selected courseInstance
	 * @desc Allows user search for items
	 * 		expected next steps
	 *			open catalog in new window
	 *			searchItems::displaySearchResults
	*/
	function displaySearchScreen($page, $cmd, $ci=null)
	{
		global $g_catalogName, $g_libraryURL;

		$instructors = common_getUsers('instructor');

		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tbody>\n";
		echo "		<tr><td width=\"100%\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";
		echo "		<tr>\n";
        echo "			<td align=\"left\" valign=\"top\">\n";
        echo "				<table border=\"0\" align=\"center\" cellpadding=\"10\" cellspacing=\"0\">\n";
		echo "					<tr align=\"left\" valign=\"top\" class=\"headingCell1\">\n";
        echo "						<td width=\"50%\">Search for Archived Materials</td><td width=\"50%\">Search by Instructor</td>\n";
        echo "					</tr>\n";

        echo "					<tr>\n";
        //		SEARCH BY Author or Title
        echo "						<td width=\"50%\" class=\"borders\" align=\"center\">\n";
        echo "							<br>\n";
        echo "							<form action=\"index.php\" method=\"post\">\n";
        echo "							<input type=\"text\" name=\"query\" size=\"25\">\n";
        echo "							<input type=\"hidden\" name=\"page\" value=\"$page\">\n";
		echo "							<input type=\"hidden\" name=\"cmd\" value=\"$cmd\">\n";
        if (!is_null($ci)) echo "							<input type=\"hidden\" name=\"ci\" value=\"$ci\">\n";
        //echo "							<br>\n";
        echo "							<select name=\"field\">\n";
        echo "								<option value=\"Title\" selected>Title</option><option value=\"Author\">Author</option>\n";
        echo "							</select>\n";
        //echo "							<br>\n";
        //echo "							<br>\n";
        echo "							<input type=\"submit\" name=\"Submit\" value=\"Find Items\">\n";
        echo "							<br>\n";
        echo "							<br>\n";
        echo "							</form>\n";
        echo "						</td>\n";

        echo "						<td width=\"50%\" align=\"left\" valign=\"top\" class=\"borders\" align=\"center\" NOWRAP>\n";
        echo "							<form method=\"post\" action=\"index.php\" name=\"frmReserveItem\">\n";
		echo "								<input type=\"hidden\" name=\"page\" value=\"$page\">\n";
		echo "								<input type=\"hidden\" name=\"cmd\" value=\"$cmd\">\n";
		echo "								<input type=\"hidden\" name=\"searchType\" value=\"reserveItem\">\n";
		echo "								<input type=\"hidden\" name=\"field\" value=\"instructor\">\n";
		if (!is_null($ci)) echo "					<input type=\"hidden\" name=\"ci\" value=\"$ci\">\n";

        echo "								<br>\n";
		echo "								<select name=\"query\">\n";
	    echo "									<option value=\"--\" selected>Choose an Instructor\n";
        foreach($instructors as $instructor)
		{
			echo "									<option value=\"" . $instructor['user_id'] . "\">" . $instructor['full_name'] . "</option>\n";
		}

        echo "								</select>\n";
        //echo "								<br>\n";
        //echo "								<br>\n";
        echo "								<input type=\"submit\" name=\"Submit2\" value=\"Get Instructor's Reserves\">\n";
        echo "							</form>\n";
        echo "						</td>\n";
        echo "					</tr>\n";
        echo "					<tr align=\"left\" valign=\"top\">\n";
		echo "						<td colspan=\"2\" class=\"borders\" align=\"center\">\n";
        echo "							<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\">\n";
        echo "								<tr>\n";
        echo "									<td align=\"left\" valign=\"top\" align=\"center\">\n";
        echo "										You may also search the library's collection in <a href=\"$g_libraryURL\">$g_catalogName</a>.\n";
        echo "									</td>\n";
        echo "								</tr>\n";
        echo "							</table>\n";
        echo "						</td>\n";
        echo "					</tr>\n";
        echo "				</table>\n";
		echo "			</td>\n";
		echo "		</tr>\n";
		echo "	</tbody>\n";
		echo "</table>\n";

	}

	/**
	 * @return void
	 * @param string $page 	  -- the current page selector
	 * @param string $subpage -- subpage selector
	 * @param int $courseInstance -- user selected courseInstance
	 * @param string $query -- users search terms
	 * @desc display search resulting items
	 * 		expected next steps
	 *			open catalog in new window and search for query
	 *			dependent on page value
	*/
	function displaySearchResults($user, $search, $cmd, $ci=null, $hidden_requests=null, $hidden_reserves=null, $loan_periods=null)
	{
		global $g_reservesViewer, $g_permission;

		$showNextLink = false;
		$showPrevLink = false;
		$e = 20;

		if ($search->totalCount > ($search->first + 20)){
			$showNextLink = true;
			$fNext = $search->first + 20;
		}

		if ($search->first > 0){
			$showPrevLink = true;
			$fPrev = $search->first - 20;
		}

		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "		<tbody>\n";
		echo "			<tr><td width=\"100%\" colspan=\"2\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";
		echo "			<form name=\"searchResults\"method=\"post\" action=\"index.php\">\n";

		if (is_array($hidden_reserves) && !empty($hidden_reserves)){
			foreach($hidden_reserves as $r)
			{
				echo "<input type=\"hidden\" name=\"reserve[" . $r ."]\" value=\"" . $r ."\">\n";
			}
		}

		if (is_array($hidden_requests) && !empty($hidden_requests)){
			foreach($hidden_requests as $r)
			{
				echo "<input type=\"hidden\" name=\"request[" . $r ."]\" value=\"" . $r ."\">\n";
			}
		}

		echo "			<input type=\"hidden\" name=\"cmd\" value=\"$cmd\">\n";
		echo "			<input type=\"hidden\" name=\"ci\" value=\"$ci\">\n";

		echo "			<input type=\"hidden\" name=\"f\">\n";
		echo "			<input type=\"hidden\" name=\"e\" value=\"$e\">\n";
		echo "			<input type=\"hidden\" name=\"field\" value=\"$search->field\">\n";
		echo "			<input type=\"hidden\" name=\"query\" value=\"".urlencode($search->query)."\">\n";

		echo "			<tr>\n";
		echo "					<td align=\"left\">[ <a href=\"index.php?cmd=searchScreen&ci=$ci\" class=\"editlinks\">New Search</a> ] &nbsp;[ <a href=\"index.php?cmd=editClass&ci=$ci\" class=\"editlinks\">Cancel Search</a> ]</td>\n";
		echo "					<td align=\"right\"><input type=\"submit\" name=\"Submit\" value=\"Add Selected Materials\"></td>\n";
		echo "			</tr>\n";

	    if ($showNextLink || $showPrevLink) {
	   		echo "       	<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
        	echo "			<tr><td colspan=\"2\" align='right'>";
        	if ($showPrevLink) {
        		echo "<img src=\"images/getPrevious.gif\" onClick=\"javaScript:document.forms.searchResults.cmd.value='searchResults';document.forms.searchResults.f.value=".$fPrev.";document.forms.searchResults.submit();\">&nbsp;&nbsp;";
        	}
        	if ($showNextLink) {
        		echo "<img src=\"images/getNext.gif\" onClick=\"javaScript:document.forms.searchResults.cmd.value='searchResults';document.forms.searchResults.f.value=".$fNext.";document.forms.searchResults.submit();\">";
        	}
        	echo "</td></tr>\n";
        } else {
        	echo "<tr><td>&nbsp;</tr></td>\n";
        }


        echo "			<tr>\n";
        echo "				<td colspan=\"2\">\n";
        echo "					<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
        echo "					    <tr align=\"left\" valign=\"top\">\n";
        echo "					    	<td class=\"headingCell1\"><div align=\"center\">SEARCH RESULTS</div></td><td width=\"75%\"> <div align=\"right\"></div></td>\n";
        echo "					    </tr>\n";
        echo "					</table>\n";
        echo "				</td>\n";
        echo "			</tr>\n";
        echo "			<tr>\n";
        echo "				<td colspan=\"2\" align=\"left\" valign=\"top\" class=\"borders\">\n";
        echo "					<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" class=\"displayList\">\n";
		echo "						<tr align=\"left\" valign=\"middle\">\n";
        echo "					        <td colspan=\"2\" valign=\"left\" bgcolor=\"#FFFFFF\" class=\"headingCell2\">&nbsp;&nbsp;<i>". $search->totalCount . " items found</i></td>\n";
        echo "							<td class=\"headingCell1\">Select</td>\n";
        echo "				        </tr>\n";

		$cnt = $search->first;
		$i = 0;
		for ($ndx=0;$ndx<count($search->items);$ndx++)
		{

			$item = $search->items[$ndx];
			$physicalCopy = new physicalCopy();
			$physicalCopy->getByItemID($item->getItemID());
			$callNumber = $physicalCopy->getCallNumber();

			$title = $item->getTitle();
			$author = $item->getAuthor();
			$url = $item->getURL();
			$performer = $item->getPerformer();
			$volTitle = $item->getVolumeTitle();
			$volEdition = $item->getVolumeEdition();
			$pagesTimes = $item->getPagesTimes();
			$source = $item->getSource();
			$itemNotes = $item->getNotes();

			$cnt++;
			$rowClass = ($i++ % 2) ? "evenRow" : "oddRow";

			 if ((is_array($hidden_requests) && in_array($item->getItemID(),$hidden_requests)) || (is_array($hidden_reserves) && in_array($item->getItemID(),$hidden_reserves)))
			 {
			 	$checked = 'checked';
			 } else {
			 	$checked = '';
			 }

			echo "						<tr align=\"left\" valign=\"middle\" class=\"$rowClass\">\n";
        	echo "					        <td width=\"4%\" valign=\"top\">\n";
        	echo "								<img src=\"". $item->getitemIcon() ."\" width=\"24\" height=\"20\"></td>\n";
        	echo "							</td>\n";
        	//echo "							<td width=\"88%\"><font class=\"titlelink\">" . $title . ". " . $author . "</font>";

        	$viewReserveURL = "reservesViewer.php?item=" . $item->getItemID();
				if ($item->isPhysicalItem()) {
					//move to config file
					$viewReserveURL = $g_reservesViewer . $item->getLocalControlKey();
				}
				echo '<td width="88%">';
	            if (!$item->isPhysicalItem()) {
	            	echo '<a href="'.$viewReserveURL.'" target="_blank" class="titlelink">'.$title.'</a>';
	            } else {
	            	echo '<em>'.$title.'</em>.';
	            	if ($item->getLocalControlKey()) echo ' (<a href="'.$viewReserveURL.'" target="_blank">more info</a>)';
	            }
	            if ($author)
	            	echo '<br><font class="titlelink"> '. $author . '</font>';


        				if ($callNumber) {
            				echo '<br>Call Number: '.$callNumber;
            				//if ($this->itemGroup == 'MULTIMEDIA' || $this->itemGroup == 'MONOGRAPH')
            			}

           		if ($performer)
	            {
	            	echo '<br><span class="itemMetaPre">Performed by:</span><span class="itemMeta"> '.$performer.'</span>';
	            }
	            if ($volTitle)
	            {
	            	echo '<br><span class="itemMetaPre">From:</span><span class="itemMeta"> '.$volTitle.'</span>';
	            }
	            if ($volEdition)
	            {
	            	echo '<br><span class="itemMetaPre">Volume/Edition:</span><span class="itemMeta"> '.$volEdition.'</span>';
	            }
	            if ($pagesTimes)
	            {
	            	echo '<br><span class="itemMetaPre">Pages/Time:</span><span class="itemMeta"> '.$pagesTimes.'</span>';
	            }
	            if ($source)
	            {
	            	echo '<br><span class="itemMetaPre">Source/Year:</span><span class="itemMeta"> '.$source.'</span>';
	            }

				//show notes
				self::displayNotes($itemNotes);
	            
	        	if ($item->getItemGroup() != "ELECTRONIC" && !is_null($loan_periods)) 
			    {
			    	echo "<br>\n";
			    	echo "<b>Requested Loan Period:<b> ";
			    	echo "	<select name=\"requestedLoanPeriod_". $item->getItemID() ."\">\n";
					for($n=0; $n < count($loan_periods); $n++)
					{
						$selected = ($loan_periods[$n]['default'] == 'true') ? " selected " : "";
			    		echo "		<option value=\"" . $loan_periods[$n]['loan_period'] . "\" $selected>". $loan_periods[$n]['loan_period'] . "</option>\n";
					}
			    	echo "	</select>\n";	    	
			    }	        	   

            echo "							</td>\n";

            echo "						    <td width=\"8%\" valign=\"top\" class=\"borders\" align=\"center\">\n";

            if ($item->getItemGroup() == "ELECTRONIC"){
				echo "                          <input type=\"checkbox\" name=\"reserve[" . $item->getItemID() ."]\" value=\"" . $item->getItemID() ."\" ".$checked.">\n";
			} else {
				echo "                          <input type=\"checkbox\" name=\"request[" . $item->getItemID() ."]\" value=\"" . $item->getItemID() ."\" ".$checked.">\n";
			}

            echo "				            </td>\n";
            echo "						</tr>\n";
		}

        echo "         			</table>\n";
        echo "         		</td>\n";
        echo "         	</tr>";
        echo "       	<tr><td colspan=\"2\">&nbsp;</td></tr>\n";

	    if ($showNextLink || $showPrevLink) {
	   		echo "       	<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
        	echo "			<tr><td colspan=\"2\" align='right'>";
        	if ($showPrevLink) {
        		echo "<img src=\"images/getPrevious.gif\" onClick=\"javaScript:document.forms.searchResults.cmd.value='searchResults';document.forms.searchResults.f.value=".$fPrev.";document.forms.searchResults.submit();\">&nbsp;&nbsp;";
        	}
        	if ($showNextLink) {
        		echo "<img src=\"images/getNext.gif\" onClick=\"javaScript:document.forms.searchResults.cmd.value='searchResults';document.forms.searchResults.f.value=".$fNext.";document.forms.searchResults.submit();\">";
        	}
        	echo "</td></tr>\n";
        } else {
        	echo "<tr><td>&nbsp;</tr></td>\n";
        }        
        

		echo "			<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
		echo "			<tr><td colspan=\"2\" align=\"right\"><input type=\"submit\" name=\"Submit2\" value=\"Add Selected Materials\"></td></tr>\n";
		echo "			<tr><td colspan=\"2\"><img src=\"images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
		echo "		</tbody>\n";
		echo "</table>\n";

	}

function displayReserveAdded($user, $reserve=null, $ci)
{
	global $g_reservesViewer, $g_permission;

	echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
  echo "	<tr><td width=\"100%\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\">&nbsp;</td></tr>\n";
  echo "	<tr>\n";
  echo "		<td align=\"left\" valign=\"top\" class=\"borders\">\n";
  echo "			<table width=\"50%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"5\">\n";
  echo "				<tr><td><strong>Your items have been added successfully.</strong></td></tr>\n";
  echo "              <tr><td>\n";
  echo "							<ul><li class=\"nobullet\"><a href=\"index.php?cmd=editClass&ci=$ci\">Go to class</a></li>\n";
  echo "						</ul></td></tr>\n";
  echo "				<tr>\n";
  echo "					<td align=\"left\" valign=\"top\"><p>Would you like to put more items on reserve?</p><ul>\n";
  echo "						<li><a href=\"index.php\">No</a></li>\n";
  echo "						<li><a href=\"index.php?cmd=addReserve&ci=$ci\">Yes, to this class.</a></li>\n";
  echo "						<li><a href=\"index.php?cmd=addReserve\">Yes, to another class.</a></li>\n";
  echo "					</ul></td>\n";
  echo "				</tr>\n";
  
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
		$itemNotes = $reserve->item->getNotes();
		$reserveNotes = $reserve->getNotes();
			
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
	    	
		//show notes
		self::displayNotes($itemNotes);
		self::displayNotes($reserveNotes);
			
    	echo '	</td>';
    	echo '	<td width="17%" valign="top">[ <a href="index.php?cmd=editReserve&reserveID='.$reserve->getReserveID().'" class="editlinks">edit item</a> ]</td>';
    	echo ' 	<td width="0%">&nbsp;</td>';
    	echo '</tr>';
    	echo '</table></td></tr>';

	}
  
  
  echo "			</table>\n";
  echo "		</td>\n";
	echo "	</tr>\n";
  echo "	<tr><td><img src=\"images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
  echo "</table>\n";
}

function displayUploadForm($user, $ci, $type, $docTypeIcons=null)
{
	global $g_permission, $g_notetype, $g_copyrightNoticeURL;
	
	if ($type == "URL")		
		$documentTest = "if (frm.url.value == \"\") alertMsg = alertMsg + \"URL is required.<br>\";\n";
	else
		$documentTest = "if (frm.userFile.value == \"\") alertMsg = alertMsg + \"File is required.<br>\";\n";
	
	echo "
		<script language=\"JavaScript\">
		//<!--
			function validateForm(frm)
			{			
				var alertMsg = \"\";

				if (frm.title.value == \"\")
					alertMsg = alertMsg + \"Title is required.<br>\";
				
				$documentTest				
				
				if (!alertMsg == \"\") 
				{ 
					document.getElementById('alertMsg').innerHTML = alertMsg;
					return false;
				}
					
			}
		//-->
		</script>	
	";
	
	
	echo "<form action=\"index.php\" method=\"post\"";
	if ($type == 'DOCUMENT') echo " ENCTYPE=\"multipart/form-data\"";
	echo " onSubmit=\"return validateForm(this);\">\n";

	echo "<input type=\"hidden\" name=\"cmd\" value=\"storeUploaded\">\n";
	echo "<input type=\"hidden\" name=\"ci\" value=\"$ci\">\n";
	echo "<input type=\"hidden\" name=\"type\" value=\"$type\">\n";
	echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
	echo "	<tr><td width=\"100%\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";
	echo "	<tr>\n";
	echo "		<td align=\"left\" valign=\"top\">\n";
	echo "			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
	echo "				<tr><td width=\"35%\" align=\"left\" valign=\"top\" class=\"headingCell1\">FILE INFORMATION</td><td>&nbsp;</td></tr>\n";
	echo "			</table>\n";
	echo "		</td>\n";
	echo "	</tr>\n";

	echo "	<tr>\n";
	echo "		<td align=\"left\" valign=\"top\" class=\"borders\">\n";
	echo "			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\">\n";
	echo "				<tr valign=\"middle\">\n";
	echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\" class=\"strong\"><font color=\"#FF0000\"><strong>*</strong></font>Document Title:</div></td>\n";
	echo "					<td align=\"left\"><INPUT TYPE=\"text\" NAME=\"title\" SIZE=50></td>\n";
	echo "				</tr>\n";

	echo "				<tr valign=\"middle\">\n";
	echo "					<td width=\"35%\" height=\"31\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\" class=\"strong\">Author/Composer:</div></td>\n";
	echo "					<td align=\"left\"><INPUT TYPE=\"text\" NAME=\"author\" SIZE=50></td>\n";
	echo "				</tr>\n";

	if ($type == "URL")
	{
		echo "				<tr valign=\"middle\">\n";
		echo "					<td align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\" class=\"strong\"><font color=\"#FF0000\"><strong>*</strong></font>URL:</div></td>\n";
		echo "					<td align=\"left\"><input name=\"url\" type=\"text\" size=\"50\"></td>\n";
		echo "				</tr>\n";
	} else {
		echo "				<tr valign=\"middle\">\n";
		echo "					<td align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\" class=\"strong\"><font color=\"#FF0000\"><strong>*</strong></font>File:</div></td>\n";
		echo "					<td align=\"left\"><INPUT TYPE=\"file\" NAME=\"userFile\" SIZE=40></td>\n";
		echo "				</tr>\n";

		echo "				<tr valign=\"middle\">\n";
		echo "					<td align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\" class=\"strong\">&nbsp;</div></td>\n";
		echo "					<td align=\"left\">Please limit uploaded documents to 25 clear, clean sheets to minimize downloading and printing time.</td>\n";
		echo "				</tr>\n";
	}
	
	if (!is_null($docTypeIcons))
	{
		echo "				<tr valign=\"middle\">\n";
		echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\"><span class=\"strong\">Document Type Icon:</span></td>\n";
		echo "					<td align=\"left\">";
		echo "						<select name=\"selectedDocIcon\" onChange=\"document.iconImg.src = this[this.selectedIndex].value;\">\n";
				
		for ($j = 0; $j<count($docTypeIcons); $j++)
		{
			//$selectedIcon = (reserveItem::getItemIcon() == $docTypeIcons[$j]['helper_app_icon']) ? " selected " : "";
			echo "							<option value=\"" . $docTypeIcons[$j]['helper_app_icon']  . "\" $selectedIcon>" . $docTypeIcons[$j]['helper_app_name'] . "</option>\n";
		}
			
		echo "						</select>\n";
		echo "					<img name=\"iconImg\" width=\"24\" height=\"20\" border=\"0\" src=\"".reserveItem::getItemIcon()."\">\n";
		echo "					</td>\n";
		echo "				</tr>\n";
	}	
	
	echo "				<tr valign=\"middle\">\n";
	echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\"><span class=\"strong\">Performer </span>(<em>if applicable)</em><span class=\"strong\">:</span></div></td>\n";
	echo "					<td align=\"left\"><input name=\"performer\" type=\"text\" id=\"Title3\" size=\"50\"></td>\n";
	echo "				</tr>\n";

	echo "				<tr valign=\"middle\">\n";
	echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\"><span class=\"strong\">Book/Journal/Work Title</span> (<em>if applicable</em>)<span class=\"strong\">:</span></div></td>\n";
	echo "					<td align=\"left\"><INPUT TYPE=\"text\" NAME=\"volumetitle\" SIZE=50></td>\n";
	echo "				</tr>\n";

	echo "				<tr valign=\"middle\">\n";
	echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\"><span class=\"strong\">Volume / Edition</span> (<em>if applicable</em>)<span class=\"strong\">:</span></div></td>\n";
	echo "					<td align=\"left\"><INPUT TYPE=\"text\" NAME=\"volume\" SIZE=50></td>\n";
	echo "				</tr>\n";

	echo "				<tr valign=\"middle\">\n";
	echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\"><span class=\"strong\">Pages</span> (<em>if applicable</em>)<span class=\"strong\">:</span></div></td>\n";
	echo "					<td align=\"left\"><INPUT TYPE=\"text\" NAME=\"pagefrom\" SIZE=3>  To:  <INPUT TYPE=\"text\" NAME=\"pageto\" SIZE=3></td>\n";
	echo "				</tr>\n";

	echo "				<tr valign=\"middle\">\n";
	echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\"><span class=\"strong\">Times</span> (<em>if applicable</em>)<span class=\"strong\">:</span></div></td>\n";
	echo "					<td align=\"left\"><INPUT TYPE=\"text\" NAME=\"timefrom\" SIZE=3>  To:  <INPUT TYPE=\"text\" NAME=\"timeto\" SIZE=3></td>\n";
	echo "				</tr>\n";

	echo "				<tr valign=\"middle\">\n";
	echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\"><span class=\"strong\">Source / Year</span> (<em>if applicable</em>)<span class=\"strong\">:</span></div></td>\n";
	echo "					<td align=\"left\"><INPUT TYPE=\"text\" NAME=\"source\" SIZE=50></td>\n";
	echo "				</tr>\n";

	echo "				<tr valign=\"middle\">\n";
	if ($user->getRole() >= $g_permission['staff']) {
		echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\"><span class=\"strong\">Note</span>(<em>if applicable</em>)<span class=\"strong\">:</span></div></td>\n";
		echo "					<td align=\"left\"><TEXTAREA NAME=\"noteText\" cols=50 rows=3>\n</TEXTAREA>\n<br>\n";
	
  	echo '      			<span class="small">Note Type:';
    echo '					<label><input type="radio" name="noteType" value="'.$g_notetype['content'].'" checked>Content Note</label>';
    echo '					<label><input type="radio" name="noteType" value="'.$g_notetype['instructor'].'">Instructor Note</label>';
    echo '					<label><input type="radio" name="noteType" value="'.$g_notetype['staff'].'">Staff Note</label>';
		echo '					<label><input type="radio" name="noteType" value="'.$g_notetype['copyright'].'">Copyright Note</label>';
		echo '					</span>';
	} else {
		echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\"><span class=\"strong\">Instructor Note</span>(<em>if applicable</em>)<span class=\"strong\">:</span></div></td>\n";
		echo "					<td align=\"left\"><TEXTAREA NAME=\"noteText\" cols=50 rows=3>\n</TEXTAREA>\n<br>\n";
		echo '					<input type="hidden" name="noteType" value="'.$g_notetype['instructor'].'">';
	}
	echo "</td>";
	echo "				</tr>\n";

	echo "				<tr valign=\"middle\">\n";
	echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\"><span class=\"strong\">This document is from my personal collection:</span> (<em>if applicable</em>)<span class=\"strong\">:</span></div></td>\n";
	echo "					<td align=\"left\"><INPUT TYPE=\"checkbox\" NAME=\"personal\"></td>\n";
	echo "				</tr>\n";

	

	echo "			</table>\n";
	echo "		</td>\n";
	echo "	</tr>\n";
	echo "	<tr><td><img src=\"images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
	if ($type == "URL")
	{
		echo "				<tr><td width=\"20%\" valign=\"top\" colspan=\"3\" align=\"center\"><input type=\"submit\" name=\"Submit\" value=\"Save URL\"></td></tr>\n";
	} else {
		echo "				<tr><td width=\"20%\" valign=\"top\" colspan=\"3\" align=\"center\">\n";
		echo "						<div style=\"font:arial; font-weight:bold; font-size:small; padding:5px;\">I have read the Library's <a href=\"$g_copyrightNoticeURL\" target=\"blank\">copyright notice</a> and certify that to the best of my knowledge my use of this document falls within those guidelines.</div>\n";
		echo "						<br><input type=\"submit\" name=\"Submit\" value=\"Save Document\">\n";
		echo "				</td></tr>\n";
	}
	echo "</table>\n";
	echo "</form>\n";
}

function displayFaxInfo($ci)
{

	echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
    echo "	<tr><td width=\"100%\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";
    echo "	<tr>\n";
    echo "		<td align=\"left\" valign=\"top\">\n";
    echo "			<table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"3\" cellspacing=\"0\" class=\"borders\">\n";
    echo "				<tr>\n";
    echo "					<td align=\"left\" valign=\"top\">\n";
    echo "						<blockquote>\n";
    echo "							<p class=\"helperText\">ReservesDirect allows you to fax in a document and will automatically convert it to PDF. Please limit faxed documents to 25 clear, clean sheets to minimize downloading and printing time. To proceed, please fax each document individually (with no cover sheet!) to: </p>\n";
    echo "							<p><span class=\"strong\">(404) 727-9089</span> (On-campus may dial <span class=\"strong\">7-9089</span> )</p>\n";
    echo "							<p class=\"helperText\">Please note that faxes make take up to a minute per page to process during peak times. For best results, wait for a confirmation sheet to print from your fax machine before faxing another document.</p>\n";
    echo "						</blockquote>\n";
    echo "					</td>\n";
    echo "				</tr>\n";
    echo "			</table>\n";
    echo "		</td>\n";
    echo "	</tr>\n";
    echo "	<tr><td>&nbsp;</td></tr>\n";
    echo "	<tr>\n";
    echo "		<td>\n";
    echo "			<form method=\"post\" action=\"index.php\">\n";
	echo "			<input type=\"hidden\" name=\"cmd\" value=\"getFax\">\n";
	echo "			<input type=\"hidden\" name=\"ci\" value=\"$ci\">\n";
    echo "			<p align=\"center\">\n";
    echo "				<input type=\"submit\" name=\"Submit\" value=\"After your fax has finished transmitting, Click Here\">\n";
    echo "			</p>\n";
    echo "			</form>\n";
    echo "			<p align=\"center\">Unclaimed faxes are deleted at midnight.</p>\n";
    echo "		</td>\n";
    echo "	</tr>\n";
    echo "	<tr><td><img src=\"images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
    echo "</table>\n";
}

function claimFax($faxReader, $ci)
{
	global $g_faxURL;

	echo "<form method=\"post\" action=\"index.php\">\n";
	echo "<input type=\"hidden\" name=\"cmd\" value=\"addFaxMetadata\">\n";
	echo "<input type=\"hidden\" name=\"ci\" value=\"$ci\">\n";
	echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
    echo "	<tr><td width=\"100%\" colspan=\"2\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";
    echo "	<tr>\n";
	echo "		<td width=\"50%\" align=\"left\" valign=\"top\" class=\"helperText\">Claim your fax.</td>\n";
	echo "		<td width=\"50%\" align=\"left\" valign=\"top\" align=\"right\"><a href=\"index.php?cmd=faxReserve&amp;ci=".$ci."\">Return to Previous Page</a></td>\n";
	echo "	</tr>\n";

	echo "	<tr><td height=\"14\" colspan=\"2\" align=\"left\" valign=\"top\">&nbsp;</td></tr>\n";

	echo "	<tr>\n";
	echo "		<td height=\"14\" colspan=\"2\" align=\"left\" valign=\"top\">\n";
	echo "			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
	echo "				<tr>\n";
	echo "					<td width=\"35%\" align=\"left\" valign=\"top\" class=\"headingCell1\">ACTIVE FAXES</td><td width=\"65%\" align=\"right\" valign=\"top\">&nbsp;</td></tr>\n";
	echo "			</table>\n";
	echo "		</td>\n";
	echo "	</tr>\n";

	if (is_array($faxReader->faxes) && !empty($faxReader->faxes)){
		echo "	<tr>\n";
		echo "		<td colspan=\"2\" align=\"left\" valign=\"top\" class=\"borders\">\n";
		echo "			<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" class=\"displayList\">\n";
		echo "				<tr align=\"left\" valign=\"middle\">\n";
		echo "					<td width=\"20%\" valign=\"top\" bgcolor=\"#FFFFFF\" class=\"headingCell1\">Fax Number</td>\n";
		echo "					<td width=\"40%\" bgcolor=\"#FFFFFF\" class=\"headingCell1\">Time of  Fax</td>\n";
		echo "					<td width=\"15%\" class=\"headingCell1\">Pages</td>\n";
		echo "					<td width=\"10%\" class=\"headingCell1\">&nbsp;</td>\n";
		echo "					<td width=\"15%\" class=\"headingCell1\">Claim Fax</td>\n";
		echo "				</tr>\n";

		for($i=0;$i<count($faxReader->faxes);$i++)
		{
			$fax =& $faxReader->faxes[$i];

			$rowClass = ($i % 2) ? "evenRow" : "oddRow";

			echo "				<tr align=\"left\" valign=\"middle\" class=\"$rowClass\">\n";
			echo "					<td width=\"20%\" valign=\"top\" class=\"$rowClass\" align=\"center\">" . $fax['phone'] . "</td>\n";
			echo "					<td width=\"40%\" class=\"$rowClass\" align=\"center\">" . $fax['time'] . "</td>\n";
			echo "					<td width=\"15%\" valign=\"top\" class=\"$rowClass\" align=\"center\">" . $fax['pages'] . "</td>\n";
			echo "					<td width=\"10%\" valign=\"top\" class=\"$rowClass\" align=\"center\"><a href=\"".$g_faxURL.$fax['file']."\" target=\"_new\">preview</a></td>\n";
			echo "					<td width=\"15%\" valign=\"top\" class=\"$rowClass\" align=\"center\"><input type=\"checkbox\" name=\"claimFax[$i]\" value=\"" . $fax['file'] . "\" onclick=\"this.form.submit.disabled=false;\"></td>\n";
			echo "				</tr>\n";

		}
		echo "				<tr align=\"left\" valign=\"middle\"><td width=\"20%\" valign=\"top\" class=\"headingCell1\">&nbsp;</td><td width=\"40%\" class=\"headingCell1\">&nbsp;</td><td width=\"15%\" valign=\"top\" class=\"headingCell1\">&nbsp;</td><td width=\"10%\" valign=\"top\" class=\"headingCell1\">&nbsp;</td><td width=\"15%\" valign=\"top\" class=\"headingCell1\">&nbsp;</td></tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";

		echo "	<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
		echo "	<tr><td colspan=\"2\" align=\"center\"><input type=\"submit\" name=\"submit\" value=\"Continue\" disabled=true></td></tr>\n";
	} else {
		echo "	<tr><td colspan=\"2\" align=\"center\"><b>No faxes have been received.  Remember unclaimed faxes are deleted at midnight.</td></tr>\n";
	}
	echo "	<tr><td colspan=\"2\"><img src=\"images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
}

function displayFaxMetadataForm($user, $faxes, $ci)
{
	global $g_faxURL, $g_permission, $g_notetype;

	echo "<FORM METHOD=POST ACTION=\"index.php\">\n";
	echo "	<INPUT TYPE=\"HIDDEN\" NAME=\"cmd\" VALUE=\"storeFaxMetadata\">\n";
	echo "	<INPUT TYPE=\"HIDDEN\" NAME=\"ci\" VALUE=\"$ci\">\n";

	echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
	echo "	<tr><td width=\"100%\" colspan=\"2\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";
	echo "	<tr>\n";
	echo "		<td width=\"50%\" align=\"left\" valign=\"top\" class=\"helperText\">Add information about your fax(es).</td>\n";
	echo "		<td width=\"50%\" align=\"left\" valign=\"top\" align=\"right\"><a href=\"index.php?cmd=getFax&amp;ci=".$ci."\">Return to previous page</a></td>\n";
	echo "	</tr>\n";

	echo "	<tr><td colspan=\"2\" align=\"left\" valign=\"top\">&nbsp;</td></tr>\n";

	echo "	<tr>\n";
	echo "		<td colspan=\"2\" align=\"left\" valign=\"top\">\n";
	echo "			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
	echo "				<tr><td width=\"35%\" class=\"headingCell1\">FAX DETAILS</td><td>&nbsp;</td></tr>\n";
	echo "			</table>\n";
	echo "		</td>\n";
	echo "	</tr>\n";


	if (is_array($faxes) && !empty($faxes))
	{
		$i = 0;
		foreach ($faxes as $fax)
		{
			$rowClass = ($i++ % 2) ? "evenRow" : "oddRow";
			echo "	<tr>\n";
			echo "		<td colspan=\"2\" align=\"left\" valign=\"top\" class=\"borders\">\n";
			echo "			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\">\n";
			echo "				<tr align=\"center\" valign=\"top\" class=\"#CCCCCC\" class=\"displayList\">\n";
			echo "					<td width=\"25%\"><div align=\"center\">" . $fax['phone'] . "</div></td>\n";
			echo "					<td width=\"25%\"><div align=\"center\">" . $fax['time'] . "</div></td>\n";
			echo "					<td width=\"25%\"><div align=\"center\">" . $fax['pages'] . " page(s)</div></td>\n";
			echo "					<td width=\"25%\"><div align=\"center\"><a href=\"" . $g_faxURL . $fax['file'] . "\" target=\"preview\">preview document</a></div></td>\n";
			echo "				</tr>\n";
			echo "			</table>\n";
			echo "		</td>\n";
			echo "	</tr>\n";

			echo "	<tr>\n";
			echo "		<td colspan=\"2\" align=\"left\" valign=\"top\">\n";
			echo "			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
			echo "				<tr><td align=\"left\" valign=\"top\" class=\"headingCell1\">DOCUMENT INFORMATION</td></tr>\n";
			echo "			</table>\n";
			echo "		</td>\n";
			echo "	</tr>\n";

			echo "	<INPUT TYPE=\"HIDDEN\" NAME=\"file[" . str_replace('.', '_',$fax['file']) . "]\" value=\"" . $fax['file'] ."\" >\n";

			echo "	<tr>\n";
			echo "		<td colspan=\"2\" align=\"left\" valign=\"top\" class=\"borders\">\n";
			echo "			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\">\n";
			echo "				<tr valign=\"middle\">\n";
			echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\" align=\"right\" class=\"strong\">Title:</td>\n";
			echo "					<td align=\"left\"><INPUT TYPE=\"text\" NAME=\"" . $fax['file'] . "[title]\" SIZE=50></td>\n";
			echo "				</tr>\n";

			echo "				<tr valign=\"middle\">\n";
			echo "					<td width=\"35%\" height=\"31\" align=\"right\" bgcolor=\"#CCCCCC\" align=\"right\" class=\"strong\">Author</td>\n";
			echo "					<td align=\"left\"><INPUT TYPE=\"text\" NAME=\"" . $fax['file'] . "[author]\" SIZE=50></td>\n";
			echo "				</tr>\n";

			echo "				<tr valign=\"middle\">\n";
			echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\" align=\"right\"><span class=\"strong\">Book/Journal/Work Title</span> (<em>if applicable</em>)<span class=\"strong\">:</span></div></td>\n";
			echo "					<td align=\"left\"><INPUT TYPE=\"text\" NAME=\"" . $fax['file'] . "[volumetitle]\" SIZE=50></td>\n";
			echo "				</tr>\n";

			echo "				<tr valign=\"middle\">\n";
			echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\"><span class=\"strong\">Volume / Edition</span> (<em>if applicable</em>)<span class=\"strong\">:</span></div></td>\n";
			echo "					<td align=\"left\"><INPUT TYPE=\"text\" NAME=\"" . $fax['file'] . "[volume]\" SIZE=50></td>\n";
			echo "				</tr>\n";

			echo "				<tr valign=\"middle\">\n";
			echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\"><span class=\"strong\">Pages</span> (<em>if applicable</em>)<span class=\"strong\">:</span></div></td>\n";
			echo "					<td align=\"left\">From:  <INPUT TYPE=\"text\" NAME=\"" . $fax['file'] . "[pagefrom]\" SIZE=3> To: <INPUT TYPE=\"text\" NAME=\"" . $fax['file'] . "[pageto]\" SIZE=3></td>\n";
			echo "				</tr>\n";
/* Not implemented in database
			echo "				<tr valign=\"middle\">\n";
			echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\"><span class=\"strong\">Year</span> (<em>if applicable</em>)<span class=\"strong\">:</span></div></td>\n";
			echo "					<td align=\"left\"><input NAME=\"" . $fax[file] . "[year]\" type=\"text\" size=\"50\"></td>\n";
			echo "				</tr>\n";
*/
			echo "				<tr valign=\"middle\">\n";
			
			
			if ($user->getRole() >= $g_permission['staff']) {
				echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\"><span class=\"strong\">Note</span>(<em>if applicable</em>)<span class=\"strong\">:</span></div></td>\n";
				echo "					<td align=\"left\"><TEXTAREA NAME=\"" . $fax['file'] . "[noteText]\" cols=50 rows=3>\n</TEXTAREA>\n<br>\n";
	
  			echo '      			<span class="small">Note Type:';
    		echo '					<label><input type="radio" name="'.$fax['file'].'[noteType]" value="'.$g_notetype['content'].'" checked>Content Note</label>';
    		echo '					<label><input type="radio" name="'.$fax['file'].'[noteType]" value="'.$g_notetype['instructor'].'">Instructor Note</label>';
    		echo '					<label><input type="radio" name="'.$fax['file'].'[noteType]" value="'.$g_notetype['staff'].'">Staff Note</label>';
				echo '					<label><input type="radio" name="'.$fax['file'].'[noteType]" value="'.$g_notetype['copyright'].'">Copyright Note</label>';
				echo '					</span>';
			} else {
				echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\"><span class=\"strong\">Instructor Note</span>(<em>if applicable</em>)<span class=\"strong\">:</span></div></td>\n";
				echo "					<td align=\"left\"><TEXTAREA NAME=\"" . $fax['file'] . "[noteText]\" cols=50 rows=3>\n</TEXTAREA>\n<br>\n";
				echo '					<input type="hidden" name="'.$fax['file'].'[noteType]" value="'.$g_notetype['instructor'].'">';
			}
			
			echo "				</td></tr>\n";

			echo "				<tr valign=\"middle\">\n";
			echo "					<td align=\"right\" bgcolor=\"#CCCCCC\">&nbsp;</td>\n";
			echo "					<td align=\"left\" align=\"center\" class=\"strong\">\n";
			echo "						This Document is from my Personal Collection: \n";
			echo "						<INPUT TYPE=\"checkbox\" NAME=\"" . $fax['file'] . "[personal]\" CHECKED>\n";
			echo "					</td>\n";
			echo "				</tr>\n";
			echo "			</table>\n";
			echo "		</td>\n";
			echo "	</tr>\n";
		}
	}
	echo "	<tr>\n";
	echo "		<td colspan=\"2\" align=\"left\" valign=\"top\">\n";
	echo "			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\">\n";
	echo "				<tr><td width=\"20%\" valign=\"top\" colspan=\"3\" align=\"center\">\n";
	echo "						<div style=\"font:arial; font-weight:bold; font-size:small; padding:15px 5px 5px 5px;\">I have read the Library's <a href=\"$g_copyrightNoticeURL\" target=\"blank\">copyright notice</a> and certify that to the best of my knowledge my use of this document falls within those guidelines.</div>\n";
	echo "						<input type=\"submit\" name=\"Submit\" value=\"Save Document\"></td></tr>\n";
	echo "			</table>\n";
	echo "		</td>\n";
	echo "	</tr>\n";
	echo "	<tr><td colspan=\"2\"><img src=\"images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
}


/**
 * @return void
 * @param courseInstance $ci Reference to a CI object
 * @param array $reserves Reference to an array of reserve IDs
 * @desc displays sorting form
 */
function displayCustomSort(&$ci, &$reserves) {
?>
	<div>
		<div style="text-align:right;"><strong><a href="index.php?cmd=editClass&amp;ci=<?=$ci->getCourseInstanceID()?>">Return to Edit Class</a></strong></div>
	
		<div style="width:35%; align:left; text-align:center; background:#CCCCCC;" class="borders">
			<strong>Sort by:</strong> [ <a href="index.php?cmd=customSort&amp;ci=<?=$ci->getCourseInstanceID()?>&amp;parentID=<?=$_REQUEST['parentID']?>&amp;sortBy=title" class="editlinks">title</a> ] [ <a href="index.php?cmd=customSort&amp;ci=<?=$ci->getCourseInstanceID()?>&amp;parentID=<?=$_REQUEST['parentID']?>&amp;sortBy=author" class="editlinks">author</a> ]
		</div>
	</div>
	
	<form method="post" name="customSortScreen" action="index.php">		
		<input type="hidden" name="cmd" value="<?=$_REQUEST['cmd']?>" />
		<input type="hidden" name="ci" value="<?=$ci->getCourseInstanceID()?>" />
		<input type="hidden" name="parentID" value="<?=$_REQUEST['parentID']?>" />

		<div align="right">
			<input type="button" name="reset1" value="Reset to Saved Order" onClick="javascript:this.form.submit();">
			&nbsp;<input type="submit" name="saveOrder" value="Save Order">
		</div>
		<br />
		<div class="helperText" style="margin-right:5%; margin-left:5%;">
			NOTE: to sort items inside of a heading, return to the Edit Class screen and click on the <img src="images/sort.gif" alt="sort contents"> link next to the heading.
		</div>
		<br />		
		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
			<tr valign="middle">
				<td class="headingCell1">Reserves</td>
				<td class="headingCell1" width="100">Sort Order</td>
			</tr>
			<tr>
				<td colspan="2">
				<ul style="list-style:none; padding:0px; margin:0px;">
<?php
	//begin displaying individual reserves
	$reserve_count = count($reserves);
	$order = 1;
	foreach($reserves as $r_id):
		$reserve = new reserve($r_id);	//initialize reserve object
		$reserve->getItem();
		
		$rowStyle = ($rowStyle=='oddRow') ? 'evenRow' : 'oddRow';	//set the style
		$rowClass = ($reserve->item->isHeading()) ? 'class="headingCell2"' : 'class="'.$rowStyle.'"';
?>
			
				<li>
				<div <?=$rowClass?> >
				<div style="float:right; padding:7px 30px 5px 5px;">
					<input type="hidden" name="old_order[<?=$reserve->getReserveID()?>]" value="<?=$order?>">
					<input name="new_order[<?=$reserve->getReserveID()?>]" value="<?=$order?>" type="text" size="3" onChange="javascript:if (this.value <=0 || this.value > <?=$reserve_count?> || !parseInt(this.value)) {alert ('Invalid value')} else {updateSort(document.forms.customSortScreen, 'old_order[<?=$reserve->getReserveID()?>]', this.value, this.name)}">
				</div>
				
				<?php self::displayReserveInfo($reserve, 'class="metaBlock-wide"'); ?>
				
				<div style="clear:right;"></div>
				</div>	
				</li>

			
<?php
		$order++;
	endforeach;
?>
				</ul>
				</td>
			</tr>
			<tr valign="middle" class="headingCell1">
				<td class="HeadingCell1" colspan="2">&nbsp;</td>
			</tr>
		</table>
		<br />		
		<div style="margin-right:5%; margin-left:5%; text-align:right;">
			<input type="submit" name="reset1" value="Reset to Saved Order">
			&nbsp;<input type="submit" name="saveOrder" value="Save Order">
		</div>
	</form>
	
	<div style="margin-left:5%; margin-right:5%; text-align:right;"><strong><a href="index.php?cmd=editClass&amp;ci=<?=$ci->getCourseInstanceID()?>">Return to Edit Class</a></strong></div>
<?php
}

}
?>
