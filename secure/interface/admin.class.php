<?
/*******************************************************************************
admin.class.php
Admin Interface

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
require_once("secure/interface/staff.class.php");
require_once("secure/classes/courseInstance.class.php");
require_once("secure/common.inc.php");

class admin extends staff
{
	//Attributes

	function admin($userName=null)
	{
		if (!is_null($userName)) {
			$this->getUserByUserName($userName);
		}
	}
}
