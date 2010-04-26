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
require_once('secure/classes/reserveItem.class.php');
require_once('secure/classes/courseInstance.class.php');

class copyrightDisplayer {

  function displayCopyrightQueue($reserves)
  {
    $rowCount = 0;
    ?><ul>
        <?php foreach ($reserves as $reserve) {
            $item = new reserveItem($reserve->itemID);
            $ci = new courseInstance($reserve->courseInstanceID);

            $rowCount++;
            $rowClass = ($rowCount % 2) ? 'oddRow' : 'evenRow';

            ?><li style="list-style:none;">
              <div class="<?=$rowClass?>">
                <div class="iconBlock">
                  <img src="<?=$item->getItemIcon()?>" alt="icon">
                </div>
                <div class="metaBlock-wide">
                  <a href="reservesViewer.php?reserve=<?=$reserve->reserveID?>" target="_blank" class="itemTitle" style="margin:0px; padding:0px;"><?=$item->getTitle()?></a>
                  <a href='index.php?cmd=editItem&reserveID=<?=$reserve->reserveID?>'><img src="images/pencil-gray.gif" border="0" alt="edit"></a>
                  <br />
                  <span class="itemAuthor"<?=$item->getAuthor()?></span>

                  <?php if ($item->getPerformer()) { ?>
                    <br />
                    <span class="itemMetaPre">Performed by:</span><span class="itemMeta"><?=$item->getPerformer()?></span>
                  <?php } ?>
                  <?php if ($item->getVolumeTitle()) { ?>
                    <br />
                    <span class="itemMetaPre">From:</span><span class="itemMeta"><?=$item->getVolumeTitle()?></span>
                  <?php } ?>
                  <?php if ($item->getVolumeEdition()) { ?>
                    <br />
                    <span class="itemMetaPre">Volume/Edition:</span><span class="itemMeta"><?=$item->getVolumeEdition()?></span>
                  <?php } ?>
                  <?php if ($item->getPagesTimes()) { ?>
                    <br />
                    <span class="itemMetaPre">Pages/Times:</span><span class="itemMeta"><?=$item->getPagesTimes()?></span>
                  <?php } ?>
                  <?php if ($item->getSource()) { ?>
                    <br />
                    <span class="itemMetaPre">Source/Year:</span><span class="itemMeta"><?=$item->getSource()?></span>
                  <?php } ?>
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
