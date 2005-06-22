<?
/*******************************************************************************
note.class.php
Note Primitive Object

Created by Jason White (jbwhite@emory.edu)

This file is part of GNU ReservesDirect 2.1

Copyright (c) 2004-2005 Emory University, Atlanta, Georgia.

ReservesDirect 2.1 is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

ReservesDirect 2.1 is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with ReservesDirect 2.1; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

Reserves Direct 2.1 is located at:
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
	function note($noteID=NULL)
	{
		$n = (!is_null($noteID)) ? $noteID : $this->createNewNote();
		$this->getNoteByID($n);
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
	function createNewNote()
	{
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql  = "INSERT INTO notes (note, target_id, target_table, type) VALUES ('', 0, '', '')";
				$sql2 = "SELECT LAST_INSERT_ID() FROM notes";
		}

		$rs = $g_dbConn->query($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$rs = $g_dbConn->query($sql2);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$row = $rs->fetchRow();
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		  return $row[0];
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

}

?>
