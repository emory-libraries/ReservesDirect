<?
/*******************************************************************************
ils_request_form.php
display ils request form

Created by Jason White (jbwhite@emory.edu)

This file is part of ReservesDirect

Copyright (c) 2004-2008 Emory University, Atlanta, Georgia.

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

$load_start_time = time();
require_once("secure/config.inc.php");
require_once("secure/common.inc.php");

require_once("secure/classes/users.class.php");

require_once("secure/interface/student.class.php");
require_once("secure/interface/custodian.class.php");
require_once("secure/interface/proxy.class.php");
require_once("secure/interface/instructor.class.php");
require_once("secure/interface/staff.class.php");
require_once("secure/interface/admin.class.php");

require_once("secure/classes/reserveItem.class.php");
require_once("secure/classes/request.class.php");
require_once("secure/classes/reserves.class.php");
require_once("secure/classes/courseInstance.class.php");

//set up error-handling/debugging, skins, etc.
require_once("secure/session.inc.php");


//pull Book info from ILS
require_once("lib/RD/Ils.php");

//echo "<pre>"; print_r($_REQUEST); echo "</pre>";

$barcode = $_REQUEST['itemID'];
$u_key   = $_REQUEST['u_key'];

$tmpUsr = new user();
if (isset($_REQUEST['u_id']))
{
	$tmpUsr->getUserByID($_REQUEST['u_id']);
} else {	
	$tmpUsr->getUserByExternalUserKey($u_key);
}

//Users must be proxy or greater otherwise show access denied
//TEST how do not-trained users evaluate?
switch ($tmpUsr->getUserClass())
{
	case null:
	case '0':
	case 'student':
	case '1':
	case 'custodian':
		include "ils_request_access_denied.html";
		exit;
}

$usersObject = new users();
$u = $usersObject->initUser($tmpUsr->getUserClass(), $tmpUsr->getUsername());

$ils = RD_Ils::initILS();
$ils_result = $ils->search('barcode', $barcode); 

$item_data  = $ils_result->to_a();

if (empty($item_data['title']))
{
	include "ils_request_item_not_found.html";
	exit;
}

//Determine how to route material.  Currently routing is based on item_group
//This is terrible but its the best we can do for now.  look for changes
$mm_array = array("MEDIA", "MM-DESK", "MM-REF", "MM-RESERVE", "MMNEWBOOKS", "MUSIC-DEPT", "MUSICMEDIA");
$physical_group = (in_array($item_data['holdings'][0]['loc'], $mm_array)) ? 'MULTIMEDIA' : 'MONOGRAPH';

if (isset($_REQUEST['cmd']) && $_REQUEST['cmd'] == 'storeILSRequest')
{
	// Assume we are processing the form
	// Create Reserve, Request and Item records
	// 1 for physical requests 
	// 1 for each scan request section

	$form_data = array();
	$form_data['ci'] 						= $_REQUEST['ci'];
	$form_data['ils_request_loanPeriod']	= $_REQUEST['ils_request_loanPeriod'];
	$form_data['note']   = array();
	$form_data['note'][] = $_REQUEST['note'];
		
	$form_data['maxEnrollment']				= $_REQUEST['maxEnrollment'];
	
	if ($_REQUEST['placeAtDesk'] == 'yes')
	{
		$form_data['note'][] = "PLACE {$_REQUEST['numCopies']} copy(ies) on Reserve";
	
		if ($_REQUEST['reserveAnyAvailable'] == 'yes')
			$form_data['note'][] = 'PLACE ANY AVAILABLE VOLUME ON RESERVE';	
				
		storeData($u, $item_data, $physical_group, $form_data, 'PHYSICAL');
	}
	
	if ($_REQUEST['scanItem'] == 'yes')
	{
		for($j = 0; $j < sizeof($_REQUEST['scan_request']); $j++)
		{		
			$end = "";
			if (isset($_REQUEST['scan_request'][$j]['end']) && !empty($_REQUEST['scan_request'][$j]['end']))
				$end = " - {$_REQUEST['scan_request'][$j]['end']}";
			
			$form_data['pages'] 		= ($physical_group == 'MONOGRAPH') ? "pp. {$_REQUEST['scan_request'][$j]['start']} $end" : $_REQUEST['scan_request'][$j]['start'] . " - " . $_REQUEST['scan_request'][$j]['end'];
			$form_data['chapter_title']	= $_REQUEST['scan_request'][$j]['chapter_title'];
					
			storeData($u, $item_data, 'ELECTRONIC', $form_data, 'SCAN');
		}
	}
}

$termObject = new terms();
$terms = $termObject->getTerms(false); //get current + next 3 terms
$current_term = $termObject->getCurrentTerm();

#Limit to current and upcoming courses
$editableCI = $u->getCourseInstancesToEdit();

//organize by term for tabbed display
$courseList = array();
foreach ($editableCI as $ci) {
	$courseList[$ci->getTerm().$ci->getYear()][] = $ci; 
}

function storeData($u, $item_data, $item_group, $form_data, $request_type)
{
	global $g_dbConn;
	
	//attempt to use transactions
	if($g_dbConn->provides('transactions')) {
		$g_dbConn->autoCommit(false);
	}
	try {	
		$ci = new courseInstance($form_data['ci']);
		
		$item = new reserveItem();
			$item->createNewItem();
			$item->setLocalControlKey($item_data['controlKey']);
			$item->setTitle($item_data['title']);
			$item->setAuthor($item_data['author']);
			$item->setOCLC($item_data['OCLC']);
			$item->setISBN($item_data['ISBN']);
			$item->setISSN($item_data['ISSN']);
			$item->setGroup($item_group); 
			$item->setType('ITEM');
			$item->setPagesTimes($form_data['pages']);
			$item->setVolumeTitle($form_data['chapter_title']);
		
		$reserve = new reserve();
			$reserve->createNewReserve($ci->getCourseInstanceID(), $item->getItemID());
			$reserve->setActivationDate($ci->getActivationDate());
			$reserve->setExpirationDate($ci->getExpirationDate());
			$reserve->setRequestedLoanPeriod($form_data['ils_request_loanPeriod']);
			$reserve->setStatus('IN PROCESS');
		
		$request = new request();
			$request->createNewRequest($ci->getCourseInstanceID(), $item->getItemID());
			$request->setRequestingUser($u->getUserID());
			$request->setReserveID($reserve->getReserveID());
			$request->setMaxEnrollment($form_data['maxEnrollment']);
			$request->setType($request_type);
		
		foreach($form_data['note'] as $note)
		{
			$request->setNote($note, 'Instructor');
		}
	} catch (Exception $e) {
		trigger_error("Error Occurred While processing StoreRequest ".$e->getMessage(), E_USER_ERROR);
		if($g_dbConn->provides('transactions')) { 
			$g_dbConn->rollback();
		}					
	}
	//commit this set
	if($g_dbConn->provides('transactions')) { 
		$g_dbConn->commit();
	}	
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>Reserve Request for Woodruff Library, Emory University</title>
  <link rev="made" href="mailto:reserves@emory.edu" />
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="generator" content="NoteTab Light 5.6" />
  <meta name="author" content="Amanda French" />
  <meta name="description" content="Request form for reserving books in Woodruff Library and for requesting book chapters and other materials for online use in  ReservesDirect." />

  <meta name="keywords" content="reserves, ereserves, electronic reserves, e-reserves, request, scanning, scan, digitization, Emory, Woodruff, library, Woodruff library, Emory University, main library" />

<style type="text/css">
	p, h1, h2, legend, li { font-family: verdana; margin-left: 50px; }
	p, li { font-size: small; }
	
	h1 { font-size: x-large; font-weight: bold; } 
	h2 { font-size: large; font-weight: bold; }

	legend { font-size: medium; font-weight: bold; }
	.example { font-size: x-small; white-space: nowrap; }

	div#course_selection_area {margin-left: 25px;}
	div#course_selection_area p {margin-left: 0px;}
		
	div#maxEnrollment {margin-left: 25px; font-family: verdana; font-size: small;}
	
	div#item_info {margin-left: 50px;}
	div#term_selector { font-size: small; padding-bottom: 20px; }
	td.course_number, td.course_title { font-size: small; }
	td.course_instructors { font-size: x-small; }
		
	span.required {color: red;}
	
	
	div.error  {border: 2px double red; padding-left: 5px;}	
	span.error {color: red;}

</style>

<script language="JavaScript1.2">
	function toggle_courses_for(termID)
	{
		<? foreach ($terms as $t) { ?>
			document.getElementById('courses_for_<?= $t->getTermID() ?>').style.display = 'none';
		<? } ?>
		document.getElementById('courses_for_'+termID).style.display = '';
	}
	
	var rowNdx = 0;
	function addNewScanRequest(tableRef)
	{
		rowNdx++;
		newRow =  		  '<tr>';		
		newRow = newRow + '	<td class="course_number"><input type="text" name="scan_request[' + rowNdx + '][chapter_title]" size="50" /></td>';
		newRow = newRow + '	<td class="course_number"><input type="text" name="scan_request[' + rowNdx + '][start]" size="4" /></td>';
		newRow = newRow + '	<td class="course_number"><input type="text" name="scan_request[' + rowNdx + '][end]" size="4" /></td>';
		newRow = newRow + '</tr>';
		
		
		$(tableRef).insert({bottom: newRow});

	}
	
	function validateForm(frm)
	{	
		var errorCnt = 0;						
		
		//verify that maxEnrollment is int > 0
		if (isNaN(frm.maxEnrollment.value) || frm.maxEnrollment.value < 0)
		{
			$('maxEnrollment').className = "error";
			$('maxEnrollment_error').replace('Maximum Enrollment must be a whole number greater than 0.');
			errorCnt++;
		} else {			
			$('maxEnrollment').className = "";
			$('maxEnrollment_error').replace('');			
		}
		
		//verify that ci has been selected
		var selectedCI;
		for(i=0; i < frm.elements['ci'].length; i++) 
		{
			if (frm.elements['ci'][i].checked)
				selectedCI = frm.elements['ci'][i].value;
		}
		if (selectedCI == undefined)
		{
			$('ci_errors').insert("Please select a course.")
			$('course_selection_area').className = 'error';
			errorCnt++;
		} else {
			$('ci_errors').replace("")
			$('course_selection_area').className = '';
		}
		
		//verify that placeAtDesk and scanItem are not both no 		
		if (frm.placeAtDesk[1].checked && frm.scanItem[1].checked)
		{
			$('rd_info_error').className = "error";
			$('rd_info_error_txt').replace("Please select to place the physical item at the Reserve Desk and/or place part(s) of the item online.");
			errorCnt++;
		} else {
			$('rd_info_error').className = "";
			$('rd_info_error_txt').replace("");			
		}
		
		
		if (frm.scanItem[0].checked)
		{
			//for(i=0; i < frm.elements['ci'].length; i++) 
			alert(frm.scanItem[0].value);
			alert(frm.scan_request);
		}
		
		alert(errorCnt);
		return false;
	}
</script>
<script language="JavaScript1.2" src="secure/javascript/prototype.js"></script>
</head>

<body bgcolor="#FFFFCC" text="#000000" link="#000080" vlink="#800080" alink="#FF0000">
<h1>Reserve Request for Woodruff Library</h1>

<ul>
<li>Reserve requests are processed in the order received.</li>

<li>Reserve requests are normally fulfilled within <strong>7 days</strong> during the semester or <strong>14 days</strong> near the start of the semester.</li> 
<li>Reserve requests can take significantly longer to fulfill when items are checked out or missing.</li>
</ul>
 
<form action="ils_request_form.php" method="post" name="RESERVEREQUEST" > <!-- onSubmit="return validateForm(this)"> -->

<input type="hidden" name="itemID" value="<?= $barcode ?>" />
<input type="hidden" name="u_key"  value="<?= $u_key ?>" />
<input type="hidden" name="cmd" value="storeILSRequest"/>
<input type="hidden" name="u_id" value="<?= $u->getUserID() ?>" />
<input type="hidden" name="physical_group" value="<?= $physical_group ?>" />

<div id="item_info">
	<table cellspacing="5">
		<tr><td><b>Title:</b></td><td><?= $item_data['title'] ?></b></td></tr>
		<tr><td><b>Call number:</b></td><td><?= $item_data['holdings'][0]['callNum'] ?></b></td></tr>
		<tr><td><b>Current Location:</b></td><td><?= $item_data['holdings'][0]['loc'] ?></td></tr>
	</table>
</div>
<p />

<fieldset>
<legend>Class Information</legend>
<div id="course_selection_area">
	<div id="term_selector">
		<? 
		foreach ($terms as $term) { 
			$selected = ($term->getTermID() == $current_term->getTermID()) ? " checked=\"CHECKED\" " : "";
			$onClick = "onClick='toggle_courses_for({$term->getTermID()})';";
			print ("<label for=\"term\"><input type=\"radio\" name=\"term\" value=\"{$term->getTermID()}\" $selected $onClick/>{$term->getTerm()} </label>");
		}
		?> 
	</div>

	<!-- Display Courses in term Blocks treat as single HTML element -->
	<div id="course_instance_list">
		<? foreach($terms as $t) { ?>
			<? $display_style = ($t->getTermID() == $current_term->getTermID()) ? "display: ''" : "display: none;"; ?>
			<div id="courses_for_<?= $t->getTermID() ?>" style="<?= $display_style ?>">
				<? if (!empty($courseList[$t->getTermName().$t->getTermYear()])) { ?>				
					<table cellspacing="5px">
					
					<tr><td colspan="4" class="course_title"><b><?= $t->getTerm() ?></b></td></tr>
					
					<? foreach ($courseList[$t->getTermName().$t->getTermYear()] as $ci) { ?>
						<? $ci->getCourseForUser($u->getUserID()); ?>
						<? $ci->getInstructors(); ?>
						<tr id="ci_<?= $ci->getCourseInstanceID() ?>" class="course_instance">
							<td><input type="radio" name="ci" value="<?= $ci->getCourseInstanceID() ?>"/></td>
							<td class="course_number"><?= $ci->course->displayCourseNo() ?></td>
							<td class="course_title"><?= $ci->course->getName() ?></td>
							<td class="course_instructors"><?= $ci->displayInstructors(false) ?></td>
						</tr>	
					<? } ?>						
					</table>	
					<p>
						Please contact your Reserves Desk if your desired course is not displayed. <br/>
						<a "href="http://ereserves.library.emory.edu/emailReservesDesk.php" target="_blank">Email the Reserves Desk</a>
					</p>							
				<? } else { ?>
					<h2>
						There are no active courses for this term.  Please contact your Reserves Desk for assistance.<br/>
						<a href="http://ereserves.library.emory.edu/emailReservesDesk.php" target="_blank">Email the Reserves Desk</a>
					</h2>
				<? } ?>		
			</div>
		<? } //end terms loop ?> 				
		<span id="ci_errors" class="error"></span>
	</div>	
	<!-- end Courses Block -->
</div>
<br/>
<div id="maxEnrollment">
	<b>Maximum Enrollment for this course:</b> <span class="required">*</span>
	<input type="text" name="maxEnrollment" size="3" maxlength="4"/>
	<span id="maxEnrollment_error" class="error"></span>
</div>
</fieldset>


<p>
<fieldset>
<legend>ReservesDirect Information</legend>

<div id="rd_info_error"><span id="rd_info_error_txt" class="error"></span></div>

<p>
	<strong>Reserve the physical item at the Reserve Desk: </strong>
	<input type="radio" name="placeAtDesk" value="yes" onClick="document.getElementById('physical_request_detail').style.display='';"/>Yes 
	<input type="radio" name="placeAtDesk" value="no" checked="checked" onClick="document.getElementById('physical_request_detail').style.display='none';"/>No
</p>

<div id="physical_request_detail" style="display: none; margin-left: 10px; border: 1px solid black;">
	<p>If item is unavailable, reserve any available edition: 
		<input type="radio" name="reserveAnyAvailable" value="yes" checked="checked" />Yes 
		<input type="radio" name="reserveAnyAvailable" value="no" />No
	</p>
	<p>Number of copies to reserve: 
		<input type="radio" name="numCopies" value="one" checked="checked" />One 
		<input type="radio" name="numCopies" value="all available" />All available
	</p>

	<p>
		Checkout period:  
		<label for="ils_request_loanPeriod"><input type="radio" name="loanPeriod" value="2 Hours" checked="checked" />2 hours</label>
		<label for="ils_request_loanPeriod"><input type="radio" name="loanPeriod" value="1 Day" />1 day</label>
		<label for="ils_request_loanPeriod"><input type="radio" name="loanPeriod" value="3 Days" />3 days</label>
	</p>
</div>
	
<p><strong>Reserve part or all of the item online? </strong>
<input type="radio" name="scanItem" value="yes" onClick="document.getElementById('scan_request_detail').style.display='';"/>Yes
<input type="radio" name="scanItem" value="no" checked="checked" onClick="document.getElementById('scan_request_detail').style.display='none';"/>No</p>

<div id="scan_request_detail" style="display: none; margin-left: 10px; border: 1px solid black;">
	<p>
		If Emory Libraries determines that this reserve is for material not in the public domain and in excess of fair use,
		I would like the library staff to request and pay for permission to place the material on e-reserve: 
		<label for="ils_request_payPermission"><input type="radio" name="payPermission" value="yes" checked="checked" />Yes</label>
		<label for="ils_request_payPermission"><input type="radio" name="payPermission" value="no" />No</label>
	</p>
	
	<p><b>Part(s) of the work to put online</b><br />

	<div style="margin-left: 50px;">
		<table cellspacing="5px" id="scan_request_table">
			<tbody>
				<tr>
				<? if ($physical_group == 'MONOGRAPH') {?>
					<td class="course_number">Chapter / Article Title</td><td class="course_number">First Page</td><td class="course_number">Last Page</td>
				<? } else { ?>
					<td class="course_number">Track / Song Title</td><td class="course_number">Start Time</td><td class="course_number">End Time</td>
				<? } ?>
				</tr>
				<tr>
					<td class="course_number"><input type="text" name="scan_request[0][chapter_title]" size="50" /></td>
					<td class="course_number"><input type="text" name="scan_request[0][start]" size="4" /></td>
					<td class="course_number"><input type="text" name="scan_request[0][end]" size="4" /></td>
				</tr>		
			</tbody>
		</table>
		<p style="margin-left: 0px;"><a href="#" onClick="addNewScanRequest('scan_request_table'); return false;">Add More Sections</a></p>
	</div>
	
</div>
</p>
</fieldset>
<p><strong><label for="input11">Notes or Special Instructions</label></strong><br />
<textarea name="note" cols="80" rows="5" id="input11"></textarea>
</p>
<p>To see and manage your reserved materials, please go to ReservesDirect at <a href="https://ereserves.library.emory.edu/">https://ereserves.library.emory.edu/</a> and log in with your NetID. Students also use ReservesDirect to access online materials.</p>

<p align="center"><input type="submit" value="Submit Reserve Request" /></p>
</form>

<hr />

<p><small>Created on ... January 09, 2008</small></p>
</body>
</html>