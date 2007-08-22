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
	public static function proxyURL($url, $username) 
	{	
		//use parse_url to get url fragments	
		$fragments = parse_url($url);
		$host = $fragments['host']; 
		if (isset($fragments['port'])) $host .= ":" . $fragments['port']; 			
		
		//split on .
		$parts = array_reverse(split("\.", $host));

		
		//recursively search database for partial urls
		//reduce count by one we do not want to search for TLD
		$times = count($parts) - 1;		
		for ($i=0; $i < $times; $i++)
		{
			//reverse order to use array pop this also mean TDL will always be in 0 pos
			$match = proxyHost::doSearch(implode('.', array_reverse($parts)));
			
			if (!is_null($match))
			{
				//if we have a hit then stop looking
				break;
			}
			
			array_pop($parts);  //shorten url then try again						
		}
		
		if ($match['partial_match'] == 1 || ($match['partial_match'] == 0 && $match['domain'] == $host))
		{
			//return $match['prefix'] . $url;
			return proxyHost::generateEZproxyTicket($match['prefix'], $url, $username);
		} else {
			return $url;
		}		
	}	
	
	public static function doSearch($host)
	{
		global $g_dbConn;					
		
		switch($g_dbConn->phptype) {
			default:	//mysql
				$sql = "SELECT prefix, partial_match, domain
						FROM proxied_hosts
							JOIN proxies ON proxied_hosts.proxy_id = proxies.id
						WHERE domain LIKE ?";
				
		}
		$rs = $g_dbConn->query($sql, "%$host");

		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
			
		return $rs->fetchRow(DB_FETCHMODE_ASSOC);			
	}
	
	function generateEZproxyTicket($EZproxyServerURL, $url, $username)
	{
		global $g_EZproxyAuthorizationKey;
		
	
		$packet = '$u' . time();
		
		$EZproxyTicket = urlencode(md5($g_EZproxyAuthorizationKey . $username . $packet) . $packet);
		return $EZproxyServerURL . "/login?user=" . urlencode($username) . "&ticket=" . $EZproxyTicket . "&url=" . $url;
	}
}