<?
/*******************************************************************************
noteManager.class.php


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
require_once("secure/common.inc.php");
require_once("secure/classes/note.class.php");
require_once("secure/classes/reserves.class.php");
require_once("secure/classes/reserveItem.class.php");
require_once("secure/classes/copyright.class.php");
require_once("secure/displayers/noteDisplayer.class.php");

class noteManager
{
	public $user;
	public $displayClass;
	public $displayFunction;
	public $argList;

	function display()
	{
		if (is_callable(array($this->displayClass, $this->displayFunction)))
			call_user_func_array(array($this->displayClass, $this->displayFunction), $this->argList);
	}


	function noteManager($cmd, $user)
	{
		global $ci, $g_notetype;
		$this->displayClass = "noteDisplayer";

		switch ($cmd)
		{
			default:
			case 'addNote':
				$this->displayFunction = "displayAddNoteScreen";
				$this->argList = array($user, array('cmd'=>'saveNote', 'reserveID'=>$_REQUEST['reserveID'], 'itemID'=>$_REQUEST['itemID']));
			break;

			case 'saveNote':			
				if(!empty($_REQUEST['reserveID'])) {	//editing item+reserve
					self::saveNote('reserve', $_REQUEST['reserveID'], $_REQUEST['noteText'], $_REQUEST['noteType']);
				}
				elseif(!empty($_REQUEST['itemID'])) {	//editing item only
					self::saveNote('item', $_REQUEST['itemID'], $_REQUEST['noteText'], $_REQUEST['noteType']);
				}
				else {	//no IDs set, error
					break;
				}

				$this->displayFunction = "displaySuccess";
				$this->argList = array(null);
			break;
		}
	}
	
	
	/**
	 * @return array Array of note objects
	 * @param string $obj_type Type of object containing the notes - 'reserve', 'item', etc
	 * @param int $obj_id ID of the object
	 * @param boolean $get_item_notes_for_reserve (optional) If the object type is 'reserve' and this is true, will fetch item notes in addition to reserve notes; defaults to true
	 * @desc Fetches an array of note objects for the specified object
	 */	
	public function fetchNotesForObj($obj_type, $obj_id, $get_item_notes_for_reserve=true) {
		$notes = array();
		
		if(empty($obj_type) || empty($obj_id)) {
			return $notes;
		}
		
		//item notes or reserve notes?
		switch($obj_type) {
			case 'reserve':
				//init reserve obj
				$reserve = new reserve($obj_id);
				//grab notes and include item notes if requested
				$notes = $reserve->getNotes($get_item_notes_for_reserve);		
			break;
			
			case 'item':
				//init new rItem obj
				$item = new reserveItem($obj_id);
				//get notes
				$notes = $item->getNotes();
			break;
		}
		
		return $notes;
	}
	
	
	/**
	 * @return void
	 * @param string $obj_type Type of object containing the notes - 'reserve', 'item', etc
	 * @param int $obj_id ID of the object
	 * @param string $note_text Note text
	 * @param string $note_type Note type
	 * @param int $note_id (optional) Note ID
	 * @desc Creates or edits a note; if the note_id is set, this note will be edited, else a new note will be created
	 */
	public function saveNote($obj_type, $obj_id, $note_text, $note_type, $note_id=null) {
		global $g_notetype;
		
		if(empty($obj_type) || empty($obj_id) || empty($note_text)) {
			return;
		}
		
		//item notes or reserve notes?
		switch($obj_type) {
			case 'item':
				//init new rItem obj
				$item = new reserveItem($obj_id);
			break;
			
			case 'reserve':
				//init reserve obj
				$reserve = new reserve($obj_id);
				//get the item
				$item = new reserveItem($reserve->getItemID());
			break;			
		}
		
		//add/edit instructor note to reserve
		if(($note_type==$g_notetype['instructor'] || $note_type==$g_notetype['copyright']) && ($reserve instanceof reserve)) {
			$reserve->setNote(trim($note_text), $note_type, $note_id);
		}
//		elseif(($note_type==$g_notetype['copyright'])) {	//add/edit copyright note to copyright
//			$copyright->setNote(trim($note_text), $note_type, $note_id);
//			//add to log
//			if(!empty($note_id)) {	//editing note
//				$copyright->log('edit note', '#'.$note_id.' - '.substr($note_text, 0, 30));
//			}
//			else {
//				$copyright->log('add note', substr($note_text, 0, 30));
//			}
//		}
		elseif($item instanceof reserveItem) {	//add/edit all other types to item
			$item->setNote(trim($note_text), $note_type, $note_id);
		}
	}
	
	
	/**
	 * @return void
	 * @param int $note_id ID of note to delete
	 * @param string $obj_type (optional) Object to witch this note is attached
	 * @param int $obj_id (optional) Object id
	 * @desc Deletes the specified note
	 */
	public function deleteNote($note_id, $obj_type=null, $obj_id=null) {
		global $g_notetype;
		
		if(!empty($note_id)) {
			$note = new note($note_id);
			if($note->getID()) {
				
#########################################
#	HIDE COPYRIGHT UNTIL FURTHER NOTICE #			
#########################################	
#				if($note->getType() == $g_notetype['copyright']) {
#					//attempt to log it
#					if(($obj_type=='copyright') && !empty($obj_id)) {
#						$copyright = new Copyright($obj_id);
#						$copyright->log('delete note', '#'.$note->getID());
#					}
#				}
#########################################

				$note->destroy();				
			}
		}		
	}
}
?>
