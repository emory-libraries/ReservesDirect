<?
/*******************************************************************************
copyClassDisplayer.class.php

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
require_once("secure/managers/ajaxManager.class.php");

class copyClassDisplayer extends baseDisplayer {
	
	function displayImportClassOptions(&$src_ci, &$tree_walker, $dst_ci_id, $next_cmd, $hidden_fields=null) {
		global $u, $g_permission;
		//pull in some needed info
		$src_ci->getInstructors();				
		$src_ci->getCrossListings();
		$src_ci->getPrimaryCourse();
		$src_ci->course->getDepartment();
		$loan_periods = $src_ci->course->department->getInstructorLoanPeriods();
		$dst_ci = new courseInstance($dst_ci_id);
		$dst_ci->getCourseForUser();
		
		//handle instructors and crosslistings lists
		
		//instructors - if there is only one instructor and s/he is performing the import, then hide instructors option
		$instructors = $src_ci->instructorList;
		if((sizeof($instructors)==1) && ($u->getUserID()==$instructors[0]->getUserID())) {
			$instructors = null;
		}
		//hide crosslistings option if there are none
		$crosslistings = !empty($src_ci->crossListings) ? $src_ci->crossListings : null;
		
		//begin display
?>
        <div class="helperText">
            Select readings to import from <u><?=$src_ci->course->displayCourseNo()?> -
            <?=$src_ci->course->getName()?> <?= $src_ci->displayTerm() ?></u> to <u><?=$dst_ci->course->displayCourseNo()?> -
            <?=$dst_ci->course->getName()?> <?= $dst_ci->displayTerm() ?></u>.
        </div>
        <div class="noticeBox">
            <div class="noticeImg"></div>
            <div class="noticeText">
                Please note there are costs associated with processing physical items and uploaded materials.
                Help us reduce costs by limiting non-required readings and linking to external resources whenever possible.
            </div>
        </div>
		<p />

		<script language="javascript">
		//<!--
			function checkAll2(form, theState)
			{
				for (var i=0; i < form.elements.length; i++) {
					if (form.elements[i].type == 'checkbox' && form.elements[i].name == 'selected_reserves[]') {
						form.elements[i].checked = theState;
					}
				}
			}
		//-->
		</script>
		
		<form action="index.php" method="post" name="reservesListForm">
			<input type="hidden" name="importClass" value="importClass" />
			<input type="hidden" name="cmd" value="<?=$next_cmd?>" />
			<input type="hidden" name="ci" value="<?=$dst_ci_id?>" />
			<input type="hidden" name="sourceClass" value="<?=$src_ci->getCourseInstanceID()?>" />
			<?php self::displayHiddenFields($hidden_fields); ?>
			
			<div>
			
<?php	if(!empty($instructors) && !empty($crosslistings)): ?>
				<div class="headingCell1" style="width:33%;">Import Options</div>
				<div class="borders" style="background-color:#CCCCCC;" style="padding:10px;">
<?php		if(!empty($instructors)): ?>
					<div style="width:45%; float:left;">
						<input type="checkbox" name="copyInstructors" value="checkbox" '.$instructors_checked.'>&nbsp;<strong>Copy Instructors</strong>
						<ul>
<?php			
				foreach($instructors as $instr):
					if($u->getUserID() == $instr->getUserID()) {
						continue;	//skip the instructor doing the importing
					}
?>
							<li><?=$instr->getName();?></li>
<?php			endforeach; ?>
						</ul>							
					</div>
<?php		
			endif;
			if(!empty($crosslistings)):
?>
					<div style="width:45%; float:left;">							
						<input type="checkbox" name="copyCrossListings" value="checkbox" '.$crossListings_checked.'>&nbsp;<strong>Copy Crosslistings</strong>
						<ul>
<?php			foreach($crosslistings as $xlisting): ?>
							<li><?=$xlisting->displayCourseNo()?> &mdash; <?=$xlisting->getName()?></li>
<?php			endforeach; ?>
						</ul>
					</div>
<?php		endif; ?>
					<!-- hack to clear floats -->
					<div style="clear:both;"></div>
					<!-- end hack -->
				</div>
				<br />
				<br />
<?php	endif; ?>

				<div style="float:right; text-align:right;">
                    <?php if ($u->getRole() >= $g_permission['staff']): ?>
                        <a href="javascript:checkAll2(document.forms.reservesListForm, 1)">check all</a> | <a href="javascript:checkAll2(document.forms.reservesListForm, 0)">uncheck all</a>
                    <?php endif; ?>
                </div>
				<div class="headingCell1" style="width:33%;">Reserves List</div>
				<div style="clear:both;"></div>
					<ul style="list-style:none; padding-left:0px; margin:0px;">
				<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
					<tr>
						<td class="headingCell1">&nbsp;
						</td>
					</tr>
					<tr>
						<td>
<?php
		//begin displaying individual reserves
		//loop
		$prev_depth = 0;
		$rowStyle = '';
		foreach($tree_walker as $leaf) {
			//close list tags if backing out of a sublist
			if($prev_depth > $tree_walker->getDepth()) {
				echo str_repeat('</ul></li>', ($prev_depth-$tree_walker->getDepth()));
			}
			
		
			$reserve = new reserve($leaf->getID());	//init a reserve object
			$reserve->getItem();
			
			//set some additional info
			
			$reserve->selected = true;	//select all reserves by default
			$reserve->additional_info = '';
			
			if($reserve->item->isPhysicalItem() && !is_null($loan_periods)) {
				$reserve->additional_info .= '<br /><span class="itemMetaPre">Requested Loan Period:</span>';
				$reserve->additional_info .= '<select name="requestedLoanPeriod['.$reserve->getReserveID().']">';
				foreach($loan_periods as $loan_period) {
					$selected = ($loan_period['default'] == 'true') ? 'selected="selected"' : '';
					$reserve->additional_info .= '<option value="'.$loan_period['loan_period'].'" '.$selected.'>'.$loan_period['loan_period'].'</option>';
				}
				$reserve->additional_info .= '</select>';
			}
						
			$rowStyle = ($rowStyle=='oddRow') ? 'evenRow' : 'oddRow';	//set the style

			//display the info
			echo '<li>';
			if($reserve->item->isPhysicalItem() && $reserve->item->isPersonalCopy()):	//if physical personal item
				$reserve->additional_info = '<br /><span class="failedText">Personal items cannot be imported. Please contact your reserves desk for assistance.</span>';
?>
				<div class="<?=$rowStyle?>">
					<?php self::displayReserveInfo($reserve, 'class="metaBlock-wide"'); ?>
					<!-- hack to clear floats -->
					<div style="clear:both;"></div>
					<!-- end hack -->
				</div>			
<?php
			else:
				if($reserve->item->isHeading()) {
					$rowStyle ='headingCell2'; 
				}
?>
				<div class="<?=$rowStyle?>">
					<div class="checkBox-right">
						<input type="checkbox" name="selected_reserves[]" value="<?=$reserve->getReserveID()?>" />
					</div>
					<?php self::displayReserveInfo($reserve, 'class="metaBlock-wide"'); ?>
					<!-- hack to clear floats-->		
					<div style="clear:both;"></div>
					<!-- end hack -->
				</div>			
<?php
			endif;
			
			//start sublist or close list-item?
			echo ($leaf->hasChildren()) ? '<ul style="list-style:none;">' : '</li>';
			
			$prev_depth = $tree_walker->getDepth();
		}
		echo str_repeat('</ul></li>', ($prev_depth));	//close all lists
?>
					</ul>
				</div>
			</div>
			</td>
			</tr>
			<tr>
				<td class="headingCell1">&nbsp;
				</td>
			</tr>
		</table>
			<p />
			<div style="text-align:center;">
				<input type="submit" name="Submit" value="Import Class">
				<br />
				<small>Note: Please be patient, large classes may take several minutes to process.</small>				
			</div>
		</form>
					
<?php
	}
	
	function displayCopyClassOptions(&$sourceClass) {
?>
		<script src="secure/javascript/copyClassOptions.js"></script>
		<form action="index.php" method="post">
			<input type="hidden" name="sourceClass" value="<?=$sourceClass->getCourseInstanceID()?>" />
			
			<div>
				<div class="headingCell1" style="width:33%;">Copy Options</div>
				<div class="borders">
					<div style="padding:5px;">
						<strong>Copying from <?=$sourceClass->course->displayCourseNo()?> - <?=$sourceClass->course->getName()?> (<?=$sourceClass->displayTerm()?>)</strong> -- taught by <?=$sourceClass->displayInstructors()?>
					</div>
					<div style="background-color:#CCCCCC;" style="padding:10px;">
						<div style="width:45%; float:left;">
							<input id="copyReserves" name="copyReserves" type="checkbox" value="checkbox">&nbsp;Copy Reserve Materials
							<br />
							<input type="checkbox" id="copyCrossListings" name="copyCrossListings" value="checkbox">&nbsp;Copy Crosslistings
							<br />
							<input type="checkbox" id="copyEnrollment" name="copyEnrollment" value="checkbox">&nbsp;Copy Enrollment List
						</div>						
						<div style="width:45%; float:left;">							
							<input type="checkbox" id="copyInstructors" name="copyInstructors" value="checkbox">&nbsp;Copy Instructors
							<br />
							<input type="checkbox" id="copyProxies" name="copyProxies" value="checkbox">&nbsp;Copy Proxies
						</div>
						<div style="clear:left;">
							<br />
							<input type="checkbox" id="crosslistSource" name="crosslistSource" value="checkbox" onClick="setCopyOptions('crossList')";>&nbsp;CROSSLIST Source Class <span style="color:#000000;"><strong>Enrollment will be retained by course</strong></span>
							<br />
							<input type="checkbox" id="deleteSource" name="deleteSource" value="checkbox">&nbsp;DELETE Source Class (Merge Classes) <span style="color:#CC0000;"><strong>CAUTION! Deleting the Source Class cannot be undone!</strong></span>
						</div>
					</div>
					<div style="padding:5px;">
						Copy reserves to:&nbsp;
						<select name="cmd">
							<option selected value="copyExisting">an existing class</option>
							<option value="copyNew">a new class</option>
						</select>
					</div>
				</div>
			</div>
			<br />
			<center><input type="submit" name="submit" value="Continue" /></center>	
		</form>
<?php
	}
	
	function displayCopySuccess (&$sourceClass, &$targetClass, $copyStatus, $imported=false) {
		$copied_text = $imported ? 'imported into' : 'copied to'; 
?>
		<div class="borders" style="text-align: center;">
			<span class="strong"><?=$sourceClass->course->displayCourseNo()?> -- <?=$sourceClass->course->getName()?> (<?=$sourceClass->displayTerm()?>)</span> <span class="helperText"> has been <?=$copied_text?></span> <span class="strong"><?=$targetClass->course->displayCourseNo()?> -- <?=$targetClass->course->getName()?> (<?=$targetClass->displayTerm()?>)</span>
			<p />
			<ul>
<?php	foreach($copyStatus as $msg): ?>
				<li class="successText"><?=$msg?></li>
<?php	endforeach; ?>
			</ul>
			<p />
			<ul>
				<li><a href="index.php?cmd=editClass&ci=<?=$targetClass->getCourseInstanceID()?>">Go to target class</a></li>
<?php	if(!$imported): ?>
				<li><a href="index.php?cmd=copyClass">Copy another class</a></li>
				<li><a href="index.php?cmd=manageClasses">Return to &quot;Manage Classes&quot; home</a></li>
<?php	endif;

	}


}
?>
