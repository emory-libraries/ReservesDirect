<?php

/**
 * @category   RD
 * @package    RD_Ils
 * @copyright  
 * @license    
 */
require_once("lib/RD/Ils/AbstractResult.php");

class RD_Euclid_Result extends AbstractResult
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
    
    $search_results = array('title'=>'', 'author'=>'', 'edition'=>'', 'performer'=>'', 'times_pages'=>'', 'volume_title'=>'', 'source'=>'', 'source_year'=>'', 'controlKey'=>'', 'personal_owner'=>null, 'physicalCopy'=>'', 'OCLC'=>'', 'ISSN'=>'', 'ISBN'=>'', 'holdings' => array());

    // Remove any special characters
    $string = $this->getData();
    $string = preg_replace("#[\xC2-\xDF][\x80-\xBF]#","",$string);         // non-overlong 2-byte
    $string = preg_replace("#\xE0[\xA0-\xBF][\x80-\xBF]#","",$string);     // excluding overlongs
    $string = preg_replace("#[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}#","",$string);     // straight 3-byte
    $string = preg_replace("#\xED[\x80-\x9F][\x80-\xBF]#","",$string);     // excluding surrogates
    $string = preg_replace("#\xF0[\x90-\xBF][\x80-\xBF]{2}#","",$string);  // planes 1-3
    $string = preg_replace("#[\xF1-\xF3][\x80-\xBF]{3}#","",$string);      //  planes 4-15
    $string = preg_replace("#\xF4[\x80-\x8F][\x80-\xBF]{2}#","",$string);  // plane 16
    
    // Load the xml string as utf8 decoded
    $sXML = simplexml_load_string(utf8_decode($string));
    
    echo "<hr>" . utf8_decode($string) . "<br>";
    
    // ControlField Tag
    if (!empty($sXML->record->controlfield))
    {     
      foreach ($sXML->record->controlfield as $controlfield) {
         switch ($controlfield[@tag])
         {
          case '001':  // control Number
            $search_results['controlKey'] = (string)trim($controlfield);
            
            //also save this as OCLC w/o the letters
            //strip off 'ocm or ocn' if it exists            
            $search_results['OCLC'] = ereg_replace('oc[mn]', '', (string)trim($controlfield)); 
            //older DOBI prefix
            $search_results['OCLC'] = ereg_replace('DOBI','o', $search_results['OCLC']);
            //failing that, strip off 'o'
            $search_results['OCLC'] = ereg_replace('o', '', $search_results['OCLC']);   
          break;
        }
      }
    }
    // DataField Tags
    if (!empty($sXML->record->datafield))
    {      
      foreach ($sXML->record->datafield as $datafield) {        
        switch ($datafield[@tag])
        {            
        case '020': // ISBN
          foreach($datafield->subfield as $subfield) {
            if((string)$subfield['code']=='a') {  //isbn = subfield tag "a"
              preg_match("/([0-9Xx]*).*/", (string)$subfield, $matches);
              $subfield = (!empty($matches[1])) ? $matches[1] : (string)$subfield;
              $search_results['ISBN'] = $subfield;
            }
          }
        break;

        case '022': // ISSN
          foreach($datafield->subfield as $subfield) {
            if((string)$subfield['code']=='a') {  //issn = subfield tag "a"
              $search_results['ISSN'] = (string)$subfield;
            }
          }
          break;

          case '100':
          case '110':
          case '111':
            foreach ($datafield->subfield as $subfield)
              $search_results['author'] .= (string)$subfield;

          case '245': //Title
            $search_results['title'] = "";
            foreach ($datafield->subfield as $subfield)
            {
              if($search_results['title'] == "")
                $search_results['title'] = (string)$subfield;
              else
                $search_results['title'] .= " ".(string)$subfield;
            }
          break;

          case '260':
            $search_results['source'] = "";
            foreach ($datafield->subfield as $subfield)
            {
              if($search_results['source'] == "")
                $search_results['source'] = (string)$subfield;
              else
                $search_results['source'] .= " ".(string)$subfield;

              if((string)$subfield['code'] == 'c')
                $search_results['source_year'] = (string)$subfield;
            }
          break;
          
          case '999':
            $tmpResult = array();
            foreach ($datafield->subfield as $subfield)
            {
              switch ($subfield['code'])
              {
                case 'm': $tmpResult['library'] = (string)$subfield; break;
                case 'k': $tmpResult['loc'] = (string)$subfield; break;                    
                case 'a': $tmpResult['callNum'] = (string)$subfield; break;              
                case 't': $tmpResult['type'] = (string)$subfield; break;   
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
