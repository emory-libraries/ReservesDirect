<?
/*******************************************************************************
noteDisplayer.class.php


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
require_once('secure/displayers/baseDisplayer.class.php');

class noteDisplayer extends baseDisplayer {

	/**
	 * @return void
	 * @param array $notes Array of note objects
	 * @param string $obj_type Type of object these notes are connected to (`reserve`, `item`, etc)
	 * @param int $obj_id ID of the object
	 * @param boolean $include_add_button If true, will include that add-note button
	 * @desc Displays notes in a table with javascript links to edit and/or delete each note; NOTE: requires basicAJAX.js, notes_ajax.js
	 */
	public function displayNotesBlockAJAX($notes, $obj_type, $obj_id, $include_add_button=true) {
?>
		<div id="notes_content">
			<?php self::displayNotesContentAJAX($notes, $obj_type, $obj_id); //notes content ?>
		</div>
		
		<?php self::displayNotesFormAJAX($obj_type, $obj_id); //include the add/edit-note form ?>
		
<?php
		if($include_add_button) {	//include add-note button?
			self::displayAddNoteButtonAJAX();
		}
	}
	
	
	/**
	 * @return void
	 * @param array $notes Array of note objects
	 * @param string $obj_type Type of object these notes are connected to (`reserve`, `item`, etc)
	 * @param int $obj_id ID of the object
	 * @desc Displays notes in a table with javascript links to edit and/or delete each note
	 */
	public function displayNotesContentAJAX(&$notes, $obj_type, $obj_id) {
		global $u, $g_notetype, $g_permission;
		
		if(empty($notes)) {
			return;
		}
		
		//some notes should be shown only to staff
		$restricted_note_types = array($g_notetype['staff'], $g_notetype['copyright'], $g_notetype['content']);
		
		//display notes
?>
		<table border="0" cellpadding="2" cellspacing="0">
<?php		
		foreach($notes as $note):
			if(in_array($note->getType(), $restricted_note_types) && ($u->getRole() < $g_permission['staff'])) {
				continue;	//skip the note if it is restricted and user is less than staff
			}
?>
			<tr valign="top">
				<td align="right">
					<strong><?=$note->getType()?> Note:</strong>
					<br />
					<a href="#" onclick="javascript: notes_show_form(<?=$note->getID()?>, '<?= preg_replace('"',"'", $note->getText())?>', '<?=$note->getType()?>'); return false;">edit</a> | <a href="#" onclick="javascript: notes_delete_note('<?=$obj_type?>', <?=$obj_id?>, <?=$note->getID()?>); return false;">delete</a>&nbsp;
				</td>
				<td>
					<?=stripslashes($note->getText())?>
				</td>
			</tr>								
<?php	endforeach; ?>

		</table>
<?php
}


	/**
	 * @return void
	 * @param string $referrer_string String identifying object and its ID. ex: 'reserveID=5' or 'itemID=10'. Note: the addNote handler must recognize the object
	 * @desc outputs HTML for display of addNote button
	 */
	public function displayAddNoteButtonAJAX() {
?>
		<input type="button" value="Add Note" onclick="javascript: notes_show_form('', '', ''); return false;" />
<?php
	}
	

	/**
	 * @return void
	 * @param string $obj_type Type of object these notes are connected to (`reserve`, `item`, etc)
	 * @param int $obj_id ID of the object
	 * @desc Adds the note form to a page (hidden) and the add-note button
	 */
	public function displayNotesFormAJAX($obj_type, $obj_id) {
		global $u, $g_notetype, $g_permission;
		
		//filter the type of note that may be added, based on object type
		$available_note_types = array();
		switch($obj_type) {
			case 'item':
				$available_note_types = array('content', 'staff');
			break;			
			case 'reserve':
				$available_note_types = array('instructor', 'content', 'staff');
			break;			
			case 'copyright':
				$available_note_types = array('copyright');
			break;
		}
		
		//filter allowed note types based on permission level
		$restricted_note_types = array('content', 'staff', 'copyright');
		//filter out restricted notes if role is less than staff
		if($u->getRole() < $g_permission['staff']) {
			$available_note_types = array_diff($available_note_types, $restricted_note_types);
		}
?>
			<div id="noteform_container" class="noteform_container" style="display:none;">
				<div id="noteform_bg" class="noteform_bg"></div>
				<div id="noteform" class="noteform"">
					<form id="note_form" name="note_form" onsubmit="javascript: return false;">
						<input type="hidden" id="note_id" name="note_id" value="" />
						
						<strong><big>Add/Edit Note</big></strong>
						<br />
						<textarea id="note_text" name="note_text"></textarea>
						<small>
							<strong>Note Type:</strong>
<?php
		$first = true;
		foreach($available_note_types as $note_type):
			$checked = $first ? ' checked="true"' : '';
			$first = false;			
?>
							<input type="radio" id="note_type_<?=$g_notetype[$note_type]?>" name="note_type" value="<?=$g_notetype[$note_type]?>"<?=$checked?> /><?=ucfirst(strtolower($g_notetype[$note_type]))?>
<?php	endforeach; ?>
						</small>
						<br />
						<div style="text-align: center">
							<input type="button" value="Cancel" onclick="javascript: notes_hide_form(); return false;" />
							<input type="button" value="Save" onclick="javascript: notes_save_note('<?=$obj_type?>', <?=$obj_id?>, this.form); return false;" />					
						</div>
					</form>		
				</div>
			</div>			
<?php	
}


	/**
	 * @return void
	 * @param array $notes Reference to an array of note objects
	 * @param string $referrer_string Query sub-string to be used for the DELETE links.  ex: 'reserveID=5' or 'itemID=10'
	 * @param boolean $use_ajax_delete_links (optional) If true `delete` links will send click events to a `delete_note(note_id)` JS function
	 * @desc outputs HTML for display of notes edit boxes in item/reserve edit screens
	 */
	public function displayEditNotes(&$notes, $referrer_string, $use_ajax_delete_links=false) {
		global $u, $g_notetype, $g_permission;
		
		if(empty($notes)) {
			return;
		}
		
		//some notes should be shown only to staff
		$restricted_note_types = array($g_notetype['staff'], $g_notetype['copyright'], $g_notetype['content']);
?>
		<table border="0" cellpadding="2" cellspacing="0">
<?php		
		foreach($notes as $note):
			if(in_array($note->getType(), $restricted_note_types) && ($u->getRole() < $g_permission['staff'])) {
				continue;	//skip the note if it is restricted and user is less than staff
			}
?>
			<tr>
				<td align="right">
					<strong><?=$note->getType()?> Note:</strong>
					<br />
<?php		if($use_ajax_delete_links): ?>
					[<a href="#" onclick="javascript: notes_delete_note(<?=$note->getID()?>); return false;">Delete this note</a>]&nbsp;
<?php		else: ?>
					[<a href="index.php?cmd=<?=$_REQUEST['cmd']?>&amp;<?=$referrer_string?>&amp;deleteNote=<?=$note->getID()?>">Delete this note</a>]&nbsp;
<?php		endif; ?>
				</td>
				<td>
					<textarea name="notes[<?=$note->getID()?>]" cols="50" rows="3" wrap="virtual"><?=stripslashes($note->getText())?></textarea>
				</td>
			</tr>								
<?php	endforeach; ?>

		</table>
<?php
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
	 * @param array $notes Reference to an array of note objects
	 * @desc outputs HTML for display of notes in reserve listings
	 */
	public function displayNotes(&$notes) {
		global $u, $g_notetype, $g_permission;
		
		if(empty($notes)) {
			return;
		}
		
		//some notes should be shown only to staff
		$restricted_note_types = array($g_notetype['staff'], $g_notetype['copyright']);

		foreach($notes as $note):
			if(in_array($note->getType(), $restricted_note_types) && ($u->getRole() < $g_permission['staff'])) {
				continue;	//skip the note if it is restricted and user is less than staff
			}
?>
		<br />
		<span class="noteType"><?=ucfirst($note->getType())?> Note:</span>&nbsp;
		<span class="noteText"><?=stripslashes($note->getText())?></span>
<?php
		endforeach;	
	}
	
	
	/**
	* @return void
	* @param $user, $reserveID
	* @desc display Add Note form
	*/
	function displayAddNoteScreen($user, $hidden_fields)
	{
		global $g_permission, $g_notetype;

		echo "<form name=\"addNote\" action=\"index.php?no_table=1&amp;cmd=addNote\" method=\"post\">\n";
		
		//show hidden fields
		self::displayHiddenFields($hidden_fields);

		echo '<center>';
		echo '<table width="400" border="0" cellspacing="0" cellpadding="0" style="margin-top:30px;">';
  		echo '	<tr><td align="left" valign="top"><h1>ReservesDirect</h1></td></tr>';
		echo '	<tr><td align="left" valign="top" style="padding-bottom:30px;"><h2>Add Note</h2></td></tr>';
 // 		echo '	<tr><td align="left" valign="top">&nbsp;</td></tr>';
  //		echo '	<tr><td align="left" valign="top">&nbsp;</td></tr>';
  		if ($user->getRole() >= $g_permission['staff']) {
  			echo '	<tr>';
  			echo '  	<td align="left" valign="top">';
  			echo '			<table width="100%" border="0" cellspacing="0" cellpadding="0">';
  			echo '  			<tr>';
  			echo '  				<td width="50%" class="headingCell1">Note Options</td>';
  			echo '      			<td>&nbsp;</td>';
  			echo '				</tr>';
  			echo '			</table>';
  			echo '		</td>';
  			echo '	</tr>';
  			echo '	<tr>';
  			echo '  	<td align="left" valign="top">';
  			echo '			<table width="100%" border="0" cellpadding="3" cellspacing="0" class="borders">';
  			echo '    			<tr align="left" valign="top" bgcolor="#CCCCCC">';
  			echo '      			<td width="22%" valign="top"><p class="strong">Note Type:<br>';
  			echo '	    			<span class="small-x">(This will show as the title of the note for editing';
			echo '				    purposes.)</span></p>';
			echo '					</td>';
        	echo '					<td width="78%" align="left"><p>';
       		echo '					<label><input type="radio" name="noteType" value="'.$g_notetype['content'].'" checked>Content Note</label><br>';
       		
       		//only allow instructor notes if editing reserve
       		if(!empty($hidden_fields['reserveID'])) {
       			echo '					<label><input type="radio" name="noteType" value="'.$g_notetype['instructor'].'">Instructor Note</label><br>';
       		}
       		
       		echo '					<label><input type="radio" name="noteType" value="'.$g_notetype['staff'].'">Staff Note</label><br>';
			echo '					<label><input type="radio" name="noteType" value="'.$g_notetype['copyright'].'">Copyright Note</label><br>';
			echo '					</p></td>';
			echo '				</tr>';
			echo '			</table>';
			echo '		</td>';
  			echo '	</tr>';
		} else {
			echo '					<input type="hidden" name="noteType" value="'.$g_notetype['instructor'].'">';
		}

  		echo '	<tr>';
  		echo '		<td align="left" valign="top">&nbsp;</td>';
  		echo '	</tr>';
  		echo '	<tr>';
    	echo '		<td align="left" valign="top">';
    	echo '			<table width="100%" border="0" cellspacing="0" cellpadding="0">';
      	echo '				<tr>';
        echo '					<td width="50%" class="headingCell1">Note Text</td>';
        echo '					<td>&nbsp;</td>';
      	echo '				</tr>';
    	echo '			</table>';
    	echo '		</td>';
  		echo '	</tr>';
  		echo '	<tr>';
    	echo '		<td align="left" valign="top" class="borders">';
    	echo '			<table width="100%" border="0" cellspacing="0" cellpadding="3">';
      	echo '				<tr>';
        echo '					<td align="center" valign="top"><textarea name="noteText" cols="45"></textarea></td>';
      	echo '				</tr>';
    	echo '			</table>';
    	echo '		</td>';
  		echo '	</tr>';

  		echo "    <tr><td><img src=\images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
  		echo "    <tr>\n";
		echo "    	<td align=\"center\"><input type=\"submit\" value=\"Save Note\"></td>\n";
		echo "	</tr>\n";
		echo "    <tr><td><img src=\images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
		echo '</table>';

		echo '</td>';
		echo '</tr>';
		echo '</table>';
		echo "</form>\n";
		echo "</center>";
	}


	function displaySuccess($noteID)
	{
		echo "<script language=\"JavaScript\">this.window.opener.newWindow_returnValue='$noteID';</script>\n"; //pass value to parent window

		echo "<table width=\"400\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" style=\"margin-top:30px;\">\n"
		.	 "	<tbody>\n"
		.	 "	<tr><td align=\"left\" valign=\"top\"><h1>ReservesDirect</h1></td></tr>\n"
		.	 "	<tr><td align=\"left\" valign=\"top\" style=\"padding-bottom:30px;\"><h2>Add Note</h2></td></tr>\n"
		.	 "		<tr><td width=\"100%\"><img src=\images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n"
		.	 "		<tr>\n"
	    .	 "			<td align=\"left\" valign=\"top\" class=\"borders\" style=\"text-align:center; padding:5px 15px 10px 15px;\">\n"
	    .	 "				<p><strong>You have successfully added a note.</strong></p>\n"
	    .	 "						<p>Your note will not appear until you Save Changes to the item or heading you are working on.</p>\n"
		.	 "						<p>Please close this window to Continue</p>\n"
		.	 "						<p><input type=\"button\" value=\"Close Window\" onClick=\"window.close();\"></p>\n"
		.	 "			</td>\n"
		.	 "		</tr>\n"
		.	 "		<tr><td><img src=\images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n"
		.	 "	</tbody>\n"
		.	 "</table>\n"
		;
	}
}
?>