<?php
/*******************************************************************************
proxyHost.class.php
Manipulates proxyHost data
Contains 1 classes: proxyURL

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

/**
 * @desc Class for manipulating proxyHost information
 */
class proxyHost {
	/**
	 * Declaration
	 */


	/**
	 * @return string proxied URL
	 * @param  string URL
	 * @desc   if url requires proxy proxy prefix is added
	 */
	public static function proxyURL($url) {
		global $g_dbConn;
		
		$fragments = parse_url($url);
		$host = $fragments['host']; 
		if (isset($fragments['port'])) $host .= ":" . $fragments['port']; 			
		
		
		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql = "SELECT prefix, partial_match, domain
						FROM proxied_hosts
							JOIN proxies ON proxied_hosts.proxy_id = proxies.id
						WHERE domain LIKE ?";
				
		}
		$rs = $g_dbConn->query($sql, "%$host");
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
			
		$row = $row = $rs->fetchRow(DB_FETCHMODE_ASSOC);	
		
		if ($row['partial_match'] == 1 || ($row['partial_match'] == 0 && $row['domain'] == $host))
		{
			return $row['prefix'] . $url;
		} else {
			return $url;
		}
	}
}