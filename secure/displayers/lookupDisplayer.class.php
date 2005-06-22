<?
/*******************************************************************************
lookupDisplayer.class.php


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

class lookupDisplayer
{
	//function classLookup($nextCmd, $performAction, $instr_list, $course_list, $ci_list, $request, $hidden_fields=null)
	function classLookup($tableHeading, $instr_list, $course_list, $ci_list, $request, $hidden_fields=null)
	{
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
		echo "	<tr><td width=\"140%\"><img src=\images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";
		echo "	<tr>\n";
		echo "		<td height=\"14\" align=\"left\" valign=\"top\">\n";
		echo "			<table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\">\n";
		echo "				<tr align=\"left\" valign=\"top\">\n";
		echo "					<td width=\"35%\" align=\"left\" class=\"headingCell1\" align=\"center\">$tableHeading</td><td width=\"75%\" align=\"center\"></td>\n";
		echo "				</tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td align=\"left\" valign=\"top\" class=\"borders\">\n";
		echo "			<table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"5\" cellspacing=\"0\">\n";
		echo "				<tr align=\"left\" valign=\"top\" bgcolor=\"#CCCCCC\">\n";
		echo "					<td width=\"27%\" class=\"strong\">1) Select an Instructor:</td>\n";
		echo "					<td colspan=\"4\">\n";

		//set selected
		$username = "";
		$last_name = "";
		$selector = (isset($request['select_instr_by'])) ? $request['select_instr_by'] : "last_name";
		$$selector = "selected";

		echo "						<select name=\"select_instr_by\">\n";
		echo "							<option value=\"last_name\" $last_name>Last Name</option>\n";
		echo "							<option value=\"username\" $username>User Name</option>\n";
		$instr_qryTerm = isset($request['instr_qryTerm']) ? $request['instr_qryTerm'] : "";
		echo "						</select> &nbsp; <input name=\"instr_qryTerm\" type=\"text\" value=\"".stripslashes($instr_qryTerm)."\" size=\"15\"  onBlur=\"this.form.submit();\">\n";
		echo "						&nbsp;\n";
		echo "						<input type=\"submit\" name=\"instr_search\" value=\"Search\" onClick=\"this.form.select_course.selectedIndex=-1; this.form.selected_instr.selectedIndex=-1;\">\n"; //by setting selectedIndex to -1 we can clear the selectbox or previous values
		echo "						&nbsp;\n";

		//set selected


		$inst_DISABLED = (is_null($instr_list)) ? "DISABLED" : "";

		echo "						<select name=\"selected_instr\" $inst_DISABLED onChange=\"this.form.select_course.selectedIndex=-1; this.form.submit();\">\n";
		echo "							<option value=\"null\">-- Choose an Instructor -- </option>\n";

		for($i=0;$i<count($instr_list);$i++)
		{
			$inst_selector = (isset($request['selected_instr']) && $request['selected_instr'] == $instr_list[$i]->getUserID()) ? "selected" : "";
			echo "							<option value=\"". $instr_list[$i]->getUserID() ."\" $inst_selector>". $instr_list[$i]->getName() ."</option>\n";
		}

		echo "						</select>\n";
		echo "					</td>\n";
		echo "				</tr>\n";
		echo "				<tr bgcolor=\"#CCCCCC\"><td valign=\"middle\" class=\"strong\" align=\"center\"><font color=\"#000066\">{ OR }</font></td><td colspan=\"4\">&nbsp;</td></tr>\n";
		echo "				<tr bgcolor=\"#CCCCCC\">\n";
		echo "					<td align=\"left\" valign=\"middle\" class=\"strong\">&nbsp;&nbsp;&nbsp;&nbsp;Select a Department:</td>\n";
		echo "					<td width=\"27%\" align=\"left\" valign=\"top\">\n";
		echo "					<select name=\"select_dept\" DISABLED>\n";
		echo "						<option>-- Select a Department -- </option>\n";
      	echo "					</select>\n";
		echo "				</td>\n";
		echo "				<td width=\"8%\" align=\"left\" valign=\"middle\">&nbsp;</td><td width=\"16%\" align=\"left\" valign=\"middle\" class=\"strong\">&nbsp;</td><td width=\"22%\" align=\"left\" valign=\"middle\">&nbsp;</td>\n";
		echo "				</tr>\n";

		echo "				<tr bgcolor=\"#FFFFFF\">\n";
		echo "					<td width=\"27%\" align=\"left\" valign=\"middle\" class=\"strong\">2) Select a Course:</td>\n";
		echo "					<td colspan=\"5\" align=\"left\" valign=\"middle\">\n";

		$course_DISABLED = (is_null($course_list)) ? "DISABLED" : "";


		echo "						<select name=\"select_course\" $course_DISABLED onChange=\"this.form.submit();\">\n";
		echo "							<option value=\"null\">-- Select a Course -- </option>\n";

		for ($i=0;$i<count($course_list);$i++)
		{
			$course_selector = (isset($request['select_course']) && $request['select_course'] == $course_list[$i]->getCourseID()) ? "selected" : "";
			echo "							<option value=\"". $course_list[$i]->getCourseID() ."\" $course_selector>". $course_list[$i]->displayCourseNo() . " " . $course_list[$i]->getName() ."</option>\n";
		}

		echo "						</select>\n";

		echo "					</td>\n";
		echo "				</tr>\n";
		echo "				<tr align=\"left\">\n";
		echo "					<td colspan=\"6\" valign=\"middle\" bgcolor=\"#CCCCCC\" class=\"strong\" align=\"left\">3) Select Class:</td>\n";
		echo "				</tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td align=\"left\" valign=\"top\" class=\"borders\">\n";
		echo "			<table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"5\" cellspacing=\"0\" class=\"displayList\">\n";
		echo "				<tr align=\"left\" valign=\"middle\" bgcolor=\"#CCCCCC\" class=\"headingCell1\">\n";
		echo "					<td width=\"10%\">Select</td><td width=\"15%\">&nbsp;</td>\n";
		echo "					<td>&nbsp;</td><td>Taught By</td><td>Last Active</td><td width=\"20%\">Reserve List</td>\n";
		echo "				</tr>\n";

		for($i=0;$i<count($ci_list);$i++)
		{
			$rowClass = ($i % 2) ? "evenRow" : "oddRow";

			echo "				<tr align=\"left\" valign=\"middle\" class=\"$rowClass\">\n";

				$class_SELECTED = ((isset($_REQUEST['ci']) && $_REQUEST['ci'] != null) && ($ci_list[$i]->getCourseInstanceID()==$_REQUEST['ci'])) ? "CHECKED" : "";

			echo "					<td width=\"10%\" align=\"center\"><input type=\"radio\" name=\"ci\" $class_SELECTED value=\"". $ci_list[$i]->getCourseInstanceID() ."\" onClick=\"this.form.submit();\"></td>\n";
			echo "					<td width=\"15%\">".$ci_list[$i]->course->displayCourseNo()."</td>\n";
			echo "					<td>".$ci_list[$i]->course->getName()."</td>\n";
			echo "					<td>".$ci_list[$i]->displayInstructorList()."</td>\n";
			echo "					<td width=\"20%\" align=\"center\">".$ci_list[$i]->displayTerm()."</td>\n";
			echo "					<td width=\"20%\" align=\"center\"><a href=\"javascript:openWindow('no_control&cmd=previewReservesList&ci=".$ci_list[$i]->courseInstanceID . "','width=800,height=600');\">preview</a></td>\n";
			echo "				</tr>\n";
		}

		echo "				<tr align=\"left\" valign=\"middle\" bgcolor=\"#CCCCCC\" class=\"headingCell1\"><td colspan=\"6\">&nbsp;</td></tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr><td align=\"left\" valign=\"top\">&nbsp;</td></tr>\n";
		echo "</table>\n";
	}

	function instructorLookup($instr_list, $request, $hidden_fields=null)
	{
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

		echo "					<td>\n";

		//set selected
		$username = "";
		$last_name = "";
		$selector = (isset($request['select_instr_by'])) ? $request['select_instr_by'] : "last_name";
		$$selector = "selected";

		echo "						<select name=\"select_instr_by\">\n";
		echo "							<option value=\"last_name\" $last_name>Last Name</option>\n";
		echo "							<option value=\"username\" $username>User Name</option>\n";
		$instr_qryTerm = isset($request['instr_qryTerm']) ? $request['instr_qryTerm'] : "";
		echo "						</select> &nbsp; <input name=\"instr_qryTerm\" type=\"text\" value=\"".stripslashes($instr_qryTerm)."\" size=\"15\"  onBlur=\"this.form.submit();\">\n";
		echo "						&nbsp;\n";
		echo "						<input type=\"submit\" name=\"instr_search\" value=\"Search\" onClick=\"this.form.selected_instr.selectedIndex=-1;\">\n"; //by setting selectedIndex to -1 we can clear the selectbox or previous values
		echo "						&nbsp;\n";

		$inst_DISABLED = (is_null($instr_list)) ? "DISABLED" : "";
		echo "						<select name=\"selected_instr\" $inst_DISABLED onChange=\"this.form.submit();\">\n";
		echo "							<option value=\"null\">-- Choose an Instructor -- </option>\n";

		for($i=0;$i<count($instr_list);$i++)
		{
			$inst_selector = (isset($request['selected_instr']) && $request['selected_instr'] == $instr_list[$i]->getUserID()) ? "selected" : "";
			echo "							<option value=\"". $instr_list[$i]->getUserID() ."\" $inst_selector>". $instr_list[$i]->getName() ."</option>\n";
		}

		echo "						</select>\n";
		echo "					</td>\n";
	}
}

?>