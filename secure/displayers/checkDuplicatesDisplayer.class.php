<?
/*******************************************************************************
ReservesDirect

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

ReservesDirect is located at:
http://coursecontrol.sourceforge.net/

*******************************************************************************/
require_once("secure/common.inc.php");

class checkDuplicatesDisplayer 
{
	/**
	 * @return void
	 * @desc Displays Error Message for Duplicate Course Instance Creation
	*/
	function displayDuplicateError ($user, $type, $duplicateClasses) {
		
		global $g_permission;
		$msg="";
		
		switch ($type)
		{
			case 'courseInstance':
				$msg = "The class you are attempting to create is a duplicate of one of the following classes listed below.";
				if ($user->dfltRole < $g_permission['staff'])
					$msg = $msg."<br><br>If you have previously taught this class, you may reactivate it.  If you are currently teaching this class, please access the MyReserves tab to go to the class.  If you need further assistance, please contact your Reserves staff.";
			break;
			
			case 'reactivation':
				$msg = "The class you are attempting to reactivate is already active for the specified term and year.";
				if ($user->dfltRole < $g_permission['staff'])
					$msg = $msg."  Please see your Reserves staff for further assistance.";
			break;
		}
		
		echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo '<tr><td width="100%"><img src="images/spacer.gif" width="1" height="5"></td></tr>';

		
		echo '<tr><td class="failedText">'.$msg.'</td></tr>';
		echo '<tr><td>&nbsp;</td></tr>';
		
		echo "	<tr>\n";
		echo "		<td align=\"left\" valign=\"top\" class=\"borders\">\n";
		echo "			<table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"5\" cellspacing=\"0\" class=\"displayList\">\n";
		echo "				<tr align=\"left\" valign=\"middle\" bgcolor=\"#CCCCCC\" class=\"headingCell1\">\n";
		echo "					<td width=\"15%\">Course Number</td>\n";
		echo "					<td>Course Name</td><td>Taught By</td><td>Last Active</td><td width=\"20%\">Reserve List</td>\n";
		echo "				</tr>\n";

		for($i=0; $i<count($duplicateClasses); $i++)
		{
			$rowClass = ($i % 2) ? "evenRow" : "oddRow";
			
			echo "				<tr align=\"left\" valign=\"middle\" class=\"$rowClass\">\n";
			if ($user->dfltRole >= $g_permission['staff']) {
				echo "				<td width=\"15%\"><a href=\"index.php?cmd=editClass&ci=".$duplicateClasses[$i]->getCourseInstanceID()."\">".$duplicateClasses[$i]->course->displayCourseNo()."</a></td>\n";
				echo "				<td><a href=\"index.php?cmd=editClass&ci=".$duplicateClasses[$i]->getCourseInstanceID()."\">".$duplicateClasses[$i]->course->getName()."</a></td>\n";
			} else {
				echo "					<td width=\"15%\">".$duplicateClasses[$i]->course->displayCourseNo()."</td>\n";
				echo "					<td>".$duplicateClasses[$i]->course->getName()."</td>\n";
			}
			echo "					<td>".$duplicateClasses[$i]->displayInstructorList()."</td>\n";
			echo "					<td width=\"20%\" align=\"center\">".$duplicateClasses[$i]->displayTerm()."</td>\n";
			echo "					<td width=\"20%\" align=\"center\"><a href=\"javascript:openWindow('no_control&cmd=previewReservesList&ci=".$duplicateClasses[$i]->courseInstanceID . "','width=800,height=600');\">preview</a></td>\n";
			echo "				</tr>\n";
		}

		echo "				<tr align=\"left\" valign=\"middle\" bgcolor=\"#CCCCCC\" class=\"headingCell1\"><td colspan=\"6\">&nbsp;</td></tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		
        echo '<tr><td align="left" valign="top">&nbsp;</td></tr>';
		echo "</table>\n";		
	}
	
}

?>