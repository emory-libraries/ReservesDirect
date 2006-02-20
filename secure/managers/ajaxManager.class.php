<?php
/*******************************************************************************
ajaxManager.class.php
AJAX Manager class

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
require_once("secure/managers/baseManager.class.php");
require_once("secure/displayers/ajaxDisplayer.class.php");

class ajaxManager extends baseManager {
	
	/**
	 * @desc Takes an unlimited number of arguments.  The only required on is $cmd as the first arg.
	 */	
	public function ajaxManager($cmd=null) {
		$this->displayClass = 'ajaxDisplayer';
		
		switch($cmd) {
			case 'lookupClass':
			case 'lookupUser':
				$args = func_get_args();
				call_user_func_array(array($this, 'lookup'), $args);
			break;
		}
	}
	
	/**
	 * @return void
	 * @param string $cmd Action to take
	 * @param string $nextCmd Action to take when the form is submitted
	 * @param string $tab Which category this will fit into
	 * @param string $button_label What to label the submit button
	 * @param array $propagated_info Additional information that will be included in the form as hidden fields
	 * @param boolean $standalone If true, a complete form is displayed; if false, only the input fields (and ajax js) is displayed for insertion into a form.
	 * @param array $additional_args an array of any other arguments that may need to be passed
	 * @desc Displays textfield that is AJAX-enabled to search for a user
	 */
	public function lookup($cmd, $nextCmd, $tab, $button_label, $propagated_info=null, $standalone=true, $additional_args=null) {
		
		switch($cmd) {
			case 'lookupClass':
				$page = $tab;
				$loc  = "class lookup";

				$this->displayFunction = 'classLookup';
				$this->argList = array($nextCmd, $button_label, $propagated_info, $standalone);
			break;
			
			case 'lookupUser':
				$page = $tab;
				$loc  = "user lookup";
				
				//set some defaults
				$min_user_role = !empty($additional_args['min_user_role']) ? $additional_args['min_user_role'] : 0;
				$field_id = !empty($additional_args['field_id']) ? $additional_args['field_id'] : 'user_id';

				$this->displayFunction = 'userLookup';
				$this->argList = array($nextCmd, $button_label, $propagated_info, $standalone, $min_user_role, $field_id);					
			break;
		}
		
	}
	
}