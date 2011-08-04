<?
/*******************************************************************************
searchItem.class.php
methods to search and display Items

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

class searchItems
{
	public $items = array();
	public $totalCount;
	public $query;
	public $field;
	public $first = 0;
	public $end = 20;


	/**
	 * @return array of reserveItems
	 * @param string $term search field
	 * @param string $value  search value
	 * @desc Searchs the items table for the value in the term fields
	*/
	function search($term, $value, $f=0, $e=20)
	{
		global $g_dbConn, $u, $g_permission;

		$this->query = rtrim(ltrim($value));
		$this->field = $term;

		$values = explode(" ", strtolower($this->query));

		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql_select = "SELECT i.item_id, i.item_group ";
				$sql_cnt    = "SELECT count(i.item_id) ";

				$sql = "FROM items as i ";
					
				$sql .=  "WHERE (i.item_type != 'HEADING') ";
				
				if ($u->getRole() < $g_permission['staff'])
				{
					$sql .= " AND (i.status <> 'DENIED') ";
				}
				
				$sql .= " AND ( i.private_user_id IS NULL OR i.private_user_id = " . $u->getUserID() . " ) ";
				$sql .=  "AND ("
					;

				for($i=0;$i<count($values);$i++)
				{
					if ($i != 0) $sql .= " AND ";
					$sql .= "LOWER(i.$term) REGEXP '" . $values[$i] . "'";
				}

				$sql .= ")";

				switch ($term)
				{
					case 'Author':

						for($i=0;$i<count($values);$i++)
						{
							if ($i == 0) $s = " OR (";
							else $s = " AND ";

							$sql1 .= $s . " LOWER(i.title) REGEXP '/.*" . $values[$i] . "'";  //IN SOME CASES EUCLID STORES THE AUTHOR PRECEDED BY '\' IN THE TITLE FIELD
							$sql2 .= $s . " LOWER(i.performer) REGEXP '" . $values[$i] . "'";
						}
						$sql .= $sql1 . ") " . $sql2 . ")";
					break;

					case 'Title':
						for($i=0;$i<count($values);$i++)
						{
							if ($i == 0) $sql .= " OR (";
							else $sql .= " AND ";
							$sql .= " LOWER(i.volume_title) REGEXP '/.*" . $values[$i] . "'";
						}
						$sql .= ")";
					break;

					case 'instructor':
						// replace sql statement to select from reserves table
						$sql_select = "SELECT DISTINCT i.item_id, i.item_group ";
						$sql =  "FROM access as a "
						.	   "  JOIN course_aliases as ca ON a.alias_id = ca.course_alias_id "
						.	   "  JOIN course_instances as ci ON ca.course_instance_id = ci.course_instance_id "
						.	   "  JOIN reserves as r ON ci.course_instance_id = r.course_instance_id "
						.	   "  JOIN items as i ON r.item_id = i.item_id "
						//.	   "WHERE a.user_id = " . $values[0]
						.	   "WHERE i.item_type != 'HEADING' AND a.user_id = " . $values[0] . " AND i.status <> 'DENIED'" 
						.	   "  AND ( i.private_user_id IS NULL OR i.private_user_id = " . $u->getUserID() . " ) "
						;
				}

				$this->first = ($f > $this->first) ? $f : $this->first;
				$this->end   = ($e > $this->end) ? $e : $this->end;
				$sql_order  = " ORDER BY item_group LIMIT " . $this->first . "," . $this->end;
		}
		
		if (isset($_SESSION['debug'])) 
		{
		 	echo "itemSearch::search $sql_select $sql $sql_order<br>";
		}
		
		$rs = $g_dbConn->query($sql_select . $sql . $sql_order);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$this->items = array();
		while($row = $rs->fetchRow())
		{
			$this->items[] = new reserveItem($row[0]);
		}

		$rs = $g_dbConn->query($sql_cnt . $sql);
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }

		$row = $rs->fetchRow();
		$this->totalCount = $row[0];

	}

	function getMoreLink($page, $cmd, $ci)
	{
		if ($this->totalCount >= $this->end){
			$f = $this->first + 20;
			$e = 20;
			return "<a href=\"index.php?cmd=$cmd&ci=$ci&f=$f&e=$e&field=" . $this->field . "&query=" . urlencode($this->query) . "\">more</a>";
		} else {
			return "";
		}
	}

	/**
	 * @return void
	 * @param string $page 	  -- the current page selector
	 * @param string $subpage -- subpage selector
	 * @param string $courseInstance -- user selected courseInstance
	 * @desc Allows user search for items
	 * 		expected next steps
	 *			open catalog in new window
	 *			searchItems::displaySearchResults
	*/
	function displaySearchScreen($page, $subpage, $courseInstance=null)
	{
		global $g_catalogName, $g_libraryURL;

		$instructors = common_getUsers('instructor');

		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
		echo "	<tbody>\n";
		echo "		<tr><td width=\"140%\"><img src=\"images/spacer.gif\" width=\"1\" height=\"5\"> </td></tr>\n";
		echo "		<tr>\n";
        echo "			<td align=\"left\" valign=\"top\">\n";
        echo "				<table border=\"0\" align=\"center\" cellpadding=\"10\" cellspacing=\"0\">\n";
		echo "					<tr align=\"left\" valign=\"top\" class=\"headingCell1\">\n";
        echo "						<td width=\"50%\">Search for Archived Materials</td><td width=\"50%\">Search by Instructor</td>\n";
        echo "					</tr>\n";

        echo "					<tr>\n";
        //		SEARCH BY Author or Title
        echo "						<td width=\"50%\" class=\"borders\" align=\"center\">\n";
        echo "							<br>\n";
        echo "							<form action=\"index.php\" method=\"post\">\n";
        echo "							<input type=\"text\" name=\"query\" size=\"25\">\n";
        echo "							<input type=\"hidden\" name=\"page\" value=\"$page\">\n";
		echo "							<input type=\"hidden\" name=\"subpage\" value=\"$subpage\">\n";
        if (!is_null($courseInstance)) echo "							<input type=\"hidden\" name=\"ci\" value=\"$courseInstance\">\n";
        //echo "							<br>\n";
        echo "							<select name=\"field\">\n";
        echo "								<option value=\"Title\" selected>Title</option><option value=\"Author\">Author</option>\n";
        echo "							</select>\n";
        //echo "							<br>\n";
        //echo "							<br>\n";
        echo "							<input type=\"submit\" name=\"Submit\" value=\"Find Items\">\n";
        echo "							<br>\n";
        echo "							<br>\n";
        echo "							</form>\n";
        echo "						</td>\n";

        echo "						<td width=\"50%\" align=\"left\" valign=\"top\" class=\"borders\" align=\"center\" NOWRAP>\n";
        echo "							<form method=\"post\" action=\"index.php\" name=\"frmReserveItem\">\n";
		echo "								<input type=\"hidden\" name=\"page\" value=\"$page\">\n";
		echo "								<input type=\"hidden\" name=\"subpage\" value=\"$subpage\">\n";
		echo "								<input type=\"hidden\" name=\"searchType\" value=\"reserveItem\">\n";
		echo "								<input type=\"hidden\" name=\"field\" value=\"instructor\">\n";
		if (!is_null($courseInstance)) echo "					<input type=\"hidden\" name=\"ci\" value=\"$courseInstance\">\n";

        echo "								<br>\n";
		echo "								<select name=\"query\">\n";
	    echo "									<option value=\"--\" selected>Choose an Instructor\n";
        foreach($instructors as $instructor)
		{
			echo "									<option value=\"" . $instructor['user_id'] . "\">" . $instructor['full_name'] . "</option>\n";
		}

        echo "								</select>\n";
        //echo "								<br>\n";
        //echo "								<br>\n";
        echo "								<input type=\"submit\" name=\"Submit2\" value=\"Get Instructor's Reserves\">\n";
        echo "							</form>\n";
        echo "						</td>\n";
        echo "					</tr>\n";
        echo "					<tr align=\"left\" valign=\"top\">\n";
		echo "						<td colspan=\"2\" class=\"borders\" align=\"center\">\n";
        echo "						</td>\n";
        echo "					</tr>\n";
        echo "				</table>\n";
		echo "			</td>\n";
		echo "		</tr>\n";
		echo "	</tbody>\n";
		echo "</table>\n";

	}

}
?>
