<?
/*******************************************************************************
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

Created by Kathy A. Washington (kawashi@emory.edu)

Reserves Direct 2.0 is located at:
http://coursecontrol.sourceforge.net/

*******************************************************************************/
require_once("secure/common.inc.php");

class exportDisplayer 
{
	function getRSS_URL($file)
	{			
		return "http://".$_SERVER['SERVER_NAME'] . ereg_replace('index.php', $file, $_SERVER['PHP_SELF']);
	}
	
	function displayExportSelectClass($classList, $hidden_fields=null)
	{
		global $ci;
		
		echo "<form action=\"index.php\" method=\"POST\">\n";
		
		if (is_array($hidden_fields)){
			$keys = array_keys($hidden_fields);
			foreach($keys as $key){
				if (is_array($hidden_fields[$key])){
					foreach ($hidden_fields[$key] as $field){
						echo "<input type=\"hidden\" name=\"".$key."[]\" value=\"". $field ."\">\n";	
					}
				} else {
					echo "<input type=\"hidden\" name=\"$key\" value=\"". $hidden_fields[$key] ."\">\n";
				}
			}
		}
		
		echo "<table width=\"60%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr><td width=\"140\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>\n";
		echo "	<tr><td class=\"headingCell1\">Choose a Class and Courseware Package</td></tr>\n";
		echo "	<tr>\n";
		echo "		<td align=\"center\" class=\"borders\">\n";
		echo "			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\">\n";
		echo "				<tr align=\"left\" valign=\"middle\">\n";
		echo "					<td align=\"right\" valign=\"middle\" class=\"strong\">Choose Class: </td>\n";
		echo "					<td class=\"strong\">\n";
		
		if (is_array($classList) && !empty($classList) && !($ci instanceof courseInstance))
		{
			//This drop-down should contain all current and future courses taught by the instructor who is logged in.
			echo "						<select name=\"ci\"> \n";
			
			foreach ($classList as $class)
				echo "							<option value=\"". $class->getCourseInstanceID() ."\">". $class->course->displayCourseNo() . " -- " . $class->course->getName() .  "</option>\n";
			
			echo "						</select>\n";
		} else {
			echo "						<input type=\"hidden\" name=\"ci\" value=\"".$ci->getCourseInstanceID()."\">\n";
		}
						
		echo "					</td>\n";
		echo "				</tr>\n";

		
		
			
		echo "				<tr>\n";
		echo "					<td align=\"right\" valign=\"middle\" class=\"strong\">Export To:</td>\n";
		echo "					<td>\n";
		echo "						<label><input type=\"radio\" name=\"course_ware\" value=\"blackboard\" checked value=\"radio\">Blackboard</label><br>\n";
		echo "						<label><input type=\"radio\" name=\"course_ware\" value=\"learnlink\">Learnlink</label><br>\n";
		echo "						<label><input type=\"radio\" name=\"course_ware\" value=\"website\">Personal Web Page</label>\n";
		echo "					</td>\n";
		echo "				</tr>\n";
		echo "				<tr>\n";
		echo "					<td colspan=\"2\" align=\"center\">\n";
		echo "						<input type=\"submit\" name=\"Submit\" value=\"Get Instructions on How to Export Class\">\n";
		echo "					</td>\n";
		echo "				</tr>\n";
		echo "			</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "</table>\n";		
	}
	
	function displayExportInstructions_blackboard($ci)
	{
		echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\" align=\"center\">\n";
		echo "	<tr><td width=\"140\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";
		echo "	<tr>\n";
		echo "		<td class=\"headingCell2\">Export Reserve List for ". $ci->course->displayCourseNo() . " -- " . $ci->course->getName() . " to Blackboard</td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td align=\"left\" valign=\"top\">\n";
		echo "			<p><strong>Instructions:</strong></p>\n";
		echo "			<p>Create a Folder in one of your Content Areas and call it &quot;Reserves&quot; or &quot;Course Readings&quot;. Create an Item in that Folder called &quot;Reserves List&quot;.</p>\n";
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
		echo "			<p>Make sure the &quot;Smart Text&quot; or &quot;HTML&quot; radio button is selected.</p>\n";
		echo "			<p>Choose any other options you wish and click on Submit.</p>\n";
		echo "			<p>Your reserve list (both electronic and physical, circulating items) will appear on the page. Physical items will have links to EUCLID for their bibliographic and holdings information.</p>\n";
		echo "	        <p align=\"center\"><a href=\"index.php?cmd=exportClass\">Export another class</a><br> <a href=\"index.php\">Return to Home </a> </p>\n";		
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "</table>\n";
		
	}			
	
	function displayExportInstructions_learnlink($ci)
	{
		echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\" align=\"center\">\n";
		echo "	<tr><td width=\"140\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";
		echo "	<tr>\n";
		echo "		<td class=\"headingCell2\">Export Reserve List for ". $ci->course->displayCourseNo() . " -- " . $ci->course->getName() . " to Learnlink</td>\n";
		echo "	</tr>\n";		
		echo "	<tr>\n";
		echo "		<td align=\"left\" valign=\"top\">\n";
		echo "			<p><strong>Instructions:</strong></p>\n";
		echo "			<p>Click on the link below to download the html file needed to export to Learnlink. Save the file to your computer as &quot;reserves.html&quot;. Be sure to remember where you save the file.</p>\n";
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
		echo "			<p>Your reserve list (both electronic and physical, circulating items) will appear on the page. Physical items will have links to EUCLID for theirbib and holdings information.</p>\n";
		echo "	        <p align=\"center\"><a href=\"index.php?cmd=exportClass\">Export another class</a><br> <a href=\"index.php\">Return to Home </a> </p>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "</table>\n";

	}
	
	function displayExportInstructions_website($ci)
	{	
		echo "<table width=\"90%\" border=\"1\" cellspacing=\"0\" cellpadding=\"3\" align=\"center\">\n";
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
/*
document.write('\<style\ type\=\"text\/css\"\></style>\
\
')
document.write('\.rssTable\ \{color\:\ \#000000\;\ background\:\ \#FFFFFF\;\ border\-color\:\ \#000000\;\	border\-style\:\ solid\;\ border\-width\:\ thin\;\}\
\
')
document.write('\.rssLink\ \{background\:\ transparent\;\}\
\
')
document.write('\.rssChan\ \{color\:\ transparent\;\	background\:\ \#EEEEEE\;\ font\-size\:\ large\;\ font\-family\:\ sans\-serif\;\ font\-weight\:\ normal\;\}\
\
')
document.write('\.rssItem\ \{color\:\ transparent\;\ background\:\ \#FFFFFF\;\ font\-size\:\ small\;\ font\-family\:\ sans\-serif\;\ font\-weight\:\ bold\;\}\
\
')
document.write('\.rssDesc\ \{color\:\ \#000000\;\ background\:\ \#FFFFFF\;\ font\-size\:\ smaller\;\ font\-family\:\ sans\-serif\;\ font\-weight\:\ normal\;\}\
\
')
document.write('\<\/style\>')
document.write('<table class="rssTable" cellspacing="0" cellpadding="2" width="100%">')
document.write('<tr class="rssChan"><td valign="middle" align="center">')
document.write('<a class="rssLink" href="https://ereserves.library.emory.edu/reserves/">AAS1234-A Jason\'s Test Spring 2005 Reserves List</a>')
document.write('</td></tr>')
document.write('<tr class="rssChanDesc"><td valign="middle" align="center">')
document.write('Course Reserves for AAS1234-A Jason\'s Test Spring 2005 <br />taught by: Jason White<br />Helper Applications required for viewing reserves:<br /><a href="http://www.real.com/">RealPlayer</a>')
document.write('</td></tr>')
document.write('</td></tr></table>')
document.close()
*/		
	}
	
}