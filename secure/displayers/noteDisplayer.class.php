<?
/*******************************************************************************
noteDisplayer.class.php


Created by Kathy Washington (kawashi@emory.edu)

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
require_once('secure/displayers/baseDisplayer.class.php');

class noteDisplayer extends baseDisplayer {
	
	/**
	* @return void
	* @param $user, $reserveID
	* @desc display Add Note form
	*/
	function displayAddNoteScreen($user, $hidden_fields)
	{
		global $g_permission, $g_notetype;

		echo "<form name=\"addNote\" action=\"index.php?no_table=1&amp;cmd=addNote\" method=\"post\">\n";
		
		//show hidden fields
		self::displayHiddenFields($hidden_fields);

		echo '<center>';
		echo '<table width="400" border="0" cellspacing="0" cellpadding="0" style="margin-top:30px;">';
  		echo '	<tr><td align="left" valign="top"><h1>ReservesDirect</h1></td></tr>';
		echo '	<tr><td align="left" valign="top" style="padding-bottom:30px;"><h2>Add Note</h2></td></tr>';
 // 		echo '	<tr><td align="left" valign="top">&nbsp;</td></tr>';
  //		echo '	<tr><td align="left" valign="top">&nbsp;</td></tr>';
  		if ($user->getRole() >= $g_permission['staff']) {
  			echo '	<tr>';
  			echo '  	<td align="left" valign="top">';
  			echo '			<table width="100%" border="0" cellspacing="0" cellpadding="0">';
  			echo '  			<tr>';
  			echo '  				<td width="50%" class="headingCell1">Note Options</td>';
  			echo '      			<td>&nbsp;</td>';
  			echo '				</tr>';
  			echo '			</table>';
  			echo '		</td>';
  			echo '	</tr>';
  			echo '	<tr>';
  			echo '  	<td align="left" valign="top">';
  			echo '			<table width="100%" border="0" cellpadding="3" cellspacing="0" class="borders">';
  			echo '    			<tr align="left" valign="top" bgcolor="#CCCCCC">';
  			echo '      			<td width="22%" valign="top"><p class="strong">Note Type:<br>';
  			echo '	    			<span class="small-x">(This will show as the title of the note for editing';
			echo '				    purposes.)</span></p>';
			echo '					</td>';
        	echo '					<td width="78%" align="left"><p>';
       		echo '					<label><input type="radio" name="noteType" value="'.$g_notetype['content'].'" checked>Content Note</label><br>';
       		
       		//only allow instructor notes if editing reserve
       		if(!empty($hidden_fields['reserveID'])) {
       			echo '					<label><input type="radio" name="noteType" value="'.$g_notetype['instructor'].'">Instructor Note</label><br>';
       		}
       		
       		echo '					<label><input type="radio" name="noteType" value="'.$g_notetype['staff'].'">Staff Note</label><br>';
			echo '					<label><input type="radio" name="noteType" value="'.$g_notetype['copyright'].'">Copyright Note</label><br>';
			echo '					</p></td>';
			echo '				</tr>';
			echo '			</table>';
			echo '		</td>';
  			echo '	</tr>';
		} else {
			echo '					<input type="hidden" name="noteType" value="'.$g_notetype['instructor'].'">';
		}

  		echo '	<tr>';
  		echo '		<td align="left" valign="top">&nbsp;</td>';
  		echo '	</tr>';
  		echo '	<tr>';
    	echo '		<td align="left" valign="top">';
    	echo '			<table width="100%" border="0" cellspacing="0" cellpadding="0">';
      	echo '				<tr>';
        echo '					<td width="50%" class="headingCell1">Note Text</td>';
        echo '					<td>&nbsp;</td>';
      	echo '				</tr>';
    	echo '			</table>';
    	echo '		</td>';
  		echo '	</tr>';
  		echo '	<tr>';
    	echo '		<td align="left" valign="top" class="borders">';
    	echo '			<table width="100%" border="0" cellspacing="0" cellpadding="3">';
      	echo '				<tr>';
        echo '					<td align="center" valign="top"><textarea name="noteText" cols="45"></textarea></td>';
      	echo '				</tr>';
    	echo '			</table>';
    	echo '		</td>';
  		echo '	</tr>';

  		echo "    <tr><td><img src=\images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
  		echo "    <tr>\n";
		echo "    	<td align=\"center\"><input type=\"submit\" value=\"Save Note\"></td>\n";
		echo "	</tr>\n";
		echo "    <tr><td><img src=\images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n";
		echo '</table>';

		echo '</td>';
		echo '</tr>';
		echo '</table>';
		echo "</form>\n";
		echo "</center>";
	}


	function displaySuccess($noteID)
	{
		echo "<script language=\"JavaScript\">this.window.opener.newWindow_returnValue='$noteID';</script>\n"; //pass value to parent window

		echo "<table width=\"400\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" style=\"margin-top:30px;\">\n"
		.	 "	<tbody>\n"
		.	 "	<tr><td align=\"left\" valign=\"top\"><h1>ReservesDirect</h1></td></tr>\n"
		.	 "	<tr><td align=\"left\" valign=\"top\" style=\"padding-bottom:30px;\"><h2>Add Note</h2></td></tr>\n"
		.	 "		<tr><td width=\"100%\"><img src=\images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n"
		.	 "		<tr>\n"
	    .	 "			<td align=\"left\" valign=\"top\" class=\"borders\" style=\"text-align:center; padding:5px 15px 10px 15px;\">\n"
	    .	 "				<p><strong>You have successfully added a note.</strong></p>\n"
	    .	 "						<p>Your note will not appear until you Save Changes to the item or heading you are working on.</p>\n"
		.	 "						<p>Please close this window to Continue</p>\n"
		.	 "						<p><input type=\"button\" value=\"Close Window\" onClick=\"window.close();\"></p>\n"
		.	 "			</td>\n"
		.	 "		</tr>\n"
		.	 "		<tr><td><img src=\images/spacer.gif\" width=\"1\" height=\"15\"></td></tr>\n"
		.	 "	</tbody>\n"
		.	 "</table>\n"
		;
	}
}
?>