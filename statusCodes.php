<?
/*******************************************************************************
statusCodes.php
return custom errors

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
