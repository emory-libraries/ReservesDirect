<?
/*******************************************************************************
exportDisplayer.class.php


Created by Kathy Washington (kawashi@emory.edu)

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
require_once("secure/common.inc.php");

class exportDisplayer extends baseDisplayer {
	
	function getRSS_URL($file)
	{
		return "http://".$_SERVER['SERVER_NAME'] . ereg_replace('index.php', $file, $_SERVER['PHP_SELF']);
	}
	
	function displaySelectExportOption($ci) {
		$ci->getCourseForUser();
?>
		<div style="text-align:right; font-weight:bold;"><a href="index.php?cmd=editClass&amp;ci=<?=$ci->getCourseInstanceID()?>">Return to Class</a></div>
		
		<form method="post" action="index.php">
			<input type="hidden" name="cmd" value="exportClass" />
			<input type="hidden" name="ci" value="<?=$ci->getCourseInstanceID()?>" />
			
			<div style="width:500px; margin:auto;">
				<div class="headingCell1">Choose a Courseware Package</div>
				<div class="borders" style="padding:10px;">
					<strong>Class:</strong>
					<?=$ci->course->displayCourseNo()." -- ".$ci->course->getName()?>
					<p />
					<strong>Export To:</strong>
					<br />
					<label><input type="radio" name="course_ware" value="blackboard" checked value=\"radio\">Blackboard</label><br>
					<label><input type="radio" name="course_ware" value="learnlink">Learnlink</label><br>
					<label><input type="radio" name="course_ware" value="website">Personal Web Page</label>
					<p />
					<input type="submit" name="Submit" value="Get Instructions on How to Export Class">
				</div>
			</div>
		</form>
<?php
	}


	function displayExportInstructions_blackboard($ci)
	{
		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\" align=\"center\">\n";
		echo "	<tr><td width=\"140\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";
		echo "	<tr>\n";
		echo "		<td class=\"headingCell2\">Export Reserve List for ". $ci->course->displayCourseNo() . " -- " . $ci->course->getName() . " to Blackboard</td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td align=\"left\" valign=\"top\">\n";
		echo "			<p><strong>Instructions:</strong></p>\n";
		echo "			<p><strong>Please Note:</strong> These instructions will not work if you are using Internet Explorer 6. There is currently no way to export a class if you are logged into Blackboard using IE6. <strong>You must use a broswer other than IE6 (Netscape and Firefox are recommended) when following these instructions.</strong> Your reserves list will be viewable to students using any browser, but you must set it up using something other than IE6.</p>\n";
		echo "			<p>Create a Folder or Item in one of your Content Areas and call it &quot;Reserves List&quot; or &quot;Course Readings&quot;. </p>\n";
		echo "			<p>In the Text area, cut and paste:</p>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td align=\"left\" valign=\"top\" bgcolor=\"#CCCCCC\" class=\"strong\">\n";
		echo "			&lt;script src=&quot;". exportDisplayer::getRSS_URL('perl/reserves2.cgi') ."?ci=". $ci->getCourseInstanceID() ."&amp;style=reserves&quot;&gt;&lt;/script&gt;\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td align=\"left\" valign=\"top\">\n";
		echo "			<p>Make sure the &quot;Smart Text&quot; radio button is selected.</p>\n";
		echo "			<p>Choose any other options you wish and click on Submit.</p>\n";
		echo "			<p>Your reserve list (both electronic and physical, circulating items) will appear on the page. Physical items will have links to $g_catalogName for their bibliographic and holdings information.</p>\n";
		echo "	        <p align=\"center\"><a href=\"index.php?cmd=exportClass\">Export another class</a><br> <a href=\"index.php\">Return to Home </a> </p>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "</table>\n";

	}

	function displayExportInstructions_learnlink($ci)
	{
		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\" align=\"center\">\n";
		echo "	<tr><td width=\"140\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";
		echo "	<tr>\n";
		echo "		<td class=\"headingCell2\">Export Reserve List for ". $ci->course->displayCourseNo() . " -- " . $ci->course->getName() . " to Learnlink</td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td align=\"left\" valign=\"top\">\n";
		echo "			<p><strong>Instructions:</strong></p>\n";
		echo "			<p>Right-click on the link below (control-click on a Mac) to download the html file needed to export to Learnlink. Save the file to your computer as &quot;reserves.html&quot;. Be sure to remember where you save the file.</p>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td align=\"center\" valign=\"top\" bgcolor=\"#CCCCCC\" class=\"strong\">\n";
		echo "			<a href=\"". exportDisplayer::getRSS_URL('export.php') ."?ci=". $ci->getCourseInstanceID() ."\" target=\"_blank\">Click Here to Download File</a>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td align=\"left\" valign=\"top\">\n";
		echo "			<p>In Learnlink, open your conference and in the &quot;File&quot; menu, select &quot;Upload&quot;. Find the file on your computer and click &quot;Select&quot;. It should appear in your conference. If you open the file, it should open the course listing in the browser window.</p>\n";
		echo "			<p>Your reserve list (both electronic and physical, circulating items) will appear on the page. Physical items will have links to $g_catalogName for theirbib and holdings information.</p>\n";
		echo "	        <p align=\"center\"><a href=\"index.php?cmd=exportClass\">Export another class</a><br> <a href=\"index.php\">Return to Home </a> </p>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "</table>\n";

	}

	function displayExportInstructions_website($ci)
	{
		echo "<table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"3\" align=\"center\">\n";
		echo "	<tr><td width=\"140\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";
		echo "	<tr>\n";
		echo "		<td class=\"headingCell2\">Export Reserve List for ". $ci->course->displayCourseNo() . " -- " . $ci->course->getName() . " to your Personal Web Page</td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td align=\"left\" valign=\"top\">\n";
		echo "			<p><strong>Instructions:</strong></p>\n";
		echo "			<p>Create your page and cut and paste the following in the &lt;head&gt; &lt;/head&gt; area:</p>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td align=\"left\" valign=\"top\" bgcolor=\"#CCCCCC\" class=\"strong\">\n";
		echo "			&lt;script src=&quot;". exportDisplayer::getRSS_URL('rss.php') ."?ci=". $ci->getCourseInstanceID() ."&amp;style=reserves&quot;&gt;&lt;/script&gt;\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td align=\"left\" valign=\"top\">\n";
		echo "			<p>If you wish to use your own stylesheet, remove &quot;style=reserves&quot; from the html.<br> The default stylesheet looks like:</p>\n";
		echo "			<p>\n";
		echo "				.rssTable {color: #000000; background: #FFFFFF; border-color: #000000; border-style: solid; border-width: thin;}<br>\n";
		echo "				.rssLink {background: transparent;}<br>\n";
		echo "				.rssChan {color: transparent; background: #EEEEEE; font-size: large; font-family: sans-serif; font-weight: normal;}<br>\n";
		echo "				.rssItem {color: transparent; background: #FFFFFF; font-size: small; font-family: sans-serif; font-weight: bold;}<br>\n";
		echo "				.rssDesc {color: #000000; background: #FFFFFF; font-size: smaller; font-family: sans-serif; font-weight: normal;} \n";
		echo "			</p>\n";
		echo "	        <p align=\"center\"><a href=\"index.php?cmd=exportClass\">Export another class</a><br> <a href=\"index.php\">Return to Home </a> </p>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "</table>\n";
	}

	function generateRSS_javascript($ci)
	{
	}

}
