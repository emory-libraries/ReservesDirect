<?
/*******************************************************************************
note.class.php
Note Primitive Object

Created by Jason White (jbwhite@emory.edu)

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

class note
{
	//Attributes
	public $noteID;
	public $targetTable;
	public $targetID;
	public $type;
	public $text;


	/**
	* Constructor Method
	* @param optional $noteID
	* @construct note and populate it passed noteID
	*/
	function note($noteID=NULL) {
		if(!empty($noteID))
			$this->getNoteByID($noteID);
		else
			$this->createNewNote();
	}

	/**
	* @return void
	* @param string $type
	* @desc set note type and update db
	*/
	function setType($type)
	{
		global $g_dbConn;

		$this->type = $type;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE notes SET type = ? WHERE note_id = !";
		}

		$rs = $g_dbConn->query($sql, array($type, $this->noteID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	/**
	* @return void
	* @param $text new note text
	* @desc set the note text
	*/
	function setText($text)
	{
		global $g_dbConn;

		$this->text = $text;
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE notes SET note = ? WHERE note_id = !";
		}
		$rs = $g_dbConn->query($sql, array($text, $this->noteID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	/**
	* @return void
	* @param $id foreign primary key, $table foreign table name
	* @desc set the foreign key for the note
	*/
	function setTarget($id, $table)
	{
		global $g_dbConn;

		$this->targetTable = $table;
		$this->targetID = $id;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "UPDATE notes SET target_id = !, target_table = ? WHERE note_id = !";
		}

		$rs = $g_dbConn->query($sql, array($id, $table, $this->noteID));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

	function getType() { return $this->type; }

	function getText() { return stripslashes($this->text); }

	/**
	* @return int noteID
	* @desc return the note id assigned by the DB
	*/
	function getID()   { return $this->noteID; }

	/**
	* @return int newNoteID
	* @desc Create new note object and insert new record into db
	*/
	function createNewNote() {
		global $g_dbConn;

		switch ($g_dbConn->phptype) {
			default: //'mysql'
				$sql  = "INSERT INTO notes () VALUES ()";
				$sql2 = "SELECT LAST_INSERT_ID() FROM notes";
		}

		$rs = $g_dbConn->query($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		//get the id	
		$rs = $g_dbConn->getOne($sql2);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		$this->noteID = $rs;

		return $rs;
	}

	/**
	* @return void
	* @param int $ID DB note_id
	* @PRIVATE desc get record from DB by NoteID
	*/
	private function getNoteByID($ID)
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "SELECT note_id, note, target_id, target_table, type "
					.  "FROM notes "
					.  "WHERE note_id = !";
		}

		$rs = $g_dbConn->query($sql, $ID);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		list($this->noteID, $this->text, $this->targetID, $this->targetTable, $this->type) = $rs->fetchRow();
	}

	/**
	* @return void
	* @desc destroy the database entry
	*/
	function destroy()
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = "DELETE "
					.  "FROM notes "
					.  "WHERE note_id = !"
					;
		}

		$rs = $g_dbConn->query($sql, $this->noteID);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}
	
	
	/**
	 * @return void
	 * @param int $new_target_id new target ID
	 * @desc Duplicate this note in the DB with the new target id.
	 */
	public function duplicate($new_target_id) {
		global $g_dbConn;
		
		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql = 'INSERT INTO notes (type, target_id, note, target_table)
						VALUES(?, !, ?, ?)';
		}
		
		//run the query
		$rs = $g_dbConn->query($sql, array($this->type, $new_target_id, $this->text, $this->targetTable));
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}

}

?>
