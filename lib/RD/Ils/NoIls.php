<?
/*******************************************************************************
RD_Ils_NoIls
Implementation of ILS

Created by Jason White (jbwhite@emory.edu)

This file is part of ReservesDirect

Copyright (c) 2004-2008 Emory University, Atlanta, Georgia.

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

*******************************************************************************
This class extends RD_Ils_Abstract

Implementing a system with no ils support
*******************************************************************************/
class RD_Ils_NoIls extends RD_Ils_Abstract
{
	protected $create_reserve_script = "";
	protected $holdings_script = "";

	protected function setILSName(){ $this->_ilsName = ''; }
	
	protected function setReservableFormats(){ $this->_reservable_formats = array(''); }
	
	public function createReserve(Array $form_vars, Reserve $reserve){ return ''; }
	
	public function displayReserveForm(){ return ''; }
	
	public function getHoldings($key){ return array(''); }
	
	public function isReservableFormat($format){ return (boolean) false; }
		
}
?>