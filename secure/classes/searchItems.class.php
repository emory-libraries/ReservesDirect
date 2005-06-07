<?
/*******************************************************************************
searchItem.class.php
methods to search and display Items

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

Created by Jason White (jbwhite@emory.edu)

Reserves Direct 2.0 is located at:
http://coursecontrol.sourceforge.net/

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
		global $g_dbConn;
		
		$this->query = rtrim(ltrim($value));
		$this->field = $term;
				
		$values = explode(" ", strtolower($this->query));
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql_select = "SELECT i.item_id, i.item_group ";
				$sql_cnt    = "SELECT count(i.item_id) ";
				
				$sql = "FROM items as i "						  
					.  "WHERE ("
					;
					
				for($i=0;$i<count($values);$i++)
				{
					if ($i != 0) $sql .= " AND ";
					$sql .= "LOWER(i.$term) REGEXP '" . $values[$i] . "'";
				}	
				
				$sql .= ")";
					
				switch ($term)
				{
					case 'author':
					
						for($i=0;$i<count($values);$i++)
						{
							if ($i == 0) $s = " OR (";
							else $s = " AND ";
							
							$sql1 .= $s . " LOWER(i.title) REGEXP '/.*" . $values[$i] . "'";  //IN SOME CASES EUCLID STORES THE AUTHOR PRECEDED BY '\' IN THE TITLE FIELD
							$sql2 .= $s . " LOWER(i.performer) REGEXP '" . $values[$i] . "'";
						}
						$sql .= $sql1 . ") " . $sql2 . ")";
					break;
					
					case 'title':
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
						.	   "WHERE a.user_id = " . $values[0]
						;
				}	

				$this->first = ($f > $this->first) ? $f : $this->first;
				$this->end   = ($e > $this->end) ? $e : $this->end;	
				$sql_order  = " ORDER BY item_group LIMIT " . $this->first . "," . $this->end;	
		}
		//echo "itemSeach::search $sql_select . $sql . $sql_order<br>";
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
				      	
		echo "<table width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
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
        echo "							<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\">\n";
        echo "								<tr>\n";
        echo "									<td align=\"left\" valign=\"top\" align=\"center\">\n";
        echo "										You may also search the library's collection in <a href=\"$g_libraryURL\">$g_catalogName</a>.\n";
        echo "									</td>\n";
        echo "								</tr>\n";
        echo "							</table>\n";
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