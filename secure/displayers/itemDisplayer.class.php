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
   * Displays the edit-item-source block - upload file or set url
   * @param reserveItem object $reserveItem
   */
  function displayEditItemSource($reserveItem) {
    global $u, $g_permission;


      print '<div class="headingCell1">ITEM SOURCE</div>';
      // FIXME: why all static calls?
      
        //editing an electronic item - show URL/upload fields
        if($reserveItem->getItemGroup() == 'ELECTRONIC') {
    if($reserveItem->itemID) {  // editing an existing digital item 
      print itemDisplayer::_itemsource_existingElectronic($reserveItem);
    } else {    // adding a new item
      print itemDisplayer::_itemsource_newElectronic($reserveItem);
    }
        } elseif ($reserveItem->isPhysicalItem()) {
    if($reserveItem->itemID) {  // editing an existing physical item
      //editing a physical item - show library, etc.
      //only allow staff or better to edit this info
      print itemDisplayer::_itemsource_existingPhysical($reserveItem);
    } else {      // adding a new physical item
      print itemDisplayer::_itemsource_addPhysical($reserveItem);
    }
  }

  } //displayEditItemSource()


  /**
   * item source section of edit form for an existing electronic item
   * upload new file or set new URL
   * @param reserveItem $reserveItem
   * @return string html contents for item source display
   */
  function _itemsource_existingElectronic($reserveItem) {
    global $u, $g_permission;
    $output = <<<ITEM_SOURCE
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
ITEM_SOURCE;
    $output .= 'Current URL <small>[<a href="reservesViewer.php?item=' .
            $reserveItem->getItemID() . '" target="_blank">Preview</a>]</small>:';
    
    if ($reserveItem->isLocalFile()){ //local file
            $output .= "    Local File";
      if ($u->getRole() >= $g_permission['staff']) { //only show local path to staff or greater 
        $output .= "&ndash; <em>" . $reserveItem->getURL() . "</em>";
      }
    } else { //remote file - show link to everyone 
      $output .= "<em>" . $reserveItem->getURL() . "</em>";
    }   // end local file
    
    $output .= <<<ITEM_SOURCE
      </div>
      <small>Please note that items stored on the ReservesDirect server are access-restricted; use the Preview link to view the item.<br />
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
ITEM_SOURCE;

    if ($u->getRole() >= $g_permission['staff']) { //only show status to staff or greater 
            $status = $reserveItem->getStatus();
            $status = " checked='CHECKED' ";
            // FIXME: where is this used?
    }
    
    $output .= "
  </div>";
    
    return $output;
  }

  /**
   * item source section of edit form for a new electronic item
   * upload file or set URL
   * @param reserveItem $reserveItem
   * @return string html contents for item source display
   */
  function _itemsource_newElectronic($reserveItem) {
    $output = <<<ITEM_SOURCE
    <script type="text/javascript" src="secure/javascript/openurl.js"></script>
    <script language="JavaScript">
      function testurl(mypage) {
        var alertMsg = "";  
        if (mypage) { openWin(mypage,640,480); }
        else {
          alertMsg = 'Please enter a URL in the "Add a link" text box if you would like to "Test URL".';
        }
        document.getElementById('alertMsg').innerHTML = alertMsg;        
      }    
      function getopenurl(frm) {      
        var alertMsg = "";  
        alertMsg = get_url(frm);
        //document.getElementById('alertMsg').innerHTML = alertMsg;      
        return alertMsg;       
      } 
      function editurl() { 
        openWin('http://ejournals.emory.edu/openurlgen.php',860,550);
        //window.open('http://ejournals.emory.edu/openurlgen.php','Open URL Generator','width=400,height=350,resizable=yes');   
      }   
      function setAddUrlFocus(url, userFile, d0, d1) {  
        url.disabled = false;
        userFile.disabled = true;
        d0.checked = false;
        d1.checked = true;  
      }                        
      </script>

    <div id="openurl_link" style="display:none" class="borders noticeBox">
      <div class="noticeImg"></div>
      <div class="noticeText">
        Instead of uploading a journal article, consider using the
        "Get URL" button below to locate a link instead.<br/>         
      </div>
    </div>
    <table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#CCCCCC" class="borders">
      <tr>
        <td align="left" colspan="2" valign="top"> <p class="strong"><br>Would you like to:</p></td>
      </tr>
      <tr>
        <td align="left" valign="top">
          <font color="#FF0000"><strong>*</strong></font><input type="radio" name="documentType" value="DOCUMENT" checked onClick="this.form.userFile.disabled = !this.checked; this.form.url.disabled = !this.checked;">&nbsp;<span class="strong">Upload a document &gt;&gt;</span>
        </td>
        <td align="left" valign="top">
          <input type="file" name="userFile" size="40"> <br/>
          <i>Please limit uploaded documents to 25 clear, clean sheets to minimize downloading and printing time.</i>
        </td>
      </tr>
      <tr>
        <td align="left" valign="top">
          <font color="#FF0000"><strong>*</strong></font><input type="radio" name="documentType" value="URL" onClick="this.form.url.disabled = !this.checked; this.form.userFile.disabled = this.checked;this.form.iconImg.src = 'images/doc_type_icons/doctype-link.gif';this.form.iconImg.src = 'images/doc_type_icons/doctype-link.gif';this.form.selectedDocIcon.selectedIndex = 7;">
          <span class="strong">Add a link &gt;&gt;</span>
        </td>
        <td align="left" valign="top">
          <input name="url" type="text" size="50" DISABLED>
          <br>
          <table>
            <tr>
              <td><input type="button" name="test_url" value="Preview URL" onclick="testurl(this.form.url.value);" /></td>
              <td>Open a window to test if the URL link above is valid.</td>
            </tr>
            <tr>
              <td><input type="button" name="get_url" value="Get URL" onclick="setAddUrlFocus(this.form.url,this.form.userFile,this.form.documentType[0],this.form.documentType[1]);this.form.url.value = getopenurl(this.form);" /></td>
              <td>Use the metadata from the ITEM DETAILS section above to locate an open url.</td>
            </tr>   
            <tr>
              <td><input type="button" name="get_url" value="Edit URL Details" onclick="editurl();" /></td>
              <td>Launch the openurl generator for more options.</td>
            </tr>                            
          </table>
        </td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td><font size='-2'>
             http://www.reservesdirect.org<br/>
             http://links.jstor.org/xxxxx<br/>
             http://dx.doi.org/10.xxxxx
          </font>
        </td>
      </tr>
    </table>                
ITEM_SOURCE;
    return $output;
  }  
  
  /**
   * item source section of edit form for an existing physical item
   * @param reserveItem $reserveItem
   * @return string html contents for item source display
   */
  function _itemsource_existingPhysical($reserveItem) {
    global $u, $g_permission;
    $output = <<<ITEM_SOURCE
    <div id="item_source" style="padding:8px 8px 12px 8px;">
        <table border="0" cellpadding="2" cellspacing="0">  
ITEM_SOURCE;
    
    if ($u->getRole() >= $g_permission['staff']) {
    $output .= <<<ITEM_SOURCE
          <tr>
            <td align="right">
              Reserve Desk:
            </td>
            <td>
              <select name="home_library">
ITEM_SOURCE;
      foreach($u->getLibraries() as $lib) {
        $selected_lib = ($reserveItem->getHomeLibraryID() == $lib->getLibraryID()) ? 'selected="selected"' : '';
        $output .= "\n\t<option value='". $lib->getLibraryID() . "'" . $selected_lib . ">" .
        $lib->getLibrary() ."</option>";
      }
      
      $output .= <<<ITEM_SOURCE
      </select>
            </td>
          </tr>
ITEM_SOURCE;
    }   // end staff section
    
    //display details from the physical copy table (barcode/call num)
    if ($reserveItem->getPhysicalCopy()) {
      $barcode = $reserveItem->physicalCopy->getBarcode();
      $call_num = $reserveItem->physicalCopy->getCallNumber();
      $output .= <<<ITEM_SOURCE
          <tr>    
          <td align="right">
            <font color="#FF0000">*</font>&nbsp;Barcode:
          </td>
          <td>
            <input name="barcode" type="text" id="barcode" size="30" value="$barcode"/>
          </td>
        </tr>
        <tr>    
          <td align="right">
            Call Number:
          </td>
          <td>
            <input name="call_num" type="text" id="call_num" size="30" value="$call_num" />
          </td>       
        </tr>
ITEM_SOURCE;
    }
    $output .= <<<ITEM_SOURCE
      </table>
    </div>
ITEM_SOURCE;
    return $output;
  }

  /**
   * item source section of edit form for a new physical item
   * @param reserveItem $reserveItem
   * @return string html contents for item source display
   */
  function _itemsource_addPhysical($reserveItem) {
    global $u, $g_permission;
    
    //deal with barcode prefills
    if(!empty($_REQUEST['searchField'])) {
      if($_REQUEST['searchField'] == 'barcode') {
        $barcode_select = ' selected = "selected"';
        $control_select = '';
        //assume that this index exists
        $barcode_value = $_REQUEST['searchTerm'];
      } else {
        $barcode_select = '';
        $control_select = ' selected = "selected"';
        // FIXME: what is this? how to handle?
        //  $barcode_value = (is_array($item_data['physicalCopy']) && !empty($item_data['physicalCopy'])) ? $item_data['physicalCopy'][0]['bar'] : '';
      }
      $search_term = $_REQUEST['searchTerm'];
    } else {
      $barcode_select = ' selected = "selected"';
      $control_select = '';
      $search_term = '';
      // FIXME: ?? 
      //      $barcode_value = (is_array($item_data['physicalCopy']) && !empty($item_data['physicalCopy'])) ? $item_data['physicalCopy'][0]['bar'] : '';
    }
    
    //deal with physical item source pre-select
    $addType_select = array('euclid'=>'', 'personal'=>'');
    if($reserveItem->isPhysicalItem()) {
      if(!empty($_REQUEST['addType']) && ($_REQUEST['addType']=='PERSONAL')) {
        $addType_select['personal'] = ' checked="true"';        
      }
      else {
        $addType_select['euclid'] = ' checked="true"';
      }     
    }
    
    $output = <<<ITEM_SOURCE
    <script type="text/javascript">
  
      //disables/enables ILS elements
      function toggleILS(enable) {
        var frm = document.getElementById('additem_form');
        var dspl;
        if(enable) {
          frm.searchTerm.disabled=false;
          frm.searchField.disabled=false;
          dspl = '';
        }
        else {
          frm.searchTerm.disabled=true;
          frm.searchField.disabled=true;        
          dspl = 'none';
        }
    
        document.getElementById('ils_search').style.display = dspl;
      }
    
      //shows/hides non-manual entry elements
      function toggleNonManual(show) {
        if(document.getElementById('nonman_local_control_row')) {
          if(show) {
            document.getElementById('nonman_local_control_row').style.display = '';
            document.getElementById('nonman_local_control_input').disabled = false;
          }
          else {
            document.getElementById('nonman_local_control_row').style.display = 'none';
            document.getElementById('nonman_local_control_input').disabled = true;
          }           
        }
        
        if(document.getElementById('man_local_control_row')) {
          if(show) {
            document.getElementById('man_local_control_row').style.display = 'none';
            document.getElementById('man_local_control_input').disabled = true;
          }
          else {
            document.getElementById('man_local_control_row').style.display = '';
            document.getElementById('man_local_control_input').disabled = false;
          }
        }
      }
    </script>
    
    <table width="100%" border="0" cellpadding="3" cellspacing="0" class="borders">
      <tr bgcolor="#CCCCCC">
        <td width="20%" align="left" valign="middle">
        <input name="addType" type="radio" value="EUCLID_ITEM" onClick="toggleILS(1); togglePersonal(0,0); toggleNonManual(1);"{$addType_select['euclid']}>
          <span class="strong">EUCLID Item</span>
        </td>
        <td width="40%" align="left" valign="top">
             <input type="radio" name="addType" value="PERSONAL" onclick="toggleILS(1); togglePersonal(1,1); toggleNonManual(1);"{$addType_select['personal']}>
          <span class="strong">Personal Copy (EUCLID Item Available)</span>
        </td>
        <td width="40%" align="left" valign="top">
          <input type="radio" name="addType" value="MANUAL"  onclick="toggleILS(0); togglePersonal(1, 0); toggleNonManual(0);">
          <span class="strong">Enter Item Manually (no EUCLID lookup)</span>
        </td>
      </tr>
      <tr bgcolor="#CCCCCC" id="ils_search">
        <td colspan="3" align="left" valign="middle" bgcolor="#FFFFFF">
          <input name="searchTerm" type="text" size="15" value="{$search_term}">
          <select name="searchField">
             <option value="barcode"{$barcode_select}>Barcode</option>
             <option value="control"{$control_select}>Control Number</option>
          </select>
          &nbsp;
          <input type="submit" value="Search" onclick="this.form.cmd.value='{$_REQUEST['cmd']}';" / >
        </td>
      </tr>
    </table>
ITEM_SOURCE;

      return $output;
  }


    
  
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
        $reserve_status_denied = '';         
        break;
      case 'INACTIVE':
        $reserve_status_active = '';
        $reserve_status_inactive = 'checked="CHECKED"';
        $reserve_status_denied_all = '';
        $reserve_block_vis = ' display:none;';
        $reserve_status_denied = '';        
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
      $parent_heading_id = 'root';  //this will pre-select the main list
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
<?php if($reserve->getStatus()=='DENIED ALL'): ?> 
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
  <?php if (($reserve->getStatus() != 'DENIED' && $reserve->getStatus() != 'DENIED ALL') || $u->getRole() >= $g_permission['staff']): ?>        
    <?php if(!in_array($reserve->getStatus(), array('ACTIVE', 'INACTIVE', 'DENIED', 'DENIED ALL'))): ?>
  
        <div>
          <strong>Current Status:</strong>&nbsp;<span class="inProcess"><?= $reserve->getStatus() ?></span>
          <br />
          Please contact your Reserves staff to inquire about the status of this reserve.
          <input type="hidden" name="reserve_status" value="IN PROCESS" />
        </div>
              
    <?php else: ?>
        <div style="float:left; width:30%;">
          <strong>Set Status:</strong>
          <br />

          <div style="margin-left:10px; padding:3px;">
            <input type="radio" name="reserve_status" id="reserve_status_active" value="ACTIVE" onChange="toggleDates();" <?=$reserve_status_active?> />&nbsp;<span class="active">ACTIVE</span>
            <input type="radio" name="reserve_status" id="reserve_status_inactive" value="INACTIVE" onChange="toggleDates();" <?=$reserve_status_inactive?> />&nbsp;<span class="inactive">INACTIVE</span>          
      <?php if ($u->getRole() >= $g_permission['staff']): ?>
            <br/><input type="radio" name="reserve_status" id="reserve_status_denied" value="DENIED" onChange="toggleDates();" <?=$reserve_status_denied?> />&nbsp;<span class="copyright_denied">DENY ACCESS FOR THIS CLASS ONLY</span>
                        <div style="overflow:auto;">
        <p>
                    <div class="strong">Item Status</div>
                    <div>
                        <input type="radio" name="item_status" value="ACTIVE"/> <span class="active">Activate for all Classes</span>
                        <br /> <input type="radio" name="item_status" value="DENIED"/> <span class="inactive">Deny use for all Classes</span>
                    </div>
        </p>
      </div>
      <?php   endif; ?>
          </div>
    <?php   endif; #if in process?>
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
  <?php endif; ?> 
<?php endif; ?>   
<?php
  }
  
  
  /**
   * Displays the edit-item-item-details block
   * @param reserveItem $item reserveItem object
   * @global user $u current user - used to get document type icons appropriate to user
   * @global array $g_permission
   * @global string $g_catalogName
   */ 
  function displayEditItemItemDetails($item) {
    global $u, $g_permission, $g_catalogName;

    // get appropriate material types for this item
    if ($item->isPhysicalItem()) {
      $materialTypes = common_getPhysicalMaterialTypes();
      $materialTypesDetails = common_physicalMaterialTypesDetails();
    } else {
      $materialTypes = common_getElectronicMaterialTypes();
      $materialTypesDetails = common_electronicMaterialTypesDetails();
    }

?>
    <script language="JavaScript" type="text/javascript">
    //<!--
    //shows/hides text field for 'other' material type
      function toggleOtherMaterialInput() {
        var material_type = document.getElementById('material_type');
        if (material_type.options[material_type.selectedIndex].value == "OTHER") {
          $('material_type_other_block').show();
        } else {
          $('material_type_other_block').hide();
        }
      }
      var materialType_details = <?= json_encode($materialTypesDetails) ?>;
    //-->
    </script>
<script type="text/javascript" src="secure/javascript/editItem.js"></script>        
        
    <div class="headingCell1">ITEM DETAILS</div>
    <div id="item_details" style="padding:8px 8px 12px 8px;">
      <table class="editItem" border="0" cellpadding="2" cellspacing="0">
        <tr class="required">
          <th>Type of Material:</th>
          <td>
              <select id="material_type" name="material_type" onChange="typeOfMaterial();setItemGroup(this.form.material_type.value,this.form.item_group);">
<?php       foreach($materialTypes as $material_id => $material): ?>
<?php           $selected = ($material_id == $item->getMaterialType()) ? ' selected="selected"' : ''; ?>
            <option value="<?= $material_id ?>"<?= $selected ?>><?= $material ?></option>
           <?php    endforeach ?>
           </select>
<?php if($item->isPhysicalItem()): ?>           
          <img name="iconImg" width="24" height="20" src="<?=$item->getItemIcon()?>" /> 
<?php endif; ?>
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
      <input name="volume_title" type="text" id="volume_title" size="50" value="<?=$item->getVolumeTitle()?>">
         </td>
           </tr>
           <tr id="edition">
         <th>Volume/Edition:</td>
         <td>
      <input name="volume_edition" type="text" id="volume_edition" size="50" value="<?=$item->getVolumeEdition()?>">
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
           <input name="times_pages" type="text" id="times_pages" size="50" value="<?=$item->getPagesTimes()?>">
         </td>
         <? if ($item->getItemGroup() == 'ELECTRONIC'): ?>
            <td><small>Example page range: 336-371; 381-388
            <br>Example time range: 2:10-5:30; 10:30-12:44</small></td>
         <? endif ?>
        </tr>
  
        <tr id="used_times_pages">
         <th>Total Used Pages/Times:</th>
         <td><input name="used_times_pages" type="text" size="50" value="<?= $item->getUsedPagesTimes() ?>"></td>
        </tr>
         
        <tr id="total_times_pages">
         <th>Total Pages/Times:</th>
         <td><input name="total_times_pages" type="text" size="50" value="<?= $item->getTotalPagesTimes() ?>"></td>
        </tr>
<?php if(!$item->isPhysicalItem()): ?>
       <tr>
         <th>Document Type Icon:</th>
         <td>
           <select name="selectedDocIcon" onChange="document.iconImg.src = this[this.selectedIndex].value;">
<?php
    foreach($u->getAllDocTypeIcons() as $icon):
      $selected = ($item->getItemIcon() == $icon['helper_app_icon']) ? ' selected="selected"' : '';
?>
         <? /* Set the default to the Item Icon */ ?>
         <option value="<?=$icon['helper_app_icon']?>"<?=$selected?>><?=$icon['helper_app_name']?></option> 
<?php endforeach; ?>
           </select>
           <img name="iconImg" width="24" height="20" src="<?=$item->getItemIcon()?>" />
         </td>
       </tr>
<?php endif; ?>       
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
                   <tr>
         <th>OCLC:</th>
         <td>
           <input type="text" size="15" maxlength="9"  value="<?= $item->getOCLC() ?>" name="OCLC" />
         </td>
       </tr>
         
       <tr id="availability">
          <th>Availability:</th>
          <td>
         <input type="radio" name="availability"
       <?= ($item->getAvailability() === 0) ? 'checked="checked"' : '' ?>  value="0">
         <span id="availability_option0">unavailable</span>
            <input type="radio" name="availability"
       <?= ($item->getAvailability() == null || $item->getAvailability() == 1) ? 'checked="checked"' : '' ?>  value="1">
         <span id="availability_option1">available</span>
           </td>
        </tr>

         
        <tr id="barcode" style="display:none">
          <th>
      <?php if($item->isPhysicalItem()): ?>
            <?= $g_catalogName ?> Control Number:
      <?php else: ?>
         Barcode / Alternate ID:
      <?php endif; ?>
          </th>
          <td>
             <input type="text" name="local_control_key" size="15" value="<?=$item->getLocalControlKey();?>" />
          </td>
        </tr>     
          
      </table>
    </div>
    
    <script language="JavaScript">
       // init form based on selected type of material
       typeOfMaterial();
       
       // Set the item_group based on type of material for physical items
       function setItemGroup(material_type,item_group) {
        switch(material_type)
        {
          case "BOOK": item_group.value = 'MONOGRAPH'; break;
          case "CD": 
          case "DVD": 
          case "VHS": 
          case "SOFTWARE": item_group.value = 'MULTIMEDIA'; break;    
        }         
       }      
    </script>
<?php
  }
  
  
  /**
   * Displays the edit-item-notes block
   * @param reserveItem $item reserveItem object
   * @param reserves $reserve (optional) reserve object
   */ 
  function displayEditItemNotes($item, $reserve=null) {
    global $u, $g_permission;
    
    //item notes
    $notes = $item->getNotes();
    //referrer obj for deleting notes
    $note_ref = 'itemID='.$item->getItemID();
    
    //reserve notes - only applies if we are editing a reserve (item instance linked to a course instance)
    if( !empty($reserve) && ($reserve instanceof reserve) ) { //we are editing reserve info
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
<?php endif; ?>

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
    if( !empty($reserve) && ($reserve instanceof reserve) ) { //we are editing reserve info
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
    $include_addnote_button = (($u->getRole() >= $g_permission['instructor']) || ($obj_type=='reserve')) ? true : false;
    
?>
      <script language="JavaScript1.2" src="secure/javascript/basicAJAX.js"></script>
      <script language="JavaScript1.2" src="secure/javascript/notes_ajax.js"></script>

      <div style="padding:8px 8px 12px 8px;text-align:center">
  
      <?php  self::displayNotesBlockAJAX($notes, $obj_type, $id, $include_addnote_button); ?>

      </div>
<?php
  }
  
  
  /**
   * Displays form for editing item information (optionally: reserve information)
   * @param reserveItem $item reserveItem object
   * @param reserves $reserve (optional) reserve object
   * @param array $dub_array (optional) array of information pertaining to duplicating an item. currently 'dubReserve' flag and 'selected_instr'
   * @global $u current user; used for permissions, document type icons
   * @global $g_permission
   * @global $g_copyrightNoticeURL url for copyright notice link   
   *
   * @see requestDisplayer::addItem - significant overlap/duplication with this function
   * @todo figure out a way to consolidate addItem and editItem logic?
   */
  function displayEditItemMeta($item, $reserve=null, $dub_array=null) {
    global $u, $g_permission, $g_copyrightNoticeURL, $g_notetype;
    
    //determine if editing a reserve
    if(!empty($reserve) && ($reserve instanceof reserve)) { //valid reserve obj
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
      function validateForm(frm) {      
        var alertMsg = "";

<?php if ($item->isPhysicalItem()): ?>
        //make sure this physical copy is supposed to have a barcode
        //  if it is, there will be an input element for it in the form
        if( (document.getElementById('barcode') != null) && (document.getElementById('barcode').value == '') ){ 
          alertMsg = alertMsg + "Barcode is required.<br />";
        }
<? elseif (!$edit_reserve): ?>          
        if((frm.userFile.value == "") && (frm.url.value == "")) {
          alertMsg = alertMsg + 'You must choose an item source of either "Upload a document" or "Add a link".<br />';
        }
<? endif ?>   
        alertMsg += checkMaterialTypes(frm);
        if (!alertMsg == "") { 
          document.getElementById('alertMsg').innerHTML = alertMsg;
          return false;
        } else {
          return true;
        }         
      }
    //-->
    </script>

<? /* NOTE: post to same command, to preserve info about action (editItem or addDigitalItem   */ ?>
        
     <form id="item_form" name="item_form" action="index.php?cmd=<?= $_REQUEST['cmd'] ?>" method="post"
        <? if (! $item->isPhysicalItem()): ?> enctype="multipart/form-data" <? endif ?> >
<?php if(! $item->isPhysicalItem()): ?>   
      <input type="hidden" name="item_group" value="ELECTRONIC" />  
      <input type="hidden" name="submit_edit_item_meta" value="submit" />
<?php else: ?>
      <input type="hidden" name="item_group" value="MONOGRAPH" /> 
      <input type="hidden" name="store_request" value="submit" />
<?php endif; ?>      
      
      <input type="hidden" name="itemID" value="<?=$item->getItemID()?>" />
      <?php self::displayHiddenFields($dub_array); //add duplication info as hidden fields ?> 
<?php if($edit_reserve): ?>
      <input type="hidden" name="reserveID" value="<?=$reserve->getReserveID()?>" />  
<?php endif; ?>

<?php if(!empty($_REQUEST['ci'])):
  /* if course id is set (e.g., adding new digital item from a class), pass on  */ ?>
      <input type="hidden" name="ci" value="<?= $_REQUEST['ci']?>" /> 
<?php endif; ?>

      <div id="item_meta" class="displayArea">    
<?php
    //show reserve details block
    if($edit_reserve) {
      self::displayEditItemReserveDetails($reserve);
    }
    
    if ($item->isPhysicalItem()) {  // For physical items show the item source on top.
      self::displayEditItemSource($item);       //show item source
      self::displayEditItemItemDetails($item);  //show item details   
    }
    else { // For electronic items show the item details on top.
      self::displayEditItemItemDetails($item);  //show item details  
      self::displayEditItemSource($item);       //show item source
    }
?>
    </form>  
    
    <div class="headingCell1">NOTES</div>        
    <br />
    <?php if(isset($reserve)): //if editing existing item, use AJAX notes handler ?>    
      <?php  self::displayEditItemNotesAJAX($item, $reserve);  ?>
    <?php else: //just display plain note form ?>

      <strong>Add a new note:</strong>
      <br />
      <textarea name="new_note" cols="50" rows="3"></textarea>
      <br />
      <?php if ($u->getRole() >= $g_permission['staff']): // role staff level or above ?>       
        <small>Note Type:
        <label><input type="radio" name="new_note_type" value="<?=$g_notetype['instructor']?>">Instructor</label>
        <label><input type="radio" name="new_note_type" value="<?=$g_notetype['content']?>" checked="true">Content Note</label>
        <label><input type="radio" name="new_note_type" value="<?=$g_notetype['staff']?>">Staff Note</label>
        <label><input type="radio" name="new_note_type" value="<?=$g_notetype['copyright']?>">Copyright Note</label>  
      <?php endif; ?>  
      <br />       
    <?php endif; ?>  

    </div>

<div style="font:arial; font-weight:bold; font-size:small; padding:5px;text-align:center;">I have read the Library's <a href="<?= $g_copyrightNoticeURL ?>" target="blank">copyright notice</a> and certify that to the best of my knowledge my use of this document falls within those guidelines.</div>


    <font color="#FF0000"><img src="images/required.png" alt="*"/></font>       
    <span class="helperText">= required fields</span>
    <p />
    <div style="padding:10px; text-align:center;">
        <input type="submit" name="submit_edit_item_meta" value="Save Changes" onClick="return validateForm(this.form);">
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
    if($item->isPhysicalItem()) { //physical
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
    else {  //electronic
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
        $ci->getPrimaryCourse();  //fetch the course object
        $ci->getInstructors();  //get a list of instructors
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
<?php   endforeach; ?>

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
   * @param array $errors (optional) array of error messages, e.g. missing required fields
   * @desc Displays form for editing item information (optionally: reserve information)
   */
  function displayEditItem($item, $reserve=null, $dub_array=null, $errors = null) {
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
    if(!isset($_REQUEST['tab'])) $_REQUEST['tab'] = '';
    switch($_REQUEST['tab']) {
      case 'history':
        $tab_styles['history'] = 'class="current"';
      break;

#########################################
# HIDE COPYRIGHT UNTIL FURTHER NOTICE #     
######################################### 
#     case 'copyright':
#       $tab_styles['copyright'] = 'class="current"';
#     break;
#########################################
      
      default:
        $tab_styles['meta'] = 'class="current"';
      break;
    }
    
#########################################
# HIDE COPYRIGHT UNTIL FURTHER NOTICE #     
#########################################     
#   //check for a pending copyright-review
#   $copyright = new Copyright($item->getItemID());
#   $copyright_alert = '';
#   if(($copyright->getStatus() != 'APPROVED') && ($copyright->getStatus() != 'DENIED')) {
#     $copyright_alert = '<span class="alert">! pending review !</span>';
#   }
#########################################
?>
    <div id="alertMsg" align="center" class="failedText">
    <? /* FIXME: What do we need to check for to alert user? */ ?>
    </div>
        <p />  
        
<?php if($edit_reserve): ?>
    <div style="text-align:right; font-weight:bold;"><a href="index.php?cmd=editClass&amp;ci=<?=$reserve->getCourseInstanceID()?>">Return to Class</a></div>
<?php elseif (!empty($_REQUEST['search'])): ?>
    <div style="text-align:right; font-weight:bold;"><a href="index.php?cmd=doSearch&amp;search=<?=urlencode($_REQUEST['search'])?>">Return to Search Results</a></div>

<?php endif; ?>

       
<?php  $search_param = (!empty($_REQUEST['search'])) ? '&search=' . urlencode($_REQUEST['search']) : ''; ?>

    <div class="contentTabs">
      <ul>
        <li <?=$tab_styles['meta']?>><a href="index.php?cmd=editItem&<?=$edit_item_href?><?= $search_param ?>">Item Info</a></li>
<?php   if(($u->getRole() >= $g_permission['staff']) && $edit_reserve): ?>
        <li <?=$tab_styles['history']?>><a href="index.php?cmd=editItem&<?=$edit_item_href?>&tab=history<?= $search_param ?>">History</a></li>
<?php
#########################################
# HIDE COPYRIGHT UNTIL FURTHER NOTICE #
# (remove spaces b/n <, >, and ?)   #
#########################################

#       <li < ?=$tab_styles['copyright']? >><a href="index.php?cmd=editItem&amp;< ?=$edit_item_href? >&amp;tab=copyright">Copyright < ?=$copyright_alert? ></a></li>
#########################################
?>

<?php   endif; ?>
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
# HIDE COPYRIGHT UNTIL FURTHER NOTICE #     
#########################################     
#     case 'copyright':
#       self::displayEditItemCopyright($item);
#     break;
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
<?php endif; ?>

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
  function displayItemSuccessScreen($ci_id=null, $search_serial=null) {   
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
<?php if($ci_id): ?>
          <li><a href="index.php?cmd=editClass&amp;ci=<?=$ci_id?>">Return to Class</a></li>
<?php elseif($search_serial): ?>
          <li><a href="index.php?cmd=doSearch&amp;search=<?=$search_serial?>">Return to Search Results</a></li>         
<?php endif; ?>
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
  function displayHeadingSuccessScreen($ci_id=null) {   
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
