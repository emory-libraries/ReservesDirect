<?php
/*******************************************************************************
copyrightDisplayer.class.php
Copyright Displayer class

Created by Dmitriy Panteleyev (dpantel@emory.edu)

This file is part of ReservesDirect

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


ReservesDirect is located at:
http://www.reservesdirect.org/

*******************************************************************************/
require_once("secure/displayers/baseDisplayer.class.php");

class copyrightDisplayer extends baseDisplayer {
	
	/**
	 * @return void
	 * @param int $item_id ID of item using this copyright record
	 * @param string $default_status (optional) status option to check by default
	 * @param boolean $default_basis (optional) if applicable, the default status basis to check
	 * @desc displays copyright status options as radio options
	 */
	public function displayCopyrightEditStatus($item_id, $default_status='NEW', $default_basis_id=null) {
		//status
		$status_checked = array();
		$status_options = array('NEW', 'PENDING', 'APPROVED', 'DENIED');
		if(!in_array($default_status, $status_options)) {
			$default_status = 'NEW';
		}			
		foreach($status_options as $option) {
			$status_checked[$option] = ($default_status == $option) ? 'checked="checked"' : '';
		}
		
		//status bases
		$copyright = new Copyright();
		$status_bases = $copyright->getAllStatusBases();
?>
	<script language="JavaScript">
		var currentCopyrightStatusBasesBlockID = '';
		
		function showCopyrightStatusOptionDescription(option) {
			var option_descriptions = new Array();
			option_descriptions['NEW'] = '<em>This item has not yet been reviewed for copyright clearance.</em>';
			option_descriptions['PENDING'] = '<em>This item is pending approval and is/###/is not currently available to students.</em>';
			option_descriptions['APPROVED'] = '<em>Copyright clearance for this item has been approved.  It is available to students.</em>';
			option_descriptions['DENIED'] = '<em>Copyright clearance for this item has been denied.  It is not available to students or instructors.</em>';
			
			if(document.getElementById('copyright_status_option_desc')) {
				document.getElementById('copyright_status_option_desc').innerHTML = option_descriptions[option];
			}
			
			//attempt to show status bases
			showCopyrightStatusBases(option);
		}
		
		function showCopyrightStatusBases(status) {
			//hide the current block
			if(document.getElementById(currentCopyrightStatusBasesBlockID)) {
				document.getElementById(currentCopyrightStatusBasesBlockID).style.display = 'none';
				document.getElementById(currentCopyrightStatusBasesBlockID + '_select').disabled = true;
				document.getElementById(currentCopyrightStatusBasesBlockID + '_new').disabled = true;
			}
			//show the requested reasons block
			if(document.getElementById('copyright_basis_' + status)) {
				document.getElementById('copyright_basis_' + status).style.display = '';
				document.getElementById('copyright_basis_' + status + '_select').disabled = false;
				document.getElementById('copyright_basis_' + status + '_new').disabled = false;
				
				toggleCoprightStatusBasisSpecify(status);
			}			
			//save current status basis block id
			currentCopyrightStatusBasesBlockID = 'copyright_basis_' + status;
		}
		
		function toggleCoprightStatusBasisSpecify(status) {
			var selectbox, option, input;

			if(document.getElementById('copyright_basis_' + status + '_new')) {
				input = document.getElementById('copyright_basis_' + status + '_new');
				
				if(document.getElementById('copyright_basis_' + status + '_select')) {
					selectbox = document.getElementById('copyright_basis_' + status + '_select');
					option = selectbox.options[selectbox.selectedIndex].value;					
				
					if(option == '') {
						input.style.display = '';
					}
					else {
						input.style.display = 'none';
					}
				}
			}
		}
	</script>
	
	<form method="post" name="copyright_status_form" action="index.php?<?=$_SERVER['QUERY_STRING']?>">
		<input type="hidden" name="form_id" value="copyright_status" />
		<input type="hidden" name="item_id" value="<?=$item_id?>" />	
	
		<strong>Status:</strong>
		<input type="radio" name="copyright_status" value="NEW" <?=$status_checked['NEW']?> onclick="javascript: showCopyrightStatusOptionDescription('NEW');" />&nbsp;NEW 
		<input type="radio" name="copyright_status" value="PENDING" <?=$status_checked['PENDING']?> onclick="javascript: showCopyrightStatusOptionDescription('PENDING');" />&nbsp;PENDING  
		<input type="radio" name="copyright_status" value="APPROVED" <?=$status_checked['APPROVED']?> onclick="javascript: showCopyrightStatusOptionDescription('APPROVED');" />&nbsp;APPROVED</span> 
		<input type="radio" name="copyright_status" value="DENIED" <?=$status_checked['DENIED']?> onclick="javascript: showCopyrightStatusOptionDescription('DENIED');" />&nbsp;DENIED 
		<br />
		<div id="copyright_status_option_desc"></div>
	
		<br />
	
<?php	foreach($status_bases as $status=>$bases): ?>
		<div id="copyright_basis_<?=$status?>" style="display:none;">
			<strong>Reason:</strong>
			<select id="copyright_basis_<?=$status?>_select" name="copyright_status_basis_id" disabled="true" onchange="toggleCoprightStatusBasisSpecify('<?=$status?>')">
<?php		
			foreach($bases as $basis_id=>$basis):
				$checked_basis = ($basis_id==$default_basis_id) ? ' selected="true"' : '';
?>
				<option value="<?=$basis_id?>"<?=$checked_basis?>><?=$basis?></option>
<?php		endforeach; ?>
				<option value="">Other...</option>
			</select>
			<input type="text" size="30" id="copyright_basis_<?=$status?>_new" name="copyright_status_basis_new" style="display:none;" />
		</div>
<?php	endforeach; ?>
		<br />
		<input type="submit" name="submit_edit_item_copyright" value="Save Status" />
	</form>
	
	<script language="JavaScript">
		showCopyrightStatusOptionDescription('<?=$default_status?>');
	</script>
<?php
	}

	
	/**
	 * @return void
	 * @param int $item_id ID of item
	 * @param boolean $allow_add_edit If TRUE, will include ability to add new or edit selected contact
	 * @param boolean $refresh_page If TRUE, will refresh the page
	 * @desc Displays ajax copyright-lookup field; NOTE - requires: liveSearch.js, basicAJAX.js, copyright_ajax.js
	 */	
	public function displayCopyrightContactsBlockAJAX($item_id, $allow_add_edit=false, $refresh_page=false) {
		$set_contact_onclick = $refresh_page ? "javascript: contact_set_contact(); setTimeout('reload_page()', 500);" : "javascript: contact_set_contact();";
?>
		<script language="JavaScript1.2">
			function contactSearchReturnAction(contact_id_encoded) {
				//get contact_id
				eval("var contact_id = " + decode64(contact_id_encoded));
				
				//set fields
				document.getElementById('contact_id').value = contact_id;
				
				if(document.getElementById('contact_set_button')) {
					document.getElementById('contact_set_button').disabled = false;
				}

<?php
		//if allowing to edit the contact, then need to enable edit button
		if($allow_add_edit):
?>
				if(document.getElementById('contact_edit_button')) {
					document.getElementById('contact_edit_button').disabled = false;
				}
<?php	endif; ?>

				return false;
			}
			
			function reload_page() {
				window.location.href = window.location.href;
			}
		</script>

		<strong>Contact: </strong>
		<input type="text" size="40" id="search_contact_id" name="search_contact_id" onKeyPress="liveSearchStart(event, this, 'AJAX_functions.php?f=copyrightContactList', document.getElementById('search_contact_id_result'), 'contactSearchReturnAction');">&nbsp;
		<span style="font-size: x-small; font-style: italic;">Organization name</span>
		<div class="LSResult" id="search_contact_id_result" style="display:none;"><ul></ul></div>
		<script language="JavaScript">liveSearchInit(document.getElementById("search_contact_id"));</script>

		<input type="hidden" id="contact_item_id" name="contact_item_id" value="<?=$item_id?>" />
		<input type="hidden" id="contact_id" name="contact_id" value="" />
		
		<p />
		<input type="button" id="contact_set_button" name="set_contact" value="Set Contact" onclick="<?=$set_contact_onclick?>" disabled="true" />
<?php
		//include add/edit form
		if($allow_add_edit) {
			self::displayContactFormAJAX();
		}
	}
		
	
	/**
	 * @return void
	 * @desc Displays ajax copyright-edit/add form; NOTE - requires: basicAJAX.js, copyright_ajax.js
	 */	
	protected function displayContactFormAJAX() {
?>
	<div id="contactform_container" class="contactform_container" style="display:none;">
		<div id="contactform_bg" class="contactform_bg"></div>
		<div id="contactform" class="contactform"">
			<form id="contact_form" name="contact_form" onsubmit="javascript: return false;">
							
				<input type="hidden" id="edit_contact_id" name="edit_contact_id" value="" />
		
				<table>
					<tr>
						<th colspan="2">
							<strong>Add/Edit Contact Info</strong>
						</th>
					<tr>
						<td width="150">Organization name:</td>
						<td><input type="text" id="contact_org_name" name="contact_org_name" size="40" value="" /></td>
					</tr>
					<tr>
						<td>Address:</td>
						<td><input type="text" id="contact_address" name="contact_address" size="40" value="" /></td>
					</tr>
					<tr>
						<td>Phone(s):</td>
						<td><input type="text" id="contact_phone" name="contact_phone" size="40" value="" /></td>
					</tr>
					<tr>
						<td>Email(s):</td>
						<td><input type="text" id="contact_email" name="contact_email" size="40" value="" /></td>
					</tr>
					<tr>
						<td>WWW address:</td>
						<td><input type="text" id="contact_www" name="contact_www" size="40" value="" /></td>
					</tr>	
					<tr>
						<td>Primary contact name:</td>
						<td><input type="text" id="contact_name" name="contact_name" size="40" value="" /></td>
					</tr>
					<tr>
						<td colspan="2" style="text-align:center;">
							<input type="button" value="Cancel" onclick="javascript: contact_toggle_form(0); return false;" />
							<input type="button" value="Save" onclick="javascript: contact_save_contact(this.form); return false;" />
						</td>
					</tr>
				</table>
			</form>		
		</div>
	</div>

	<input id="contact_edit_button" disabled="true" type="button" value="Edit Contact" onclick="javascript: contact_edit_contact(); return false;" />
	<input id="contact_add_button" type="button" value="Create Contact" onclick="javascript: contact_add_contact(); return false;" />
<?php
	}


	/**
	 * @return void
	 * @param int $item_id Item id
	 * @return displays item copyright log
	 */
	public function displayCopyrightLogs($item_id) {
		$copyright = new Copyright($item_id);
		$logs = $copyright->getLogs();
?>
		<table class="simpleList" width="100%">
			<tr>
				<td class="labelCell1" style="text-align:center;"><strong>Date</strong></td>
				<td class="labelCell1" style="text-align:center;"><strong>User</strong></td>
				<td class="labelCell1" style="text-align:center;"><strong>Action</strong></td>
				<td class="labelCell1" style="text-align:center;"><strong>Details</strong></td>
			</tr>
<?php	foreach($logs as $log): ?>
			<tr>
				<td><?=date('M d, Y H:i', $log['u_tstamp'])?></td>
				<td><?=$log['username']?></td>
				<td><?=$log['action']?></td>
				<td><?=$log['details']?></td>
			</tr>
<?php	endforeach; ?>
		</table>
<?php
	}	
	
	
	/**
	 * @return void
	 * @param int $item_id Item id
	 * @return displays item supporting files edit
	 */
	public function displayCopyrightSupportingFile($item_id) {
		$copyright = new copyright($item_id);
		$supporting_items = $copyright->getSupportingItems();
?>
		<script type="text/javascript">
			function switchFileSource(show_upload, show_link) {
				if(document.getElementById('file_source_upload')) {
					if(show_upload) {
						document.getElementById('file_source_upload').style.display = '';
					}
					else {
						document.getElementById('file_source_upload').style.display = 'none';
					}
				}
				if(document.getElementById('file_source_link')) {
					if(show_link) {
						document.getElementById('file_source_link').style.display = '';
					}
					else {
						document.getElementById('file_source_link').style.display = 'none';
					}
				}										
			}
			
			function deleteFile(formObj, item_id) {
				if(document.getElementById('delete_file_id')) {
					document.getElementById('delete_file_id').value = item_id;
				}
				
				formObj.submit();
			}
			
			function checkForm(formObj) {
				//check title
				if(formObj.file_title.value=='') {
					alert('Need title for new file');
					return false;
				}
				
				var file_option = formObj.file_source_option[0].checked ? formObj.file_source_option[0] : formObj.file_source_option[1];
						
				//check file
				if((file_option.value=='file') && (formObj.userFile.value=='')) {
					alert('Need to specify file');
					return false;
				}
				//check url
				if((file_option.value=='url') && ((formObj.url.value=='') || (formObj.url.value=='http://'))) {
					alert('Need to specify link');
					return false;
				}
				
				return true;
			}
		</script>
		
		<form method="post" name="copyright_status_form" action="index.php?<?=$_SERVER['QUERY_STRING']?>">
		<div style="border-top:1px solid #333333; border-bottom:1px solid #333333;">
			<input type="hidden" name="item_id" value="<?=$item_id?>" />
			<input type="hidden" name="form_id" value="copyright_supporting_items_delete" />
			<input type="hidden" name="submit_edit_item_copyright" value="true" />	
			<input type="hidden" id="delete_file_id" name="delete_file_id" value="" />
<?php	
		$rowClass = 'oddRow';
		foreach($supporting_items as $sup_item):
			$rowClass = ($rowClass=='evenRow') ? 'oddRow' : 'evenRow';
?>
			<div class="<?=$rowClass?>" style="height:30px;">
				<div style="float:right; width:100px; text-align:center; padding:3px; border-left:1px solid #999999;">
					<input type="button" name="submit_edit_item_copyright" value="DELETE" onclick="javascript: deleteFile(this.form, <?=$sup_item->getItemID()?>)" />
				</div>
				<div style="padding:1px;">
					<img src="<?=$sup_item->getItemIcon()?>" alt="icon">&nbsp;
					<a href="reservesViewer.php?item=<?=$sup_item->getItemID()?>" target="_blank" class="itemTitle" style="margin:0px; padding:0px;"><?=$sup_item->getTitle()?></a>
				</div>
			</div>			
<?php	endforeach; ?>	
		</div>
		</form>
		
			
		<form method="post" enctype="multipart/form-data" name="copyright_status_form" action="index.php?<?=$_SERVER['QUERY_STRING']?>" onsubmit="javascript: return checkForm(this);" >
			<input type="hidden" name="item_id" value="<?=$item_id?>" />
			<input type="hidden" name="form_id" value="copyright_supporting_items_add" />
			<br />
			<strong>Add new supporing file:</strong>
			<br />
			Title:
			<input type="text" name="file_title" size="40" />
			<br />
			File:
			<input type="radio" name="file_source_option" value="file" checked="checked" onclick="javascript: switchFileSource(1,0);" /> Upload 
			<input type="radio" name="file_source_option" value="url" onclick="javascript: switchFileSource(0,1);" /> Link
			<br />
			<div id="file_source_upload">
				<input type="file" name="userFile" size="50" />
			</div>
			<div id="file_source_link" style="display:none;">
				<input name="url" type="text" size="50" value="http://" />
				<input type="button" onclick="openNewWindow(this.form.url.value, 500);" value="Preview" />
			</div>
			<br />
			<input type="submit" name="submit_edit_item_copyright" value="Add file" />
		</form>
<?php		
	}	
}
?>
