<?
/*******************************************************************************
noteManager.class.php


Created by Kathy Washington (kawashi@emory.edu)

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
require_once("secure/displayers/noteDisplayer.class.php");
require_once("secure/managers/itemManager.class.php");
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

				if ($_REQUEST['noteText'])
				{
					$noteText = trim($_REQUEST['noteText']);
					if ($noteText) {
						//switch b/n editing item or editing reserve+item
						if(!empty($_REQUEST['reserveID'])) {	//editing item+reserve
							//get reserve
							$reserve = new reserve($_REQUEST['reserveID']);
							//get item
							$item = new reserveItem($reserve->getItemID());
						}
						elseif(!empty($_REQUEST['itemID'])) {	//editing item only
							$item = new reserveItem($_REQUEST['itemID']);
						}
						else {	//no IDs set, error
							break;
						}

						$noteType = $_REQUEST['noteType'];

						if($noteType==$g_notetype['content'] || $noteType==$g_notetype['staff'] || $noteType==$g_notetype['copyright']) {
							$item->setNote($noteText, $noteType);
						}
						elseif(($noteType==$g_notetype['instructor']) && ($reserve instanceof reserve)) {
							$reserve->setNote($noteText);
						}
					}
				}

				$this->displayFunction = "displaySuccess";
				$this->argList = array(null);
			break;
		}
	}
}
?>