<?
/*******************************************************************************
reportDisplayer.class.php


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


Reserves Direct is located at:
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
	    echo "      <input type=\"hidden\" name=\"dataSet\" value=\"" . base64_encode(serialize($dataSet)) . "\"/>\n";
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
			
	    	case 'term_dates':
	    		//get array of all terms
	    		$t = new terms();
				$terms = $t->getTerms(true);
?>
	<tr>
		<td colspan="2" valign="top">
			<script type="text/javascript">
				//define associative arrays indexed by term_id, containing begin and end dates
				var term_begin_dates = {
<?php
			//need this so that we can string the rest w/ preceding comma
			echo '"null":"null"';
			foreach($terms as $term) {
				echo ', "'.$term->getTermID().'":"'.$term->getBeginDate().'"';
			}
?>				
				};
				var term_end_dates = {
<?php
			//need this so that we can string the rest w/ preceding comma
			echo '"null":"null"';
			foreach($terms as $term) {
				echo ', "'.$term->getTermID().'":"'.$term->getEndDate().'"';
			}
?>				
				};
				
				function prefill_term_dates(term_id) {					
					if(document.getElementById('begin_date')) {
						document.getElementById('begin_date').value = term_begin_dates[term_id];
					}
					if(document.getElementById('end_date')) {
						document.getElementById('end_date').value = term_end_dates[term_id];
					}
				}
			</script>
			
			<form method="post" action="index.php">
				<input type="hidden" name="reportID" value="<?=$report->getReportID()?>" />
				<input type="hidden" name="cmd" value="viewReport" />
				
				<p />
				<strong>Term:</strong>
				<br />
				<select name="term_id" onchange="javascript: prefill_term_dates(this.options[this.selectedIndex].value);">
<?php		foreach($terms as $term): ?>
					<option value="<?=$term->getTermID()?>"><?=$term->getTerm()?></option>
<?php		endforeach; ?>
				</select>
				<p />
				<strong>Dates (required):</strong>
				<br />
				<input type="text" id="begin_date" name="begin_date" size="10" maxlength="10" value="<?php echo $terms[0]->getBeginDate(); ?>" />&nbsp;to&nbsp;<input type="text" id="end_date" name="end_date" size="10" maxlength="10" value="<?php echo $terms[0]->getEndDate(); ?>" />&nbsp; (YYYY-MM-DD)
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
