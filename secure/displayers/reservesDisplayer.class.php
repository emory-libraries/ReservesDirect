<?
/*******************************************************************************
reservesDisplayer.class.php


Created by Kathy Washington (kawashi@emory.edu)

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
//require_once("classes/reserves.class.php");

class reservesDisplayer
{
	/**
	* @return void
	* @param user $user the user
	* @desc Display users reserves courses
	*/
	function displayCourseList($user)
	{
		global $g_permission;
		echo '<table width="90%" border="0" cellspacing="0" cellpadding="0" align="center">'
		.    '	<tr>'
	    .    '  	<td width="100%"><img src="images/spacer.gif" width="1" height="5"> </td>'
	    .    '  </tr>'
		.	 '	<tr>'
		.	 '		<td>[ <a href="index.php?cmd=searchForClass" class="editlinks">Add a class</a> ] <span class="small">Enroll as a student in a class and add it to your list.</span><br>'
		.	 '			[ <a href="index.php?cmd=removeClass" class="editlinks">Remove a class</a> ] <span class="small">Remove a class from your list of classes.</span>'
		.	 '		</td>'
		.	 '	</tr>'
		.	 '	<tr>'
		.	 '		<td>&nbsp;</td>'
		.	 '	</tr>'
	    .    '  <tr>'
	    .    '      <td><table width="100%" border="0" cellspacing="0" cellpadding="0">'
	    .    '      	<tr align="left" valign="top">'
	    .    '          	<td class="headingCell1"><div align="center">YOUR CLASSES</div></td>'
	    .    '          	<td width="75%">&nbsp;</td>'
	    .    '        	</tr>'
	    .    '      	</table>'
	    .	 '		</td>'
	    .    '  </tr>'
	    .	 '	<tr>'
	    .    '  	<td align="left" valign="top" class="borders"><table width="100%" border="0" cellpadding="2" cellspacing="0" class="displayList">	'
	    ;

	    $permissionLvl = 0; //default to student
		if (is_array($user->courseInstances) && !empty($user->courseInstances))
		{
			for ($j=0; $j<count($user->courseInstances); $j++){

				$rowClass = ($j % 2) ? "evenRow" : "oddRow";

				$ci = $user->courseInstances[$j];
				$permissionLvl = $ci->getPermissionForUser($user->getUserID());  //get user access for this ci

				echo '<tr align="left" valign="middle" class="'.$rowClass.'">';
				echo '<td width="2%">';
				if ($permissionLvl >= $g_permission['proxy']) {
					echo '<img src="images/pencil.gif" width="24" height="20"></td>';
				}else {
					echo '&nbsp;</td>';
				}
				if ($permissionLvl >= $g_permission['proxy']) {
					echo '			<td width="20%"><a href="index.php?cmd=editClass&ci='.$ci->courseInstanceID . '">' . $ci->course->displayCourseNo() . '</a></td>';
				} else {
					echo '			<td width="20%"><a href="index.php?cmd=viewReservesList&ci='.$ci->courseInstanceID . '">' . $ci->course->displayCourseNo() . '</a></td>';
					//echo '			<td width="20%"><a href="index.php?cmd=viewReservesList&ci='.$ci->courseInstanceID . '&ca='.$ci->aliasID . '">' . $ci->course->displayCourseNo() . '</a></td>';
				}
				if ($permissionLvl >= $g_permission['proxy']) {
					echo    '	<td width="40%"><a href="index.php?cmd=editClass&ci=' ;
				} else {
					echo ' 		<td width="40%"><a href="index.php?cmd=viewReservesList&ci=' ;
				}
				//echo 			$ci->courseInstanceID . '&ca='.$ci->aliasID . '">' . $ci->course->getName() . '</a></td>'
				echo 			$ci->courseInstanceID . '">' . $ci->course->getName() . '</a></td>'
				.   '			<td NOWRAP align="left" width="15%">' . $ci->displayTerm() . '</td>'
				.   '			<td width="25%">'
				;

				for($i=0;$i<count($ci->instructorList);$i++) {
					if ($i>0)
						echo ',&nbsp;';
					echo $ci->instructorList[$i]->getFirstName() . "&nbsp;" .$ci->instructorList[$i]->getLastName() ;
				}
				echo "</td>\n</tr>";
			}
		} else {
			echo "<tr><td align=\"center\">No active classes have been added</td></tr>";
		}

		echo "	</tbody>\n"
		.	 "</table>\n"
		;

		echo '<tr>'
		.	'	<td height="14"><img src="images/spacer.gif" width="1" height="15"></td>'
		.	'</tr>';
		if ($permissionLvl >= $g_permission['proxy']) {
	    	echo '<tr>'
	    	.   '	<td height="14" align="left" valign="middle" colspan="4"><img src="images/pencil.gif" alt="Edit" width="24" height="20"><span class="small"> = classes you may edit</span></td>'
	    	.   '</tr>';
		}
	    echo '<tr>'
	    .   	'<td><img src="images/spacer.gif" width="1" height="15"></td>'
	    .   '</tr>'
		;
	}

	function displayReserves($cmd, $user, $ci, $no_control=null)
	{
		global $g_permission, $g_reservesViewer;

		echo('<FORM METHOD=POST NAME="editReserves" ACTION="index.php">');
	    echo('<INPUT TYPE="HIDDEN" NAME="cmd" VALUE="'.$cmd.'">');
		echo('<INPUT TYPE="HIDDEN" NAME="ci" VALUE="'.$ci->getCourseInstanceID().'">');
		echo('<INPUT TYPE="HIDDEN" NAME="hideSelected" VALUE="">');
		echo('<INPUT TYPE="HIDDEN" NAME="showAll" VALUE="">');

		echo '<table width="90%" border="0" cellspacing="0" cellpadding="0" align="center">';
		echo '	<tr>';

		if (is_null($no_control))
			echo '		<td width ="100%" align="right" valign="middle" class="small" align="right"><a href="index.php">Exit class</a></td>';
		else
			echo '		<td width ="100%" align="right" valign="middle" class="small" align="right"><a href="javascript:window.close();">Close Window</a></td>';

		echo '	</tr>';
		echo	'<tr>'
		.	'		<td width="140%" colspan="2"><img src="images/spacer.gif" width="1" height="5"> </td>'
		.	'	</tr>'
		.	'	<tr>'
		.	'		<td width="75%" align="left" valign="top" >'
		.	'		<table width="100%" border="0" cellspacing="0" cellpadding="2">'
		.	'			<tr> '
		.	'				<td align="left" valign="top" class="courseTitle">'.$ci->course->displayCourseNo() . " " . $ci->course->getName().'</td>'
		.	'			</tr>'
		.	'			<tr align="left" valign="top">'
		.	'				<td class="courseHeaders">' . $ci->displayTerm() . '</td>'
		.	'			</tr>'
		.	'			<tr align="left" valign="top">'
		.	'				<td class="courseHeaders">Instructors: ';
								for($i=0;$i<count($ci->instructorList);$i++) {
									if ($i!=0) echo ';&nbsp;';
									echo '<a href="mailto:'.$ci->instructorList[$i]->getEmail().'">'.$ci->instructorList[$i]->getName().'</a>';
								}
		echo '				</td>'
		.	'			</tr>'
		.	'			<tr align="left" valign="top">'
		.	'				<td><span class="courseHeaders">Cross-listings: </span>';
								if (count($ci->crossListings)==0) {
									echo 'None';
								}
								else {
									for ($i=0; $i<count($ci->crossListings); $i++) {
										if ($i>0) echo',&nbsp;';
										echo $ci->crossListings[$i]->displayCourseNo();
									}
								}
		echo '				</td>'
		.	'			</tr>'
		.	'			<tr>'
		.	'				<td align="left" valign="top">&nbsp;</td>'
		.	'			</tr>'
		.	'			<tr>'
		.	'				<td align="left" valign="top"> <p class="small"><span class="strong">Helper Applications:</span> <a href="http://www.adobe.com/products/acrobat/readstep2.html">Adobe Acrobat</a>, <a href="http://www.real.com">RealPlayer</a>, <a href="http://www.apple.com/quicktime/download/ ">QuickTime</a>, <a href="http://office.microsoft.com/Assistance/9798/viewerscvt.aspx">Microsoft Word</a></p></td>'
		.	'			</tr>'
		.	'			<tr>'
		.	'				<td align="left" valign="top">&nbsp;</td>'
		.	'			</tr>'
		.	'		</table>'
		.	'		</td>'
		.	'	</tr>'
		.	'	<tr>'
		.	'		<td>'
		.	'			<table width="100%" border="0" cellspacing="0" cellpadding="0">'
		.	'				<tr align="left" valign="top">'
		.	'					<td class="headingCell1"><div align="center">COURSE MATERIALS</div></td>'
		.	'					<td width="75%" align="right">[ <a href="javascript:document.forms.editReserves.hideSelected.value=\'true\';document.forms.editReserves.submit();" class="editlinks">Hide selected</a> ]  [ <a href="javascript:document.forms.editReserves.showAll.value=\'true\';document.forms.editReserves.submit();" class="editlinks">Show all</a> ] </td>'
		.	'				</tr>'
		.	'			</table>'
		.	'		</td>'
		.	'	</tr>'
		.	'	<tr>'
		.	'		<td align="left" valign="top" class="borders">'
		.	'			<table width="100%" border="0" cellpadding="2" cellspacing="0" class="displayList">'
		.	'				<tr align="left" valign="middle">'
		.	'					<td valign="top" bgcolor="#FFFFFF" class="headingCell1">&nbsp;</td>'
		.	'					<td valign="top" bgcolor="#FFFFFF" class="headingCell1">&nbsp;</td>'
		.	'					<td bgcolor="#FFFFFF" class="headingCell1">'.count($ci->reserveList).' Item(s) On Reserve</td>'
		.	'					<td valign="top" bgcolor="#FFFFFF" class="headingCell1">&nbsp;</td>'
		.   '      			</tr>';
		//Loop through Records Here
		$rowNumber = 0;
		for($i=0;$i<count($ci->reserveList);$i++)
		{
			$ci->reserveList[$i]->getItem();
			$ci->reserveList[$i]->item->getPhysicalCopy();
			$title = $ci->reserveList[$i]->item->getTitle();
			$author = $ci->reserveList[$i]->item->getAuthor();
			$url = $ci->reserveList[$i]->item->getURL();
			$performer = $ci->reserveList[$i]->item->getPerformer();
			$volTitle = $ci->reserveList[$i]->item->getVolumeTitle();
			$volEdition = $ci->reserveList[$i]->item->getVolumeEdition();
			$pagesTimes = $ci->reserveList[$i]->item->getPagesTimes();
			$source = $ci->reserveList[$i]->item->getSource();
			$contentNotes = $ci->reserveList[$i]->item->getContentNotes();
			$itemNotes = $ci->reserveList[$i]->item->getNotes();
			$instructorNotes = $ci->reserveList[$i]->getNotes();
			$callNumber = $ci->reserveList[$i]->item->physicalCopy->getCallNumber();
			$reserveDesk = $ci->reserveList[$i]->item->physicalCopy->getOwningLibrary();


			$rowClass = ($rowNumber++ % 2) ? "evenRow" : "oddRow";
			if ($ci->reserveList[$i]->item->isHeading())
			{
				//echo "headings";
	     echo '<tr align="left" valign="middle" class="'.$rowClass.'">'
				.    '	<td colspan="4" valign="top" id="headingCell2"><span class="headingText">'.$ci->reserveList[$i]->item->getTitle().'</span>';

			} else {

				$reserveItem = new reserveItem($ci->reserveList[$i]->getItemID());
				$itemIcon = $reserveItem->getItemIcon();
				$itemGroup = $reserveItem->itemGroup;

				if ($reserveItem->isPhysicalItem()) {
					//move to config file
					$viewReserveURL = $g_reservesViewer . $ci->reserveList[$i]->item->getLocalControlKey();
				} else {
					$viewReserveURL = "reservesViewer.php?viewer=" . $user->getUserID() . "&reserve=" . $ci->reserveList[$i]->getReserveID();// . "&location=" . $ci->reserveList[$i]->item->getURL();
				}

				echo '		<tr align="left" valign="middle" class="'.$rowClass.'">'
	            .    '			<td width="3%" valign="top" class="itemNumber">'.($i+1).'</td>'
				.    '			<td width="4%" valign="top"><img src="'.$itemIcon.'" alt="text" width="24" height="20"></td>'
	            .    '			<td width="88%">';
	            if ($ci->reserveList[$i]->hidden) {
	            	echo '<div id="hiddenItem">';
	            }
	            if (!$reserveItem->isPhysicalItem()) {
	            	echo '<a href="'.$viewReserveURL.'" target="_blank" class="itemTitle">'.$title.'</a>';
	            	if ($author) {echo '<br><span class="itemAuthor">'.$author.'</span>';}
	            } else {
	            	echo '<span class="itemTitleNoLink">'.$title.'</span>';
	            	if ($author) {echo '<br><span class="itemAuthor">'.$author.'</span>';}
                	if ($callNumber) {echo '<br><span class="itemMeta">'.$callNumber.'</span>';}
					echo '<br><span class="itemMetaPre">On Reserve at:</span> <span class="itemMeta"> '.$reserveDesk.'</span>';
					if ($ci->reserveList[$i]->item->getLocalControlKey())
						{echo ' &gt;&gt; <a href="'.$viewReserveURL.'" target="_blank" class="strong">more info</a>';}
	            }
	            /*
	            	if ($url)
	            	{
	            		echo '<br><span class="itemMetaPre">URL:</span><span class="itemMeta"> '.$url.'</span>';
	            	}
	            	*/
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
	            	
			}
			if ($contentNotes)
     	{
    		echo '<br><span class="noteType">Content Note:</span>&nbsp;<span class="noteText">'.$contentNotes.'</span>';
	    }
	    if ($itemNotes)
	    {

	    	for ($n=0; $n<count($itemNotes); $n++)
	      {
        	$type = strtolower($itemNotes[$n]->getType());
          if ($type == "content") {
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
	    if ($ci->reserveList[$i]->hidden) {
	            	echo '</div>';
	            }
	    echo '</td>';

	    if (!$ci->reserveList[$i]->item->isHeading()) {
	    	if ($ci->reserveList[$i]->hidden) {
	    		$checked = "checked";
	    		echo '<input type="hidden" name="originalHiddenArray['.$ci->reserveList[$i]->getReserveID().']" value="'.$ci->reserveList[$i]->getReserveID().'">';
	    	} else {
	    		$checked="";
	    	}
	    	//($ci->reserveList[$i]->hidden) ? $hiddenArray[]=$ci->reserveList[$i]->getReserveID() : "";
	    	
	    	echo '<td width="5%" valign="top"><div align="center"><input type="checkbox" '.$checked.' name="hidden['.$ci->reserveList[$i]->getReserveID().']" value="'.$ci->reserveList[$i]->getReserveID().'"></div></td></tr>';
			}
		}
		//End Loop through Records
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td colspan=\"3\"><img src=\"images/spacer.gif\" width=\"1\" height=\"15\"></td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";

		if (is_null($no_control))
			echo "		<td colspan=\"3\"><div align=\"center\" class=\"strong\"><a href=\"index.php\">Exit Class</a></div></td>\n";
		else
			echo "		<td colspan=\"3\"><div align=\"center\" class=\"strong\"><a href=\"javascript:window.close();\">Close Window</a></div></td>\n";
		
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td colspan=\"3\"><img src=\"images/spacer.gif\" width=\"1\" height=\"15\"></td>\n";
		echo "	</tr>\n";
		echo "</table>\n";
		echo "</form>\n";;

	}
	
	function displayStaffAddReserve($request=null)
	{
		echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr><td width=\"140%\"><img src=\images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>\n";
		echo "	<tr>\n";
		echo "		<td align=\"center\" valign=\"top\">\n";
		//echo "			<table width=\"40%\" border=\"0\" cellspacing=\"0\" cellpadding=\"8\">\n";
		echo '			<table width="40%" border="0" cellspacing="0" cellpadding="8" class="borders">';
		echo "				<tr class=\"headingCell1\"><td width=\"40%\">Add / Process Materials</td></tr>\n";
		echo "				<tr align=\"left\" valign=\"top\">\n";
		echo "					<td width=\"40%\">\n";
		echo "						<ul>\n";
		echo "							<li><a href=\"index.php?cmd=displayRequest\" align=\"center\">Process Requests</a></li>\n";
		if ((!$request['ci']) || (!$request['selected_instr']))
			echo "							<li><a href=\"index.php?cmd=addDigitalItem\" align=\"center\">Add an Electronic Item</a></li>\n";
		else if ($request['ci'] && $request['selected_instr'])
			echo "							<li><a href=\"index.php?cmd=addDigitalItem&ci=".$request['ci']."&selected_instr=".$request['selected_instr']."\" align=\"center\">Add an Electronic Item</a></li>\n";
		if ((!$request['ci']) || (!$request['selected_instr']))
			echo "							<li><a href=\"index.php?cmd=addPhysicalItem\">Add a Physical Item</a></li>\n";
		else if ($request['ci'] && $request['selected_instr'])
			echo "							<li><a href=\"index.php?cmd=addPhysicalItem&ci=".$request['ci']."&selected_instr=".$request['selected_instr']."\">Add a Physical Item</a></li>\n";
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
	
	
	/**
 * @return void
 * @param int $courseInstances
 * @desc Displays the Users Active Classes this is the first screen of the addReserve
 * 		expected next steps
 *			addReserve::displaySearchItemMenu
 *			manageClasses::createClass
 *			manageClasses::reactivateClass
*/
function displaySelectClasses($courseInstances,$user)
{

	echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
	echo "	<tbody>\n";
	echo "		<tr><td width=\"140%\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";

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
			$ci->getInstructors();
			$ci->getProxies();
			if (in_array($user->getUserID(),$ci->instructorIDs) || in_array($user->getUserID(),$ci->proxyIDs)) { //only add reserves to classes where users's role is instructor or proxy
				if ($i++ % 2) $rowClass = "evenRow";
				echo "				<tr align=\"left\" valign=\"middle\" class=\"$rowClass\">\n";
				echo "					<td width=\"15%\">" . $ci->course->displayCourseNo() ."</td>\n";
				echo "					<td width=\"40%\"><a href=\"index.php?cmd=displaySearchItemMenu&ci=" . $ci->getCourseInstanceID() . "\">" . $ci->course->getName() . "</a></td>\n";
				echo "					<td width=\"10%\">" . $ci->getStatus() . "</td>\n";
				echo "					<td width=\"15%\" NOWRAP>" . $ci->displayTerm() . "</td>\n";
				echo "					<td width=\"25%\" NOWRAP>" . $ci->getActivationDate() . " - " . $ci->getExpirationDate() . "</td>\n";
				echo "				</tr>\n";
			}
		}
			echo "			</table>\n";
			echo "		</td>\n";
			echo "	</tr>\n";

			echo "	<tr><td colspan=\'4\">&nbsp;</td></tr>\n";
			echo "	<tr>\n";
			echo "		<td align=\"left\" valign=\"top\">\n";
			echo "			<p>\n";
			echo "				<strong>Don't see the class you're looking for?</strong><br>\n";
			echo "				&gt;&gt; <a href=\"index.php?cmd=reactivateClass\">Reactivate an old class</a><br>\n";
			echo "				&gt;&gt;<a href=\"index.php?cmd=createClass\"> Create a new class</a>\n";
			echo "			</p>\n";
			echo "		</td>\n";
			echo "	</tr>\n";

	} else {
		echo "		<tr><td>You have no associated classes.  Please select the Manage Classes tab.</td></tr>\n";
	}

	echo "		<tr><td align=\"left\" valign=\"top\"><img src=\"images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
	echo "	</tbody>\n";
	echo "</table>\n";
}
function displaySelectInstructor($user, $page, $cmd)
	{
		$subordinates = common_getUsers('instructor');

		echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tbody>\n";
		echo "		<tr><td width=\"140%\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";
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

	echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n"
	.	 "	<tbody>\n"
	.	 "		<tr><td width=\"140%\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n"
	.	 "		<tr>\n"
    .	 "			<td align=\"left\" valign=\"top\" class=\"borders\">\n"
    .	 "				<table width=\"50%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"5\">\n"
	.	 "					<tr><td><strong>How would you like to put the item on reserve?</strong></td></tr>\n"
	.	 "					<tr><td>\n"
	.	 "						<ul><li><a href=\"index.php?cmd=searchScreen&ci=$ci\">Search for the Item</a></li>\n"
	.	 "							<li><a href=\"index.php?cmd=uploadDocument&ci=$ci\">Upload a Document</a></li>\n"
	.	 "							<li><a href=\"index.php?cmd=addURL&ci=$ci\">Add a URL</a></li>\n"
	.	 "							<li><a href=\"index.php?cmd=faxReserve&ci=$ci\">Fax a Document</a></li>\n"
	.	 "						</ul>\n"
	.	 "					</td></tr>\n"
	.	 "				</table>\n"
	.	 "			</td>\n"
	.	 "		</tr>\n"
	.	 "		<tr><td><img src=\"images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n"
	.	 "	</tbody>\n"
	.	 "</table>\n"
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

		echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tbody>\n";
		echo "		<tr><td width=\"140%\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";
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

		echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
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
			$contentNotes = $item->getContentNotes();
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

	            if ($contentNotes)
	            {
	            	echo '<br><span class="noteType">Content Note:</span>&nbsp;<span class="noteText">'.$contentNotes.'</span>';
	            }
	            if ($itemNotes)
	            {
	            	for ($n=0; $n<count($itemNotes); $n++)
	            	{
	            		$type = strtolower($itemNotes[$n]->getType());
	            		if ($user->dfltRole >= $g_permission['staff'] || $type == "content") {
	            			echo '<br><span class="noteType">'.ucfirst($type).' Note:</span>&nbsp;<span class="noteText">'.$itemNotes[$n]->getText().'</span>';
	            		}
	            	}
	            }
	            
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
        	echo "			<tr><td colspan=\"2\" align='right'>";
        	if ($showPrevLink) {
        		echo "<img src=\"images/getPrevious.gif\" onClick=\"javaScript:document.forms.searchResults.cmd.value='searchResults';document.forms.searchResults.f.value=".$fPrev.";document.forms.searchResults.submit();\">&nbsp;&nbsp;";
        	}
        	if ($showNextLink) {
        		echo "<img src=\"images/getNext.gif\" onClick=\"javaScriptreservesDisplayer.class.php:document.forms.searchResults.cmd.value='searchResults';document.forms.searchResults.f.value=".$fNext.";document.forms.searchResults.submit();\">";
        	}
        	echo "</td></tr>\n";
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

	echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
  echo "	<tr><td width=\"140%\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\">&nbsp;</td></tr>\n";
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
  //echo "						<li><a href=\"index.php?cmd=displaySearchItemMenu&ci=".$ci->getCourseInstanceID()."\">Yes, to this class.</a></li>\n";
  echo "						<li><a href=\"index.php?cmd=displaySearchItemMenu&ci=$ci\">Yes, to this class.</a></li>\n";
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
  
  
  echo "			</table>\n";
  echo "		</td>\n";
	echo "	</tr>\n";
  echo "	<tr><td><img src=\"images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
  echo "</table>\n";
}

function displayUploadForm($user, $ci, $type, $docTypeIcons=null)
{
	global $g_permission;
	
	if ($type == "URL")		
		$documentTest = "if (frm.url.value == \"\") alertMsg = alertMsg + \"URL is required.<br>\";\n";
	else
		$documentTest = "if (frm.userfile.value == \"\") alertMsg = alertMsg + \"File is required.<br>\";\n";
	
	echo "
		<script language=\"JavaScript\">
		//<!--
			function validateForm(frm)
			{			
				var alertMsg = \"\";

				if (frm.title.value == \"\")
					alertMsg = alertMsg + \"Title is required.<br>\";
				
				if (frm.author.value == \"\")
					alertMsg = alertMsg + \"Author is required.<br>\";
										
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
	echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
	echo "	<tr><td width=\"140%\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";
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
	echo "					<td width=\"35%\" height=\"31\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\" class=\"strong\"><font color=\"#FF0000\"><strong>*</strong></font>Author/Composer:</div></td>\n";
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
		echo "					<td align=\"left\"><INPUT TYPE=\"file\" NAME=\"userfile\" SIZE=40></td>\n";
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
	if ($user->dfltRole >= $g_permission['staff']) {
		echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\"><span class=\"strong\">Note</span>(<em>if applicable</em>)<span class=\"strong\">:</span></div></td>\n";
		echo "					<td align=\"left\"><TEXTAREA NAME=\"contents\" cols=50 rows=3>\n</TEXTAREA>\n<br>\n";
	
  	echo '      			<span class="small">Note Type:';
    echo '					<label><input type="radio" name="noteType" value="Content" checked>Content Note</label>';
    echo '					<label><input type="radio" name="noteType" value="Instructor">Instructor Note</label>';
    echo '					<label><input type="radio" name="noteType" value="Staff">Staff Note</label>';
		echo '					<label><input type="radio" name="noteType" value="Copyright">Copyright Note</label>';
		echo '					</span>';
	} else {
		echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\"><span class=\"strong\">Instructor Note</span>(<em>if applicable</em>)<span class=\"strong\">:</span></div></td>\n";
		echo "					<td align=\"left\"><TEXTAREA NAME=\"contents\" cols=50 rows=3>\n</TEXTAREA>\n<br>\n";
		echo '					<input type="hidden" name="noteType" value="Instructor">';
	}
	echo "</td>";
	echo "				</tr>\n";

	echo "				<tr valign=\"middle\">\n";
	echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\"><span class=\"strong\">This document is from my personal collection:</span> (<em>if applicable</em>)<span class=\"strong\">:</span></div></td>\n";
	echo "					<td align=\"left\"><INPUT TYPE=\"checkbox\" NAME=\"personal\" CHECKED></td>\n";
	echo "				</tr>\n";

	if ($type == "URL")
	{
		echo "				<tr><td width=\"20%\" valign=\"top\" colspan=\"3\" align=\"center\"><input type=\"submit\" name=\"Submit\" value=\"Save URL\"></td></tr>\n";
	} else {
		echo "				<tr><td width=\"20%\" valign=\"top\" colspan=\"3\" align=\"center\"><input type=\"submit\" name=\"Submit\" value=\"Save Document\"></td></tr>\n";
	}

	echo "			</table>\n";
	echo "		</td>\n";
	echo "	</tr>\n";
	echo "	<tr><td><img src=\"images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
}

function displayFaxInfo($ci)
{

	echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
    echo "	<tr><td width=\"140%\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";
    echo "	<tr>\n";
    echo "		<td align=\"left\" valign=\"top\">\n";
    echo "			<table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"3\" cellspacing=\"0\" class=\"borders\">\n";
    echo "				<tr>\n";
    echo "					<td align=\"left\" valign=\"top\">\n";
    echo "						<blockquote>\n";
    echo "							<p class=\"helperText\">Reserves Direct allows you to fax in a document and will automatically convert it to PDF. Please limit faxed documents to 25 clear, clean sheets to minimize downloading and printing time. To proceed, please fax each document individually (with no cover sheet!) to: </p>\n";
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
	echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
    echo "	<tr><td width=\"140%\" colspan=\"2\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";
    echo "	<tr>\n";
	echo "		<td width=\"50%\" align=\"left\" valign=\"top\" class=\"helperText\">Claim your fax.</td>\n";
	echo "		<td width=\"50%\" align=\"left\" valign=\"top\" align=\"right\"><a href=\"link\">Return to Previous Page</a></td>\n";
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
			echo "					<td width=\"10%\" valign=\"top\" class=\"$rowClass\" align=\"center\"><a href=\"" . $g_faxURL . $fax['file'] . "\">preview</a></td>\n";
			echo "					<td width=\"15%\" valign=\"top\" class=\"$rowClass\" align=\"center\"><input type=\"checkbox\" name=\"claimFax[$i]\" value=\"" . $fax['file'] . "\"></td>\n";
			echo "				</tr>\n";

		}
		echo "				<tr align=\"left\" valign=\"middle\"><td width=\"20%\" valign=\"top\" class=\"headingCell1\">&nbsp;</td><td width=\"40%\" class=\"headingCell1\">&nbsp;</td><td width=\"15%\" valign=\"top\" class=\"headingCell1\">&nbsp;</td><td width=\"10%\" valign=\"top\" class=\"headingCell1\">&nbsp;</td><td width=\"15%\" valign=\"top\" class=\"headingCell1\">&nbsp;</td></tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";

		echo "	<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
		echo "	<tr><td colspan=\"2\" align=\"center\"><input type=\"submit\" name=\"Submit\" value=\"Continue\"></td></tr>\n";
	} else {
		echo "	<tr><td colspan=\"2\" align=\"center\"><b>No faxes have been received.  Remember unclaimed faxes are deleted at midnight.</td></tr>\n";
	}
	echo "	<tr><td colspan=\"2\"><img src=\"images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
}

function displayFaxMetadataForm($user, $faxes, $ci)
{
	global $g_faxURL, $g_permission;

	echo "<FORM METHOD=POST ACTION=\"index.php\">\n";
	echo "	<INPUT TYPE=\"HIDDEN\" NAME=\"cmd\" VALUE=\"storeFaxMetadata\">\n";
	echo "	<INPUT TYPE=\"HIDDEN\" NAME=\"ci\" VALUE=\"$ci\">\n";

	echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
	echo "	<tr><td width=\"140%\" colspan=\"2\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";
	echo "	<tr>\n";
	echo "		<td width=\"50%\" align=\"left\" valign=\"top\" class=\"helperText\">Add information about your fax(es).</td>\n";
	echo "		<td width=\"50%\" align=\"left\" valign=\"top\" align=\"right\"><a href=\"link\">Return to previous page</a></td>\n";
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
			
			
			if ($user->dfltRole >= $g_permission['staff']) {
				echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\"><span class=\"strong\">Note</span>(<em>if applicable</em>)<span class=\"strong\">:</span></div></td>\n";
				echo "					<td align=\"left\"><TEXTAREA NAME=\"" . $fax['file'] . "[contents]\" cols=50 rows=3>\n</TEXTAREA>\n<br>\n";
	
  			echo '      			<span class="small">Note Type:';
    		echo '					<label><input type="radio" name="'.$fax['file'].'[noteType]" value="Content" checked>Content Note</label>';
    		echo '					<label><input type="radio" name="'.$fax['file'].'[noteType]" value="Instructor">Instructor Note</label>';
    		echo '					<label><input type="radio" name="'.$fax['file'].'[noteType]" value="Staff">Staff Note</label>';
				echo '					<label><input type="radio" name="'.$fax['file'].'[noteType]" value="Copyright">Copyright Note</label>';
				echo '					</span>';
			} else {
				echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\"><span class=\"strong\">Instructor Note</span>(<em>if applicable</em>)<span class=\"strong\">:</span></div></td>\n";
				echo "					<td align=\"left\"><TEXTAREA NAME=\"" . $fax['file'] . "[contents]\" cols=50 rows=3>\n</TEXTAREA>\n<br>\n";
				echo '					<input type="hidden" name="'.$fax['noteType'].'" value="Instructor">';
			}
			
			
			
			//echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\"><span class=\"strong\">Contents</span> (<em>if applicable</em>)<span class=\"strong\">:</span></div></td>\n";
			//echo "					<td align=\"left\"><textarea NAME=\"" . $fax['file'] . "[contents]\" cols=\"50\" rows=\"3\"></textarea></td>\n";
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
	echo "				<tr><td width=\"20%\" align=\"left\" valign=\"top\"><div align=\"right\"></div></td><td align=\"left\" valign=\"top\">&nbsp;</td><td width=\"20%\" align=\"left\" valign=\"top\">&nbsp;</td></tr>\n";
	echo "				<tr><td width=\"20%\" align=\"left\" valign=\"top\"></td><td align=\"left\" valign=\"top\" align=\"center\"><input type=\"submit\" name=\"Submit\" value=\"Save and Continue\"></td><td width=\"20%\">&nbsp;</td></tr>\n";
	echo "			</table>\n";
	echo "		</td>\n";
	echo "	</tr>\n";
	echo "	<tr><td colspan=\"2\"><img src=\"images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
}


function displaySortScreen($user, $ci)
{
	global $g_reservesViewer, $g_permission;


	echo '<table width="90%" border="0" cellspacing="0" cellpadding="0" align="center">'
	.	 '<FORM METHOD=POST NAME="sortScreen" ACTION="index.php">'
	.	 '<INPUT TYPE="HIDDEN" NAME="cmd" VALUE="sortReserves">'
	.	 '<INPUT TYPE="HIDDEN" NAME="ci" VALUE="'.$ci->getCourseInstanceID().'">'
	.	 '<INPUT TYPE="HIDDEN" NAME="sortBy" VALUE="'.$_REQUEST['sortBy'].'">'
	.    '	<tr>'
	.    '		<td width="140%" colspan="2"><img src="images/spacer.gif" width="1" height="5"> </td>'
	.	 '	</tr>';
	echo '			<tr>';
	echo '				<td colspan="2" width ="100%" align="center" valign="middle" class="small"><a href="index.php?cmd=editClass&ci='.$ci->getCourseInstanceID().'">Return to Edit Class</a></td>';
	echo '        </tr>';
	echo	 '	<tr>'
	.    '		<td width="35%" align="left" valign="middle" bgcolor="#CCCCCC" class="borders"><div align="center"><span class="strong">Sort  by:</span> [ <a href="index.php?cmd=sortReserves&ci='.$ci->getCourseInstanceID().'&sortBy=title" class="editlinks">title</a> ] [ <a href="index.php?cmd=sortReserves&ci='.$ci->getCourseInstanceID().'&sortBy=author" class="editlinks">author</a>            ] [ <a href="index.php?cmd=customSort&ci='.$ci->getCourseInstanceID().'" class="editlinks">custom</a> ]</div></td>'
	.    '		<td width="65%" align="left" valign="top">&nbsp;</td>'
	.	 '	</tr>'
	.	 '	<tr>'
	.    '		<td colspan="2" align="left" valign="top"><div align="right">'
	.    '		<table width="100%" border="0" cellspacing="0" cellpadding="5">'
	.	 '	    	<tr>'
	.	 '             	<td width="100%">&nbsp;</td>'
	.	 '             	<td><div align="right"></div></td>'
	.	 '             	<td><div align="right"></div></td>'
	.	 '             	<td><div align="right"><input type="submit" name="saveOrder" value="Save Order"></div></td>'
	.	 '	    	</tr>'
	.    '		</table></div>'
	.    '		</td>'
	.	 '	</tr>'
	.	 '	<tr>'
	.    '		<td colspan="2" align="left" valign="top">'
	.    '		<table width="100%" border="0" cellspacing="0" cellpadding="0">'
	.	 '	    	<tr align="left" valign="top">'
	.	 '             	<td class="headingCell1"><div align="center">COURSE MATERIALS</div></td>'
	.	 '             	<td width="75%">&nbsp;</td>'
	.	 '	    	</tr>'
	.    '		</table>'
	.    '		</td>'
	.	 '	</tr>'
	.	 '	<tr>'
	.    '		<td colspan="2" align="left" valign="top" class="borders">'
	.    '		<table width="100%" border="0" cellpadding="2" cellspacing="0" class="displayList">'
	.	 '	    	<tr align="left" valign="middle">'
	.	 '             	<td width="1%" valign="top" bgcolor="#FFFFFF" class="headingCell1">&nbsp;</td>'
	.	 '             	<td width="100%" bgcolor="#FFFFFF" class="headingCell1">'.count($ci->reserveList).' Item(s) On Reserve</td>'
	.	 '	    	</tr>';
	//Begin Loop Through Records
		$rowNumber = 0;
		for($i=0;$i<count($ci->reserveList);$i++)
		{
			$rowClass = ($rowNumber++ % 2) ? "evenRow" : "oddRow";
			
			$ci->reserveList[$i]->getItem();
				
			$ci->reserveList[$i]->item->getPhysicalCopy();
			$title = $ci->reserveList[$i]->item->getTitle();
			$author = $ci->reserveList[$i]->item->getAuthor();
			$url = $ci->reserveList[$i]->item->getURL();
			$performer = $ci->reserveList[$i]->item->getPerformer();
			$volTitle = $ci->reserveList[$i]->item->getVolumeTitle();
			$volEdition = $ci->reserveList[$i]->item->getVolumeEdition();
			$pagesTimes = $ci->reserveList[$i]->item->getPagesTimes();
			$source = $ci->reserveList[$i]->item->getSource();
			$contentNotes = $ci->reserveList[$i]->item->getContentNotes();
			$itemNotes = $ci->reserveList[$i]->item->getNotes();
			$instructorNotes = $ci->reserveList[$i]->getNotes();
			$callNumber = $ci->reserveList[$i]->item->physicalCopy->getCallNumber();
			$reserveDesk = $ci->reserveList[$i]->item->physicalCopy->getOwningLibrary();
			
			if ($ci->reserveList[$i]->item->isHeading())
			{
				//echo "headings";
				echo '	<tr align="left" valign="middle" class="'.$rowClass.'">'
			.    '		<td width="1%" valign="top" id="headingCell2">&nbsp;</td>'
		    .    '		<td width="100%" id="headingCell2">';
		    echo '<span class=headingText>'.$title.'</a><br>';
			} else {

			//$rowClass = ($rowNumber++ % 2) ? "evenRow" : "oddRow";

			$reserveItem = new reserveItem($ci->reserveList[$i]->getItemID());
			$itemIcon = $reserveItem->getItemIcon();
			$itemGroup = $reserveItem->itemGroup;

			if ($reserveItem->isPhysicalItem()) {
				//move to config file
				$viewReserveURL = $g_reservesViewer . $ci->reserveList[$i]->item->getLocalControlKey();
			} else {
				$viewReserveURL = "reservesViewer.php?viewer=" . $user->getUserID() . "&reserve=" . $ci->reserveList[$i]->getReserveID();// . "&location=" . $ci->reserveList[$i]->item->getURL();
			}

			echo '	<tr align="left" valign="middle" class="'.$rowClass.'">'
			.    '		<td width="1%" valign="top"><img src="'.$itemIcon.'" alt="text" width="24" height="20"></td>'
		    .    '		<td width="100%">';
		    
		    /*
		    if (!$reserveItem->isPhysicalItem()) {
		    		echo '<a href="'.$viewReserveURL.'" target="_blank">'.$ci->reserveList[$i]->item->getTitle().'</a>';
		    } else {
		            echo $ci->reserveList[$i]->item->getTitle();
		            if ($ci->reserveList[$i]->item->getLocalControlKey()) {echo ' <a href="'.$viewReserveURL.'" target="_blank">(more info)</a>';}
		    }

		    echo '. '.$ci->reserveList[$i]->item->getAuthor().'</td></tr>';
		    */
	///START Changes to Display Metadata fields on author/title sort screen - 6.1.2005
		if (!$reserveItem->isPhysicalItem()) {
	    	echo '<a href="'.$viewReserveURL.'" target="_blank" class="itemTitle">'.$title.'</a><br>';
	        if ($author) {echo '<span class="itemAuthor">'.$author.'</span><br>';}
	    } else {
	      echo '<span class="itemTitleNoLink">'.$title.'</span><br>'; 
	      if ($author) {echo '<span class="itemAuthor">'.$author.'</span><br>';}
          if ($callNumber) {echo '<span class="itemMeta">'.$callNumber.'</span><br>';}
          
          echo '<span class="itemMetaPre">On Reserve at:</span> <span class="itemMeta"> '.$reserveDesk.'</span>';
          if ($ci->reserveList[$i]->item->getLocalControlKey())
          	{echo ' &gt;&gt; <a href="'.$viewReserveURL.'" target="_blank" class="strong">more info</a>';}
          echo '<br>';
	    }

	    /*
	    if ($url)
	    {
	    	echo '<span class="itemMetaPre">URL:</span><span class="itemMeta"> '.$url.'</span><br>';
	    }
	    */
	    if ($performer)
	    {
	    	echo '<span class="itemMetaPre">Performed by:</span><span class="itemMeta"> '.$performer.'</span><br>';
	    }
	    if ($volTitle)
	    {
	    	echo '<span class="itemMetaPre">From:</span><span class="itemMeta"> '.$volTitle.'</span><br>';
	    }
	    if ($volEdition)
	    {
	    	echo '<span class="itemMetaPre">Volume/Edition:</span><span class="itemMeta"> '.$volEdition.'</span><br>';
	    }
	    if ($pagesTimes)
	    {
	    	echo '<span class="itemMetaPre">Pages/Time:</span><span class="itemMeta"> '.$pagesTimes.'</span><br>';
	    }
	    if ($source)
	    {
	    	echo '<span class="itemMetaPre">Source/Year:</span><span class="itemMeta"> '.$source.'</span><br>';
	    }
	    

		}
		if ($contentNotes)
	    {
	        echo '<span class="noteType">Content Note:</span>&nbsp;<span class="noteText">'.$contentNotes.'</span><br>';
	    }
	    if ($itemNotes) 
	    {
	    
	    	for ($n=0; $n<count($itemNotes); $n++)
	    	{
            	$type = strtolower($itemNotes[$n]->getType());
            	//if ($type == "content") {
            	if ($user->dfltRole >= $g_permission['staff'] || $type == "content") {
	            	echo '<span class="noteType">'.ucfirst($type).' Note:</span>&nbsp;<span class="noteText">'.$itemNotes[$n]->getText().'</span><br>';
            	}
	        }
	    }
	    if ($instructorNotes)
	    {
	    
	    	for ($n=0; $n<count($instructorNotes); $n++)
	        {
	        	echo '<span class="noteType">Instructor Note:</span>&nbsp;<span class="noteText">'.$instructorNotes[$n]->getText().'</span><br>';
	        }
	    }
	    echo '</td></tr>';
	}
	
	//END Changes to Display Metadata fields on author/title sort screen - 6.1.2005

	//End Loop Through Records

	echo '	    	<tr align="left" valign="middle" class="headingCell1">'
	.	 '             	<td valign="top">&nbsp;</td>'
	.	 '             	<td width="100%"><div align="right"> </div></td>'
	.	 '	    	</tr>'
	.    '		</table>'
	.    '		</td>'
	.	 '	</tr>'
	.	 '	<tr>'
	.    '		<td colspan="2">&nbsp;</td>'
	.	 '	</tr>'
	.	 '	<tr>'
	.    '		<td colspan="2"><div align="right">'
	.    '		<table width="100%" border="0" cellspacing="0" cellpadding="5">'
	.	 '	    	<tr>'
	.	 '             	<td width="100%">&nbsp;</td>'
	.	 '             	<td><div align="right"></div></td>'
	.	 '             	<td><div align="right"></div></td>'
	.	 '             	<td><div align="right"><input type="submit" name="saveOrder" value="Save Order"></div></td>'
	.	 '	    	</tr>'
	.    '		</table></div>'
	.    '		</td>'
	.	 '	</tr>';
	echo '			<tr>';
	echo '				<td colspan="2" width ="100%" align="center" valign="middle" class="small"><a href="index.php?cmd=editClass&ci='.$ci->getCourseInstanceID().'">Return to Edit Class</a></td>';
	echo '        </tr>';
	echo	 '	<tr>'
	.    '		<td colspan="2"><img src="images/spacer.gif" width="1" height="15"></td>'
	.	 '	</tr>'
	.	 '	</form>'
	.	 '	</table>';

}

function displayCustomSort($user,$ci)
{
	global $g_reservesViewer, $g_permission;

	echo '      <table width="90%" border="0" cellspacing="0" cellpadding="0" align="center">';
	echo '        <tr>';
	echo '          <td width="140%" colspan="2"><img src="images/spacer.gif" width="1" height="5"> </td>';
	echo '        </tr>';
	echo'			<FORM METHOD=POST NAME="customSortScreen" ACTION="index.php">';
	echo'			<INPUT TYPE="HIDDEN" NAME="cmd" VALUE="customSort">'
	.	 '			<INPUT TYPE="HIDDEN" NAME="ci" VALUE="'.$ci->getCourseInstanceID().'">';
	echo '			<tr>';
	echo '				<td colspan="2" width ="100%" align="center" valign="middle" class="small"><a href="index.php?cmd=editClass&ci='.$ci->getCourseInstanceID().'">Return to Edit Class</a></td>';
	echo '        </tr>';
	
	if (!$ci->containsHeading) {
		echo '        <tr>';
		echo '    		<td width="35%" align="left" valign="middle" bgcolor="#CCCCCC" class="borders"><div align="center"><span class="strong">Sort  by:</span> [ <a href="index.php?cmd=customSort&ci='.$ci->getCourseInstanceID().'&sortBy=title" class="editlinks">title</a> ] [ <a href="index.php?cmd=customSort&ci='.$ci->getCourseInstanceID().'&sortBy=author" class="editlinks">author</a>            ] <!--[ <a href="index.php?cmd=customSort&ci='.$ci->getCourseInstanceID().'" class="editlinks">custom</a> ]--></div></td>';
		echo '          <td width="65%" align="left" valign="top">&nbsp;</td>';
		echo '        </tr>';
	}
	echo '        <tr>';
	echo '          <td colspan="2" align="left" valign="top"><div align="right">';
	echo '            <table width="100%" border="0" cellspacing="0" cellpadding="5">';
	echo '              <tr>';
	echo '                <td width="100%">&nbsp;</td>';
	echo '                <td><div align="right">';
	echo '                  <input type="button" name="reset1" value="Reset to Saved Order" onClick="javascript:this.form.submit();">';
	echo '                </div></td>';
	echo '                <td><div align="right">';
	echo '                  <input type="submit" name="saveOrder" value="Save Order">';
	echo '                </div></td>';
	echo '              </tr>';
	echo '            </table>';
	echo '          </div></td>';
	echo '        </tr>';
	echo '        <tr>';
	echo '          <td colspan="2" align="left" valign="top"><div align="right"></div></td>';
	echo '        </tr>';
	echo '        <tr>';
	echo '          <td colspan="2" align="left" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0">';
	echo '            <tr align="left" valign="top">';
	echo '              <td class="headingCell1"><div align="center">COURSE';
	echo '                MATERIALS</div></td>';
	echo '              <td width="75%">&nbsp;</td>';
	echo '            </tr>';
	echo '          </table></td>';
	echo '        </tr>';
	echo '        <tr>';
	echo '          <td colspan="2" align="left" valign="top" class="borders"><table width="100%" border="0" cellpadding="2" cellspacing="0" class="displayList">';
	echo '            <tr align="left" valign="middle">';
	echo '              <td width="1%" valign="top" bgcolor="#FFFFFF" class="headingCell1">&nbsp;</td>';
	echo '              <td width="60%" bgcolor="#FFFFFF" class="headingCell1">'.count($ci->reserveList).' Item(s) On Reserve</td>';
	echo '              <td width="10%" class="headingCell1">Sort Order</td>';
	echo '            </tr>';

	//Begin Loop Through Records
		$rowNumber = 0;
		$oldValue = array();
		for($i=0;$i<count($ci->reserveList);$i++)
		{
			$rowClass = ($rowNumber++ % 2) ? "evenRow" : "oddRow";
			
			$ci->reserveList[$i]->getItem();
			$ci->reserveList[$i]->item->getPhysicalCopy();
			$title = $ci->reserveList[$i]->item->getTitle();
			$author = $ci->reserveList[$i]->item->getAuthor();
			$url = $ci->reserveList[$i]->item->getURL();
			$performer = $ci->reserveList[$i]->item->getPerformer();
			$volTitle = $ci->reserveList[$i]->item->getVolumeTitle();
			$volEdition = $ci->reserveList[$i]->item->getVolumeEdition();
			$pagesTimes = $ci->reserveList[$i]->item->getPagesTimes();
			$source = $ci->reserveList[$i]->item->getSource();
			$contentNotes = $ci->reserveList[$i]->item->getContentNotes();
			$itemNotes = $ci->reserveList[$i]->item->getNotes();
			$instructorNotes = $ci->reserveList[$i]->getNotes();
			$callNumber = $ci->reserveList[$i]->item->physicalCopy->getCallNumber();
			$reserveDesk = $ci->reserveList[$i]->item->physicalCopy->getOwningLibrary();
				
			if ($ci->reserveList[$i]->item->isHeading())
			{
				//echo "headings";
				echo '	<tr align="left" valign="middle" class="'.$rowClass.'">'
			/*
			.    '		<td width="1%" valign="top" id="headingCell2">&nbsp;</td>'
		  .    '		<td width="60%" id="headingCell2">';
		  echo '<span class=headingText>'.$title.'</a><br>';
		  */
		    .    '		<td width="61%" valign="top" colspan="2" id="headingCell2"><span class=headingText>'.$title.'</span><br>';
			} else {

				//$rowClass = ($rowNumber++ % 2) ? "evenRow" : "oddRow";

				$reserveItem = new reserveItem($ci->reserveList[$i]->getItemID());
				$itemIcon = $reserveItem->getItemIcon();
				$itemGroup = $reserveItem->itemGroup;

				if ($reserveItem->isPhysicalItem()) {
					//move to config file
					$viewReserveURL = $g_reservesViewer . $ci->reserveList[$i]->item->getLocalControlKey();
				} else {
					$viewReserveURL = "reservesViewer.php?viewer=" . $user->getUserID() . "&reserve=" . $ci->reserveList[$i]->getReserveID();// . "&location=" . $ci->reserveList[$i]->item->getURL();
				}

				echo '	<tr align="left" valign="middle" class="'.$rowClass.'">'
				.    '		<td width="1%" valign="top"><img src="'.$itemIcon.'" alt="text" width="24" height="20"></td>'
		  	  .    '		<td width="60%">';
		    
			    /*
			    if (!$reserveItem->isPhysicalItem()) {
			    		echo '<a href="'.$viewReserveURL.'" target="_blank">'.$ci->reserveList[$i]->item->getTitle().'</a>';
		  	  } else {
		    	        echo $ci->reserveList[$i]->item->getTitle();
		      	      if ($ci->reserveList[$i]->item->getLocalControlKey()){
		        	    	echo ' <a href="'.$viewReserveURL.'" target="_blank">(more info)</a>';
		          	  }
			    }
	
			    echo '. '.$ci->reserveList[$i]->item->getAuthor().'</td>';
			    */
			  ///START Changes to Display Metadata fields on custom sort screen - 6.1.2005
		    if (!$reserveItem->isPhysicalItem()) {
		    	echo '<a href="'.$viewReserveURL.'" target="_blank" class="itemTitle">'.$title.'</a><br>';
	  	     if ($author) {echo '<span class="itemAuthor">'.$author.'</span><br>';}
	    	} else {
	      	echo '<span class="itemTitleNoLink">'.$title.'</span><br>'; 
		      if ($author) {echo '<span class="itemAuthor">'.$author.'</span><br>';}
  	      if ($callNumber) {echo '<span class="itemMeta">'.$callNumber.'</span><br>';}
          
    	    echo '<span class="itemMetaPre">On Reserve at:</span> <span class="itemMeta"> '.$reserveDesk.'</span>';
      	  if ($ci->reserveList[$i]->item->getLocalControlKey())
        		{echo ' &gt;&gt; <a href="'.$viewReserveURL.'" target="_blank" class="strong">more info</a>';}
          echo '<br>';
	    	}

		    /*
		    if ($url)
	  	  {
	    		echo '<span class="itemMetaPre">URL:</span><span class="itemMeta"> '.$url.'</span><br>';
		    }
		    */
	  	  if ($performer)
	    	{
	    		echo '<span class="itemMetaPre">Performed by:</span><span class="itemMeta"> '.$performer.'</span><br>';
		    }
		    if ($volTitle)
		    {
	  	  	echo '<span class="itemMetaPre">From:</span><span class="itemMeta"> '.$volTitle.'</span><br>';
	    	}
		    if ($volEdition)
		    {
	  	  	echo '<span class="itemMetaPre">Volume/Edition:</span><span class="itemMeta"> '.$volEdition.'</span><br>';
	    	}
		    if ($pagesTimes)
		    {
	  	  	echo '<span class="itemMetaPre">Pages/Time:</span><span class="itemMeta"> '.$pagesTimes.'</span><br>';
	    	}
		    if ($source)
		    {
	  	  	echo '<span class="itemMetaPre">Source/Year:</span><span class="itemMeta"> '.$source.'</span><br>';
	    	}
	    
			}

			if ($contentNotes)
	    {
	        echo '<span class="noteType">Content Note:</span>&nbsp;<span class="noteText">'.$contentNotes.'</span><br>';
	    }
	    if ($itemNotes) 
	    {
	    
	    	for ($n=0; $n<count($itemNotes); $n++)
	    	{
            	$type = strtolower($itemNotes[$n]->getType());
            	//if ($type == "content") {
            	if ($user->dfltRole >= $g_permission['staff'] || $type == "content") {
	            	echo '<span class="noteType">'.ucfirst($type).' Note:</span>&nbsp;<span class="noteText">'.$itemNotes[$n]->getText().'</span><br>';
            	}
	        }
	    }
	    if ($instructorNotes)
	    {
	    
	    	for ($n=0; $n<count($instructorNotes); $n++)
	        {
	        	echo '<span class="noteType">Instructor Note:</span>&nbsp;<span class="noteText">'.$instructorNotes[$n]->getText().'</span><br>';
	        }
	    }
	    echo '</td>';
	    //END Changes to Display Metadata fields on custom sort screen - 6.1.2005  
		    echo '              <td width="10%" valign="middle" class="borders"><div align="center">';
			//echo '                <input type="hidden" name="'.$ci->reserveList[$i]->reserveID.'" value="'.$ci->reserveList[$i]->sortOrder.'">';
			//echo '                <input name="reserveSortIDs['.$ci->reserveList[$i]->reserveID.'][newSortOrder]" value="'.$ci->reserveList[$i]->sortOrder.'" type="text" size="3" onChange="javascript:if (this.value <=0 || this.value > '.count($ci->reserveList).' || !parseInt(this.value)) {alert (\'Invalid value\')} else {updateSort(document.forms.customSortScreen, '.$ci->reserveList[$i]->reserveID.', this.value, this.name)}">';
			echo '                <input type="hidden" name="'.$ci->reserveList[$i]->reserveID.'" value="'.($i+1).'">';
		  echo '                <input name="reserveSortIDs['.$ci->reserveList[$i]->reserveID.'][newSortOrder]" value="'.($i+1).'" type="text" size="3" onChange="javascript:if (this.value <=0 || this.value > '.count($ci->reserveList).' || !parseInt(this.value)) {alert (\'Invalid value\')} else {updateSort(document.forms.customSortScreen, '.$ci->reserveList[$i]->reserveID.', this.value, this.name)}">';
			echo '              </td>';
			echo '            </tr>';

		}

	//End Loop Through Records

	echo '';
	echo '            <tr align="left" valign="middle" class="headingCell1">';
	echo '              <td valign="top">&nbsp;</td>';
	echo '              <td><div align="right"> </div>';
	echo '              </td>';
	echo '              <td>&nbsp;</td>';
	echo '            </tr>';
	echo '          </table></td>';
	echo '        </tr>';
	echo '        <tr>';
	echo '          <td colspan="2">&nbsp;</td>';
	echo '        </tr>';
	echo '        <tr>';
	echo '          <td colspan="2"><div align="right">';
	echo '            <table width="100%" border="0" cellspacing="0" cellpadding="5">';
	echo '              <tr>';
	echo '                <td width="100%">&nbsp;</td>';
	echo '                <td><div align="right">';
	//echo '                    <input type="button" name="reset1" value="Reset to Original Values" onClick="javascript:resetForm(this.form)">';
	echo '                    <input type="button" name="reset1" value="Reset to Saved Order" onClick="javascript:this.form.submit();">';
	echo '                  </div>';
	echo '                </td>';
	echo '                <td>&nbsp;</td>';
	echo '                <td><div align="right">';
	echo '                    <input type="submit" name="saveOrder" value="Save Order">';
	echo '                  </div>';
	echo '                </td>';
	echo '              </tr>';
	echo '            </table>';
	echo '          </div></td>';
	echo '        </tr>';
	echo '			<tr>';
	echo '				<td colspan="2" width ="100%" align="center" valign="middle" class="small"><a href="index.php?cmd=editClass&ci='.$ci->getCourseInstanceID().'">Return to Edit Class</a></td>';
	echo '        </tr>';
	echo '        <tr>';
	echo '          <td colspan="2"><img src="images/spacer.gif" width="1" height="15"></td>';
	echo '        </tr>';
	echo '			</FORM>';
	echo '      </table>';

	}

}

?>