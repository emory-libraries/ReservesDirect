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

require_once("secure/classes/calendar.class.php");
require_once("secure/classes/users.class.php");
require_once("secure/classes/skins.class.php");
require_once("secure/classes/news.class.php");

require_once("secure/interface/student.class.php");
require_once("secure/interface/custodian.class.php");
require_once("secure/interface/proxy.class.php");
require_once("secure/interface/instructor.class.php");
require_once("secure/interface/staff.class.php");
require_once("secure/interface/admin.class.php");


//set up error-handling/debugging, skins, etc.
require_once("secure/session.inc.php");


//pull Book info from ILS
require_once("lib/RD/Ils.php");

$barcode = $_REQUEST['itemID'];
$u_key   = $_REQUEST['u_key'];

$tmpUsr = new user();
$tmpUsr->getUserByExternalUserKey($u_key);

//Users must be proxy or greater otherwise show access denied
//TEST how do not-trained users evaluate?
switch ($tmpUsr->getUserClass())
{
	case '0':
	case 'student':
	case '1':
	case 'custodian':
		include "ils_request_access_denied.html";
		exit;
}

$termObject = new terms();
$terms = $termObject->getTerms(false); //get current + next 3 terms
$current_term = $termObject->getCurrentTerm();

$usersObject = new users();
$u = $usersObject->initUser($tmpUsr->getUserClass(), $tmpUsr->getUsername());

#Limit to current and upcoming courses
$editableCI = $u->getCourseInstancesToEdit();

//organize by term for tabbed display
$courseList = array();
foreach ($editableCI as $ci) {
	$courseList[$ci->getTerm().$ci->getYear()][] = $ci; 
}

$ils = RD_Ils::initILS();
$item_data = $ils->search('barcode', $barcode)->to_a();

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
div#term_selector { font-size: small; padding-bottom: 20px; }
td.course_number, td.course_title { font-size: small; }
td.course_instructors { font-size: x-small; }

</style>

<script language="JavaScript">
	function toggle_courses_for(termID)
	{
		<? foreach ($terms as $t) { ?>
			document.getElementById('courses_for_<?= $t->getTermID() ?>').style.display = 'none';
		<? } ?>
		document.getElementById('courses_for_'+termID).style.display = '';
	}
</script>

</head>

<body bgcolor="#FFFFCC" text="#000000" link="#000080" vlink="#800080" alink="#FF0000" onload="document.forms[0].NHYOURNAME.focus();">

<h1>Reserve Request for Woodruff Library</h1>
<p>This form is for instructors and their proxies only.</p>

<ul>
<li>Reserve requests are processed in the order received.</li>

<li>Reserve requests are normally fulfilled within <strong>7 days</strong> during the semester or <strong>14 days</strong> near the start of the semester.</li> 
<li>Reserve requests can take significantly longer to fulfill when items are checked out or missing.</li>
</ul>
 
<form action="/uhtbin/woodruff_rsrv_both_request" method="post" name="RESERVEREQUEST">

<input type="hidden" name="state" value="1" />
<input type="hidden" name="itemID" value="010000529626" />
<input type="hidden" name="u_key" value="47654" />


<h2>Title: <?= $item_data['title'] ?></h2>

<h2>Call number: <?= $item_data['holdings'][0]['callNum'] ?></h2>

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
				<? } else { ?>
					<h2>
						There are no active courses for this term.  Please contact your Reserves Desk for assistance. <a href="http://ereserves.library.emory.edu/emailReservesDesk.php" target="_blank">Email the Reserves Desk</a>
					</h2>
				<? } ?>		
			</div>
		<? } //end terms loop ?> 
	</div>
	<!-- end Courses Block -->
</div>
</fieldset>


<p>
<fieldset>
<legend>ReservesDirect Information</legend>

<p><strong>Reserve the physical item at the Reserve Desk: </strong>
	<input type="radio" name="NHATDESK" value="yes" onClick="document.getElementById('physical_request_detail').style.display='';"/>Yes 
	<input type="radio" name="NHATDESK" value="no" checked="checked" onClick="document.getElementById('physical_request_detail').style.display='none';"/>No</p>

<div id="physical_request_detail" style="display: none; margin-left: 10px; border: 1px solid black;">
	<p>If item is unavailable, reserve any available edition: <input type="radio" name="NHANYEDN" value="yes" checked="checked" />Yes <input type="radio" name="NHANYEDN" value="no" />No</p>

	<p>Number of copies to reserve: <input type="radio" name="NH#_COPIES" value="one" checked="checked" />One <input type="radio" name="NH#_COPIES" value="all" />All available</p>

	<p>Checkout period:  <input type="radio" name="NHLOANPERIOD" value="twohours" checked="checked" />2 hours <input type="radio" name="NHLOANPERIOD" value="oneday" />1 day <input type="radio" name="NHLOANPERIOD" value="threedays" />3 days</p>
</div>
	
<p><strong>Reserve part or all of the item online? </strong>
<input type="radio" name="NHONLINEINFO" value="yes" onClick="document.getElementById('scan_request_detail').style.display='';"/>Yes
<input type="radio" name="NHONLINEINFO" value="no" checked="checked" onClick="document.getElementById('scan_request_detail').style.display='none';"/>No</p>

<div id="scan_request_detail" style="display: none; margin-left: 10px; border: 1px solid black;">
	<p>If Emory Libraries determines that this reserve is for material not in the public domain and in excess of fair use, I would like the library staff to request and pay for permission to place the material on e-reserve: <input type="radio" name="NHPAYPRMSSN" value="yes" checked="checked" />Yes <input type="radio" name="NHPAYPRMSSN" value="no" />No</p>
	
	<p><label for="input10"><strong>Part(s) of the work to put online</strong></label><br />

	<div style="margin-left: 50px;">
	<table cellspacing="5px">
		<tr><td class="course_number">Chapter / Article Title</td><td class="course_number">First Page</td><td class="course_number">Last Page</td></tr>
		<tr>
			<td class="course_number"><input type="text" size="50"></td>
			<td class="course_number"><input type="text" size="4"></td>
			<td class="course_number"><input type="text" size="4"></td>
		</tr>
		<tr><td colspan="3"  class="course_number"><a href="#" onClick="alert('not implemented')">Add More Sections</a></td></tr>
	</table>
	</div>
	
</div>
</p>
</fieldset>
<p><strong><label for="input11">Notes or Special Instructions</label></strong><br />
<textarea name="NHNOTE" cols="80" rows="5" id="input11"></textarea>
</p>
<p>To see and manage your reserved materials, please go to ReservesDirect at <a href="https://ereserves.library.emory.edu/">https://ereserves.library.emory.edu/</a> and log in with your NetID. Students also use ReservesDirect to access online materials.</p>

<p align="center"><input type="submit" value="Submit Reserve Request" /></p>
</form>

<hr />

<p><small>Created on ... January 09, 2008</small></p>
</body>
</html>