<?
/*******************************************************************************
reservesViewer.php
reservesViewer.php logs user access to reserves items

Created by Jason White (jbwhite@emory.edu)

This file is part of GNU ReservesDirect 2.1

Copyright (c) 2004-2005 Emory University, Atlanta, Georgia.

ReservesDirect 2.1 is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

ReservesDirect 2.1 is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with ReservesDirect 2.1; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

Reserves Direct 2.1 is located at:
http://www.reservesdirect.org/

*******************************************************************************/
	require_once("secure/config.inc.php");
	require_once("secure/common.inc.php");
	require_once("secure/classes/reserves.class.php");
	require_once("secure/classes/reserveItem.class.php");
	require_once("secure/classes/user.class.php");
	require_once("secure/classes/skins.class.php");

	include("secure/session.inc.php");


    import_request_variables("g", "in_");

	if (isset($in_item)) {
		$reserveItem = new reserveItem($in_item);
		// Redirect the user to where they want to go and continue processing
    	header("Content-Type: " . $reserveItem->getMimeType());
    	header("Location:  " . $reserveItem->getURL());
	} else {

    	$reserve = new reserve($in_reserve);
		$reserve->getItem();

		//$helper = new helpApplication($item->getMimeType());

    	// Redirect the user to where they want to go and continue processing
    	header("Content-Type: " . $reserve->item->getMimeType());
    	header("Location:  " . $reserve->item->getURL());

    	// Log the user, instruct_id and access time into the reserves_viewed table
    	$reserve->addUserView($in_viewer);
	}

    exit(0);
?>


