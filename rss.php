<?
/*******************************************************************************
rss.php
This page generate rss xml

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
require_once("common.inc.php");

require_once("classes/reserves.class.php");
require_once("classes/course.class.php");
require_once("classes/note.class.php");
require_once("classes/courseInstance.class.php");
require_once("classes/reserveItem.class.php");
require_once("interface/instructor.class.php");
require_once("interface/student.class.php");


if (!isset($_REQUEST['ci']))
	die ("<html><h1>Course Instacnce must be set</h1></html>");
	

	//$i = new instructor();
	$ci = new courseInstance($_REQUEST['ci']);
	$ci->getCrossListings();
	$ci->getInstructors();
	$ci->getPrimaryCourse();
	$ci->getReserves();
	
	//remove the follwoing
	$c = new course();


	
	header("Content-Type: application/xml");
    echo "<?xml version=\"1.0\"?>\n";
    echo "<!DOCTYPE rss ["; include('rss/ansel_unicode.ent');  echo "]>\n";
    echo "<rss version=\"2.0\">\n";
    echo "	<channel>\n";
    
    echo "		<title>" . htmlentities($ci->course->displayCourseNo() . " " . $c->name . $ci->displayTerm()) . " Reserve List</title>\n";
    echo "		<link>".htmlentities($g_siteURL)."</link>\n";
    
    foreach($ci->instructorList as $instr)
    	echo "		<managingEditor>" . htmlentities($instr->getEmail() . " (" . $instr->getName()) . ")</managingEditor>\n";
    
    echo "		<webMaster>reservesdesk@listserv.cc.emory.edu (Reserves Desk)</webMaster>\n";
    
    echo "		<description>";
    echo 		"Course Reserves for" . htmlentities($ci->course->displayCourseNo() . " " . $c->name . $ci->displayTerm()) . "<br/>";
    echo 		"taught by: ";
    foreach($ci->instructorList as $instr)
    	echo htmlentities($instr->getEmail() . " (" . $instr->getName()) . ")<br/>";
    
    echo		"Helper Applications required for viewing reserves: <a href=\"http://www.adobe.com/products/acrobat/readstep2.html\">Adobe Acrobat Reader</a>";
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
    		if ($rItem->item->getAuthor() != "") 				echo htmlentities($rItem->item->getAuthor()) . "<br/>";
    		if ($rItem->item->getPerformer() != "") 			echo "preformed by: " . htmlentities($rItem->item->getPerformer()) . "<br/>";
    		
    		if ($rItem->item->getVolumeTitle() != "")			echo htmlentities($rItem->item->getVolumeTitle() . " " . $rItem->item->getVolumeEdition() . " " . $rItem->item->getPagesTimes()) . "<br/>";
    		elseif ($rItem->item->getVolumeEdition() != "")		echo htmlentities($rItem->item->getVolumeEdition() . " " . $rItem->item->getPagesTimes()) . "<br/>";
    		elseif ($rItem->item->getPagesTimes() != "")		echo htmlentities($rItem->item->getPagesTimes()) . "<br/>";
    		
    		if ($rItem->item->getSource() != "") 				echo htmlentities($rItem->item->getSource()) . "<br/>";
    	
    		foreach ($rItem->item->notes as $n)
    		{
    			if ($n->getType() == 'Instructor') echo htmlentities($n->getText()) . "<br/>";
    			elseif ($n->getType() == 'Content') echo htmlentities($n->getText()) . "<br/>";
    			
    		}
    	echo 			htmlentities("<hr noshade/>");	
    	echo "			</description>\n";    				
    	echo "		</item>\n";
    }
    
    
	echo "</channel>";
	echo "</rss>\n";
?>