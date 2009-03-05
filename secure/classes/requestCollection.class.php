<?
/*******************************************************************************
requestCollection.class.php
request Collection Object

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

require_once("secure/classes/request.class.php");

class requestCollection extends ArrayObject 
{	
	
	function sort($sortBy = "request_id")
	{
				
		switch ($sortBy)
		{
			case "call_number":				
				$call_back = "_sort_by_call_number";
			break;
			
			case "request_id":
			default:
				$call_back = "_sort_by_id";
				
		}
		
		usort($this, array("requestCollection", $call_back));		
	}
	
	public function id_list()
	{
		$rv = "";
		foreach ($this as $r)
		{
			$rv .= $r->getRequestID() . ",";
		}
		return rtrim($rv, ","); //strip trailing ,
	}
		
	private static function _sort_by_call_number($a, $b)
	{
		return strcasecmp($a->holdings[0]['callNum'], $b->holdings[0]['callNum']);		
	}
	
	private static function _sort_by_id($a, $b)
	{
	    if ($a->request_id == $b->request_id) {
	        return 0;
	    }		
		return ($a->request_id < $b->request_id) ? -1 : 1;
	}	
}