<?php
/*******************************************************************************
ajaxDisplayer.class.php
AJAX Displayer class

Created by Dmitriy Panteleyev (dpantel@emory.edu)

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
require_once("secure/displayers/baseDisplayer.class.php");

class ajaxDisplayer extends baseDisplayer {
	
	/**
	 * @return void
	 * @param string $nextCmd Action to take when the form is submitted
	 * @param string $button_label (optional) What to label the submit button
	 * @param array $hidden_fields (optional) Additional information that will be included in the form as hidden fields
	 * @param string $ci_variable name of html variable for selected ci
	 */	
	function classLookup($nextCmd, $button_label='Submit', $hidden_fields=null, $ci_variable = 'ci') {
?>
		<script language="JavaScript1.2" src="secure/javascript/liveSearch.js"></script>
		<script language="JavaScript1.2">
			function instSearchReturnAction(userArray)
			{
				eval ("var user = " + decode64(userArray));
				document.getElementById('inst_id').value = user['userID'];
				getClasses();
			}
			function deptSearchReturnAction(dept)
			{
				eval ("var dept = " + decode64(dept));
				document.getElementById('dept_id').value = dept['department_id'];
				getClasses();
			}
			function courseSearchReturnAction(course_info)
			{
				eval ("var course_info = " + decode64(course_info));

				if(course_info['num']) {
					document.getElementById('course_num').value = course_info['num'];
				}
				document.getElementById('course_name').value = course_info['name'];
				
				getClasses();
			}			
			function getClasses()
			{
				//clear hidden fields if needed
				if (document.getElementById('search_inst').value == "")
					document.getElementById('inst_id').value = '';
					
				if(document.getElementById('search_dept').value == "")
					document.getElementById('dept_id').value = '';
					
				if(document.getElementById('search_course').value == "") {
					document.getElementById('course_num').value = '';
					document.getElementById('course_name').value = '';
				}
				
				var value = document.getElementById('inst_id').value + "::" 
						  + document.getElementById('dept_id').value + "::" 
						  + document.getElementById('course_num').value + "::"
						  + document.getElementById('course_name').value + "::"
						  + document.getElementById('search_term').options[document.getElementById('search_term').selectedIndex].value + "::"
						  + document.getElementById('ci_variable').value
						  ;

				if (value != "::::::::::")
				{
					//document.getElementById('test').value = "AJAX_functions.php?f=classList&qu=" + encode64(value);
					
					liveSearchInitXMLHttpRequest();
					liveSearchReq.onreadystatechange = displayClasses;
					liveSearchReq.open("GET", "AJAX_functions.php?f=classList&qu=" + encode64(value));				
					liveSearchReq.send(null);
				} else
					resetFields();
			}
			
			function displayClasses()
			{
				if (liveSearchReq.readyState == 4) 
				{		
					//alert(liveSearchReq.responseText);
					document.getElementById("course_area").innerHTML = liveSearchReq.responseText;
					
				}
			}
			function enableButton() {document.getElementById("editButton").disabled = false;}
			
			function resetFields()
			{
				document.getElementById("search_inst").value = "";
				document.getElementById("search_dept").value = "";
				document.getElementById("search_course").value = "";
				document.getElementById("search_term").selectedIndex = 0;
				
				document.getElementById("course_area").innerHTML = "";
			}

			function getTerms()
			{
				liveSearchInitXMLHttpRequest();
				liveSearchReq.onreadystatechange = loadTerms;
				liveSearchReq.open("GET", "AJAX_functions.php?f=termsList&qu=");
				liveSearchReq.send(null);		
			}
			
			function loadTerms()
			{
				if (liveSearchReq.readyState == 4) 
				{
					eval ("var terms = " + liveSearchReq.responseText);
					
					var today = new Date();
					//get pieces of date
					var cur_month = today.getMonth() + 1;
					var cur_day = today.getDate();
					//format pieces of date
					if(cur_month < 10) {
						cur_month = '0' + cur_month;
					}
					if(cur_day < 10) {
						cur_day = '0' + cur_day;
					}					
					//build date string
					today = today.getFullYear() + '-' + cur_month + '-' + cur_day;				
										
					document.getElementById("search_term").options[0] = new Option("All Terms", "");

					//must index options as i+1 to offset All Terms at index 0
					for(var i = 0; i<terms.length; i++)					
					{
						document.getElementById("search_term").options[i+1] = new Option(terms[i]['term_name'] + " " + terms[i]['term_year'], terms[i]['term_id']);
						if (terms[i]['begin_date'] <= today && today <= terms[i]['end_date'])
							document.getElementById("search_term").selectedIndex = i+1;	
					}
				}
			}	
		</script>
		
			<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" valign="top">
				<tr align="left" valign="top">
					<td width="35%" align="left" class="headingCell1" align="center">Class Lookup</td>
					<td width="75%" align="center"></td>
				</tr>
			</table>

			<input id="inst_id" value="" type="hidden"/>
			<input id="dept_id" value="" type="hidden"/>
			<input id="course_num" value="" type="hidden"/>
			<input id="course_name" value="" type="hidden"/>
			<input id="ci_variable" value="<?=$ci_variable?>" type="hidden"/>
			
			<!--<br><input id=test><br>-->
			
			<table width="100%" class="borders" border="0" align="center" cellpadding="5" cellspacing="0">
				<tr align="left" valign="top" bgcolor="#CCCCCC">
					<td width="5%">&nbsp;</td>
					<td class="strong">Instructor:</td>
					<td>
						<input type="text" size="55" id="search_inst" name="search_inst" onKeyPress="liveSearchStart(event, this, 'AJAX_functions.php?f=userList&amp;role=3', document.getElementById('LSResult'), 'instSearchReturnAction');">
						<div class="LSResult" id="LSResult" style="display:none;"><ul></ul></div>
						<script language="JavaScript">liveSearchInit(document.getElementById("search_inst"));</script>
					</td>
					<td width="100%" align="left" style="font-size: x-small; font-style: italic;">Name OR username</td>
				</tr>
				<tr bgcolor="#CCCCCC">
					<td width="5%">&nbsp;</td>
					<td class="strong">Department:</td>
					<td>
						<input type="text" size="55" id="search_dept" name="search_dept" onKeyPress="liveSearchStart(event, this, 'AJAX_functions.php?f=deptList', document.getElementById('LSResult1'), 'deptSearchReturnAction');">
						<div class="LSResult" id="LSResult1" style="display:none;"><ul></ul></div>
						<script language="JavaScript">liveSearchInit(document.getElementById("search_dept"));</script>
					</td>					
					<td align="left" style="font-size: x-small; font-style: italic;">Department Name OR Abbreviation</td>
				</tr>
				<tr bgcolor="#CCCCCC">
					<td width="5%">&nbsp;</td>
					<td class="strong">Course:</td>
					<td>
						<input type="text" size="55" id="search_course" name="search_course" onKeyPress="liveSearchStart(event, this, 'AJAX_functions.php?f=courseList', document.getElementById('LSResult2'), 'courseSearchReturnAction');">
						<div class="LSResult" id="LSResult2" style="display:none;"><ul></ul></div>
						<script language="JavaScript">liveSearchInit(document.getElementById("search_course"));</script>
					</td>					
					<td align="left" style="font-size: x-small; font-style: italic;">Course Number and/or Name</span></td>
				</tr>
				<tr bgcolor="#CCCCCC">
					<td width="5%">&nbsp;</td>
					<td class="strong">Term:</td>
					<td><select id="search_term" onChange="getClasses();"></select></td>
					<script language="JavaScript">getTerms();</script>
					<td>&nbsp;</td>
				</tr>
				<tr bgcolor="#CCCCCC">
					<td colspan="2">&nbsp;</td>
					<td>
						<input type="button" onClick="getClasses()" value="Refresh">
						&nbsp;&nbsp;
						<input type="button" onClick="resetFields()" value="Reset">
					</td>
					<td>&nbsp;</td>
				</tr>
			</table>
			
		<form action="index.php" method="POST">
			<div>
				<?php self::displayHiddenFields($hidden_fields); ?>
				<input type="hidden" name="cmd" value="<?=$nextCmd?>" />
			
				<div id="course_area" style="border-bottom:1px solid #666666;"></div>
				<p>
					<img src="images/pencil.gif" width="24" height="20" /> <span style="font-size:small;">= active courses</span> &nbsp;
					<img src="images/activate.gif" width="24" height="20" /> <span style="font-size:small;">= new courses not yet in use</span> &nbsp;
					<img src="images/cancel.gif" width="24" height="20" /> <span style="font-size:small;">= courses canceled by the registrar</span> &nbsp;
				</p>		
			</div>
			<div align="center" style="padding:10px;"><input id="editButton" type="submit" value="<?= $button_label ?>" DISABLED></div>
		</form>
<?php
	}
	
	
	/**
	 * @return void
	 * @param string $nextCmd Action to take when the form is submitted
	 * @param string $button_label (optional) What to label the submit button
	 * @param array $hidden_fields (optional) Additional information that will be included in the form as hidden fields
	 * @param boolean $standalone (optional) If true, displays complete form, else just displays the field (to be part of a form)
	 * @param int $min_user_role (optional) Search for user by role (searches for specified role and above). Default is all users
	 * @param string $field_id (optional) ID and name to use for the <input> field
	 */	
	function userLookup($nextCmd, $button_label='Submit', $hidden_fields=null, $standalone=true, $min_user_role=0, $field_id='user_id') {
		//prefill if possible
		$dflt_user_id = !empty($_REQUEST[$field_id]) ? $_REQUEST[$field_id] : '';
		$dflt_user_label = !empty($_REQUEST['search_'.$field_id]) ? $_REQUEST['search_'.$field_id] : '';
?>
		<script language="JavaScript1.2" src="secure/javascript/liveSearch.js"></script>
		<script language="JavaScript1.2">
			function userSearchReturnAction(userArray)
			{
				eval ("var user = " + decode64(userArray));
				document.getElementById('<?=$field_id?>').value = user['userID'];
				
				if(document.getElementById('editButton')) {
					document.getElementById('editButton').disabled = false;
				}
				return false;
			}
		</script>

<?php	if($standalone): ?>
		<div>
			<div class="headingCell1" style="width:33%;">User Lookup</div>
			<div class="borders" style="padding:5px; background-color:#CCCCCC;">
				<strong>User:</strong>&nbsp;
<?php	endif; ?>

				<input type="text" size="40" id="search_<?=$field_id?>" name="search_<?=$field_id?>" value="<?=$dflt_user_label?>" onKeyPress="liveSearchStart(event, this, 'AJAX_functions.php?f=userList&amp;role=<?=$min_user_role?>', document.getElementById('search_<?=$field_id?>_result'), 'userSearchReturnAction');">&nbsp;
				<span style="font-size: x-small; font-style: italic;">Name OR username</span>
				<div class="LSResult" id="search_<?=$field_id?>_result" style="display:none;"><ul></ul></div>
				<script language="JavaScript">liveSearchInit(document.getElementById("search_<?=$field_id?>"));</script>
				
<?php	if($standalone): ?>
			</div>
		</div>
				
		<form action="index.php" method="post">
<?php	endif; ?>

			<?php self::displayHiddenFields($hidden_fields); ?>			
			<input id="<?=$field_id?>" name="<?=$field_id?>" value="<?=$dflt_user_id?>" type="hidden">
				
<?php	if($standalone): ?>	
			<input type="hidden" name="cmd" value="<?=$nextCmd?>" />		
			<br />
			<div align="center"><input id="editButton" type="submit" value="<?=$button_label?>" disabled=true></div>
		</form>
<?php	endif;
	}
}