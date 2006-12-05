<?
/*******************************************************************************
adminDisplayer.class.php


Created by Jason White (jbwhite@emory.edu)

This file is part of GNU ReservesDirect 2.1

Copyright (c) 2004-2006 Emory University, Atlanta, Georgia.

Licensed under the ReservesDirect License, Version 1.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the full License at
http://www.reservesdirect.org/licenses/LICENSE-1.0

ReservesDirect is distributed in the hope that it will be useful,
but is distributed "AS IS" and WITHOUT ANY WARRANTY, without even the
implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
PURPOSE, and without any warranty as to non-infringement of any third
party's rights.  See the License for the specific language governing
permissions and limitations under the License.


Reserves Direct 2.1 is located at:
http://www.reservesdirect.org/

*******************************************************************************/
require_once("secure/common.inc.php");
require_once("secure/classes/terms.class.php");
require_once('secure/displayers/helpDisplayer.class.php');

class adminDisplayer
{
	/**
	 * Display List all admins available to user
	 *
	 * $adminList array adminID, adminTitle
	 */
	function displayAdminFunctions()
	{
?>
	<a href="index.php?cmd=admin&function=editDept">Add/Edit Departments</a>
	<p />
	<a href="index.php?cmd=admin&function=editLib">Add/Edit Libraries</a>
	<p />
	
	<strong>Help</strong>
	<br />
	<a href="index.php?cmd=helpEditArticle">Add Article</a>
	<br />
	<a href="index.php?cmd=helpEditCategory">Add Category</a>
	<br />
	<form method="post" action="index.php?cmd=helpEditCategory">
		Edit 
		<?php helpDisplayer::displayCategorySelect(); ?>
		Category
		<input type="submit" name="submit" value="Edit" />
	</form>
<?php
	}
	
	function displayEditDept($function, $libraries)
	{		
		?>
		<script language="JavaScript1.2" src="secure/javascript/liveSearch.js"></script>
		<script language="JavaScript1.2">
			function deptnameReturnAction(dept_array)
			{
				eval("var dept = " + decode64(dept_array));	
					
				document.getElementById('editDeptArea').style.display = '';	

				if (typeof(dept['department_id']) != "undefined")
				{
					document.getElementById('dept_id').value 	= dept['department_id'];
					document.getElementById('dept_name').value 	= dept['name'];
					document.getElementById('dept_abbr').value 	= dept['abbreviation'];
					
					var lib = document.getElementById('library_id');
					
					for(var i=0; i<lib.options.length; i++)
						if (lib.options[i].value == dept['library_id'])
							lib.selectedIndex = i;
					
					document.getElementById('dept_name').enabled = true;
					document.getElementById('dept_abbr').enabled = true;
					document.getElementById('library_id').enabled = true;
					
					document.getElementById('dept_name').focus();
				}
				
			}
			
			function checkSubmit()
			{
				if (document.getElementById('dept_name').value == "")
				{
					alert ("Department Name is required.");
					return false;
				}
				
				if (document.getElementById('dept_abbr').value == "")
				{
					alert ("Department Abbreviation is required.");
					return false;
				}				
				
				if (document.getElementById('library_id').selectedIndex == -1)
				{
					alert ("Processing Library is required.");
					return false;
				}
			}
			
			function createNew(deptName){
				document.getElementById('editDeptArea').style.display = '';	
				
				document.getElementById('dept_id').enabled = false;
				document.getElementById('dept_name').value 	= deptName;
				document.getElementById('dept_abbr').value 	= '';
				document.getElementById('library_id').selectedIndex = -1;
					
				document.getElementById('dept_name').enabled = true;
				document.getElementById('dept_abbr').enabled = true;
				document.getElementById('library_id').enabled = true;
				
				document.getElementById('dept_name').focus();
			}
		</script>			
		
		<table>
			<tr><td width="5"></td><td><input type="button" onClick="createNew('');" value="New Department"></td></tr>
			<tr><td width="5"></td><td class="strong">Enter Department Name or Abbreviation:</td></tr>
			<tr><td width="5"></td>
		
				<td>					
					<input size=55 name="dept_search" id="dept_search" value="" onKeyPress="liveSearchStart(event, this, 'AJAX_functions.php?f=deptList', document.getElementById('LSResult'), 'deptnameReturnAction');">
					<div class="LSResult" id="LSResult" style="display: none;"><ul id="LSShadow"></ul></div>
					<script language="JavaScript">liveSearchInit(document.getElementById("dept_search"));</script>
				</td>
		
			</tr>
		<form method="POST" action="index.php?cmd=admin&function=saveDept" onSubmit="return checkSubmit();">
		<input type="hidden" id="dept_id" name="dept_id" value="">			

			<tr><td></td><td>&nbsp;</td></tr>

			<table id="editDeptArea" style="display: none;">
				<tr>
					<td>&nbsp;</td>
					<td>Department Name:</td>
					<td><input id="dept_name" name="dept_name" value="" size="55"></td>
				</tr>			
				
				<tr>
					<td>&nbsp;</td>
					<td>Department Abbreviation:</td>
					<td><input id="dept_abbr" name="dept_abbr" value="" size="55"></td>
				</tr>
				
				<tr>
					<td>&nbsp;</td>
					<td>Processing Library:</td>
					<td>
						<select name="library_id" id="library_id">
						<?
							foreach ($libraries as $l)
							{
								echo "<option value=\"" . $l->getLibraryID() ."\">". $l->getLibraryNickname() . "</option>";
							}
						?>
						</select>
					</td>
				</tr>
			</table>
			
		</table>		
		
		<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td><td><input type=submit value="Save Department" id="frmSubmit"></td></tr>
		
		</form>
		<?
	}

	function displayEditLibrary($libraries)
	{		
		?>
		<script language="JavaScript1.2" src="secure/javascript/liveSearch.js"></script>
		<script language="JavaScript1.2">
			function getLibraryData(element)
			{
				value = element.options[element.selectedIndex].value;
				if (liveSearchLast != value)
				{					
					liveSearchInitXMLHttpRequest();
					
					liveSearchReq.onreadystatechange = libraryReturnAction;
					liveSearchReq.open("GET", 'AJAX_functions.php?f=libList&qu=' + encode64(value) + "&rf=" + encode64('libraryReturnAction'));
					liveSearchLast = value;
			
					liveSearchReq.send(null);
				}
			}
		
			function libraryReturnAction(event, lib_array)
			{				
				if (liveSearchReq.readyState == 4) {					
					eval("var lib = " + decode64(liveSearchReq.responseText));	
					
					document.getElementById('lib_id').value 			= lib['id'];
					document.getElementById('lib_name').value 			= lib['name'];
					document.getElementById('lib_nickname').value 		= lib['nickname'];
					document.getElementById('ils_prefix').value 		= lib['ils_prefix'];										
					document.getElementById('desk').value 				= lib['desk'];
					document.getElementById('lib_url').value		 	= lib['url'];		
					document.getElementById('contactEmail').value 	= lib['email'];			
					
					var mono = document.getElementById('monograph_library_id');												
					for(var i=0; i<mono.options.length; i++)
						if (mono.options[i].value == lib['monograph_library_id'])
							mono.selectedIndex = i;
					
					var multi = document.getElementById('multimedia_library_id');						
					for(var i=0; i<multi.options.length; i++)
						if (multi.options[i].value == lib['multimedia_library_id'])
							multi.selectedIndex = i;
							
					document.getElementById('lib_id').enabled = true;
					document.getElementById('lib_name').enabled = true;
					document.getElementById('lib_nickname').enabled = true;
					document.getElementById('ils_prefix').enabled = true;
					document.getElementById('desk').enabled = true;
					document.getElementById('lib_url').enabled = true;
					document.getElementById('contactEmail').enabled = true;
					document.getElementById('monograph_library_id').enabled = true;
					document.getElementById('multimedia_library_id').enabled = true;
					
					document.getElementById('lib_name').focus();
				}				
			}
			
			function checkSubmit()
			{
				if (document.getElementById('lib_name').value == "")
				{
					alert ("library Name is required.");
					return false;
				}
				
				if (document.getElementById('lib_nickname').value == "")
				{
					alert ("library nickname is required.");
					return false;
				}				
				
				if (document.getElementById('monograph_library_id').selectedIndex == -1)
				{
					alert ("Monograph Processing Library is required.");
					return false;
				}
				if (document.getElementById('multimedia_library_id').selectedIndex == -1)
				{
					alert ("Multimedia Processing Library is required.");
					return false;
				}				

			}
			
			function createNew()
			{
				document.getElementById('lib_id').value 			= "";
				document.getElementById('lib_name').value 			= "";
				document.getElementById('lib_nickname').value 		= "";
				document.getElementById('ils_prefix').value 		= "";
				document.getElementById('desk').value 				= "";
				document.getElementById('lib_url').value		 	= "";
				document.getElementById('contactEmail').value 		= "";
				
				document.getElementById('monograph_library_id').selectedIndex = -1;
				document.getElementById('multimedia_library_id').selectedIndex = -1;
					
				document.getElementById('lib_id').enabled = false;
				document.getElementById('lib_name').enabled = true;
				document.getElementById('lib_nickname').enabled = true;
				document.getElementById('ils_prefix').enabled = true;
				document.getElementById('desk').enabled = true;
				document.getElementById('lib_url').enabled = true;
				document.getElementById('contactEmail').enabled = true;
				document.getElementById('monograph_library_id').enabled = true;
				document.getElementById('multimedia_library_id').enabled = true;
				
				document.getElementById('lib_name').focus();
			}
		</script>			
		
		
		<table>
			<tr><td width="5"></td><td class="strong">Enter library Name or nickname:</td></tr>
			<tr><td width="5"></td>
		
				<td>					
						<select name="lib_search" id="lib_search" onChange="getLibraryData(this);" >
						<?
							foreach ($libraries as $l)
							{
								echo "<option value=\"" . $l->getLibraryID() ."\">". $l->getLibraryNickname() . "</option>";
							}
						?>
						</select>
						&nbsp;
						<!--<input type="button" value="Lookup Library" onClick="getLibraryData(document.getElementById('lib_search'));">-->
						&nbsp;
						<input type="button" value="Create New Library" onClick="createNew();">
					<div class="LSResult" id="LSResult1" style="display: none;"><ul id="LSShadow"></ul></div>					
				</td>
		
			</tr>
		<form method="POST" action="index.php?cmd=admin&function=saveLib" onSubmit="return checkSubmit();">
		<input type="hidden" id="lib_id" name="lib_id" value="">			
			<tr><td></td><td>&nbsp;</td></tr>
			
			<table id="editlibArea">
				<tr>
					<td>&nbsp;</td>
					<td>Library Name:</td>
					<td><input id="lib_name" name="lib_name" value="" size="112" maxlength="100"></td>
				</tr>			
				
				<tr>
					<td>&nbsp;</td>
					<td>Library Nickname:</td>
					<td><input id="lib_nickname" name="lib_nickname" value="" size="20" maxlength="15"></td>
				</tr>
				
				<tr>
					<td>&nbsp;</td>
					<td>ILS Prefix:</td>
					<td><input id="ils_prefix" name="ils_prefix" value="" size="12" maxlength="10"></td>
				</tr>

				<tr>
					<td>&nbsp;</td>
					<td>Reserve Desk:</td>
					<td><input id="desk" name="desk" value="" size="55" maxlength="50"></td>
				</tr>				
				
				<tr>
					<td>&nbsp;</td>
					<td>URL:</td>
					<td><input id="lib_url" name="lib_url" value="" size="112"></td>
				</tr>				
				
				<tr>
					<td>&nbsp;</td>
					<td>Contact Email:</td>
					<td><input id="contactEmail" name="contactEmail" value="" size="112" maxlength="255"></td>
				</tr>				
				
				<tr>
					<td>&nbsp;</td>
					<td>Monograph Processing Library:</td>
					<td>
						<select name="monograph_library_id" id="monograph_library_id">
						<?
							foreach ($libraries as $l)
							{
								echo "<option value=\"" . $l->getLibraryID() ."\">". $l->getLibraryNickname() . "</option>";
							}
						?>
						</select>
					</td>
				</tr>
				
				<tr>
					<td>&nbsp;</td>
					<td>Multimedia Processing Library:</td>
					<td>
						<select name="multimedia_library_id" id="multimedia_library_id">
						<?
							foreach ($libraries as $l)
							{
								echo "<option value=\"" . $l->getLibraryID() ."\">". $l->getLibraryNickname() . "</option>";
							}
						?>
						</select>
					</td>
				</tr>				
			</table>
			
		</table>		
		
		<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td><td><input type=submit value="Save library" id="frmSubmit"></td></tr>
		
		</form>
		<script language="JavaScript">getLibraryData(document.getElementById('lib_search'));</script>
		<?
	}	
}
?>
