<?php
/*******************************************************************************
calendar.class.php
Wrapper class for JSCalendar

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
require_once('secure/jscalendar/calendar.php');

class Calendar extends DHTML_Calendar {
	
	//declaration
	protected $default_options;	//array of default options
		
	
	/**
	 * @desc Constructor: initiates a calendar object
	 */
	function __construct($path='secure/jscalendar/', $lang='en', $css='skins/aqua/theme', $stripped=true) {
		//initialize with preset attributes
		parent::DHTML_Calendar($path, $lang, $css, $stripped);
		
		//set default options
		$this->default_options = array(
			'ifFormat'		=> '%Y-%m-%d',
			'singleClick'	=> true,
			'firstDay'		=> 0,	//start week on Sunday
			'weekNumbers'	=> false,	//show week numbers
			'showsTime'		=> false,
			'cache'			=> false,
			'range'			=> array(date('Y'), date('Y', mktime(date('Y')+1)))	//limit dates from current year to next year
//'range' => '[2000,2010]'
		);
			
		//merge these options w/ the parent's defaults
		$this->calendar_options = array_merge($this->calendar_options, $this->default_options);
	}
	
	
	/**
	 * @return string
	 * @param string $target ID of the date target element (input, textfield, etc)
	 * @param string $default_date Initial date highlighted when calendar opens.
	 * @desc returns HTML/JavaScript code setting up a calendar widget + button
	 */	
	public function getWidgetAndTrigger($target, $default_date = null) {
		$id = $this->_gen_id();
		
		$html = '<a href="#" id="'.$this->_trigger_id($id).'"><img align="middle" border="0" src="'.$this->calendar_lib_path.'calendar.gif" alt="icon" /></a>';
		$html .= $this->getWidget($this->_trigger_id($id), $target, $default_date);
		
		return $html;
	}
	
	
	/**
	 * @return string
	 * @param string $trigger ID of the calendar trigger element (button, img, etc)
	 * @param string $target ID of the date target element (input, textfield, etc)
	 * @param string $default_date Initial date highlighted when calendar opens.
	 * @desc returns HTML/JavaScript code setting up a calendar widget
	 */	
	public function getWidget($trigger, $target, $default_date = null) {
		return $this->make_calendar(array(
			'inputField'	=> $target,
			'button'		=> $trigger,
			'date'			=> $this->_getDefDate($default_date)
		));
	}
	
	
	/**
	 * @return string
	 * @param string $field_name Name of the html input field which will receive the date (target)
	 * @param string $default_date Initial date highlighted when calendar opens.
	 * @desc returns HTML/JavaScript code adding an input target field AND setting up a calendar widget
	 */
	public function getDateFieldWidget($field_name, $default_date=null) {
		$date = $this->_getDefDate($default_date);
		
		return $this->make_input_field(
					array('date' => $date),
					array(
						'name'	=> $field_name,
						'value'	=> $date,
						'size'	=> 10
					)
				);
	}
	

	/**
	 * @return string
	 * @param string $date Date value to use
	 * @desc formats the default date; uses default_options[ifFormat] formatting as reference
	 */
	protected function _getDefDate($date) {
		//use default_date[ifFormat] format, but strip out % (used by jscript)
		$format = !empty($default_date['ifFormat']) ? str_replace('%', '', $default_date['ifFormat']) : 'm/d/Y';
		//return formatted date, or null if no date set
		return !empty($date) ? date($format, strtotime($date)) : null;
	}
	
	
	/**
	 * @return string
	 * @param array $array An array of settings; may be neseted
	 * @param boolean $with_key (optional) Function will ignore keys, if this is set to false
	 * @desc I am overwriting parent's function, since that one does not work for nested arrays. This functionality is necessary to specify the 'range' option, whish is a nested array. This function is now pseudo-recursive
	 */
	protected function _make_js_hash($array, $with_key=true) {
		$string = '';
		
		foreach($array as $key=>$val) {
			//check type of setting
			if(is_bool($val)) {
				$val = $val ? 'true' : 'false';
			}
			elseif(is_array($val)) {
				$val = '['.$this->_make_js_hash($val, false).']';
			}
			elseif(!is_numeric($val)) {
				$val = '"'.$val.'"';
			}
			
			//add onto the string
			if(!empty($string)) {
				$string .= ',';
			}
			
			$string .= $with_key ? '"'.$key.'":'.$val : $val;
		}

		return $string;	
	}
}
?>
