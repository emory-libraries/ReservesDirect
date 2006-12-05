<?
/*******************************************************************************
statusCodes.php
return custom errors

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
function sendStatusCode($status_code)
{
	switch ($status_code)
	{
		case '403':
			header("HTTP/1.0 403 Forbidden");
            break;
        case '404':
            header("HTTP/1.0 404 Not Found");
            break;
        default:
            header("HTTP/1.0 $status_code");
            break;
        
	}
        include("secure/html/403-404.html");
}
?>
