<?
/*******************************************************************************
Reserves Direct 2.0

Copyright (c) 2004 Emory University General Libraries

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
\"Software\"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be included
in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED \"AS IS\", WITHOUT WARRANTY OF ANY KIND,
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

class itemDisplayer 
{
	/**
	* @return void
	* @param user $user
	* @param courseInstance $ci
	* @desc display edit course form
	*/
	function displayEditItemScreen($reserve,$user)
	{
		
		global $g_permission;
		
		if (!is_a($reserve->item, "reserveItem")) $reserve->getItem();
		
		echo "<form name=\"reservesMgr\" action=\"index.php?cmd=editItem\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"ci\" value=\"".$reserve->getCourseInstanceID()."\">\n";
		echo "<input type=\"hidden\" name=\"rID\" value=\"".$reserve->getReserveID()."\">\n";

				
		$activationDate = $reserve->getActivationDate();
		list($year, $month, $day) = split("-", $activationDate);
		
		$status = $reserve->getStatus();
		$todaysDate = date ("Y-m-d");

		$statusColor = common_getStatusDisplayColor($status);
		
		$title = $reserve->item->getTitle();
		$author = $reserve->item->getAuthor();
		$url = $reserve->item->getURL();
		$performer = $reserve->item->getPerformer();
		$volTitle = $reserve->item->getVolumeTitle();
		$volEdition = $reserve->item->getVolumeEdition();
		$pagesTimes = $reserve->item->getPagesTimes();
		$source = $reserve->item->getSource();
		$contentNotes = $reserve->item->getContentNotes();
		$itemNotes = $reserve->item->getNotes(); //Valid note types, associated with an item, are content, copyright, and staff
		$instructorNotes = $reserve->getNotes();
		
		echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr>\n";
		echo "    	<td width=\"140%\"><img src=\images/spacer.gif\" width=\"1\" height=\"5\"></td>\n";
		echo "	</tr>\n";
		echo "    <tr>\n";
		echo "    	<td>\n";
		echo "    	<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		echo "        	<tr align=\"left\" valign=\"top\">\n";
		echo "            	<td class=\"headingCell1\"><div align=\"center\">ITEM DETAILS</div></td>\n";
		echo "            ";
		echo "			<!--The \"Show All Editable Item\" Links appears by default when this";
		echo "			page is loaded if some of the metadata fields for the document are blank.";
		echo "			Blank fields will be hidden upon page load. -->  ";
		echo "			  ";
		echo "				<td width=\"75%\"><!-- <div align=\"right\">[ <a href=\"link\" class=\"editlinks\">show all editable fields</a><a href=\"link\" class=\"editlinks\"></a> ]</div>--></td>\n";
		echo "			</tr>\n";
		echo "		</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "    <tr>\n";
		echo "    	<td align=\"left\" valign=\"top\" class=\"borders\">\n";
		echo "    	<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\">\n";
		echo "        	<tr valign=\"middle\">\n";
		echo "            	<td colspan=\"2\" align=\"right\" bgcolor=\"#CCCCCC\" class=\"borders\">\n";
		echo "            	<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		echo "                	<tr>\n";
		echo "                  		<td width=\"35%\" height=\"14\"><p><span class=\"strong\">Current Status: </span><strong><font color=\"".$statusColor."\">".$status."</font></strong>\n";
		
		if ($status == "ACTIVE") {
			if ($activationDate > $todaysDate) {
				echo "<span class=\"small\"> (hidden until ".$month."/".$day."/".$year.")</span>\n";
			}
			echo " | <input type=\"checkbox\" name=\"deactivateReserve\" value=\"".$reserve->getReserveID()."\"> Deactivate?";
			echo "						<td width=\"100%\"><span class=\"strong\">Activation Date:</span><strong></strong>&nbsp;<input name=\"month\" type=\"text\" size=\"2\" maxlength=\"2\" value=\"".$month."\"> / <input name=\"day\" type=\"text\" size=\"2\" maxlength=\"2\" value=\"".$day."\"> / <input name=\"year\" type=\"text\" size=\"4\" maxlength=\"4\" value=\"".$year."\"></td>\n";
		} elseif ($status == "INACTIVE") {
			echo " | <input type=\"checkbox\" name=\"activateReserve\" value=\"".$reserve->getReserveID()."\"> Activate?";
		} elseif (($status == "IN PROCESS") && ($user->dfltRole >= $g_permission['staff'])) { //only staff can change an in-process status
			echo " | <input type=\"checkbox\" name=\"activateReserve\" value=\"".$reserve->getReserveID()."\"> Activate?";
		}
		
		echo "					</tr>\n";
		echo "              	</table>\n";
		echo "              	</td>\n";
		echo "			</tr>\n";
		echo "            <tr valign=\"middle\">\n";
		echo "            	<td width=\"25%\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\" class=\"strong\"><font color=\"#FF0000\"><strong>*</strong></font>Document Title:</div></td>\n";
		echo "				<td width=\"100%\" align=\"left\"><input name=\"title\" type=\"text\" id=\"title\" size=\"50\" value=\"$title\"></td>\n";
		echo "			</tr>\n";
		echo "            <tr valign=\"middle\">\n";
		echo "            	<td width=\"25%\" height=\"31\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\" class=\"strong\"><font color=\"#FF0000\"><strong>*</strong></font>Author/Composer:</div></td>\n";
		echo "				<td width=\"100%\" align=\"left\"><input name=\"author\" type=\"text\" id=\"author\" size=\"50\" value=\"".$author."\"></td>\n";
		echo "			</tr>\n";
		echo "            <tr valign=\"middle\">\n";
		echo "            	<td align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\" class=\"strong\"><font color=\"#FF0000\"><strong>*</strong></font>URL:</div></td>\n";
		echo "				<td width=\"100%\" align=\"left\"><input name=\"url\" type=\"text\" size=\"50\" value=\"".urldecode($url)."\"></td>\n";
		echo "			</tr>\n";
		echo "            <tr valign=\"middle\">\n";
		echo "            	<td width=\"25%\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\"><span class=\"strong\">Performer </span><span class=\"strong\">:</span></div></td>\n";
		echo "				<td width=\"100%\" align=\"left\"><input name=\"performer\" type=\"text\" id=\"performer\" size=\"50\" value=\"".$performer."\"></td>\n";
		echo "			</tr>\n";
		echo "            <tr valign=\"middle\">\n";
		echo "            	<td width=\"25%\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\"><span class=\"strong\">Book/Journal/Work Title</span><span class=\"strong\">:</span></div></td>\n";
		echo "				<td width=\"100%\" align=\"left\"><input name=\"volumeTitle\" type=\"text\" id=\"volumeTitle\" size=\"50\" value=\"".$volTitle."\"></td>\n";
		echo "			</tr>\n";
		echo "            <tr valign=\"middle\">\n";
		echo "            	<td width=\"25%\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\"><span class=\"strong\">Volume/ Edition</span><span class=\"strong\">:</span></div></td>\n";
		echo "				<td width=\"100%\" align=\"left\"><input name=\"volumeEdition\" type=\"text\" id=\"volumeEdition\" size=\"50\" value=\"".$volEdition."\"></td>\n";
		echo "			</tr>\n";
		echo "            <tr valign=\"middle\">\n";
		echo "            	<td width=\"25%\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\"><span class=\"strong\">Pages/Time</span><span class=\"strong\">:</span></div></td>\n";
		echo "				<td width=\"100%\" align=\"left\"><input name=\"pagesTimes\" type=\"text\" id=\"pages\" size=\"50\" value=\"".$pagesTimes."\"></td>\n";
		echo "            </tr>\n";
		echo "            <tr valign=\"middle\">\n";
		echo "            	<td width=\"25%\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\"><span class=\"strong\">Source/ Year</span><span class=\"strong\">:</span></div></td>\n";
		echo "				<td width=\"100%\" align=\"left\"><input name=\"source\" type=\"text\" id=\"source\" size=\"50\" value=\"".$source."\"></td>\n";
		echo "			</tr>\n";
		if ($contentNotes) {
		
			echo "            <tr valign=\"middle\">\n";
			echo "            	<td width=\"25%\" align=\"right\" bgcolor=\"#CCCCCC\"><div align=\"right\"><span class=\"strong\">Content Note:<br></span></div></td>\n";
			echo "				<td width=\"100%\" align=\"left\"><textarea name=\"contentNotes\" cols=\"50\" rows=\"3\">".$contentNotes."</textarea></td>\n";
			echo "			</tr>\n";
		}
		if ($itemNotes) {
			
			for ($i=0; $i<count($itemNotes); $i++) {
				
				if ($user->dfltRole >= $g_permission['staff'] || $itemNotes[$i]->getType() == "Instructor" || $itemNotes[$i]->getType() == "Content") {
					echo "      <tr valign=\"middle\">\n";
					echo "			";
					echo "			<!-- On page load, by default, there is no blank \"Notes\" field showing, only ";
					echo "			previously created notes, if any, and the \"add Note\" button. Notes should";
					echo "			be added one after the other at the bottom of the table, but above the \"add Note\" button.-->\n";
					echo "            	<td align=\"right\" bgcolor=\"#CCCCCC\"><span class=\"strong\">".$itemNotes[$i]->getType()." Note:</span><br><a href=\"index.php?cmd=editItem&reserveID=".$reserve->getReserveID()."&deleteNote=".$itemNotes[$i]->getID()."\">Delete this note</a></td>\n";
					echo "				<td align=\"left\"><textarea name=\"itemNotes[".$itemNotes[$i]->getID()."]\" cols=\"50\" rows=\"3\">".$itemNotes[$i]->getText()."</textarea></td>\n";
					echo "      </tr>\n";
				}
			}
		}			
		if ($instructorNotes) {
			
			for ($i=0; $i<count($instructorNotes); $i++) {
				
				echo "      <tr valign=\"middle\">\n";
				echo "			";
				echo "			<!-- On page load, by default, there is no blank \"Notes\" field showing, only ";
				echo "			previously created notes, if any, and the \"add Note\" button. Notes should";
				echo "			be added one after the other at the bottom of the table, but above the \"add Note\" button.-->\n";
				echo "            	<td align=\"right\" bgcolor=\"#CCCCCC\"><span class=\"strong\">Instructor Note:</span><br><a href=\"index.php?cmd=editItem&reserveID=".$reserve->getReserveID()."&deleteNote=".$instructorNotes[$i]->getID()."\">Delete this note</a></td>\n";
				echo "				<td align=\"left\"><textarea name=\"instructorNotes[".$instructorNotes[$i]->getID()."]\" cols=\"50\" rows=\"3\">".$instructorNotes[$i]->getText()."</textarea></td>\n";
				echo "      </tr>\n";
			}
		}			
		echo "          <tr valign=\"middle\">\n";
		//echo "            	<td colspan=\"2\" align=\"left\" valign=\"top\" bgcolor=\"#CCCCCC\" class=\"borders\" align=\"center\"><a href='index.php?cmd=addNote&reserveID=".$reserve->getReserveID()."'><input type=\"button\" name=\"addNote\" value=\"Add Note\"></a></td>\n";
		echo "            	<td colspan=\"2\" valign=\"top\" bgcolor=\"#CCCCCC\" class=\"borders\" align=\"center\">\n";
		echo "					<input type=\"button\" name=\"addNote\" value=\"Add Note\" onClick=\"openWindow('&cmd=addNote&reserve_id=".$reserve->getReserveID()."');\">\n";
		echo "				</td>\n";
		echo "			</tr>\n";
		echo "		</table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "    <tr>\n";
		echo "    	<td><strong><font color=\"#FF0000\">* </font></strong><span class=\"helperText\">=required fields</span></td>\n";
		echo "	</tr>\n";
		echo "    <tr>\n";
		echo "    	<td><div align=\"center\"><input type=\"submit\" name=\"Submit\" value=\"Save Changes\"></div></td>\n";
		echo "	</tr>\n";
		echo "    <tr>\n";
		echo "    	<td><img src=\images/spacer.gif\" width=\"1\" height=\"15\"></td>\n";
		echo "	</tr>\n";
		echo "</table>\n";
		echo "</form>\n";
	}

	
}
?>