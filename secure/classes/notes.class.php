<?php
/*******************************************************************************
notes.class.php
Notes Object

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

//need the note class
require_once('secure/classes/note.class.php');

/**
 * Notes Object
 * - Manipulation of note-groups.
 * - To be used as a base class for classes that use notes.
 */
abstract class Notes {
		
	//declaration
	private $targetTable;		//DB table to be used as target
	private $targetID;			//DB ID of the target record
	private $noteType;			//Note type (optional)
	private $notes = array();	//Array of note objects
	
	
	/**
	 * @return void
	 * @param string $target_table Target DB table
	 * @param int $target_id Target DB row id
	 * @param string $note_type (optional) If the object only has one type of note, then it can be set through this setup method
	 * @desc Initializes private variables
	 */
	protected function setupNotes($target_table, $target_id, $note_type=null)  {
		$this->targetTable = $target_table;
		$this->targetID = $target_id;
		$this->noteType = $note_type;		
	}
	
	
	/**
	 * @return void
	 * @desc Deletes all notes for the target from the DB. Protected so that it can only be called from the class itself.
	 */
	protected function destroyNotes() {
		global $g_dbConn;
		
		//build query based on DB type
		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql = 'DELETE FROM notes
						WHERE target_table = '.$g_dbConn->quoteSmart($this->targetTable).'
							AND target_id = '.$this->targetID;
		}
		
		//query
		$rs = $g_dbConn->query($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

		
	/**
	 * @return string
	 * @param string note type
	 * @desc Makes sure there is a note type set (through method call or setup). function argument takes precedence over setup-defined var. Returns type on success. If both empty, triggers error and returns null.
	 */
	protected function pickNoteType($note_type) {
		//try to set to default if empty
		$type = !empty($note_type) ? $note_type : $this->noteType;
		
		//error check
		if(empty($type)) {	//error
			trigger_error('Notes -> note-type: Must set note type during function call or object initiation.', E_USER_ERROR);
			return null;
		}
		
		return $type;
	}
	
	
	/**
	* @return void
	* @param string $note_type (optional) May specify which type of notes to get
	* @desc Queries the DB for notes; Returns array of note objects
	*/
	protected function fetchNotesFromDB($note_type=null) {
		global $g_dbConn;
		
		//build query based on DB type
		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql = 'SELECT note_id
						FROM notes
						WHERE target_table = '.$g_dbConn->quoteSmart($this->targetTable).'
							AND target_id = '.$this->targetID;
				
				//are we selecting only certain type?
				$sql .= !empty($note_type) ? ' AND type='.$g_dbConn->quoteSmart($note_type) : '';
		}
		
		//query
		$rs = $g_dbConn->query($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		//reset $notes array
		$this->notes = array();
		
		while($row = $rs->fetchRow()) {
			$this->notes[] = new note($row[0]);	//fetch the note object and store to array
		}
	}
	
	
	/**
	 * @return void
	 * @desc Fetches all notes for this target as object notes and stores them in notes[]
	 */
	public function fetchNotes() {
		$this->fetchNotesFromDB();
	}
	
	
	/**
	 * @return void
	 * @param string $note_type (optional for some) The type of note wanted. If note type set through setup, this can be left unset; otherwise, it is required.
	 * @desc Fetches all notes for this target as object notes and stores them in notes[]
	 */
	public function fetchNotesByType($note_type=null) {
		//get note type
		if(!is_null($type = $this->pickNoteType($note_type))) {
			$this->fetchNotesFromDB($type);
		}
	}
	
	
	/**
	 * @return array
	 * @desc Returns all notes for this target as array ofobject notes
	 */
	public function getNotes() {
		return $this->notes;
	}

	
	/**
	 * @return note object
	 * @param string $note_text Body of note
	 * @param string $note_type (optional for some) The type of note. If note type set through setup, this can be left unset
	 * @param int $note_id (optional) If set, attempts to update an existing note, as opposed to creating a new one
	 * @desc Creates a new note object (or uses an old one if a note_id is provided) and sets its attributes in the DB. Returns resulting note object.
	 */
	public function setNote($note_text, $note_type=null, $note_id=null) {
		//get note type
		if(!is_null($type = $this->pickNoteType($note_type))) {
			//create or fetch a note object
			$note = new note($note_id);
			//set data
			$note->setTarget($this->targetID, $this->targetTable);
			$note->setType($type);
			$note->setText($note_text);
			
			if(empty($note_id)) {	//if note_id is empty, we are creating a new note
				$this->notes[] = $note;	//add the new note to array
			}
			//else the note is already in the array, we are just updating the info
			
			//return result
			return $note;
		}
	}
	
	
	/**
	 * @return void
	 * @param int $target_id ID of destination target
	 * @desc Duplicates the notes array of this object with a new target ID
	 */
	public function duplicateNotes($target_id) {
		//a bit of error checking
		if(empty($target_id)) {
			return;
		}
		
		foreach($this->notes as $note) {
			$note->duplicate($target_id);
		}
	}

	
}

?>