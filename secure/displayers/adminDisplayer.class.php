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
require_once('secure/displayers/baseDisplayer.class.php');

class adminDisplayer extends baseDisplayer 
{
  /**
   * Display List all admins available to user
   *
   * $adminList array adminID, adminTitle
   */
  function displayAdminFunctions()
  {
?>
  <p><a href="index.php?cmd=admin&function=editDept">Add/Edit Departments</a></p>
  <p><a href="index.php?cmd=admin&function=editLib">Add/Edit Libraries</a></p>
  <p><a href="index.php?cmd=admin&function=editTerms">Add/Edit Terms</a></p>
  <p><a href="index.php?cmd=admin&function=editNews">Add/Edit News</a></p>
  <p><a href="index.php?cmd=admin&function=editClassFeed">Manage Course Feed for a Class</a></p>
  <p><a href="index.php?cmd=admin&function=clearReviewedFlag">Flag Course for Copyright Review</a></p>
  
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
          document.getElementById('dept_id').value  = dept['department_id'];
          document.getElementById('dept_name').value  = dept['name'];
          document.getElementById('dept_abbr').value  = dept['abbreviation'];
          
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
        document.getElementById('dept_name').value  = deptName;
        document.getElementById('dept_abbr').value  = '';
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
          
          document.getElementById('lib_id').value       = lib['id'];
          document.getElementById('lib_name').value       = lib['name'];
          document.getElementById('lib_nickname').value     = lib['nickname'];
          document.getElementById('ils_prefix').value     = lib['ils_prefix'];                    
          document.getElementById('desk').value         = lib['desk'];
          document.getElementById('lib_url').value      = lib['url'];   
          document.getElementById('contactEmail').value   = lib['email'];     
          
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
        document.getElementById('lib_id').value       = "";
        document.getElementById('lib_name').value       = "";
        document.getElementById('lib_nickname').value     = "";
        document.getElementById('ils_prefix').value     = "";
        document.getElementById('desk').value         = "";
        document.getElementById('lib_url').value      = "";
        document.getElementById('contactEmail').value     = "";
        
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
              <option value="null">No Monograph Processing Library</option>
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
              <option value="null">No Multimedia Processing Library</option>
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
    
  /**
   * @return void
   * @param string $next_cmd The next command to execute
   * @param array $courses (optional) Array of crosslistings
   * @param string $msg (optional) Text to display above the class select
   * @param array $hidden_fields (optional) Array of info to pass on as hidden fields
   * @param string $return_variable name of html variable for selected
   * @desc Displays list of courses (course_alias) for selection
   */
  public function displayEditRegistrarKey($next_cmd, $courses=null, $msg=null, $hidden_fields=null, $return_variable='ca')  
  {

    if(!empty($msg)) {
      echo "<span class=\"helperText\">$msg</span><p />\n";
    }
    
    print("<form action=\"index.php\" method=\"post\" name=\"select_course\" id=\"select_class\">\n");
    print(" <input type=\"hidden\" id=\"cmd\" name=\"cmd\" value=\"$next_cmd\" />\n");
    
    self::displayHiddenFields($hidden_fields);

    $c = new course();
    
    print "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\" align=\"center\">\n";
    
    $rowNumber = 0;

    print("<td></td><td></td><td></td></td><td style='text-align:center'>Override All&nbsp;&nbsp; <input type='checkbox' id='overrideAll'></td>");
    foreach($courses as $c)
    {
      $c->getDepartment();  

      // If thes override_feed is 1, then all import scripts will be overridden.
      // The new setting override_feed = 2, will not display here as override_feed ON, 
      // this can only be set during a manual crosslistings.
      // If this is checked, then the override_feed will be set to 1 during the update.
      $checked = ($c->getOverrideFeed() == 1) ? "CHECKED" : "";
      $rowClass = ($rowNumber % 2) ? "evenRow" : "oddRow\n";


      print("<tr class=\"$rowClass\">\n");
      print("<td style=\"text-align:center;\">".$c->displayCourseNo().$c->getSection() . "</td>");
      print("<td style=\"text-align:center;\">" . $c->getName() . "</td>");
      print("<td style=\"text-align:center;\"><input type=\"text\" id=\"{$return_variable}_{$rowNumber}_reg\" name=\"".$return_variable."[" . $c->getCourseAliasID() ."][registrar_key]\" value=\"". $c->getRegistrarKey() ."\" size=\"60\" maxlength=\"255\"/></td>\n");
      print("<td style=\"text-align:center;\"><label for=\"override\">Override Feed<input type=\"checkbox\" id=\"{$return_variable}_{$rowNumber}_override\" name=\"".$return_variable."[" . $c->getCourseAliasID() ."][override_feed]\"  $checked value=\"true\"\"></td>\n");


      print("</tr>\n");
      $rowNumber++;
    }
    
    print "<tr><td>&nbsp;</td></tr>";
    print "<tr><td colspan=\"3\" align=\"center\"><input type=\"submit\" value=\"Update\"> <input type=\"button\" value=\"Cancel\" onClick=\"window.location.href='index.php?cmd=admin';\"></td></tr>\n";
    
    print "</table>\n";
    
    print("</form>\n");
?>
    <script type="text/javascript" charset="utf-8">
    
            var checkboxes = $$("#select_class input[type=checkbox]");
            var cbControl = $("overrideAll");
    
            cbControl.observe("click", function(){
             checkboxes.each(function(box){
             box.checked = cbControl.checked;
             });
          });
    
    </script>
<?

  } 
  
  
  function displayEditTerm($function, $terms, $edit_id = null)
  { 
    global $calendar, $g_terms;
    ?>
    <script language="JavaScript1.2" src="secure/javascript/liveSearch.js"></script>
    <script language="JavaScript1.2">
      function termReturnAction(term_array)
      {
        eval("var term = " + decode64(term_array)); 
          
        document.getElementById('editTermArea').style.display = ''; 

        if (typeof(term['term_id']) != "undefined")
        {
          document.getElementById('term_id').value  = term['term_id'];          
                              
          document.getElementById('sort_order').enabled = true;
          document.getElementById('term_name').enabled = true;
          document.getElementById('term_year').enabled = true;
          document.getElementById('begin_date').enabled = true;         
          document.getElementById('end_date').enabled = true;
          
          document.getElementById('term_name').focus();
        }
        
      }
      
      function checkSubmit()
      {
        if (document.getElementById('term_name').SelectIndex == -1)
        {
          alert ("Term Name is required.");
          return false;
        }
                
        if (document.getElementById('term_year').value == "")
        {
          alert ("Year is required.");
          return false;
        }

        if (document.getElementById('begin_date').value == "")
        {
          alert ("Begin Date is required.");
          return false;
        }       
        if (document.getElementById('end_date').value == "")
        {
          alert ("End Date is required.");
          return false;
        }       
        if (document.getElementById('sort_order').value == "")
        {
          alert ("Sort Order is required.");
          return false;
        }                       
      }
      
      function createNew(deptName){
        document.getElementById('editTermArea').style.display = ''; 
        
        document.getElementById('term_id').enabled = false;
        document.getElementById('term_name').value  = deptName;
        
        document.getElementById('term_name').focus();
      }
      
      function load()
      {
        <? if (!is_null($edit_id)) { ?>
        <? $term = new term($edit_id); ?>
          document.getElementById('editTermArea').style.display = '';
          document.getElementById('term_id').value  =  <?=  $term->getTermID(); ?>;
          document.getElementById('term_name').value  = '<?= $term->getTermName(); ?>';
          document.getElementById('term_year').value  = '<?= $term->getTermYear(); ?>';
          document.getElementById('begin_date').value     = '<?= $term->getBeginDate(); ?>';
          document.getElementById('end_date').value     = '<?= $term->getEndDate(); ?>';
          document.getElementById('sort_order').value = '<?= $term->getSortOrder(); ?>';
          
          document.getElementById('newTerm').style.display = 'none';
          //document.getElementById('term_id_select').style.display = 'none';
        <? } ?>
      }
    </script>     
    
    <table>
      <tr>
        <td width="5"></td>
        <td><input type="button" onClick="createNew('');" value="New Term" id="newTerm"></td>
      </tr>
      <tr><td width="5"></td><td class="strong">Select a Term:</td></tr>
    <form method="POST" action="index.php?cmd=admin&function=editTerms">  
      <tr><td width="5"></td>
      
        <td>
          <select id="term_id_select" name="term_id_select" onChange="this.form.submit();">
            <option value=''>Select A Term</option>
            <? foreach ($terms as $term) { ?>
              <option value="<?= $term->getTermID() ?>"> <?= $term->getTermName() ?> <?= $term->getTermYear() ?>
            <? } ?>
          </select>
        </td>
    
      </tr>
    </form>
    <form method="POST" action="index.php?cmd=admin&function=saveTerm" onSubmit="return checkSubmit();">
    <input type="hidden" id="term_id" name="term_id" value="">      
      <? $last_term = reset($terms); ?>
      
      <tr><td></td><td>&nbsp;</td></tr>

      <table id="editTermArea" style="display: none;">
        <tr>
          <td>&nbsp;</td>
          <td>Term:</td>
                  
          <td align="left">
            <select name="term_name" id="term_name">
              <? foreach ($g_terms as $term) { ?>
                <option><?= $term ?></option>
              <? } ?>
            </select>
            <input id="term_year" name="term_year" value="<?= $last_term->getTermYear() + 1 ?>" size="4">
          </td>
        </tr>       
        <tr>
          <td>&nbsp;</td>
          <td>Begin Date:</td>
          <td>
            <input type="text" id="begin_date" name="begin_date" size="10" maxlength="10" value="<?= $last_term->getEndDate() ?>"/>
            <?=$calendar->getWidgetAndTrigger('begin_date', $last_term->getEndDate())?>
          </td>
          <td><i>default date for class/reserve activation if not provided in feed</i></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>End Date:</td>
          <td>
            <input type="text" id="end_date" name="end_date" size="10" maxlength="10" value="<?= $last_term->getEndDate() ?>" />
            <?=$calendar->getWidgetAndTrigger('end_date', $last_term->getEndDate())?>
          </td>
          <td><i>default date for class/reserve expiration if not provided in feed</i></td>
        </tr> 
        <tr>
          <td>&nbsp;</td>
          <td>Sort Order:</td>
          <td><input type="text" id="sort_order" name="sort_order" size="4" maxlength="3" value="<?= $last_term->getSortOrder() + 1?>" /> </td>
          <td><i><?= $last_term->getSortOrder() ?> is the current max sort.  You probably want to use the next value</i></td>
        </tr>     
      </table>
      
    </table>    
    
    <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
    <tr><td>&nbsp;</td><td><input type=submit value="Save Term" id="frmSubmit"></td></tr>
    
    </form>
    <script language="JavaScript">load();</script>
    <?
  } 
  
  function displayEditNews($function, $news, $news_item=null)
  {
    global $g_permission;
    $perms = array_flip($g_permission);
    
    $calendar = new Calendar();
    $calendar->set_option('ifFormat', '%Y-%m-%d %H:%M');
    $calendar->set_option('showsTime', true); 
    $calendar->set_option('range', array(date('Y'), (date('Y')+5)));

    ?>
    <script language="JavaScript">
      function disable_dateSelect(self, id)
      {
        document.getElementById(id).enabled = false;
      }
      
      function checkSubmit()
      {
        
        if (!(document.getElementById('permission_level_0').checked ||
            document.getElementById('permission_level_1').checked ||
            document.getElementById('permission_level_2').checked ||
            document.getElementById('permission_level_3').checked ||
            document.getElementById('permission_level_4').checked ||
            document.getElementById('permission_level_5').checked))
        {
          alert("You must select at least one Show To group.");
          return false;     
        }
        
        if (document.getElementById('font_class').selectedIndex == -1)
        {
          alert("Please select the desired CSS Class");
          return false;
        }
        if ((document.getElementById('begin_time').value == '' && document.getElementById('begin_time_null').checked == false))
        {
          alert("Please set the Begin date/time or check Ongoing");
          return false;
        }
        if ((document.getElementById('end_time').value == '' && document.getElementById('end_time_null').checked == false))
        {
          alert("Please set the End date/time or check Ongoing");
          return false;
        }                       
      }
      
    </script>
    
    <form id="insertNews" name="insertNews" action="index.php" onsubmit="return checkSubmit()" method="Post">
    <input type="hidden" name="cmd" value="admin"/>
    <? if (is_null($news_item)) { ?>
      <input type="hidden" name="function" value="insertNews"/>     
    <? } else { ?>
      <input type="hidden" name="function" value="updateNews"/>     
      <input type="hidden" name="news_id" id="news_id" value="<?= $news_item['id'] ?>" />         
    <? } ?>
    <h2>Add News</h2>
    <table>
      <tr>
        <td valign="top">Show To:</td>
          <? if (is_null($news_item)) { ?>
            <td>
            <? $i = 0; ?>         
            <? foreach ($g_permission as $label => $value) { ?>           
              <input type="checkbox" name="permission_level[]" id="permission_level_<?=$i?>" value="<?= $value ?>" <?= $checked ?>/>
              <?= strtoupper($label) ?>
              <? if ($i % 2)  echo "<br/>"; ?>
              <? $i++; ?>
            <? } ?>
            </td>
            <td valign="top"></td>
          <? } else { //Dont allow edit of display level
              echo "<td>";
              if (is_null($news_item['permission_level'])) { 
                echo "ALL"; 
              } else { 
                echo strtoupper($perms[$news_item['permission_level']]);
              }
              echo "</td>";
             } ?>
        </td>       
      </tr>
      
      <tr>
        <td>Style:</td>
        <?
          if (!is_null($news_item))
          {
            $selected = $news_item['class'];
            $$selected = "selected";
          }
        ?>
        <td>
          <select id="font_class" name="font_class">
            <option value="notice" <?= $notice ?>>Notice</option>
            <option value="emergency" <?= $emergency ?> >Emergency</option>           
          </select>       
        </td>
      </tr>
      
      <tr>
        <? 
          if (is_null($news_item) || is_null($news_item['begin_time']))
          {
            $begin_null =  "checked";
            $begin = date("Y-m-d h:i");
          } else {
            $begin_null = '';
            $begin = $news_item['begin_time'];
          }         
        ?>
        <td>Begin:</td>
        <td>          
          <input type="text" id="begin_time" name="begin_time"  value="<?= $begin ?>"/>
                     (YYYY-MM-DD 24H:MIN)
          <?=$calendar->getWidgetAndTrigger('begin_time', $begin) ?>
          <? 
          /* allows setting begin date to null
          <input type="checkbox" id="begin_time_null" name="begin_time_null" <?= $begin_null ?> onclick="disable_dateSelect(this, 'begin_time');">
            Ongoing
          */
          ?>
        </td>
            </tr>
      <tr>
        <? 
          if (is_null($news_item) || is_null($news_item['end_time']))
          {
            $end_null =  "checked";
            $end = '';
          } else {
            $end_null = '';
            $end = $news_item['end_time'];
          }         
        ?>      
        <td>End:</td>
        <td>            
          <input type="text" id="end_time" name="end_time" value="<?= $end ?>" />
                    (YYYY-MM-DD 24H:MIN)
          <?=$calendar->getWidgetAndTrigger('end_time', $end) ?>
          <input type="checkbox" id="end_time_null" name="end_time_null" <?= $end_null ?> onclick="disable_dateSelect(this, 'end_time');">
            Ongoing
        </td>
      </tr>     
      <? $item_sort = (is_null($news_item['sort_order'])) ? 1 : $news_item['sort_order']; ?>
      <tr>
        <td>Sort:</td>
        <td><input type="text" name="sort_order" id="sort_order" maxlength="3" size="4" value="<?= $item_sort ?>"/></td>
      </tr>     
      <tr>
        <td valign="top">Message Text:</td>
        <td colspan="2"><textarea name="news_text" id="news_text" wrap="virtual" cols="80" rows="6"><?= $news_item['text'] ?></textarea></td>
      </tr>
      
      <? $butText = (is_null($news_item)) ? "Create New" : "Submit Changes"; ?>
      <tr><td colspan="3" align="left"><input type="submit" value="<?= $butText ?>" /></td></tr>
    </table>
    </form>
    
    
    <br/><br/>
        
    <h2>Edit News</h2>
    <table width="100%" border="0" cellspacing="0" cellpadding="5" class="displayList">
      <tr>
        <th></th>
        <th>User Level</th>
        <th>Begin</th>
        <th>End</th>
        <th>Text</th>
      </tr>
      
      <? foreach ($news as $n) { ?>
      <? $p = (!is_null($n['permission_level'])) ? strtoupper($perms[$n['permission_level']]) : 'ALL'; ?>
      <? $b = (!is_null($n['begin_time'])) ? $n['begin_time'] : 'Ongoing'; ?>
      <? $e = (!is_null($n['end_time']))   ? $n['end_time']   : 'Ongoing'; ?>
      
      <? $rowClass = ($rowClass=='evenRow') ? 'oddRow' : 'evenRow'; ?>
      
      <tr class="<?= $rowClass ?>">
        <td>
          <a href="index.php?cmd=admin&function=editNews&id=<?= $n['id'] ?>">
            <img src="images/pencil.gif" alt="edit" width="24" height="20" border="0"/> Edit
            </a>
        </td>
        <td align="center"><?= $p ?></td>
        <td align="center" nowrap><?= $b ?></td>
        <td align="center" nowrap><?= $e ?></td>
        <td><?= htmlentities(substr($n['text'], 0, 200)) ?></td>
      </tr>
      <? } ?>
    </table>

    <?
  }
}
?>
