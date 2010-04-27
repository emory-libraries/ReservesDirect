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

  function displayCopyrightQueue($reserves)
  {
    $rowCount = 0;
    ?><ul class='wideList'>
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

                  <div><?=common_getStatusSpan($reserve->getStatus())?></div>

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

                </div>
              </div>
            </li>
           <div style="clear:both;"></div><?php
          }
        ?>
      </ul>
    <?php
  }

}
