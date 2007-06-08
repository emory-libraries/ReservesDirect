<?php
/*******************************************************************************
json_wrapper.class.php
allows seamless usage of either PHP-built-in json functions or PEAR::Services_JSON
very basic - only encode/decode

Created by Dmitriy Panteleyev (dpantel@gmail.com)

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

/**
 * Very simple class that wraps built-in json_encode()/json_decode()
 * or PEAR::Services_JSON->encode()/decode() into single interface
 */
class JSON_Wrapper {
	protected $is_built_in;
	protected $pear_json_obj;
	
	function __construct() {
		if(function_exists('json_encode') && function_exists('json_decode')) {	//use built-in functions
			$this->is_built_in = true;
			$this->pear_json_obj = null;			
		}
		else {	//have to rely on PEAR
			require_once("PEAR/JSON.php");
			$this->is_built_in = false;
			$this->pear_json_obj = new Services_JSON();
		}
	}
	
	
	/**
	 * Returns $var as json-encoded string or FALSE on error
	 *
	 * @param mixed $var
	 * @return mixed
	 */
	public function encode($var) {
		if($this->is_built_in) {
			return json_encode($var);
		}
		elseif(!is_null($this->pear_json_obj)) {
			return $this->pear_json_obj->encode($var);
		}
		
		return false;
	}
	
	
	/**
	 * Returns the object encoded by $str of FALSE on error
	 *
	 * @param string $str
	 * @return mixed
	 */
	public function decode($str) {
		if($this->is_built_in) {
			return json_decode($str);
		}
		elseif(!is_null($this->pear_json_obj)) {
			return $this->pear_json_obj->decode($str);
		}
		
		return false;	
	}
}
?>