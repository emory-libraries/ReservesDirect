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

require_once('secure/displayers/noteDisplayer.class.php');
require_once('secure/managers/ajaxManager.class.php');

class itemDisplayer extends noteDisplayer {
  
  /**
   * Displays the edit-item-source block - upload file or set url
   * @param reserveItem object $reserveItem
   */
  function displayEditItemSource($reserveItem) {
    global $u, $g_permission, $ajax_browser;


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
    if($_REQUEST['cmd'] == 'editItem') {  // editing an existing physical item
    //if($reserveItem->itemID) {  // editing an existing digital item     
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
    <script type="text/javascript" src="secure/javascript/openurl.js"></script>    
        
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
ITEM_SOURCE;

    $output .= '<div id="item_source_link" style="display:none;">
          <input id="url" name="url" type="text" size="50" value="' . $reserveItem->getURL() .'"/>
          <input type="button" onclick="openNewWindow(this.form.url.value, 500);" value="Preview" />
          <input type="button" id="geturl" value="Get URL" /> 
          <input type="button" id="editurl" value="Edit URL" /> 
        </div>
      </div>';

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

    <div id="openurl_link" style="display:none" class="borders noticeBox">
      <div class="noticeImg"></div>
      <div class="noticeText">
        Instead of uploading a journal article, please use the "Get URL" button below to locate a link to the article, as not all of the Journal license agreements allow for the uploading of pdfs.<br/>         
      </div>
    </div>
    <table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#CCCCCC" class="borders">
      <tr>
        <td align="left" colspan="2" valign="top"> <p class="strong"><br>Would you like to:</p></td>
      </tr>
      <tr>
        <td align="left" valign="top">
          <font color="#FF0000"><strong>*</strong></font><input type="radio" name="documentType" id="radioDOC" value="DOCUMENT" checked onClick="this.form.userFile.disabled = !this.checked; this.form.url.disabled = this.checked;this.form.selectedDocIcon.value = 'images/doc_type_icons/doctype-pdf.gif';this.form.iconImg.src = 'images/doc_type_icons/doctype-pdf.gif';">&nbsp;<span class="strong">Upload a document &gt;&gt;</span>
        </td>
        <td align="left" valign="top">
          <input type="file" id="userFile" name="userFile" size="40"> <br/>
          <i>Please limit uploaded documents to 25 clear, clean sheets to minimize downloading and printing time.</i>
        </td>
      </tr>
      <tr>
        <td align="left" valign="top">
          <font color="#FF0000"><strong>*</strong></font><input type="radio" name="documentType" id="radioURL" value="URL" onClick="this.form.url.disabled = !this.checked; this.form.userFile.disabled = this.checked;this.form.selectedDocIcon.value = 'images/doc_type_icons/doctype-link.gif';this.form.iconImg.src = 'images/doc_type_icons/doctype-link.gif';">
          <span class="strong">Add a link &gt;&gt;</span>
        </td>
        <td align="left" valign="top">
          <input id="url" name="url" type="text" size="50" DISABLED>
          <br>
          <table>
            <tr>
              <td><input type="button" id="get_url" value="Get URL" /></td>
              <td>Use the metadata from the ITEM DETAILS section above to locate an open url.</td>
            </tr>          
            <tr>
              <td><input type="button" id="preview_url" value="Preview URL" /></td>
              <td>Open a window to test if the URL link above is valid.</td>
            </tr>   
            <tr>
              <td><input type="button" id="edit_url" value="Edit URL Details" /></td>
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
    }    // end staff section
    
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
            <input name="barcode" type="text" id="barcode" size="30" value="$barcode" />
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
        
    $barcode = isset($_REQUEST['barcode']) ? $_REQUEST['barcode'] : "";
  
    $output = <<<ITEM_SOURCE
     
<table border="0" cellpadding="2" cellspacing="0" >   
      <tr bgcolor="#CCCCCC" id="ils_search">
          <tr>    
          <th align="right" width="220">
            Barcode:<font color="#FF0000">*</font>&nbsp;
          </th>
          <td  valign="middle">
            <input name="barcode" type="text" id="barcode" size="30" value="$barcode" />
          </td>
        <td valign="middle">        
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
      </div>      
      <?php   endif; ?>
          </div>
    <?php   endif; #if in process?>

              
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
   * @global string $g_copyright_limit
   * @global string $g_copyright_notice
   */ 
  function displayEditItemItemDetails($item, $copyrightStatusDisplay=0, $ciid=null) {
    global $u, $g_permission, $g_catalogName, $g_copyrightNotice, $g_copyrightLimit;    

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
    <script type="text/javascript" src="secure/javascript/domFunction.js"></script>
    <script language="JavaScript">
      var unobtrusive = new domFunction(unobtrusive, { 'Footer' : 'id'});                           
    </script>
        
    <div class="headingCell1">ITEM DETAILS</div>
    <div id="item_details" style="padding:8px 8px 12px 8px;">
      <table class="editItem" border="0" cellpadding="2" cellspacing="0">
        <tr class="required">
          <th>Type of Material:</th>
          <td>
              <select id="material_type" name="material_type" onChange="typeOfMaterial();materialTypeEvents();">
<?php       foreach($materialTypes as $material_id => $material): ?>
<?php           $selected = ($material_id == $item->getMaterialType()) ? ' selected="selected"' : ''; ?>
            <option value="<?= $material_id ?>"<?= $selected ?>><?= $material ?></option>
           <?php    endforeach ?>
           </select>
<?php if($item->isPhysicalItem()): ?>           
          <img id="iconImg" name="iconImg" width="24" height="20" src="<?=$item->getItemIcon()?>" /> 
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
        
       <tr id="isbn">
         <th>ISBN:</th>
         <td><input id="itemisbn" type="text" size="15" maxlength="13" value="<?= $item->getISBN() ?>" name="ISBN" /></td>
          <? if ($item->getItemGroup() == 'ELECTRONIC'): ?>
            <td>If you do not know the ISBN of your book, please search for it in <a target="_blank" href="http://www.booksinprint.com/bip/">Books In Print
            </a></td>
         <? endif ?>
       </tr>        
        
        <tr id="times_pages">
         <th>Pages/Time:</th>
         <td>
           <input id="timespagesrange" name="times_pages" type="text" size="50" value="<?=$item->getPagesTimes()?>" >
         </td>
         <? if ($item->getItemGroup() == 'ELECTRONIC'): ?>
            <td><small>Example page range: 336-371; 381-388
            <br>Example time range: 2:10-5:30; 10:30-12:44</small></td>
         <? endif ?>
        </tr>
  
        <tr id="used_times_pages">
         <th>Total Used Pages/Times:</th>
         <td><input id="timespagesused"  name="used_times_pages" type="text" size="50" value="<?= $item->getUsedPagesTimes() ?>" ></td>
        </tr>
         
        <tr id="total_times_pages">
         <th>Total Pages/Times:</th>
         <td><input id="timespagestotal" name="total_times_pages" type="text" size="50" value="<?= $item->getTotalPagesTimes() ?>" ></td>        
        </tr>
        
        <tr id="percent_times_pages">
         <th>Overall Book Usage:</th>
         <td>
<?php if(!empty($_REQUEST['ci'])): ?>
        <input id="percenttimespages" name="percent_times_pages" value="<?= $item->getOverallBookUsage($_REQUEST['ci'], true) ?>" readonly /> %     
<?php else: ?>
        <input id="percenttimespages" name="percent_times_pages" value="<?= $item->getOverallBookUsage($ciid, false) ?>" readonly /> %
<?php endif; ?>         
         <input type="hidden" id="copyright_limit" value="<?= $g_copyrightLimit ?>" />         
         <input type="hidden" id="copyright_notice" value="<?= $g_copyrightNotice ?>" />
         </td>
         <? if ($item->getItemGroup() == 'ELECTRONIC'): ?>
            <td><label id='percentmsg' /><small>This field displays the total % of book uploaded to this course.</small></td>
         <? endif ?>          
        </tr>  
              
<?php if(!$item->isPhysicalItem()): ?>
        <? /* Display copyright status for this reserve, if appropriate. (id="copyrightstatus") */ ?>
        <? /* Dropdown menu sample output available in testGetCopyrightStatusDisplay */ ?> 
        <?=$copyrightStatusDisplay?>

       <tr>
         <th>Document Type Icon:</th>
         <td>
           <select id="selectedDocIcon" name="selectedDocIcon" onChange="document.iconImg.src = this[this.selectedIndex].value;">
<?php
    foreach($u->getAllDocTypeIcons() as $icon):
      $selected = ($item->getItemIcon() == $icon['helper_app_icon']) ? ' selected="selected"' : '';
?>
         <? /* Set the default to the Item Icon */ ?>
         <option value="<?=$icon['helper_app_icon']?>"<?=$selected?>><?=$icon['helper_app_name']?></option> 
<?php endforeach; ?>
           </select>
           <img id="iconImg" name="iconImg" width="24" height="20" src="<?=$item->getItemIcon()?>" />
         </td>
       </tr>
<?php endif; ?>       

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
          <th>Print status:</th>
          <td>
         <input type="radio" name="availability"
       <?= ($item->getAvailability() === 0) ? 'checked="checked"' : '' ?>  value="0">
         <span id="availability_option0">out of print</span>
            <input type="radio" name="availability"
       <?= ($item->getAvailability() == null || $item->getAvailability() == 1) ? 'checked="checked"' : '' ?>  value="1">
         <span id="availability_option1">in print</span>
           </td>
        </tr>  
        
<?php if($item->isPhysicalItem()): ?>
        <tr>
          <th>
            Control Number:
          </th>
          <td>
             <input type="text" name="local_control_key" size="15" value="<?=$item->getLocalControlKey();?>" />
          </td>
        </tr> 
        
<?php if($_REQUEST['cmd'] == 'addPhysicalItem'): ?>       
          <tr>
            <th>Reserve Desk:</th>
            <td>
              <select name="home_library">
<?php
      foreach($u->getLibraries() as $lib):
        $selected_lib = ($item->getHomeLibraryID() == $lib->getLibraryID()) ? 'selected="selected"' : '';
?>
                <option value='<?=$lib->getLibraryID()?>' <?=$lib->getLibraryID()?> > <?=$lib->getLibrary()?> </option>;
<?php endforeach; ?>
              </select>
            </td>
          </tr>        
<?php endif; ?>	
	
<?php endif; ?>                
      </table>
    </div>
    
    <script language="JavaScript">
       // init form based on selected type of material
       typeOfMaterial();
       
       // Set the item_group based on type of material for physical items
       function materialTypeEvents() {
         
        var material_type = document.getElementById('material_type').value;

        switch(material_type)
        {          
          // FOR NEW OR EDIT PHYSICAL ITEMS
          // Change icon based on material type, when material type has changed.
          // Also, set the item group for physical items.          
          case "BOOK": document.getElementById('item_group').value = 'MONOGRAPH'; 
            document.getElementById('iconImg').src = "images/doc_type_icons/doctype-book.gif"; break;
          case "CD": 
          case "DVD": 
          case "VHS": 
          case "SOFTWARE": document.getElementById('item_group').value = 'MULTIMEDIA'; 
            document.getElementById('iconImg').src = "images/doc_type_icons/doctype-disc.gif"; break;
        }
        
<?php if (!$item->itemID): ?>         
        switch(material_type) {             
          // For NEW ELECTRONIC ITEMS ONLY
          // Change icon based on material type, when material type has changed.          
          case "BOOK_PORTION": document.getElementById('iconImg').src = "images/doc_type_icons/doctype-pdf.gif"; 
                    document.item_form.selectedDocIcon.selectedIndex = 1;  break;
          case "JOURNAL_ARTICLE": document.getElementById('iconImg').src = "images/doc_type_icons/doctype-pdf.gif";  
                  document.item_form.selectedDocIcon.selectedIndex = 1;    break;
          case "CONFERENCE_PAPER": document.getElementById('iconImg').src = "images/doc_type_icons/doctype-text.gif";   
                  document.item_form.selectedDocIcon.selectedIndex = 4;    break;
          case "COURSE_MATERIALS": document.getElementById('iconImg').src = "images/doc_type_icons/doctype-text.gif";   
                  document.item_form.selectedDocIcon.selectedIndex = 4;    break;
          case "IMAGE": document.getElementById('iconImg').src = "images/doc_type_icons/doctype-image.gif";  
                  document.item_form.selectedDocIcon.selectedIndex = 8;    break;
          case "VIDEO": document.getElementById('iconImg').src = "images/doc_type_icons/doctype-movie.gif";  
                  document.item_form.selectedDocIcon.selectedIndex = 3;    break;
          case "AUDIO": document.getElementById('iconImg').src = "images/doc_type_icons/doctype-sound.gif";  
                  document.item_form.selectedDocIcon.selectedIndex = 2;    break;
          case "WEBPAGE": document.getElementById('iconImg').src = "images/doc_type_icons/doctype-link.gif"; 
                  document.item_form.selectedDocIcon.selectedIndex = 7;  break;
          case "OTHER": document.getElementById('iconImg').src = "images/doc_type_icons/doctype-clear.gif"; 
                  document.item_form.selectedDocIcon.selectedIndex = 0;  break;
        }        
<? endif ?> 

        switch(material_type)
        { // For BOOK_PORTION only, display the rightsholder section.
          case "BOOK_PORTION":     document.getElementById('rightsholder_hideshow') .style.display = '';    break;
          default:   document.getElementById('rightsholder_hideshow') .style.display = 'none';     break;
        }
       }      
    </script>
<?php
  }
  
  /**
   * Display rightsholder info for item
   * @param rightsholder $rh the item's rightsholder object
   */
  function displayEditItemRightsholder($rh,$materialType) {
?>
<div id="rightsholder_hideshow" style="display:<?= ($materialType == 'BOOK_PORTION') ? 'inline' : 'none' ?>">
    <div class="headingCell1">RIGHTSHOLDER</div>
    <div id="rightsholder_details" style="padding:8px 8px 12px 8px;">
      <small>This rightsholder information is shared with all reserves that
        use the ISBN specified above.</small>
      <table class="editItem" border="0" cellpadding="2" cellspacing="0">
        <tr>
          <th>Rightsholder Name:</th>
          <td><input id="rh_name" name="rh_name" type="text" size="50"
                value="<?= is_null($rh) ? '' : $rh->getName() ?>"></td>
        </tr>
        <tr>
          <th>Contact Name:</th>
          <td><input id="rh_contact_name" name="rh_contact_name" type="text" size="50"
                value="<?= is_null($rh) ? '' : $rh->getContactName() ?>"></td>
        </tr>
        <tr>
          <th>Contact Email:</th>
          <td><input id="rh_contact_email" name="rh_contact_email" type="text" size="50"
                value="<?= is_null($rh) ? '' : $rh->getContactEmail() ?>"></td>
        </tr>
        <tr>
          <th>Fax:</th>
          <td><input id="rh_fax" name="rh_fax" type="text" size="50"
                value="<?= is_null($rh) ? '' : $rh->getFax() ?>"></td>
        </tr>
        <tr>
          <th>Rights URL:</th>
          <td><input id="rh_rights_url" name="rh_rights_url" type="text" size="50"
                value="<?= is_null($rh) ? '' : $rh->getRightsUrl() ?>"></td>
        </tr>
        <tr>
          <th>Limit:</th>
          <td><input id="rh_policy_limit" name="rh_policy_limit" type="text" size="50"
                value="<?= is_null($rh) ? '' : $rh->getPolicyLimit() ?>"></td>
        </tr>
        <tr>
          <th>Postal Address:</th>
          <td><textarea id="rh_post_address" name="rh_post_address" rows="3" cols="50"><?= 
              is_null($rh) ? '' : $rh->getPostAddress() ?></textarea></td>
        </tr>
      </table>
    </div>
    </div>
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
      
      <? /* Notes cannot be created unless the item has been saved first.   */ ?>
      <?php if(isset($id)): ?>        
        <?php  self::displayNotesBlockAJAX($notes, $obj_type, $id, $include_addnote_button); ?>
      <?php else: ?>
        <p>If you would like to add a note, please save the item first.<p>
      <?php endif; ?> 
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
    global $u, $g_permission, $g_copyrightNoticeURL, $g_notetype, $ajax_browser;
    
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

        // Validation: Is copyright percentage within guideline limit?
        if (!validateCopyrightPercentage()) return false;

<?php if ($item->isPhysicalItem()): ?>
        //make sure this physical copy is supposed to have a barcode
        //  if it is, there will be an input element for it in the form
        if( (document.getElementById('barcode') != null) && (document.getElementById('barcode').value == '') ){ 
          alertMsg = alertMsg + "Barcode is required.<br />";
        }
<? elseif (!$item->itemID): ?>  
        if((document.getElementById('userFile').value == "") && (document.getElementById('url').value == "")) {       
          alertMsg = alertMsg + 'You must choose an item source of either "Upload a document" or "Add a link".<br />';
        }     
<? endif ?>   
        // Validate the required fields for the defined material type.
        alertMsg += checkMaterialTypes2(frm); 
        if (!alertMsg == "") { 
          document.getElementById('alertMsg').innerHTML = alertMsg;
          return false;
        } else {
          // set store_request to true for "Save Changes" button
          document.getElementById('store_request').value=1; 
          // make sure that this submit to the correct (outer) form.
          document.item_form.submit();
        }         
      }
      // do form-validation for material-type portion of add/edit form
      function checkMaterialTypes2(form) {
        // remove any 'incomplete' markings
        var edit_tables = $$('.editItem');
        var table = edit_tables[0];
        for (var i = 0; i < table.rows.length; i++) {
          row = table.rows[i];
          if (row.cells[1]) {
            row.cells[1].className = "";
          }
        }
        var alertMsg = '';

        // material type is now required
        if ($('material_type').options[$('material_type').selectedIndex].value == '') {
          alertMsg += 'Please select type of material.<br/>';
          form.material_type.parentNode.className = 'incomplete';
        } else {
          var type = $('material_type').options[$('material_type').selectedIndex].value;
         
          var theLabel = document.getElementById('material_type');
          // special-case for material type 'other' 
          if ((type == "OTHER") && ($('material_type_other').getValue() == '')) {
            alertMsg += 'Type of material must be specified when "Other" is selected.<br/>';
            theLabel.parentNode.className = 'incomplete';
            theLabel.parentNode.className = 'incomplete';
          } else {
            theLabel.parentNode.className = '';
          }
          
          // check all required fields for current type of material
          var type_details = materialType_details[type];
          for (var field in type_details) {
            if (type_details[field]["required"]) {
              var tr = $(field);
              var inputs = tr.select('input[type="text"]');
              var radio_inputs = tr.select('input[type="radio"]');
              if ((inputs.length && inputs[0].getValue() == '') ||
                  (radio_inputs.length && (! radio_inputs[0].checked)
                   && (! radio_inputs[1].checked))) {
                alertMsg += type_details[field]['label'] + ' is required.<br/>';
                tr.cells[1].className = 'incomplete';
              }
            }
          }
        }
        
        return alertMsg;
      }
    //-->
    </script>

<? /* NOTE: post to same command, to preserve info about action (editItem or addDigitalItem   */ ?>
      
     <form id="item_form" name="item_form" action="index.php?cmd=<?= $_REQUEST['cmd'] ?>" method="post"
        <? if (! $item->isPhysicalItem()): ?> enctype="multipart/form-data" <? endif ?> >        
<?php if ($item->isPhysicalItem()): ?>   
      <?php if (isset($_REQUEST['item_group'])): ?>
          <input type="hidden" id="item_group" name="item_group" value="<?=$_REQUEST['item_group']?>" />      
      <?php else: ?>  
         <input type="hidden" id="item_group" name="item_group" value="MONOGRAPH" />
      <?php endif; ?> 
<?php else: ?>
      <input type="hidden" id="item_group" name="item_group" value="ELECTRONIC" />  
<?php endif; ?>      
      
      <input type="hidden" id="itemID" name="itemID" value="<?=$item->getItemID()?>" />
      <?php self::displayHiddenFields($dub_array); //add duplication info as hidden fields ?> 
<?php if($edit_reserve): ?>
      <input type="hidden" name="reserveID" value="<?=$reserve->getReserveID()?>" />  
<?php endif; ?>
      <input type="hidden" id="store_request" name="store_request" value=0>

<?php if(!empty($_REQUEST['ci'])):
  /* if course id is set (e.g., adding new digital item from a class), pass on  */ ?>
      <input type="hidden" name="ci" value="<?= $_REQUEST['ci']?>" />
      <input type="hidden" id="ciid" value="<?= $_REQUEST['ci']?>" /> 
<?php elseif($edit_reserve): ?>
      <input type="hidden" id="ciid" value="<?=$reserve->getCourseInstanceID()?>" />
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
      if($edit_reserve) {
        // get the copyright information from the reserves before passing on to the detail section.
        $reservesLimit = $item->getOverallBookUsage($reserve->getCourseInstanceID(), false);
        self::displayEditItemItemDetails($item, $reserve->getCopyrightStatusDisplay($u, $reservesLimit, $item->getISBN()), $reserve->getCourseInstanceID());  
        //show item details  
      }
      else {
        self::displayEditItemItemDetails($item,"");  //show item details 
      }
      self::displayEditItemSource($item);       //show item source

      if ($u->getRole() >= $g_permission['staff']) {
        $materialType = ($item->getMaterialType() == null) ?  'BOOK_PORTION' : $item->getMaterialType();     
        // show rightsholder section, but only to >= staff
        self::displayEditItemRightsholder($item->getRightsholder(), $materialType);
      }
    }
?>    
    <br />

    <div class="headingCell1">NOTES</div>        
    <br />    
    <?php if(isset($reserve) || (!empty($_REQUEST['search']))): //if editing existing item, use AJAX notes handler ?> 
      <?php if($ajax_browser): // determine if the browser handles ajax ?> 
        <?php  self::displayEditItemNotesAJAX($item, $reserve);  ?>
      <?php else: // suggest a non IE browser for editting notes. ?>
        <?php  self::displayEditItemNotes($item, $reserve);  ?>   
      <?php endif; ?>  
        
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
        <input type="button" value="Save Changes" onClick="return validateForm(this.form);">
    </div>
    </form>    
<?php   
  }
  
  
  /**
   * @return void
   * @param reserveItem object $item
   * @desc Displays item history screen
   */
  function displayItemHistory($item) {
    //get dates and terms
    $creation_date = date('F d, Y', strtotime($item->getCreationDate()));
    $creation_term = new term();
    $creation_term = $creation_term->getTermByDate($item->getCreationDate()) ? $creation_term->getTerm() : 'n/a';
    $modification_date = date('F d, Y', strtotime($item->getLastModifiedDate()));
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

      default:
        $tab_styles['meta'] = 'class="current"';
      break;
    }

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
<?php   if(($u->getRole() >= $g_permission['staff']) && ((!empty($_REQUEST['search'])) || $edit_reserve)): ?>
        <li <?=$tab_styles['history']?>><a href="index.php?cmd=editItem&<?=$edit_item_href?>&tab=history<?= $search_param ?>">History</a></li>

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
