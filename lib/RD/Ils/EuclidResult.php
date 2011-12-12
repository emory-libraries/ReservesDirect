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
    //echo "<hr>" . utf8_decode($string) . "<br>";
    
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
	  
          case '035':  // control Number location for ALEPH marc record
            foreach ($datafield->subfield as $subfield) {
	      if (preg_match('/^oc[mn]/', (string)trim($subfield))) {
		$search_results['controlKey'] = (string)trim($subfield);
	      }
	      break;	      
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
                case 'm': switch ($subfield[0])
			    {
			      case "Robert W. Woodruff Library": $tmpResult['library'] = 'GENERAL'; break;
			      case "Chemistry Library": $tmpResult['library'] = 'CHEMISTRY'; break;
			      case "Goizueta Business Library": $tmpResult['library'] = 'BUS'; break;
			      case "Marian K. Heilbrun Music Media": $tmpResult['library'] = 'MUSICMEDIA'; break;
			      case "Woodruff Health Sciences Cntr.": $tmpResult['library'] = 'HEALTH'; break;
			      case "Law Library": $tmpResult['library'] = 'LAW'; break;
			      case "Oxford College Library": $tmpResult['library'] = 'OXFORD'; break;
			      case "Pitts Theology Library": $tmpResult['library'] = 'THEOLOGY'; break;
			      default: $tmpResult['library'] = 'GENERAL'; break;  
			    }
			    //echo "<br>RD library: [" . $subfield[0] . " => [" . $tmpResult['library'] . "]<hr>";

                case 'k': $tmpResult['loc'] = (string)$subfield; break;                    
                case 'a': $tmpResult['callNum'] = (string)$subfield; break;              
                case 't': try {
			    $cd_types = array("Audiotape", "CD", "CD ROM", "CD Sound Recording", "CD-ROM", "Sound Recording");		  
			    $dvd_types = array("Blu-Ray Disc", "DVD", "DVD-ROM", "Videodisc");
			    $vhs_types = array("Videotape", "Video");
			    if (isset($subfield[0]) && in_array($subfield[0], $cd_types))
			      $tmpResult['type'] = 'CD';
			    elseif (isset($subfield[0]) && in_array($subfield[0], $dvd_types))
			      $tmpResult['type'] = 'DVD';
			    elseif (isset($subfield[0]) && in_array($subfield[0], $vhs_types))
			      $tmpResult['type'] = 'VHS';
			    else $tmpResult['type'] = 'BOOK';
			    
			  } catch (Exception $e) { // if error default to book
			    $tmpResult['type'] = 'BOOK';
			  }    
			  echo "<br>RD type: [" . $subfield[0] . "] => [" . $tmpResult['type'] . "]<hr>";       
              }
            }
            $search_results['holdings'][] = $tmpResult;	    
            unset($tmpResult);
          break;
        }
      }
    }
    // debug stmts
    //$arr_dump = print_r($search_results, true);
    //echo "<hr>$arr_dump<hr>";    
    return $search_results;
  }  
}
