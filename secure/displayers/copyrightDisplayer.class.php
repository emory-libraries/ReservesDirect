<?
/*******************************************************************************
copyrightDisplayer.class.php


Created by Ben Ranker <branker@emory.edu>

This file is part of ReservesDirect

Copyright (c) 2004-2010 Emory University, Atlanta, Georgia.

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

Reserves Direct is located at:
http://www.reservesdirect.org/


*******************************************************************************/
require_once('secure/common.inc.php');
require_once('secure/classes/reserveItem.class.php');
require_once('secure/classes/course.class.php');
require_once('secure/classes/courseInstance.class.php');

class copyrightDisplayer {

  function displayCopyrightQueue($reserves, $numcopyrightreserves, $currentpage, $totalpages)
  {     
    
    $rowCount = 0; // used for odd/even row tinting

    ?>
        <ul class='wideList'>
        <div class="headingCell1">COPYRIGHT RESERVES REVIEW QUEUE &mdash; page <?=$currentpage?></div>        
        <?php foreach ($reserves as $reserve) {
            $item = new reserveItem($reserve->itemID);
            $ci = new courseInstance($reserve->courseInstanceID);
            $ci->getInstructors();
            $course = new course($ci->primaryCourseAliasID);

            $rowCount++;
            $rowClass = ($rowCount % 2) ? 'oddRow' : 'evenRow';
                        
            ?><li style="list-style:none;">
              <div class="<?=$rowClass?>">
                <div class="iconBlock">
                  <img src="<?=$item->getItemIcon()?>" alt="icon">
                </div>
                <div class="metaBlock-wide">
                  <div>
                    <a href="reservesViewer.php?reserve=<?=$reserve->reserveID?>" target="_blank" class="itemTitle" style="margin:0px; padding:0px;"><?=$item->getTitle()?></a>
                    <a href='index.php?cmd=editItem&reserveID=<?=$reserve->reserveID?>'><img src="images/pencil.gif" border="0" alt="edit"></a>
                  </div>
                  <?php if ($item->getAuthor()) { ?>
                    <div class="itemAuthor"<?=$item->getAuthor()?></div>
                  <?php } ?>

                  <div><?=common_getStatusSpan($reserve->getCopyrightStatus())?></div>

                  <div>
                    <span class="itemMetaPre">Used in:</span>
                    <span class="itemMeta">
                      <a href='index.php?cmd=editClass&ci=<?=$ci->courseInstanceID?>'><span><?=$course->displayCourseNo()?></span>:
                      <span><?=$course->getName()?></span></a>,
                      <span><?=$ci->displayTerm()?></span>
                    </span>
                  </div>

                  <div>
                    <span class="itemMetaPre">Instructor:</span>
                    <span class="itemMeta"><?=$ci->displayInstructors()?></span>
                  </div>

                  <div>
                    <span class="itemMetaPre">Students:</span>
                    <span class="itemMeta"><?=$ci->getRollCount()?></span>
                  </div>

                </div>
              </div>
            </li>
           <div style="clear:both;"></div><?php
          }
        ?>
      </ul>
    <?php
    if (isset($reserves) && !empty($reserves)) {
      /******  build the pagination links ******/
      // range of num links to show
      $range = 3;

      // if not on page 1, don't show back links
      if ($currentpage > 1) {
         // show << link to go back to page 1
         echo " <a href='{$_SERVER['PHP_SELF']}?cmd={$_REQUEST['cmd']}&currentpage=1'><<</a> ";
         // get previous page num
         $prevpage = $currentpage - 1;
         // show < link to go back to 1 page
         echo " <a href='{$_SERVER['PHP_SELF']}?cmd={$_REQUEST['cmd']}&currentpage=$prevpage'><</a> ";
      } // end if 

      // loop to show links to range of pages around current page
      for ($x = ($currentpage - $range); $x < (($currentpage + $range) + 1); $x++) {
         // if it's a valid page number...
         if (($x > 0) && ($x <= $totalpages)) {
            // if we're on current page...
            if ($x == $currentpage) {
               // 'highlight' it but don't make a link
               echo " [<b>$x</b>] ";
            // if not current page...
            } else {
               // make it a link
               echo " <a href='{$_SERVER['PHP_SELF']}?cmd={$_REQUEST['cmd']}&currentpage=$x'>$x</a> ";
            } // end else
         } // end if 
      } // end for
                       
      // if not on last page, show forward and last page links        
      if ($currentpage != $totalpages) {
        // get next page
        $nextpage = $currentpage + 1;
        // echo forward link for next page 
        echo " <a href='{$_SERVER['PHP_SELF']}?cmd={$_REQUEST['cmd']}&currentpage=$nextpage'>></a> ";
        // echo forward link for lastpage
        echo " <a href='{$_SERVER['PHP_SELF']}?cmd={$_REQUEST['cmd']}&currentpage=$totalpages'>>></a> ";

      } // end if
      /****** end build pagination links ******/
      // Show the total number of reserves in the queue      
      echo "<div align='right'>($numcopyrightreserves reserves in the copyright queue)</div>";
    }
    else {
      echo "<h2>There are no reserves that are new or pending exceeding the copyright limit at this time.</h2>";
    }    
  }
}
