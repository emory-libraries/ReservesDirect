<?php
/*******************************************************************************
reservesViewer.php
controls and logs user access to reserves items

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
	require_once("secure/config.inc.php");
	require_once("secure/common.inc.php");
	require_once("secure/classes/reserves.class.php");
	require_once("secure/classes/reserveItem.class.php");
	require_once("secure/classes/user.class.php");
	require_once("secure/classes/skins.class.php");
	
	include("secure/session.inc.php");
	include("statusCodes.php");
	
	header("Cache-control: private");	//send some headers
    header("Pragma: public");
	
	$user = new user($_SESSION['userID']);	//grab current user from session
	
	//get the reserve/item
	//When previewing items we do not have a reserve_id and must use the item_id to view the URL
	//This should only be accessed by staff/admin and will not be tracked
	if(isset($_REQUEST['reserve'])) {	//requesting reserve
		$reserve = new reserve($_REQUEST['reserve']);
		
		if($reserve->getItemForUser($user)) {	//make sure this user should be allowed access to the reserve
			$item =& $reserve->item;	//grab the item for info
		}
	
		//since a reserve was requested, track the views
		if($user->getRole() < $g_permission['instructor']) {	//only count student views
			$reserve->addUserView($user->getUserID());	//log user, reserve and access time
		}
	}
	elseif(isset($_REQUEST['item']) && ($user->getRole() >= $g_permission['proxy'])) {	//requesting item		
		$item = new reserveItem($_REQUEST['item']);	//grab the item for info
	} 
	
	//if we have an item object, then we try to serve the doc
	if($item instanceof reserveItem) {
		$url = $item->getURL();	//grab the url
        if ($url == FALSE) {
            sendStatusCode('404');
            exit;
        }
		
		if(ereg('^https?://',$url) != 1) {	//if item URL points to local server, serve the document directly
			if($stream = @fopen($g_documentDirectory . $url, r)) {	//open file for reading

                $author = ereg_replace("[^A-Za-z0-9]", "", $item->author);
                if ($author != "") {
                    $author = substr(($author), 0, 24) . "_";
                }
                
                $title = ereg_replace("[^A-Za-z0-9]", "", $item->title);
                if ($title != "") {
                    $title = substr(($title), 0, 24) . "_";
                }

                $ext = end(split('\.', $url));
                if ($ext != "") {
                    $ext = "." . $ext;
                }

                $filename = $author . $title . $item->itemID . $ext;

				//serve the doc			
				header('Content-Type: '.$item->getMimeType());
				header('Content-Disposition: inline; filename="'.$filename.'"');
				fpassthru($stream);
				fclose($stream);	//close file
			}
			else {	//file not found
				sendStatusCode(404);
			}
		}
		else {	//item is on remote server -- redirect
			header('Location: '.$url);
		}
	}
	else {	//no item, assume that no ID was specified
		header('"HTTP/1.0 403 Permission Denied');
		sendStatusCode(403);
	}
?>
