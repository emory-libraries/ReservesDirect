<?
/*******************************************************************************
rss.php
This page generate rss xml

Created by Jason White (jbwhite@emory.edu)

This file is part of ReservesDirect.

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
error_reporting(0);
require_once("secure/config.inc.php");
require_once("secure/common.inc.php");

require_once("secure/classes/reserves.class.php");
require_once("secure/classes/course.class.php");
require_once("secure/classes/courseInstance.class.php");
require_once("secure/classes/reserveItem.class.php");
require_once('secure/classes/tree.class.php');
require_once("secure/interface/instructor.class.php");
require_once("secure/interface/student.class.php");


if (!isset($_REQUEST['ci']))
	{
		flush;
		echo "<?xml version=\"1.0\"?>\n";
		echo "<!DOCTYPE rss ["; 
// these entities are not valid in rss 2.0, and a lot of readers choke on them
//      include('rss/ansel_unicode.ent');  
        echo "]>\n";
    	echo "<rss version=\"2.0\">\n";
    	echo "	<channel>\n";
    	echo "		<error>Data could not be retrieved from rss.php ci not set. Please contact the systems administrator.</error>";
    	echo "	</channel>\n";
    	echo "</rss>\n";
    	exit;
	}


	//$i = new instructor();
	$ci = new courseInstance($_REQUEST['ci']);
	$ci->getCrossListings();
	$ci->getInstructors();
	$ci->getPrimaryCourse();
	//get reserves as a tree + recursive iterator
	$walker = $ci->getReservesAsTreeWalker('getActiveReserves');

	flush;

	header("Content-Type: application/xml");
    echo "<?xml version=\"1.0\"?>\n";
    echo "<!DOCTYPE rss ["; 
//  include('rss/ansel_unicode.ent');  
    echo "]>\n";
    echo "<rss version=\"2.0\">\n";
    echo "	<channel>\n\n";

    echo "		<title>" .  htmlentities(stripslashes($ci->course->displayCourseNo() . " " . $ci->course->name . " " . $ci->displayTerm())) . " - Reserve List</title>\n";
// mantis #429
//    echo "		<link>".$g_siteURL."/index.php?cmd=viewReservesList&amp;ci=".$_REQUEST['ci']."</link>\n";
    echo "		<link>$g_siteURL</link>\n";

// having multiple managingEditors is also invalid, but most readers handle it gracefully
    foreach($ci->instructorList as $instr)
        echo "		<managingEditor>" . $instr->getEmail() . " (" .  $instr->getName() . ") </managingEditor>\n" ;

    echo "		<webMaster>$g_reservesEmail (Reserves Desk)</webMaster>\n";

    echo "		<description>";
    echo 		"Course Reserves for " . htmlentities(stripslashes($ci->course->displayCourseNo() . " " . $ci->course->name . " " . $ci->displayTerm())) . ", ";
    echo 		"taught by:";
    foreach($ci->instructorList as $instr)
    	echo " " . $instr->getName() . " (" . $instr->getEmail() . ") ";

    echo		". Helper application for viewing reserves: Adobe Acrobat Reader, http://www.adobe.com/products/acrobat/readstep2.html .";
    echo 		"</description>\n\n";
    
    foreach($walker as $leaf) {
    	$rItem = new reserve($leaf->getID());
    	$rItem->getItem();
    	$itemNotes = $rItem->item->getNotes();
    	$resNotes = $rItem->getNotes();

    	echo "		<item>\n";
        
    	//do not show link for headings
    	if(!$rItem->item->isHeading()) {
	        if ($rItem->item->isPhysicalItem()) {
	            echo "          <link>" . htmlentities($g_reservesViewer . $rItem->item->getLocalControlKey()) . "</link>";
	        } else {
	            echo "			<link>" . htmlentities($g_siteURL."/reservesViewer.php?reserve=". $rItem->getReserveID() ."&location=" . $rItem->item->getURL()) . "</link>\n";
	        }
    	}
    	
        echo "			<title>" . $rItem->item->getTitle() . "</title>\n";

    	echo "			<description>";

    	//ouput what we have as the description
    		if ($rItem->item->getAuthor() != "")
                    echo trim($rItem->item->getAuthor()) . ". ";
                    
    		if ($rItem->item->getPerformer() != "")
                    echo "performed by: " . trim($rItem->item->getPerformer()) . ". ";

    		if ($rItem->item->getVolumeTitle() != "")
                    echo trim($rItem->item->getVolumeTitle() . " " . $rItem->item->getVolumeEdition() . " " . $rItem->item->getPagesTimes()) . ". ";
                    
    		elseif ($rItem->item->getVolumeEdition() != "")
                    echo trim($rItem->item->getVolumeEdition() . " " . $rItem->item->getPagesTimes()) . ". ";
                    
    		elseif ($rItem->item->getPagesTimes() != "")
                    echo trim($rItem->item->getPagesTimes()) . ". ";

    		if ($rItem->item->getSource() != "")
                    echo trim($rItem->item->getSource()) . ". ";

			foreach($itemNotes as $note) {
				if($note->getType() == 'Content') {
					echo $note->getText().'. ';
				}
			}
			foreach($resNotes as $note) {
				echo $note->getText().'. ';
			}

    	echo "</description>\n";
    	
    	//show category
    	if($rItem->item->isHeading()) {
    		echo '<category>heading_'.($walker->getDepth()+1).'</category>';
    	}
    	else {
    		echo "<category>reserve</category>";
    	}
    	
    	echo "		</item>\n\n";
    }


	echo "</channel>";
	echo "</rss>\n";
?>
