<?
/*******************************************************************************
copyClassDisplayer.class.php


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
require_once("secure/classes/terms.class.php");
require_once("secure/managers/lookupManager.class.php");

class copyClassDisplayer {

	function displayCopyClass ($cmd, $u, $request) {
		echo "<form action=\"index.php\" method=\"POST\">\n";
		echo "<input type=\"hidden\" name=\"cmd\" value=\"$cmd\">\n";

		$tableHeading="SOURCE CLASS (Copy FROM)";
		$selectClassMgr = new lookupManager($tableHeading, 'lookupClass', $u, $request);
		$selectClassMgr->display();
		if (isset($_REQUEST['ci']) && $_REQUEST['ci'] && $_REQUEST['ci'] != null)
		{
			$ci= new courseInstance($_REQUEST['ci']);
			$ci->getPrimaryCourse();
			echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
			echo '<tr>';
          	echo '<td><div align="center" class="strong"> ';
            echo '<!--As with the same form used for "Reactivate Class", the "Copy This Class To" drop down menu and the "Continue" button should not be active until a course instance is selected from the above list. -->';
            echo 'Copy '.$ci->course->displayCourseNo().' ('.$ci->displayTerm().') to: ';
            echo '<select name="copyAction">';
            echo '	<option selected value="copyExisting">an existing class</option>';
            echo '<option value="copyNew">a new class</option>';
            echo '</select>';
            echo '</div></td>';
        	echo '</tr>';
        	echo '<tr><td>&nbsp;</td></tr>';
			echo "	<tr><td valign=\"top\" align=\"center\"><input type=\"submit\" name=\"performAction\" value=\"Continue\" onClick=\"this.form.cmd.value='clearCopyClassLookup';\"></td></tr>\n";
		}
		else {
			echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
			echo "	<tr><td valign=\"top\" align=\"center\"><input type=\"submit\" name=\"performAction\" value=\"Continue\" DISABLED></td></tr>\n";
		}
		echo "	<tr><td align=\"left\" valign=\"top\"><img src=\"images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
		echo "</table>\n";
		echo "</form>\n";

	}


	function displayCopyExisting ($cmd, $u, $sourceClass, $request) {

		echo "<form action=\"index.php\" method=\"POST\">\n";
		echo "<input type=\"hidden\" name=\"cmd\" value=\"$cmd\">\n";


		echo "<input type=\"hidden\" name=\"sourceClass\" value=\"".$sourceClass->getCourseInstanceID()."\">\n";

		echo'	<table width="90%" border="0" cellspacing="0" cellpadding="0" align="center">';

		echo'		<tr>';
		echo'			<td width="140%"><img src="images/spacer.gif" width="1" height="5"> </td>';
		echo'		</tr>';

		echo'		<tr>';
    	echo'			<td>';
    	echo'			<table width="100%" border="0" cellspacing="0" cellpadding="0">';
    	echo'    			<tr align="left" valign="top">';
    	echo'        			<td width="35%" class="headingCell1">SOURCE CLASS (Copy FROM)</td>';
		echo'					<td>&nbsp; </td>';
		echo'				</tr>';
		echo'					</table>';
		echo'			</td>';
		echo'		</tr>';

		echo'	    <tr>';
    	echo'			<td align="left" valign="top" class="borders">';
    	echo'			<table width="100%" border="0" cellspacing="0" cellpadding="3">';
		echo'	        	<tr>';
    	echo'    		    	<td colspan="2"><span class="strong">'.$sourceClass->course->displayCourseNo().' - '.$sourceClass->course->getName().' ('.$sourceClass->displayTerm().')</span> -- taught by ';

    	for($i=0;$i<count($sourceClass->instructorList);$i++) {
			if ($i>0)
				echo ',&nbsp;';
			echo '<a href="mailto:'.$sourceClass->instructorList[$i]->getEmail().'">'.$sourceClass->instructorList[$i]->getFirstName().'&nbsp;'.$sourceClass->instructorList[$i]->getLastName().'</a>';
		}
    	echo'</td>';
    	echo'        		</tr>';
		echo'	            <tr align="left" valign="top" bgcolor="#CCCCCC">';

		if (isset($request['copyReserves']))
			$reserves_checked = 'checked';
		else
			$reserves_checked = '';

		if (isset($request['copyInstructors']))
			$instructors_checked = 'checked';
		else
			$instructors_checked = '';

		if (isset($request['copyCrossListings']))
			$crossListings_checked = 'checked';
		else
			$crossListings_checked = '';

		if (isset($request['copyProxies']))
			 $proxies_checked = 'checked';
		else
			$proxies_checked = '';

		if (isset($request['copyEnrollment']))
			$enrollment_checked = 'checked';
		else
			$enrollment_checked = '';

		if (isset($request['deleteSource']))
			$deleteSource_checked = 'checked';
		else
			$deleteSource_checked = '';

		echo'    		    	<td width="50%"><input name="copyReserves" type="checkbox" value="checkbox" '.$reserves_checked.'>&nbsp;Copy Reserve Materials</td>';
    	echo'          			<td width="50%"><input type="checkbox" name="copyInstructors" value="checkbox" '.$instructors_checked.'>&nbsp;Copy Instructors</td>';
    	echo'        		</tr>';
		echo'	            <tr align="left" valign="top" bgcolor="#CCCCCC">';
    	echo'    		    	<td width="50%"><input type="checkbox" name="copyCrossListings" value="checkbox" '.$crossListings_checked.'>&nbsp;Copy Crosslistings</td>';
    	echo'          			<td width="50%"><input type="checkbox" name="copyProxies" value="checkbox" '.$proxies_checked.'>&nbsp;Copy Proxies</td>';
		echo'	            </tr>';
    	echo'    		    <tr align="left" valign="top" bgcolor="#CCCCCC">';
    	echo'        			<td><input type="checkbox" name="copyEnrollment" value="checkbox" '.$enrollment_checked.'>&nbsp;Copy Enrollment List</td>';
    	echo'          			<td>&nbsp;</td>';
		echo'	            </tr>';
    	echo'		        <tr align="left" valign="top" bgcolor="#CCCCCC">';
    	echo'        		  	<td colspan="2"><input type="checkbox" name="deleteSource" value="checkbox" '.$deleteSource_checked.'>&nbsp;DELETE Source Class (Merge Classes) ';
    	echo'          				<font color="#CC0000"><strong>CAUTION! Deleting the Source Class cannot be undone!</strong></font></td>';
		echo'				</tr>';
		echo'			</table>';
		echo'			</td>';
		echo'		</tr>';

		echo'	    <tr class="borders">';
		echo'	    	<td align="right" valign="top">&nbsp;</td>';
		echo'		</tr>';

		echo'	</table>';

		$tableHeading="TARGET CLASS (Copy TO)";
		$selectClassMgr = new lookupManager($tableHeading, 'lookupClass', $u, $request);
		$selectClassMgr->display();

		echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		if (isset($_REQUEST['ci']) && $_REQUEST['ci'] && $_REQUEST['ci'] != null)
		{

			echo "	<tr><td valign=\"top\" align=\"center\"><input type=\"submit\" name=\"performAction\" value=\"Copy\" onClick=\"this.form.cmd.value='processCopyClass'\"></td></tr>\n";
		} else {
			echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
			echo "	<tr><td valign=\"top\" align=\"center\"><input type=\"submit\" name=\"performAction\" value=\"Copy\" DISABLED></td></tr>\n";
		}
		echo "	<tr><td align=\"left\" valign=\"top\"><img src=\"images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
		echo "</table>\n";
		echo "</form>\n";

	}



	function displayCopyNew ($cmd, $u, $sourceClass, $termsArray, $departments, $request) {

		echo "\n<script language=\"JavaScript\">\n";
		echo "	function activateDates(frm, activateDate, expirationDate)\n";
		echo "	{\n";
		echo "		frm.activation_date.value = activateDate;\n";
		echo "		frm.expiration_date.value = expirationDate;\n";
		echo "	}\n";
		echo "</script>\n";

		echo "<form action=\"index.php\" method=\"POST\">\n";
		echo "<input type=\"hidden\" name=\"cmd\" value=\"$cmd\">\n";
		echo "<input type=\"hidden\" name=\"enrollment\" value=\"public\">\n";

		echo "<input type=\"hidden\" name=\"sourceClass\" value=\"".$sourceClass->getCourseInstanceID()."\">\n";

		echo'	<table width="90%" border="0" cellspacing="0" cellpadding="0" align="center">';

		echo'		<tr>';
		echo'			<td width="140%"><img src="images/spacer.gif" width="1" height="5"> </td>';
		echo'		</tr>';

		echo'		<tr>';
    	echo'			<td>';
    	echo'			<table width="100%" border="0" cellspacing="0" cellpadding="0">';
    	echo'    			<tr align="left" valign="top">';
    	echo'        			<td width="35%" class="headingCell1">SOURCE CLASS (Copy FROM)</td>';
		echo'					<td>&nbsp; </td>';
		echo'				</tr>';
		echo'			</table>';
		echo'			</td>';
		echo'		</tr>';

		echo'	    <tr>';
    	echo'			<td align="left" valign="top" class="borders">';
    	echo'			<table width="100%" border="0" cellspacing="0" cellpadding="3">';
		echo'	        	<tr>';
    	echo'    		    	<td colspan="2"><span class="strong">'.$sourceClass->course->displayCourseNo().' - '.$sourceClass->course->getName().' ('.$sourceClass->displayTerm().')</span> -- taught by ';

    	for($i=0;$i<count($sourceClass->instructorList);$i++) {
			if ($i>0)
				echo ',&nbsp;';
			echo '<a href="mailto:'.$sourceClass->instructorList[$i]->getEmail().'">'.$sourceClass->instructorList[$i]->getFirstName().'&nbsp;'.$sourceClass->instructorList[$i]->getLastName().'</a>';
		}
    	echo'</td>';
    	echo'        		</tr>';
		echo'	            <tr align="left" valign="top" bgcolor="#CCCCCC">';

		if (isset($request['copyReserves']))
			$reserves_checked = 'checked';
		else
			$reserves_checked = '';

		if (isset($request['copyInstructors']))
			$instructors_checked = 'checked';
		else
			$instructors_checked = '';

		if (isset($request['copyCrossListings']))
			$crossListings_checked = 'checked';
		else
			$crossListings_checked = '';

		if (isset($request['copyProxies']))
			 $proxies_checked = 'checked';
		else
			$proxies_checked = '';

		if (isset($request['copyEnrollment']))
			$enrollment_checked = 'checked';
		else
			$enrollment_checked = '';

		if (isset($request['deleteSource']))
			$deleteSource_checked = 'checked';
		else
			$deleteSource_checked = '';

		if (!isset($request['term'])) {
			$request['term'] = $termsArray[0]->getTermID();
			$request['activation_date'] = $termsArray[0]->getBeginDate();
			$request['expiration_date'] = $termsArray[0]->getEndDate();
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

		echo'    		    	<td width="50%"><input name="copyReserves" type="checkbox" value="checkbox" '.$reserves_checked.'>&nbsp;Copy Reserve Materials</td>';
    	echo'          			<td width="50%"><input type="checkbox" name="copyInstructors" value="checkbox" '.$instructors_checked.'>&nbsp;Copy Instructors</td>';
    	echo'        		</tr>';
		echo'	            <tr align="left" valign="top" bgcolor="#CCCCCC">';
    	echo'    		    	<td width="50%"><input type="checkbox" name="copyCrossListings" value="checkbox" '.$crossListings_checked.'>&nbsp;Copy Crosslistings</td>';
    	echo'          			<td width="50%"><input type="checkbox" name="copyProxies" value="checkbox" '.$proxies_checked.'>&nbsp;Copy Proxies</td>';
		echo'	            </tr>';
    	echo'    		    <tr align="left" valign="top" bgcolor="#CCCCCC">';
    	echo'        			<td><input type="checkbox" name="copyEnrollment" value="checkbox" '.$enrollment_checked.'>&nbsp;Copy Enrollment List</td>';
    	echo'          			<td>&nbsp;</td>';
		echo'	            </tr>';
    	echo'		        <tr align="left" valign="top" bgcolor="#CCCCCC">';
    	echo'        		  	<td colspan="2"><input type="checkbox" name="deleteSource" value="checkbox" '.$deleteSource_checked.'>&nbsp;DELETE Source Class (Merge Classes) ';
    	echo'          				<font color="#CC0000"><strong>CAUTION! Deleting the Source Class cannot be undone!</strong></font></td>';
		echo'				</tr>';
		echo'			</table>';
		echo'			</td>';
		echo'		</tr>';

		echo'	    <tr class="borders">';
		echo'	    	<td align="right" valign="top">&nbsp;</td>';
		echo'		</tr>';

		echo'	</table>';

		echo'	<table width="90%" border="0" cellspacing="0" cellpadding="0" align="center">';
		echo'		<tr>';
        echo'			<td>';
        echo '			<table width="100%" border="0" cellspacing="0" cellpadding="0">';
        echo '      		<tr align="left" valign="top">';
        echo '        			<td width="35%" class="headingCell1">TARGET CLASS (Copy TO)</td>';
        echo '        			<!--The "Show All Editable Item" Links appears by default when this';
		echo '						page is loaded if some of the metadata fields for the document are blank.';
		echo '						Blank fields will be hidden upon page load. -->';
        echo '        			<td>&nbsp; </td>';
        echo '      		</tr>';
        echo '    		</table>';
        echo '  		</td>';
        echo '		</tr>';
        echo '		<tr>';
        echo '  		<td align="left" valign="top" class="borders">';
        echo '			<table width="100%" border="0" cellspacing="0" cellpadding="3">';
        echo '    			<tr align="left" valign="middle">';
        echo '      			<td height="22" colspan="2" bgcolor="#CCCCCC" class="borders">';
        echo '					<table width="100%" border="0" cellspacing="0" cellpadding="0">';
        echo '          			<tr align="left" valign="middle">';
        echo '            				<td class="strong">Semester:</td>';

        foreach($termsArray as $t)
		{
			($t->getTermID() == $request['term']) ? $term_checked = "checked" : $term_checked = "";
			echo "							<td><input type=\"radio\" name=\"term\" $term_checked value=\"". $t->getTermID() ."\" onClick=\"activateDates(this.form, '". $t->getBeginDate() ."','". $t->getEndDate() ."');\">". $t->getTerm() ."</td>\n";

		}

        echo '          			</tr>';
        echo '        			</table>';
        echo '      			</td>';
        echo '    			</tr>';
        echo '    			<tr valign="middle">';
        echo '      			<td width="35%" height="30" align="right" bgcolor="#CCCCCC"><div align="right" class="strong">Department:</div></td>';
        echo '      			<td align="left">';
        echo '						<select name="department">';
        echo '          				<option>-- Select a Department --</option>';

        foreach ($departments as $dept) {
        	($dept[0] == $request['department']) ? $dept_selected = "selected" : $dept_selected = "";
        	echo "<option $dept_selected value=\"". $dept[0] ."\">". $dept[1] ." " . $dept[2] ."</option>\n";
        }

        echo '        				</select>';
        echo '      			</td>';
        echo '    			</tr>';
        echo '    			<tr valign="middle">';
        echo '      			<td width="35%" height="31" align="right" bgcolor="#CCCCCC"><div align="right" class="strong">Course Number</div></td>';
        echo '      			<td align="left"><input name="course_number" type="text" id="Title2" size="5" value="'.$course_number.'"></td>';
        echo '    			</tr>';
        echo '    			<tr valign="middle">';
        echo '      			<td align="right" bgcolor="#CCCCCC"><div align="right" class="strong">Section:</div></td>';
        echo '      			<td align="left"><input name="section" type="text" size="4" value="'.$section.'"></td>';
        echo '    			</tr>';
        echo '    			<tr valign="middle">';
        echo '      			<td width="35%" align="right" bgcolor="#CCCCCC"><div align="right"><span class="strong">Course Name</span><span class="strong">:</span></div></td>';
        echo '      			<td align="left"><input name="course_name" type="text" id="Title3" size="50" value="'.$course_name.'"></td>';
        echo '    			</tr>';
        echo '    			<tr valign="middle">';
        echo '      			<td width="35%" align="right" bgcolor="#CCCCCC"><div align="right"><span class="strong">Instructor</span><span class="strong">:</span></div></td>';

        $selectClassMgr = new lookupManager('','lookupInstructor', $u, $request);
		$selectClassMgr->display();

        echo '    			</tr>';

        echo "				<tr valign=\"middle\">\n";
		echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\" align=\"right\" class=\"strong\">Activation Date: (yyyy-mm-dd)</td>\n";
		echo "					<td align=\"left\"><input type=\"text\" name=\"activation_date\" value=\"". $request['activation_date'] ."\"></td>\n";
		echo "				</tr>\n";

		echo "				<tr valign=\"middle\">\n";
		echo "					<td width=\"35%\" align=\"right\" bgcolor=\"#CCCCCC\" align=\"right\" class=\"strong\">Expiration Date: (yyyy-mm-dd)</td>\n";
		echo "					<td align=\"left\"><input type=\"text\" name=\"expiration_date\" value=\"". $request['expiration_date'] ."\"></td>\n";
		echo "				</tr>\n";

		echo '  		</table>';
        echo '			</td>';
        echo '		</tr>';
        echo '		<tr><td align="left" valign="top">&nbsp;</td></tr>';
        echo '	</table>';

		echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr><td valign=\"top\" align=\"center\"><input type=\"submit\" name=\"copyNew\" value=\"Copy\" onClick=\"this.form.cmd.value='processCopyClass'\"></td></tr>\n";
		echo "	<tr><td align=\"left\" valign=\"top\"><img src=\"images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
		echo "</table>\n";
		echo "</form>\n";
	}

	function displayCopySuccess ($sourceClass, $targetClass, $copyStatus) {

		echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo '<tr><td width="100%"><img src="images/spacer.gif" width="1" height="5"></td></tr>';

		echo '<tr>';
		echo 	'<td align="left" valign="top">';
        echo 	  '<p>';
        echo 		'<span class="strong">';
        echo 			$sourceClass->course->displayCourseNo().' -- '.$sourceClass->course->getName().' ('.$sourceClass->displayTerm().')';
        echo 		'</span>';
        echo 		'<span class="helperText"> has been copied to </span>';
        echo 		'<span class="strong">';
        echo 			$targetClass->course->displayCourseNo().' -- '.$targetClass->course->getName().' ('.$targetClass->displayTerm().').';
        echo 		'</span>';
        echo 	  '</p>';
        echo 		'<ul>';

        for ($i=0; $i<count($copyStatus); $i++)
        {
        	echo 		'<li class="successText">'.$copyStatus[$i].'</li>';

        }

        echo 		'</ul>';
        echo 	  '<p>';
        echo 		'&gt;&gt;<a href="index.php?cmd=copyClass">Copy another class</a><br>';
        echo 		'&gt;&gt;<a href="index.php?cmd=manageClasses">Return to &quot;Manage Classes&quot; home</a><br>';
        echo 	  '</p>';
        echo 	'</td>';
        echo '</tr>';

        echo '<tr><td align="left" valign="top">&nbsp;</td></tr>';
		echo "</table>\n";

	}


}
?>