<?
/*******************************************************************************
term.class.php
term object handles term table

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
class term
{
	public $term_id;
	public $sort_order;
	public $term_name;
	public $term_year;
	public $begin_date;
	public $end_date;
	
	function term($termID)
	{
		global $g_dbConn;
		
		switch ($g_dbConn->phptype)
		{
			default: //'mysql'
				$sql = 	"SELECT term_id, sort_order, term_name, term_year, begin_date, end_date "
				.		"FROM terms "
				.		"WHERE term_id = !"
				;			
		}
		
		$rs = $g_dbConn->query($sql, $termID);		
		if (DB::isError($rs)) { trigger_error($rs->getMessage(), E_USER_ERROR); }
		
		$row = $rs->fetchRow();
			$this->term_id 		= $row[0];
			$this->sort_order 	= $row[1];
			$this->term_name 	= $row[2];
			$this->term_year 	= $row[3];
			$this->begin_date 	= $row[4];
			$this->end_date 	= $row[5];
	}	
	
	function getTermID() { return $this->term_id; }
	function getSortOrder() { return $this->sort_order; }
	function getTerm() { return $this->term_name . " " . $this->term_year; }
	function getTermName() { return $this->term_name; }
	function getTermYear() { return $this->term_year; }
	function getBeginDate() { return $this->begin_date; }
	function getEndDate() { return $this->end_date; }	
	
	/**
	* @return date
	* @desc returns the date after which the term becomes modifiable
	*/
	function getModifyBeginDate() 
	{
		$D = explode("-", $this->begin_date);
		
		$m = $D[1];
		$d = $D[2];
		$y = $D[0];
		
		if ($m == "01"){
			$m = "12";
			$y = $y - 1;
		} else 
			$m = $m - 1;

		return "$y-$m-$d";		
	} 
	
	/**
	* @return date
	* @desc returns the date after which the term is nolonger modifiable
	*/
	function getModifyEndDate() 
	{
		$D = explode("-", $this->end_date);
		
		$m = $D[1];
		$d = $D[2];
		$y = $D[0];
		
		if ($m == "12"){
			$m = "01";
			$y = $y + 1;
		} else 
			$m = $m + 1;
			
		return "$y-$m-$d";		
	}
	
}
?>