<?
/*******************************************************************************
zQuery.class.php
connect to library catalog to get book info

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
require_once("secure/common.inc.php");
require_once("secure/config.inc.php");

class zQuery
{
	public $xmlResults;

	function zQuery($search_term, $search_field='control')
	{
		global $g_zhost;

		if ($search_field == 'barcode')  //use barcode to get controlNumber
		{			
			//open socket to EUCLID widget which will return a controlNumber 
			$fp = fsockopen($g_zhost, 4321, $errno, $errstr, 60);
			if (!$fp) {
				 trigger_error("zQuery could not connect $errstr ($errno)", E_USER_ERROR); 
			} else {
				fwrite ($fp, $search_term);
				while (!feof($fp)) {
					$term =  fgets ($fp,128);
					$term = ereg_replace("[^A-z0-9]", "", $term);
					//echo "bib=$term<hr>";            
				}
				fclose ($fp);
			}
		} else $term = $search_term;
		//$bibval = array("isbn"=>7, "issn"=>8,"bib"=>12,"callno"=>926, "title"=>4);		
		//echo '@attr 1=' . $bibval[$search_field] .  ' "' . $term . '"<br>';
		
		//search for xml data based on the controlNumber		
		
		if (ltrim(rtrim($term)) != "")
			$this->zDoQuery('@attr 1=12 "' . $term . '"', 0, 1);
	}
	
	function zDoQuery($query, $start, $limit) 
	{
		/*
		// Executes a z39.50 search
		// Get back our results in MARC format
		yaz_syntax($zConn, "xml");
		// We only want 10 records at a time -- "$start" is the record number we want to start from
		yaz_range($zConn, $start, 10);
		// Throw in some default attributes -- (4 (Structure) = 1 (Phrase), 3 (Position) = 3 (any position), 5 (Truncate) = 1 (Right Truncate)
		yaz_search($zConn,"rpn", $query);
		// yaz_wait actually executes the query
		yaz_wait();
		*/
		
		global $g_zhost, $g_zport, $g_zdb, $g_zReflector;
		//echo "$g_zReflector?host=$g_zhost&port=$g_zport&db=$g_zdb&query=" . urlencode($query) . "&start=$start&limit=$limit<br>";

		$xmlresults = "";
		if (ereg('ocm[0-9]+', $query)) // until corrected we can only search for non-personal items
		{
			$fp = fopen("$g_zReflector?host=$g_zhost&port=$g_zport&db=$g_zdb&query=" . urlencode($query) . "&start=$start&limit=$limit", "r");
			if(!$fp) {
				echo("<TR><TD>Unable to access g_zReflector at $g_zReflector!</TD></TR>\n");
			}
			while(!feof($fp)) {
			      $xmlresults.= fread($fp,1024);
			}
			fclose($fp);
			
			$this->xmlResults = $xmlresults;
		} //else  echo("<TR><TD>Record not found.  This item may be hidden from searches please enter catalog information manually.</TD></TR>\n");
	}
	
	function getResults()
	{
		return simplexml_load_string(rtrim(ltrim($this->xmlResults)));		
	}
	
	function showXMLResults()
	{
		echo htmlentities($this->xmlResults);
	}
	
	function parseToArray()
	{
		$search_results = array('title'=>'', 'author'=>'', 'edition'=>'', 'performer'=>'', 'times_pages'=>'', 'volume_title'=>'', 'source'=>'', 'content_note'=>'', 'controlKey'=>'', 'personal_owner'=>null, 'physicalCopy'=>'');
		$sXML = simplexml_load_string(rtrim(ltrim($this->xmlResults)));
		
		//if (is_array($sXML->record->field) && !empty($sXML->record->field))
		if (!empty($sXML->record->field))
		{
			foreach ($sXML->record->field as $field) {
			   switch ($field[@type])
			   {
						case '001':  // control Number
			   			$search_results['controlKey'] = (string)$field;
			   		break;
			   		
			   		case '100':
			   		case '110':
			   		case '111':
			   			foreach ($field->subfield as $subfield)
			   				$search_results['author'] .= (string)$subfield;
						   	
			   		case '245': //Title
			   			$search_results['title'] = "";
			   			foreach ($field->subfield as $subfield)
			   			{
			   					$search_results['title'] .= " ".(string)$subfield;
			   			}
			   		break;
			   					   		
			   		case '260':
			   			$search_results['source'] = "";
			   			foreach ($field->subfield as $subfield)
			   			{
			   					$search_results['source'] .= " ".(string)$subfield;
			   			}
			   		break;

	
					/* replaced by getHoldings jbwhite 10/25/04
			   		case '926':	
			   			unset($tmpArray);
			   			$tmpArray = array();	   			
			   			foreach ($field->subfield as $subfield)
			   			{		   				
			   				if ($subfield[@type] == 'a')
			   					 $tmpArray['library'] = (string)$subfield;
			   				if ($subfield[@type] == 'b')
			   					$tmpArray['status'] = (string)$subfield;
			   				if ($subfield[@type] == 'c')
			   					$tmpArray['callNumber'] = (string)$subfield;
			   				if ($subfield[@type] == 'd')
			   					$tmpArray['type'] = (string)$subfield;
			   				if ($subfield[@type] == 'e')
			   					$tmpArray['returnDate'] = (string)$subfield;		   					
			   				if ($subfield[@type] == 'f')
			   					$tmpArray['copy'] = (string)$subfield;
			   			}
			   			$search_results['physicalCopy'][] = $tmpArray;
			   		break;					   		
			   		*/
				}
			}	
		}			
		return $search_results;
	}
	
	function getHoldings($keyType, $key)
	{
		global $g_holdingsScript;

		$rs = array();
		//echo $g_holdingsScript . "?key=" . $key . "&key_type=$keyType<P>";
		$fp = fopen($g_holdingsScript . "?key=" . $key . "&key_type=$keyType", "rb");
		if(!$fp) {
			trigger_error("zQuery could not get holdings", E_USER_ERROR);
		}
		while (!feof ($fp)) {
			array_push($rs, @fgets($fp, 1024));            
		}
		$returnStatus = join($rs, "");

		if(ereg("outcome=OK", $returnStatus)) 
		{
			list($devnull, $holdings) = split("result: ", $returnStatus);
			$thisCopies = split("\n", $holdings);
			for($i = 0; $i < (count($thisCopies) - 1); $i++) 
			{
				list($catKey, $sequence, $copy, $callnum, $loc, $type, $bar, $library) = split("\|", $thisCopies[$i]);							
					$tmpArray[$i]['catKey'] 	= $catKey;
					$tmpArray[$i]['sequence'] 	= $sequence;
					$tmpArray[$i]['copy']		= $copy;
					$tmpArray[$i]['callNum']	= $callnum;
					$tmpArray[$i]['loc']		= $loc;
					$tmpArray[$i]['type']		= $type;
					$tmpArray[$i]['bar']		= ltrim(rtrim($bar));
					$tmpArray[$i]['library']	= $library;
 			}
/*jbwhite we now want to display all holding info
 			if ($keyType == "barcode")
 			{ 			
				for($x = 0; $x < count($tmpArray); $x++) {
					if($key == $tmpArray[$x]['bar']) 
					{	
						return array($tmpArray[$x]);  //we need to make this a multi dem array
					}
				}
 			} else
*/ 			
				return $tmpArray;
		} else return null;
	}
}

?>