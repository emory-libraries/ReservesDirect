<?
/*******************************************************************************
reportDisplayer.class.php


Created by Jason White (jbwhite@emory.edu)

This file is part of GNU ReservesDirect 2.1

Copyright (c) 2004-2005 Emory University, Atlanta, Georgia.

ReservesDirect 2.1 is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

ReservesDirect 2.1 is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with ReservesDirect 2.1; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

Reserves Direct 2.1 is located at:
http://www.reservesdirect.org/

*******************************************************************************/
require_once("secure/classes/terms.class.php");
require_once("secure/managers/ajaxManager.class.php");

class reportDisplayer extends baseDisplayer {
	/**
	 * Display List all reports available to user
	 *
	 * $reportList array reportID, reportTitle
	 */
	function displayReportList($reportList)
	{
		
		echo "<table width=\"60%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr><td width=\"100%\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>\n";

		if (empty($reportList))
			echo "<tr><td>No Reports Specified</td></tr>\n";
		else
		{
			$i = 0;
			while($i<count($reportList))
			{
				echo "<tr>\n";
				echo "	<td NOWRAP><a href=\"index.php?cmd=viewReport&reportID=" . $reportList[$i]['report_id'] . "\">" . $reportList[$i]['title'] . "</a></td>\n";			
				echo "<tr>\n";
				$i++;
			}
		}
		
		echo "</table>\n";
	}
	
	function displayReport($title, &$dataSet)
	{
		echo "<div style=\"align:right; text-align:right; padding=5px;\"><a href=\"index.php?cmd=reportsTab\">Return to Reports List</a></div>\n";
		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tr><td width=\"100%\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"></td></tr>\n";

		echo "	<tr><td>&nbsp;</td></tr>\n";
	    echo "  <tr>\n";
	    echo "      <td><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
	    echo "      	<tr align=\"left\" valign=\"top\">\n";
	    echo "          	<td class=\"headingCell1\"><div align=\"center\">$title</div></td>\n";
	    echo "          	<td width=\"75%\">&nbsp;</td>\n";
	    echo "        	</tr>\n";
	    echo "      	</table>\n";
	    echo "		</td>\n";
	    echo "  </tr>\n";
	    echo "	<tr>\n";
	    echo "  	<td align=\"left\" valign=\"top\" class=\"borders\"><table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" class=\"displayList\">\n";

	    if (!empty ($dataSet))
	    {
		    echo "<tr>";
		    foreach ($dataSet[0] as $key => $value) 
		    	echo "<td><b>$key</b></td>";
			echo "</tr>\n";	    	
		    
			$i = 0;
			$dataSet_0 = '';  //when first column changes we will insert a blank row
			while($i<count($dataSet))
			{
				$rowClass = ($i % 2) ? "evenRow" : "oddRow";
				
				echo "<tr align=\"left\" valign=\"middle\" class=".$rowClass.">\n";
				foreach ($dataSet[$i] as $key => $value) 
				{
					echo "	<td>$value</td>\n";				
				}
				$i++;
				echo "</tr>\n";
				
			}
	    } else {
	    	echo "<tr><td><font color=\"red\">Report completed with no results</font></td></tr>\n";
	    }
		echo "</table>\n";
		echo "</td></tr></table>\n";
	
	    echo "<div style=\"padding:15px; text-align:center;\">\n";
	    echo "  <form method=\"post\" action=\"tsvGenerator.php\"><br/>\n";
	    echo "      <input type=\"hidden\" name=\"dataSet\" value=\"" . urlencode(serialize($dataSet)) . "\"/>\n";
	    echo "      <input type=\"submit\" name=\"exportTsv\" value=\"Export to Spreadsheet\"/><br/><br/>\n";
	    echo "  </form>\n";
	    echo "</div>\n";
	}
	
	function enterReportParams(&$report)
	{
		global $u, $g_permission;
			
		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		
		
	    echo "  <tr>\n";
	    echo "      <td><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
	    echo "      	<tr align=\"left\" valign=\"top\">\n";
	    echo "          	<td class=\"headingCell1\"><div align=\"center\">". $report->getTitle()  ."</div></td>\n";
	    echo "          	<td width=\"75%\">&nbsp;</td>\n";
	    echo "        	</tr>\n";
	    echo "      	</table>\n";
	    echo "		</td>\n";
	    echo "  </tr>\n";

	    switch ($report->getParam_group())
	    {
	    	case 'term_lib': 
	    		$t = new terms();
?>
	<tr>
		<td colspan="2" valign="top">
			<form method="post" action="index.php">
			
				<input type="hidden" name="reportID" value="<?=$report->getReportID()?>" />
				<input type="hidden" name="cmd" value="viewReport" />
				
				(Use SHIFT/CTRL keys to select multiple values)
				<p />
				<strong>Term(s):</strong>
				<br />
				<select multiple="true" name="term_id[]" size="5">
<?php		foreach($t->getTerms(true) as $term): ?>
					<option value="<?=$term->getTermID()?>"><?=$term->getTerm()?></option>
<?php		endforeach; ?>
				</select>
				
				<p />
				
				<strong>Library(ies):</strong>
				<br /> 
				<select multiple="true" name="library_id[]" size="5">
<?php		foreach($u->getLibraries() as $lib): ?>
					<option value="<?=$lib->getLibraryID()?>"><?=$lib->getLibrary()?></option>
<?php		endforeach; ?>
				</select>
				<p />
				<input type="submit" name="submit" value="Generate Report" />
			</form>
		</td>
	</tr>
<?php
	    	break;
	    	
	    	case 'term':
	    		$t = new terms();
?>
	<tr>
		<td colspan="2" valign="top">
			<form method="post" action="index.php">
			
				<input type="hidden" name="reportID" value="<?=$report->getReportID()?>" />
				<input type="hidden" name="cmd" value="viewReport" />
				
				(Use SHIFT/CTRL keys to select multiple values)
				<p />
				<strong>Term(s):</strong>
				<br />
				<select multiple="true" name="term_id[]" size="5">
<?php		foreach($t->getTerms(true) as $term): ?>
					<option value="<?=$term->getTermID()?>"><?=$term->getTerm()?></option>
<?php		endforeach; ?>
				</select>

				<p />
				<input type="submit" name="submit" value="Generate Report" />
			</form>
		</td>
	</tr>
<?php    		
			break;
			
			case 'class':
				//generate class list for instructors
				$class_list = $u->getCourseInstancesToEdit();				
?>
	<tr>
		<td colspan="2" valign="top">
		
			<?php self::displaySelectClass('viewReport', $class_list, '', array('reportID'=>$_REQUEST['reportID'])); ?>

		</td>
	</tr>
<?php
			break;			
			
			default:
	    }
	    echo "</table>";
	}
}
?>
