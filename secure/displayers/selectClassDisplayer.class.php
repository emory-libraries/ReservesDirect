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

Created by Jason White (jbwhite@emory.edu)

Reserves Direct 2.0 is located at:
http://coursecontrol.sourceforge.net/

*******************************************************************************/
require_once("common.inc.php");
//require_once("classes/reserves.class.php");

class selectClassDisplayer 
{
	function classLookup($nextCmd, $performAction, $instr_list, $course_list, $ci_list, $request, $hidden_fields=null)
	{
		echo "<form action=\"index.php\" method=\"POST\">\n";
		echo "<input type=\"hidden\" name=\"cmd\" value=\"$nextCmd\">\n";
		if (is_array($hidden_fields)){
			$keys = array_keys($hidden_fields);
			foreach($keys as $key){
				if (is_array($hidden_fields[$key])){
					foreach ($hidden_fields[$key] as $field){
						echo "<input type=\"hidden\" name=\"".$key."[]\" value=\"". $field ."\">\n";	
						echo 'key: '.$key.'&nbsp;field: '.$field.'<br>'; //kaw
					}
				} else {
					echo "<input type=\"text\" name=\"$key\" value=\"". $hidden_fields[$key] ."\">\n";
				}
			}
		}
		
		echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr><td width=\"140%\"><img src=\../images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";
		echo "	<tr>\n";
		echo "		<td height=\"14\" align=\"left\" valign=\"top\">\n";
		echo "			<table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\">\n";
		echo "				<tr align=\"left\" valign=\"top\">\n";
		echo "					<td width=\"35%\" align=\"left\" class=\"headingCell1\" align=\"center\">CLASS LOOKUP</td><td width=\"75%\" align=\"center\"></td>\n";
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
		$selector = (isset($request['select_instr_by'])) ? $request['select_instr_by'] : "last_name";
		$$selector = "selected";
		
		echo "						<select name=\"select_instr_by\">\n";
		echo "							<option value=\"last_name\" $last_name>Last Name</option>\n";
		echo "							<option value=\"username\" $username>User Name</option>\n";
		echo "						</select> &nbsp; <input name=\"instr_qryTerm\" type=\"text\" value=\"".$request['instr_qryTerm']."\" size=\"15\"  onBlur=\"this.form.submit();\">\n";
		echo "						&nbsp;\n";
		echo "						<input type=\"submit\" name=\"instr_search\" value=\"Search\" onClick=\"this.form.select_course.selectedIndex=-1; this.form.selected_instr.selectedIndex=-1;\">\n"; //by setting selectedIndex to -1 we can clear the selectbox or previous values
		echo "						&nbsp;\n";
		
		//set selected
				
	
		$inst_DISABLED = (is_null($instr_list)) ? "DISABLED" : "";
		
		echo "						<select name=\"selected_instr\" $inst_DISABLED onChange=\"this.form.select_course.selectedIndex=-1; this.form.submit();\">\n";		
		echo "							<option value=\"null\">-- Choose an Instructor -- </option>\n";
		
		for($i=0;$i<count($instr_list);$i++)
		{
			$inst_selector = ($request['selected_instr'] == $instr_list[$i]->getUserID()) ? "selected" : "";
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
		echo "							<option value=\"null\" $course_selector>-- Select a Course -- </option>\n";
		
		for ($i=0;$i<count($course_list);$i++)
		{
			$course_selector = ($request['select_course'] == $course_list[$i]->getCourseID()) ? "selected" : "";
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
			echo "					<td width=\"10%\" align=\"center\"><input type=\"radio\" name=\"ci\" value=\"". $ci_list[$i]->getCourseInstanceID() ."\" onClick=\"this.form.performAction.disabled=false\"></td>\n";
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
		echo "	<tr><td valign=\"top\" align=\"center\"><input type=\"submit\" name=\"performAction\" value=\"$performAction\" DISABLED></td></tr>\n";
		echo "	<tr><td align=\"left\" valign=\"top\"><img src=\../images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
		echo "</table>\n";		
		echo "</form>\n";
	}
}

?>