<?php
/*******************************************************************************
reservesViewer.php
controls and logs user access to reserves items

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
	require_once("secure/config.inc.php");
	require_once("secure/common.inc.php");
	require_once("secure/classes/reserves.class.php");
	require_once("secure/classes/reserveItem.class.php");
	require_once("secure/classes/proxyHost.class.php");
	require_once("secure/classes/user.class.php");
	require_once("secure/classes/skins.class.php");
	include("statusCodes.php");	
    
	//set up error-handling/debugging, skins, etc.
	//require_once("secure/session.inc.php");	
	
	header("Cache-control: private");	//send some headers
        header("Pragma: public");
        header("Cache-Control: no-cache");
        header("Pragma: no-cache");
        header("Expires: -1");

	
	//authenticate user
	//if user is valid, then initializes global user object as $u
	//else shows login page
	require_once('secure/auth.inc.php');
	
	//get the reserve/item
	//When previewing items we do not have a reserve_id and must use the item_id to view the URL
	//This should only be accessed by staff/admin and will not be tracked
	if(isset($_REQUEST['reserve'])) {	//requesting reserve
		$reserve = new reserve($_REQUEST['reserve']);
		
		if($reserve->getItemForUser($u)) {	//make sure this user should be allowed access to the reserve
			$item =& $reserve->item;	//grab the item for info
		}
	
		//since a reserve was requested, track the views
		if($u->getRole() < $g_permission['instructor']) {	//only count student views
			$reserve->addUserView($u->getUserID());	//log user, reserve and access time
		}
	}
	elseif(isset($_REQUEST['item']) && ($u->getRole() >= $g_permission['proxy'])) {	//requesting item		
		$item = new reserveItem($_REQUEST['item']);	//grab the item for info
	} 
	
	//if we have an item object, then we try to serve the doc
	if($item instanceof reserveItem) {
		$url = $item->getURL();	//grab the url
        if ($url == FALSE) {        	
            sendStatusCode('404');
            exit;
        }
		
		if($item->isLocalFile()) {	//if item URL points to local server, serve the document directly
			if($stream = @fopen($g_documentDirectory . $url, "rb")) {       //open file for reading

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
			//echo 'Location: '.proxyHost::proxyURL($url, $u->getUsername());exit;			
			header('Location: '.proxyHost::proxyURL($url, $u->getUsername()));
		}
	}
	else {	//no item, assume that no ID was specified
		header('"HTTP/1.0 403 Permission Denied');
		sendStatusCode(403);
	}

?>
