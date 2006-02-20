<?
/*******************************************************************************
classDisplayer.class.php


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
require_once("secure/classes/terms.class.php");
require_once('secure/classes/tree.class.php');
require_once("secure/managers/ajaxManager.class.php");
require_once("secure/managers/lookupManager.class.php");
require_once('secure/displayers/baseDisplayer.class.php');

class classDisplayer extends baseDisplayer {
	
	function displayStaffHome($user)
	{
		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr><td width=\"100%\"><img src=\images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>\n";
		echo "	<tr>\n";
		echo "		<td align=\"center\" valign=\"top\">\n";
		echo "			<table width=\"66%\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\">\n";
		echo "				<tr class=\"headingCell1\"><td width=\"66%\" colspan=\"2\">Manage Classes</td><!--<td width=\"33%\">Manage Departments</td>--></tr>\n";
		echo "				<tr align=\"left\" valign=\"top\">\n";
		echo "					<td width=\"33%\" class=\"borders\">\n";
		echo "						<ul>\n";
		echo "							<li><a href=\"index.php?cmd=createClass\" align=\"center\">Create Class</a></li>\n";
		echo "							<li><a href=\"index.php?cmd=staffEditClass\">Edit Class</a></li>\n";
		echo "							<li><a href=\"index.php?cmd=deleteClass\">Delete Class</a></li>\n";
		echo "							<li><a href=\"index.php?cmd=reactivateClass\" align=\"center\">Reactivate Class</a></li>\n";
		echo "						</ul>\n";
		echo "					</td>\n";
		
		echo "					<td width=\"33%\" class=\"borders\">\n";
		echo "						<ul>\n";
		echo "							<li><a href=\"index.php?cmd=copyClass\">Copy Reserve List or Merge Classes</a><!--Links to staff-mngClass-CopyList1.html --></li>\n";
		echo "							<li><a href=\"index.php?cmd=viewEnrollment\">View Student Enrollment</a><!--Links to staff-mngClass-CopyList1.html --></li>\n";
		echo "							<li><a href=\"index.php?cmd=exportClass\">Export a Class to Blackboard, etc.</a><!--Same screens as faculty use for exporting a class--></li>\n";
		echo "						</ul>\n";
		echo "					</td>\n";
		
		//echo "					<td width=\"33%\" class=\"borders\">\n";
		//echo "						&nbsp;<ul>\n";
		//echo "							<!--<li><a DISABLED href=\"index.php?cmd=addDept\">Add Department</a>--><!--Goes to staff-mngClass-createDept1.html --></li>\n";
		//echo "							<!--<li><a DISABLED href=\"index.php?cmd=editDept\">Edit a Department</a>--><!--Goes to staff-mngClass-editDept1.html --></li>\n";
		//echo "						</ul>\n";
		//echo "					</td>\n";
		echo "				</tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr><td>&nbsp;</td></tr>\n";
		echo "</table>\n";
	}

	function displayInstructorHome()
	{
		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\" align=\"center\">";
        echo "	<tr><td width=\"100%\" colspan=\"2\"><img src=\images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>";
        echo "	<tr> ";
        echo "		<td width=\"70%\" align=\"left\" valign=\"top\"> ";
        echo "			<p><a href=\"index.php?cmd=reactivateClass\" class=\"titlelink\">Reactivate Reserve Materials </a><br>";
        echo "			Reactivate a course you have taught in the past or reserve readings you have used in the past</p>";
        echo "			<p><a href=\"index.php?cmd=createClass\" class=\"titlelink\">Create a New Course</a><br>";
        echo "      	Create a new course and reserves list from scratch.</p>";
        echo "			<p><a href=\"index.php?cmd=myReserves\" class=\"titlelink\">Edit an Existing Course</a><br>";
        echo "			Advanced management of your classes. Edit class title, crosslistings, proxies, reserve materials, enrollment, and much more.</p>";
        echo "		</td>";

        echo "		<td width=\"30%\" align=\"left\" valign=\"top\">";
		echo "			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\">";
        echo "				<tr>";
		echo "					<td class=\"headingCell1\">Quick Links</td>";
        echo "      		</tr>";
        echo "				<tr> ";
        echo "					<td align=\"left\" valign=\"top\" class=\"borders\">";
        echo "						<ul>";
        //echo "						<li class=\"small\"><a href=\"link\">Sort my Reserves</a></li>";
        //echo "            			<li class=\"small\"><a href=\"link\">Annotate my Reserves</a></li>";
        echo "            			<li class=\"small\"><a href=\"index.php\">View my Reserves lists</a></li>";
        echo "            			<li class=\"small\"><a href=\"index.php?cmd=exportClass\">Export my Reserves to Courseware class (Blackboard, Learnlink, etc.)</a></li>";
        //echo "            			<li class=\"small\"><a href=\"link\">Get my URLs</a></li>";
        //echo "            			<li class=\"small\"><a href=\"link\">Manage Enrollment for my classes</a></li>";
        echo "         				</ul>";
        echo "					</td>";
        echo "    			</tr>";
        echo "      		<tr>";
        echo "        			<td>&nbsp;</td>";
       	echo "       		</tr>";
      	echo "			</table>";
        echo "		</td>";

      	echo "	</tr>";
        echo "	<tr> ";
     	echo "		<td colspan=\"2\"><img src=\images/spacer.gif\" width=\"1\" height=\"15\"></td>";
        echo "	</tr>";
      	echo "</table>";
	}
	
	
	function displayEditClass($cmd, &$ci, &$tree_walker) {
		global $u, $g_permission, $calendar, $g_siteURL;
		
		
?>

		<form method="post" name="editReserves" action="index.php">		
			<input type="hidden" name="cmd" value="<?=$cmd?>" />
			<input type="hidden" name="ci" value="<?=$ci->getCourseInstanceID()?>" />

		<div>
		
			<div style="text-align:right;"><strong><a href="javascript:openWindow('no_control=1&cmd=previewStudentView&amp;ci=<?=$ci->courseInstanceID?>','width=800,height=600');">Preview Student View</a> | <a href="index.php">Exit class</a></strong></div>
			
			<div class="courseTitle"><?=$ci->course->displayCourseNo() . " " . $ci->course->getName()?>&nbsp;<small>[ <a href="index.php?cmd=editTitle&amp;ci=<?=$ci->getCourseInstanceID()?>" class="editlinks">edit</a> ]</small></div>
			
			<div class="courseHeaders"><span class="label"><?=$ci->displayTerm()?></span></div>
			
			<div class="courseHeaders">
				<span class="label">Cross-listings&nbsp;</span><small>[ <a href="index.php?cmd=editCrossListings&ci=<?=$ci->getCourseInstanceID()?>" class="editlinks">edit</a> ]</small>:

<?php
		if(count($ci->crossListings)==0) {
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
			<div class="courseHeaders"><span class="label">Instructor(s)&nbsp;<small></span>[ <a href="index.php?cmd=editInstructors&ci=<?=$ci->getCourseInstanceID()?>" class="editlinks">edit</a> ]</small>:

<?php 
		for($i=0;$i<count($ci->instructorList);$i++) {
			if ($i!=0) echo ',&nbsp;';
			echo '<a href="mailto:'.$ci->instructorList[$i]->getEmail().'">'.$ci->instructorList[$i]->getFirstName().'&nbsp;'.$ci->instructorList[$i]->getLastName().'</a>';
		}
?>
			</div>
			<div class="courseHeaders"><span class="label">Proxies&nbsp;</span><small>[ <a href="index.php?cmd=editProxies&ci=<?=$ci->getCourseInstanceID()?>" class="editlinks">edit</a> ]</small>:
			
<?php 
		if(count($ci->proxies)==0) {
			echo 'None';
		}
		else {
			for($i=0; $i<count($ci->proxies); $i++) {
				if ($i>0) echo',&nbsp;';
				 echo $ci->proxies[$i]->getFirstName().'&nbsp;'.$ci->proxies[$i]->getLastName().'</a>';
			}
		}
?>

			</div>
			<div class="courseHeaders"><span class="label">Enrollment: </span><span class="<?=common_getStatusStyleTag($ci->getEnrollment())?>"><?=strtoupper($ci->getEnrollment())?></span></div>

<?php	if($u->getRole() >= $g_permission['staff']): 	//hide activate/deactivate dates from non-staff ?>

			<div class="courseHeaders"><span class="label">Class Active Dates: </span><input type="text" id="activation" name="activation" size="10" maxlength="10" value="<?=$ci->getActivationDate()?>" /> <?=$calendar->getWidgetAndTrigger('activation', $ci->getActivationDate())?> to <input type="text" id="expiration" name="expiration" size="10" maxlength="10" value="<?=$ci->getExpirationDate()?>" /> <?=$calendar->getWidgetAndTrigger('expiration', $ci->getExpirationDate())?> <input type="submit" name="updateClassDates" value="Change Dates"></div>
			
<?php	endif; ?>
			
			<br />
			<br />
			
			<script languge="JavaScript">
				//a bit of a hack to highlight all <span>s with class="highlightable"
				function highlightAll() {				
					var items = document.getElementsByTagName("span");
					
					for(var x=0; x<items.length; x++) {
						if(items[x].className == "highlightable") {
							items[x].style.background = "yellow";
						}
					}
				}
			</script>

			<div>
			
				[ <a href="index.php?cmd=customSort&ci=<?=$ci->getCourseInstanceID()?>&parentID=" class="editlinks">sort main list</a> ]
			
<?php	if($u->getRole() >= $g_permission['staff']): ?>

				[ <a href="index.php?cmd=addReserve&ci=<?=$ci->getCourseInstanceID()?>&selected_instr=<?=$ci->instructorList[0]->getUserID()?>" class="editlinks">add new materials</a> ] 
				
<?php	else: ?>

				[ <a href="index.php?cmd=displaySearchItemMenu&ci=<?=$ci->getCourseInstanceID()?>" class="editlinks">add new materials</a> ] 

<?php 	endif; ?>

				[ <a href="index.php?cmd=editHeading&ci=<?=$ci->getCourseInstanceID()?>" class="editlinks">add new heading</a> ]
				[ <a href="#" class="editlinks" onclick="highlightAll(); return false;">show reserve links</a> ]
		
			</div>
			
		</div>
		<br />		
		
		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
			<tr align="left" valign="middle">
				<td class="headingCell1">COURSE MATERIALS</td>
				<td width="75%" align="right">
					<a href="javascript:checkAll(document.forms.editReserves, 1)">check all</a> | <a href="javascript:checkAll(document.forms.editReserves, 0)">uncheck all</a>
				</td>
			</tr>
			<tr valign="middle">
				<td class="headingCell1" align="right" colspan="2">
					<div class="editOptionsTitles">
						<div class="itemNumber">
							#
						</div>	
						<div class="checkBox">
							Select
						</div>
						<div class="sortBox">
							Sort
						</div>	
						<div class="editBox">
							Edit
						</div>	
						<div class="statusBox">
							Status
						</div>					
					</div>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<ul style="list-style:none; padding-left:0px; margin:0px;">
<?php
		//begin displaying individual reserves and building dataSet for TSV export
		//loop
		$prev_depth = 0;
		$counter = 0;
        $i=0;
        $fields = array("author", "title", "source", "volumeTitle", "volumeEdition", "pagesTimes", "performer");
		foreach($tree_walker as $leaf) {
			//close list tags if backing out of a sublist
			if($prev_depth > $tree_walker->getDepth()) {
				echo str_repeat('</ul></li>', ($prev_depth-$tree_walker->getDepth()));
			}
			
			$reserve = new reserve($leaf->getID());	//init a reserve object
			$reserve->getItem();	//pull item info
			
			//set edit link and status
			if($reserve->item->isHeading()) {
				//set edit link
	        	$editURL = "index.php?cmd=editHeading&ci=".$ci->getCourseInstanceID()."&headingID=".$reserve->getReserveID();
	        	//set the status
	        	$status = 'HEADING';
            }
            else {
            	//edit link
            	if($u->getRole() >= $g_permission['staff']) {	//staff, show them the editItem link
            		$editURL = 'index.php?cmd=editItem&reserveID='.$reserve->getReserveID();
            	}
            	else {	//user is instructor or proxy -- show editReserve link
            		$editURL = "index.php?cmd=editReserve&reserveID=".$reserve->getReserveID();
            	}
            	//status
            	$status = $reserve->getStatus();
            	//if the reserve is not supposed to be active yet, hide it
            	if(($status=='ACTIVE') && ($reserve->getActivationDate() > date('Y-m-d'))) {
            		$status = 'HIDDEN';
            	}
            }
            //do not show edit link for physical items unless viewed by staff
            $reserve->edit_link = (!$reserve->item->isPhysicalItem() || $u->getRole() >= $g_permission['staff']) ? '<a href="'.$editURL.'"><img src="images/pencil-gray.gif" border="0" alt="edit"></a>' : '';
			$reserve->status = $status;	//pass the status
            $reserve->counter = ++$counter;	//increment and pass the counter
            
            //if this reserve is a non-empty folder, set sort link
            $reserve->sort_link = ($leaf->numChildren() > 1) ? '<a href="index.php?cmd=customSort&amp;ci='.$ci->getCourseInstanceID().'&amp;parentID='.$leaf->getID().'"><img src="images/sort.gif" border="0" alt="sort contents"></a>' : '';
            
            //show plain-text links to electronic reserves
           	if(!$reserve->item->isPhysicalItem()) {	//only needed for electronic items
           		$reserve->additional_info = '<br /><span style="font-weight:bold; color:#333333;">Link to this item:</span> <span class="highlightable">'.$g_siteURL.'/reservesViewer.php?reserve='.$reserve->getReserveID().'</span>';
			}
            			
			$rowStyle = ($rowStyle=='oddRow') ? 'evenRow' : 'oddRow';	//set the style

			//display the info
			echo '<li>';
			self::displayReserveRow($reserve, 'class="'.$rowStyle.'"', true);
			
			//start sublist or close list-item?
			echo ($leaf->hasChildren()) ? '<ul style="list-style:none;">' : '</li>';
			
            //append to TSV dataSet
            foreach ($fields as $key => $field) 
                $dataSet[$i]["$field"] = $reserve->item->$field;
                $dataSet[$i]["url"] = $g_siteURL . "/reservesViewer.php?reserve=" . $reserve->reserveID;
            $i++;
            
			$prev_depth = $tree_walker->getDepth();
		}
		echo str_repeat('</ul></li>', ($prev_depth));	//close all lists
?>

					</ul>
				</td>
			</tr>
			<tr valign="middle">
				<td class="headingCell1" style="text-align:right; padding:2px;" align="right" colspan="2">
					Add checked items to a heading: <?php self::displayHeadingSelect($ci); ?>
					<input type="submit" name="" value="Submit">
					&nbsp;&nbsp;
					<select name="reserveListAction">
						<option selected>For all Selected Items:</option>
						<option value="copyAll">Copy items to another class</option>
						<option value="deleteAll">Delete all selected items</option>
						<option value="activateAll">Set all Selected to ACTIVE</option>
						<option value="deactivateAll">Set all Selected to INACTIVE</option>
					</select>
					<input type="submit" name="modifyReserveList" value="Submit">
				</td>
			</tr>			
		</table>
		</form>
        <table width="100%"><tr><td align="center"><br/>
            <form method="post" action="tsvGenerator.php">
                <input type="hidden" name="dataSet" value="<?=urlencode(serialize($dataSet))?>">
                <input type="submit" name="exportTsv" value="Export to Spreadsheet">
            </form>
        </td></tr></table>

		<p />
		<div style="margin-left:5%; margin-right:5%; text-align:right;"><strong><a href="index.php">Exit class</a></strong></div>
				
<?php
	}

	function displayEditTitle($ci, $deptList, $deptID)
	{
		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "<tr>\n";
		echo "<td width =\"100%\" align=\"right\" valign=\"middle\"><!--<div align=\"right\" class=\"currentClass\">".$ci->course->displayCourseNo()."&nbsp;".$ci->course->getName()."</div>--></td>\n";
		echo "</tr>\n";
		echo " <form action=\"index.php?cmd=editTitle&ci=".$ci->getCourseInstanceID()."\" method=\"post\">\n";
		echo " <tr>\n";
		echo " 	<td width=\"100%\"><img src=\images/spacer.gif\" width=\"1\" height=\"5\"> </td>\n";
		echo " </tr>\n";
		echo "	<tr><td colspan=\"3\" align=\"right\"> <a href=\"index.php?cmd=editClass&ci=".$ci->courseInstanceID."\">Return to Edit Class</a></div></td></tr>\n";
		echo " <tr>\n";
		echo " 	<td>\n";
		echo " 	<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		echo "     	<tr align=\"left\" valign=\"top\">\n";
		echo "         	<td width=\"40%\" class=\"headingCell1\">CLASS TITLE and CROSSLISTINGS</td>\n";
		echo " 			<td>&nbsp;</td>\n";
		echo " 		</tr>\n";
		echo " 	</table>\n";
		echo " 	</td>\n";
		echo "</tr>\n";
		echo " <tr>\n";
		echo " 	<td align=\"left\" valign=\"top\" class=\"borders\">\n";
		echo " 	<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\">\n";
		echo "     	<tr class=\"headingCell1\">\n";
		echo "      	<td width=\"8%\" align=\"center\" valign=\"middle\">Primary</td>\n";
		echo "          <td width=\"6%\" align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "          <td width=\"4%\" align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "          <td width=\"13%\" align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "          <td width=\"4%\" align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "          <td width=\"8%\" align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "          <td width=\"4%\" align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "          <td width=\"7%\" align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "          <td width=\"35%\" align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "          <td width=\"11%\" align=\"center\" valign=\"middle\">Delete</td>\n";
		echo "		</tr>\n";
		echo "		<tr class=\"evenRow\">\n";
		echo "			<td align=\"center\" valign=\"middle\"><input name=\"primaryCourse\" type=\"radio\" value=\"".$ci->course->courseAliasID."\" checked></td>\n";
		echo"			<INPUT TYPE=\"HIDDEN\" NAME=\"oldPrimaryCourse\" VALUE=\"".$ci->course->courseAliasID."\">\n";
		echo "          <td align=\"right\" valign=\"middle\" class=\"strong\">Dept:</td>\n";
		echo "          <td align=\"left\" valign=\"middle\"> <select name=\"primaryDept\">\n";
		echo "	            <option value=\"\">--Select-- \n";

		foreach($deptList as $department)
				{
					echo "<option value=\"" . $department[0] . "\"\n";

					if ($department[0] == $deptID) {
						echo " selected\n";
					}
					echo ">" . $department[1] . "</option>\n\n";
				}

		echo " 				</select>\n";
		echo "     		</td>\n";
		echo "          <td align=\"right\" valign=\"middle\">Course#:</td>\n";
		echo "          <td align=\"left\" valign=\"middle\"> <input name=\"primaryCourseNo\" type=\"text\" size=\"5\" maxlength=\"8\" value=\"".$ci->course->getCourseNo()."\"></td>\n";
		echo "          <td align=\"right\" valign=\"middle\">Section:</td>\n";
		echo "          <td align=\"left\" valign=\"middle\"> <input name=\"primarySection\" type=\"text\" size=\"5\" maxlength=\"8\" value=\"".$ci->course->getSection()."\"></td>\n";
		echo "          <td align=\"right\" valign=\"middle\">Title:</td>\n";
		echo "          <td align=\"left\" valign=\"middle\"> <input name=\"primaryCourseName\" type=\"text\" size=\"25\" value=\"".$ci->course->getName()."\"></td>\n";
		echo "          <td align=\"center\" valign=\"middle\"></td>\n";
		echo "		</tr>\n";
		
		$rowNumber = 0;
		for ($i=0; $i<count($ci->crossListings); $i++) 
		{
			$rowClass = ($rowNumber % 2) ? "evenRow" : "oddRow\n";
			echo "		<tr class=\"".$rowClass."\"> \n";
			echo "			<td align=\"center\" valign=\"middle\"><!--<input type=\"radio\" name=\"primaryCourse\" value=\"".$ci->crossListings[$i]->courseAliasID."\">--></td>\n";
			echo "			<td align=\"right\" valign=\"middle\" class=\"strong\">Dept:</td>\n";
			echo "			<td align=\"left\" valign=\"middle\"> <select name=\"cross_listings[".$ci->crossListings[$i]->courseAliasID."][dept]\">\n";
			echo "			<option value=\"\">--Select-- \n";
				foreach($deptList as $department)
				{
						echo "<option value=\"" . $department[0] . "\"\n";
	
						if ($department[0] == $ci->crossListings[$i]->deptID) {
							echo " selected\n";
						}
						echo ">" . $department[1] . "</option>\n\n";
				}
	
	
			echo "			            </select></td>\n";
			echo "			<td align=\"right\" valign=\"middle\">Course#:</td>\n";
			echo "			<td align=\"left\" valign=\"middle\"> <input name=\"cross_listings[".$ci->crossListings[$i]->courseAliasID."][courseNo]\" type=\"text\" size=\"5\" maxlength=\"8\" value=\"".$ci->crossListings[$i]->courseNo."\"></td>\n";
			echo "			<td align=\"right\" valign=\"middle\">Section:</td>\n";
			echo "			<td align=\"left\" valign=\"middle\"> <input name=\"cross_listings[".$ci->crossListings[$i]->courseAliasID."][section]\" type=\"text\" size=\"5\" maxlength=\"8\" value=\"".$ci->crossListings[$i]->section."\"></td>\n";
			echo "			<td align=\"right\" valign=\"middle\">Title:</td>\n";
			echo "			<td align=\"left\" valign=\"middle\"> <input name=\"cross_listings[".$ci->crossListings[$i]->courseAliasID."][courseName]\" type=\"text\" size=\"25\" value=\"".$ci->crossListings[$i]->getName()."\"></td>\n";
			echo "			<td align=\"center\" valign=\"middle\"><input type=\"checkbox\" name=\"deleteCrossListing[".$ci->crossListings[$i]->courseAliasID."]\" value=\"".$ci->crossListings[$i]->courseAliasID."\"></td>\n";
			echo "		</tr>\n";
			$rowNumber++;
		}
		
		echo "		<tr class=\"headingCell1\">\n";
		echo "			<td align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "			<td align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "			<td align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "			<td align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "			<td align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "			<td align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "			<td colspan=\"4\" align=\"left\" valign=\"top\"><div align=\"right\"><input type=\"submit\" name=\"updateCrossListing\" value=\"Update Course Info\">&nbsp;<input type=\"submit\" name=\"deleteCrossListings\" value=\"Delete Selected\"></div></td>\n";
		//echo "				<br>\n";
		echo "		</tr>\n";
		echo "  </table>\n";
		echo " </td>\n";
		echo " </tr>\n";
		echo "</form>\n";
		echo " <tr>\n";
		echo " 	<td height=\"15\">&nbsp;</td>\n";
		echo " </tr>\n";
		echo " <form action=\"index.php\" method=\"post\">\n";
		echo " <input type=\"hidden\" name=\"cmd\" value=\"editCrossListings\">\n";
		echo " <input type=\"hidden\" name=\"ci\" value=\"".$ci->getCourseInstanceID()."\">\n";
		echo " <tr> \n";
		echo " 	<td>\n";
		echo " 	<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		echo " 		<tr align=\"left\" valign=\"top\"> \n";
		echo " 			<td width=\"35%\" class=\"headingCell1\">ADD NEW CROSSLISTING</td>\n";
		echo " 			<!--The \"Show All Editable Item\" Links appears by default when this\n";
		echo " 			page is loaded if some of the metadata fields for the document are blank.\n";
		echo " 			Blank fields will be hidden upon page load. -->\n";
		echo " 			<td>&nbsp; </td>\n";
		echo " 		</tr>\n";
		echo " 	</table>\n";
		echo " 	</td>\n";
		echo " </tr>\n";

		echo "<tr> "
		."    	<td align=\"left\" valign=\"top\" class=\"borders\">\n"
		."    	<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\">\n"
		."      	<tr class=\"headingCell1\">"
		."          	<td width=\"10%\" align=\"left\" valign=\"middle\">&nbsp;</td>\n"
		."              <td width=\"10%\" align=\"left\" valign=\"middle\">&nbsp;</td>\n"
		."              <td width=\"10%\" align=\"left\" valign=\"middle\">&nbsp;</td>\n"
		."              <td width=\"5%\" align=\"left\" valign=\"middle\">&nbsp;</td>\n"
		."              <td width=\"10%\" align=\"left\" valign=\"middle\">&nbsp;</td>\n"
		."              <td width=\"5%\" align=\"left\" valign=\"middle\">&nbsp;</td>\n"
		."              <td width=\"5%\" align=\"left\" valign=\"middle\">&nbsp;</td>\n"
		."              <td width=\"55%\" align=\"left\" valign=\"middle\">&nbsp;</td>\n"
		."			</tr>\n"
		."          <tr> "
		."          	<td align=\"left\" valign=\"middle\" class=\"strong\"><div align=\"center\">Department:</div></td>\n"
		."              <td align=\"left\" valign=\"middle\"> <select name=\"newDept\">\n"
		."                    <option selected value=\"\">--Select-- \n";
		foreach($deptList as $department)
			{
					echo "<option value=\"" . $department[0] . "\"\n";
					echo ">\n" . $department[1] . "</option>\n\n";
			}

		echo "				</select> </td>\n"
		."              <td align=\"left\" valign=\"middle\" class=\"strong\"><div align=\"center\">Course Number:</div></td>\n"
		."              <td align=\"left\" valign=\"middle\"> <div align=\"left\"><input name=\"newCourseNo\" type=\"text\" id=\"Title2\" size=\"4\" maxlength=\"6\"></div></td>\n"
		."              <td align=\"left\" valign=\"middle\" class=\"strong\"><div align=\"center\">Section:</div></td>\n"
		."              <td align=\"left\" valign=\"middle\"> <div align=\"left\"><input name=\"newSection\" type=\"text\" size=\"4\" maxlength=\"6\"></div></td>\n"
		."              <td align=\"left\" valign=\"middle\" class=\"strong\"><div align=\"center\">Title:</div></td>\n"
		."          	<td align=\"left\" valign=\"middle\"> <div align=\"left\"><input name=\"newCourseName\" type=\"text\" size=\"30\"></div></td>\n"
		."			</tr>\n"
		."           <tr class=\"headingCell1\"> "
		."                <td align=\"left\" valign=\"middle\">&nbsp;</td>\n"
		."                <td align=\"left\" valign=\"middle\">&nbsp;</td>\n"
		."                <td align=\"left\" valign=\"middle\">&nbsp;</td>\n"
		."                <td align=\"left\" valign=\"middle\">&nbsp;</td>\n"
		."                <td align=\"left\" valign=\"middle\">&nbsp;</td>\n"
		."                <td align=\"left\" valign=\"middle\">&nbsp;</td>\n"
		."                <td align=\"left\" valign=\"middle\">&nbsp;</td>\n"
		."                <td align=\"left\" valign=\"middle\"><div align=\"right\"><input type=\"submit\" name=\"addCrossListing\" value=\"Add Crosslisting\"></div></td>\n"
		."   		</tr>\n"
		."   	</table>\n"
		."		</td>\n"
		." 	</tr>\n"
		."        <tr>\n"
		."          <td>&nbsp;</td>\n"
		."        </tr>\n"
		."</form>\n"
		."        <tr>\n"
		."          <td><div align=\"center\"><a href=\"index.php?cmd=editClass&ci=".$ci->courseInstanceID."\">Return to Edit Class</a></div></td>\n"
		."        </tr>\n"
		."        <tr>\n"
		."          <td><img src=\images/spacer.gif\" width=\"1\" height=\"15\"></td>\n"
		."        </tr>\n"
		." </table>\n";
	}

	function displayEditInstructors($ci, $addTableTitle, $dropDownDefault, $userType, $removeTableTitle, $removeButtonText, $request)
	{
		echo " <table width=\"95%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">";
		echo "<form name=\"editInstructors\" action=\"index.php?cmd=editInstructors&ci=".$ci->courseInstanceID."\" method=\"post\">";
		echo "<tr>";
		echo "<td colspan=\"3\" align=\"right\" valign=\"middle\"><!--<div align=\"right\" class=\"currentClass\">".$ci->course->displayCourseNo()."</div>--></td>";
		echo " 	</tr>";
		/* Use this logic if we decide to display the course numbers for the cross listings
			for ($i=0; $i<count($ci->crossListings); $i++) {
				echo "<tr>";
				echo "<td colspan=\"3\" align=\"right\" valign=\"middle\"><div align=\"right\" class=\"currentClass\">".$ci->crossListings[$i]->displayCourseNo()."</div></td>";
				echo " 	</tr>";
			}
		*/
		echo " 	<tr>";
		echo " 		<td colspan=\"3\"><img src=\images/spacer.gif\" width=\"1\" height=\"5\"> </td>";
		echo " 	</tr>";
		echo " 	<tr>";
		echo " 		<td height=\"14\" colspan=\"3\" align=\"left\" valign=\"top\">";
		echo " 		<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
		echo "         	<tr>";
		echo "             	<td width=\"35%\" align=\"left\" valign=\"top\" class=\"headingCell1\">".$addTableTitle."</td>";
		echo " 				<td align=\"left\" valign=\"top\">&nbsp;</td>";
		echo " 				<td width=\"35%\" align=\"left\" valign=\"top\" class=\"headingCell1\">".$removeTableTitle."</td>";
		echo " 			</tr>";
		echo " 		</table>";
		echo " 		</td>";
		echo " 	</tr>";
		echo "     <tr>";
		echo "     	<td width=\"50%\" align=\"left\" valign=\"top\">";
		echo "     	<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"borders\">";
		echo " 			<tr>";
		echo "             	<td align=\"left\" valign=\"top\">";
		echo "             	<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" class=\"displayList\">";
		echo "                 	<tr align=\"left\" valign=\"middle\">";
		echo "                   		<td bgcolor=\"#FFFFFF\" class=\"headingCell1\">&nbsp;</td>";
		echo "                 	</tr>";
		echo "                 	<tr align=\"left\" valign=\"middle\">";
		echo "                   		<td width=\"88%\" bgcolor=\"#CCCCCC\"><div align=\"center\">";
		/*
		echo "                	    		<select name=\"prof\">";
		echo "                           		<option value=\"\" selected>-- ".$dropDownDefault." --</option>";
								foreach($instructorList as $instructor)
								{
									echo "<option value=\"" . $instructor["user_id"] . "\">" . $instructor["full_name"] . "</option>";
								}
		echo "                       		</select>";
		*/
		$selectClassMgr = new lookupManager('','lookupInstructor', $u, $request);
		$selectClassMgr->display();
		echo "                   		</div></td>";

		echo "                 	</tr>";
		echo "                 	<tr align=\"left\" valign=\"middle\">";
		echo "                   		<td class=\"headingCell1\">&nbsp;</td>";
		echo "                 	</tr>";
		echo "                 	<tr align=\"left\" valign=\"middle\">";
		echo "                   		<td><div align=\"center\"><input type=\"submit\" name=\"add".$userType."\" value=\"Add ".$userType."\"></div></td>";
		echo "                 	</tr>";
		echo "               </table>";
		echo "               </td>";
		echo " 			</tr>";
		echo " 		</table>";
		echo " 		</td>";
		echo "         <td align=\"left\" valign=\"top\"><img src=\images/spacer.gif\" width=\"15\" height=\"1\"></td>";
		echo "         <td width=\"50%\" align=\"left\" valign=\"top\">";
		echo "         <table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"borders\">";
		echo "         	<tr>";
		echo "             	<td align=\"right\" valign=\"top\">";
		echo "             	<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" class=\"displayList\">";
		echo "                 	<tr align=\"left\" valign=\"middle\">";
		echo "                   		<td bgcolor=\"#FFFFFF\" class=\"headingCell1\">&nbsp;</td>";
		echo "                   		<td class=\"headingCell1\">Remove</td>";
		echo "                 	</tr>";

					$rowNumber = 0;
					if ($userType=="Instructor") {
						$numInstructors = count($ci->instructorList);
						$instruct = $ci->instructorList;
					} elseif ($userType=="Proxy") {
						$numInstructors = count($ci->proxies);
						$instruct = $ci->proxies;
					}
					for($i=0;$i<$numInstructors;$i++) {
						$rowClass = ($rowNumber++ % 2) ? "evenRow" : "oddRow\n";
						echo "                 	<tr align=\"left\" valign=\"middle\" class=\"".$rowClass."\">";
						echo "                   		<td>".$instruct[$i]->getName()."</td>";
						echo "                   		<td width=\"8%\" valign=\"top\" class=\"borders\"><div align=\"center\">";
						if ($userType=="Instructor" && $i>0)
							echo "<input type=\"checkbox\" name=\"".$userType."[".$instruct[$i]->userID."]\" value=\"".$instruct[$i]->userID."\">";
						echo "&nbsp;</div></td>";
							
						echo "                 	</tr>";
					}
		echo "                 	<tr align=\"left\" valign=\"middle\">";
		echo "                   		<td colspan=\"2\"  class=\"headingCell1\">&nbsp;</td>";
		echo "                 	</tr>";
		echo "                 	<tr align=\"left\" valign=\"middle\">";
		echo "                   		<td colspan=\"2\"><div align=\"center\"><input type=\"submit\" name=\"remove".$userType."\" value=\"".$removeButtonText."\"></div></td>";
		echo "                 	</tr>";
		echo " 				</table>";
		echo " 				</td>";
		echo " 			</tr>";
		echo " 		</table>";
		echo " 		</td>";
		echo " 	</tr>";
		echo "     <tr>";
		echo "     	<td colspan=\"3\">&nbsp;</td>";
		echo " 	</tr>";
		echo "     <tr>";
		echo "     	<td colspan=\"3\"><div align=\"center\"><a href=\"index.php?cmd=editClass&ci=".$ci->courseInstanceID."\">Return to Edit Class</a></div></td>";
		echo " 	</tr>";
		echo "     <tr>";
		echo "     	<td colspan=\"3\"><img src=\images/spacer.gif\" width=\"1\" height=\"15\"></td>";
		echo " 	</tr>";
		echo " </form>";
		echo " </table>";
	}

	function displayEditProxies($ci, $proxyList, $request)
	{
		$ci->getPrimaryCourse();

		echo "<form action=\"index.php?cmd=editProxies&ci=".$ci->getCourseInstanceID()."\" method=\"POST\">\n";
		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr>\n";
		echo "		<td colspan=\"3\" align=\"right\"> <a href=\"index.php?cmd=editClass&ci=". $ci->getCourseInstanceID() ."\" class=\"strong\">Return to Class</a></div></td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td colspan=\"3\">&nbsp;</td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td height=\"14\" colspan=\"3\" align=\"left\" valign=\"top\">\n";
		echo "			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		echo "				<tr>\n";
		echo "					<td width=\"35%\" align=\"left\" valign=\"top\" class=\"headingCell1\">ADD A PROXY</td>\n";
		echo "					<td align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "					<td width=\"35%\" align=\"left\" valign=\"top\" class=\"headingCell1\">CURRENT PROXIES</td>\n";
		echo "				</tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td width=\"50%\" align=\"left\" valign=\"top\">\n";
		echo "			<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"borders\">\n";
		echo "				<tr>\n";
		echo "					<td align=\"left\" valign=\"top\">\n";
		echo "						<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" class=\"displayList\">\n";
		echo "							<tr align=\"left\" valign=\"middle\"><td bgcolor=\"#FFFFFF\" class=\"headingCell1\">&nbsp;</td></tr>\n";
		echo "							<tr align=\"left\" valign=\"middle\">\n";
		echo "								<td bgcolor=\"#CCCCCC\" align=\"left\"><p align=\"center\"><span class=\"strong\">Search by: </span>\n";
		echo "									<select name=\"queryTerm\"><option selected value=\"last_name\">Last Name</option><option value=\"username\">User Name</option></select>\n";
		echo "									&nbsp;\n";
		echo "									<input name=\"queryText\" type=\"text\" value=\"".$request['queryText']."\" size=\"15\">&nbsp;\n";
		echo "									<input type=\"submit\" name=\"search\" value=\"Search\"></p>\n";
		echo "								</td>\n";
		echo "							</tr>\n";
		echo "							<tr align=\"left\" valign=\"middle\">\n";
		echo "								<td width=\"88%\" height=\"68\" valign=\"top\" bgcolor=\"#CCCCCC\" align=\"center\">\n";


		$addProxyDisabled = "DISABLED";
		if (is_array($proxyList) && !empty($proxyList)){
			$addProxyDisabled = "";
			echo "									<hr align=\"center\" width=\"150\">\n";
			echo "									<span class=\"strong\">Search Results:</span>\n";
			echo "									<select name=\"proxy\" onChange='this.form.addProxy.disabled=false;'>\n";
			foreach($proxyList as $proxy)
			{
				echo "										<option value=\"".$proxy->getUserID()."\">".$proxy->getName()."</option>\n";
			}
			echo "									</select>\n";
		}

		echo "								</td>\n";
		echo "							</tr>\n";
		echo "							<tr align=\"left\" valign=\"middle\"><td class=\"headingCell1\">&nbsp;</td></tr>\n";
		echo "							<tr align=\"left\" valign=\"middle\">\n";
		echo "								<td align=\"center\"><input type=\"submit\" name=\"addProxy\" value=\"Add Proxy\" $addProxyDisabled></td>\n";
		echo "							</tr>\n";
		echo "						</table>\n";
		echo "					</td>\n";
		echo "				</tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "		<td align=\"left\" valign=\"top\"><img src=\images/spacer.gif\" width=\"15\" height=\"1\"></td>\n";
		echo "		<td width=\"50%\" align=\"left\" valign=\"top\">\n";
		echo "			<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"borders\">\n";
		echo "				<tr>\n";
		echo "					<td align=\"right\" valign=\"top\">\n";
		echo "						<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" class=\"displayList\">\n";
		echo "							<tr align=\"left\" valign=\"middle\"><td bgcolor=\"#FFFFFF\" class=\"headingCell1\">&nbsp;</td><td class=\"headingCell1\">Remove</td></tr>\n";

		if (is_array($ci->proxies) && !empty($ci->proxies))
		{
			foreach($ci->proxies as $proxy)
			{
//				echo "							<tr align=\"left\" valign=\"middle\"><td bgcolor=\"#FFFFFF\" class=\"headingCell1\">&nbsp;</td><td class=\"headingCell1\">Remove</td></tr>\n";
				echo "							<tr align=\"left\" valign=\"middle\">\n";
				echo "								<td bgcolor=\"#CCCCCC\">". $proxy->getName() ."</td>\n";
				echo "								<td width=\"8%\" valign=\"top\" bgcolor=\"#CCCCCC\" class=\"borders\" align=\"center\">\n";
				echo "									<input type=\"checkbox\" name=\"proxies[]\" value=\"".$proxy->getUserID()."\">\n";
				echo "								</td>\n";
				echo "							</tr>\n";
//				echo "							<tr align=\"left\" valign=\"middle\"><td colspan=\"2\"  class=\"headingCell1\">&nbsp;</td></tr>\n";
//				echo "							<tr align=\"left\" valign=\"middle\"><td colspan=\"2\" align=\"center\"><input type=\"submit\" name=\"removeProxy\" value=\"Remove Selected Proxies\"></td></tr>\n";
			}
		} else {
//			echo "							<tr align=\"left\" valign=\"middle\"><td bgcolor=\"#FFFFFF\" class=\"headingCell1\">&nbsp;</td><td class=\"headingCell1\">&nbsp;</td></tr>\n";
			echo "							<tr align=\"left\" valign=\"middle\"><td bgcolor=\"#CCCCCC\">This class currently has no proxies.</td><td width=\"8%\" valign=\"top\" bgcolor=\"#CCCCCC\" align=\"center\">&nbsp;</td></tr>\n";
//			echo "							<tr align=\"left\" valign=\"middle\"><td colspan=\"2\"  class=\"headingCell1\">&nbsp;</td></tr>\n";
		}
		echo "							<tr align=\"left\" valign=\"middle\"><td colspan=\"2\"  class=\"headingCell1\">&nbsp;</td></tr>\n";
		echo "							<tr align=\"left\" valign=\"middle\"><td colspan=\"2\" align=\"center\"><input type=\"submit\" name=\"removeProxy\" value=\"Remove Selected Proxies\"></td></tr>\n";
		echo "						</table>\n";
		echo "					</td>\n";
		echo "				</tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr><td colspan=\"3\">&nbsp;</td></tr>\n";
		echo "	<tr><td colspan=\"3\"><strong>To Add a Proxy:</strong><br>\n";
		echo "			<ol><li>Type the last name of the person you would like to add into the search box and click \"Search\".</li>\n";
		echo "				<li>A drop-down menu will appear with names that match your search. Choose a name from the menu and click the \"Add Proxy\" button.</li>\n";
		echo "				<li>The name of your proxy will appear on the right under \"Current Proxies\".</li>\n";
		echo "			</ol>\n";
		echo "	<tr><td colspan=\"3\">&nbsp;</td></tr>\n";
		echo "	<tr><td colspan=\"3\"><strong>To Remove a Proxy:</strong><br>\n";
		echo "			<ol><li>Under the \"Current Proxies\" list, check the box next to the name of the proxy you wish to remove.</li>\n";
		echo "				<li>Click the \"Delete Proxy\" button.</li>\n";
		echo "			</ol>\n";
		echo "		</td></tr>\n";
		echo "	<tr><td colspan=\"3\">&nbsp;</td></tr>\n";
		echo "	<tr><td colspan=\"3\" align=\"center\"> <a href=\"index.php?cmd=editClass&ci=". $ci->getCourseInstanceID() ."\" class=\"strong\">Return to Class</a></div></td></tr>\n";
		echo "	<tr><td colspan=\"3\"><img src=\images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
		echo "</table>\n";
		echo "</form>\n";
	}

	function displayReactivate($courses, $courseInstances, $nextPage, $request, $hidden_fields=null)
	{
		global $u, $g_permission;

		if($u->getRole() >= $g_permission['staff']) {	//use ajax class lookup
			//display selectClass
			$mgr = new ajaxManager('lookupClass', $nextPage, 'manageClasses', 'Reactivate Class', $hidden_fields);
			$mgr->display();
		}
		else {	//instructor class select
		
		    echo "<form action=\"index.php\" method=\"post\" name=\"reactivate\">\n";
	
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
	
		    echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
			echo "	<tr><td width=\"100%\"><img src=\images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>\n";
	
			echo "<tr><td height=\"14\" align=\"left\" valign=\"top\" class=\"helperText\">The classes you have taught in the past are listed below. Choose a class and reactivate it, then edit the class.</td></tr>\n";
			echo "	<tr><td width=\"100%\"><img src=\images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>\n";
			echo "	<tr>\n";
			echo "		<td height=\"14\" align=\"left\" valign=\"top\">\n";
			echo "			<table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\">\n";
			echo "				<tr align=\"left\" valign=\"top\">\n";
			echo "					<td width=\"35%\" align=\"left\" class=\"headingCell1\"><div align=\"center\">YOUR PAST CLASSES</div></td>\n";
			echo "					<td width=\"75%\"><div align=\"center\"></div></td>\n";
			echo "				</tr>\n";
			echo "			</table>\n";
			echo "		</td>\n";
			echo "	</tr>\n";
	
			echo "	<input type=\"hidden\" name=\"instructor\" value=\"" . $u->getUserID() ."\">\n";
	
			echo "	<tr>\n";
			echo "		<td align=\"left\" valign=\"top\">\n";
			echo "			<table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" class=\"borders\">\n";
			echo "				<tr>\n";
			echo "					<td>\n";
			echo "						<table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"5\" cellspacing=\"0\" class=\"displayList\">\n";
			echo "							<tr align=\"left\" valign=\"middle\" bgcolor=\"#CCCCCC\" class=\"headingCell1\">\n";
			echo "								<td width=\"15%\">&nbsp;</td>\n";
			echo "								<td>&nbsp;</td>\n";
			echo "								<td>Last Active</td>\n";
			echo "								<td width=\"20%\">Reserve List</td>\n";
			echo "								<td width=\"10%\">Select</td>\n";
			echo "							</tr>\n";
	
			for ($i=0;$i<count($courseInstances);$i++)
			{
				$ci = $courseInstances[$i];
				$rowClass = ($i % 2) ? "evenRow" : "oddRow";
				$selected_ci = ($request['ci_id']==$ci->getCourseInstanceID()) ? 'checked="true"' : '';
	
				echo "							<tr align=\"left\" valign=\"middle\" class=\"$rowClass\">\n";
				echo "								<td width=\"15%\">". $ci->course->displayCourseNo() ."</td>\n";
				echo "								<td>". $ci->course->getName() ."</td>\n";
				echo "								<td width=\"20%\"><div align=\"center\">". $ci->displayTerm() ."</div></td>\n";
				echo "								<td width=\"20%\" align=\"center\"><a href=\"javascript:openWindow('no_control=1&cmd=previewReservesList&ci=". $ci->getCourseInstanceID() ."','width=800,height=600');\">preview</a></td>\n";
				echo "								<td width=\"10%\" align=\"center\"><input type=\"radio\" name=\"ci\" value=\"". $ci->getCourseInstanceID() ."\" onClick=\"this.form.submit.disabled=false;\" ".$selected_ci."></td>\n";
				echo "							</tr>\n";
			}
	
			echo "							<tr align=\"left\" valign=\"middle\" bgcolor=\"#CCCCCC\" class=\"headingCell1\">\n";
			echo "								<td width=\"15%\">&nbsp;</td>\n";
			echo "								<td>&nbsp;</td>\n";
			echo "								<td>&nbsp;</td>\n";
			echo "								<td width=\"20%\">&nbsp;</td>\n";
			echo "								<td width=\"10%\">&nbsp;</td>\n";
			echo "							</tr>\n";
			echo "						</table>\n";
			echo "					</td>\n";
			echo "				</tr>\n";
			echo "			</table>\n";
			echo "		</td>\n";
			echo "	</tr>\n";
			echo "	<tr><td align=\"left\" valign=\"top\">&nbsp;</td></tr>\n";
			echo "	<tr><td align=\"left\" valign=\"top\" align=\"center\"><input type=\"submit\" name=\"submit\" value=\"Re-activate Class\" DISABLED onClick=\"this.form.cmd.value='$nextPage';\"></td></tr>\n";
			echo "	<tr><td align=\"left\" valign=\"top\"><img src=\images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
			echo "</table>\n";
	
			echo "</form>\n";
		}
	}
	
	
	function displayReactivateConfirm($ci, &$terms, &$departments, $hidden_fields=null) {
?>
	<script languge="JavaScript">
		function checkForm(frm) {
			var alertMsg = '';

			//if entering new course info, check for completeness
			if(document.getElementById('same_no').checked) {
				if(frm.department.value == '')
					alertMsg = alertMsg + 'Please choose a department.<br />';
				if(frm.course_number.value == '')
					alertMsg = alertMsg + 'Please enter a course number.<br />';
				if(frm.department.value == '')
					alertMsg = alertMsg + 'Please enter a course name.<br />';
			}
			
			if(alertMsg == '') {
				frm.submit();
			} 
			else {
				document.getElementById('alertMsg').innerHTML = alertMsg;
				return false;
			}
		}
		
		function toggleOptions() {
			if(document.getElementById('same_yes').checked) {
				document.getElementById('old_info_block').style.visibility = 'visible';
				document.getElementById('new_info_block').style.display = 'none';
			}
			else {
				document.getElementById('old_info_block').style.visibility = 'hidden';
				document.getElementById('new_info_block').style.display = '';				
			}
		}
	</script>
	
	<form action="index.php" method="post" name="reactivate_confirm">
	
<?php	self::displayHiddenFields($hidden_fields); ?>

		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
			<tr>
				<td width="100%"><img src="/images/spacer.gif" width="1" height="5"> </td>
			</tr>
			<tr>
				<td height="14" align="left" valign="top">
					<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
		    			<tr align="left" valign="top">
		      				<td width="35%" align="left" class="headingCell1"><div align="center">REACTIVATE A CLASS</div></td>
		      				<td width="75%"><div align="right">[ <a href="index.php?cmd=manageClasses">Cancel Reactivation</a> ]</div></td>
		    			</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td align="left" valign="top" class="borders" style="padding:10px;">				
	            	<p class="helperText">You have selected to reactivate <span class="strong"><?=$ci->course->department->getAbbr().' '.$ci->course->getCourseNo().' '.$ci->course->getName()?></span>.</p>
	            	
					<p>Reactivate class for:&nbsp;

<?php
		$first = true;
		foreach($terms as $term):
			$selected = $first ? 'checked="true"' : '';	//select the first option
?>
						<input type="radio" name="term" <?=$selected?> value="<?=$term->getTermID()?>" onclick="this.form.submit_confirmation.disabled=false" />&nbsp;<?=$term->getTerm()?> &nbsp;&nbsp;&nbsp; 
<?php	
			$first = false;
		endforeach;
?>

	            	</p>
	            	
	            	<p>Would you like to change the course number and name?</p>           	

            		<input id="same_yes" name="keep_selection" type="radio" value="yes" checked="checked" onclick="toggleOptions();" /> No.&nbsp;&nbsp;
            		<span id="old_info_block">
            			Section:&nbsp;
            			<input name="section" type="text" id="section" size="5" value="<?=$ci->course->getSection()?>" />
            		</span>
            		<br />
            		<input id="same_no" name="keep_selection" type="radio" value="no"  onclick="toggleOptions();" /> Yes.&nbsp;&nbsp;
            		<br />
            		<div id="new_info_block" style="margin-left:3em;">
						<span style="margin-right:30px;">Department:</span>
			            <select name="department">
        		    		<option value="">-- Select a Department -- </option>
<?php
		//build a list of departments
		foreach($departments as $dept):
			$selected = ($dept[0]==$ci->course->department->getDepartmentID()) ? ' selected="selected"' : '';		
?>
							<option value="<?=$dept[0]?>"<?=$selected?>><?=$dept[1].' '.$dept[2]?></option>
<?php	endforeach; ?>
						</select>
						<br />
						<span style="margin-right:7px;">Course Number:</span>
						<input name="course_number" type="text" id="course_number" size="5" value="<?=$ci->course->getCourseNo()?>" />
						<br />
						<span style="margin-right:60px;">Section:</span>
						<input name="section2" type="text" id="section2" size="5" value="<?=$ci->course->getSection()?>" />
						<br />
						<span style="margin-right:20px;">Course Name:</span>
						<input name="course_name" type="text" id="course_name" size="50" value="<?=$ci->course->getName()?>" />
					</div>
	            </td>
			</tr>
			<tr>
				<td align="center">
					<br />
					<input type="button" name="submit_confirmation" value="Re-activate Class" onclick="checkForm(this.form);"/>
				</td>
			</tr>
		</table>
	</form>
	
	<script languge="JavaScript">
		//set option defaults (on page load)
		toggleOptions();
	</script>
<?php
	}


	function displaySelectReservesToReactivate(&$ci, &$tree_walker, &$instructor_list, $hidden_fields=null, $loan_periods=null)
	{
		global $g_permission, $u, $calendar;
		
		echo "<form action=\"index.php\" method=\"POST\" name=\"reactivateList\">\n";

		if (isset($_REQUEST['term'])) {
			$term = new term($_REQUEST['term']);
			
			$course_activation_date = $term->getBeginDate();	
			$course_expiration_date = $term->getEndDate();
		}

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
		
		//set some info for new course
		if(empty($hidden_fields['course'])) {	//no course_id passed, new course dept, number and name should have been passed instead
			$d = new department($hidden_fields['dept_id']);
			
			//make a string label for the new course
			$new_course_name = $dept = $d->getAbbr().' '.$hidden_fields['course_number'].'-'.$hidden_fields['section'].' '.$hidden_fields['course_name'];
		}
		else {	//we kept the old course, use that info
			$new_course_name = $ci->course->displayCourseNo() . ' ' . $ci->course->getName();
		}
?>

		<script language="javascript">
		//<!--
			//resets reserve dates
			function resetDates(from, to) {
				document.getElementById('course_activation_date').value = from;
				document.getElementById('course_expiration_date').value = to;
			}
			
			function checkAll2(form, theState)
			{
				for (var i=0; i < form.elements.length; i++) {
					if (form.elements[i].type == 'checkbox' && form.elements[i].name == 'selected_reserves[]') {
						form.elements[i].checked = theState;
					}
				}
			}
		//-->
		</script>
		
<?php

		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr><td width=\"100%\" colspan=\"2\"><img src=\images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";
		echo "	<tr>\n";
		echo "		<td width=\"75%\" align=\"left\" valign=\"top\">\n";
		echo "			<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
		echo "				<tr>\n";
		echo "					<td colspan=\"3\" align=\"left\" valign=\"top\" class=\"helperText\">\n";
		if (isset($term))
			echo "						You have chosen the following class to reactivate for <span class=\"strong\">".$term->getTermName()." ".$term->getTermYear()."</span>.\n";
		echo "						Choose what options and readings you would like to retain for the new class:\n";
		echo "					</td>\n";
		echo "				</tr>\n";

		echo "				<tr><td width=\"50%\" align=\"left\" valign=\"top\">&nbsp;</td><td width=\"15\" align=\"left\" valign=\"top\">&nbsp;</td><td width=\"50%\" align=\"left\" valign=\"top\">&nbsp;</td></tr>\n";

		echo "				<tr><td colspan=\"3\" align=\"left\" valign=\"top\" class=\"courseTitle\">" . $new_course_name ."</td></tr>\n";
		echo "				<tr align=\"left\" valign=\"top\">\n";
		echo "					<td width=\"50%\"><span class=\"courseHeaders\">Last Active:</span> ". $ci->displayTerm() . " as " . $ci->course->displayCourseNo() . ' ' . $ci->course->getName() ."</td>\n";
		echo "					<td width=\"15\">&nbsp;</td><td width=\"50%\">&nbsp;</td>\n";
		echo "				</tr>\n";

		echo "				<tr align=\"left\" valign=\"top\"><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
		
		//only show activation/expiration dates override to staff
		if($u->getRole() >= $g_permission['staff']):
?>
				<tr valign="top">
					<td>
						<table width="100%" border="0" cellpadding="0" cellspacing="0">
							<tr valign="top">
								<td class="headingCell1" width="50%" align="center">DATES</td>
								<td width="50%" align="center">&nbsp;</td>
							</tr>
							<tr valign="top">
								<td colspan="3" class="borders" bgcolor="#CCCCCC">	
									<div style="padding:5px;">
										<strong>Active Dates</strong> (YYYY-MM-DD) &nbsp;&nbsp; [<a href="#" name="reset_dates" onclick="resetDates('<?=$course_activation_date?>', '<?=$course_expiration_date?>'); return false;">Reset dates</a>]
										<br />
										<div style="margin-left:10px;">
											<strong>From:</strong>&nbsp;<input type="text" id="course_activation_date" name="course_activation_date" size="10" maxlength="10" value="<?=$course_activation_date?>" /> <?=$calendar->getWidgetAndTrigger('course_activation_date', $course_activation_date)?> &nbsp; &nbsp; <strong>To:</strong>&nbsp;<input type="text" id="course_expiration_date" name="course_expiration_date" size="10" maxlength="10" value="<?=$course_expiration_date?>" /> <?=$calendar->getWidgetAndTrigger('course_expiration_date', $course_expiration_date)?>
										</div>
									</div>									
								</td>
							</tr>
						</table>
					</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>	
				<tr><td colspan="3">&nbsp;</td></tr>
<?php
		endif;	//end activate/expiration override
		
		
		echo "				<tr align=\"left\" valign=\"top\">\n";
		echo "					<td width=\"50%\" class=\"courseHeaders\">\n";
		echo "						<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		echo "							<tr align=\"left\" valign=\"top\">\n";
		echo "								<td width=\"50%\" class=\"headingCell1\">INSTRUCTORS\n";
		echo "									<!--If an instructor is reactivating the course, they are automatically defaulted to be the instructor for the new instance of the course. This field allows them to select any other instructors they would like to reactivate in addition to themselves. If login role=staff, then drop-down menu of all instructors appears in addition to previous instructors. Staff must select  one or more of the previous instructors or an instructor from the drop-down menu or form should return error \"You must select an instructor to reactivate this class.\"-->\n";
		echo "								</td>\n";
		echo "								<td>&nbsp;</td>\n";
		echo "							</tr>\n";
		echo "						</table>\n";
		echo "					</td>\n";
		echo "					<td width=\"15\">&nbsp;</td>\n";
		echo "					<td width=\"50%\">\n";
		echo "						<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		echo "							<tr align=\"left\" valign=\"top\">\n";
		echo "								<td width=\"50%\" class=\"headingCell1\">CROSSLISTINGS</td>\n";
		echo "								<td>&nbsp; </td>\n";
		echo "							</tr>\n";
		echo "						</table>\n";
		echo "					</td>\n";
		echo "				</tr>\n";

		echo "				<tr align=\"left\" valign=\"top\">\n";
		echo "					<td width=\"50%\" class=\"borders\">\n";
		echo "						<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" class=\"displayList\">\n";
		echo "							<tr align=\"left\" valign=\"middle\"><td bgcolor=\"#FFFFFF\" class=\"headingCell1\">&nbsp;</td><td class=\"headingCell1\">Select</td></tr>\n";

		if (is_array($ci->instructorList) && !empty($ci->instructorList)) {
			foreach($ci->instructorList as $ciInstructor) {
				echo "							<tr align=\"left\" valign=\"middle\">\n";
				echo "								<td bgcolor=\"#CCCCCC\">". $ciInstructor->getName() ."</td>\n";
				echo "								<td width=\"8%\" valign=\"top\" bgcolor=\"#CCCCCC\" class=\"borders\" align=\"center\">\n";
				
				//attempt to prevent the reactivating instructors from de-selecting themselves
				//so, if instructor id = current user id, do not show the checkbox
				if($u->getUserID() == $ciInstructor->getUserID()) {
					//add this instructor through hidden field
					echo '<input type="hidden" name="carryInstructor[]" value="'.$ciInstructor->getUserID().'" />';
				}
				else {
					//do normal checkbox
					echo "									<input type=\"checkbox\" name=\"carryInstructor[]\" value=\"". $ciInstructor->getUserID() ."\" checked>\n";
				}
				
				echo "								</td>\n";
				echo "							</tr>\n";
			}
		}

		if ($u->getRole() >= $g_permission['staff'])
		{
			echo "							<tr align=\"left\" valign=\"middle\" bgcolor=\"#CCCCCC\">\n";
			echo "								<td colspan=\"2\">\n";
			echo "									<select name=\"additionalInstructor\">\n";
			echo "										<option value=\"\">-- Select Additional Instructors --</option>\n";

			foreach ($instructor_list as $instr)
			{
				echo "										<option value=\"". $instr['user_id'] ."\">". $instr['full_name'] ."</option>\n";
			}

			echo "									</select>\n";
			echo "								</td>\n";
			echo "							</tr>\n";
		}

		echo "							<tr align=\"left\" valign=\"middle\"><td colspan=\"2\"  class=\"headingCell1\">&nbsp;</td></tr>\n";
		echo "						</table>\n";
		echo "					</td>\n";

		echo "					<td width=\"15\"><img src=\images/spacer.gif\" width=\"15\" height=\"1\"></td>\n";
		echo "					<td width=\"50%\">\n";
		echo "						<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" class=\"borders\">\n";
		echo "							<tr align=\"left\" valign=\"middle\">\n";
		echo "								<td bgcolor=\"#FFFFFF\" class=\"headingCell1\">&nbsp;</td>\n";
		echo "								<td class=\"headingCell1\">Select</td>\n";
		echo "							</tr>\n";

		//crosslistings
		if(empty($ci->crossListings)):	//no crosslistings
?>
								<tr>
									<td colspan="2" bgcolor="#CCCCCC">None</td>
								</tr>
<?php
		else:
			foreach($ci->crossListings as $crossListing):
?>
								<tr align="left" valign="middle">
									<td bgcolor="#CCCCCC">
										<?=$crossListing->displayCourseNo()?> -- <?=$crossListing->getName()?>
									</td>
									<td width="8%" valign="top" bgcolor="#CCCCCC" class="borders" align="center">
										<input type="checkbox" name="carryCrossListing[]" value="<?=urlencode(serialize(array($crossListing->getCourseID(), $crossListing->getSection())))?>" checked>
									</td>
								</tr>
<?php
			endforeach;
		endif;
		
		echo "							<tr align=\"left\" valign=\"middle\"><td colspan=\"2\"  class=\"headingCell1\">&nbsp;</td></tr>\n";
		echo "						</table>\n";
		echo "					</td>\n";
		echo "				</tr>\n";

		echo "				<tr><td>&nbsp;</td></tr>\n";
		echo "			</table>\n";
		
		echo "		</td>\n";
		echo "	</tr>\n";
		
/***********************************************
		//proxy reactivation checkbox
//this should probably be changed to a list with individual checkboxes (like for instructors) at a later date
//the handler will have to be changed in copyCourseInstance()
//for now, it will be a list of all proxies with a single checkbox -- dmitriy - 2005.10.13
		if(!empty($ci->proxies)):
?>
		<tr>
			<td class="borders" style="padding:5px;">
				<strong>Proxies:</strong>
				<br />
				<div style="margin-left:15px;">
<?php
			//store all IDs
			$carryProxy = array();
		
			foreach($ci->proxies as $proxy) {
				//show name
				echo $proxy->getName().'<br />';
				//add the id to the array
				$carryProxy[] = $proxy->getUserID();
			}
		
			//prepare array for form
			$carryProxy = urlencode(serialize($carryProxy));	
?>
				</div>	
				<p />
				<input type="checkbox" name="carryProxy" value="<?=$carryProxy?>" /> Restore Proxies
			</td>
		</tr>
		<tr><td>&nbsp;</td></tr>
<?php
		endif;	//end proxy block
***********************************************/


		echo "	<tr>\n";
		echo "		<td colspan=\"2\">\n";
		echo "			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		echo "				<tr align=\"left\" valign=\"top\">\n";
		echo "					<td class=\"headingCell1\"><div align=\"center\">COURSE MATERIALS</div></td>\n";
		echo "					<td width=\"75%\" align=\"right\"><div align=\"right\" class=\"small\"><a href=\"javascript:checkAll2(document.forms.reactivateList, 1)\">check all</a> | <a href=\"javascript:checkAll2(document.forms.reactivateList, 0)\">uncheck all</a></div></td>\n";
		echo "				</tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td colspan=\"2\">\n";
		
		
		echo '			<ul style="list-style:none; padding-left:0px; margin:0px;">';

		//begin displaying individual reserves
		//loop
		$prev_depth = 0;
		foreach($tree_walker as $leaf) {
			//close list tags if backing out of a sublist
			if($prev_depth > $tree_walker->getDepth()) {
				echo str_repeat('</ol></li>', ($prev_depth-$tree_walker->getDepth()));
			}
			
		
			$reserve = new reserve($leaf->getID());	//init a reserve object
			$reserve->getItem();
			
			//set some additional info
			
			$reserve->selected = true;	//select all reserves by default
			$reserve->additional_info = '';
			
			if($reserve->item->isPhysicalItem() && !is_null($loan_periods)) {
				$reserve->additional_info .= '<br /><span class="itemMetaPre">Requested Loan Period:</span>';
				$reserve->additional_info .= '<select name="requestedLoanPeriod['.$reserve->getReserveID().']">';
				foreach($loan_periods as $loan_period) {
					$selected = ($loan_period['default'] == 'true') ? 'selected="selected"' : '';
					$reserve->additional_info .= '<option value="'.$loan_period['loan_period'].'" '.$selected.'>'.$loan_period['loan_period'].'</option>';
				}
				$reserve->additional_info .= '</select>';
			}
						
			$rowStyle = ($rowStyle=='oddRow') ? 'evenRow' : 'oddRow';	//set the style

			//display the info
			echo '<li>';
			if($reserve->item->isPhysicalItem() && $reserve->item->isPersonalCopy()):	//if physical personal item
				$reserve->additional_info = '<br /><span class="failedText">Personal items cannot be reactivated. Please contact your reserves desk for assistance.</span>';
?>
				<div class="<?=$rowStyle?>">
					<?php self::displayReserveInfo($reserve, 'class="metaBlock-wide"'); ?>
					<!-- hack to clear floats -->
					<div style="clear:both;"></div>
					<!-- end hack -->
				</div>			
<?php
			else:
				if($reserve->item->isHeading()) {
					$rowStyle ='headingCell2'; 
				}
?>
				<div class="<?=$rowStyle?>">
					<div class="checkBox-right">
						<input type="checkbox" checked="true" name="selected_reserves[]" value="<?=$reserve->getReserveID()?>" />
					</div>
					<?php self::displayReserveInfo($reserve, 'class="metaBlock-wide"'); ?>
					<!-- hack to clear floats -->
					<div style="clear:both;"></div>
					<!-- end hack -->
				</div>			
<?php
			endif;
			
			//start sublist or close list-item?
			echo ($leaf->hasChildren()) ? '<ul style="list-style:none;">' : '</li>';
			
			$prev_depth = $tree_walker->getDepth();
		}
		echo str_repeat('</ul></li>', ($prev_depth));	//close all lists
		
		echo '			</ul>';
		
		
		echo "		</td>\n";
		echo "	</tr>\n";
			
		echo "	<tr><td colspan=\"3\"><img src=\images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
		echo "	<tr><td colspan=\"3\" align=\"center\" class=\"strong\"><input type=\"submit\" name=\"Submit\" value=\"Reactivate Class\"><br /><small>Note: Please be patient, large classes may take several minutes to process.</small></td></tr>\n";
		echo "	<tr><td colspan=\"3\"><img src=\images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
		echo "</table>\n";

		echo "</form>\n";
	}


	function displaySuccess($page, $ci)
	{

		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n"
		.	 "	<tbody>\n"
		.	 "		<tr><td width=\"100%\"><img src=\images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n"
		.	 "		<tr>\n"
	    .	 "			<td align=\"left\" valign=\"top\" class=\"borders\">\n"
	    .	 "				<table width=\"50%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"5\">\n"
		.	 "					<tr><td><strong>You have successfully added a class.  You May now:</strong></td></tr>\n"
		.	 "					<tr><td>\n"
		.	 "				<ul><li><a href=\"index.php?cmd=displaySearchItemMenu&ci=". $ci->getCourseInstanceID() ."\">Add materials to this class</a>		<br>\n"
		.	 "							<br>\n"
		.	 "						    <li><a href=\"index.php?cmd=editClass&ci=". $ci->getCourseInstanceID() ."\">Go to this class.</a></li>\n"
		.	 "							<li><a href=\"index.php?cmd=reactivateClass\">Reactivate another class.</a></li>\n"
		.	 "							<li><a href=\"index.php?cmd=createClass\">Create a New Class.</a></li>\n"
		.	 "						</ul>\n"
		.	 "					</td></tr>\n"
		.	 "				</table>\n"
		.	 "			</td>\n"
		.	 "		</tr>\n"
		.	 "		<tr><td><img src=\images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n"
		.	 "	</tbody>\n"
		.	 "</table>\n"
		;
	}
	
	function displayCreateClass($departments, $terms, $next_cmd, $request, $hidden_fields=null)
	{
		global $u, $g_permission, $calendar;

		echo "\n<script language=\"JavaScript\">\n";
		echo "	function activateDates(frm, activateDate, expirationDate)\n";
		echo "	{\n";
		echo "		frm.activation_date.value = activateDate;\n";
		echo "		frm.expiration_date.value = expirationDate;\n";
		echo "	}\n";
		echo "function validate(form)";
		echo "	{";

		echo "		var fieldCount = 0;";
		echo "		var requiredFields=true;";
		echo "		var errorMsg='The following fields are required: ';";

		echo "		if (!(form.department.value)) {";
		echo "			requiredFields=false;";
		echo "			errorMsg = errorMsg + 'Department';";
		echo "			fieldCount++;";
		echo "		}";

		echo "		if (!(form.course_number.value)) {";
		echo "			requiredFields=false;";
		echo "			if (fieldCount>0) errorMsg = errorMsg + ', ';";
		echo "			errorMsg = errorMsg + 'Course Number';";
		echo "			fieldCount++;";
		echo "		}";

		echo "		if (!(form.course_name.value)) {";
		echo "			requiredFields=false;";
		echo "			if (fieldCount>0) errorMsg = errorMsg + ', ';";
		echo "			errorMsg = errorMsg + 'Course Name';";
		echo "			fieldCount++;";
		echo "		}";

		echo "		var term_choice = false;";
		echo "		for (counter = 0; counter < form.term.length; counter++)";
		echo "		{";
		echo "			if (form.term[counter].checked)";
		echo "				term_choice = true; ";
		echo "		}";
		echo "		if (!term_choice) {";
		echo "			requiredFields=false;";
		echo "			if (fieldCount>0) errorMsg = errorMsg + ', ';";
		echo "			errorMsg = errorMsg + 'Semester';";
		echo "			fieldCount++;";
		echo "		}";
		
		echo "		if (!(form.selected_instr.value)) {";
		echo "			requiredFields=false;";
		echo "			if (fieldCount>0) errorMsg = errorMsg + ', ';";
		echo "			errorMsg = errorMsg + 'Instructor';";
		echo "			fieldCount++;";
		echo "		}";

		echo "		if (!(form.activation_date.value)) {";
		echo "			requiredFields=false;";
		echo "			if (fieldCount>0) errorMsg = errorMsg + ', ';";
		echo "			errorMsg = errorMsg + 'Activation Date';";
		echo "			fieldCount++;";
		echo "		}";

		echo "		if (!(form.expiration_date.value)) {";
		echo "			requiredFields=false;";
		echo "			if (fieldCount>0) errorMsg = errorMsg + ', ';";
		echo "			errorMsg = errorMsg + 'Expiration Date';";
		echo "			fieldCount++;";
		echo "		}";

		echo "		if (requiredFields) {";
		echo "			return true;";
		echo "		} else {";
		echo "			alert (errorMsg);";
		echo "			return false;";
		echo "		}		";

		echo "	}";

		echo "</script>\n";

	    echo "<form action=\"index.php\" method=\"post\" name=\"frmClass\">\n";
	
	
		if (!isset($request['term'])) {
			$request['term'] = $terms[0]->getTermID();
			$request['activation_date'] = $terms[0]->getBeginDate();
			$request['expiration_date'] = $terms[0]->getEndDate();
		}
		
		if (isset($request['course_number']))
			$course_number = $request['course_number'];
		else 
			$course_number = '';
			
		if (isset($request['course_name']))
			$course_name = stripslashes($request['course_name']);
		else 
			$course_name = '';
			
		if (isset($request['section']))
			$section = $request['section'];
		else 
			$section = '';
	    
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

		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr><td width=\"100%\"><img src=\images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>\n";
		echo "	<tr>\n";
		echo "		<td>\n";
		echo "			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		echo "				<tr align=\"left\" valign=\"top\"><td class=\"headingCell1\" align=\"center\">CLASS DETAILS</td><td>&nbsp;</td></tr>\n";

		/*
		<!--The \"Show All Editable Item\" Links appears by default when this
		page is loaded if some of the metadata fields for the document are blank.
		Blank fields will be hidden upon page load. -->
		*/


		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td align=\"left\" valign=\"top\" class=\"borders\">\n";
		echo "			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\">\n";
		echo "				<tr valign=\"middle\">\n";
		echo "					<td width=\"35%\" height=\"30\" align=\"right\" bgcolor=\"#CCCCCC\" align=\"right\" class=\"strong\">Department:</td>\n";
		echo "						<td align=\"left\">\n";
		echo "							<select name=\"department\">\n";
		echo "								<option value=''>-- Select a Department --</option>\n";
			foreach ($departments as $dept) { 
        		($dept[0] == $request['department']) ? $dept_selected = "selected" : $dept_selected = "";
        		echo "<option $dept_selected value=\"". $dept[0] ."\">". $dept[1] ." " . $dept[2] ."</option>\n"; 
        	}
		echo "							</select>\n";
		echo "						</td>\n";
		echo "				</tr>\n";

		echo "				<tr valign=\"middle\">\n";
		echo "					<td width=\"35%\" height=\"31\" align=\"right\" bgcolor=\"#CCCCCC\" align=\"right\" class=\"strong\">Course Number</td>\n";
		echo "					<td align=\"left\"><input name=\"course_number\" value=\"$course_number\" type=\"text\" size=\"5\"></td>\n";
		echo "				</tr>\n";

		echo "				<tr valign=\"middle\">\n";
		echo "					<td align=\"right\" bgcolor=\"#CCCCCC\" align=\"right\" class=\"strong\">Section:</td>\n";
		echo "					<td align=\"left\"><input name=\"section\" value=\"$section\" type=\"text\" size=\"4\"></td>\n";
		echo "				</tr>\n";

		echo "				<tr valign=\"middle\">\n";
		echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\" align=\"right\" class=\"strong\">Course Name:</td>\n";
		echo "					<td align=\"left\"><input name=\"course_name\" value=\"$course_name\" type=\"text\" size=\"50\"></td>\n";
		echo "				</tr>\n";

		echo "				<tr valign=\"middle\">\n";
		echo "					<td align=\"right\" bgcolor=\"#CCCCCC\" class=\"strong\">Semester:</td>\n";
		echo "					<td><table><tr>\n";

		foreach($terms as $t)
		{
			($t->getTermID() == $request['term']) ? $term_checked = "checked" : $term_checked = "";
			echo "								<td>\n";
			echo "									<input type=\"radio\" name=\"term\" $term_checked value=\"". $t->getTermID() ."\" onClick=\"activateDates(this.form, '". $t->getBeginDate() ."','". $t->getEndDate() ."');\">". $t->getTerm() ."\n";
			echo "								</td>\n";
		}	
		
		echo "					</tr></table></td>";
		echo "				</tr>\n";

		if ($u->getRole() >= $g_permission['staff'])
		{
			echo "				<tr valign=\"middle\">\n";
			echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\" align=\"right\" class=\"strong\">Instructor:</td>\n";
			echo "					<td align=\"left\">\n";

			//ajax lookup
			$mgr = new ajaxManager('lookupUser', null, null, null, null, false, array('min_user_role'=>3, 'field_id'=>'selected_instr'));
			$mgr->display();
			
			echo "					</td>\n";
			echo "				</tr>\n";

			echo "				<tr valign=\"middle\">\n";
			echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\" align=\"right\" class=\"strong\">Activation Date: (yyyy-mm-dd)</td>\n";
?>

	<td><input type="text" id="activation_date" name="activation_date" size="10" maxlength="10" value="<?=$request['activation_date']?>" /> <?=$calendar->getWidgetAndTrigger('activation_date', $request['activation_date'])?></td>

<?php
			echo "				</tr>\n";

			echo "				<tr valign=\"middle\">\n";
			echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\" align=\"right\" class=\"strong\">Expiration Date: (yyyy-mm-dd)</td>\n";	
?>

	<td><input type="text" id="expiration_date" name="expiration_date" size="10" maxlength="10" value="<?=$request['expiration_date']?>" /> <?=$calendar->getWidgetAndTrigger('expiration_date', $request['expiration_date'])?></td>

<?php
			echo "				</tr>\n";
		} else { 
			echo "			<input type=\"hidden\" name=\"selected_instr\" value=\"". $u->getUserID() . "\">\n";
			echo "			<input type=\"hidden\" name=\"activation_date\" value=\"". $terms[0]->getBeginDate() ."\">\n";
			echo "			<input type=\"hidden\" name=\"expiration_date\" value=\"". $terms[0]->getEndDate() ."\">\n";
		}

		echo "				<input type=\"hidden\" name=\"enrollment\" value=\"public\">\n";
		//echo "				<tr valign=\"middle\">\n";
		//echo "					<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" class=\"strong\">Enrollment:</td>\n";
		//echo "					<td align=\"left\"><input type=\"radio\" name=\"enrollment\" value=\"public\"><font color=\"#009900\"><strong>PUBLIC</strong></font></td>\n";
		//echo "					<td><input type=\"radio\" name=\"enrollment\" value=\"private\"><font color=\"#CC0000\"><strong>MODERATED</strong></font></td>\n";
		//echo "				</tr>\n";

		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr><td align=\"center\"><input type=\"submit\" name=\"Submit\" value=\"Create Course\" onClick=\"this.form.cmd.value='".$next_cmd."';javascript:return validate(document.forms.frmClass);\"></td></tr>\n";
		echo "	<tr><td><img src=\images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
		echo "</table>\n";
		echo "</form>\n";

	}

	/**
	 * @return void
	 * @param int $courseInstances
	 * @desc Displays the Users Active Classes this is the first screen of the addReserve
	 * 		expected next steps
	 *			addReserve::displaySearchItemMenu
	 *			manageClasses::createClass
	 *			manageClasses::reactivateClass
	*/
	function displaySelectClasses($courseInstances)
	{
		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tbody>\n";
		echo "		<tr><td width=\"100%\"><img src=\images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";

		if (is_array($courseInstances) && !empty($courseInstances))
		{

			echo "      <tr>\n";
	        echo "          <td height=\"14\"><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
	        echo "              <tr align=\"left\" valign=\"top\">\n";
	        echo "                <td height=\"14\" class=\"headingCell1\"><div align=\"center\">YOUR CLASSES</div>\n";
	        echo "                </td>\n";
	        echo "                <td width=\"75%\"><div align=\"center\"><font color=\"#CC0000\">Click on a class name to add reserves.</font></div></td>\n";
	        echo "              </tr>\n";
	        echo "            </table>\n";
	        echo "          </td>\n";
	        echo "		</tr>\n";

	        echo "		<tr>\n";
	        echo "			<td align=\"left\" valign=\"top\" class=\"borders\">\n";
	        echo "				<table width=\"100%\" border=\"0\" cellpadding=\"5\" cellspacing=\"0\" class=\"displayList\">\n";

	        $i=0;
			foreach($courseInstances as $ci)
			{
				$rowClass = "oddRow";
				if ($i++ % 2) $rowClass = "evenRow";
				echo "				<tr align=\"left\" valign=\"middle\" class=\"$rowClass\">\n";
				echo "					<td width=\"15%\">" . $ci->course->displayCourseNo() ."</td>\n";
				echo "					<td width=\"40%\"><a href=\"index.php?cmd=displaySearchItemMenu&ci=" . $ci->getCourseInstanceID() . "\">" . $ci->course->getName() . "</a></td>\n";
				echo "					<td width=\"10%\">" . $ci->getStatus() . "</td>\n";
				echo "					<td width=\"15%\" NOWRAP>" . $ci->displayTerm() . "</td>\n";
				echo "					<td width=\"25%\" NOWRAP>" . $ci->getActivationDate() . " - " . $ci->getExpirationDate() . "</td>\n";
				echo "				</tr>\n";
			}
				echo "			</table>\n";
				echo "		</td>\n";
				echo "	</tr>\n";

				echo "	<tr><td colspan=\'4\">&nbsp;</td></tr>\n";
				echo "	<tr>\n";
				echo "		<td align=\"left\" valign=\"top\">\n";
				echo "			<p>\n";
				echo "				<strong>Don't see the class you're looking for?</strong><br>\n";
				echo "				&gt;&gt;<a href=\"index.php?cmd=reactivateClass\">Reactivate an old class</a><br>\n";
				echo "				&gt;&gt;<a href=\"index.php?cmd=createClass\"> Create a new class</a>\n";
				echo "			</p>\n";
				echo "		</td>\n";
				echo "	</tr>\n";

		} else {
			echo "		<tr><td>You have no associated classes.  Please select the Manage Classes tab.</td></tr>\n";
		}

		echo "		<tr><td align=\"left\" valign=\"top\"><img src=\images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
		echo "	</tbody>\n";
		echo "</table>\n";
	}

	function displaySelectInstructor($user)
	{
		$user->selectUserForAdmin('instructor', 'selectClass');
	}
	
	function displaySearchForClass($deptList, $request)
	{
		global $u;
        global $g_permission;

		echo '<table width="95%" border="0" cellspacing="0" cellpadding="0" align="center">';
		echo '	<tr>';
		echo '    	<td width="100%"><img src=images/spacer.gif" width="1" height="5"></td>';
		echo '	</tr>';
		echo '    <tr>';
		echo '    	<td align="left" valign="top">';
		echo '    	<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">';
		echo '        	<tr align="left" valign="top" class="headingCell1">';
		echo '            	<td width="50%">Search by Instructor</td>';
		echo '                <td width="50%">Search by Department</td>';
		echo '            </tr>';
		echo '			<tr>';
		echo '        		<td width="50%" class="borders"><div align="center"><br>';
		echo '<FORM METHOD=POST ACTION="index.php">';
		//echo '<INPUT TYPE="HIDDEN" NAME="cmd" VALUE="addClass">';
		//echo "<input type=\"hidden\" name=\"cmd\" value=\"$cmd\">\n";
		echo '<INPUT TYPE="HIDDEN" NAME="cmd" VALUE="searchForClass">';
		/*
		echo '                	    <select name="prof">';
		echo '                    	  <option value="" selected>Choose an Instructor ';
		foreach($instructorList as $instructor)
		{
			echo '<option value="' . $instructor['username'] . '">' . $instructor['full_name'] . '</option>';
		}

		echo '                    	</select>';
		*/
		$selectClassMgr = new lookupManager('','lookupInstructor', $u, $request);
		$selectClassMgr->display();
		//echo '                    	<br>';
		echo '                    	<br>';
		echo '                    	<input type="submit" name="Submit2" value="Lookup Classes" onClick="this.form.cmd.value=\'addClass\'">';
		echo '                    	<br>';
		echo '                    	<br>';
		echo '                    	<br>';
		echo '</form>';
		echo '              	</div></td>';
		echo '              	<td width="50%" align="left" valign="top" class="borders"><div align="center"><br>';
		echo '<FORM METHOD=POST ACTION="index.php">';
		echo '<INPUT TYPE="HIDDEN" NAME="cmd" VALUE="addClass">';
		echo '                	    <select name="dept">';
		echo '                      	<option value="" selected>-- Choose a Department --</option>';
		while ($row = $deptList->fetchRow())
		{
                echo '<OPTION VALUE="'.$row[0].'">'.$row[1].'</OPTION>';
        }
        echo '                    	</select>';
        echo '                    	<br>';
        echo '                    	<br>';
        echo '                    	<input type="submit" name="Submit" value="Lookup Classes">';
        echo '                    	<br>';
        echo '</form>';
        echo '              	</div></td>';
        //echo '			/td>';
        echo '			</tr>';
        if ($u->getRole() >= $g_permission['instructor']) {
			echo '<tr><td colspan="2">&nbsp;</td></tr>';
			echo '<tr><td colspan="2"><blockquote><strong>Instructors:</strong> Adding a class through this page will only allow you to see that class as a student would. Classes that you are teaching show up automatically in your MyReserves list with a pencil icon next to them. If you do not see your class under your MyReserves list you may:<br>';
			echo "				&gt;&gt; <a href=\"index.php?cmd=reactivateClass\">Reactivate a class you have used in the past.</a><br>\n";
			echo "				&gt;&gt;<a href=\"index.php?cmd=createClass\"> Create a new class</a></blockquote>\n";
			echo "</td></tr>\n";
		} else {
			echo '<tr><td>&nbsp;</td></tr>';
		}
        echo '		</table>';
        echo '		</td>';
        echo '	</tr>';

        echo '    <tr>';
        echo '    	<td><img src=images/spacer.gif" width="1" height="15"></td>';
        echo '	</tr>';
        echo '</form>';
        echo '</table>';
	}

	function displayAddClass($courseList, $searchParam)
	{
		$terms = new terms();
        $currentTerm = $terms->getCurrentTerm();


		echo '<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">';
		echo '	<tr> ';
		echo '    	<td width="100%" colspan="2"><img src=images/spacer.gif" width="1" height="5"></td>';
		echo '	</tr>';
		echo '    <tr> ';
		echo '    	<td width="50%" c><span class="strong">';
		if (is_a($searchParam,"instructor")) {
			echo 'Instructor: '.$searchParam->getName();
		} elseif (is_a($searchParam,"department")) {
			echo 'Department: '.$searchParam->getAbbr();
		}
		echo '</span></td>';
		echo '		<td width="50%" c><div align="right" class="strong">'.$currentTerm->term_name . " " . $currentTerm->term_year.'</div></td>';
		echo '	</tr>';
		echo '    <tr> ';
		echo '    	<td colspan="2" c>&nbsp;</td>';
		echo '	</tr>';
		echo '	<tr> ';
		echo '    	<td colspan="2" c>';
		echo '    	<table width="100%" border="0" cellspacing="0" cellpadding="0">';
		echo '        	<tr align="left" valign="top"> ';
		echo '            	<td class="headingCell1"><div align="center">SELECT CLASSES</div></td>';
		echo '                <td width="75%">&nbsp;</td>';
		echo '			</tr>';
		echo '		</table>';
		echo '		</td>';
		echo '	</tr>';
		echo '    <tr> ';
		echo '    	<td colspan="2" align="left" valign="top">';
		echo '    	<table width="100%" border="0" cellpadding="2" cellspacing="0" class="displayList">';
		$rowNumber = 0;
		for ($i=0; $i<count($courseList); $i++)
		{
			$rowClass = "oddRow";
			if ($rowNumber++ % 2) {$rowClass = "evenRow";}
			echo '        	<tr align="left" valign="middle" class="'.$rowClass.'">';
			echo '            	<td width="20%">'.$courseList[$i]->displayCourseNo().'</td>';
			echo '              <td width="35%">'.$courseList[$i]->getName().'</td>';

			echo '              <td width="30%"><div align="center"><a href="index.php?cmd=addStudent&aID='.$courseList[$i]->getCourseAliasID().'">click here to add</a></div></td>';
			echo '			</tr>';
		}
		echo '		</table>';
		echo '		</td>';
		echo '	</tr>';

		echo '    <tr> ';
		echo '    	<td colspan="2"><img src=images/spacer.gif" width="1" height="15"></td>';
		echo '	</tr>';
		echo '</table>';
	}

	function displayRemoveClass($user)
	{
		global $u;
        global $g_permission;


		echo '<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">';
		echo('<FORM METHOD=POST ACTION="index.php">');
		echo('<INPUT TYPE="HIDDEN" NAME="cmd" VALUE="removeStudent">');
		echo '	<tr> ';
		echo '		<td width="100%"><img src=images/spacer.gif" width="1" height="5"></td>';
		echo '	</tr>';
		echo '    <tr> ';
		echo '    	<td c>';
		echo '    	<table width="100%" border="0" cellspacing="0" cellpadding="0">';
		echo '        	<tr align="left" valign="top"> ';
		echo '            	<td class="headingCell1"><div align="center">YOUR CLASSES</div></td>';
		echo '                <td width="75%">&nbsp;</td>';
		echo '			</tr>';
		echo '		</table>';
		echo '		</td>';
		echo '	</tr>';
		echo '    <tr> ';
		echo '    	<td align="left" valign="top">';
		echo '    	<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" class="borders">';
		echo '        	<tr> ';
		echo '            	<td align="left" valign="top"> ';
		echo '                <table width="100%" border="0" align="center" cellpadding="2" cellspacing="0" class="displayList">';
		echo '                	<tr align="left" valign="middle" bgcolor="#CCCCCC" class="headingCell1"> ';
		echo '						<td width="18%">&nbsp;</td>';
		echo '                      	<td width="71%">&nbsp;</td>';
		echo '                      	<td width="11%">Select</td>';
		echo '                    </tr>';
		$numCourseInstances = count($u->courseInstances);
		if ($numCourseInstances > 0){
			$rowNumber = 0;
			for ($j=0;$j<$numCourseInstances;$j++){
				$ci = $u->courseInstances[$j];
				$rowClass = "oddRow";
				if ($rowNumber++ % 2) {$rowClass = "evenRow";}
				echo '<tr align="left" valign="middle" class="'.$rowClass.'">';
				echo '			<td width="18%">' . $ci->course->displayCourseNo() . '</td>';
				echo '			<td width="71%">'.$ci->course->getName() . '</td>';
				echo '			<td width="11%" align="center"><input type="checkbox" name="alias['.$ci->course->getCourseAliasID().']" value="'.$ci->course->getCourseAliasID().'"></td>';
				echo "</tr>";
			}
		} else {
			echo "<tr><td align=\"center\">There are no classes to remove</td></tr>";
		}
		echo '                </table>';
		echo '                </td>';
		echo '			</tr>';
		echo '		</table>';
		echo '		</td>';
		echo '	</tr>';
		if ($u->getRole() >= $g_permission['proxy']) {
			echo '<tr><td>&nbsp;</td></tr>';
			echo '<tr><td><strong>Please note:</strong> If you are a proxy or instructor in a class, you may not remove it from your MyReserves list.';
			if ($u->getRole() >= $g_permission['instructor']) {
						echo '<br>If you would like to completely remove a class from ReservesDirect, please contact your reserves staff.';
			}
			echo '</td></tr>';
		} else {
			echo '<tr><td>&nbsp;</td></tr>';
		}
		echo '    <tr>';
		echo '    	<td>&nbsp;</td>';
		echo '	</tr>';
		echo '    <tr> ';
		echo '    	<td><div align="center"><img src=images/spacer.gif" width="1" height="15"><input type="submit" name="deleteAlias" value="Remove Selected Classes"></div></td>';
		echo '	</tr>';
		echo '</form>';
		echo '</table>';
	}
	
	
	function displayClassEnrollment(&$ci=null) {
		if($ci instanceof courseInstance):	//CI set, show enrollment
			if(!($ci->course instanceof course)) {	//get course
				$ci->getPrimaryCourse();
			}
			
			$student_count = count($ci->students);
?>
		<div>
			<div class="headingCell1" style="width:33%;">Class Enrollment</div>
			<div class="borders" style="padding:5px;">
				Enrollment for <strong><?=$ci->course->displayCourseNo() . " " . $ci->course->getName()?></strong>
				<br />
				<?=$student_count?> Total Enrolled Students
				<ul>				
<?php		for($x=0; $x<$student_count; $x++): ?>
				<li><?=$ci->students[$x]->getName();?></li>				
<?php		endfor; ?>
				</ul>
			</div>
		</div>
		
<?php
		else :	//no CI, show class lookup
			//ajax lookup
			$mgr = new ajaxManager('lookupClass', 'viewEnrollment', 'manageClasses', 'View Enrollment');
			$mgr->display();
		endif;
	}


	function old_displayClassEnrollment ($cmd, $u, $request)
	{

		switch ($cmd) {
			case 'viewEnrollment':
				echo "<form action=\"index.php\" method=\"POST\">\n";
				echo "<input type=\"hidden\" name=\"cmd\" value=\"$cmd\">\n";

				$tableHeading="VIEW STUDENT ENROLLMENT FOR:";
				$selectClassMgr = new lookupManager($tableHeading, 'lookupClass', $u, $request);
				$selectClassMgr->display();
				if (isset($_REQUEST['ci']) && $_REQUEST['ci'] && $_REQUEST['ci'] != null)
				{
					echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";

        			echo '<tr><td>&nbsp;</td></tr>';
					echo "	<tr><td valign=\"top\" align=\"center\"><input type=\"submit\" name=\"performAction\" value=\"View Enrollment\" onClick=\"this.form.cmd.value='processViewEnrollment';\"></td></tr>\n";
				}
				else {
					echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
					echo "	<tr><td valign=\"top\" align=\"center\"><input type=\"submit\" name=\"performAction\" value=\"View Enrollment\" DISABLED></td></tr>\n";
				}
				echo "	<tr><td align=\"left\" valign=\"top\"><img src=\"images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
				echo "</table>\n";
				echo "</form>\n";

			break;

			case 'processViewEnrollment':
				$ci=new courseInstance($_REQUEST['ci']);
				$ci->getStudents();
				echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
				echo '<tr><td width="100%"><img src="images/spacer.gif" width="1" height="5"></td></tr>';
				echo '<tr>';
				echo 	'<td align="left" valign="top" class="helperText">'.count($ci->students).' Total Enrolled Students<br><br>';

				for ($i=0; $i<count($ci->students); $i++)
				{
					echo '<span class="strong">'.$ci->students[$i]->getName().'</span>';
					echo '<br>';
				}



				echo 	'</td>';
        		echo '</tr>';
        		echo '<tr><td align="left" valign="top">&nbsp;</td></tr>';
        		echo '<tr><td>&gt;&gt;<a href="index.php?cmd=manageClasses">Return to &quot;Manage Classes&quot; home</a></td></tr>';
        		echo '<tr><td align="left" valign="top">&nbsp;</td></tr>';
				echo "</table>\n";
			break;

		}

	}

	function displayDeleteClass ($cmd, $u, $request) {
		//display selectClass
		$mgr = new ajaxManager('lookupClass', 'confirmDeleteClass', 'manageClasses', 'Delete Class');
		$mgr->display();
		//show warning
		echo '<div align="center" class="strong"><font color="#CC0000">CAUTION! Deleting a class cannot be undone!</font></div>';
	}

	function displayConfirmDelete ($sourceClass) {

		echo '<input type="hidden" name="ci" value="'.$sourceClass->getCourseInstanceID().'">';

		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo '<tr><td width="100%"><img src="images/spacer.gif" width="1" height="5"></td></tr>';

		echo '<tr>';
		echo 	'<td align="left" valign="top">';
        echo 	  '<p>';
        echo 		'<span class="courseTitle">';
        echo 			$sourceClass->course->displayCourseNo().' -- '.$sourceClass->course->getName().' ('.$sourceClass->displayTerm().')</span>,';
        echo 			' <span class="helperText">Instructors: ';

        	for($i=0;$i<count($sourceClass->instructorList);$i++) {
				if ($i>0)
					echo ',&nbsp;';
				echo $sourceClass->instructorList[$i]->getFirstName().'&nbsp;'.$sourceClass->instructorList[$i]->getLastName();
			}

		echo 			'</span></p></td></tr>';
		echo '<tr><td>&nbsp;</td></tr>';
		echo '<tr>';
				echo 	'<td align="left" valign="top"><p><span class="helperText">'.count($sourceClass->students).' Total Enrolled Students<br><br>';

				for ($i=0; $i<count($sourceClass->students); $i++)
				{
					echo '<span class="strong">'.$sourceClass->students[$i]->getName().'</span>';
					echo '<br>';
				}



				echo 	'</p></td>';
        		echo '</tr>';
        echo '<tr><td>&nbsp;</td></tr>';
        echo '<tr>';
		echo 	'<td align="left" valign="top">';
        echo 	  '<p>';
        echo 		'<span class="failedText">';
        echo 'Are you sure you want to delete this class?';
        echo 		'</span>';
        echo 	  '</p>';

        echo 	  '<p>';
        echo 		'&gt;&gt;<a href="index.php?cmd=deleteClassSuccess&ci='.$sourceClass->getCourseInstanceID().'">Yes, Delete this class</a><br>';
        echo 		'&gt;&gt;<a href="index.php?cmd=deleteClass">No, Delete another class</a><br>';
        echo 		'&gt;&gt;<a href="index.php?cmd=manageClasses">No, Return to &quot;Manage Classes&quot; home</a><br>';
        echo 	  '</p>';
        echo 	'</td>';
        echo '</tr>';

        echo '<tr><td align="left" valign="top">&nbsp;</td></tr>';
		echo "</table>\n";

	}

	function displayDeleteSuccess ($sourceClass) {

		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo '<tr><td width="100%"><img src="images/spacer.gif" width="1" height="5"></td></tr>';

		echo '<tr>';
		echo 	'<td align="left" valign="top">';
        echo 	  '<p>';
        echo 		'<span class="strong">';
        echo 			$sourceClass->course->displayCourseNo().' -- '.$sourceClass->course->getName().' ('.$sourceClass->displayTerm().')';
        echo 		'</span>';
        echo 		'<span class="successText"> has been deleted.</span>';
        echo 	  '</p>';

        echo 	  '<p>';
        echo 		'&gt;&gt;<a href="index.php?cmd=deleteClass">Delete another class</a><br>';
        echo 		'&gt;&gt;<a href="index.php?cmd=manageClasses">Return to &quot;Manage Classes&quot; home</a><br>';
        echo 	  '</p>';
        echo 	'</td>';
        echo '</tr>';

        echo '<tr><td align="left" valign="top">&nbsp;</td></tr>';
		echo "</table>\n";

	}
	
	function displayCopyItems ($cmd, $user, $request) {
		global $u, $g_permission;

		if($u->getRole() >= $g_permission['staff']) {	//use ajax class lookup
			//display selectClass
			$mgr = new ajaxManager('lookupClass', 'processCopyItems', 'manageClasses', 'Copy', $request);
			$mgr->display();
		}
		else {	//instructor class select
			echo "<form action=\"index.php\" method=\"POST\">\n";
			echo "<input type=\"hidden\" name=\"cmd\" value=\"$cmd\">\n";
			
			$hidden_fields = array('originalClass'=>$request['originalClass'], 'reservesArray'=>$request['reservesArray']);		
			self::displayHiddenFields($hidden_fields);

			$ci_list = $user->getCourseInstances(null, null, 'true');
			echo "			<table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"5\" cellspacing=\"0\" class=\"displayList\">\n";
			echo "				<tr align=\"left\" valign=\"middle\" bgcolor=\"#CCCCCC\" class=\"headingCell1\">\n";
			echo "					<td width=\"10%\">&nbsp;</td>\n";
			echo "					<td width=\"15%\">Course Number</td>\n";
			echo "					<td>Course Name</td><td>Taught By</td><td>Last Active</td><td width=\"20%\">Reserve List</td>\n";
			echo "				</tr>\n";
	
			for($i=0; $i<count($ci_list); $i++)
			{
				
				$ci_list[$i]->getPrimaryCourse();
				$ci_list[$i]->getInstructors();
				
				$rowClass = ($i % 2) ? "evenRow" : "oddRow";
			
				echo "				<tr align=\"left\" valign=\"middle\" class=\"$rowClass\">\n";
				
				$class_SELECTED = ((isset($request['ci']) && $request['ci'] != null) && ($ci_list[$i]->getCourseInstanceID()==$request['ci'])) ? "CHECKED" : "";		
			
				echo "					<td width=\"10%\" align=\"center\"><input type=\"radio\" name=\"ci\" $class_SELECTED value=\"". $ci_list[$i]->getCourseInstanceID() ."\" onClick=\"this.form.submit();\"></td>\n";
				
				echo "					<td width=\"15%\">".$ci_list[$i]->course->displayCourseNo()."</td>\n";
				echo "					<td>".$ci_list[$i]->course->getName()."</td>\n";
	
				echo "					<td>".$ci_list[$i]->displayInstructorList()."</td>\n";
				echo "					<td width=\"20%\" align=\"center\">".$ci_list[$i]->displayTerm()."</td>\n";
				echo "					<td width=\"20%\" align=\"center\"><a href=\"javascript:openWindow('no_control&cmd=previewReservesList&ci=".$ci_list[$i]->courseInstanceID . "','width=800,height=600');\">preview</a></td>\n";
				echo "				</tr>\n";
			}
	
			echo "				<tr align=\"left\" valign=\"middle\" bgcolor=\"#CCCCCC\" class=\"headingCell1\"><td colspan=\"6\">&nbsp;</td></tr>\n";
			if (isset($request['ci']) && $request['ci'] && $request['ci'] != null)
			{
				echo "	<tr><td valign=\"top\" align=\"center\" colspan=\"6\"><input type=\"submit\" name=\"performAction\" value=\"Copy\" onClick=\"this.form.cmd.value='processCopyItems'\"></td></tr>\n";
			} else {
				//echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
				echo "	<tr><td valign=\"top\" align=\"center\" colspan=\"6\"><input type=\"submit\" name=\"performAction\" value=\"Copy\" DISABLED></td></tr>\n";
			}
			
			echo "			</table>\n";
	
			echo "</form>\n";		
		}
	}
	
	
	function displayCopyItemsSuccess ($targetClass, $originalClass, $numberCopied) {

		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo '<tr><td width="100%"><img src="images/spacer.gif" width="1" height="5"></td></tr>';

		echo '<tr>';
		echo 	'<td align="left" valign="top">';
        echo 	  '<p>';
        echo 		'<span class="successText">'.$numberCopied.' item(s) were copied from </span>';
        echo 		'<span class="strong">';
        echo 			$originalClass->course->displayCourseNo().' -- '.$originalClass->course->getName().' ('.$originalClass->displayTerm().')';
        echo 		'</span>';
        echo 		'<span class="successText"> to </span>';
        echo 		'<span class="strong">';
        echo 			$targetClass->course->displayCourseNo().' -- '.$targetClass->course->getName().' ('.$targetClass->displayTerm().')';
        echo 		'</span>';
        echo 	  '</p>';

        echo 	  '<p>';
        echo 		'&gt;&gt;<a href="index.php?cmd=editClass&ci='.$originalClass->getCourseInstanceID().'">Return to original class</a><br>';
        echo 		'&gt;&gt;<a href="index.php?cmd=editClass&ci='.$targetClass->getCourseInstanceID().'">Go to target class</a><br>';
        echo 	  '</p>';
        echo 	'</td>';
        echo '</tr>';
        
        echo '<tr><td align="left" valign="top">&nbsp;</td></tr>';
		echo "</table>\n";		
		
	}
	
	
	function displayDuplicateCourses($user, $dupes, $prev_state, $instr_id=null) {
		global $g_permission;
		$msg_clash = $msg_reactivate = '';
		$rowClass = '';
		
		//make a form with hidden items and a button to return to previous screen
		if(!empty($prev_state)):
?>
		<div style="width:100%; margin:auto;">
			<form action="index.php" method="post" name="return_to_previous">			
				<?php self::displayHiddenFields(unserialize(urldecode($prev_state))); ?>
				<input type="submit" name="return" value="Go Back to the Previous Screen" />
			</form>
		</div>
<?php
		endif;
		
		//output tables
		
		if(!empty($dupes[0])):
			$msg_clash = 'The course you are attempting to create/reactive is already active for this term!  Please double-check the department, course number, section, and term of your course.  If you believe this to be an error, or need further assistance, please contact your Reserves staff.';
			
			if($user->getRole() >= $g_permission['staff']) {
				$msg_clash .= ' If you are trying to copy a class to a "new class", please copy "to existing" instead.';
			}
			
			//link course name/num to editClass, if viewed by instructor
			if($user->getRole() >= $g_permission['staff']) {
				$course_num = '<a href="index.php?cmd=editClass&ci='.$dupes[0]->getCourseInstanceID().'">'.$dupes[0]->course->displayCourseNo().'</a>';
				$course_name = '<a href="index.php?cmd=editClass&ci='.$dupes[0]->getCourseInstanceID().'">'.$dupes[0]->course->getName().'</a>';
			}
			else {
				$course_num = $dupes[0]->course->displayCourseNo();
				$course_name = $dupes[0]->course->getName();
			}
?>	
		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
			<tr><td width="100%"><img src="images/spacer.gif" width="1" height="5"></td></tr>
			<tr><td class="failedText"><?=$msg_clash?></td></tr>
			<tr><td>&nbsp;</td></tr>
			<tr>
				<td align="left" valign="top" class="borders">
					<table width="100%" border="0" align="center" cellpadding="5" cellspacing="0" class="displayList">
						<tr align="left" valign="middle" bgcolor="#CCCCCC" class="headingCell1">
							<td>Course Number</td><td align="left">Course Name</td><td>Instructor</td><td>Active Term</td><td>Reserve List</td>
						</tr>
						<tr align="left" valign="middle" class="oddRow">
							<td width="15%" align="center"><?=$course_num?></td>
							<td><?=$course_name?></td>
							<td width="20%" align="center"><?=$dupes[0]->displayInstructorList()?></td>
							<td width="15%" align="center"><?=$dupes[0]->displayTerm()?></td>
							<td width="10%" align="center"><a href="javascript:openWindow('no_control&cmd=previewReservesList&ci=<?=$dupes[0]->getCourseInstanceID()?>','width=800,height=600');">preview</a></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr><td align="left" valign="top">&nbsp;</td></tr>
		</table>
		<p />
<?php
			//do not show reactivatable items
			return;
		endif;
		
		//now handle reactivatable courses
				
		//customize output based on user role
		if($user->getRole() >= $g_permission['staff']) {	//if staff, show courses for _all_ instructors, link to editClass
			//merge the 2 arrays
			$dupes_reac = array_merge($dupes[1], $dupes[2]);
			//message
			$msg_reactivate = 'A match exists for the course you are trying to create. You can <u>reactivate</u> one of the matching courses below, or create a new course.';
		}
		else {
			$dupes_reac = $dupes[1];
			$msg_reactivate = 'It appears that you have previously taught the course you are trying to create.  You can <u>reactivate</u> one of the matching courses below, or create a new course. If you need further assistance, please contact your Reserves staff.';
		}
		
		//if there are dupes, display table
		if(!empty($dupes_reac)):
?>
		<div>
			<span class="failedText"><?=$msg_reactivate?></span>
			<p />		
			<table width="100%" border="0" align="center" cellpadding="5" cellspacing="0" class="displayList borders">
				<tr align="left" valign="middle" bgcolor="#CCCCCC" class="headingCell1">
					<td>Course Number</td><td align="left">Course Name</td>
							
<?php		if($user->getRole() >= $g_permission['staff']):	//show instructor ?>
					<td align="left">Instructor</td>
<?php		endif; ?>
							
					<td>Last Active</td><td>Reserve List</td><td>Reactivate</td>
				</tr>
<?php
				foreach($dupes_reac as $dupe):
					$rowClass = ($rowClass=='oddRow') ? 'evenRow' : 'oddRow';				
					
					//create links to reactivate screen					
					$reactivate_url = 'index.php?cmd=reactivateConfirm&amp;ci='.$dupe->getCourseInstanceID();	//build the URL
					//mark those courses where the instructor matches
					$mark = (($user->getRole() >= $g_permission['staff']) && in_array($instr_id, $dupe->instructorIDs)) ? ' *' : '';
?>
				<tr align="left" valign="middle" class="<?=$rowClass?>">
					<td width="15%" align="center"><?=$dupe->course->displayCourseNo()?></td>
					<td><?=$dupe->course->getName()?></td>
							
<?php				if($user->getRole() >= $g_permission['staff']):	//show instructor ?>
						<td width="20%"><?=$dupe->displayInstructorList()?></td>
<?php				endif; ?>

					<td width="10%" align="center" nowrap="nowrap"><?=$dupe->displayTerm()?></td>
					<td width="10%" align="center" nowrap="nowrap"><a href="javascript:openWindow('no_control&cmd=previewReservesList&ci=<?=$dupe->getCourseInstanceID()?>','width=800,height=600');">preview</a></td>
					<td width="10%" align="center" nowrap="nowrap"><a href="<?=$reactivate_url?>">Reactivate<?=$mark?></a></td>
				</tr>
<?php
				endforeach;
?>
			</table>
			<br />
					
<?php			if($user->getRole() >= $g_permission['staff']):	//show mark note ?>
			<strong>*</strong> Selected instructor match
<?php			endif; ?>
		</div>
<?php
		endif; //if empty dupe array
		
		//override button		
	
		//show a button to confirm creation of new course (if do not wish to reactivate) -- pass on all the necessary info
		//pass some necessary info
		$info = $_REQUEST;
		$info['cmd'] = 'createNewClass';
		$info['confirm_new'] = 'true';
?>		
		<p />	
		<div style="width:100%; margin:auto;">
			<form action="index.php" method="post" name="confirm_new">	
				<?php self::displayHiddenFields($info); ?>					
				<input type="submit" name="confirm" value="Create New Course" />
			</form>
		</div>
<?php
	}
}
?>
