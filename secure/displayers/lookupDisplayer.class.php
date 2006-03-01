<?
/*******************************************************************************
lookupDisplayer.class.php


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

class lookupDisplayer
{
	function instructorLookup($instr_list, $request)
	{

		//echo "					<td>\n";
		echo "<table><tr><td>";
		
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

		$inst_DISABLED = (is_null($instr_list) || count($instr_list) < 1) ? "DISABLED" : "";
		
		echo "						<select id=\"selected_instr\" name=\"selected_instr\" $inst_DISABLED>\n";
		echo "							<option value=\"\">-- Choose an Instructor -- </option>\n";

		for($i=0;$i<count($instr_list);$i++)
		{
			$inst_selector = (isset($request['selected_instr']) && $request['selected_instr'] == $instr_list[$i]->getUserID()) ? "selected" : "";
			echo "							<option value=\"". $instr_list[$i]->getUserID() ."\" $inst_selector>". $instr_list[$i]->getName() ."</option>\n";
		}

		echo "						</select>\n";
		//echo "					</td>\n";
		echo "</td></tr></table>";
	}
}

?>