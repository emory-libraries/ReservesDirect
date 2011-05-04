<?
/*******************************************************************************
RD_Ils_Abstract
Abstract Implementation of ILS

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
This is an abstract class for documentation on php abstract classes see
http://www.php.net/abstract

All fuctions marked as abstract must be implemented in derived classes
*******************************************************************************/
require_once("lib/RD/Ils/Exception.php");

abstract class RD_Ils_Abstract
{
	public $_ilsName;	
	public $_view;
	protected $_reservable_formats = array();
	
	protected $create_reserve_script;
	protected $holdings_script;	
	
	public function __construct($basePath)
	{
//		$this->_view = new Zend_View();
//		$this->_view->setBasePath($basePath);	
		
		$this->setILSName();
		$this->setReservableFormats();
	}

    /**
     * Set the local name of ILS
     *
     * @param  void
     * @return void
    */		
	protected abstract function setILSName();  
	
    /**
     * Load Array of items types which may be placed on reserve
     *
     * @param  void
     * @return void
    */		
	protected abstract function setReservableFormats();
	public function getReserveableFormats() { return $this->_reservable_formats; }

	/**
     * Determine if passed format is reservable
     *
     * @param  string format
     * @return boolean
     * 
     * This function may be overloaded if needed
    */		
	public function isReservableFormat($format)
	{
		return in_array($format, $this->_reservable_formats);
	}
			
    /**
     * Return HTML form to collect necessary information to create reserve
     *    preferred method: render template file found in view directory
     * 	  secondary method: return HTML as string
     *
     * @param  void
     * @return HTML form
    */			
	public abstract function displayReserveForm();	
	
    /**
     * Return Array containing Holding data from ILS
     *
     * @param  void
     * @return Array
    */				
	public abstract function getHoldings($key, $keyType = 'barcode');
	
    /**
     * Set reserve in ILS
     *
     * @param  void
     * @return RD_Ils_Result
    */				
	public abstract function createReserve(Array $form_vars, Reserve $reserve);

    /**
     * Search ILS for items
     *
     * @param  String $searchField
     * @return RD_Ils_Result
    */	
	public abstract function search($search_field, $search_term);	
}
?>