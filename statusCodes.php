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
			header("HTTP/1.0 403 Permission Denied");
	?>
		<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
		<html><head>
		<title>403 Forbidden</title>
		</head><body>
		<h1>Forbidden</h1>
		<p>You don't have permission to access the requested file on this server.</p>
        <p><?=$_SERVER['REQUEST_URI']?></p>
		<hr />
		<? echo "ReservesDirect" . $_SERVER["SERVER_SIGNATURE"]; ?>
		</body></html>
	<?
		break;

        case '404':
            header("HTTP/1.0 404 Not Found");

	?>
		<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
		<html><head>
		<title>404 Not Found</title>
		</head><body>
		<h1>Not Found</h1>
		<p>The requested resource was not found on this server.</p>
        <p><?=$_SERVER['REQUEST_URI']?></p>
		<hr />
		<? echo "ReservesDirect" . $_SERVER["SERVER_SIGNATURE"]; ?>
		</body></html>
	<?
        
	}
}
?>
