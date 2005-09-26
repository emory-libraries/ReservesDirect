<?
/*******************************************************************************
rss.php
This page generate rss xml

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
error_reporting(0);
require_once("secure/config.inc.php");
require_once("secure/common.inc.php");

require_once("secure/classes/reserves.class.php");
require_once("secure/classes/course.class.php");
require_once("secure/classes/note.class.php");
require_once("secure/classes/courseInstance.class.php");
require_once("secure/classes/reserveItem.class.php");
require_once("secure/interface/instructor.class.php");
require_once("secure/interface/student.class.php");


if (!isset($_REQUEST['ci']))
	{
		flush;
		echo "<?xml version=\"1.0\"?>\n";
		echo "<!DOCTYPE rss ["; include('rss/ansel_unicode.ent');  echo "]>\n";
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
	$ci->getActiveReserves();

	flush;

	header("Content-Type: application/xml");
    echo "<?xml version=\"1.0\"?>\n";
    echo "<!DOCTYPE rss ["; include('rss/ansel_unicode.ent');  echo "]>\n";
    echo "<rss version=\"2.0\">\n";
    echo "	<channel>\n";

    echo "		<title>" . htmlentities($ci->course->displayCourseNo() . " " . $ci->course->name . " " . $ci->displayTerm()) . " Reserve List</title>\n";
    echo "		<link>".htmlentities($g_siteURL)."</link>\n";

    foreach($ci->instructorList as $instr)
    	echo "		<managingEditor>" . htmlentities($instr->getEmail() . " (" . $instr->getName()) . ")</managingEditor>\n";

    echo "		<webMaster>$g_reservesEmail (Reserves Desk)</webMaster>\n";

    echo "		<description>";
    echo 		"Course Reserves for" . htmlentities($ci->course->displayCourseNo() . " " . $ci->course->name . " " . $ci->displayTerm()) . "&lt;br/&gt;";
    echo 		"taught by: ";
    foreach($ci->instructorList as $instr)
    	echo htmlentities($instr->getEmail() . " (" . $instr->getName()) . ")&lt;br/&gt;";

    echo		"Helper Applications required for viewing reserves: &lt;a href=\"http://www.adobe.com/products/acrobat/readstep2.html\"&gt;Adobe Acrobat Reader&lt;/a&gt;";
    echo 		"</description>\n";

    //$rItem = new reserveItem();

    foreach ($ci->reserveList as $rItem)
    {
    	$rItem->getItem();
    	$rItem->getNotes();

    	echo "		<item>";
    	echo "			<link>" . htmlentities($g_siteURL."/reservesViewer.php?viewer=-115&reserve=". $rItem->getReserveID() ."&location=" . $rItem->item->getURL()) . "</link>\n";
    	echo "			<title>".htmlentities($rItem->item->getTitle())."</title>\n";

    	echo "			<description>";

    	//ouput what we have as the description
    		if ($rItem->item->getAuthor() != "") 				echo htmlentities($rItem->item->getAuthor()) . "&lt;br/&gt;";
    		if ($rItem->item->getPerformer() != "") 			echo "preformed by: " . htmlentities($rItem->item->getPerformer()) . "&lt;br/&gt;";

    		if ($rItem->item->getVolumeTitle() != "")			echo htmlentities($rItem->item->getVolumeTitle() . " " . $rItem->item->getVolumeEdition() . " " . $rItem->item->getPagesTimes()) . "&lt;br/&gt;";
    		elseif ($rItem->item->getVolumeEdition() != "")		echo htmlentities($rItem->item->getVolumeEdition() . " " . $rItem->item->getPagesTimes()) . "&lt;br/&gt;";
    		elseif ($rItem->item->getPagesTimes() != "")		echo htmlentities($rItem->item->getPagesTimes()) . "&lt;br/&gt;";

    		if ($rItem->item->getSource() != "") 				echo htmlentities($rItem->item->getSource()) . "&lt;br/&gt;";

    		foreach ($rItem->item->notes as $n)
    		{
    			if ($n->getType() == 'Instructor') echo htmlentities($n->getText()) . "&lt;br/&gt;";
    			elseif ($n->getType() == 'Content') echo htmlentities($n->getText()) . "&lt;br/&gt;";

    		}
//    	echo 			htmlentities("&lt;hr noshade/&gt;");
    	echo "			</description>\n";
    	echo "		</item>\n";
    }


	echo "</channel>";
	echo "</rss>\n";
?>
