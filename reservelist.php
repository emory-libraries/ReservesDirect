<?
/*******************************************************************************
reservelist.php
This page generates javascript code to display a plain HTML reserves list

Created by Chris Roddy (croddy@emory.edu)

This file is part of ReservesDirect.

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
error_reporting(0);
require_once("secure/config.inc.php");
require_once("secure/common.inc.php");

require_once("secure/classes/reserves.class.php");
require_once("secure/classes/course.class.php");
require_once("secure/classes/courseInstance.class.php");
require_once("secure/classes/reserveItem.class.php");
require_once('secure/classes/tree.class.php');


//$i = new instructor();
$ci = new courseInstance($_REQUEST['ci']);
$ci->getCrossListings();
$ci->getInstructors();
$ci->getPrimaryCourse();
//get reserves as a tree + recursive iterator
$walker = $ci->getReservesAsTreeWalker('getActiveReserves');

$htmloutput = "<h3><a href=\"{$g_siteURL}/index.php?cmd=viewReservesList&amp;ci={$_REQUEST['ci']}\">";
$htmloutput .= htmlentities(stripslashes($ci->course->displayCourseNo() . " " . $ci->course->name . " " . $ci->displayTerm())) . " - Reserve List</a></h3>\n";

foreach($ci->instructorList as $instr)
	$htmloutput .= "<h4>Taught by: <a href=\"mailto:{$instr->getEmail()}\">{$instr->getName()}</a></h4>\n" ;

$htmloutput .= "<ul>";

foreach($walker as $leaf) {
	$rItem = new reserve($leaf->getID());
	$rItem->getItem();
	$itemNotes = $rItem->item->getNotes();
	$resNotes = $rItem->getNotes();

        
	//do not show link for headings
	if(!$rItem->item->isHeading()) {
		if ($rItem->item->isPhysicalItem()) {
			$itemURL = htmlentities($g_reservesViewer . $rItem->item->getLocalControlKey());
		} else {
			$itemURL = htmlentities($g_siteURL."/reservesViewer.php?reserve=". $rItem->getReserveID());
		}
	}
    	
	$htmloutput .= "\t<li><a href=\"$itemURL\">" . htmlentities($rItem->item->getTitle()) . "</a><br/>\n";


	//ouput what we have as the description
		if ($rItem->item->getAuthor() != "")
			$htmloutput .= trim($rItem->item->getAuthor()) . ". ";
                    
		if ($rItem->item->getPerformer() != "")
			$htmloutput .= "performed by: " . trim($rItem->item->getPerformer()) . ". ";

		if ($rItem->item->getVolumeTitle() != "")
			$htmloutput .= trim($rItem->item->getVolumeTitle() . " " . $rItem->item->getVolumeEdition() . " " . $rItem->item->getPagesTimes()) . ". ";
                    
		elseif ($rItem->item->getVolumeEdition() != "")
			$htmloutput .= trim($rItem->item->getVolumeEdition() . " " . $rItem->item->getPagesTimes()) . ". ";
                    
		elseif ($rItem->item->getPagesTimes() != "")
			$htmloutput .= trim($rItem->item->getPagesTimes()) . ". ";

		if ($rItem->item->getSource() != "")
			$htmloutput .= trim($rItem->item->getSource()) . ". ";

		foreach($itemNotes as $note) {
				if($note->getType() == 'Content') {
					$htmloutput .= trim(str_replace("\r", "", str_replace("\n", " ", $note->getText().'. ')));
				}
			}
		foreach($resNotes as $note) {
			$htmloutput .= $note->getText().'. ';
		}

	$htmloutput .= "</li>\n";
}
$htmloutput .= "</ul>";

$htmloutput .= "<a href=\"mailto:$g_reservesEmail\">Reserves desk: $g_reservesEmail</a>\n";

header("Content-Type: application/x-javascript");

foreach(split("\n", $htmloutput) as $line) 
	echo "document.write('" . addslashes($line) . "');\n";

?>
