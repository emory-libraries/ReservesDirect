<?php

/**
 * @category   RD
 * @package    RD_Ils
 * @copyright  
 * @license    
 */
require_once("lib/RD/Ils/AbstractResult.php");

class RD_Ils_Result extends AbstractResult
{    
	/**
    * Parse xml data and return array
    * NOTE:  this does not parse all fields only those currently used.
    * Add additional case matches to parse addtional fields
    *
    * @return Array
    */    
	public function to_a()
	{
		$search_results = array('title'=>'', 'author'=>'', 'edition'=>'', 'performer'=>'', 'times_pages'=>'', 'volume_title'=>'', 'source'=>'', 'controlKey'=>'', 'personal_owner'=>null, 'physicalCopy'=>'', 'OCLC'=>'', 'ISSN'=>'', 'ISBN'=>'', 'holdings' => array());
		$sXML = simplexml_load_string($this->getData());

		//if (is_array($sXML->record->field) && !empty($sXML->record->field))
		if (!empty($sXML->record->field))
		{
			foreach ($sXML->record->field as $field) {
			   switch ($field[@type])
			   {
					case '001':  // control Number
			   			$search_results['controlKey'] = (string)trim($field);
			   			
			   			//also save this as OCLC w/o the letters
			   			$search_results['OCLC'] = ereg_replace('oc[mn]', '', (string)trim($field));		//strip off 'ocm or ocn' if it exists
			   			$search_results['OCLC'] = ereg_replace('DOBI','o', $search_results['OCLC']);	//older DOBI prefix
			   			$search_results['OCLC'] = ereg_replace('o', '', $search_results['OCLC']);		//failing that, strip off 'o'
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
			   		
			   		case '926':
			   			$tmpResult = array();
			   			foreach ($field->subfield as $subfield)
			   			{
			   				switch ($subfield['type'])
			   				{
			   					case 'a':
			   						$tmpResult['loc'] = (string)$subfield;
			   					break;
			   					case 'b':
			   						$tmpResult['status'] = (string)$subfield;
			   					break;			   						
			   					case 'c':
			   						$tmpResult['callNum'] = (string)$subfield;
			   					break;			   			
			   					case 'd':
			   						$tmpResult['type'] = (string)$subfield;
			   					break;			   					   							   					
			   				}
			   			}
			   			$search_results['holdings'][] = $tmpResult;
			   			unset($tmpResult);
			   		break;
				}
			}
		}
		return $search_results;
	}    
}
