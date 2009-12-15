<?
/*******************************************************************************
itemDisplayer.class.php

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

require_once('secure/classes/copyright.class.php');
require_once('secure/displayers/noteDisplayer.class.php');
require_once('secure/displayers/copyrightDisplayer.class.php');
require_once('secure/managers/ajaxManager.class.php');

class itemDisplayer extends noteDisplayer {
	
	
	/**
	 * @return void
	 * @param reserveItem object $reserveItem
	 * @desc Displays the edit-item-source block
	 */
	function displayEditItemSource(&$reserveItem) {
		global $u, $g_permission;

		//editing an electronic item - show URL/upload fields
		if($reserveItem->getItemGroup() == 'ELECTRONIC'):
?>
		<div class="headingCell1">ITEM SOURCE</div>
		<div id="item_source" style="padding:8px 8px 12px 8px;">
			<script language="JavaScript">
				var currentItemSourceOptionID;
				
				function toggleItemSourceOptions(option_id) {
					if(document.getElementById(currentItemSourceOptionID)) {
						document.getElementById(currentItemSourceOptionID).style.display = 'none';
					}
					if(document.getElementById(option_id)) {
						document.getElementById(option_id).style.display = '';
					}
					
					currentItemSourceOptionID = option_id;
				}
			</script>		

			<div style="overflow:auto;" class="strong">
				Current URL <small>[<a href="reservesViewer.php?item=<?=$reserveItem->getItemID()?>" target="_blank">Preview</a>]</small>: 
<?php		if($reserveItem->isLocalFile()): //local file ?>
				Local File 
<?php			if($u->getRole() >= $g_permission['staff']): //only show local path to staff or greater ?>
				 &ndash; <em><?=$reserveItem->getURL()?></em>
<?php			endif; ?>
<?php		else: //remote file - show link to everyone ?>
				<em><?=$reserveItem->getURL()?></em>
<?php		endif; ?>
			</div>
			<small>
				Please note that items stored on the ReservesDirect server are access-restricted; use the Preview link to view the item.
				<br />
				To overwrite this URL, use the options below.
			</small>
			<p />
			<div>
				<input type="radio" name="documentType" checked="checked" onclick="toggleItemSourceOptions('');" /> Maintain current URL &nbsp;
				<input type="radio" name="documentType" value="DOCUMENT" onclick="toggleItemSourceOptions('item_source_upload');" /> Upload new file &nbsp;
				<input type="radio" name="documentType" value="URL" onclick="toggleItemSourceOptions('item_source_link');" /> Change URL
			</div>
			<div style="margin-left:40px;">
				<div id="item_source_upload" style="display:none;">
					<input type="file" name="userFile" size="50" />
				</div>
				<div id="item_source_link" style="display:none;">
					<input name="url" type="text" size="50" />
					<input type="button" onclick="openNewWindow(this.form.url.value, 500);" value="Preview" />
				</div>
			</div>

<?php	if($u->getRole() >= $g_permission['staff']): //only show status to staff or greater ?>
			<? 
				$status = $reserveItem->getStatus();
				$$status = " checked='CHECKED' ";
			?>
<?php	endif; ?>			
		</div>	
<?php	
		//editing a physical item - show library, etc.
		//only allow staff or better to edit this info
		elseif($reserveItem->isPhysicalItem() && ($u->getRole() >= $g_permission['staff'])):	 
?>
		<div class="headingCell1">ITEM SOURCE</div>
		<div id="item_source" style="padding:8px 8px 12px 8px;">
	    	<table border="0" cellpadding="2" cellspacing="0">	
	    		<tr>
	    			<td align="right">
	    				Reserve Desk:
	    			</td>
	    			<td>
	    				<select name="home_library">
<?php
			foreach($u->getLibraries() as $lib):
				$selected_lib = ($reserveItem->getHomeLibraryID() == $lib->getLibraryID()) ? 'selected="selected"' : '';
?>
							<option value="<?=$lib->getLibraryID()?>"<?=$selected_lib?>><?=$lib->getLibrary()?></option>			
<?php		endforeach; ?>
	    				</select>
	    			</td>
	    		</tr>
<?php
			//details from the physical copy table (barcode/call num)
			if($reserveItem->getPhysicalCopy()):
?>
	    		<tr>		
					<td align="right">
						<font color="#FF0000">*</font>&nbsp;Barcode:
					</td>
					<td>
						<input name="barcode" type="text" id="barcode" size="30" value="<?=$reserveItem->physicalCopy->getBarcode()?>" />
	
					</td>
				</tr>
				<tr>		
					<td align="right">
						Call Number:
					</td>
					<td>
						<input name="call_num" type="text" id="call_num" size="30" value="<?=$reserveItem->physicalCopy->getCallNumber()?>" />
					</td>				
				</tr>
<?php			endif;	//end physical copy info ?>

			</table>
		</div>
<?php		
		endif; //end physical item block
	}	//displayEditItemSource()
	
	
	/**
	 * @return void
	 * @param Reserve object $reserve
	 * @desc Displays the edit-item-reserve-details block
	 */
	function displayEditItemReserveDetails(&$reserve) {
		global $calendar, $g_permission, $u;
		
		switch($reserve->getStatus()) {
			case 'ACTIVE':
				$reserve_status_active = 'checked="CHECKED"';
				$reserve_status_inactive = '';
				$reserve_status_denied_all = '';
				$reserve_block_vis = '';
				break;
			case 'INACTIVE':
				$reserve_status_active = '';
				$reserve_status_inactive = 'checked="CHECKED"';
				$reserve_status_denied_all = '';
				$reserve_block_vis = ' display:none;';
				break;
			case 'DENIED':
				$reserve_status_active = '';
				$reserve_status_inactive = '';
				$reserve_status_denied = 'checked="CHECKED"';
				$reserve_block_vis = ' display:none;';
		}
		
		//dates
		$reserve_activation_date = $reserve->getActivationDate();
		$reserve_expiration_date = $reserve->getExpirationDate();
		
		//set reset dates to course dates
		$ci = new courseInstance($reserve->getCourseInstanceID());
		$course_activation_date = $ci->getActivationDate();	
		$course_expiration_date = $ci->getExpirationDate();

		//determine the parent heading
		$parent_heading_id = $reserve->getParent();		
		if(empty($parent_heading_id)) {
			$parent_heading_id = 'root';	//this will pre-select the main list
		}
?>
		<script language="JavaScript">
		//<!--			
			//shows/hides activation/expiration date form elements
			function toggleDates() {
				if(document.getElementById('reserve_status_active').checked) {
					document.getElementById('reserve_dates_block').style.display = '';
				}
				else {
					document.getElementById('reserve_dates_block').style.display = 'none';
				}
			}
			
			//resets reserve dates
			function resetDates(from, to) {
				document.getElementById('reserve_activation_date').value = from;
				document.getElementById('reserve_expiration_date').value = to;
			}
		//-->
		</script>
		
		<div class="headingCell1">RESERVE DETAILS</div>
		<div id="reserve_details" style="padding:8px 8px 12px 8px;">
<?php	if($reserve->getStatus()=='DENIED ALL'): ?>	
			<div>
				<strong>Current Status:</strong>&nbsp;<span class="copyright_denied">Item Access Denied</span>
				<br />
				Access to this item has be denied for All Classes.  You must reactive the item status before making changes. 
				<input type="hidden" name="reserve_status" value="<?=$reserve->status?>"/>
			</div>
            <?php if($u->getRole() >= $g_permission['staff']): ?>
            <p>
				<div>
                        <input type="radio" name="item_status" <?= $ACTIVE ?> value="ACTIVE"/> <span class="active">Activate for all Classes</span>
                        <br /> <input type="radio" name="item_status" <?= $DENIED ?> value="DENIED"/> <span class="inactive">Deny use for all Classes</span>
				</div>
			</p>
            <?php endif; ?>
<?php else: ?>
	<?php	if (($reserve->getStatus() != 'DENIED' && $reserve->getStatus() != 'DENIED ALL') || $u->getRole() >= $g_permission['staff']): ?>				
		<?php	if(!in_array($reserve->getStatus(), array('ACTIVE', 'INACTIVE', 'DENIED', 'DENIED ALL'))): ?>
	
				<div>
					<strong>Current Status:</strong>&nbsp;<span class="inProcess"><?= $reserve->getStatus() ?></span>
					<br />
					Please contact your Reserves staff to inquire about the status of this reserve.
					<input type="hidden" name="reserve_status" value="IN PROCESS" />
				</div>
							
		<?php	else: ?>
				<div style="float:left; width:30%;">
					<strong>Set Status:</strong>
					<br />

					<div style="margin-left:10px; padding:3px;">
						<input type="radio" name="reserve_status" id="reserve_status_active" value="ACTIVE" onChange="toggleDates();" <?=$reserve_status_active?> />&nbsp;<span class="active">ACTIVE</span>
						<input type="radio" name="reserve_status" id="reserve_status_inactive" value="INACTIVE" onChange="toggleDates();" <?=$reserve_status_inactive?> />&nbsp;<span class="inactive">INACTIVE</span>					
			<?php	if ($u->getRole() >= $g_permission['staff']): ?>
						<br/><input type="radio" name="reserve_status" id="reserve_status_denied" value="DENIED" onChange="toggleDates();" <?=$reserve_status_denied?> />&nbsp;<span class="copyright_denied">DENY ACCESS FOR THIS CLASS ONLY</span>
                        <div style="overflow:auto;">
				<p>
                    <div class="strong">Item Status</div>
                    <div>
                        <input type="radio" name="item_status" <?= $ACTIVE ?> value="ACTIVE"/> <span class="active">Activate for all Classes</span>
                        <br /> <input type="radio" name="item_status" <?= $DENIED ?> value="DENIED"/> <span class="inactive">Deny use for all Classes</span>
                    </div>
				</p>
			</div>
			<?php 	endif; ?>
					</div>
		<?php 	endif; #if in process?>
				</div>
							
				<div id="reserve_dates_block" style="float:left;<?=$reserve_block_vis?>">
					<strong>Active Dates</strong> (YYYY-MM-DD) &nbsp;&nbsp; [<a href="#" name="reset_dates" onclick="resetDates('<?=$course_activation_date?>', '<?=$course_expiration_date?>'); return false;">Reset dates</a>]
					<br />
					<div style="margin-left:10px;">
						From:&nbsp;<input type="text" id="reserve_activation_date" name="reserve_activation_date" size="10" maxlength="10" value="<?=$reserve_activation_date?>" /> <?=$calendar->getWidgetAndTrigger('reserve_activation_date', $reserve_activation_date)?>
						To:&nbsp;<input type="text" id="reserve_expiration_date" name="reserve_expiration_date" size="10" maxlength="10" value="<?=$reserve_expiration_date?>" />  <?=$calendar->getWidgetAndTrigger('reserve_expiration_date', $reserve_expiration_date)?>
					</div>
				</div>
							
			<div style="clear:left; padding-top:10px;">
				<strong>Current Heading:</strong> 
				<?php self::displayHeadingSelect($ci, $parent_heading_id); ?>
			</div>		
		</div>					
	<?php	endif; ?>	
<?php	endif; ?>		
<?php
	}
	
	
	/**
	* @return void
	* @param reserveItem $item reserveItem object
	* @desc Displays the edit-item-item-details block
	*/	
	function displayEditItemItemDetails($item) {
		global $u, $g_permission, $g_catalogName;
				
		//private user
		if( !is_null($item->getPrivateUserID()) ) {
			$privateUserID = $item->getPrivateUserID();
			$item->getPrivateUser();
			$privateUser = $item->privateUser->getName(). ' ('.$item->privateUser->getUsername().')';
		}
?>
        
		<script language="JavaScript">
		//<!--
			//shows/hides personal item elements; marks them as required or not
			function togglePersonal(enable) {
				if(enable) {
					document.getElementById('personal_item_yes').checked = true;
					document.getElementById('personal_item_owner_block').style.display ='';
					togglePersonalOwnerSearch();
				}
				else {
					document.getElementById('personal_item_no').checked = true;
					document.getElementById('personal_item_owner_block').style.display ='none';
				}
			}
		
			//shows/hides personal item owner search fields
			function togglePersonalOwnerSearch() {
				//if no personal owner set, 
				if(document.getElementById('personal_item_owner_curr').checked) {
					document.getElementById('personal_item_owner_search').style.visibility = 'hidden';
				}
				else if(document.getElementById('personal_item_owner_new').checked) {
					document.getElementById('personal_item_owner_search').style.visibility = 'visible';
				}	
			}
						//shows/hides text field for 'other' material type
			function toggleOtherMaterialInput() {
			  var material_type = document.getElementById('material_type');
			  if (material_type.options[material_type.selectedIndex].value == "OTHER") {
			    document.getElementById('material_type_other_block').style.display = 'inline';
			  } else {
			    document.getElementById('material_type_other_block').style.display = 'none';
			  }
			}	
		//-->
		</script>
<script type="text/javascript">
  var materialType_details = <?= json_encode(common_materialTypesDetails()) ?>;
</script>
<script type="text/javascript" src="secure/javascript/editItem.js"></script>		    

				
		<div class="headingCell1">ITEM DETAILS</div>
		<div id="item_details" style="padding:8px 8px 12px 8px;">
		  <table class="editItem" border="0" cellpadding="2" cellspacing="0">
		    <tr class="required">
		      <th>Type of Material:</th>
		      <td>
    	 	      <select id="material_type" name="material_type" onChange="typeOfMaterial()">
<?php   		foreach(common_getMaterialTypes() as $material_id => $material): ?>
<?php	        	$selected = ($material_id == $item->getMaterialType()) ? ' selected="selected"' : ''; ?>
		        <option value="<?= $material_id ?>"<?= $selected ?>><?= $material ?></option>
		       <?php		endforeach ?>
		       </select>
		       <div id="material_type_other_block"
		       style="display:<?= ($item->getMaterialType() == 'OTHER') ? 'inline' : 'none' ?>">
		         <input name="material_type_other" id="material_type_other"
		            type="text" size="25" value="<?=$item->getMaterialType('detail') ?>"/>
		         <i>specify type of material</i>
		       </div>
		      </td>
		   </tr>

	    	   <tr id="title" class="required">
	    	      <th>Document Title:</th>
	    	      <td>
		        <input name="title" type="text" id="title" size="50" value="<?=$item->getTitle()?>">
		      </td>
		   </tr>
		   <tr id="author">
	    	     <th>Author/Composer:</th>
  		     <td>
		       <input name="author" type="text" id="author" size="50" value="<?=$item->getAuthor()?>">
		     </td>
		   </tr>
	    	   <tr id="work_title">
		     <th>Book/Journal/Work Title:</th>
		     <td>
			<input name="volumeTitle" type="text" id="volumeTitle" size="50" value="<?=$item->getVolumeTitle()?>">
		     </td>
	    	   </tr>
	    	   <tr id="edition">
		     <th>Volume/Edition:</td>
		     <td>
			<input name="volumeEdition" type="text" id="volumeEdition" size="50" value="<?=$item->getVolumeEdition()?>">
		     </td>
		   </tr>
		   <tr id="publisher">
		     <th>Publisher:</th>
		     <td><input name="publisher" type="text" size="50" value="<?=$item->getPublisher() ?>"> </td>
		   </tr>
	    	   <tr id="year">
	    	     <th>Source/Year:</th>
		     <td>
			<input name="source" type="text" id="source" size="50" value="<?=$item->getSource()?>">
		     </td>
	    	   </tr>
		   <tr id="performer">
		     <th>Performer:</th>
		     <td>
		       <input name="performer" type="text" id="performer" size="50" value="<?=$item->getPerformer()?>">
		     </td>
		   </tr>
		   <tr id="times_pages">
		     <th>Pages/Time:</th>
		     <td>
		       <input name="pagesTimes" type="text" id="pagesTimes" size="50" value="<?=$item->getPagesTimes()?>">
		     </td>
		     <? if ($item->getItemGroup() == 'ELECTRONIC'): ?>
		        <td><small>pp. 336-371 and pp. 399-442 (78 of 719)</small></td>
		     <? endif ?>
	    	   </tr>
		   <tr id="total_times_pages">
		     <th>Total Pages/Times:</th>
		     <td><input name="total_times_pages" type="text" size="50" value="<?= $item->getTotalPagesTimes() ?>"></td>
		   </tr>
			   
	    	   <tr>
		     <th>Document Type Icon:</th>
		     <td>
		       <select name="selectedDocIcon" onChange="document.iconImg.src = this[this.selectedIndex].value;">
<?php
		foreach($u->getAllDocTypeIcons() as $icon):
			$selected = ($item->getItemIcon() == $icon['helper_app_icon']) ? ' selected="selected"' : '';
?>
		         <option value="<?=$icon['helper_app_icon']?>"<?=$selected?>><?=$icon['helper_app_name']?></option>
<?php	endforeach; ?>
		       </select>
		       <img name="iconImg" width="24" height="20" src="<?=$item->getItemIcon()?>" />
		     </td>
		   </tr>
		   <tr id="isbn">
		     <th>ISBN:</th>
		     <td><input type="text" size="15" maxlength="13" value="<?= $item->getISBN() ?>" name="ISBN" /></td>
		   </tr>
                   <tr id="issn">
		     <th>ISSN:</th>
		     <td>
		       <input type="text" size="15" maxlength="8"  value="<?= $item->getISSN() ?>" name="ISSN" />
		     </td>
		   </tr>
                   <tr id="oclc">
		     <th>OCLC:</th>
		     <td>
		       <input type="text" size="15" maxlength="9"  value="<?= $item->getOCLC() ?>" name="OCLC" />
		     </td>
		   </tr>
			   
		   <tr id="availability">
		      <th>Availability:</th>
		      <td>
			   <? /* FIXME: set to checked accordingly! */ ?>
		       <input type="radio" name="availability" value="0"> <span id="availability_option0">unavailable</span>
		        <input type="radio" name="availability" value="1"> <span id="availability_option1">available</span>
		       </td>
		    </tr>

			   

<?php	
		if($item->isPhysicalItem()):
?>
		  <tr>
		     <th><?= $g_catalogName ?> Control Number:</th>
		     <td><input type="text" name="local_control_key" size="15" value="<?=$item->getLocalControlKey();?>" /></td>
		  </tr>
<?php   else: ?>
		   <tr id="barcode">
		      <th>Barcode:</th>
		      <td><input type="text" name="local_control_key" size="15" value="<?=$item->getLocalControlKey();?>" /></td>
		   </tr>
<?php	endif; ?>
	    		
<? /* disabling person-item owner
<?php		//only allow choosing personal-item owner to staff or better
		if($u->getRole() >= $g_permission['staff']):
		?>
    
    <tr id="personal_item_row" valign="top">
					<td align="right">
						Personal Copy Owner:
					</td>
					<td>
						<div id="personal_item_choice" style="background-color:#EEDDCC;">
							<input type="radio" name="personal_item" id="personal_item_no" value="no" onChange="togglePersonal(0);" /> No
							&nbsp;&nbsp;
							<input type="radio" name="personal_item" id="personal_item_yes" value="yes" onChange="togglePersonal(1);" /> Yes
						</div>
						<div id="personal_item_owner_block" style="padding:2px 3px 15px; background-color:#DFD8C6; border-top:1px dashed #999999;">
<?php
			//if there is an existing owner, give a choice of picking new one
			if(isset($privateUser)):
?>
							<input type="radio" name="personal_item_owner" id="personal_item_owner_curr" value="old" checked="checked" onChange="togglePersonalOwnerSearch();" /> Current - <strong><?=$privateUser?></strong>
							<br />
							<input type="radio" name="personal_item_owner" id="personal_item_owner_new" value="new" onChange="togglePersonalOwnerSearch();" /> New &nbsp;
<?php
			else:	//if not, then just assume we are searching for a new one
?>
							<input type="hidden" name="personal_item_owner" id="personal_item_owner_new" value="new" />
<?php
			endif;
?>
							<span id="personal_item_owner_search">
<?php
				//ajax user lookup
				$mgr = new ajaxManager('lookupUser', null, null, null, null, false, array('min_user_role'=>3, 'field_id'=>'selected_owner'));
				$mgr->display();		
?>
							</span>
						</div>
					</td>
				</tr>
<?php	endif; ?>
end personal-item owner (disabled)   */ ?>
			</table>
		</div>
		
<?php	
		//only allow choosing personal-item owner to staff or better
		if($u->getRole() >= $g_permission['staff']):
?>		
		<script language="JavaScript">
			//set up some fields on load
		   <? /* personal-item owner stuff disabled 
			if( document.getElementById('personal_item_owner_curr') != null ) {	//if there is already a private owner
				//select current owner
				document.getElementById('personal_item_owner_curr').checked = true;
				//show private owner block
				togglePersonal(1);			
			}
			else {
				//default to no private owner
				togglePersonal(0);
				} */ ?>
		   // init form based on selected type of material
		   typeOfMaterial();
		</script>
<?php
		endif;
	}
	
	
	/**
	 * @return void
	 * @param reserveItem $item reserveItem object
	 * @param reserves $reserve (optional) reserve object
	 * @desc Displays the edit-item-notes block
	 */	
	function displayEditItemNotes($item, $reserve=null) {
		global $u, $g_permission;
		
		//item notes
		$notes = $item->getNotes();
		//referrer obj for deleting notes
		$note_ref = 'itemID='.$item->getItemID();
		
		//reserve notes - only applies if we are editing a reserve (item instance linked to a course instance)
		if( !empty($reserve) && ($reserve instanceof reserve) ) {	//we are editing reserve info
			//notes
			$notes = $reserve->getNotes(true);
			//override referrer obj
			$note_ref = 'reserveID='.$reserve->getReserveID();			
		}
?>
		<div class="headingCell1">NOTES</div>
		<div id="item_notes" style="padding:8px 8px 12px 8px;">
			<?php self::displayEditNotes($notes, $note_ref); ?>
			
<?php
		//only allow adding notes to reserves (not items) unless edited by staff
		if(($u->getRole() >= $g_permission['staff']) || ($reserve instanceof reserve)):
?>
			<div id="add_note" style="text-align:center; padding:10px; border-top:1px solid #333333;">
				<?php self::displayAddNoteButton($note_ref); //display "Add Note" button ?>			
			</div>
<?php	endif; ?>

		</div>
<?php
	}
	

	/**
 	 * @return void
	 * @param reserveItem $item reserveItem object
	 * @param reserves $reserve (optional) reserve object
	 * @desc Displays the edit-item-notes block
	 */	
	function displayEditItemNotesAJAX($item, $reserve=null) {
		global $u, $g_permission, $g_notetype;
		
		//reserve notes - only applies if we are editing a reserve (item instance linked to a course instance)
		if( !empty($reserve) && ($reserve instanceof reserve) ) {	//we are editing reserve info
			$obj_type = 'reserve';
			$id = $reserve->getReserveID();			
		}
		else {
			$obj_type = 'item';
			$id = $item->getItemID();
		}
		
		//fetch notes
		$notes = noteManager::fetchNotesForObj($obj_type, $id, true);
		
		//only allow adding notes to reserves (not items) unless edited by staff
		$include_addnote_button = (($u->getRole() >= $g_permission['staff']) || ($obj_type=='reserve')) ? true : false;
?>

		<script language="JavaScript1.2" src="secure/javascript/basicAJAX.js"></script>
		<script language="JavaScript1.2" src="secure/javascript/notes_ajax.js"></script>

		<div class="headingCell1">NOTES</div>
		<div style="padding:8px 8px 12px 8px;">
			<?php self::displayNotesBlockAJAX($notes, $obj_type, $id, $include_addnote_button); ?>
		</div>
<?php
	}
	
	
	/**
	 * @return void
	 * @param reserveItem $item reserveItem object
	 * @param reserves $reserve (optional) reserve object
	 * @param array $dub_array (optional) array of information pertaining to duplicating an item. currently 'dubReserve' flag and 'selected_instr'
	 * @desc Displays form for editing item information (optionally: reserve information)
	 *
	 * @see requestDisplayer::addItem - significant overlap/duplication with this function
	 * @todo figure out a way to consolidate addItem and editItem logic?
	 */
	function displayEditItemMeta($item, $reserve=null, $dub_array=null) {
		global $u, $g_permission;
		
		//determine if editing a reserve
		if(!empty($reserve) && ($reserve instanceof reserve)) {	//valid reserve obj
			$edit_reserve = true;
			//make sure that the item is for this reserve
			$reserve->getItem();
			$item = $reserve->item;
		}
		else $edit_reserve = false;
		
		//build the form
?>
		<script language="JavaScript">
		//<!--
		   function submitForm() {
		     var form = document.getElementById('item_form');
		     if(form && validateForm(form)) {
		       document.getElementById('item_form').submit();
		     }
		   }

			function validateForm(frm,physicalCopy) {			
				var alertMsg = "";

				if (physicalCopy) {
					//make sure this physical copy is supposed to have a barcode
					//	if it is, there will be an input element for it in the form
					if( (document.getElementById('barcode') != null) && (document.getElementById('barcode').value == '') )
						alertMsg = alertMsg + "Barcode is required.<br />";
				}
				else if((frm.documentType.value == "DOCUMENT") && (frm.userFile.value == "")) {
					alertMsg = alertMsg + "You must choose a file to upload.<br />";
				}
				else if((frm.documentType.value == "URL") && (frm.url.value == "")) {
					alertMsg = alertMsg + "URL is required.<br />";
				}

				if (! physicalCopy) {
				  alertMsg += checkMaterialTypes(frm);
				}
				
				if (!alertMsg == "") { 
				  document.getElementById('alertMsg').innerHTML = alertMsg;
				  return false;
				} else {
				  return true;
				}					
			}
		//-->
		</script>
<? /* NOTE: this form has *significant* overlap with addItem in requestDisplayer; if you make changes here,
    you probably will need to make similar changes there also. */ ?>
		
<?php	if($item->getItemGroup() == 'ELECTRONIC'): ?>
		<form id="item_form" name="item_form" enctype="multipart/form-data" action="index.php?cmd=editItem" method="post" onSubmit="return validateForm(this,false);">
<?php	else: ?>
		<form id="item_form" name="item_form" action="index.php?cmd=editItem" method="post" onSubmit="return validateForm(this,true);">
<?php	endif; ?>

			<input type="hidden" name="submit_edit_item_meta" value="submit" />
			<input type="hidden" name="itemID" value="<?=$item->getItemID()?>" />
			<?php self::displayHiddenFields($dub_array); //add duplication info as hidden fields ?>	
<?php	if($edit_reserve): ?>
			<input type="hidden" name="reserveID" value="<?=$reserve->getReserveID()?>" />	
<?php	endif; ?>
			
			<div id="item_meta" class="displayArea">		
<?php
		//show reserve details block
		if($edit_reserve) {
			self::displayEditItemReserveDetails($reserve);
		}
		
		//show item source
		self::displayEditItemSource($item);
		
		//show item details
		self::displayEditItemItemDetails($item);
?>
		</form>		
<?php
		
		//show item/reserve notes
		if($u->getRole() >= $g_permission['staff']) {	//show ajax to staff and above
			self::displayEditItemNotesAJAX($item, $reserve);
		}
		else {	//show normal notes to everyone else
			self::displayEditItemNotes($item, $reserve);
		}
?>
		</div>

		<strong style="color:#FF0000;">*</strong> <span class="helperText">= required fields</span>
		<p />
		<div style="padding:10px; text-align:center;">
		    <input type="button" name="submit_edit_item_meta" value="Save Changes" onclick="javascript: submitForm();"> <? /*			<input type="button" name="submit_edit_item_meta" value="Save Changes" onClick="return validateForm(this.form, <?= ($item->getItemGroup() == 'ELECTRONIC') ? 'false' : 'true' ?>);">*/ ?>
		</div>
<?php		
	}
	
	
	/**
	 * @return void
	 * @param reserveItem object $item
	 * @desc Displays item history screen
	 */
	function displayItemHistory($item) {
		//get dates and terms
		$creation_date = date('F m, Y', strtotime($item->getCreationDate()));
		$creation_term = new term();
		$creation_term = $creation_term->getTermByDate($item->getCreationDate()) ? $creation_term->getTerm() : 'n/a';
		$modification_date = date('F m, Y', strtotime($item->getLastModifiedDate()));
		$modification_term = new term();
		$modification_term = $modification_term->getTermByDate($item->getLastModifiedDate()) ? $modification_term->getTerm() : 'n/a';
		
		//get creator (if electronic item), or home library (if physical item)
		if($item->isPhysicalItem()) {	//physical
			$home_lib_id = $item->getHomeLibraryID();
			if(!empty($home_lib_id)) {
				$home_lib = new library($home_lib_id);
				$owner = $home_lib->getLibrary();
			}
			else {
				$owner = 'n/a';
			}
			$owner_label = 'Owning Library';			
		}
		else {	//electronic
			$item_audit = new itemAudit($item->getItemID());
			$creator_id = $item_audit->getAddedBy();
			if(!empty($creator_id)) {
				$creator = new user($creator_id);
				$owner = $creator->getName(false).' ('.$creator->getUsername().') &ndash; '.$creator->getUserClass();
			}
			else {
				$owner = 'n/a';
			}			
			$owner_label = 'Created By';
			
		}
		
		//get reserve history
		$classes = $item->getAllCourseInstances();
		
		//get history totals
		
		//total # of classes
		$total_classes = sizeof($classes);		
		//total # of instructors
		$instructors = array();
		foreach($classes as $ci) {
			$ci->getInstructors();
			foreach($ci->instructorIDs as $instrID) {
				$instructors[] = $instrID;
			}
		}
		$instructors = array_unique($instructors);
		$total_instructors = sizeof($instructors);
?>
	<div class="displayArea">
		<div class="headingCell1">ITEM ORIGIN</div>
		<div id="item_origin" style="padding:8px 8px 12px 8px;">
			<div style="float:left; width:30%;">
				<strong>Item Created On:</strong>
				<br />
				<?=$creation_date?> (<?=$creation_term?>)
			</div>
			<div style="float:left; width:30%;">
				<strong><?=$owner_label?>:</strong>
				<br />
				<?=$owner?>
			</div>
			<div style="float:left; width:30%;">			
				<strong>Last Modified:</strong>
				<br />
				<?=$modification_date?> (<?=$modification_term?>)	
			</div>
			<div class="clear"></div>
		</div>
		<div class="headingCell1">CLASS HISTORY</div>
		<div id="item_history">
			<div style="padding:8px; border-bottom:1px solid #333333;">
				<strong>Total # of classes:</strong> <?=$total_classes?>
				<br />
				<strong>Total # of instructors:</strong> <?=$total_instructors?>
<!--				
				<br />
				<strong>Total times viewed (all semesters):</strong> ###
-->
			</div>
			
			<table width="100%" border="0" cellpadding="4" cellspacing="0">
				<tr class="headingCell2" align="left" style="text-align:left;">
					<td width="5%">&nbsp;</td>
					<td width="15%">Term</td>
					<td width="15%">Course Number</td>
					<td width="30%">Course Name</td>
					<td width="25%">Instructor</td>					
					<td width="10%">&nbsp;</td>					
				</tr>
<?php
			$rowClass = 'evenRow';
			//loop through the courses
			foreach($classes as $ci):
				$ci->getPrimaryCourse();	//fetch the course object
				$ci->getInstructors();	//get a list of instructors
				$rowClass = ($rowClass=='evenRow') ? 'oddRow' : 'evenRow';
				
				//determine if this is a currently-active class
				$now = time();  //get current time
				//if given only the date, strtotime() assumes we mean start of day, ie YYYY-MM-DD 00:00:00
				//to make sure we include the whole expiration day, we'll make it YYYY-MM-DD 23:59:59
				if(($ci->getStatus()=='ACTIVE') && (strtotime($ci->getActivationDate()) <= $now) && (strtotime($ci->getExpirationDate().' 23:59:59') >= $now)) {
					$icon = '<img src="images/astx-green.gif" alt="**" width="15" height="15" />';
				}
				else {
					$icon = '&nbsp;';
				}
?>
				<tr class="<?=$rowClass?>">
					<td align="center"><?=$icon?></td>
					<td><?=$ci->displayTerm()?></td>
	    			<td><?=$ci->course->displayCourseNo()?></td>
					<td><?=$ci->course->getName()?></td>
					<td><?=$ci->displayInstructors()?></td>					
					<td style="text-align:center;"><a href="javascript:openWindow('no_control=1&cmd=previewReservesList&ci=<?=$ci->getCourseInstanceID()?>','width=800,height=600');">preview</a></td>
				</tr> 
<?php		endforeach;	?>

			</table>
			<div style="padding:8px; border-top:1px solid #333333;">
				<img src="images/astx-green.gif" alt="**" width="15" height="15" /> = classes currently using this item
			</div>
		</div>
	</div>
<?php
	}
	
	
	/**
	 * @return void
	 * @param reserveItem object $item
	 * @desc Displays item-copyright edit screen
	 */
	function displayEditItemCopyright($item) {
		$copyright = new Copyright($item->getItemID());

		//get copyright library
		$home_lib_id = $item->getHomeLibraryID();
		if(!empty($home_lib_id)) {
			$home_lib = new library($home_lib_id);
			$copyright_lib = new library($home_lib->getCopyrightLibraryID());
			$copyright_lib_name = $copyright_lib->getLibrary();
		}
		else {
			$copyright_lib_name = 'n/a';
		}
		
		//get status basis
		$status_basis = $copyright->getStatusBasis();
		$status_basis = !empty($status_basis) ? $status_basis : 'n/a';
		
		//get contact name
		$contact = $copyright->getContact();
		$contact_org = $contact['org_name'];
?>
	<script language="JavaScript1.2" src="secure/javascript/liveSearch.js"></script>
	<script language="JavaScript1.2" src="secure/javascript/basicAJAX.js"></script>
	<script language="JavaScript1.2" src="secure/javascript/notes_ajax.js"></script>
	<script language="JavaScript1.2" src="secure/javascript/copyright_ajax.js"></script>
	
	<script language="JavaScript">
		function toggleDisplay(element_id, show) {
			if(document.getElementById(element_id)) {
				if(show) {				
					document.getElementById(element_id).style.display = '';
				}
				else {
					document.getElementById(element_id).style.display = 'none';
				}
			}
		}
	</script>
		
	<div id="copyright" class="displayArea">
		<div class="headingCell1">SUMMARY</div>
		<table width="100%" class="simpleList">
			<tr>
				<td width="150" class="labelCell1"><strong>Current Status:</strong></td>
				<td width="150" class="<?=$copyright->getStatus()?>"><?=$copyright->getStatus()?></td>
				<td width="150" class="labelCell1"><strong>Review Library:</strong></td>
				<td><?=$copyright_lib_name?></td>
			</tr>
			<tr>
				<td width="150" class="labelCell1"><strong>Reason:</strong></td>
				<td><?=$status_basis?></td>
				<td width="150" class="labelCell1"><strong>Copyright Contact:</strong></td>
				<td><?=$contact_org?></td>
			</tr>		
		</table>
<?php
		self::displayEditItemCopyrightStatus($item->getItemID());
		
		//only show the rest of the sections if the record exists
		$copyright_id = $copyright->getID();
		if(!empty($copyright_id)) {
			self::displayEditItemCopyrightContact($item->getItemID());
			self::displayEditItemCopyrightNotes($item->getItemID());
			self::displayEditItemCopyrightFiles($item->getItemID());
			self::displayEditItemCopyrightLog($item->getItemID());	
		}
?>	
	</div>
<?php	
	}
	
	
	/**
	 * @return void
	 * @desc Displays form to edit copyright status.
	 */
	function displayEditItemCopyrightStatus($item_id) {
		$copyright = new Copyright($item_id);
?>
		<div class="contentTabs" style="text-align:center; border-top:1px solid #333333;"><a href="#" onclick="javascript: toggleDisplay('copyright_edit_status', 1); return false;">STATUS</a></div>
		<div id="copyright_edit_status" style="border-top:1px solid #333333; padding:10px; display:none;">		
			<?php copyrightDisplayer::displayCopyrightEditStatus($item_id, $copyright->getStatus(), $copyright->getStatusBasisID()); ?>	
			<input type="button" name="cancel" value="Close" onclick="javascript: toggleDisplay('copyright_edit_status', 0);" />
		</form>
		</div>
<?php
	}
	
	
	/**
	 * @return void
	 * @desc Displays form to edit copyright status.
	 */
	function displayEditItemCopyrightContact($item_id) {
?>		
		<div class="contentTabs" style="text-align:center; border-top:1px solid #333333;"><a href="#" onclick="javascript: toggleDisplay('copyright_contact', 1); return false;">CONTACT</a></div>
		<div id="copyright_contact" style="border-top:1px solid #333333; padding:10px; display:none;">
			<?php copyrightDisplayer::displayCopyrightContactsBlockAJAX($item_id, true, true); ?>
			<input type="button" name="cancel" value="Close" onclick="javascript: toggleDisplay('copyright_contact', 0);" />
		</div>
<?php
	}
	
	
	/**
	 * @return void
	 * @param int $item_id ID of item
	 * @desc Displays copyright notes.
	 */
	function displayEditItemCopyrightNotes($item_id) {
		//fetch notes
		$notes = noteManager::fetchNotesForObj('copyright', $item_id);
?>
		<div class="contentTabs" style="text-align:center; border-top:1px solid #333333;"><a href="#" onclick="javascript: toggleDisplay('copyright_notes', 1); return false;">NOTES</a></div>
		<div id="copyright_notes" style="border-top:1px solid #333333; padding:10px; display:none;">
			<?php self::displayNotesBlockAJAX($notes, 'copyright', $item_id, true); ?>
			<input type="button" name="cancel" value="Close" onclick="javascript: toggleDisplay('copyright_notes', 0);" />
		</div>
<?php
	}
	
	
	/**
	 * @return void
	 * @param int $item_id ID of item
	 * @desc Displays copyright files.
	 */
	function displayEditItemCopyrightFiles($item_id) {
		
?>
		<div class="contentTabs" style="text-align:center; border-top:1px solid #333333;"><a href="#" onclick="javascript: toggleDisplay('copyright_files', 1); return false;">SUPPORTING FILES</a></div>
		<div id="copyright_files" style="border-top:1px solid #333333; padding:10px; display:none;">
		
<span style="color:red">DELETE ACTUAL FILE ON DELETE?</span>
<br /><br />

			<?php copyrightDisplayer::displayCopyrightSupportingFile($item_id); ?>
			<input type="button" name="cancel" value="Close" onclick="javascript: toggleDisplay('copyright_files', 0);" />
		</div>
<?php
	}
	

	/**
	 * @return void
	 * @desc Displays copyright log.
	 */
	function displayEditItemCopyrightLog($item_id) {
?>
		<div class="contentTabs" style="text-align:center; border-top:1px solid #333333;"><a href="#" onclick="javascript: toggleDisplay('copyright_log', 1); return false;">LOG</a></div>
		<div id="copyright_log" style="display:none;">
			<?php copyrightDisplayer::displayCopyrightLogs($item_id); ?>
			<input type="button" name="cancel" value="Close" onclick="javascript: toggleDisplay('copyright_log', 0);" />
		</div>
<?php
	}
	
	
	/**
	 * @return void
	 * @param reserveItem $item reserveItem object
	 * @param reserve $reserve (optional) reserve object
	 * @param array $dub_array (optional) array of information pertaining to duplicating an item. currently 'dubReserve' flag and 'selected_instr'
	 * @desc Displays form for editing item information (optionally: reserve information)
	 */
	function displayEditItem($item, $reserve=null, $dub_array=null) {
		global $u, $g_permission;						
		
		//determine if editing a reserve
		if(!empty($reserve) && ($reserve instanceof reserve)) {
			$edit_reserve = true;
			$edit_item_href = 'reserveID='.$reserve->getReserveID();			
		}
		else {
			$edit_reserve = false;
			$edit_item_href = 'itemID='.$item->getItemID();
		}
		
		//style the tab
		$tab_styles = array('meta'=>'', 'history'=>'', 'copyright'=>'');
		switch($_REQUEST['tab']) {
			case 'history':
				$tab_styles['history'] = 'class="current"';
			break;

#########################################
#	HIDE COPYRIGHT UNTIL FURTHER NOTICE #			
#########################################	
#			case 'copyright':
#				$tab_styles['copyright'] = 'class="current"';
#			break;
#########################################
			
			default:
				$tab_styles['meta'] = 'class="current"';
			break;
		}
		
#########################################
#	HIDE COPYRIGHT UNTIL FURTHER NOTICE #			
#########################################			
#		//check for a pending copyright-review
#		$copyright = new Copyright($item->getItemID());
#		$copyright_alert = '';
#		if(($copyright->getStatus() != 'APPROVED') && ($copyright->getStatus() != 'DENIED')) {
#			$copyright_alert = '<span class="alert">! pending review !</span>';
#		}
#########################################
?>
		<div id="alertMsg" align="center" class="failedText"></div>
        <p />  
        
<?php	if($edit_reserve): ?>
		<div style="text-align:right; font-weight:bold;"><a href="index.php?cmd=editClass&amp;ci=<?=$reserve->getCourseInstanceID()?>">Return to Class</a></div>
<?php	else: ?>
		<div style="text-align:right; font-weight:bold;"><a href="index.php?cmd=doSearch&amp;search=<?=urlencode($_REQUEST['search'])?>">Return to Search Results</a></div>
<?php	endif; ?>

		<div class="contentTabs">
			<ul>
				<li <?=$tab_styles['meta']?>><a href="index.php?cmd=editItem&<?=$edit_item_href?>&search=<?=urlencode($_REQUEST['search'])?>">Item Info</a></li>
<?php		if($u->getRole() >= $g_permission['staff']): ?>
				<li <?=$tab_styles['history']?>><a href="index.php?cmd=editItem&<?=$edit_item_href?>&tab=history&search=<?=urlencode($_REQUEST['search'])?>">History</a></li>
<?php
#########################################
#	HIDE COPYRIGHT UNTIL FURTHER NOTICE #
#	(remove spaces b/n <, >, and ?)		#
#########################################

#				<li < ?=$tab_styles['copyright']? >><a href="index.php?cmd=editItem&amp;< ?=$edit_item_href? >&amp;tab=copyright">Copyright < ?=$copyright_alert? ></a></li>
#########################################
?>

<?php		endif; ?>
			</ul>
		</div>
		<div class="clear"></div>
		
<?php
		//switch screens
		//only allow non-default tab for staff and better
		$tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : null;
		$tab = ($u->getRole() >= $g_permission['staff']) ? $tab : null;				
		switch($tab) {
			case 'history':
				self::displayItemHistory($item);
			break;
			
#########################################
#	HIDE COPYRIGHT UNTIL FURTHER NOTICE #			
#########################################			
#			case 'copyright':
#				self::displayEditItemCopyright($item);
#			break;
#########################################
			
			default:
				if ($reserve instanceof Reserve)
				{
					$status = $reserve->status;
				} elseif ($item instanceof reserveItem ) {
					$status = $item->status;
				} else {
					$status = 'DENIED';
				}
				
				if ($status != 'DENIED' || $u->getRole() >= $g_permission['staff'])
				{
					self::displayEditItemMeta($item, $reserve, $dub_array);
				} else {
					echo "Access to this item has be denied.  Please contact your reserves desk for assistance.";
				}
			break;
		}
?>
<?php
	}

	
	function displayEditHeadingScreen($ci, $heading)
	{
		$heading->getItem();
		
		if ($heading->getSortOrder() == 0 || $heading->getSortOrder() == null)
			$currentSortOrder = "Not Yet Specified";
		else
			$currentSortOrder = $heading->getSortOrder();
?>
		<form action="index.php" method="post" name="editHeading">
		
			<input type="hidden" name="cmd" value="processHeading">
			<input type="hidden" name="nextAction" value="editClass">
			<input type="hidden" name="ci" value="<?=$ci?>">
			<input type="hidden" name="headingID" value="<?=$heading->itemID?>">
			
		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
			<tr>
				<td colspan="2" align="right"><strong><a href="index.php?cmd=editClass&ci=<?=$ci?>">Cancel and return to class</a></strong></td>	
			</tr>
			<tr>
				<td colspan="2">
					<div class="helperText" style="align:left; padding:8px 0 20px 0; margin-right:180px;">
					Headings help organize your list of materials into topics or weeks. Headings can stand alone, 
					or you can add items to them. To add an item to a heading (like you would to a folder), go to the Edit Class
					screen, check the items to add to the heading, and scroll to the bottom of your list of materials.
					Select which heading to add the materials to and click the "Submit" button.
					</div>
				</td>
			</tr>
			<tr>
				<td class="headingCell1" width="25%" align="center">HEADING DETAILS</td>
				<td width="75%" align="center">&nbsp;</td>
			</tr>
		    <tr>
		    	<td colspan="2" class="borders">
			    	<table width="100%" border="0" cellspacing="0" cellpadding="5">
			    		<tr>
			    			<td width="30%" bgcolor="#CCCCCC" align="right" class="strong">
			    				<font color="#FF0000">*</font>&nbsp;Heading Title:
			    			</td>
			    			<td>
			    				<input name="heading" type="text" size="60" value="<?=$heading->item->getTitle()?>" />
			    			</td>
			    		</tr>
			    		<tr>
			    			<td bgcolor="#CCCCCC" align="right" class="strong">
			    				Current Sort Position:
			    			</td>
	        				<td>
	        					<?=$currentSortOrder?>
	        				</td>			    		
			    		</tr>
<?php
		//notes - only deal with notes if editing a heading (as opposed to creating)
		if($heading->getReserveID()):		
			//display edit notes
			self::displayEditNotes($heading->getNotes(true), 'headingID='.$heading->getReserveID().'&amp;ci='.$ci);
			
			//display "Add Note" button
?>
						<tr>
							<td colspan="2" bgcolor="#CCCCCC" align="center" class="borders" style="border-left:0px; border-bottom:0px; border-right:0px;">
								<?php self::displayAddNoteButton('reserveID='.$heading->getReserveID()); ?>
							</td>
						</tr>
<?php	endif; ?>

					</table>
        		</td>
      		</tr>
      		<tr>
      			<td colspan="2" align="center">
      				<br />
      				<input type="submit" name="submit" value="Save Heading" />
				</td>
      		</tr>
    	</table>
    	</form>
<?php
  
	}

	
	/**
	* @return void
	* @param int $ci_id courseInstance ID
	* @param string $search_serial serialized search _request
	* @desc Displays editItem/editReserve success screen
	*/	
	function displayItemSuccessScreen($ci_id=null, $search_serial=null)	{		
?>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
			<tr>
		    	<td width="140%"><img src="/images/spacer.gif" width="1" height="5"> </td>
		    </tr>
		    <tr>
		        <td align="left" valign="top" class="borders">
					<table width="50%" border="0" align="center" cellpadding="0" cellspacing="5">
		            	<tr>
		                	<td><strong>Your item has been updated successfully.</strong></td>
		                </tr>
		                <tr>
		                	<td align="left" valign="top">
		                		<ul>		                		
<?php	if($ci_id): ?>
					<li><a href="index.php?cmd=editClass&amp;ci=<?=$ci_id?>">Return to Class</a></li>
<?php	elseif($search_serial): ?>
					<li><a href="index.php?cmd=doSearch&amp;search=<?=$search_serial?>">Return to Search Results</a></li>					
<?php	endif; ?>
		                			<li><a href="index.php">Return to MyCourses</a><br></li>
		                		</ul>
		                	</td>
		                </tr>
		            </table>
				</td>
			</tr>
		</table>
<?php
	}
	
	/**
	* @return void
	* @param int $ci_id courseInstance ID
	* @desc Displays editHeading success screen
	*/	
	function displayHeadingSuccessScreen($ci_id=null)	{		
?>
		<div class="borders" style="text-align:middle;">
			<div style="width:50%; margin:auto;">
				<strong>Your heading has been added/updated successfully.</strong>
				<br />
				<ul>
	    			<li><a href="index.php?cmd=editClass&amp;ci=<?=$ci_id?>">Return to class</a></li>
	    			<li><a href="index.php?cmd=editHeading&amp;ci=<?=$ci_id?>">Create another heading</a></a></li>
	    			<li><a href="index.php?cmd=customSort&amp;ci=<?=$ci_id?>">Change heading sort position</a></li>
	    		</ul>
			</div>
		</div>
<?php
	}
	
}
?>
