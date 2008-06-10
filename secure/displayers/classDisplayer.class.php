<?
/*******************************************************************************
classDisplayer.class.php


Created by Kathy Washington (kawashi@emory.edu)

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

Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

ReservesDirect is located at:
http://www.reservesdirect.org/

*******************************************************************************/
require_once("secure/common.inc.php");
require_once("secure/classes/terms.class.php");
require_once('secure/classes/tree.class.php');
require_once("secure/managers/ajaxManager.class.php");
require_once("secure/managers/lookupManager.class.php");
require_once('secure/displayers/baseDisplayer.class.php');

class classDisplayer extends baseDisplayer {
	
	function displayStaffHome($user)
	{
		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr><td width=\"100%\"><img src=\images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>\n";
		echo "	<tr>\n";
		echo "		<td align=\"center\" valign=\"top\">\n";
		echo "			<table width=\"66%\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\">\n";
		echo "				<tr class=\"headingCell1\"><td width=\"66%\" colspan=\"2\">Manage Classes</td><!--<td width=\"33%\">Manage Departments</td>--></tr>\n";
		echo "				<tr align=\"left\" valign=\"top\">\n";
		echo "					<td width=\"33%\" class=\"borders\">\n";
		echo "						<ul>\n";
		echo "							<li><a href=\"index.php?cmd=createClass\" align=\"center\">Create Class</a></li>\n";
		echo "							<li><a href=\"index.php?cmd=importClass\">Reactivate Class</a></li>\n";		
		echo "							<li><a href=\"index.php?cmd=editClass\">Edit Class</a></li>\n";
		echo "							<li><a href=\"index.php?cmd=deleteClass\">Delete Class</a></li>\n";
		echo "						</ul>\n";
		echo "					</td>\n";
		
		echo "					<td width=\"33%\" class=\"borders\">\n";
		echo "						<ul>\n";
		echo "							<li><a href=\"index.php?cmd=copyClass\">Copy Reserve List or Merge Classes</a></li>\n";
		echo "							<li><a href=\"index.php?cmd=editCrossListings\">Edit Crosslistings</a></li>\n";
		echo "							<li><a href=\"index.php?cmd=exportClass\">Export a Class to Blackboard, etc.</a></li>\n";
		echo "						</ul>\n";
		echo "					</td>\n";
		
		//echo "					<td width=\"33%\" class=\"borders\">\n";
		//echo "						&nbsp;<ul>\n";
		//echo "							<!--<li><a DISABLED href=\"index.php?cmd=addDept\">Add Department</a>--><!--Goes to staff-mngClass-createDept1.html --></li>\n";
		//echo "							<!--<li><a DISABLED href=\"index.php?cmd=editDept\">Edit a Department</a>--><!--Goes to staff-mngClass-editDept1.html --></li>\n";
		//echo "						</ul>\n";
		//echo "					</td>\n";
		echo "				</tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr><td>&nbsp;</td></tr>\n";
		echo "</table>\n";
	}
	
	
	function displayEditClassHeader(&$ci, $next_cmd, $show_quicklinks_box=true) {
		global $u, $g_permission, $calendar;

		//grab all the necessary info
		$ci->getCourseForUser();
		$crosslistings = $ci->getCrossListings();
		$instructors = $ci->getInstructors();
		$proxies = $ci->getProxies();

		//build crosslistings display string
		$crosslistings_string = '';
		if(empty($crosslistings)) {
			$crosslistings_string = 'None';
		}
		else {
			foreach($crosslistings as $crosslisting) {
				$crosslistings_string .= ', '.$crosslisting->displayCourseNo().' '.$crosslisting->getName();
			}
			$crosslistings_string = ltrim($crosslistings_string, ', ');	//trim off the first comma
		}
		
		//build instructors display string; assume there is at least one
		$instructors_string = '';
		foreach($instructors as $instructor) {
			$instructors_string .= ', <a href="mailto:'.$instructor->getEmail().'">'.$instructor->getName(false).'</a>';
		}
		$instructors_string = ltrim($instructors_string, ', ');	//trim off the first comma
		
		//build proxies display string
		$proxies_string = '';
		if(empty($proxies)) {
			$proxies_string = 'None';
		}
		else {
			foreach($proxies as $proxy) {
				$proxies_string .= ', '.$proxy->getName(false);
			}
			$proxies_string = ltrim($proxies_string, ', ');	//trim off the first comma
		}
		
?>
		<div style="text-align:right;"><strong><a href="index.php">Exit class</a></strong></div>
		<p />
		
		<div>			
			<div id="courseInfo">
				<div class="courseTitle"><?=$ci->course->displayCourseNo() . " " . $ci->course->getName()?>&nbsp;<small>[ <a href="index.php?cmd=editTitle&amp;ci=<?=$ci->getCourseInstanceID()?>" class="editlinks">edit</a> ]</small></div>
			
				<div class="courseHeaders"><span class="label"><?=$ci->displayTerm()?></span></div>
			
				<div class="courseHeaders">
					<span class="label">Cross-listings&nbsp;</span><small>[ <a href="index.php?cmd=editCrossListings&ci=<?=$ci->getCourseInstanceID()?>" class="editlinks">edit</a> ]</small>: <?=$crosslistings_string?>
				</div>
				
				<div class="courseHeaders"><span class="label">Instructor(s)&nbsp;<small></span>[ <a href="index.php?cmd=editInstructors&ci=<?=$ci->getCourseInstanceID()?>" class="editlinks">edit</a> ]</small>: <?=$instructors_string?>
				</div>
				
				<div class="courseHeaders"><span class="label">Proxies&nbsp;</span><small>[ <a href="index.php?cmd=editProxies&ci=<?=$ci->getCourseInstanceID()?>" class="editlinks">edit</a> ]</small>: <?=$proxies_string?>
				</div>
				
				<div class="courseHeaders"><span class="label">Enrollment&nbsp;</span><small>[ <a href="index.php?cmd=<?=$next_cmd?>&amp;ci=<?=$ci->getCourseInstanceID()?>&amp;tab=enrollment" class="editlinks">view</a> ]</small>: <span class="<?=common_getEnrollmentStyleTag($ci->getEnrollment())?>"><?=strtoupper($ci->getEnrollment())?></span></div>

<?php	if($u->getRole() >= $g_permission['staff']): 	//hide activate/deactivate dates from non-staff ?>
				<div class="courseHeaders">
					<form name="change_status_form" action="index.php" method="post">
						<input type="hidden" name="cmd" value="<?=$next_cmd?>" />
						<input type="hidden" name="ci" value="<?=$ci->getCourseInstanceID()?>" />
						
						<span class="label">Class Status</span>:
						<input type="radio" name="status" value="ACTIVE" <?php echo ($ci->getStatus()=='ACTIVE') ? 'checked="true"' : 'moo'; ?> /> <span class="<?=common_getStatusStyleTag('ACTIVE')?>">ACTIVE</span>
						<input type="radio" name="status" value="INACTIVE" <?php echo (($ci->getStatus()=='INACTIVE') || ($ci->getStatus()=='AUTOFEED')) ? 'checked="true"' : ''; ?> /> <span class="<?=common_getStatusStyleTag('INACTIVE')?>">INACTIVE</span>
<?php 		if($ci->getStatus()=='AUTOFEED'): ?>
						(Added by Registrar)
<?php 		endif; ?>

<?php		if($ci->getStatus()=='CANCELED'): ?>
						<input type="radio" name="status" disabled="true" checked="true" /> <span class="inprocess">CANCELED BY REGISTRAR</span>
<?php		endif; ?>

						<input type="submit" name="updateClassStatus" value="Change Status">
					</form>				
					<p />
					<form name="change_dates_form" action="index.php" method="post">
						<input type="hidden" name="cmd" value="<?=$next_cmd?>" />
						<input type="hidden" name="ci" value="<?=$ci->getCourseInstanceID()?>" />
						<span class="label">Class Active Dates</span>: <input type="text" id="activation" name="activation" size="10" maxlength="10" value="<?=$ci->getActivationDate()?>" /> <?=$calendar->getWidgetAndTrigger('activation', $ci->getActivationDate())?> to <input type="text" id="expiration" name="expiration" size="10" maxlength="10" value="<?=$ci->getExpirationDate()?>" /> <?=$calendar->getWidgetAndTrigger('expiration', $ci->getExpirationDate())?> <input type="submit" name="updateClassDates" value="Change Dates">
					</form>
				</div>			
<?php	endif; ?>
			
			</div>			

<?php	if($show_quicklinks_box): ?>
			<div id="courseActions">
				<script language="JavaScript">
					function submit_tsv_export_form() {
						if(document.getElementById('tsv_export_form')) {
							document.getElementById('tsv_export_form').submit();
						}
						return false;
					}
				</script>
				<ul>
					<li><a href="javascript:openWindow('no_control=1&cmd=previewStudentView&amp;ci=<?=$ci->getCourseInstanceID()?>','width=800,height=600');">Preview Student View</a></li>
					<li><a href="index.php?cmd=exportClass&amp;ci=<?=$ci->getCourseInstanceID()?>">Export readings to Courseware</a></li>
					<li><a href="#" onclick="return submit_tsv_export_form();">Export class to Spreadsheet</a></li>
				</ul>
			</div>
<?php	endif; ?>
		
		</div>		
		<div class="clear"></div>

		<? $color = ($ci->reviewed() ? 'green' : 'red'); ?> 
		<div class="courseHeaders">
			<span class="label">Copyright Approved</span>: <span style="color: <?=$color?>;"><?= $ci->getReviewed(); ?></span>
		</div>		
		<br/>

			
<?php		
	} //displayEditClassHeader()
	
	
	function displayEditClassReservesList(&$ci, $next_cmd, $show_students_pending_warning=false) {
		global $u, $g_permission, $g_siteURL;
		
		$students_pending_warning = $show_students_pending_warning ? '<span class="alert">&nbsp;&nbsp;&nbsp;! students requesting to join class !</span>' : '';
		
		//get reserves as a tree + recursive iterator
		$tree_walker = $ci->getReservesAsTreeWalker('getReserves');
		
?>
		<script languge="JavaScript">
			//a bit of a hack to highlight all <span>s with class="highlightable"
			function highlightAll() {				
				var items = document.getElementsByTagName("span");
				
				for(var x=0; x<items.length; x++) {
					if(items[x].className == "highlightable") {
						items[x].style.background = "yellow";
					}
				}
			}
		</script>
		
		<div>
			[ <a href="index.php?cmd=customSort&ci=<?=$ci->getCourseInstanceID()?>&parentID=" class="editlinks">sort main list</a> ]
			[ <a href="index.php?cmd=addReserve&ci=<?=$ci->getCourseInstanceID()?>" class="editlinks">add new materials</a> ]
			[ <a href="index.php?cmd=editHeading&ci=<?=$ci->getCourseInstanceID()?>" class="editlinks">add new heading</a> ]
			[ <a href="#" class="editlinks" onclick="highlightAll(); return false;">highlight reserve links</a> ]
		
		</div>
		
		<div class="contentTabs">
			<ul>
				<li class="current"><a href="index.php?cmd=<?=$next_cmd?>&amp;ci=<?=$ci->getCourseInstanceID()?>">Course Materials</a></li>
				<li><a href="index.php?cmd=<?=$next_cmd?>&amp;ci=<?=$ci->getCourseInstanceID()?>&amp;tab=enrollment">Enrollment<?=$students_pending_warning?></a></li>	
			</ul>
		</div>
		<div style="float:right;">
			<a href="javascript:checkAll(document.forms.editReserves, 1)">check all</a> | <a href="javascript:checkAll(document.forms.editReserves, 0)">uncheck all</a>
		</div>
		<div class="clear"></div>
		
		<div id="course_materials_block">
			<form method="post" name="editReserves" action="index.php">		
				<input type="hidden" name="cmd" value="editMultipleReserves" />
				<input type="hidden" name="ci" value="<?=$ci->getCourseInstanceID()?>" />
				
			<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
				<tr valign="middle">
					<td class="headingCell1" align="right" colspan="2">
						<div class="editOptionsTitles">
							<div class="itemNumber">
								#
							</div>	
							<div class="checkBox">
								Select
							</div>
							<div class="sortBox">
								Sort
							</div>	
							<div class="editBox">
								Edit
							</div>	
							<div class="statusBox">
								Status
							</div>					
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<ul style="list-style:none; padding-left:0px; margin:0px;">
<?php
		//begin displaying individual reserves and building dataSet for TSV export
		//loop
		$prev_depth = 0;
		$counter = 0;
        $i=0;
        $fields = array("author", "title", "source", "volumeTitle", "volumeEdition", "pagesTimes", "performer");
		foreach($tree_walker as $leaf) {
			//close list tags if backing out of a sublist
			if($prev_depth > $tree_walker->getDepth()) {
				echo str_repeat('</ul></li>', ($prev_depth-$tree_walker->getDepth()));
			}
			
			$reserve = new reserve($leaf->getID());	//init a reserve object
			$reserve->getItem();	//pull item info
			
			//set edit link and status
			if($reserve->item->isHeading()) {
				//set edit link
	        	$editURL = "index.php?cmd=editHeading&ci=".$ci->getCourseInstanceID()."&headingID=".$reserve->getReserveID();
	        	//set the status
	        	$status = 'HEADING';
            }
            else {
            	//edit link
           		$editURL = 'index.php?cmd=editItem&reserveID='.$reserve->getReserveID();
            	//status
            	$status = $reserve->getStatus();
            	//if the reserve is not supposed to be active yet, hide it
            	if(($status=='ACTIVE') && ($reserve->getActivationDate() > date('Y-m-d'))) {
            		$status = 'HIDDEN';
            	}
            }
            //do not show edit link for physical items unless viewed by staff
            $reserve->edit_link = (!$reserve->item->isPhysicalItem() || $u->getRole() >= $g_permission['staff']) ? '<a href="'.$editURL.'"><img src="images/pencil-gray.gif" border="0" alt="edit"></a>' : '';
			$reserve->status = $status;	//pass the status
            $reserve->counter = ++$counter;	//increment and pass the counter
            
            //if this reserve is a non-empty folder, set sort link
            $reserve->sort_link = ($leaf->numChildren() > 1) ? '<a href="index.php?cmd=customSort&amp;ci='.$ci->getCourseInstanceID().'&amp;parentID='.$leaf->getID().'"><img src="images/sort.gif" border="0" alt="sort contents"></a>' : '';
            
            //show plain-text links to electronic reserves
           	if(!$reserve->item->isPhysicalItem()) {	//only needed for electronic items
           		$reserve->additional_info = '<br /><span style="font-weight:bold; color:#333333;">Link to this item:</span> <span class="highlightable">'.$g_siteURL.'/reservesViewer.php?reserve='.$reserve->getReserveID().'</span>';
			}
            			
			$rowStyle = ($rowStyle=='oddRow') ? 'evenRow' : 'oddRow';	//set the style

			//display the info
			echo '<li>';
			self::displayReserveRowEdit($reserve, 'class="'.$rowStyle.'"');
			
			//start sublist or close list-item?
			echo ($leaf->hasChildren()) ? '<ul style="list-style:none;">' : '</li>';
			
            //append to TSV dataSet
            foreach ($fields as $key => $field) 
                $dataSet[$i]["$field"] = $reserve->item->$field;
                $dataSet[$i]["url"] = $g_siteURL . "/reservesViewer.php?reserve=" . $reserve->reserveID;
            $i++;
            
			$prev_depth = $tree_walker->getDepth();
		}
		echo str_repeat('</ul></li>', ($prev_depth));	//close all lists
?>

						</ul>
					</td>
				</tr>
				<tr valign="middle">
					<td class="headingCell1" style="text-align:right; padding:2px;" align="right" colspan="2">
						<input type="submit" name="delete_multiple" value="Delete Selected" />
						<input type="submit" name="copy_multiple" value="Copy Selected to Another Class" />
						<input type="submit" name="edit_multiple" value="Edit Selected" />
					</td>
				</tr>	
				<? if ($u->getRole() >= $g_permission['staff']) { ?>		
					<tr>
						<? if (!$ci->reviewed()) { ?>
						<td class="headingCell1" style="text-align:left; padding:2px;" align="left">						
							<input type="submit" name="approve_copyright" value="Set Copyright Reviewed" />
						</td>
						<? } ?>												
						<td class="headingCell1" style="text-align:right; padding:2px;" align="right">						
							<input type="submit" name="copyright_deny_class" value="Deny Use For This Class" />
							<input type="submit" name="copyright_deny_all_classes" value="Deny Use For All Classes" />
						</td>				
					</tr>
				<? } ?>					
			</table>
			</form>
			<p />
			<form method="post" id="tsv_export_form" name="tsv_export_form" action="tsvGenerator.php">
				<input type="hidden" name="dataSet" value="<?=base64_encode(serialize($dataSet))?>">
            </form>
		</div>
<?php
	} //displayEditClassReservesList()
	
	
	function displayEditClassEnrollment(&$ci, &$roll, $next_cmd) {		
		
	$pending_roll = array();
	foreach ($roll as $courseRoll)
	{
		if (key_exists('PENDING', $courseRoll))
			array_push($pending_roll, $courseRoll['PENDING']);
	}
	//echo "<pre>";print_r($pending_roll);echo"</pre>";

?>
	<form method="post" name="editReserves" action="index.php">		
		<input type="hidden" name="cmd" value="<?=$next_cmd?>" />
		<input type="hidden" name="ci" value="<?=$ci->getCourseInstanceID()?>" />
		<input type="hidden" name="tab" value="enrollment" />

		<div class="contentTabs">
			<ul>
				<li><a href="index.php?cmd=<?=$next_cmd?>&amp;ci=<?=$ci->getCourseInstanceID()?>">Course Materials</a></li>
				<li class="current"><a href="index.php?cmd=<?=$next_cmd?>&amp;ci=<?=$ci->getCourseInstanceID()?>&amp;tab=enrollment">Enrollment</a></li>	
			</ul>
		</div>
		<div class="clear"></div>
		
		<div id="enrollment_block" class="borders">
			<div id="class_enrollment" class="classEnrollmentOptions">
				<strong>Enrollment Type:</strong>
				<?php self::displayEnrollmentSelect($ci->getEnrollment(), true); ?>
				<input type="submit" name="setEnrollment" value="Set Enrollment" style="margin-top:5px;">
			</div>
			<div id="class_roll" class="classRoll">
				<div class="classRollPending">
					<strong>Add a new student to this class:</strong>
					<br />
<?php
		//ajax user lookup
		$mgr = new ajaxManager('lookupUser', null, null, null, null, false, array('min_user_role'=>0, 'field_id'=>'student_id'));
		$mgr->display();
?>
					<br />
					<small>Select a name from the menu and click "Add Student to Roll"</small>
					<br />
					<input type="hidden" name="rollAction" id="rollActionAdd" value="" />
					<input type="submit" name="submit" value="Add Student to Roll" onclick="javascript: document.getElementById('rollActionAdd').value='add';" style="margin-top:5px;" />
					<p />

	
<?php	if(!empty($pending_roll[0])): ?>
					<strong>Students requesting to join this class:</strong>
					<table align="center" class="simpleList">
						<tr>
							<td colspan="2" style="text-align:center;">
								<a href="index.php?cmd=<?=$next_cmd?>&amp;ci=<?=$ci->getCourseInstanceID()?>&amp;tab=enrollment&amp;rollAction=add&amp;student_id=all">approve all</a> | <a href="index.php?cmd=<?=$next_cmd?>&amp;ci=<?=$ci->getCourseInstanceID()?>&amp;tab=enrollment&amp;rollAction=deny&amp;student_id=all">deny all</a>
							</td>
						</tr>
<?php		foreach($pending_roll[0] as $student): ?>
						<tr bgcolor="#FFFFFF">
							<td width="60%">
								<?=$student->getName()?>
							</td>
							<td width="40%" align="center">
								<a href="index.php?cmd=<?=$next_cmd?>&amp;ci=<?=$ci->getCourseInstanceID()?>&amp;tab=enrollment&amp;rollAction=add&amp;student_id=<?=$student->getUserID()?>">approve</a> | <a href="index.php?cmd=<?=$next_cmd?>&amp;ci=<?=$ci->getCourseInstanceID()?>&amp;tab=enrollment&amp;rollAction=deny&amp;student_id=<?=$student->getUserID()?>">deny</a>
							</td>
						</tr>
<?php		endforeach; ?>
					</table>
<?php 	else: ?>
					<strong>There are no enrollment requests.</strong>
<?php	endif; ?>			
				</div><!-- classRollPending -->
				<div style="clear:both;" ></div><!-- hack to clear floats -->				
<?php 
  if (!empty($roll)):	
  	 $i = 0;		
  	 $ca_ids = array_keys($roll);
	 foreach ($roll as $courseRoll): 
		
		$course = new course($ca_ids[$i]);
		$autofed_roll  = $courseRoll['AUTOFEED'];
		$approved_roll = $courseRoll['APPROVED'];
		
		if ($i % 4 == 0) //carriage return div after 4 
			echo "<div style=\"clear:both;\" ></div><!-- hack to clear floats -->";
		$i++;			
	?>
				
				<div class="classRollActive">
	<?php	if(!empty($autofed_roll) || !empty($approved_roll)): ?>
						<strong>Currently enrolled in <?php echo $course->displayCourseNo() ?>:</strong>
						<table align="center" class="simpleList">
	<?php		if(!empty($autofed_roll)): ?>
							<tr>
								<td colspan="2">
									<strong>Students added by the Registrar:</strong>
								</td>
							</tr>
	<?php			foreach($autofed_roll as $student): ?>
							<tr>
								<td colspan="2">
									<?=$student->getName()?>
								</td>
							</tr>
	<?php
					endforeach;
				endif;
			
				if(!empty($approved_roll)):
					if(!empty($autofed_roll)):	//only show the label if there are both kinds of students (autofed + manual)
	?>
							<tr><td colspan="2" align="center"><strong>* * *</strong></td></tr>
							<tr>
								<td colspan="2">
									<strong>Manually-added students:</strong>
								</td>
							</tr>
	<?php		
					endif;
					foreach($approved_roll as $student):
	?>
							<tr>
								<td width="80%">
									<?=$student->getName()?>
								</td>
								<td width="20%" align="center">
									<a href="index.php?cmd=<?=$next_cmd?>&amp;ci=<?=$ci->getCourseInstanceID()?>&amp;tab=enrollment&amp;rollAction=remove&amp;student_id=<?=$student->getUserID()?>">remove</a>
								</td>
							</tr>
	<?php		
					endforeach;
				endif;
	?>
						</table>
	<?php	else: ?>
						<strong>There are no enrolled students.</strong>
	<?php	endif; ?>	
					</div>
	<?php 
		endforeach; 				
				echo "<div class=\"clear\"></div></div>";
			echo "</div>";
	endif;
	?>
	</form>

					
<?php
	} //displayEditClassEnrollment()
	
	
	function displayEditClass(&$ci, $next_cmd, $tab=null) {
		//get the class roll	
		$roll = $ci->getRoll();
		//show a warning if there are students pending enrollment approval
		//check pending array
		$show_students_pending_warning = (!empty($roll['PENDING'])) ? true : false;		
		
		if($tab=='enrollment') {	//display enrollment screen
			self::displayEditClassHeader($ci, $next_cmd, false);	//display header without the quicklinks box
			self::displayEditClassEnrollment($ci, $roll, $next_cmd);	//display enrollment info
		}
		else {	//display reserves list screen
			self::displayEditClassHeader($ci, $next_cmd, true);	//display header with the quicklinks box
			self::displayEditClassReservesList($ci, $next_cmd, $show_students_pending_warning);	//display reseves list
		}
		
		//display footer
?>
		<p />
		<div style="text-align:right;"><strong><a href="index.php">Exit class</a></strong></div>
<?php
	}
	
	/**
	 * @return void
	 * @param string $cmd currently executing cmd
	 * @param CourseInstance $ci course_instance object being edited
	 * @param string $msg Helper message to display above the form
	 * @desc Displays form for editting title and crosslistings
	 */	

	function displayEditTitle($cmd, $ci, $deptID, $msg=null, $potential_xlistings=null)
	{
		global $u, $g_permission;
		
		if(!is_null($msg)) {
			echo "<span class=\"helperText\">$msg</span><p />\n";
		}
		
		
		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "<tr>\n";
		echo "<td width =\"100%\" align=\"right\" valign=\"middle\"><!--<div align=\"right\" class=\"currentClass\">".$ci->course->displayCourseNo()."&nbsp;".$ci->course->getName()."</div>--></td>\n";
		echo "</tr>\n";
		echo " <form action=\"index.php?cmd=editTitle&ci=".$ci->getCourseInstanceID()."\" method=\"post\">\n";		
		echo " <tr>\n";
		echo " 	<td width=\"100%\"><img src=\images/spacer.gif\" width=\"1\" height=\"5\"> </td>\n";
		echo " </tr>\n";
		echo "	<tr><td colspan=\"3\" align=\"right\"> <a href=\"index.php?cmd=editClass&ci=".$ci->courseInstanceID."\">Return to Edit Class</a></div></td></tr>\n";
		echo " <tr>\n";
		echo " 	<td>\n";
		echo " 	<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		echo "     	<tr align=\"left\" valign=\"top\">\n";
		echo "         	<td width=\"40%\" class=\"headingCell1\">CLASS TITLE and CROSSLISTINGS</td>\n";
		echo " 			<td>&nbsp;</td>\n";
		echo " 		</tr>\n";
		echo " 	</table>\n";
		echo " 	</td>\n";
		echo "</tr>\n";
		echo " <tr>\n";
		echo " 	<td align=\"left\" valign=\"top\" class=\"borders\">\n";
		echo " 	<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\">\n";
		echo "     	<tr class=\"headingCell1\">\n";
		echo "      	<td width=\"8%\" align=\"center\" valign=\"middle\">Primary</td>\n";
		echo "          <td width=\"6%\" align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "          <td width=\"4%\" align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "          <td width=\"13%\" align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "          <td width=\"4%\" align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "          <td width=\"8%\" align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "          <td width=\"4%\" align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "          <td width=\"7%\" align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "          <td width=\"35%\" align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "          <td width=\"11%\" align=\"center\" valign=\"middle\">Delete</td>\n";
		echo "		</tr>\n";
		echo "		<tr class=\"evenRow\">\n";
		echo "			<td align=\"center\" valign=\"middle\"><input name=\"primaryCourse\" type=\"radio\" value=\"".$ci->course->courseAliasID."\" checked></td>\n";
		echo"			<INPUT TYPE=\"HIDDEN\" NAME=\"oldPrimaryCourse\" VALUE=\"".$ci->course->courseAliasID."\">\n";
		echo "          <td align=\"right\" valign=\"middle\" class=\"strong\">Dept:</td>\n";
		echo "          <td align=\"left\" valign=\"middle\">\n";

		self::displayDepartmentSelect($deptID, true, 'primaryDept');

		echo "     		</td>\n";
		echo "          <td align=\"right\" valign=\"middle\">Course#:</td>\n";
		echo "          <td align=\"left\" valign=\"middle\"> <input name=\"primaryCourseNo\" type=\"text\" size=\"5\" maxlength=\"8\" value=\"".$ci->course->getCourseNo()."\"></td>\n";
		echo "          <td align=\"right\" valign=\"middle\">Section:</td>\n";
		echo "          <td align=\"left\" valign=\"middle\"> <input name=\"primarySection\" type=\"text\" size=\"5\" maxlength=\"8\" value=\"".$ci->course->getSection()."\"></td>\n";
		echo "          <td align=\"right\" valign=\"middle\">Title:</td>\n";
		echo "          <td align=\"left\" valign=\"middle\"> <input name=\"primaryCourseName\" type=\"text\" size=\"25\" value=\"".$ci->course->getName()."\"></td>\n";
		echo "          <td align=\"center\" valign=\"middle\"></td>\n";
		echo "		</tr>\n";
		
		$rowNumber = 0;
		for ($i=0; $i<count($ci->crossListings); $i++) 
		{
			$rowClass = ($rowNumber % 2) ? "evenRow" : "oddRow\n";
			echo "		<tr class=\"".$rowClass."\"> \n";
			echo "			<td align=\"center\" valign=\"middle\"><!--<input type=\"radio\" name=\"primaryCourse\" value=\"".$ci->crossListings[$i]->courseAliasID."\">--></td>\n";
			echo "			<td align=\"right\" valign=\"middle\" class=\"strong\">Dept:</td>\n";
			echo "			<td align=\"left\" valign=\"middle\">\n";

			self::displayDepartmentSelect($ci->crossListings[$i]->deptID, true, 'cross_listings['.$ci->crossListings[$i]->courseAliasID.'][dept]');
			
			echo "			<td align=\"right\" valign=\"middle\">Course#:</td>\n";
			echo "			<td align=\"left\" valign=\"middle\"> <input name=\"cross_listings[".$ci->crossListings[$i]->courseAliasID."][courseNo]\" type=\"text\" size=\"5\" maxlength=\"8\" value=\"".$ci->crossListings[$i]->courseNo."\"></td>\n";
			echo "			<td align=\"right\" valign=\"middle\">Section:</td>\n";
			echo "			<td align=\"left\" valign=\"middle\"> <input name=\"cross_listings[".$ci->crossListings[$i]->courseAliasID."][section]\" type=\"text\" size=\"5\" maxlength=\"8\" value=\"".$ci->crossListings[$i]->section."\"></td>\n";
			echo "			<td align=\"right\" valign=\"middle\">Title:</td>\n";
			echo "			<td align=\"left\" valign=\"middle\"> <input name=\"cross_listings[".$ci->crossListings[$i]->courseAliasID."][courseName]\" type=\"text\" size=\"25\" value=\"".$ci->crossListings[$i]->getName()."\"></td>\n";
			echo "			<td align=\"center\" valign=\"middle\"><input type=\"checkbox\" name=\"deleteCrossListing[".$ci->crossListings[$i]->courseAliasID."]\" value=\"".$ci->crossListings[$i]->courseAliasID."\"></td>\n";
			echo "		</tr>\n";
			$rowNumber++;
		}
		
		echo "		<tr class=\"headingCell1\">\n";
		echo "			<td align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "			<td align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "			<td align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "			<td align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "			<td align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "			<td align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "			<td colspan=\"4\" align=\"left\" valign=\"top\"><div align=\"right\"><input type=\"submit\" name=\"updateCrossListing\" value=\"Update Course Info\">&nbsp;<input type=\"submit\" name=\"deleteCrossListings\" value=\"Delete Selected\"></div></td>\n";
		//echo "				<br>\n";
		echo "		</tr>\n";
		echo "  </table>\n";
		echo " </td>\n";
		echo " </tr>\n";
		//echo "</form>\n";
		echo " <tr>\n";
		echo " 	<td height=\"15\">&nbsp;</td>\n";
		echo " </tr>\n";
		//echo " <form action=\"index.php\" method=\"get\">\n";
		echo " <input type=\"hidden\" name=\"cmd\" value=\"editCrossListings\">\n";
		echo " <input type=\"hidden\" name=\"ci\" value=\"".$ci->getCourseInstanceID()."\">\n";
		echo " <tr> \n";
		echo " 	<td>\n";
		echo " 	<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		echo " 		<tr align=\"left\" valign=\"top\"> \n";
		echo " 			<td width=\"35%\" class=\"headingCell1\">ADD NEW CROSSLISTING</td>\n";
		echo " 			<td>&nbsp;</td>\n";
		echo " 		</tr>\n";
		echo " 	</table>\n";
		echo " 	</td>\n";
		echo " </tr>\n";		
		
		//SELECT EXISTING COURSE
		echo "		<tr> \n";
		echo "			<td align=\"left\" valign=\"top\" class=\"borders\" colspan=\"8\">\n";
		echo "     			<tr class=\"headingCell1\">\n";
		echo "      			<td colspan=\"8\" align=\"left\">SELECT EXISTING COURSE</td>\n";
		echo "				</tr>\n";		
		echo "			</td>\n";
		echo "	</td>\n";
		echo "</tr>\n";				
		
		//echo "   	</table>\n";
		//echo "		</td>\n";
		//echo " 	</tr>\n";		

		if (!isset($_REQUEST['xlist_new_course']))
		{
			//give list of possible xlistings or class selecter for staff or greater
			echo "</form>\n"; //close form so that class lookup will work
			self::displaySelectClass($cmd, $potential_xlistings, '', array('ci'=>$_REQUEST['ci'], 'addCrossListing' => 'true'), false, 'xlist_ci', 'index.php?cmd=editCrossListings&xlist_new_course=true&ci='.$ci->getCourseInstanceID());
		} else {
		//Create New Course
			echo "<tr> "
			."    	<td align=\"left\" valign=\"top\" class=\"borders\">\n"
			."    	<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\">\n"
			."      	<tr class=\"headingCell1\">"
			."          	<td colspan=\"8\" align=\"left\">CREATE NEW COURSE</td>\n"	
			."			</tr>\n"
			."          <tr> "
			."          	<td align=\"left\" valign=\"middle\" class=\"strong\"><div align=\"center\">Department:</div></td>\n"
			."              <td align=\"left\" valign=\"middle\">\n";
			
			self::displayDepartmentSelect(null, true, 'newDept');
	
			echo "          <td align=\"left\" valign=\"middle\" class=\"strong\"><div align=\"center\">Course Number:</div></td>\n"
			."              <td align=\"left\" valign=\"middle\"> <div align=\"left\"><input name=\"newCourseNo\" type=\"text\" id=\"Title2\" size=\"4\" maxlength=\"6\"></div></td>\n"
			."              <td align=\"left\" valign=\"middle\" class=\"strong\"><div align=\"center\">Section:</div></td>\n"
			."              <td align=\"left\" valign=\"middle\"> <div align=\"left\"><input name=\"newSection\" type=\"text\" size=\"4\" maxlength=\"6\"></div></td>\n"
			."              <td align=\"left\" valign=\"middle\" class=\"strong\"><div align=\"center\">Title:</div></td>\n"
			."          	<td align=\"left\" valign=\"middle\"> <div align=\"left\"><input name=\"newCourseName\" type=\"text\" size=\"30\"></div></td>\n"
			."			</tr>\n";	
			
			echo "      <tr class=\"headingCell1\">\n";
			echo "          <td align=\"left\" valign=\"middle\" colspan=\"8\"><div align=\"right\"><input type=\"submit\" name=\"addCrossListing\" value=\"Add Crosslisting\"></div></td>\n";
			echo "   	</tr>\n";				
		}

		
		echo "<tr>\n"
		."          <td>&nbsp;</td>\n"
		."        </tr>\n"
		."        <tr>\n"
		."          <td><div align=\"center\"><a href=\"index.php?cmd=editClass&ci=".$ci->courseInstanceID."\">Return to Edit Class</a></div></td>\n"
		."        </tr>\n"
		."        <tr>\n"
		."          <td><img src=\images/spacer.gif\" width=\"1\" height=\"15\"></td>\n"
		."        </tr>\n"
		." </table>\n";
		echo "</form>\n";
	}

	function displayEditInstructors($ci, $addTableTitle, $dropDownDefault, $userType, $removeTableTitle, $removeButtonText, $request)
	{
		echo " <table width=\"95%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">";
		echo "<form name=\"editInstructors\" action=\"index.php?cmd=editInstructors&ci=".$ci->courseInstanceID."\" method=\"post\">";
		echo "<tr>";
		echo "<td colspan=\"3\" align=\"right\" valign=\"middle\"><!--<div align=\"right\" class=\"currentClass\">".$ci->course->displayCourseNo()."</div>--></td>";
		echo " 	</tr>";
		/* Use this logic if we decide to display the course numbers for the cross listings
			for ($i=0; $i<count($ci->crossListings); $i++) {
				echo "<tr>";
				echo "<td colspan=\"3\" align=\"right\" valign=\"middle\"><div align=\"right\" class=\"currentClass\">".$ci->crossListings[$i]->displayCourseNo()."</div></td>";
				echo " 	</tr>";
			}
		*/
		echo " 	<tr>";
		echo " 		<td colspan=\"3\"><img src=\images/spacer.gif\" width=\"1\" height=\"5\"> </td>";
		echo " 	</tr>";
		echo " 	<tr>";
		echo " 		<td height=\"14\" colspan=\"3\" align=\"left\" valign=\"top\">";
		echo " 		<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
		echo "         	<tr>";
		echo "             	<td width=\"35%\" align=\"left\" valign=\"top\" class=\"headingCell1\">".$addTableTitle."</td>";
		echo " 				<td align=\"left\" valign=\"top\">&nbsp;</td>";
		echo " 				<td width=\"35%\" align=\"left\" valign=\"top\" class=\"headingCell1\">".$removeTableTitle."</td>";
		echo " 			</tr>";
		echo " 		</table>";
		echo " 		</td>";
		echo " 	</tr>";
		echo "     <tr>";
		echo "     	<td width=\"50%\" align=\"left\" valign=\"top\">";
		echo "     	<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"borders\">";
		echo " 			<tr>";
		echo "             	<td align=\"left\" valign=\"top\">";
		echo "             	<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" class=\"displayList\">";
		echo "                 	<tr align=\"left\" valign=\"middle\">";
		echo "                   		<td bgcolor=\"#FFFFFF\" class=\"headingCell1\">&nbsp;</td>";
		echo "                 	</tr>";
		echo "                 	<tr align=\"left\" valign=\"middle\">";
		echo "                   		<td width=\"88%\" bgcolor=\"#CCCCCC\"><div align=\"center\">";
		/*
		echo "                	    		<select name=\"prof\">";
		echo "                           		<option value=\"\" selected>-- ".$dropDownDefault." --</option>";
								foreach($instructorList as $instructor)
								{
									echo "<option value=\"" . $instructor["user_id"] . "\">" . $instructor["full_name"] . "</option>";
								}
		echo "                       		</select>";
		*/
		$selectClassMgr = new lookupManager('','lookupInstructor', $u, $request);
		$selectClassMgr->display();
		echo "                   		</div></td>";

		echo "                 	</tr>";
		echo "                 	<tr align=\"left\" valign=\"middle\">";
		echo "                   		<td class=\"headingCell1\">&nbsp;</td>";
		echo "                 	</tr>";
		echo "                 	<tr align=\"left\" valign=\"middle\">";
		echo "                   		<td><div align=\"center\"><input type=\"submit\" name=\"add".$userType."\" value=\"Add ".$userType."\"></div></td>";
		echo "                 	</tr>";
		echo "               </table>";
		echo "               </td>";
		echo " 			</tr>";
		echo " 		</table>";
		echo " 		</td>";
		echo "         <td align=\"left\" valign=\"top\"><img src=\images/spacer.gif\" width=\"15\" height=\"1\"></td>";
		echo "         <td width=\"50%\" align=\"left\" valign=\"top\">";
		echo "         <table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"borders\">";
		echo "         	<tr>";
		echo "             	<td align=\"right\" valign=\"top\">";
		echo "             	<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" class=\"displayList\">";
		echo "                 	<tr align=\"left\" valign=\"middle\">";
		echo "                   		<td bgcolor=\"#FFFFFF\" class=\"headingCell1\">&nbsp;</td>";
		echo "                   		<td class=\"headingCell1\">Remove</td>";
		echo "                 	</tr>";

					$rowNumber = 0;
					if ($userType=="Instructor") {
						$numInstructors = count($ci->instructorList);
						$instruct = $ci->instructorList;
					} elseif ($userType=="Proxy") {
						$numInstructors = count($ci->proxies);
						$instruct = $ci->proxies;
					}
					for($i=0;$i<$numInstructors;$i++) {
						$rowClass = ($rowNumber++ % 2) ? "evenRow" : "oddRow\n";
						echo "                 	<tr align=\"left\" valign=\"middle\" class=\"".$rowClass."\">";
						echo "                   		<td>".$instruct[$i]->getName()."</td>";
						echo "                   		<td width=\"8%\" valign=\"top\" class=\"borders\"><div align=\"center\">";
						if ($userType=="Instructor")
							echo "<input type=\"checkbox\" name=\"".$userType."[".$instruct[$i]->userID."]\" value=\"".$instruct[$i]->userID."\">";
						echo "&nbsp;</div></td>";
							
						echo "                 	</tr>";
					}
		echo "                 	<tr align=\"left\" valign=\"middle\">";
		echo "                   		<td colspan=\"2\"  class=\"headingCell1\">&nbsp;</td>";
		echo "                 	</tr>";
		echo "                 	<tr align=\"left\" valign=\"middle\">";
		echo "                   		<td colspan=\"2\"><div align=\"center\"><input type=\"submit\" name=\"remove".$userType."\" value=\"".$removeButtonText."\"></div></td>";
		echo "                 	</tr>";
		echo " 				</table>";
		echo " 				</td>";
		echo " 			</tr>";
		echo " 		</table>";
		echo " 		</td>";
		echo " 	</tr>";
		echo "     <tr>";
		echo "     	<td colspan=\"3\">&nbsp;</td>";
		echo " 	</tr>";
		echo "     <tr>";
		echo "     	<td colspan=\"3\"><div align=\"center\"><a href=\"index.php?cmd=editClass&ci=".$ci->courseInstanceID."\">Return to Edit Class</a></div></td>";
		echo " 	</tr>";
		echo "     <tr>";
		echo "     	<td colspan=\"3\"><img src=\images/spacer.gif\" width=\"1\" height=\"15\"></td>";
		echo " 	</tr>";
		echo " </form>";
		echo " </table>";
	}

	function displayEditProxies($ci, $proxyList, $request)
	{
		$ci->getPrimaryCourse();

		echo "<form action=\"index.php?cmd=editProxies&ci=".$ci->getCourseInstanceID()."\" method=\"POST\">\n";
		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr>\n";
		echo "		<td colspan=\"3\" align=\"right\"> <a href=\"index.php?cmd=editClass&ci=". $ci->getCourseInstanceID() ."\" class=\"strong\">Return to Class</a></div></td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td colspan=\"3\">&nbsp;</td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td height=\"14\" colspan=\"3\" align=\"left\" valign=\"top\">\n";
		echo "			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		echo "				<tr>\n";
		echo "					<td width=\"35%\" align=\"left\" valign=\"top\" class=\"headingCell1\">ADD A PROXY</td>\n";
		echo "					<td align=\"left\" valign=\"top\">&nbsp;</td>\n";
		echo "					<td width=\"35%\" align=\"left\" valign=\"top\" class=\"headingCell1\">CURRENT PROXIES</td>\n";
		echo "				</tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td width=\"50%\" align=\"left\" valign=\"top\">\n";
		echo "			<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"borders\">\n";
		echo "				<tr>\n";
		echo "					<td align=\"left\" valign=\"top\">\n";
		echo "						<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" class=\"displayList\">\n";
		echo "							<tr align=\"left\" valign=\"middle\"><td bgcolor=\"#FFFFFF\" class=\"headingCell1\">&nbsp;</td></tr>\n";
		echo "							<tr align=\"left\" valign=\"middle\">\n";
		echo "								<td bgcolor=\"#CCCCCC\" align=\"left\"><p align=\"center\"><span class=\"strong\">Search by: </span>\n";
		echo "									<select name=\"queryTerm\"><option selected value=\"last_name\">Last Name</option><option value=\"username\">User Name</option></select>\n";
		echo "									&nbsp;\n";
		echo "									<input name=\"queryText\" type=\"text\" value=\"".$request['queryText']."\" size=\"15\">&nbsp;\n";
		echo "									<input type=\"submit\" name=\"search\" value=\"Search\"></p>\n";
		echo "								</td>\n";
		echo "							</tr>\n";
		echo "							<tr align=\"left\" valign=\"middle\">\n";
		echo "								<td width=\"88%\" height=\"68\" valign=\"top\" bgcolor=\"#CCCCCC\" align=\"center\">\n";


		$addProxyDisabled = "DISABLED";
		if (is_array($proxyList) && !empty($proxyList)){
			$addProxyDisabled = "";
			echo "									<hr align=\"center\" width=\"150\">\n";
			echo "									<span class=\"strong\">Search Results:</span>\n";
			echo "									<select name=\"proxy\" onChange='this.form.addProxy.disabled=false;'>\n";
			foreach($proxyList as $proxy)
			{
				echo "										<option value=\"".$proxy->getUserID()."\">".$proxy->getName()."</option>\n";
			}
			echo "									</select>\n";
		}

		echo "								</td>\n";
		echo "							</tr>\n";
		echo "							<tr align=\"left\" valign=\"middle\"><td class=\"headingCell1\">&nbsp;</td></tr>\n";
		echo "							<tr align=\"left\" valign=\"middle\">\n";
		echo "								<td align=\"center\"><input type=\"submit\" name=\"addProxy\" value=\"Add Proxy\" $addProxyDisabled></td>\n";
		echo "							</tr>\n";
		echo "						</table>\n";
		echo "					</td>\n";
		echo "				</tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "		<td align=\"left\" valign=\"top\"><img src=\images/spacer.gif\" width=\"15\" height=\"1\"></td>\n";
		echo "		<td width=\"50%\" align=\"left\" valign=\"top\">\n";
		echo "			<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"borders\">\n";
		echo "				<tr>\n";
		echo "					<td align=\"right\" valign=\"top\">\n";
		echo "						<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" class=\"displayList\">\n";
		echo "							<tr align=\"left\" valign=\"middle\"><td bgcolor=\"#FFFFFF\" class=\"headingCell1\">&nbsp;</td><td class=\"headingCell1\">Remove</td></tr>\n";

		if (is_array($ci->proxies) && !empty($ci->proxies))
		{
			foreach($ci->proxies as $proxy)
			{
//				echo "							<tr align=\"left\" valign=\"middle\"><td bgcolor=\"#FFFFFF\" class=\"headingCell1\">&nbsp;</td><td class=\"headingCell1\">Remove</td></tr>\n";
				echo "							<tr align=\"left\" valign=\"middle\">\n";
				echo "								<td bgcolor=\"#CCCCCC\">". $proxy->getName() ."</td>\n";
				echo "								<td width=\"8%\" valign=\"top\" bgcolor=\"#CCCCCC\" class=\"borders\" align=\"center\">\n";
				echo "									<input type=\"checkbox\" name=\"proxies[]\" value=\"".$proxy->getUserID()."\">\n";
				echo "								</td>\n";
				echo "							</tr>\n";
//				echo "							<tr align=\"left\" valign=\"middle\"><td colspan=\"2\"  class=\"headingCell1\">&nbsp;</td></tr>\n";
//				echo "							<tr align=\"left\" valign=\"middle\"><td colspan=\"2\" align=\"center\"><input type=\"submit\" name=\"removeProxy\" value=\"Remove Selected Proxies\"></td></tr>\n";
			}
		} else {
//			echo "							<tr align=\"left\" valign=\"middle\"><td bgcolor=\"#FFFFFF\" class=\"headingCell1\">&nbsp;</td><td class=\"headingCell1\">&nbsp;</td></tr>\n";
			echo "							<tr align=\"left\" valign=\"middle\"><td bgcolor=\"#CCCCCC\">This class currently has no proxies.</td><td width=\"8%\" valign=\"top\" bgcolor=\"#CCCCCC\" align=\"center\">&nbsp;</td></tr>\n";
//			echo "							<tr align=\"left\" valign=\"middle\"><td colspan=\"2\"  class=\"headingCell1\">&nbsp;</td></tr>\n";
		}
		echo "							<tr align=\"left\" valign=\"middle\"><td colspan=\"2\"  class=\"headingCell1\">&nbsp;</td></tr>\n";
		echo "							<tr align=\"left\" valign=\"middle\"><td colspan=\"2\" align=\"center\"><input type=\"submit\" name=\"removeProxy\" value=\"Remove Selected Proxies\"></td></tr>\n";
		echo "						</table>\n";
		echo "					</td>\n";
		echo "				</tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr><td colspan=\"3\">&nbsp;</td></tr>\n";
		echo "	<tr><td colspan=\"3\"><strong>To Add a Proxy:</strong><br>\n";
		echo "			<ol><li>Type the last name of the person you would like to add into the search box and click \"Search\".</li>\n";
		echo "				<li>A drop-down menu will appear with names that match your search. Choose a name from the menu and click the \"Add Proxy\" button.</li>\n";
		echo "				<li>The name of your proxy will appear on the right under \"Current Proxies\".</li>\n";
		echo "			</ol>\n";
		echo "	<tr><td colspan=\"3\">&nbsp;</td></tr>\n";
		echo "	<tr><td colspan=\"3\"><strong>To Remove a Proxy:</strong><br>\n";
		echo "			<ol><li>Under the \"Current Proxies\" list, check the box next to the name of the proxy you wish to remove.</li>\n";
		echo "				<li>Click the \"Delete Proxy\" button.</li>\n";
		echo "			</ol>\n";
		echo "		</td></tr>\n";
		echo "	<tr><td colspan=\"3\">&nbsp;</td></tr>\n";
		echo "	<tr><td colspan=\"3\" align=\"center\"> <a href=\"index.php?cmd=editClass&ci=". $ci->getCourseInstanceID() ."\" class=\"strong\">Return to Class</a></div></td></tr>\n";
		echo "	<tr><td colspan=\"3\"><img src=\images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
		echo "</table>\n";
		echo "</form>\n";
	}
	
	
	function displayCreateSuccess($ci_id) {
?>
		<div class="borders" style="text-align: center;">
			<div style="width:50%; margin:auto; text-align:left;">
				<strong>You have successfully created a class. What would you like to do now?</strong>
				<p />
				<ul>
					<li><a href="index.php?cmd=importClass&new_ci=<?=$ci_id?>">Import materials into this class from another class (Reactivate)</a></li>
					<li><a href="index.php?cmd=addReserve&ci=<?=$ci_id?>">Add new materials to this class</a></li>
					<li><a href="index.php?cmd=editClass&ci=<?=$ci_id?>">Go to this class.</a></li>
					<li><a href="index.php?cmd=createClass">Create a New Class.</a></li>
				</ul>
			</div>
		</div>
<?php
	}
	
	
	function displayActivateSuccess($ci_id) {
?>
		<div class="borders" style="text-align: center;">
			<div style="width:50%; margin:auto; text-align:left;">
				<strong>You are opening this class for the first time this semester.  What would you like to do?</strong>
				<p />
				<ul>
					<li><a href="index.php?cmd=importClass&new_ci=<?=$ci_id?>">Import materials into this class from another class (Reactivate)</a></li>
					<li><a href="index.php?cmd=addReserve&ci=<?=$ci_id?>">Add new materials to this class</a></li>
					<li><a href="index.php?cmd=editClass&ci=<?=$ci_id?>">Go to this class.</a></li>
					<li><a href="index.php?cmd=deactivateClass&ci=<?=$ci_id?>"><strong>Cancel</strong> - I do not wish students to see this class.</a></li>
					<li><a href="index.php?cmd=removeClass&ci=<?=$ci_id?>"><strong>Remove</strong> - I do not plan on teaching this class.</a></li>
				</ul>
			</div>
		</div>
<?php
	}
	
	
	/**
	 * @return void
	 * @param string $preproc_cmd Command that is originating a call to this method (where should the script return in case of duplicate)
	 * @param string $postproc_cmd Command to issue after the new class is successfully created [choices limited by switch() in classManager]
	 * @param array $hidden_fields Data to be passed on as hidden fields
	 * @param string $msg Helper message to display above the form
	 * @desc Displays form for creating a new class; sends data to classManager, which actually handles class creation and then forwards user to $postproc_cmd with the ID of the newly-created CI passed in $_REQUEST['new_ci'].
	 */	
	function displayCreateClass($preproc_cmd, $postproc_cmd=null, $hidden_fields=null, $msg=null) {
		global $u, $g_permission;
		
		//set defaults if they exists
		$department = !empty($_REQUEST['department']) ? $_REQUEST['department'] : '';
		$section = !empty($_REQUEST['section']) ? $_REQUEST['section'] : '';
		$course_number = !empty($_REQUEST['course_number']) ? $_REQUEST['course_number'] : '';
		$course_name = !empty($_REQUEST['course_name']) ? $_REQUEST['course_name'] : '';
		$term = !empty($_REQUEST['term']) ? $_REQUEST['term'] : '';
		$enrollment = !empty($_REQUEST['enrollment']) ? $_REQUEST['enrollment'] : '';
		
		//add the origin cmd and the next cmd to hidden fields
		//this will tell the manager where to return in case of a dupe
		//and where to proceed if class is created successfully
		$hidden_fields['preproc_cmd'] = $preproc_cmd;
		$hidden_fields['postproc_cmd'] = $postproc_cmd;
		
?>
		<script language="JavaScript">
			function validate(form) {
				var fieldCount = 0;
				var requiredFields=true;
				var errorMsg='The following fields are required: ';

				if (!(form.department.value)) {
					requiredFields=false;
					errorMsg = errorMsg + 'Department';
					fieldCount++;
				}

				if (!(form.course_number.value)) {
					requiredFields=false;
					if (fieldCount>0) errorMsg = errorMsg + ', ';
					errorMsg = errorMsg + 'Course Number';
					fieldCount++;
				}

				if (!(form.course_name.value)) {
					requiredFields=false;
					if (fieldCount>0) errorMsg = errorMsg + ', ';
					errorMsg = errorMsg + 'Course Name';
					fieldCount++;
				}
				
				if (!(form.term.value)) {
					requiredFields=false;
					if (fieldCount>0) errorMsg = errorMsg + ', ';
					errorMsg = errorMsg + 'Term';
					fieldCount++;
				}
		
				if (!(form.selected_instr.value)) {
					requiredFields=false;
					if (fieldCount>0) errorMsg = errorMsg + ', ';
					errorMsg = errorMsg + 'Instructor';
					fieldCount++;
				}

				if (!(form.activation_date.value)) {
					requiredFields=false;
					if (fieldCount>0) errorMsg = errorMsg + ', ';
					errorMsg = errorMsg + 'Activation Date';
					fieldCount++;
				}

				if (!(form.expiration_date.value)) {
					requiredFields=false;
					if (fieldCount>0) errorMsg = errorMsg + ', ';
					errorMsg = errorMsg + 'Expiration Date';
					fieldCount++;
				}

				if (requiredFields) {
					return true;
				} else {
					alert (errorMsg);
					return false;
				}
			}
		</script>
		
		<form name="frmClass" action="index.php" method="post" onSubmit="return validate(this);">	
			<input type="hidden" name="cmd" value="<?=$preproc_cmd?>" />			
			<?php self::displayHiddenFields($hidden_fields); ?>

<?php	if(!empty($msg)): ?>
			<span class="helperText"><?=$msg?></span><p />
<?php	endif; ?>
		
		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
			<tr>
				<td class="headingCell1" width="25%" align="center">CLASS DETAILS</td>
				<td width="75%" align="center">&nbsp;</td>
			</tr>
		    <tr>
		    	<td colspan="2" class="borders">
			    	<table width="100%" border="0" cellspacing="0" cellpadding="5">
			    		<tr>
			    			<td width="30%" bgcolor="#CCCCCC" align="right" class="strong">
			    				Department:
			    			</td>
			    			<td>
			    				<?php self::displayDepartmentSelect($department); ?>
			    			</td>
			    		</tr>
			    		<tr>
			    			<td width="30%" bgcolor="#CCCCCC" align="right" class="strong">
			    				Course Number:
			    			</td>
			    			<td>
			    				<input name="course_number" type="text" id="course_number" size="5" value="<?=$course_number?>" />
			    			</td>
			    		</tr>
			    		<tr>
			    			<td width="30%" bgcolor="#CCCCCC" align="right" class="strong">
			    				Section:
			    			</td>
			    			<td>
			    				<input name="section" type="text" id="section" size="5" value="<?=$section?>" />
			    			</td>
			    		</tr>
			    		<tr>
			    			<td width="30%" bgcolor="#CCCCCC" align="right" class="strong">
			    				Course Name:
			    			</td>
			    			<td>
			    				<input name="course_name" type="text" id="course_name" size="50" value="<?=$course_name?>" />
			    			</td>
			    		</tr>
			    		<tr>
			    			<td width="30%" bgcolor="#CCCCCC" align="right" class="strong">
			    				Term:
			    			</td>
			    			<td>
<?php
		//allow staff or above to edit start/end dates
		$show_dates = ($u->getRole() >= $g_permission['staff']) ? true : false;
	
		//show term selection
		self::displayTermSelect($term, $show_dates);
?>
			    			</td>
			    		</tr>
			    		<tr>
			    			<td width="30%" bgcolor="#CCCCCC" align="right" class="strong">
			    				Enrollment:
			    			</td>
			    			<td>
			    				<?php self::displayEnrollmentSelect($enrollment, true); ?>
			    			</td>
			    		</tr>
<?php	if($u->getRole() >= $g_permission['staff']): //show instructor lookup for staff ?>
			    		<tr>
			    			<td width="30%" bgcolor="#CCCCCC" align="right" class="strong">
			    				Instructor:
			    			</td>
			    			<td>
<?php
			//ajax lookup
			$mgr = new ajaxManager('lookupUser', null, null, null, null, false, array('min_user_role'=>3, 'field_id'=>'selected_instr'));
			$mgr->display();
?>
			    			</td>
			    		</tr>
<?php	 else:	//add instructor as hidden field ?>

						<input type="hidden" id="selected_instr" name="selected_instr" value="<?=$u->getUserID()?>" />

<?php	endif; ?>
			    	</table>
			    </td>
			</tr>
		</table>
		<p />
		<div style="text-align:center;"><input type="submit" name="Submit" value="Create Course" onClick="this.form.cmd.value='createNewClass';javascript:return validate(document.forms.frmClass);"></div>
<?php
	}

	
	function displaySelectDept_Instr($hidden_fields=null) {
		global $u, $g_permission;
		
		$depObj = new department();
?>
		<script>
			function checkInstructor() {
				var frm = document.getElementById('instructor_form');
				if(frm.selected_instr.options[frm.selected_instr.selectedIndex].value == '') {
					alert('Please select an instructor');
					return false;					
				}
				else {
					return true;					
				}
			}
		</script>
		
		<table width="100%" cellspacing="0" cellpadding="0" align="center">
			<tr class="headingCell1" align="center">
				<td>Search by Instructor</td>
				<td>Search by Department</td>
			</tr>
			<tr align="center">
				<td class="borders">
					<br />
					<form method="post" id="instructor_form" action="index.php">
						<input type="hidden" name="cmd" value="addClass" />
						<?php self::displayHiddenFields($hidden_fields); ?>
						
<?php
		$lookupMgr = new lookupManager('', 'lookupInstructor', $u, $_REQUEST);
		$lookupMgr->display();
?>
						<p />
						<input type="submit" name="submit_instructor" value="Look Up Classes" onclick="return checkInstructor();" />
					</form>
				</td>
				<td class="borders">
					<br />
					<form method="post" action="index.php">
						<input type="hidden" name="cmd" value="addClass" />
						<?php self::displayHiddenFields($hidden_fields); ?>
						
						<?php self::displayDepartmentSelect(); ?>

						<p />
						<input type="submit" name="submit_dept" value="Look Up Classes" />
					</form>
				</td>
			</tr>
		</table>
		<p />
<?php	if($u->getRole() >= $g_permission['instructor']): ?>
		<strong>Instructors:</strong> Adding a class through this page will only allow you to see that class as a student would. Classes that you are teaching show up automatically in your MyCourses list with a pencil icon next to them. If you do not see your class under your MyCourses list, you may try <a href="index.php?cmd=createClass">creating a class</a> or contacting the Reserves staff.
<?php		
		endif;			
	}


	function displayDeleteClass ($cmd, $u, $request) {
		//display selectClass
		$mgr = new ajaxManager('lookupClass', 'confirmDeleteClass', 'manageClasses', 'Delete Class');
		$mgr->display();
		//show warning
		echo '<div align="center" class="strong"><font color="#CC0000">CAUTION! Deleting a class cannot be undone!</font></div>';
	}

	function displayConfirmDelete ($sourceClass) {
		$roll_arrays = $sourceClass->getRoll();
		$roll = array_merge($roll_arrays['AUTOFEED'], $roll_arrays['APPROVED']);

		echo '<input type="hidden" name="ci" value="'.$sourceClass->getCourseInstanceID().'">';

		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo '<tr><td width="100%"><img src="images/spacer.gif" width="1" height="5"></td></tr>';

		echo '<tr>';
		echo 	'<td align="left" valign="top">';
        echo 	  '<p>';
        echo 		'<span class="courseTitle">';
        echo 			$sourceClass->course->displayCourseNo().' -- '.$sourceClass->course->getName().' ('.$sourceClass->displayTerm().')</span>,';
        echo 			' <span class="helperText">Instructors: ';
        echo $sourceClass->displayInstructors();
		echo 			'</span></p></td></tr>';
		echo '<tr><td>&nbsp;</td></tr>';
		echo '<tr>';
				echo 	'<td align="left" valign="top"><p><span class="helperText">'.count($roll).' Total Enrolled Students<br><br>';
				
				foreach($roll as $student) {
					echo '<strong>'.$student->getName().'</strong><br />';
				}

				echo 	'</p></td>';
        		echo '</tr>';
        echo '<tr><td>&nbsp;</td></tr>';
        echo '<tr>';
		echo 	'<td align="left" valign="top">';
        echo 	  '<p>';
        echo 		'<span class="failedText">';
        echo 'Are you sure you want to delete this class?';
        echo 		'</span>';
        echo 	  '</p>';

        echo 	  '<p>';
        echo 		'&gt;&gt;<a href="index.php?cmd=deleteClassSuccess&ci='.$sourceClass->getCourseInstanceID().'">Yes, Delete this class</a><br>';
        echo 		'&gt;&gt;<a href="index.php?cmd=deleteClass">No, Delete another class</a><br>';
        echo 		'&gt;&gt;<a href="index.php?cmd=manageClasses">No, Return to &quot;Manage Classes&quot; home</a><br>';
        echo 	  '</p>';
        echo 	'</td>';
        echo '</tr>';

        echo '<tr><td align="left" valign="top">&nbsp;</td></tr>';
		echo "</table>\n";

	}

	function displayDeleteSuccess ($sourceClass) {

		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo '<tr><td width="100%"><img src="images/spacer.gif" width="1" height="5"></td></tr>';

		echo '<tr>';
		echo 	'<td align="left" valign="top">';
        echo 	  '<p>';
        echo 		'<span class="strong">';
        echo 			$sourceClass->course->displayCourseNo().' -- '.$sourceClass->course->getName().' ('.$sourceClass->displayTerm().')';
        echo 		'</span>';
        echo 		'<span class="successText"> has been deleted.</span>';
        echo 	  '</p>';

        echo 	  '<p>';
        echo 		'&gt;&gt;<a href="index.php?cmd=deleteClass">Delete another class</a><br>';
        echo 		'&gt;&gt;<a href="index.php?cmd=manageClasses">Return to &quot;Manage Classes&quot; home</a><br>';
        echo 	  '</p>';
        echo 	'</td>';
        echo '</tr>';

        echo '<tr><td align="left" valign="top">&nbsp;</td></tr>';
		echo "</table>\n";

	}
	

	function displayCopyItemsSuccess ($targetClass, $originalClass, $numberCopied) {

		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo '<tr><td width="100%"><img src="images/spacer.gif" width="1" height="5"></td></tr>';

		echo '<tr>';
		echo 	'<td align="left" valign="top">';
        echo 	  '<p>';
        echo 		'<span class="successText">'.$numberCopied.' item(s) were copied from </span>';
        echo 		'<span class="strong">';
        echo 			$originalClass->course->displayCourseNo().' -- '.$originalClass->course->getName().' ('.$originalClass->displayTerm().')';
        echo 		'</span>';
        echo 		'<span class="successText"> to </span>';
        echo 		'<span class="strong">';
        echo 			$targetClass->course->displayCourseNo().' -- '.$targetClass->course->getName().' ('.$targetClass->displayTerm().')';
        echo 		'</span>';
        echo 	  '</p>';

        echo 	  '<p>';
        echo 		'&gt;&gt;<a href="index.php?cmd=editClass&ci='.$originalClass->getCourseInstanceID().'">Return to original class</a><br>';
        echo 		'&gt;&gt;<a href="index.php?cmd=editClass&ci='.$targetClass->getCourseInstanceID().'">Go to target class</a><br>';
        echo 	  '</p>';
        echo 	'</td>';
        echo '</tr>';
        
        echo '<tr><td align="left" valign="top">&nbsp;</td></tr>';
		echo "</table>\n";		
		
	}
	
	
	/**
	 * @return void
	 * @param array $student_CIs Reference to an array of CI objects this user is enrolled in
	 * @param array $intructor_CIs Reference to an array of CI objects this user is teaching
	 * @param array $proxy_CIs Reference to an array of CI objects this user is proxying
	 * @desc Display the user's courses
	 */
	public function displayCourseList(&$student_CIs, &$instructor_CIs, &$proxy_CIs) {
		global $u, $g_permission;	

		//need term info
		$termsObj = new terms();
		$terms = array();
		$term_blocks_string = '';
		
		//the idea is to separate instructor/proxy lists by term
		//and also order those terms (according to their sort order)
		$instructor_ci_array = array();
		$proxy_ci_array = array();
		foreach($termsObj->getTerms() as $term) {
			//rearrange the term info as Array[year][term] = term_obj_id to quickly index it by CI-year/term
			$terms[$term->getTermYear()][$term->getTermName()] = $term->getTermID();
			//also initialize these arrays, so that the term arrays are in proper order
			$instructor_ci_array[$term->getTermID()] = array();
			$proxy_ci_array[$term->getTermID()] = array();
		}	
		//put CIs in sub-arrays indexed by term_id
		foreach($instructor_CIs as $ci_status=>$CIs) {	//instructor courses
			foreach($CIs as $ci) {
				$instructor_ci_array[$terms[$ci->year][$ci->term]][$ci_status][] = $ci;
			}
		}		
		foreach($proxy_CIs as $ci) {	//proxy courses
			$proxy_ci_array[$terms[$ci->year][$ci->term]][] = $ci;
		}
		
		//the process used above to put all the terms in the correct order may have created a bunch of empty arrays
		//go through instructor/proxy arrays again, removing empty term/status arrays
		//instructor first
		$tmp = array();
		foreach($instructor_ci_array as $term_id=>$status_ci_arrays) {
			foreach($status_ci_arrays as $status=>$ci_array) {
				if(!empty($ci_array)) {
					$tmp[$term_id][$status] = $ci_array;
				}
			}
		}
		$instructor_ci_array = $tmp;
		//repeat for proxy
		$tmp = array();
		foreach($proxy_ci_array as $term_id=>$ci_array) {
			if(!empty($ci_array)) {
				$tmp[$term_id] = $ci_array;
			}
		}
		$proxy_ci_array = $tmp;
		
		//this will hold the jscript calls to select the initial tab view
		//it will be run after everything is rendered
		$onload_jscript = '';
		if(!empty($proxy_ci_array)) {	//show proxy on top			
			$onload_jscript .= "showBlock('proxy_tab', 'proxy_block');\n";
			//add call to preselect the first term sub-block
			$keys = array_keys($proxy_ci_array);
			$onload_jscript .= "showTermBlock('proxy_block_".$keys[0]."');\n";
		}
		if(!empty($student_CIs)) {	//show student on top
			$onload_jscript .= "showBlock('student_tab', 'student_block');\n";
		}
		if($u->getDefaultRole() >= $g_permission['instructor']) {	//show instructor on top
			$onload_jscript .= "showBlock('instructor_tab', 'instructor_block');\n";
			$keys = array_keys($instructor_ci_array);		
			$onload_jscript .= "showTermBlock('instructor_block_".$keys[0]."');\n";
		}
		if(empty($onload_jscript)) {	//hide everything
			$onload_jscript = "showBlock('student_tab', 'student_block');\n";
		}
		
		//note that by default, only the student block is visible
		//this is done so that if the user cannot process jscript, but does
		//recognize display:none style, then s/he will still see the student block
		
		//begin display
?>		

	<script language="JavaScript" type="text/javascript">
		var current_tab_id = 'student_tab';
		var current_block_id = 'student_block';
		var current_term_blocks = new Array();
		
		/**
		 * @return false
		 * @param strin tab_id - id of the tab
		 * @param string block_id - id of the associated block
		 * @desc Marks the selected tab and switches to the associated block
		 */
		function showBlock(tab_id, block_id) {
			//unmark the last selected tab
			if(document.getElementById(current_tab_id)) {
				document.getElementById(current_tab_id).className = '';
			}
			//mark the new selection
			if(document.getElementById(tab_id)) {
				document.getElementById(tab_id).className = 'current';
			}
			
			//do the same with the blocks
			if(document.getElementById(current_block_id)) {
				document.getElementById(current_block_id).style.display = 'none';
			}
			//mark the new selection
			if(document.getElementById(block_id)) {
				document.getElementById(block_id).style.display = 'block';
			}
		
			//remember the current selections
			current_tab_id = tab_id;
			current_block_id = block_id;
			
			//try to set the term block
			if(current_term_blocks[block_id]) {
				showTermBlock(current_term_blocks[block_id]);
			}
			
			return false;
		}
		
		function showTermBlock(term_block_id) {
			//unmark the last selected term block
			if(document.getElementById(current_term_blocks[current_block_id])) {
				document.getElementById(current_term_blocks[current_block_id]).style.display = 'none';
			}
			//mark the new selection
			if(document.getElementById(term_block_id)) {
				document.getElementById(term_block_id).style.display = '';
			}
			
			//remember the selection
			current_term_blocks[current_block_id] = term_block_id;		
		}
	</script>
      			
	<div class="contentTabs">
		<ul>
<?php	if($u->getDefaultRole() >= $g_permission['instructor']): //check DEFAULT role, so that not-trained instructors still see this tab ?>
			<li id="instructor_tab"><a href="#" onclick="return showBlock('instructor_tab', 'instructor_block');">You are teaching:</a></li>
<?php	endif; ?>

			<li id="student_tab" class="current"><a href="#" onclick="return showBlock('student_tab', 'student_block');">You are enrolled in:</a></li>

<?php	if(!empty($proxy_CIs)): //only show proxy tab if user is currently proxying courses ?>
			<li id="proxy_tab"><a href="#" onclick="return showBlock('proxy_tab', 'proxy_block');">You are proxy for:</a></li>
<?php	endif; ?>
		</ul>
	</div>
	<div class="clear"></div>
	
<?php	if($u->getDefaultRole() >= $g_permission['instructor']): ?>
		<div id="instructor_block" style="display:none;">
			<div width="100%" class="displayList">
				<div style="padding:4px;" class="head">
					<div style="float:left;">
<?php
			//show a radio choices for terms, to act as a filter for class list display
			//pre-select first option
			$select_option = true;
			foreach(array_keys($instructor_ci_array) as $term_id):
				$term = new term($term_id);
				$select = ($select_option) ? 'checked="true"' : '';
?>
							<input type="radio" name="instructor_term_block" onclick="showTermBlock('instructor_block_<?=$term_id?>');" <?=$select?> /><?=$term->getTermName().' '.$term->getTermYear()?>&nbsp;
<?php		
				//stop pre-selecting
				$select_option = false;
			endforeach;
?>
					</div>
<?php		if($u->getRole() >= $g_permission['instructor']): ?>
					<div style="float:right;"><span class="actions">[ <a href="index.php?cmd=createClass">Create a New Class</a> ]</span></div>
<?php		endif; ?>
					<div style="clear:both;"></div>
				</div>
			</div>
<?php
			//loop through all the available terms/courses
			if(!empty($instructor_ci_array)):
				foreach($instructor_ci_array as $term_id=>$status_ci_array):	//split the courses by term
?>
			<table id="instructor_block_<?=$term_id?>" class="displayList" style="display:none;" width="100%">
<?php
					$rowClass = 'evenRow';
					foreach($status_ci_array as $status=>$ci_list):	//split courses by status
						//begin looping through courses	for this term
						foreach($ci_list as $ci):
							$ci->getCourseForUser();	//get course object
							$ci->getInstructors();	//get a list of instructors	
							
							//sort out the edit/activate/view links and icons, based on effective role		
							if($u->getRole() < $g_permission['instructor']) {	//if the users's effective role is less than instructor (not-trained)
								$edit_icon = '';	//they get no icon
								$course_num = '<a href="index.php?cmd=viewReservesList&ci='.$ci->getCourseInstanceID().'">'.$ci->course->displayCourseNo().'</a>';									$course_name = '<a href="index.php?cmd=viewReservesList&ci='.$ci->getCourseInstanceID().'">'.$ci->course->getName().'</a>';
								$enrollment = '<span class="'.common_getEnrollmentStyleTag($ci->getEnrollment()).'">'.$ci->getEnrollment().'</span>';
							}
							else {	//full-fledged instructor
								if($ci->getStatus() == 'AUTOFEED') {	//if the course has been fed through registrar, but not activated						
									$edit_icon = '<img src="images/activate.gif" width="24" height="20" />';	//show the 'activate-me' icon
									$course_num = '<a href="index.php?cmd=activateClass&ci='.$ci->getCourseInstanceID().'">'.$ci->course->displayCourseNo().'</a>';									
									$course_name = '<a href="index.php?cmd=activateClass&ci='.$ci->getCourseInstanceID().'">'.$ci->course->getName().'</a>';
									$enrollment = '<span class="'.common_getEnrollmentStyleTag($ci->getEnrollment()).'">'.$ci->getEnrollment().'</span>';
								}
								elseif($ci->getStatus() == 'CANCELED') {	//if the course has been cance led by the registrar
									$edit_icon = '<img src="images/cancel.gif" alt="edit" width="24" height="20">';	//show the 'activate-me' icon
									$course_num = $ci->course->displayCourseNo();
									$course_name = $ci->course->getName();
									$enrollment = '<strong>[<a href="index.php?cmd=removeClass&ci='.$ci->getCourseInstanceID().'">remove</a>]</strong>';
								}
								else {
									$edit_icon = '<img src="images/pencil.gif" alt="edit" width="24" height="20">';	//show the edit icon
									$course_num = '<a href="index.php?cmd=editClass&ci='.$ci->getCourseInstanceID().'">'.$ci->course->displayCourseNo().'</a>';
									$course_name = '<a href="index.php?cmd=editClass&ci='.$ci->getCourseInstanceID().'">'.$ci->course->getName().'</a>';
									$enrollment = '<span class="'.common_getEnrollmentStyleTag($ci->getEnrollment()).'">'.$ci->getEnrollment().'</span>';
								}								
							}
							
							$rowClass = ($rowClass=='oddRow') ? 'evenRow' : 'oddRow';	//set the row class
?>
				<tr align="left" valign="middle" class="<?=$rowClass?>">
					<td width="5%"><?=$edit_icon?></td>
					<td width="15%"><?=$course_num?></td>
					<td><?=$course_name?></td>
					<td width="30%"><?=$ci->displayInstructors()?></td>	
					<td width="10%"><?=$enrollment?></td>		
				</tr>
<?php					
						endforeach;	//end loop through CIs
					endforeach;	//end for each status
?>
			</table>
<?php
				endforeach;	//end for each term
			else:	//not teaching any courses
?>
			<div class="borders" style="padding:5px;">
				You are not teaching any courses.
			</div>
<?php		endif; ?>
			<p />
			<img src="images/pencil.gif" width="24" height="20" /> <span style="font-size:small;">= active courses you may edit</span>
			<br />
			<img src="images/activate.gif" width="24" height="20" /> <span style="font-size:small;">= new courses not yet in use</span>
			<br />
			<img src="images/cancel.gif" width="24" height="20" /> <span style="font-size:small;">= courses canceled by the registrar</span>
			<p />
		</div>
<?php	endif; ?>


		<div id="student_block">
			<table width="100%" class="displayList">
				<tr align="right" valign="middle" class="head">
					<td colspan="4">
						<span class="actions">[ <a href="index.php?cmd=addClass">Join a Class</a> ] [ <a href="index.php?cmd=removeClass">Leave a Class</a> ]</span>
					</td>
				</tr>
<?php	
		if(!empty($student_CIs)):
			//begin looping through courses - separate by enrollment status
			foreach($student_CIs as $status=>$courses):
				if($status == 'PENDING'):	//show a label for pending courses
?>
				<tr align="left" valign="middle">
					<td colspan="4" class="divider">
						Courses you have requested to join (pending approval):
					</td>
				</tr>
				
<?php			elseif($status == 'DENIED'):	//show label for denied courses ?>

				<tr align="left" valign="middle">
					<td colspan="4" class="divider">
						Courses you may not join (denied enrollment):
					</td>
				</tr>
<?php
				endif;
				
				if(empty($rowClass)) {
					$rowClass = 'evenRow';
				}
				foreach($courses as $ci):
					$ci->getCourseForUser();	//get course object
					$ci->getInstructors();	//get a list of instructors
					
					//only link enrolled classes
					if(($status == 'AUTOFEED') || ($status == 'APPROVED')) {
						$course_num = '<a href="index.php?cmd=viewReservesList&ci='.$ci->getCourseInstanceID().'">'.$ci->course->displayCourseNo().'</a>';
						$course_name = '<a href="index.php?cmd=viewReservesList&ci='.$ci->getCourseInstanceID().'">'.$ci->course->getName().'</a>';
					}
					else {
						$course_num = $ci->course->displayCourseNo();
						$course_name = $ci->course->getName();
					}
					
					$rowClass = ($rowClass=='oddRow') ? 'evenRow' : 'oddRow';	//set the row class
?>
				<tr align="left" valign="middle" class="<?=$rowClass?>">
					<td width="15%"><?=$course_num?></td>
					<td><?=$course_name?></td>
					<td width="10%"><?=$ci->displayTerm()?></td>
					<td width="25%"><?=$ci->displayInstructors()?></td>			
				</tr>
<?php		
				endforeach;
			endforeach;
		else:	//not enrolled in any classes
?>
			<tr>
				<td>You are not enrolled in any classes this semester</td>
			</tr>

<?php	endif; ?>
			</table>
			<p />
		</div>


<?php	if(!empty($proxy_ci_array)): ?>
		<div id="proxy_block" style="display:none;">
			<div width="100%" class="displayList">
				<div style="padding:4px;" class="head">
<?php
			//show a radio choices for terms, to act as a filter for class list display
			//pre-select first option
			$select_option = true;
			foreach(array_keys($proxy_ci_array) as $term_id):
				$term = new term($term_id);
				$select = ($select_option) ? 'checked="true"' : '';
?>
							<input type="radio" name="proxy_term_block" onclick="showTermBlock('proxy_block_<?=$term_id?>');" <?=$select?> /><?=$term->getTermName().' '.$term->getTermYear()?>&nbsp;
<?php		
				//stop pre-selecting
				$select_option = false;
			endforeach;
?>
				</div>
			</div>
<?php		foreach($proxy_ci_array as $term_id=>$term_ci_list):	//split up the subarrays by term ?>
			<table id="proxy_block_<?=$term_id?>" class="displayList" style="display:none;" width="100%">
<?php
				//begin looping through courses		
				$rowClass = 'evenRow';
				foreach($term_ci_list as $ci):
					$ci->getCourseForUser();	//get course object
					$ci->getInstructors();	//get a list of instructors				
					$edit_icon = 'images/pencil.gif';
					
					$rowClass = ($rowClass=='oddRow') ? 'evenRow' : 'oddRow';	//set the row class
?>
				<tr align="left" valign="middle" class="<?=$rowClass?>">
					<td width="5%"><img src="<?=$edit_icon?>" alt="edit" width="24" height="20"></td>
					<td width="15%"><a href="index.php?cmd=editClass&ci=<?=$ci->getCourseInstanceID()?>"><?=$ci->course->displayCourseNo()?></a></td>
					<td><a href="index.php?cmd=editClass&ci=<?=$ci->getCourseInstanceID()?>"><?=$ci->course->getName()?></a></td>
					<td width="30%"><?=$ci->displayInstructors()?></td>	
					<td width="10%"><span class="<?=common_getEnrollmentStyleTag($ci->getEnrollment())?>"><?=$ci->getEnrollment()?></span></td>		
				</tr>
<?php			endforeach; ?>
			</table>
<?php		endforeach; ?>
			<p />
			<img src="images/pencil.gif" width="24" height="20"> <span style="font-size:small;">= courses you may edit</span>
			<p />
		</div>
<?php	endif; ?>

		<script language="JavaScript" type="text/javascript">
			<?=$onload_jscript?>
		</script>
<?php
	} //displayCourseList()
	
	
	function displayDuplicateCourse(&$ci, $prev_state=null) {
		global $u, $g_permission;
		
		$dup_msg = "The course you are attempting to create is already active for this term.  Please double-check the department, course number, section, and term of your course.";  
		if (is_array($ci->duplicates))
			$dup_msg .=	"You may copy reserves by selecting one of the course(s) below.";
		
		
?>	
		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
			<tr><td width="100%"><img src="images/spacer.gif" width="1" height="5"></td></tr>
			<tr>
				<td class="failedText"><?=$dup_msg?></td>
			</tr>
			<tr><td>&nbsp;</td></tr>
			<? if (is_array($ci->duplicates)) { ?>
				<form action="index.php" method="post" id="frmCopyClass">
					<input type="hidden" name="cmd" value="processCopyClass">
					<input type="hidden" name="importClass">				
					<input type="hidden" name="ci" value="<?= $ci->getCourseInstanceID(); ?>">
					<tr>
						<td align="left" valign="top">
							<table width="100%" border="0" align="center" cellpadding="5" cellspacing="0" class="displayList">
									<tr align="left" valign="middle" bgcolor="#CCCCCC" class="headingCell1">
										<td>&nbsp;</td><td>Course Number</td><td align="left">Course Name</td><td>Instructor</td><td>Active Term</td><td>Reserve List</td>
									</tr>
											
									<? 
									foreach ($ci->duplicates as $dup)
									{ 							
										$dup->getPrimaryCourse();	//pull in course object
										$dup->getInstructors();	//pull in instructor info
										
										//link course name/num to editClass, if viewed by instructor
										if($u->getRole() >= $g_permission['staff']) {
											$course_num = '<a href="index.php?cmd=editClass&ci='.$dup->getCourseInstanceID().'">'.$dup->course->displayCourseNo().'</a>';
											$course_name = '<a href="index.php?cmd=editClass&ci='.$dup->getCourseInstanceID().'">'.$dup->course->getName().'</a>';
										}
										else {
											$course_num = $dup->course->displayCourseNo();
											$course_name = $dup->course->getName();
										}							
										echo "<tr align=\"left\" valign=\"middle\" class=\"oddRow\">\n";
										echo "	<td align=\"center\"><input type='radio' name='new_ci' value='".$dup->getCourseInstanceID()."' onClick=\"this.form.submit.disabled=false;\"></td>\n";
										echo "	<td width=\"15%\" align=\"center\">$course_num</td>\n";
										echo "	<td>$course_name</td>\n";
										echo "	<td width=\"20%\" align=\"center\">".$dup->displayInstructors()."</td>\n";
										echo "	<td width=\"15%\" align=\"center\">".$dup->displayTerm()."</td>\n";
										echo "	<td width=\"10%\" align=\"center\"><a href=\"javascript:openWindow('no_control&cmd=previewReservesList&ci=".$dup->getCourseInstanceID()."','width=800,height=600');\">preview</a></td>\n";
										echo "</tr>\n";
									}
									?>	
									
								</table>
						</td>
					</tr>
					
					<tr><td align="left" valign="top">&nbsp;</td></tr>
					
					<tr><td align="center" valign="top"><input type="submit" value="Copy Into Selected Course" name="submit" disabled> 
					</form>
				<? } //isarray($ci->duplicates 
				   else 
				   { echo '<tr><td align="center" valign="top">'; }

							//make a form with hidden items and a button to return to previous screen
							if(!empty($prev_state))
							{	
								//echo "<div style=\"width:100%; margin:auto;\">\n";
								echo "	<form action=\"index.php\" method=\"post\" name=\"return_to_previous\">\n";
										self::displayHiddenFields(unserialize(base64_decode($prev_state))); 
								echo "		<input type=\"submit\" name=\"return\" value=\"Go Back to the Previous Screen\" />\n";
								echo "	</form>\n";
								//echo "</div>\n";
							}
						?>
					</td>
				</tr>						
		</table>
		<p />
<?php
	}
}?>
