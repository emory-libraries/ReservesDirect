<?
/*******************************************************************************
reservesViewer.php
reservesViewer.php logs user access to reserves items

Reserves Direct 2.0

Copyright (c) 2004 Emory University General Libraries

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be included
in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

Created by Jason White (jbwhite@emory.edu)

Reserves Direct 2.0 is located at:
http://coursecontrol.sourceforge.net/

*******************************************************************************/
	require_once("config.inc.php");
	require_once("secure/common.inc.php");
	require_once("classes/reserves.class.php");
	require_once("classes/reserveItem.class.php");
	require_once("classes/user.class.php");
	
	include("session.inc.php");


    import_request_variables("g", "in_");
    
	$reserve = new reserve($in_reserve);
	$reserve->getItem();
	
	//$helper = new helpApplication($item->getMimeType());
    
    // Redirect the user to where they want to go and continue processing
    header("Content-Type: " . $reserve->item->getMimeType());
    header("Location:  " . $reserve->item->getURL());
       
    // Log the user, instruct_id and access time into the reserves_viewed table
    $reserve->addUserView($in_viewer);

    exit(0);
?>
    
    
