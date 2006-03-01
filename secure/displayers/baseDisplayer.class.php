<?php
/*******************************************************************************
baseDisplayer.class.php
Base Displayer abstract class

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

require_once('secure/classes/reserves.class.php');

/**
 * Base Displayer abstract class
 * - Contains functions common to many displayers
 * - To be extended by other displayer classes
 * - this is very layout-specific, since it will assume the general layout of
 *	its children's elements - ie. it will be using <table> often, as that is what
 *	its children currently use.
 */
abstract class baseDisplayer {
	
	/**
	 * @return void
	 * @param array $notes Reference to an array of note objects
	 * @param string $referrer_string Query sub-string to be used for the DELETE links.  ex: 'reserveID=5' or 'itemID=10'
	 * @desc outputs HTML for display of notes edit boxes in item/reserve edit screens
	 */
	public function displayEditNotes(&$notes, $referrer_string) {
		foreach($notes as $note):
?>
			<tr>
				<td bgcolor="#CCCCCC" align="right">
					<strong><?=$note->getType()?> Note:</strong>
					<br />
					[<a href="index.php?cmd=<?=$_REQUEST['cmd']?>&amp;<?=$referrer_string?>&amp;deleteNote=<?=$note->getID()?>">Delete this note</a>]&nbsp;
				</td>
				<td>
					<textarea name="notes[<?=$note->getID()?>]" cols="50" rows="3"><?=stripslashes($note->getText())?></textarea>
				</td>
			</tr>								
<?php
		endforeach;
		//rewind array
		reset($notes);
	}
	
	
	/**
	 * @return void
	 * @param string $referrer_string String identifying object and its ID. ex: 'reserveID=5' or 'itemID=10'. Note: the addNote handler must recognize the object
	 * @desc outputs HTML for display of addNote button
	 */
	public function displayAddNoteButton($referrer_string) {
?>
		<input type="button" name="addNote" value="Add Note" onClick="openWindow('no_table=1&amp;cmd=addNote&amp;<?=$referrer_string?>','width=600,height=400');">
<?php
	}
	
	
	/**
	 * @return void
	 * @param array $itemNotes Reference to an array of note objects
	 * @desc outputs HTML for display of notes in reserve listings
	 */
	public function displayItemNotes(&$itemNotes) {
		global $u, $g_permission, $g_notetype;
		
		if(empty($itemNotes))
			return;
		
		foreach($itemNotes as $note) {
			if($note->getType() == $g_notetype['content']) {	//show content notes to everyone
				echo '<br /><span class="noteType">Content Note:</span>&nbsp;<span class="noteText">'.stripslashes($note->getText()).'</span>';
			}
			elseif($u->getRole() >= $g_permission['staff']) {	//if other type of item note, only show to staff or greater
				echo '<br /><span class="noteType">'.ucfirst($note->getType()).' Note:</span>&nbsp;<span class="noteText">'.stripslashes($note->getText()).'</span>';
			}
		}	
	}
	
	
	/**
	 * @return void
	 * @param array $reserveNotes Reference to an array of note objects
	 * @desc outputs HTML for display of notes in reserve listings
	 */
	public function displayReserveNotes(&$reserveNotes) {
		if(empty($reserveNotes))
			return;
		
		foreach($reserveNotes as $note) {
			echo '<br><span class="noteType">'.ucfirst($note->getType()).' Note:</span>&nbsp;<span class="noteText">'.stripslashes($note->getText()).'</span>';
		}		
	}
	
	
	/**
	 * @return void
	 * @param array $hidden_fields A reference to an array (may be two-dimensional) of keys/values
	 * @desc outputs hidden <input> fields for the array
	 */
	public function displayHiddenFields(&$hidden_fields) {
		if(empty($hidden_fields))
			return;
			
		foreach($hidden_fields as $key=>$val) {
			if(is_array($val)) {
				foreach($val as $subkey=>$val) {
					echo '<input type="hidden" name="'.$key.'['.$subkey.']" value="'.$val.'" />'."\n";
				}
			}
			else {		
				echo '<input type="hidden" name="'.$key.'" value="'.$val.'" />'."\n";
			}
		}
	}
	
	
	/**
	 * @return void
	 * @param reserve $reserve Reference to a reserve object
	 * @param string $block_style String added to the main <div> to style it
	 * @param boolean $edit_options If set to true, will display editing options. If false, will show student view
	 * @desc outputs HTML showing information about a reserve plus aditional links and info.  For use in class/reserve lists.
	 */
	public function displayReserveRow(&$reserve, $block_style='', $edit_options=false) {
		if(!($reserve->item instanceof reserveItem)) {
			$reserve->getItem();	//pull in item info
		}
		
		if($reserve->item->isHeading()) {	//style as heading
			$block_style ='class="headingCell2"';
		}
?>
	
	<div <?=$block_style?> >

<?php	if($reserve->hidden): ?>
		<div class="hiddenItem">
<?php	endif; ?>

		
		
<?php
		//are we editing?
		if($edit_options):	//yes, show editing info and options
			//if item is heading, warn on checkbox click
			$checkbox_onchange = $reserve->item->isHeading() ? 'onchange="javascript:alert(\'Checking this box will affect everything in this folder\')"' : '';
			$meta_style = 'metaBlock';
?>
		<div class="editOptions">
			<div class="itemNumber">
				<?=$reserve->counter?>
			</div>
			<div class="checkBox">
				<input type="checkbox" name="selected_reserves[]" value="<?=$reserve->getReserveID()?>" <?=$checkbox_onchange?> />
			</div>
			<div class="sortBox">
				<?=$reserve->sort_link?>&nbsp;
			</div>
			<div class="editBox">
				<?=$reserve->edit_link?>&nbsp;
			</div>
			<div class="statusBox">
				<span class="<?=common_getStatusStyleTag($reserve->status)?>"><?=$reserve->status?></span>
			</div>
		</div>
<?php	
		else:	//not editing -- "student view"
			//if item is hidden, mark it as such
			$checkbox_checked = ((isset($reserve->hidden) && $reserve->hidden) || (isset($reserve->selected) && $reserve->selected)) ? 'checked="checked"' : '';
			//if item is heading, warn on checkbox click
			$checkbox_onchange = $reserve->item->isHeading() ? 'onchange="javascript:alert(\'Checking/Unchecking this box will hide/unhide everything in this folder\')"' : '';
			$meta_style = 'metaBlock-wide';

			
?>

			<div class="checkBox-right">
				<input type="checkbox" <?=$checkbox_checked?> name="selected_reserves[]" value="<?=$reserve->getReserveID()?>" <?=$checkbox_onchange?> />
			</div>
		
<?php		endif; ?>
		
<?php	self::displayReserveInfo($reserve, 'class="'.$meta_style.'"'); ?>

<?php	if($reserve->hidden): ?>
		</div>
<?php	endif; ?>

		<!-- hack to clear floats -->
		<div style="clear:both;"></div>
		<!-- end hack -->
	</div>
<?php
	}
	
	
	/**
	 * @return void
	 * @param reserve $reserve Reference to a reserve object
	 * @param string $row_style styles the row
	 * @desc outputs HTML showing information about a reserve.  For use in class/reserve lists.
	 */
	public function displayReserveInfo(&$reserve, $meta_style) {
		global $u, $g_reservesViewer;
	
		//collect and set data	
		
		if(!($reserve->item instanceof reserveItem)) {
			$reserve->getItem();	//pull in item info
		}
		
		if(!$reserve->item->isHeading()) {	//if not heading/folder, assign all the pertinent info
			$title = $reserve->item->getTitle();
			$author = $reserve->item->getAuthor();
			$url = $reserve->item->getURL();
			$performer = $reserve->item->getPerformer();
			$volTitle = $reserve->item->getVolumeTitle();
			$volEdition = $reserve->item->getVolumeEdition();
			$pagesTimes = $reserve->item->getPagesTimes();
			$source = $reserve->item->getSource();
			$itemIcon = $reserve->item->getItemIcon();
			
			$reserve->item->getPhysicalCopy();	//get physical copy info
			$callNumber = $reserve->item->physicalCopy->getCallNumber();
			//get home library/reserve desk
			$lib = new library($reserve->item->getHomeLibraryID());
			$reserveDesk = $lib->getReserveDesk();
			
			if($reserve->item->isPhysicalItem()) {
				$viewReserveURL = $g_reservesViewer . $reserve->item->getLocalControlKey();
			}
			else {
				$viewReserveURL = "reservesViewer.php?reserve=" . $reserve->getReserveID();
			}
		}
		$itemNotes = $reserve->item->getNotes();
		$reserveNotes = $reserve->getNotes();	
		
		//begin display of data

		if($reserve->item->isHeading()):
?>

		<div class="headingText" style="border:0px solid red;">
<?php
			echo $reserve->item->getTitle();
			//show notes
			self::displayItemNotes($itemNotes);
			self::displayReserveNotes($reserveNotes);
?>
		</div>
	
<?php	else: ?>

		<div class="iconBlock">
			<img src="<?=$itemIcon?>" alt="icon">&nbsp;
		</div>

		<div <?=$meta_style?>>
<?php		if($reserve->item->isPhysicalItem()): ?>

			
			<span class="itemTitleNoLink"><?=$title?></span>
			<br />
			<span class="itemAuthor"><?=$author?></span>
			<br />
			<span class="itemMeta"><?=$callNumber?></span>
			<br />
			<span class="itemMetaPre">On Reserve at:</span><span class="itemMeta"><?=$reserveDesk?></span> [<a href="<?=$viewReserveURL?>" target="_blank" class="strong">more info</a>]
	
<?php		else: ?>
		
		
			<a href="<?=$viewReserveURL?>" target="_blank" class="itemTitle" style="margin:0px; padding:0px;"><?=$title?></a>
		
		<br />
					
			<span class="itemAuthor"><?=$author?></span>
			
<?php		endif; ?>


<?php		if($performer): ?>

       		<br />
       		<span class="itemMetaPre">Performed by:</span><span class="itemMeta"><?=$performer?></span>
       		
<?php		endif; ?>
<?php		if($volTitle): ?>

       		<br />
       		<span class="itemMetaPre">From:</span><span class="itemMeta"><?=$volTitle?></span>
       		
<?php		endif; ?>
<?php		if($volEdition): ?>

       		<br />
       		<span class="itemMetaPre">Volume/Edition:</span><span class="itemMeta"><?=$volEdition?></span>
       		
<?php		endif; ?>
<?php		if($pagesTimes): ?>

       		<br />
       		<span class="itemMetaPre">Pages/Time:</span><span class="itemMeta"><?=$pagesTimes?></span>
       		
<?php		endif; ?>
<?php		if($source): ?>

       		<br />
       		<span class="itemMetaPre">Source/Year:</span><span class="itemMeta"><?=$source?></span>
       		
<?php		endif; ?>

<?php
			//show notes
			self::displayItemNotes($itemNotes);
			self::displayReserveNotes($reserveNotes);
			
			//show additional info
			if(!empty($reserve->additional_info)) {
				echo $reserve->additional_info;
			}
?>

		</div>

<?php 	
		endif; 
	}
	
	
	/**
	 * @return void
	 * @param courseInstance $ci Reference to a Course Instance object
	 * @desc displays a <select> box that shows all available folders (headings) for a given CI
	 */
	public function displayHeadingSelect(&$ci) {
		//get headings as a tree + recursive iterator
		$walker = $ci->getReservesAsTreeWalker('getHeadings');
?>
	<select name="heading_select">
		<option value= "" selected="selected">...</option>
		<option value="root">Main List</option>
<?php
		foreach($walker as $leaf):
			$heading = new reserve($leaf->getID());
			$heading->getItem();
			$label = str_repeat('&nbsp;&nbsp;', ($walker->getDepth()+1)).$heading->item->getTitle();
?>	
			<option value="<?=$leaf->getID()?>"><?=$label?></option>
<?php	endforeach; ?>
	</select>
<?php
	}
	
	
	/**
	 * @return void
	 * @param int $default_dept (optional) ID of department to pre-select
	 * @desc displays a <select> box that shows all available departments
	 */
	public function displayDepartmentSelect($default_dept=null) {
		$department = new department();	//init a department object
?>
	<select name="department" id="department">
		<option value="">-- Select a Department --</option>
<?php
		foreach($department->getAllDepartments() as $dep):
			$selected = ($dep[0]==$default_dept) ? 'selected="selected"' : '';
?>
		<option value="<?=$dep[0]?>" <?=$selected?>><?=$dep[1]?> <?=$dep[2]?></option>
<?php	endforeach; ?>			
	</select>
<?php
	}
	
	
	/**
	 * @return void
	 * @param int $default_term (optional) ID of term to pre-select
	 * @param boolean $show_dates (optional) If true, will show input fields for activation and expiration dates; else will include them as hidden fields
	 * @desc displays a <select> box of semesters and date fields
	 */
	public function displayTermSelect($default_term=null, $show_dates=false) {
		global $calendar;
		
		$termsObj = new terms();
		$terms = $termsObj->getTerms();
		
		if(empty($default_term)) {	//set default if none specified
			$default_term = $terms[0]->getTermID();
		}
		
		//must build a javascript array with term dates
		$term_dates_jscript = '';
		foreach($terms as $term) {
			$term_dates_jscript .= "term_dates[".$term->getTermID()."] = new Array();\n";
			$term_dates_jscript .= "term_dates[".$term->getTermID()."][0] = '".$term->getBeginDate()."';\n";
			$term_dates_jscript .= "term_dates[".$term->getTermID()."][1] = '".$term->getEndDate()."';\n";
		}
		
?>
	<script language="JavaScript">
		/*
			This date change could be accomiplished much easier if you could call
			the term_setActiveDates() function directly from <option onclick>. 
			Howerver, IE does not support that even, so we must do the workaround where
			we build a list of all possible dates ahead of time.
		*/
		
		function term_setTermDates(term_id) {
			var term_dates = new Array();			
			<?=$term_dates_jscript?>

			return term_setActiveDates(term_dates[term_id][0], term_dates[term_id][1]);
		}
		
		function term_setActiveDates(activateDate, expirationDate) {
			if(document.getElementById('activation_date')) {
				document.getElementById('activation_date').value = activateDate;
			}
			if(document.getElementById('expiration_date')) {
				document.getElementById('expiration_date').value = expirationDate;
			}
			return false;
		}
	</script>

	<select name="term" id="term" onchange="term_setTermDates(this.options[this.selectedIndex].value);">
<?php
		foreach($terms as $term):
			$selected = '';
			if($term->getTermID()==$default_term) {	//if the term matches default term
				$selected = 'selected="selected"';	//preselect the field
				//fetch the default dates
				$activation_date = $term->getBeginDate();
				$expiration_date = $term->getEndDate();
			}
?>
	<!--	<option value="<?=$term->getTermID()?>" <?=$selected?> onclick="term_setActiveDates('<?=$term->getBeginDate()?>','<?=$term->getEndDate()?>')"><?=$term->getTerm()?></option>	-->
		<option value="<?=$term->getTermID()?>" <?=$selected?>><?=$term->getTerm()?></option>
<?php	endforeach; ?>			
	</select>
	
<?php	if($show_dates): //show date fields ?>

	&mdash; <input type="text" id="activation_date" name="activation_date" size="10" maxlength="10" value="<?=$activation_date?>" /> <?=$calendar->getWidgetAndTrigger('activation_date', $activation_date)?> &raquo; <input type="text" id="expiration_date" name="expiration_date" size="10" maxlength="10" value="<?=$expiration_date?>" /> <?=$calendar->getWidgetAndTrigger('expiration_date', $expiration_date)?>
	
<?php 	else:	//include them as hidden fields ?>

	<input type="hidden" id="activation_date" name="activation_date" value="<?=$activation_date?>" />
	<input type="hidden" id="expiration_date" name="expiration_date" value="<?=$expiration_date?>" />
	
<?php						
		endif;
	}
	
	
	/**
	 * @return void
	 * @param string $default_enrollment (optional) Enrollment option to check by default
	 * @desc displays enrollment options as radio options
	 */
	public function displayEnrollmentSelect($default_enrollment='OPEN') {
		//set default
		$checked = array();
		$options = array('OPEN', 'MODERATED', 'CLOSED');
		if(!in_array($default_enrollment, $options)) {	//if not a valid default, set it to OPEN
			$default_enrollment = 'OPEN';
		}
		//now set up the checks
		foreach($options as $option) {
			$checked[$option] = ($default_enrollment == $option) ? 'checked="checked"' : '';
		}		
?>
		<input type="radio" name="enrollment" id="enrollment" value="OPEN" <?=$checked['OPEN']?> /> OPEN 
		<input type="radio" name="enrollment" id="enrollment" value="MODERATED" <?=$checked['MODERATED']?> /> MODERATED 
		<input type="radio" name="enrollment" id="enrollment" value="CLOSED" <?=$checked['CLOSED']?> /> CLOSED 
<?php		
	}
	
	
	/**
	 * @return void
	 * @param string $next_cmd The next command to execute
	 * @param array $course_instances (optional) Array of courseInstance objects to show for proxy/instructor select; ignored for staff
	 * @param string $msg (optional) Text to display above the class select
	 * @param array $hidden_fields (optional) Array of info to pass on as hidden fields
	 * @desc Displays class selector -- ajax for staff, list of classes for proxy/instructor
	 */
	public function displaySelectClass($next_cmd, $course_instances=null, $msg=null, $hidden_fields=null) {
		global $u, $g_permission;
		
		if(!empty($msg)) {
			echo '<span class="helperText">'.$msg.'</span><p />';
		}				
		
		if($u->getRole() >= $g_permission['staff']) {	//staff - use ajax class lookup
			//display selectClass
			$mgr = new ajaxManager('lookupClass', $next_cmd, 'manageClasses', 'Continue', $hidden_fields);
			$mgr->display();
		}
		elseif(($u->getRole() == $g_permission['proxy']) || ($u->getRole() == $g_permission['instructor'])) {	//proxy/instructor class select
			//begin display
?>
		<form action="index.php" method="post" name="select_class" id="select_class">
			<input type="hidden" id="cmd" name="cmd" value="<?=$next_cmd?>" />		
			<?php self::displayHiddenFields($hidden_fields); ?>
			
		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
			<tr>
				<td class="headingCell1" width="25%" align="center">SELECT CLASS</td>
				<td width="75%" align="center">&nbsp;</td>
			</tr>
			<tr>
		    	<td colspan="2" class="borders">
			    	<table width="100%" border="0" cellspacing="0" cellpadding="5" class="displayList">
			    		<tr class="headingCell1" style="text-align:left;">
			    			<td width="10%" style="text-align:center;">Select</td>
			    			<td width="20%">Course Number</td>
							<td>Course Name</td>
							<td width="15%">Last Active</td>
							<td width="10%" style="text-align:center;">Reserve List</td>							
			    		</tr>
			
<?php		
			$rowClass = 'evenRow';
			//loop through the courses
			foreach($course_instances as $ci):
				$ci->getPrimaryCourse();	//fetch the course object
				$rowClass = ($rowClass=='evenRow') ? 'oddRow' : 'evenRow';
?>
						<tr class="<?=$rowClass?>">
							<td width="10%" style="text-align:center;"><input type="radio" id="ci" name="ci" value="<?=$ci->getCourseInstanceID()?>" onClick="this.form.submit.disabled=false;" /></td>
			    			<td width="20%"><?=$ci->course->displayCourseNo()?></td>
							<td><?=$ci->course->getName()?></td>
							<td width="10%"><?=$ci->displayTerm()?></td>
							<td width="10%" style="text-align:center;"><a href="javascript:openWindow('no_control=1&cmd=previewReservesList&ci=<?=$ci->getCourseInstanceID()?>','width=800,height=600');">preview</a></td>
						</tr>   

<?php		endforeach;	?>

					</table>
				</td>
			</tr>
		</table>
		<p />		
		<input type="submit" name="submit" value="Continue" disabled="disabled">
		
		</form>
<?php
		}
	}
}