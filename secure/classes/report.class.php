<?
/*******************************************************************************
report.class.php
Report Primitive Object

Created by Jason White (jbwhite@emory.edu)

This file is part of GNU ReservesDirect 2.1

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

Reserves Direct 2.1 is located at:
http://www.reservesdirect.org/

*******************************************************************************/

class cachedReport extends report {
	/**
	* Attributes
	*/
	protected $isCacheable;
	protected $cacheID;
	protected $cacheParamData;
	protected $cacheData;	
	protected $cacheRefreshDelay;
	protected $cacheLastModDate;

	/**
	 * @return void
	 * @param int $report_id ID of the report
	 * @desc Constructor: initializes object; fetches cache data and report info.
	 */
	public function __construct($report_id) {
		if(empty($report_id)) {	//do not allow an empty object to be created
			 trigger_error('Error: report ID is required when initializing a cachedReport object', E_USER_ERROR);
		}
		
		parent::__construct($report_id);	//fetch report meta
		$this->fetchCacheMeta();	//fetch report cache meta
	}
	
	/**
	 * @return void
	 * @desc Fetches cache meta -- whether the report should be cached and how long should the cache refresh delay be -- from the reports table.
	 */
	protected function fetchCacheMeta() {
		global $g_dbConn;
		
		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql = 'SELECT cached, cache_refresh_delay FROM reports WHERE report_id='.$g_dbConn->quoteSmart($this->reportID);
		}
		
		$rs = $g_dbConn->getRow($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		$this->isCacheable = ($rs[0]==1) ? true : false;
		$this->cacheRefreshDelay = $rs[1];				
	}
	
	/**
	 * @return array
	 * @desc Fetch cached data, or run a live report and return report data
	 */
	public function doQry() {
		global $g_dbConn;
		
		if($this->checkParameters()) {	//if all parameter data has been entered
			if($this->isCacheable) {	//is this report cached?
				$this->fetchCache();	//fetch cache data
				
				if($this->checkCache() && (($cacheData = unserialize($this->cacheData)) !== false)) {	//if we have some valid cached data
					return $cacheData;
				}
				else {	//need to refresh data
					$data = parent::doQry();
					$this->storeCache(&$data);
					return $data;
				}
				
			}
			else {	//report not cacheable, run a live one
				return parent::doQry();
			}
		}
	}
	
	/**
	 * @return void
	 * @desc fetch cache data from db
	 */
	protected function fetchCache() {
		global $g_dbConn;
		
		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql = "SELECT report_cache_id, report_cache, UNIX_TIMESTAMP(last_modified)
						FROM reports_cache
						WHERE report_id = ".$g_dbConn->quoteSmart($this->reportID)." AND params_cache = ".$g_dbConn->quoteSmart(serialize($this->parameters_data));
		}
		
		//fetch all cache with this report id
		$rs = $g_dbConn->getRow($sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		list($this->cacheID, $this->cacheData, $this->cacheLastModDate) = $rs;
	}
	
	protected function checkCache() {
		//see if there is some data AND it is not expired
		return (!empty($this->cacheData) && (time() <= strtotime('+'.$this->cacheRefreshDelay.' hours', $this->cacheLastModDate)));
	}
	
	protected function storeCache($report_data) {
		global $g_dbConn;
		
		switch($g_dbConn->phptype) {
			default:	//mysql
				//update sql
				$sql_update = 'UPDATE reports_cache
								SET last_modified = NOW(), params_cache = ?, report_cache = ? 
								WHERE report_cache_id = !';				
				//insert sql		
				$sql_insert = 'INSERT INTO reports_cache (report_id, params_cache, report_cache) VALUES (!, ?, ?)';
		}
		
		if(!empty($this->cacheID)) {	//cache expired, update it
			$rs = $g_dbConn->query($sql_update, array(serialize($this->parameters_data), serialize($report_data), $this->cacheID));
		}
		else {	//need to insert
			$rs = $g_dbConn->query($sql_insert, array($this->reportID, serialize($this->parameters_data), serialize($report_data)));
		}
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
	}
}


class report
{
	//Attributes
	public $reportID;
	public $title;
	public $sql;
	public $parameters;
	public $parameters_data;
	public $param_group;
	public $minPermission;
	public $order;

	public function __construct($report_id) {
		if(!empty($report_id)) {
			$this->getReportByID($report_id);
		}			
	}
		
	function getReportByID($reportID) {
		global $g_dbConn;

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql  = "SELECT report_id, title, `sql`, parameters, min_permissions, sort_order, param_group "
					  . "FROM reports "
					  . "WHERE report_id = !";

		}

		$rs = $g_dbConn->query($sql, $reportID);		
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		list($this->reportID, $this->title, $this->sql, $parameters_string, $this->minPermission, $this->order, $this->param_group) = $rs->fetchRow();

		//convert parameters string into an array
		$this->parameters = !empty($parameters_string) ? split('( )*,( )*', $parameters_string) : null;
	}

	/**
	 * @return array
	 * @desc Run the query and return an array of data rows
	 */
	function doQry() {
		global $g_dbConn;
		
		//make sure all parameters have been filled
		if($this->checkParameters()) {
			$rs = $g_dbConn->query($this->sql, $this->parameters_data);	
			if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
			
			$tmpArray = array();
			while ($row = $rs->fetchRow(DB_FETCHMODE_ASSOC)) {		
				$tmpArray[] = $row;
			}						
			return $tmpArray;
		}
		else {
			return null;
		}
	}
	
	/**
	 * @return void
	 * @param array $data_array
	 * @desc Looks in the data array (usually $_REQUEST) and pulls out data indexed by parameters.
	 */
	public function fillParameters($data_array) {
		//fill data
		foreach($this->parameters as $param) {
			if(!empty($data_array[$param])) {
				//handle selection of multiple param values for use in sql WHERE ... IN (<..>) by imploding them into a string
				$this->parameters_data[$param] = is_array($data_array[$param]) ? implode(',', $data_array[$param]) : $data_array[$param];
			}
		}
	}
	
	/**
	 * @return boolean
	 * @desc Returns true if all parameters have been filled, false otherwise.
	 */
	public function checkParameters() {
		//do a quick and dirty check to see if all params have been filled
		return (count($this->parameters)==count($this->parameters_data)) ? true : false;
	}
	
	function getReportID()	{ return $this->reportID; }
	function getTitle() 	{ return $this->title; }
	function getSQL() 		{ return $this->sql; }	
	function getOrder() 	{ return $this->order; }	
	function getParameters() { return $this->parameters; }
	function getParam_group() { return $this->param_group; }
	function getMinPermission() { return $this->minPermission; }
}
?>
