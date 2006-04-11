<?
/*******************************************************************************
noteManager.class.php


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
			
			case 'copyright':
				$copyright = new Copyright($obj_id);
				$notes = $copyright->getNotes();
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
			case 'reserve':
				//init reserve obj
				$reserve = new reserve($obj_id);
				//get the item
				$item = new reserveItem($reserve->getItemID());
			break;
			
			case 'item':
				//init new rItem obj
				$item = new reserveItem($obj_id);
			break;
			
			case 'copyright':
				$copyright = new Copyright($obj_id);
			break;
		}
		
		//add/edit instructor note to reserve
		if(($note_type==$g_notetype['instructor']) && ($reserve instanceof reserve)) {
			$reserve->setNote(trim($note_text), $note_type, $note_id);
		}
		elseif(($note_type==$g_notetype['copyright']) && ($copyright instanceof Copyright)) {	//add/edit copyright note to copyright
			$copyright->setNote(trim($note_text), $note_type, $note_id);
			//add to log
			if(!empty($note_id)) {	//editing note
				$copyright->log('edit note', '#'.$note_id.' - '.substr($note_text, 0, 30));
			}
			else {
				$copyright->log('add note', substr($note_text, 0, 30));
			}
		}
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
				if($note->getType() == $g_notetype['copyright']) {
					//attempt to log it
					if(($obj_type=='copyright') && !empty($obj_id)) {
						$copyright = new Copyright($obj_id);
						$copyright->log('delete note', '#'.$note->getID());
					}
				}
				$note->destroy();				
			}
		}		
	}
}
?>