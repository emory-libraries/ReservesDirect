<?php	
/*******************************************************************************
AJAX_functions.php
returns data for ajax data fields

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

	// workaround for workaround for ie's idiotic caching policy handling
	header("Cache-Control: no-cache");
	header("Pragma: no-cache");

	require_once("secure/config.inc.php");
	require_once("secure/classes/department.class.php");
	require_once("secure/classes/users.class.php");
	require_once("secure/classes/terms.class.php");
	require_once("secure/classes/json_wrapper.class.php");	
	require_once("secure/managers/noteManager.class.php");
	require_once("secure/managers/helpManager.class.php");
	require_once("secure/managers/requestManager.class.php");
	require_once("secure/displayers/noteDisplayer.class.php");
	require_once("secure/common.inc.php");
	
	//set up error-handling/debugging, skins, etc.
	//require_once("secure/session.inc.php");
	
	//authenticate user
	//if user is valid, then initializes global user object as $u
	//else shows login page
	require_once('secure/auth.inc.php');
	
	//process passed arguments
	$f = $_REQUEST['f'];
	$qry = (isset($_REQUEST['qu'])) ? base64_decode($_REQUEST['qu']) : null;
	$rf  = (isset($_REQUEST['rf'])) ? base64_decode($_REQUEST['rf']) : null;
	
/*	
	echo "<pre>";
	print_r($_REQUEST);
	echo "</pre>";
	echo "<br>f=$f";
	echo "<br>qry=$qry";
*/
	$json = new JSON_Wrapper();
	
	
	switch ($f)
	{
		case 'deptList':			
			$dept = new department();
			$depts = $dept->findByPartialName($qry);
			
			$returnValue = xmlHead();
			
			if (count($depts) > 0)					
				foreach($depts as $d)
					$returnValue .=	wrapResults($json->encode($d), $d['abbreviation'] . ' - ' . $d['name']);
			
			$returnValue .= xmlFoot();		
		break;
		
		case 'libList':
			$library = new library($qry);
			
			$data = array (
				'id'   => $library->getLibraryID(),
				'name' => $library->getLibrary(),
				'nickname' => $library->getLibraryNickname(),
				'ils_prefix' => $library->getILS_prefix(),
				'desk' => $library->getReserveDesk(),
				'url' => $library->getLibraryURL(),
				'email' => $library->getContactEmail(),
				'monograph_library_id' => $library->getMonograph_library_id(),
				'multimedia_library_id' => $library->getMultimedia_library_id()
			);		
			
			$returnValue = base64_encode($json->encode($data));
			//$returnValue .= wrapResults($json->encode($data), $data['name']);		
		break;
		
		case 'userList':
			//get the role - all by default
			$min_role = is_int($_REQUEST['role']) ? $_REQUEST['role'] : 0;
			
			$usersObj = new users();
			$usersObj->search(null, $qry, $min_role);
			
			$returnValue = xmlHead();
			
			if (count($usersObj->userList) > 0)				
				foreach($usersObj->userList as $usr)
					$returnValue .=	wrapResults($json->encode($usr), $usr->getName() . ' -- ' . $usr->getUsername());			
			
			$returnValue .= xmlFoot();
		break;
		
		case 'courseList':	
			$usersObj = new users();
			$courses = $usersObj->searchForCourses($qry);
						
			$returnValue = xmlHead();
			
			foreach($courses as $info) {
				//show num and name or just name?
				$label = !empty($info['num']) ? $info['num'].' - '.$info['name'] : $info['name'];
				
				$returnValue .= wrapResults($json->encode($info), $label);
			}
	
			$returnValue .= xmlFoot();		
		break;			

		case 'classList':
			/*
				Expects $_REQUEST['qry'] to be base64 encode '::' delimited string
				instructor_id :: department_id :: course_num :: course_name :: term_id :: ci_variable
				ANY values can be empty			
			*/
		
			list($user_id, $dept_id, $course_num, $course_name, $term_id, $ci_variable) = split("::", $qry);			
			
			$userObj = new users();
			$ci_list = $userObj->searchForCI($user_id, $dept_id, $course_num, $course_name, $term_id);
			
			if(sizeof($ci_list) > 0) {
				//display table header
				$returnValue .= "<div align=\"left\" class=\"headingCell1\">\n";
				$returnValue .= "	<div align=\"left\" style=\"width:60px; float:left;\">&nbsp;</div>\n";
				$returnValue .= "	<div align=\"left\" style=\"width:15%; float:left;\">Course Number</div>\n";
				$returnValue .= "	<div align=\"left\" style=\"width:30%; float:left;\">Course Name</div>\n";
				$returnValue .= "	<div align=\"left\" style=\"width:25%; float:left;\">Instructor</div>\n";
				$returnValue .= "	<div align=\"left\" style=\"width:14%; float:left;\">Last Active</div>\n";
				$returnValue .= "	<div align=\"left\" style=\"width:55px; float:right; padding-right:5px;\">Preview</div>\n";
				$returnValue .= "	<div style=\"clear:both;\" class=\"headingCell1\"></div>\n";
				$returnValue .= "</div>\n";		
				
				foreach($ci_list as $ci) {
					//show status icon
					switch($ci->getStatus()) {
						case 'AUTOFEED':
							$edit_icon = '<img src="images/activate.gif" width="24" height="20" />';	//show the 'activate-me' icon
						break;
						case 'CANCELED':
							$edit_icon = '<img src="images/cancel.gif" alt="edit" width="24" height="20">';	//show the 'activate-me' icon
						break;
						default:
							$edit_icon = '<img src="images/pencil.gif" alt="edit" width="24" height="20">';	//show the edit icon
						break;						
					}
									
					//get crosslistings
					$crosslistings = $ci->getCrossListings();
					$crosslistings_string = '';
					foreach($crosslistings as $crosslisting) {
						$crosslistings_string .= ', '.$crosslisting->displayCourseNo().' '.$crosslisting->getName();
					}
					$crosslistings_string = ltrim($crosslistings_string, ', ');	//trim off the first comma
					
					//being "output"					
					$rowStyle = (empty($rowStyle) || ($rowStyle=='evenRow')) ? 'oddRow' : 'evenRow';	//set the style
									
					$returnValue .= "<div align=\"left\" class=\"$rowStyle\" style=\"padding:5px;\">\n";					
					$returnValue .= "	<div align=\"left\" style=\"width: 30px; float:left; text-align:left;\"><input name=\"".$ci_variable."\" type=\"radio\" value=\"".$ci->getCourseInstanceID()."\" onClick=\"document.getElementById('editButton').disabled=false\"></div>\n";
					$returnValue .= '	<div style="width: 30px; float:left; text-align:left">'.$edit_icon.'</div>';
					$returnValue .= "	<div align=\"left\" style=\"width:15%; float:left;\">".$ci->course->displayCourseNo()."&nbsp;</div>\n";
					$returnValue .= "	<div align=\"left\" style=\"width:30%; float:left;\">".$ci->course->getName()."&nbsp;</div>\n";
					$returnValue .= "	<div align=\"left\" style=\"width:25%; float:left;\">".$ci->displayInstructors()."&nbsp;</div>\n";
					$returnValue .= "	<div align=\"left\" style=\"width:14%; float:left;\">".$ci->displayTerm()."&nbsp;</div>\n";
					$returnValue .= "	<div align=\"left\" style=\"width:55px; float:right;\"><a href=\"javascript:openWindow('no_control=1&cmd=previewReservesList&ci=".$ci->getCourseInstanceID(). "','width=800,height=600');\">preview</a></div>\n";
					$returnValue .= "	<div style=\"clear:both;\">";
					
					if(!empty($crosslistings_string)) {
						$returnValue .= "<div style=\" margin-left:30px; padding-top:5px;\"><em>Crosslisted As:</em> <small>$crosslistings_string</small></div>";
					}
					
					$returnValue .= "	</div>\n";
					$returnValue .= "</div>\n";
				}
			}
			else
				$returnValue .= "<div align=\"center\" class=\"failedText\">No Matches Found.</div>\n";
		break;
		
		case 'termsList':
			$t = new terms();
			$returnValue = $json->encode($t->getTerms(true));
		break;
		

		case 'fetchNotes':
			//parse the request
			parse_str(base64_decode($_REQUEST['query']), $request);
			
			//fetch notes
			$notes = noteManager::fetchNotesForObj($request['obj_type'], $request['id'], true);
			
			//start output buffering
			ob_start();
			//output edit-note blocks (table rows)
			noteDisplayer::displayNotesContentAJAX($notes, $request['obj_type'], $request['id']);
			//grab the content for return
			$returnValue = ob_get_contents();
			//end buffering
			ob_end_clean();		
		break;

		
		case 'saveNote':	
			//parse the request
			parse_str(base64_decode($_REQUEST['query']), $request);	
			//save note
			noteManager::saveNote($request['obj_type'], $request['id'], $request['note_text'], $request['note_type'], $request['note_id']);	
		break;
		
		case 'deleteNote':
			//parse the request
			parse_str(base64_decode($_REQUEST['query']), $request);
			//delete note
			noteManager::deleteNote($request['id'], $request['obj_type'], $request['obj_id']);
		break;
		
		case 'fetchHelpTags':
			//parse the request
			parse_str(base64_decode($_REQUEST['query']), $request);
			
			$returnValue = helpManager::getTags($request['article_id']);
		break;
		
		case 'saveHelpTags';
			//parse the request
			parse_str(base64_decode($_REQUEST['query']), $request);
			
			helpManager::setTags($request['article_id'], $request['tags_string']);
		break;
		
		case 'storeRequest':
			//parse the request
			parse_str(base64_decode($_REQUEST['query']), $request);
			
			//actually need all the data in $_REQUEST for storeReserve() to work, so we'll replace it
			$_REQUEST = $request;
			
			//create the reserve
			if(($data = requestManager::storeReserve()) !== false) {
				$reserve = new reserve($data['reserve_id']);
				$reserve->getItem();
				
				//duplicate links for digital items
				$duplicate = !$reserve->item->isPhysicalItem() ? $duplicate = true : false;
				
				//build return message
				$returnValue = '<div class="borders" style="margin:10px; padding:10px; background:lightgreen; text-align:center"><strong>Reserve created successfully</strong>';
				
				//show "duplicate" links for non-physical items
				if(!$reserve->item->isPhysicalItem()) {
					$returnValue .= '<p />You may <a href="index.php?cmd=duplicateReserve&amp;reserveID='.$reserve->getReserveID().'">duplicate this item and add copy to the same class</a><br /><small>Note: clicking this link will take you away from this screen</small>';
				}
				
				$returnValue .= '<p /><a href="index.php?cmd=editClass&ci='.$reserve->getCourseInstanceID().'">Return to Edit Class.</a><br />';
				
				$returnValue .= '</div>';
			}
			else {
				$returnValue = '<div class="borders" style="margin:10px; padding:10px; background:#FF9900; text-align:center"><strong>Problem creating reserve.</strong>';
			}
		break;
		
		case 'updateRequestStatus':
			parse_str($rf, $args);		
			$r = new request($args['request_id']);
			$r->setStatus($args['status']);
			$returnValue = "<img src='images/check.png' />";
		break;
		
		default:
			return null;
	}
	

	print($returnValue);

function xmlHead(){	return "<?xml version='1.0' encoding='utf-8'  ?><ul class=\"LSRes\">";	}
function xmlFoot(){ return "</ul>"; }

function wrapResults($value, $option)
{
	return "<li class=\"LSRow\" onmouseover='liveSearchHover(this)' onclick='liveSearchClicked(this, \"". base64_encode($value)."\")'>$option</li>";
}

?>
