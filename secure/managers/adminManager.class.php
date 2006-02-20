<?
/*******************************************************************************
adminManager.class.php


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
require_once("secure/displayers/adminDisplayer.class.php");
require_once("secure/classes/department.class.php");
require_once("secure/classes/library.class.php");

class adminManager
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


	function adminManager($cmd, $user, $request)
	{
		global $ci, $loc, $u, $alertMsg;
		
		$this->displayClass = "adminDisplayer";
		$this->user = $user;
		
		$function = (isset($request['function'])) ? $request['function'] : null;
		
		switch ($function)
		{
			
			case 'editDept':
				$libraries = $u->getLibraries();
			
				$this->displayFunction = "displayEditDept";
				$this->argList = array($function, $libraries);			
			break;
			
			case 'saveDept':
				$d_id = (isset($_REQUEST['dept_id']) && $_REQUEST['dept_id'] != "") ? $_REQUEST['dept_id'] : null;
				$dept = new department($d_id);
				
				if (is_null($d_id))
				{
					$dept->createDepartment($_REQUEST['dept_name'], $_REQUEST['dept_abbr'], $_REQUEST['library_id']);
					$alertMsg = "Department Successfully Added";
					$this->adminManager(null, $user, null);
				}
				else
				{
					$dept->setName($_REQUEST['dept_name']);
					$dept->setAbbr($_REQUEST['dept_abbr']);
					$dept->setLibraryID($_REQUEST['library_id']);
					if ($dept->updateDepartment())
					{
						$alertMsg = "Department Successfully Updated";
						$this->adminManager($cmd, $user, null);
					}
						
				}
			break;
			
			case 'editLib':
				$this->displayFunction = "displayEditLibrary";
				$this->argList = array($u->getLibraries());
			break;
			
			case 'saveLib':
				$lib_id = ($_REQUEST['lib_id']  != "") ? $_REQUEST['lib_id'] : null;
				$l = new library($lib_id);
				
				if (is_null($lib_id))
					$l->createNew(
						$_REQUEST['lib_name'],
						$_REQUEST['lib_nickname'],
						$_REQUEST['ils_prefix'],
						$_REQUEST['desk'],
						$_REQUEST['lib_url'],
						$_REQUEST['contactEmail'],
						$_REQUEST['monograph_library_id'],
						$_REQUEST['multimedia_library_id']
					);
				else 
				{
					$l->setLibrary($_REQUEST['lib_name']);
					$l->setLibraryNickname($_REQUEST['lib_nickname']);
					$l->setILS_prefix($_REQUEST['ils_prefix']);
					$l->setReserveDesk($_REQUEST['desk']);
					$l->setLibraryURL($_REQUEST['lib_url']);
					$l->setContactEmail($_REQUEST['contactEmail']);
					$l->setMonograph_library_id($_REQUEST['monograph_library_id']);
					$l->setMultimedia_library_id($_REQUEST['multimedia_library_id']);
					$l->update();
				}
				
				$alertMsg = "Library Successfully Updated";
				$this->adminManager($cmd, $user, null);
				
			break;
			
			default:
				$loc = "System Administration";
			
				$this->displayFunction = "displayAdminFunctions";
				$this->argList = null;			
		}
	}
}
?>
