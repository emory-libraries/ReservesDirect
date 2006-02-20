<?
/*******************************************************************************
copyClassDisplayer.class.php


Created by Dmitriy Panteleyev (dpantel@emory.edu)

This file is part of ReservesDirect

Copyright (c) 2004-2005 Emory University, Atlanta, Georgia.

ReservesDirect is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

ReservesDirect is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with ReservesDirect; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

ReservesDirect is located at:
http://www.reservesdirect.org/


*******************************************************************************/
require_once("secure/managers/ajaxManager.class.php");

class copyClassDisplayer {
	
	function displaySelectClass($next_cmd, $msg=null, $hidden_fields=null) {
		if(!empty($msg)) {
			echo '<span class="helperText">'.$msg.'</span><p />';
		}
		
		//display selectClass
		$mgr = new ajaxManager('lookupClass', $next_cmd, 'manageClasses', 'Continue', $hidden_fields);
		$mgr->display();
	}
	
	function displayCopyClassOptions(&$sourceClass) {
?>
		<form action="index.php" method="post">
			<input type="hidden" name="sourceClass" value="<?=$sourceClass->getCourseInstanceID()?>" />
			
			<div>
				<div class="headingCell1" style="width:33%;">Copy Options</div>
				<div class="borders">
					<div style="padding:5px;">
						<strong>Copying from <?=$sourceClass->course->displayCourseNo()?> - <?=$sourceClass->course->getName()?> (<?=$sourceClass->displayTerm()?>)</strong> -- taught by <?=$sourceClass->displayInstructorList()?>
					</div>
					<div style="background-color:#CCCCCC;" style="padding:10px;">
						<div style="width:45%; float:left;">
							<input name="copyReserves" type="checkbox" value="checkbox" '.$reserves_checked.'>&nbsp;Copy Reserve Materials
							<br />
							<input type="checkbox" name="copyCrossListings" value="checkbox" '.$crossListings_checked.'>&nbsp;Copy Crosslistings
							<br />
							<input type="checkbox" name="copyEnrollment" value="checkbox" '.$enrollment_checked.'>&nbsp;Copy Enrollment List
						</div>
						<div style="width:45%; float:left;">
							<input type="checkbox" name="copyInstructors" value="checkbox" '.$instructors_checked.'>&nbsp;Copy Instructors
							<br />
							<input type="checkbox" name="copyProxies" value="checkbox" '.$proxies_checked.'>&nbsp;Copy Proxies
						</div>
						<div style="clear:left;">
							<br />
							<input type="checkbox" name="deleteSource" value="checkbox" '.$deleteSource_checked.'>&nbsp;DELETE Source Class (Merge Classes) <span style="color:#CC0000;"><strong>CAUTION! Deleting the Source Class cannot be undone!</strong></span>
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
	
	function displayCopySuccess ($sourceClass, $targetClass, $copyStatus) {
?>

		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
		<tr><td width="100%"><img src="images/spacer.gif" width="1" height="5"></td></tr>

		<tr>
		 	<td align="left" valign="top">
         	  <p>
         		<span class="strong">
         			<?=$sourceClass->course->displayCourseNo()?> -- <?=$sourceClass->course->getName()?> (<?=$sourceClass->displayTerm()?>)
         		</span>
         		<span class="helperText"> has been copied to </span>
         		<span class="strong">
         			<?=$targetClass->course->displayCourseNo()?> -- <?=$targetClass->course->getName()?> (<?=$targetClass->displayTerm()?>)
         		</span>
         	  </p>
         		<ul>
<?php
        for ($i=0; $i<count($copyStatus); $i++)
        {
        	echo 		'<li class="successText">'.$copyStatus[$i].'</li>';

        }
?>
        		</ul>
        	  <p>
        		&gt;&gt;<a href="index.php?cmd=editClass&ci=<?=$targetClass->getCourseInstanceID()?>">Go to target class</a><br>
        		&gt;&gt;<a href="index.php?cmd=copyClass">Copy another class</a><br>
        		&gt;&gt;<a href="index.php?cmd=manageClasses">Return to &quot;Manage Classes&quot; home</a><br>
        	  </p>
        	</td>
        </tr>

        <tr><td align="left" valign="top">&nbsp;</td></tr>
		</table>
<?php

	}


}
?>