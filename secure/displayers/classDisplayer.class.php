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
require_once("common.inc.php");
require_once("classes/terms.class.php");

class classDisplayer 
{
	function displayStaffHome($user)
	{
		echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr><td width=\"140%\"><img src=\../images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>\n";
		echo "	<tr>\n";
		echo "		<td align=\"center\" valign=\"top\">\n";
		echo "			<table width=\"66%\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\">\n";
		echo "				<tr class=\"headingCell1\"><td width=\"33%\">Process Materials</td><td width=\"33%\">Classes</td><!--<td width=\"33%\">Departments</td>--></tr>\n";
		echo "				<tr align=\"left\" valign=\"top\">\n";
		echo "					<td width=\"33%\" class=\"borders\"><p align=\"center\"><span class=\"strong\">Unprocessed Requests:</span> <!--(21/200)--></p>\n";
		echo "						<ul>\n";
		echo "							<li><a href=\"index.php?cmd=displayRequest\" align=\"center\">Process Requests</a><!--Goes to staff-mngClass-requests.html --></li>\n";
		echo "							<li><a href=\"index.php?cmd=addDigitalItem\" align=\"center\">Add an Electronic Item</a><!--Goes to class lookup, then staff-mngClass-addItem-dig.html --></li>\n";
		echo "							<li><a href=\"index.php?cmd=addPhysicalItem\">Add a Physical Item</a><!--Goes to class lookup, then staff-mngClass-addItem-phys.html --></li>\n";
		echo "							<!--<li><a href=\"index.php?cmd=physicalItemXListing\">Physical Item Cross-listings </a>--><!--Goes to staff-mngClass-phys-XList1.html --></li>\n";
		echo "						</ul>\n";
		echo "					</td>\n";
		echo "					<td width=\"33%\" class=\"borders\">\n";
		echo "						<ul>\n";
		echo "							<li><a href=\"index.php?cmd=createClass\" align=\"center\">Add Class</a></li>\n";
		echo "							<li><a href=\"index.php?cmd=staffEditClass\">Edit Class</a></li>\n";
		echo "							<!--<li><a href=\"index.php?cmd=deleteClass\">Delete Class</a>--?<!--Goes to staff-mngClass-deleteClass.html --></li>\n";
		echo "							<li><a href=\"index.php?cmd=reactivateClass\" align=\"center\">Reactivate Course</a></li>\n";
		echo "							<!--<li><a href=\"index.php?cmd=copyCourse\">Copy Reserve List or Merge Classes</a>--><!--Links to staff-mngClass-CopyList1.html --></li>\n";
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
		echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\" align=\"center\">";
        echo "	<tr><td width=\"140%\" colspan=\"2\"><img src=\../images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>";
        echo "	<tr> ";
        echo "		<td width=\"70%\" align=\"left\" valign=\"top\"> ";
        echo "			<p><a href=\"index.php?cmd=reactivateClass\" class=\"titlelink\">Reactivate Reserve Materials </a><br>";
        echo "			Reactivate a course you have taught in the past or reserve readings you have used in the past</p>";
        echo "			<p><a href=\"index.php?cmd=createClass\" class=\"titlelink\">Create a New Course</a><br>";
        echo "      	Create a new course and reserves list from scratch.</p>";
        echo "			<p><a href=\"index.php?cmd=myReserves\" class=\"titlelink\">Edit an Existing Course</a><br>";
        echo "			Advanced management of your classes. Edit class title, crosslistings, proxies, reserve materials, enrollment, and much more.</p>";
        echo "		</td>";
        /*
        echo "		<td width=\"30%\" align=\"left\" valign=\"top\">";
		echo "			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\">";
        echo "				<tr>";
		echo "					<td class=\"headingCell1\">Quick Links</td>";
        echo "      		</tr>";
        echo "				<tr> ";
        echo "					<td align=\"left\" valign=\"top\" class=\"borders\">";
        echo "						<ul>";
        echo "						<li class=\"small\"><a href=\"link\">Sort my Reserves</a></li>";
        echo "			            	<!-- This should send them to a class selection screen that includes current and future";
		echo "							classes, then straight to the \"sort reserves\" screen (myReserves/staff-myReserves-sort.html -->";
        echo "            			<li class=\"small\"><a href=\"link\">Annotate my Reserves</a></li>";
        echo "            				<!--This should send them to a course select screen with current and future classes,";
		echo "							then to a screen with all reserve readings listed with note fields. -->";
        echo "            			<li class=\"small\"><a href=\"link\">View my Reserves lists</a></li>";
        echo "            				<!-- This goes to course select screen with all courses past, current, and future,";
		echo "							with current and future on top. Clicking into class goes to basic student view of class,";
		echo "							no edit features available. -->";
        echo "            			<li class=\"small\"><a href=\"link\">Export my Reserves to Courseware class (Blackboard, Learnlink, etc.)</a></li>";
		echo "			  				<!-- This goes to mockup page instr-mngClasses-Export1.html, which gives instructions";
		echo "			  				on how to export readings to Courseware. -->";
        echo "            			<li class=\"small\"><a href=\"link\">Get my URLs</a></li>";
		echo "							<!-- This goes to course select, then displays a simple list of readings with the URLs";
		echo "							showing underneath them (inst-manageClasses-getURLs.html). For use with Courseware, learnlink, etc. -->";
        echo "            			<li class=\"small\"><a href=\"link\">Manage Enrollment for my classes</a></li>";
		echo "							<!-- This goes to course select, then straight to Enrollment queue for that class. -->";
        echo "         				</ul>";
        echo "					</td>";
        echo "    			</tr>";
        echo "      		<tr>";
        echo "        			<td>&nbsp;</td>";
       	echo "       		</tr>";
      	echo "			</table>";
        echo "		</td>";
        */
      	echo "	</tr>";
        echo "	<tr> ";
     	echo "		<td colspan=\"2\"><img src=\../images/spacer.gif\" width=\"1\" height=\"15\"></td>";
        echo "	</tr>";
      	echo "</table>";
	}
	
	function displayEditClass($user, $ci)
	{
		global $g_permission;
					
		echo('<FORM METHOD=POST NAME="editReserves" ACTION="index.php">');
	    echo('<INPUT TYPE="HIDDEN" NAME="cmd" VALUE="editClass">');
		echo('<INPUT TYPE="HIDDEN" NAME="ci" VALUE="'.$ci->getCourseInstanceID().'">');
	
		echo '<table width="90%" border="0" cellspacing="0" cellpadding="0" align="center">';
		echo '<tr>';
		echo "<td width =\"140%\" align=\"right\" valign=\"middle\" class=\"small\" align=\"right\"><a href=\"javascript:openWindow('&cmd=previewReservesList&ci=".$ci->courseInstanceID . "','width=800,height=600');\">Preview Student View</a> | <a href=\"index.php\">Exit class</a></td>\n";
		echo '</tr>';
		echo	'<tr>'
		.	'	<td width="140%" colspan="2"><img src=../images/spacer.gif" width="1" height="5"> </td>'
		.	'</tr>'
		.	'<tr>'
		.   ' 	<td width="75%" height="79" align="left" valign="top" >'
		.   ' 		<table width="100%" border="0" cellspacing="0" cellpadding="2">'
		.  	'		<tr>'
		.   '         	<td width="80%" align="left" valign="top" class="courseTitle">'.$ci->course->displayCourseNo() . " " . $ci->course->getName().'</td>'
		.   '          	<td align="left" valign="top">[ <a href="index.php?cmd=editTitle&ci='.$ci->getCourseInstanceID().'" class="editlinks">edit title</a> ]</td>'
		.   '       </tr>'
		.   '       <tr align="left" valign="top">'
		.   '         <td width="80%" class="courseHeaders">Instructor(s): ';
		
		for($i=0;$i<count($ci->instructorList);$i++) {
			echo '<a href="mailto:'.$ci->instructorList[$i]->getEmail().'">'.$ci->instructorList[$i]->getName().'</a>&nbsp;';
		}
		echo '		  </td>';
		echo '        <td>[ <a href="index.php?cmd=editInstructors&ci='.$ci->getCourseInstanceID().'" class="editlinks">edit instructors</a> ]</td>'
		.    '      </tr>';
		echo '      <tr align="left" valign="top">'
		.    '        	<td width="80%"><span class="courseHeaders">Cross-listings: </span>';
		if (count($ci->crossListings)==0) {
			echo 'None';
		} 
		else {
			for ($i=0; $i<count($ci->crossListings); $i++) {
				if ($i>0) echo',&nbsp;';
				echo $ci->crossListings[$i]->displayCourseNo();
			}
		}
		
		echo '			</td>'
		.    '         	<td>[ <a href="index.php?cmd=editCrossListings&ci='.$ci->getCourseInstanceID().'" class="editlinks">edit crosslistings</a> ]</td>'
		.    '      </tr>'
		. 	 '		<tr align="left" valign="top">'
		.	 '			<td>&nbsp;</td>'
		.    '			<td>&nbsp;</td>'
		.	 '		</tr>'
		.    '		<tr align="left" valign="top">'
		.	 '	    	<td><span class="courseHeaders">Proxies:</span>';
		if (count($ci->proxies)==0) {
			echo '&nbsp;None';
		} 
		else {
			for ($i=0; $i<count($ci->proxies); $i++) {
				echo '&nbsp;'.$ci->proxies[$i]->getName();
			}
		}
		echo '			</td>'
		.	 '			<td>';
		if (in_array($user->getUserID(),$ci->instructorIDs) || $user->dfltRole >= $g_permission['staff']) {
			echo '[ <a href="index.php?cmd=editProxies&ci='.$ci->getCourseInstanceID().'" class="editlinks">edit proxies</a> ]';
		}
		echo '			</td>';
		echo	 '	</tr>'
		.	 '		<tr align="left" valign="top">'
		.	 '			<td colspan="2"><div align="center">'
		.	 '				<table width="40%" border="0" cellpadding="5" cellspacing="0" class="borders">'
		.	 '              <tr align="left" valign="middle">'
		.	 '					<td width="50%" bgcolor="#CCCCCC"><span class="strong">Enrollment:</span> <strong><font color="'.common_getStatusDisplayColor($ci->getEnrollment()).'">'.strtoupper($ci->getEnrollment()).'</font></strong></td>'
		.	 '					<td bgcolor="#CCCCCC"><!--<div align="right">[ <a href="index.php?cmd=editEnrollment&ci='.$ci->getCourseInstanceID().'" class="editlinks">edit enrollment</a> ]</div>--></td>'
		.	 '				</tr>'
		.	 '				</table>'
		.	 '			</div></td>'
		.	 '		</tr>'
		.	 '		<tr align="left" valign="top">'
		.	 '			<td height="28" colspan="2"> <div align="left">[ <a href="index.php?cmd=sortReserves&ci='.$ci->getCourseInstanceID().'" class="editlinks">sort materials</a> ] [ <a href="index.php?cmd=displaySearchItemMenu&ci='.$ci->getCourseInstanceID().'" class="editlinks">add new materials</a> ]</div></td>'
		.	 '		</tr>'
		.	 '      </table>'
		.	 '	</td>'
		.	 '</tr>'
		.    '<tr>'
		.    '	<td colspan="2">'
		.    '		<table width="100%" border="0" cellspacing="0" cellpadding="0">'
		.    '		<tr align="left" valign="top">'
		.    '		   	<td class="headingCell1"><div align="center">COURSE MATERIALS</div></td>'
		.    '        	<td width="75%" valign="bottom"><div align="right" class="small"><a href="javascript:checkAll(document.forms.editReserves, 1)">check all</a> | <a href="javascript:checkAll(document.forms.editReserves, 0)">uncheck all</a></div></td>'
		.    '      </tr>'
		.    '     	</table>'
		.    '   </td>'
		.    '</tr>'
		.    '<tr>'
		.    '	<td colspan="2" align="left" valign="top" class="borders">'
		.    '		<table width="100%" border="0" cellpadding="2" cellspacing="0" class="displayList">'
		.    '      <tr align="left" valign="middle">'
		.    '          <td valign="top" bgcolor="#FFFFFF" class="headingCell1">&nbsp;</td>'
		.	 '			<td bgcolor="#FFFFFF" class="headingCell1">'.count($ci->reserveList).' Item(s) On Reserve</td>'
		.    '          <td class="headingCell1">Status</td>'
		.    '          <td class="headingCell1">Edit</td>'
		.    '          <td class="headingCell1">Select</td>'
		.    '      </tr>';
	
		$rowNumber = 0;
		for($i=0;$i<count($ci->reserveList);$i++)
		{
			$ci->reserveList[$i]->getItem();
			
			if ($ci->reserveList[$i]->item->isHeading())
			{
				//echo "headings";
			} else {	
	
				$rowClass = ($rowNumber++ % 2) ? $rowClass = "evenRow" : "oddRow";
				
				// begin remove
				$status = $ci->reserveList[$i]->getStatus();
				$activationDate = $ci->reserveList[$i]->activationDate;
				$todaysDate = date ('Y-m-d');
				//override status of ACTIVE and make HIDDEN if reserve is Active w/a future activation date
				if (($status == 'ACTIVE') && ($activationDate > $todaysDate)) {$status = 'HIDDEN';}
				$reserveItem = new reserveItem($ci->reserveList[$i]->getItemID());
				$itemIcon = $reserveItem->getItemIcon();
				$itemGroup = $reserveItem->itemGroup;
				
				$url = $reserveItem->getURL();
				$performer = $reserveItem->getPerformer();
				$volTitle = $reserveItem->getVolumeTitle();
				$volEdition = $reserveItem->getVolumeEdition();
				$pagesTimes = $reserveItem->getPagesTimes();
				$source = $reserveItem->getSource();
				
				if ($reserveItem->isPhysicalItem()) {
					$reserveItem->getPhysicalCopy();
					$callNumber = $reserveItem->physicalCopy->getCallNumber();
					$reserveDesk = $reserveItem->physicalCopy->getOwningLibrary();
				}

				$contentNotes = $reserveItem->getContentNotes();
				$itemNotes = $reserveItem->getNotes();
				$instructorNotes = $ci->reserveList[$i]->getNotes();
	
				$statusColor = common_getStatusDisplayColor($status); //add to css -- class = active etc
				//end remove code
				
				$viewReserveURL = "reservesViewer.php?viewer=" . $user->getUserID() . "&reserve=" . $ci->reserveList[$i]->getReserveID();// . "&location=" . $ci->reserveList[$i]->item->getURL();
				if ($reserveItem->isPhysicalItem()) {
					//move to config file
					$viewReserveURL = "http://libcat1.cc.emory.edu/uhtbin/cgisirsi/x/0/5?searchdata1=" . $ci->reserveList[$i]->item->getLocalControlKey();
				}
				echo '<tr align="left" valign="middle" class="'.$rowClass.'">'
	            .    '	<td width="4%" valign="top"><img src="'.$itemIcon.'" alt="text" width="24" height="20"></td>'
	            .    '	<td width="72%">'.$ci->reserveList[$i]->item->getAuthor().'&nbsp;';
	            if (!$reserveItem->isPhysicalItem()) {
	            	echo '<a href="'.$viewReserveURL.'" target="_blank">'.$ci->reserveList[$i]->item->getTitle().'</a>';
	            } else {
	            	echo '<em>'.$ci->reserveList[$i]->item->getTitle().'</em>.';
	            	if ($callNumber) {echo '<br>'.$callNumber;}
	            	echo '<br>On Reserve At: '.$reserveDesk.' (<a href="'.$viewReserveURL.'" target="_blank">more info</a>)';
	            }
	            
	            /*
	            if ($url)
	            
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
	            
	            if ($contentNotes)
	            {
	            	echo '<br><span class="noteType">Content Note:</span>&nbsp;<span class="noteText">'.$contentNotes.'</span>';
	            }
	            if ($itemNotes) 
	            {
	            	for ($n=0; $n<count($itemNotes); $n++)
	            	{
	            		if ($user->dfltRole >= $g_permission['staff'] || $itemNotes[$n]->getType() == "Instructor" || $itemNotes[$n]->getType() == "Content") {
	            			echo '<br><span class="noteType">'.$itemNotes[$n]->getType().' Note:</span>&nbsp;<span class="noteText">'.$itemNotes[$n]->getText().'</span>';
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
	            
	            echo '</td>';
	            echo    '  <td width="11%" valign="middle" class="borders"><div align="center"><font color="'.$statusColor.'"><strong>'.$status.'</strong></font></div></td>'
	            .    '	<td width="6%" valign="middle" class="borders"><div align="center">';
	            if (!$reserveItem->isPhysicalItem() || $user->getDefaultRole() >= $g_permission['staff']) {
	            	echo '<a href="index.php?cmd=editItem&reserveID='.$ci->reserveList[$i]->getReserveID().'">edit</a>';
	            } else {
	            	echo '&nbsp;';
	            }
	            echo '		</div></td>';
	            echo    '	<td width="7%" valign="middle" class="borders"><div align="center"><input type="checkbox" name="reserve['.$ci->reserveList[$i]->getReserveID().']" value="'.$ci->reserveList[$i]->getReserveID().'"></div></td>'
	            .	 '</tr>';
			}
		}
	
		echo '		<tr align="left" valign="middle" class="headingCell1">'
		.	 '			<td valign="top">&nbsp;</td>'
		. '				<td colspan="4"><div align="right">'
		. '				<select name="reserveListAction">'
		. '					<option selected>For all Selected Items:</option>'
		. '					<option value="deleteAll">Delete all selected items</option>'
		. '					<option value="activateAll">Set all Selected to ACTIVE</option>'
		. '					<option value="deactivateAll">Set all Selected to INACTIVE</option>'
		. '				</select>'
		. '				&nbsp; '
		. '				<input type="submit" name="modifyReserveList" value="Submit">'
		. '				</div></td>'
		. '			</tr>'
		.	 '		</table>'
		.    '    </td>'
		.	 '</tr>'
		.	 '<tr>'
		.	 '	<td colspan="2"><img src=../images/spacer.gif" width="1" height="15"></td>'
		.	 '</tr>'
		.	 '<tr>'
		.	 '	<td colspan="2"><div align="center" class="strong"><a href="index.php">Exit Class</a></div></td>'
		.	 '</tr>'
		.	 '<tr>'
		.	 '	<td colspan="2"><img src=../images/spacer.gif" width="1" height="15"></td>'
		.	 '</tr>'
		.    '</table>'
		.	 '</form>';
	}
	
	function displayEditTitle($ci, $deptList, $deptID)
	{
		echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "<tr>\n";
		echo "<td width =\"140%\" align=\"right\" valign=\"middle\"><!--<div align=\"right\" class=\"currentClass\">".$ci->course->displayCourseNo()."&nbsp;".$ci->course->getName()."</div>--></td>\n";
		echo "</tr>\n";
		echo " <form action=\"index.php?cmd=editTitle&ci=".$ci->getCourseInstanceID()."\" method=\"post\">\n";
		echo " <tr>\n";
		echo " 	<td width=\"140%\"><img src=\../images/spacer.gif\" width=\"1\" height=\"5\"> </td>\n";
		echo " </tr>\n";
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
		echo "		<tr bgcolor=\"#CCCCCC\"> \n";
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
		echo "          <td align=\"left\" valign=\"middle\"> <input name=\"primaryCourseName\" type=\"text\" size=\"50\" value=\"".addslashes($ci->course->getName())."\"></td>\n";
		echo "          <td align=\"center\" valign=\"middle\"></td>\n";
		echo "		</tr>\n";
		$rowNumber = 0;
		for ($i=0; $i<count($ci->crossListings); $i++) {
			$rowClass = ($rowNumber++ % 2) ? "evenRow" : "oddRow\n";			
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
		echo "			<td align=\"left\" valign=\"middle\"> <input name=\"cross_listings[".$ci->crossListings[$i]->courseAliasID."][courseName]\" type=\"text\" size=\"50\" value=\"".$ci->crossListings[$i]->getName()."\"></td>\n";
		echo "			<td align=\"center\" valign=\"middle\"><input type=\"checkbox\" name=\"deleteCrossListing[".$ci->crossListings[$i]->courseAliasID."]\" value=\"".$ci->crossListings[$i]->courseAliasID."\"></td>\n";
		echo "		</tr>\n";
		}
		echo "		<tr class=\"headingCell1\">\n";
		echo "			<td colspan=\"2\" align=\"left\" valign=\"top\"><div align=\"left\"><input type=\"submit\" name=\"updateCrossListing\" value=\"Update Course Info\"></div></td>\n";
		echo "			<td align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "			<td align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "			<td align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "			<td align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "			<td align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "			<td align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "			<td colspan=\"2\" align=\"left\" valign=\"top\"><div align=\"right\"><input type=\"submit\" name=\"deleteCrossListings\" value=\"Delete Selected\"></div></td>\n";
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
		."          	<td align=\"left\" valign=\"middle\">&nbsp;</td>\n"
		."              <td align=\"left\" valign=\"middle\">&nbsp;</td>\n"
		."              <td align=\"left\" valign=\"middle\">&nbsp;</td>\n"
		."              <td align=\"left\" valign=\"middle\">&nbsp;</td>\n"
		."              <td align=\"left\" valign=\"middle\">&nbsp;</td>\n"
		."              <td align=\"left\" valign=\"middle\">&nbsp;</td>\n"
		."              <td align=\"left\" valign=\"middle\">&nbsp;</td>\n"
		."              <td align=\"left\" valign=\"middle\">&nbsp;</td>\n"
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
		."          	<td align=\"left\" valign=\"middle\"> <div align=\"left\"><input name=\"newCourseName\" type=\"text\" size=\"40\"></div></td>\n"
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
		."          <td><img src=\../images/spacer.gif\" width=\"1\" height=\"15\"></td>\n"
		."        </tr>\n"
		." </table>\n";
	}

	function displayEditInstructors($ci, $instructorList, $addTableTitle, $dropDownDefault, $userType, $removeTableTitle, $removeButtonText)
	{
		echo " <table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">";
		echo "<form name=\"editInstructors\" action=\"index.php?cmd=editInstructors&ci=".$ci->courseInstanceID."\" method=\"post\">";
		echo "<tr>";
		echo "<td colspan=\"3\" align=\"right\" valign=\"middle\"><!--<div align=\"right\" class=\"currentClass\">".$ci->course->displayCourseNo()."</div>--</td>";
		echo " 	</tr>";
		/* Use this logic if we decide to display the course numbers for the cross listings
			for ($i=0; $i<count($ci->crossListings); $i++) {
				echo "<tr>";
				echo "<td colspan=\"3\" align=\"right\" valign=\"middle\"><div align=\"right\" class=\"currentClass\">".$ci->crossListings[$i]->displayCourseNo()."</div></td>";
				echo " 	</tr>";
			}
		*/	
		echo " 	<tr>";
		echo " 		<td colspan=\"3\"><img src=\../images/spacer.gif\" width=\"1\" height=\"5\"> </td>";
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
		echo "                	    		<select name=\"prof\">";
		echo "                           		<option value=\"\" selected>-- ".$dropDownDefault." --</option>";
								foreach($instructorList as $instructor)
								{
									echo "<option value=\"" . $instructor["user_id"] . "\">" . $instructor["full_name"] . "</option>";
								}
		echo "                       		</select>";
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
		echo "         <td align=\"left\" valign=\"top\"><img src=\../images/spacer.gif\" width=\"15\" height=\"1\"></td>";
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
						echo "                   		<td width=\"8%\" valign=\"top\" class=\"borders\"><div align=\"center\"><input type=\"checkbox\" name=\"".$userType."[".$instruct[$i]->userID."]\" value=\"".$instruct[$i]->userID."\"></div></td>";
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
		echo "     	<td colspan=\"3\"><img src=\../images/spacer.gif\" width=\"1\" height=\"15\"></td>";
		echo " 	</tr>";
		echo " </form>";
		echo " </table>";
	}

	function displayEditProxies($ci, $proxyList, $request)
	{
		echo "<form action=\"index.php?cmd=editProxies&ci=".$ci->getCourseInstanceID()."\" method=\"POST\">\n";
		echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr>\n";
		echo "		<td colspan=\"3\"><img src=\../images/spacer.gif\" width=\"1\" height=\"5\"> </td>\n";
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
		echo "									<hr align=\"center\" width=\"150\">\n";
		echo "									<span class=\"strong\">Search Results:</span>\n";
		
		$addProxyDisabled = "DISABLED";
		if (is_array($proxyList) && !empty($proxyList)){
			$addProxyDisabled = "";
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
		echo "		<td align=\"left\" valign=\"top\"><img src=\../images/spacer.gif\" width=\"15\" height=\"1\"></td>\n";
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
				echo "							<tr align=\"left\" valign=\"middle\">\n";
				echo "								<td bgcolor=\"#CCCCCC\">". $proxy->getName() ."</td>\n";
				echo "								<td width=\"8%\" valign=\"top\" bgcolor=\"#CCCCCC\" class=\"borders\" align=\"center\">\n";
				echo "									<input type=\"checkbox\" name=\"proxies[]\" value=\"".$proxy->getUserID()."\">\n";
				echo "								</td>\n";
				echo "							</tr>\n";
			}
		} else {
			echo "							<tr align=\"left\" valign=\"middle\"><td bgcolor=\"#CCCCCC\">&nbsp;</td><td width=\"8%\" valign=\"top\" bgcolor=\"#CCCCCC\" class=\"borders\" align=\"center\">&nbsp;</td></tr>\n";
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
		echo "	<tr><td colspan=\"3\" align=\"center\"> <a href=\"index.php?cmd=editClass&ci=". $ci->getCourseInstanceID() ."\" class=\"strong\">Return to Class</a></div></td></tr>\n";
		echo "	<tr><td colspan=\"3\"><img src=\../images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
		echo "</table>\n";
		echo "</form>\n";
	}
	
	
	function displayReactivate($userList, $courses, $courseInstances, $nextPage, $request, $hidden_fields=null)
	{
		global $u;
		
		$terms = new terms();
		
	    echo "<form action=\"index.php\" method=\"get\" name=\"reactivate\">\n";
	
		if (is_array($hidden_fields)){
			$keys = array_keys($hidden_fields);
			foreach($keys as $key){
				if (is_array($hidden_fields[$key])){
					foreach ($hidden_fields[$key] as $field){
						echo "<input type=\"hidden\" name=\"".$key."[]\" value=\"". $field ."\">\n";	
						echo 'key: '.$key.'&nbsp;field: '.$field.'<br>'; //kaw
					}
				} else {
					echo "<input type=\"hidden\" name=\"$key\" value=\"". $hidden_fields[$key] ."\">\n";
				}
			}
		}
	    
	    echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr><td width=\"140%\"><img src=\../images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>\n";
	
		if ($u->getUserClass() == 'instructor')
		{
			echo "<tr><td height=\"14\" align=\"left\" valign=\"top\" class=\"helperText\">The classes you have taught in the past are listed below. Choose which class you would like to reactivate.</td></tr>\n";
			echo "	<tr><td width=\"140%\"><img src=\../images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>\n";
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
			
		} else {
			echo "	<tr>\n";
			echo "		<td height=\"14\" align=\"left\" valign=\"top\">\n";
			echo "			<table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\">\n";
			echo "				<tr align=\"left\" valign=\"top\">\n";
			echo "					<td width=\"35%\" align=\"left\" class=\"headingCell1\" align=\"center\">Class Lookup</td>\n";
			echo "					<td width=\"75%\"></td>\n";
			echo "				</tr>\n";
			echo "			</table>\n";
			echo "		</td>\n";
			echo "	</tr>\n";
	
			echo "	<tr>\n";
			echo "		<td align=\"left\" valign=\"top\">\n";
			echo "			<table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"5\" cellspacing=\"0\" class=\"borders\">\n";
			echo "				<tr>\n";
			echo "					<td width=\"50%\" align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" class=\"strong\">1) Select an Instructor:</td>\n";
			echo "					<td align=\"left\" valign=\"middle\">\n";
			echo "						<select name=\"instructor\" onChange=\"this.form.submit();\">\n";
			echo "							<option>-- Select --</option>\n";
			
			foreach ($userList as $aUser){
				$SELECTED = (isset($request['instructor']) && $aUser['user_id'] == $request['instructor']) ? " SELECTED " : "";
				echo "							<option $SELECTED value=\"". $aUser['user_id'] ."\">". $aUser['full_name'] ."</option>\n";
			}
			echo "						</select>\n";
			echo "					</td>\n";
			echo "				</tr>\n";
			echo "				<tr>\n";
			echo "					<td width=\"50%\" align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" class=\"strong\">2) Select a Course:</td>\n";
			echo "					<td align=\"left\" valign=\"middle\">\n";
		
			
			if (!isset($request['instructor']))  //if instuctor has been selected
			{
				echo "						<select name=\"select2\" DISABLED><option>-- Select --</option></select>\n";
			} else {
				echo "						<select name=\"course\" onChange=\"this.form.submit();\">\n";
				echo "							<option value=\"null\">-- Select --</option>\n";
				if (is_array($courses) && !empty($courses)){  //and classes have been found allow slection of class
					foreach($courses as $c)
					{
						$SELECTED = (isset($request['course']) && $c->getCourseID() == $request['course']) ? " SELECTED " : "";
						echo "							<option $SELECTED value=\"". $c->getCourseID() ."\">". $c->displayCourseNo() ." ". $c->getName() ."</option>\n";
					}
					echo "						</select>\n";
				} else //otherwise display none found
					echo "						<font color=\red\">There are no classes for this instructor.</font>\n";			
			}
			
			echo "					</td>\n";
			echo "				</tr>\n";
			echo "				<tr>\n";
			echo "					<td width=\"50%\" align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" class=\"strong\">3) Select Class Version:</td>\n";
			echo "					<td align=\"left\" valign=\"middle\">&nbsp;</td>\n";
			echo "				</tr>\n";
			echo "			</table>\n";
			echo "		</td>\n";
			echo "	</tr>\n";
		}
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
			
			echo "							<tr align=\"left\" valign=\"middle\" class=\"$rowClass\">\n";
			echo "								<td width=\"15%\">". $ci->course->displayCourseNo() ."</td>\n";
			echo "								<td>". $ci->course->getName() ."</td>\n";
			echo "								<td width=\"20%\"><div align=\"center\">". $ci->displayTerm() ."</div></td>\n";
			echo "								<td width=\"20%\" align=\"center\"><a href=\"javascript:openWindow('&cmd=previewReservesList&ci=". $ci->getCourseInstanceID() ."');\">preview</a></td>\n";
			echo "								<td width=\"10%\" align=\"center\"><input type=\"radio\" name=\"ci\" value=\"". $ci->getCourseInstanceID() ."\" onClick=\"setSubmit(this.form);\"></td>\n";
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
		echo "	<tr>\n";
		echo "		<td align=\"left\" valign=\"top\">\n";
		echo "			<table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"5\" cellspacing=\"0\" class=\"borders\">\n";
		echo "				<tr align=\"left\" valign=\"middle\">\n";
		echo "					<td height=\"22\" bgcolor=\"#FFFFFF\">\n";
		echo "						<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		echo "							<tr align=\"left\" valign=\"middle\">\n";
		echo "								<td class=\"strong\">4) Activate for:</td>\n";
		
		foreach($terms->getTerms() as $t)
		{
			echo "								<td>\n";
			echo "									<input type=\"radio\" name=\"term\" value=\"". $t->getTermID() ."\" onClick=\"setSubmit(this.form);\">". $t->getTerm() ."\n";
			echo "								</td>\n";
		}	
		echo "							</tr>\n";
		echo "							<tr align=\"left\" valign=\"middle\"><td><input type=\"checkbox\" name=\"restoreProxies\">Restore Proxies</td></tr>\n";
		echo "						</table>\n";
		echo "					</td>\n";
		echo "				</tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr><td align=\"left\" valign=\"top\">&nbsp;</td></tr>\n";
		echo "	<tr><td align=\"left\" valign=\"top\" align=\"center\"><input type=\"submit\" name=\"Submit\" value=\"Re-activate Class\" DISABLED onClick=\"this.form.cmd.value='$nextPage';\"></td></tr>\n";
		echo "	<tr><td align=\"left\" valign=\"top\"><img src=\../images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
		echo "</table>\n";
		
		echo "<script language=\"javaScript\">
			function setSubmit(frm)
			{
				var termSelected   = false;
				var ciSelected     = false;	
			
				for(i=0; i<frm.elements.length; i++) {
					var e = frm.elements[i];
					if (e.type == 'radio') {
						if (e.name == 'term' && e.checked) termSelected = true;
			 			if (e.name == 'ci' && e.checked)   ciSelected = true;
					}
				}
				frm.Submit.disabled = !(termSelected && ciSelected);
			} 
			setSubmit(this.document.forms[0]); //onLoad set submit
		</script>";
		
		echo "</form>\n";
		
		////if (this.form.cithis.form.Submit.disabled=false
	}
	
	
	function displaySelectReservesToReactivate($ci, $user, $instructor_list, $hidden_fields=null)
	{
		global $g_permission;		
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

		echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr><td width=\"140%\" colspan=\"2\"><img src=\../images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";
		echo "	<tr>\n";
		echo "		<td width=\"75%\" align=\"left\" valign=\"top\">\n";
		echo "			<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
		echo "				<tr>\n";
		echo "					<td colspan=\"3\" align=\"left\" valign=\"top\" class=\"helperText\">\n";
		echo "						You have chosen the following class to reactivate for <span class=\"strong\">FALL 2004</span>.\n";
		echo "						Choose what options and readings you would like to retain for the new class:\n";
		echo "					</td>\n";
		echo "				</tr>\n";
		
		echo "				<tr><td width=\"50%\" align=\"left\" valign=\"top\">&nbsp;</td><td width=\"15\" align=\"left\" valign=\"top\">&nbsp;</td><td width=\"50%\" align=\"left\" valign=\"top\">&nbsp;</td></tr>\n";

		echo "				<tr><td colspan=\"3\" align=\"left\" valign=\"top\" class=\"courseTitle\">" . $ci->course->displayCourseNo() . "-" . $ci->course->getName() ."</td></tr>\n";
		echo "				<tr align=\"left\" valign=\"top\">\n";
		echo "					<td width=\"50%\"><span class=\"courseHeaders\">Last Active:</span>". $ci->displayTerm() ."</td>\n";
		echo "					<td width=\"15\">&nbsp;</td><td width=\"50%\">&nbsp;</td>\n";
		echo "				</tr>\n";
		
		echo "				<tr align=\"left\" valign=\"top\"><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";

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
		
		if (is_array($ci->instructorList) && !empty($ci->instructorList))
		{
			foreach($ci->instructorList as $ciInstructor)
			{
				echo "							<tr align=\"left\" valign=\"middle\">\n";
				echo "								<td bgcolor=\"#CCCCCC\">". $ciInstructor->getName() ."</td>\n";
				echo "								<td width=\"8%\" valign=\"top\" bgcolor=\"#CCCCCC\" class=\"borders\" align=\"center\">\n";
				echo "									<input type=\"checkbox\" name=\"carryInstructor[]\" value=\"". $ciInstructor->getUserID() ."\" checked>\n";
				echo "								</td>\n";
				echo "							</tr>\n";
			}
		}		

		if ($user->getDefaultRole() >= $g_permission['staff'])
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

		echo "					<td width=\"15\"><img src=\../images/spacer.gif\" width=\"15\" height=\"1\"></td>\n";
		echo "					<td width=\"50%\">\n";
		echo "						<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" class=\"borders\">\n";
		echo "							<tr align=\"left\" valign=\"middle\">\n";
		echo "								<td bgcolor=\"#FFFFFF\" class=\"headingCell1\">&nbsp;</td>\n";
		echo "								<td class=\"headingCell1\">Select</td>\n";
		echo "							</tr>\n";

		if (is_array($ci->crossListings) && !empty($ci->crossListings))
		{
			echo "							<tr align=\"left\" valign=\"middle\">\n";
			echo "								<td bgcolor=\"#CCCCCC\">". $ci->course->displayCourseNo() ." -- " . $ci->course->getName() ."</td>\n";
			echo "								<td width=\"8%\" valign=\"top\" bgcolor=\"#CCCCCC\" class=\"borders\" align=\"center\">\n";
			echo "									<input type=\"checkbox\" name=\"carryCrossListing\" value=\"". $ci->course->getCourseID() ."::". $ci->course->getSection() ."\" checked>\n";
			echo "								</td>\n";
			echo "							</tr>\n";
		} else 
			echo "							<tr><td bgcolor=\"#CCCCCC\" colspan=\"2\">None</td></tr>\n";
			
		echo "							<tr align=\"left\" valign=\"middle\"><td colspan=\"2\"  class=\"headingCell1\">&nbsp;</td></tr>\n";
		echo "						</table>\n";
		echo "					</td>\n";
		echo "				</tr>\n";
		
		echo "				<tr><td>&nbsp;</td></tr>\n";
		echo "			</table>\n";
		
		
		echo "	<tr>\n";
		echo "		<td colspan=\"2\">\n";
		echo "			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		echo "				<tr align=\"left\" valign=\"top\">\n";
		echo "					<td class=\"headingCell1\"><div align=\"center\">COURSE MATERIALS</div></td>\n";
		echo "					<td width=\"75%\" align=\"right\"><!--[ <a href=\"link\" class=\"editlinks\">Hide/Unhide Readings</a> ]--></td>\n";
		echo "				</tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td colspan=\"2\" align=\"left\" valign=\"top\" class=\"borders\">\n";
		echo "			<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" class=\"displayList\">\n";
		echo "				<tr align=\"left\" valign=\"middle\">\n";
		echo "					<td valign=\"top\" bgcolor=\"#FFFFFF\" class=\"headingCell1\">&nbsp;</td>\n";
		echo "					<td bgcolor=\"#FFFFFF\" class=\"headingCell1\">".count($ci->reserveList)." Item(s) On Reserve</td>\n";
		echo "					<td bgcolor=\"#FFFFFF\" class=\"headingCell1\">Select</td>\n";
		echo "      		</tr>\n";

		//Loop through Records Here
		$rowNumber = 0;
		for($i=0;$i<count($ci->reserveList);$i++)
		{
			$ci->reserveList[$i]->getItem();
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
			
			if ($ci->reserveList[$i]->item->isHeading())
			{
				//echo "headings";
			} else {	
	
				$rowClass = ($rowNumber++ % 2) ? "evenRow" : "oddRow";
				
				$reserveItem = new reserveItem($ci->reserveList[$i]->getItemID());
				$itemIcon = $reserveItem->getItemIcon();
				$itemGroup = $reserveItem->itemGroup;
									
				if ($reserveItem->isPhysicalItem()) {
					//move to config file
					$viewReserveURL = "http://libcat1.cc.emory.edu/uhtbin/cgisirsi/x/0/5?searchdata1=" . $ci->reserveList[$i]->item->getLocalControlKey();
				} else {
					$viewReserveURL = "reservesViewer.php?viewer=" . $user->getUserID() . "&reserve=" . $ci->reserveList[$i]->getReserveID();// . "&location=" . $ci->reserveList[$i]->item->getURL();
				}
				
				echo '		<tr align="left" valign="middle" class="'.$rowClass.'">'
	            .    '			<td width="4%" valign="top"><img src="'.$itemIcon.'" alt="text" width="24" height="20"></td>'
	            .    '			<td width="96%">';
	            if (!$reserveItem->isPhysicalItem()) {
	            	echo '<a href="'.$viewReserveURL.'" target="_blank" class="itemTitle">'.$title.'</a><br>';
	            	echo '<span class="itemAuthor">'.$author.'</span><br>';
	            } else {
	            	echo '<span class="itemTitleNoLink">'.$title.'</span><br>'; 
	            	echo '<span class="itemAuthor">'.$author.'</span><br>';
	            }
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
	            	echo '</td>';
	            	echo "<td align=\"right\"><input type=\"checkbox\" name=\"carryReserve[]\" value=\"".$ci->reserveList[$i]->getReserveID()."\" checked></td></tr>";
			}
		}
		//End Loop through Records
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
			
		echo "	<tr><td colspan=\"2\"><img src=\../images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
		echo "	<tr><td colspan=\"2\" align=\"center\" class=\"strong\"><input type=\"submit\" name=\"Submit\" value=\"Reactivate Class\"></td></tr>\n";
		echo "	<tr><td colspan=\"2\"><img src=\../images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
		echo "</table>\n";

		echo "</form>\n";
	}
	

	function displaySuccess($page, $ci)
	{
			
		echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n"
		.	 "	<tbody>\n"
		.	 "		<tr><td width=\"140%\"><img src=\../images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n"
		.	 "		<tr>\n"
	    .	 "			<td align=\"left\" valign=\"top\" class=\"borders\">\n"
	    .	 "				<table width=\"50%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"5\">\n"
		.	 "					<tr><td><strong>You have successfully added a class.  You May now:</strong></td></tr>\n"
		.	 "					<tr><td>\n"
		.	 "						<ul><li><a href=\"index.php?cmd=editClass&ci=". $ci->getCourseInstanceID() ."\">Edit Reserves for this class.</a></li>\n"
		.	 "							<li><a href=\"index.php?cmd=reactivateClass\">Reactivate another class.</a></li>\n"
		.	 "							<li><a href=\"index.php?cmd=createClass\">Create a New Class.</a></li>\n"
		.	 "						</ul>\n"
		.	 "					</td></tr>\n"
		.	 "				</table>\n"
		.	 "			</td>\n"
		.	 "		</tr>\n"
		.	 "		<tr><td><img src=\../images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n"
		.	 "	</tbody>\n"
		.	 "</table>\n"
		;
	}
	
	function displayCreateClass($instructors, $departments, $terms, $hidden_fields=null)
	{
		global $u, $g_permission;
	
		echo "\n<script language=\"JavaScript\">\n";
		echo "	function activateDates(frm, activateDate, expirationDate)\n";
		echo "	{\n";
		echo "		frm.activation_date.value = activateDate;\n";
		echo "		frm.expiration_date.value = expirationDate;\n";
		echo "	}\n";
		echo "</script>\n";
		
	    echo "<form action=\"index.php\" method=\"post\" name=\"frmClass\">\n";
	
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
	
		echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr><td width=\"140%\"><img src=\../images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>\n";
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
		echo "								<option>-- Select a Department --</option>\n";
		foreach ($departments as $dept) { echo "								<option value=\"". $dept[0] ."\">". $dept[1] ." " . $dept[2] ."</option>\n"; }
		echo "							</select>\n";
		echo "						</td>\n";
		echo "				</tr>\n";
	
		echo "				<tr valign=\"middle\">\n";
		echo "					<td width=\"35%\" height=\"31\" align=\"right\" bgcolor=\"#CCCCCC\" align=\"right\" class=\"strong\">Course Number</td>\n";
		echo "					<td align=\"left\"><input name=\"course_number\" type=\"text\" size=\"5\"></td>\n";
		echo "				</tr>\n";
	
		echo "				<tr valign=\"middle\">\n";
		echo "					<td align=\"right\" bgcolor=\"#CCCCCC\" align=\"right\" class=\"strong\">Section:</td>\n";
		echo "					<td align=\"left\"><input name=\"section\" type=\"text\" size=\"4\"></td>\n";
		echo "				</tr>\n";
		
		echo "				<tr valign=\"middle\">\n";
		echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\" align=\"right\" class=\"strong\">Course Name:</td>\n";
		echo "					<td align=\"left\"><input name=\"course_name\" type=\"text\" size=\"50\"></td>\n";
		echo "				</tr>\n";
		
		echo "				<tr valign=\"middle\">\n";
		echo "					<td align=\"right\" bgcolor=\"#CCCCCC\" class=\"strong\">Semester:</td>\n";
		echo "					<td><table><tr>\n";
	
		foreach($terms as $t)
		{
			echo "								<td>\n";
			echo "									<input type=\"radio\" name=\"term\" value=\"". $t->getTermID() ."\" onClick=\"activateDates(this.form, '". $t->getBeginDate() ."','". $t->getEndDate() ."');\">". $t->getTerm() ."\n";
			echo "								</td>\n";
		}	
		echo "					</tr></table></td>";
		echo "				</tr>\n";	
	
		if ($u->getDefaultRole() >= $g_permission['staff'])
		{
			echo "				<tr valign=\"middle\">\n";
			echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\" align=\"right\" class=\"strong\">Instructor:</td>\n";
			echo "					<td align=\"left\">\n";
			echo "						<select name=\"instructor\">\n";
			echo "							<option selected>-- Select an Instructor --</option>\n";
			foreach ($instructors as $inst) { echo "								<option value=\"". $inst['user_id'] ."\">". $inst['full_name'] ."</option>\n"; }
			echo "						</select>\n";
			echo "					</td>\n";
			echo "				</tr>\n";
			
			echo "				<tr valign=\"middle\">\n";
			echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\" align=\"right\" class=\"strong\">Activation Date: (yyyy-mm-dd)</td>\n";
			echo "					<td align=\"left\"><input type=\"text\" name=\"activation_date\" value=\"". $terms[0]->getBeginDate() ."\"></td>\n";
			echo "				</tr>\n";
					
			echo "				<tr valign=\"middle\">\n";
			echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\" align=\"right\" class=\"strong\">Expiration Date: (yyyy-mm-dd)</td>\n";		
			echo "					<td align=\"left\"><input type=\"text\" name=\"expiration_date\" value=\"". $terms[0]->getEndDate() ."\"></td>\n";	
			echo "				</tr>\n";
		} else { 
			echo "			<input type=\"hidden\" name=\"instructor\" value=\"". $u->getUserID() . "\">\n";
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
		echo "	<tr><td align=\"center\"><input type=\"submit\" name=\"Submit\" value=\"Create Course\"></td></tr>\n";
		echo "	<tr><td><img src=\../images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
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
		echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tbody>\n";
		echo "		<tr><td width=\"140%\"><img src=\../images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";
		
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
		
		echo "		<tr><td align=\"left\" valign=\"top\"><img src=\../images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";		
		echo "	</tbody>\n";
		echo "</table>\n";
	}	

	function displaySelectInstructor($user)
	{
		$user->selectUserForAdmin('instructor', 'selectClass');
	}
	
	function displaySearchForClass($instructorList, $deptList)
	{
		echo '<table width="90%" border="0" cellspacing="0" cellpadding="0" align="center">';
		echo '	<tr>';
		echo '    	<td width="140%"><img src=../images/spacer.gif" width="1" height="5"></td>';
		echo '	</tr>';
		echo '    <tr>';
		echo '    	<td align="left" valign="top">';
		echo '    	<table width="75%" border="0" align="center" cellpadding="0" cellspacing="0">';
		echo '        	<tr align="left" valign="top" class="headingCell1">';
		echo '            	<td width="50%">Search by Instructor</td>';
		echo '                <td width="50%">Search by Department</td>';
		echo '            </tr>';
		echo '			<tr>';
		echo '        		<td width="50%" class="borders"><div align="center"><br>';
		echo '<FORM METHOD=POST ACTION="index.php">';
		echo '<INPUT TYPE="HIDDEN" NAME="cmd" VALUE="addClass">';
		echo '                	    <select name="prof">';
		echo '                    	  <option value="" selected>Choose an Instructor ';
		foreach($instructorList as $instructor)
		{
			echo '<option value="' . $instructor['username'] . '">' . $instructor['full_name'] . '</option>';
		}

		echo '                    	</select>';
		echo '                    	<br>';
		echo '                    	<br>';
		echo '                    	<input type="submit" name="Submit2" value="Lookup Classes">';
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
        echo '			</tr>';
        echo '		</table>';
        echo '		</td>';
        echo '	</tr>';
        echo '    <tr>';
        echo '    	<td><img src=../images/spacer.gif" width="1" height="15"></td>';
        echo '	</tr>';
        echo '</form>';
        echo '</table>';
	}

	function displayAddClass($courseList, $searchParam) 
	{
		echo '<table width="90%" border="0" cellspacing="0" cellpadding="0" align="center">';
		echo '	<tr> ';
		echo '    	<td width="140%" colspan="2"><img src=../images/spacer.gif" width="1" height="5"></td>';
		echo '	</tr>';
		echo '    <tr> ';
		echo '    	<td width="50%" c><span class="strong">';
		if (is_a($searchParam,"instructor")) {
			echo 'Instructor: '.$searchParam->getName();
		} elseif (is_a($searchParam,"department")) {
			echo 'Department: '.$searchParam->getAbbr();
		}
		echo '</span></td>';
		echo '		<td width="50%" c><div align="right" class="strong">FALL 2004</div></td>';
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
			echo '                <td width="50%">'.$courseList[$i]->getName().'</td>';
			echo '                <td width="30%"><div align="center"><a href="index.php?cmd=addStudent&aID='.$courseList[$i]->getCourseAliasID().'">click here to add</a></div></td>';
			echo '			</tr>';
		}
		echo '		</table>';
		echo '		</td>';
		echo '	</tr>';
		echo '    <tr> ';
		echo '    	<td colspan="2"><img src=../images/spacer.gif" width="1" height="15"></td>';
		echo '	</tr>';
		echo '</table>';
	}

	function displayRemoveClass()
	{
		global $u;
	
		echo '<table width="90%" border="0" cellspacing="0" cellpadding="0" align="center">';
		echo('<FORM METHOD=POST ACTION="index.php">');
		echo('<INPUT TYPE="HIDDEN" NAME="cmd" VALUE="removeStudent">');
		echo '	<tr> ';
		echo '		<td width="190%"><img src=../images/spacer.gif" width="1" height="5"></td>';
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
		echo '    <tr>';
		echo '    	<td>&nbsp;</td>';
		echo '	</tr>';
		echo '    <tr> ';
		echo '    	<td><div align="center"><img src=../images/spacer.gif" width="1" height="15"><input type="submit" name="deleteAlias" value="Remove Selected Classes"></div></td>';
		echo '	</tr>';
		echo '</form>';
		echo '</table>';
	}	
}
?>