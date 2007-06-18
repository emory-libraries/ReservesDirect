<?
/*******************************************************************************
zQuery.class.php
connect to library catalog to get book info

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

		if (isset($_SESSION['debug']))
			echo "$g_zReflector?host=$g_zhost&port=$g_zport&db=$g_zdb&query=" . urlencode($query) . "&start=$start&limit=$limit<br>";

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
		$search_results = array('title'=>'', 'author'=>'', 'edition'=>'', 'performer'=>'', 'times_pages'=>'', 'volume_title'=>'', 'source'=>'', 'controlKey'=>'', 'personal_owner'=>null, 'physicalCopy'=>'', 'OCLC'=>'', 'ISSN'=>'', 'ISBN'=>'');
		$sXML = simplexml_load_string(rtrim(ltrim($this->xmlResults)));

		//if (is_array($sXML->record->field) && !empty($sXML->record->field))
		if (!empty($sXML->record->field))
		{
			foreach ($sXML->record->field as $field) {
			   switch ($field[@type])
			   {
					case '001':  // control Number
			   			$search_results['controlKey'] = (string)$field;
			   			
			   			//also save this as OCLC w/o the letters
			   			if(stripos((string)$field, 'ocm') !== false) {	//found 'ocm'
			   				$search_results['OCLC'] = substr((string) $field, 3);	//strip off 'ocm'
			   			}
			   			elseif(stripos((string)$field, 'o') !== false) {	//did not find 'ocm', but found 'o'
			   				$search_results['OCLC'] = substr((string) $field, 1);	//strip off 'o'
			   			}
			   			else {
			   				$search_results['OCLC'] = (string)$field;	//just store the whole string
			   			}
			   		break;
			   		
					case '020':	// ISBN
						foreach($field->subfield as $subfield) {
							if((string)$subfield['type']=='a') {	//isbn = subfield type "a"
								$search_results['ISBN'] = (string)$subfield;
							}
						}
					break;
					
					case '022':	// ISSN
						foreach($field->subfield as $subfield) {
							if((string)$subfield['type']=='a') {	//issn = subfield type "a"
								$search_results['ISSN'] = (string)$subfield;
							}
						}
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
			   					if($search_results['title'] == "")
			   						$search_results['title'] = (string)$subfield;
			   					else
			   						$search_results['title'] .= " ".(string)$subfield;
			   			}
			   		break;

			   		case '260':
			   			$search_results['source'] = "";
			   			foreach ($field->subfield as $subfield)
			   			{
			   					if($search_results['source'] == "")
			   						$search_results['source'] = (string)$subfield;
			   					else
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
		
		$key = ereg_replace('ocm','o',$key);
		
		if (isset($_SESSION['debug']))
			echo $g_holdingsScript . "?key=" . $key . "&key_type=$keyType<P>";
						
		$fp = fopen($g_holdingsScript . "?key=" . $key . "&key_type=$keyType", "rb");
		if(!$fp) {
			trigger_error("zQuery could not get holdings", E_USER_ERROR);
		}
		while (!feof ($fp)) {
			array_push($rs, @fgets($fp, 1024));
		}
		$returnStatus = join($rs, "");

		if(ereg("Outcome=OK\n", $returnStatus))
		{
			list($devnull, $holdings) = split("Outcome=OK\n", $returnStatus);

			$thisCopies = split("\n", $holdings);

			$j = 0;
			for($i = 0; $i < (count($thisCopies) - 1); $i++)
			{
				//list($catKey, $sequence, $copy, $callnum, $loc, $type, $bar, $library) = split("\|", $thisCopies[$i]);
				list($devnull, $devnull, $copy, $callnum, $loc, $type, $bar, $library, $status, $reservesDesk) = split("\|", $thisCopies[$i]);
				if ($copy != "" && $callnum != "")
				{
					$tmpArray[$j]['copy']		= $copy;
					$tmpArray[$j]['callNum']	= $callnum;
					$tmpArray[$j]['loc']		= $loc;
					$tmpArray[$j]['type']		= $type;
					$tmpArray[$j]['bar']		= ltrim(rtrim($bar));
					$tmpArray[$j]['library']	= $library;
					$j++;
				}
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
