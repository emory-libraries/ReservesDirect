#!/usr/local/bin/php -q

<?
/*******************************************************************************
parse_proxy_hosts.php
This script parses the proxied host list into the proxied_hosts table

Created by Jason White (jbwhite@emory.edu)

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


//set working directory
if (count($argv) < 2)
{
   echo "usage: parse_proxy_hosts.php <host.cfg> <path_to_rd>\n";
   exit(-1);
} else {
  chdir($argv[2]);
}

//delimiter
$feedDelimiter = " ";
$feedLineLength = 1000;

$proxyName = 'SFX';
$proxyPrefix = 'http://proxy.library.emory.edu/login?url=';

//open standard out
$stdout = fopen('php://stdout', 'w');

//open file
$filename = $argv[1];
$fp = fopen($filename, 'r');
if ($fp == false)
        fwrite($stdout, "Could not open file for read: $filename\n");


require_once("secure/config.inc.php");
require_once("secure/common.inc.php");
require_once("decode_semester.php");

//create needed sql statements
switch($g_dbConn->phptype) 
{
	default:	//mysql			
		$insert_host	 = "INSERT INTO proxied_hosts (proxy_id, domain, partial_match) VALUES (!,?,!)";
		$find_proxy		 = "SELECT id FROM proxies WHERE prefix = ?";
		$insert_proxy    = "INSERT INTO proxies (name, prefix) VALUES (?,?)";
}

$g_dbConn->autoCommit(false);

//get proxy id
$proxy_id =& $g_dbConn->getOne($find_proxy, array($proxyPrefix));
if (DB::isError($proxy_id)) 
{
	$g_dbConn->rollback();			
	fwrite($stdout, $proxy_id->getMessage() . "$find_proxy, $proxyPrefix \n"); 
	exit(-1);
}

if ($proxy_id == '')
{
	$rs =& $g_dbConn->query($insert_proxy, array($proxyName, $proxyPrefix));	
	if (DB::isError($proxy_id)) 
	{
		$g_dbConn->rollback();			
		fwrite($stdout, $count->getMessage() . "$insert_proxy, $proxyName, $proxyPrefix \n"); 
		exit(-1);
	}	
	
	$proxy_id =& $g_dbConn->getOne("SELECT last_inserted FROM proxies");
	if (DB::isError($proxy_id)) 
	{
		$g_dbConn->rollback();			
		fwrite($stdout, $proxy_id->getMessage() . " SELECT last_inserted FROM proxies\n"); 
		exit(-1);
	}	
}

while(($line = trim(fgets($fp, $feedLineLength))) != FALSE)
{	
	if ($line[0] != '#') //ignore comments
	{	
		list($directive, $domain) = split($feedDelimiter, $line);
				
		if (strtolower($directive[0]) == 'h')
			$partial_match = 0;
		elseif (strtolower($directive[0]) == 'd')			
			$partial_match = 1;
		else
			continue; //we dont need to process other directives
			
		$host = trim(rtrim($domain, '/'));
		$rs =& $g_dbConn->query($insert_host, array($proxy_id, $host, $partial_match));
	
		//echo ($sql . ", " .  $line[$net_id] . ", " .  $line[$last_name] . ", " .  $line[$first_name] . ", " .  $line[$email] . "\n");

		if (DB::isError($rs)) 
		{	
			if ($rs->getCode() != '-5')
			{
				$g_dbConn->rollback();			
				fwrite($stdout, $rs->getMessage() . " $directive $domain\n"); 
				exit(-1);
			}
		} 
		
		$rs = null;
	}		
}

$g_dbConn->commit();
$g_dbConn->disconnect();
fclose($fp);

exit(0);
?>
