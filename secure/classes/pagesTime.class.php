<?php
// PURPOSE: This class processes the pages_times column data in the items table.

// TESTING: To run the tests on this class run: php test_pagestimes_update.php

// I/O is dependent on the items table of reserves direct:
// Input fed into the process_data method is a value from the pages_times column in the items table.
// Output the following three values:
// $this->pgs_used will populate the new used_pages_time column.
// $this->pgs_total will populate the new total_pages_time column.
// $this->pgs_format will rewrite the field pages_times to a consistent format.

// STANDARDIZE PAGES_TIMES column data for future:
// The preferred formats for the pages_times column are as follows:
// arabic or roman individual page.  i.e. 24   i.e. viii
// arabic or roman page range.  i.e. 24-26     i.e. i-vii
// a list.  if more than one page or page range, then separate by commas.
// For music and audio a time range is necessary.  i.e. 1:20-3:33

// The Pear Roman library is needed to process the roman number conversions.
// If an invalid roman numeral is found such as iiiix, it throws a warning.
// To install: pear install Numbers_Roman; pear channel-update pear.php.net
require_once 'Numbers/Roman.php';   

class PagesTime {
  
  // DECLARATIONS
  protected $filename;     // This filename of the test data. 
  protected $skipped = 0;  // This item did not get processed because of bad data.
  public $pattern = 0;     // Identify the pattern that was used to clean up the data.
  public $pgs_total = "";  // The total number of pages in the item.
  public $pgs_used = "";   // The total number of pages used from the item.
  public $pgs_format = ""; // The new format of the data to overwrite existing pages_times data.  
        
  // CONSTRUCTOR
  public function __construct() { }
  
  protected function clear() {
    $this->pgs_used = "";
    $this->pgs_total = "";
    $this->pgs_format = "";
    $this->pattern = 0;
    $data_has_been_processed = false;     
  } 
  
  /**
  * @return array of data patterns 
  * @desc Common regex patterns used to extract data from the pagestime field. 
  * Defined regex where pages used and/or pages_total may be extracted.
  * Any defined value in the array named (total, used, range_start, range_end,
  * roman_start, roman_end, roman_used) all are indexes to $matches, the result 
  * of the preg_match method call for the regex pattern.
  */  
  function getDataPattern() {
   
    $data_patterns = array(
      0 => array( "regex" => "/^([0]+[\s]*\/[\s]*[0]+)$/"), // 0 - 0 
      1 => array( "regex" => "/^([\s-\/?0]+)$/"), // null data   
      2 => array( "regex" => "/^[p\.]*?[\s]*([\d]+)[\s]*-[\s]*([\d]+)[\s]*\([\s]*([\d]+)[\s]*(of|\/)[\s]*([\d]+)[\s]*(=[\s]*[\d]+[\s]*%[\s]*)?\)[\s]*/i",
      "total" => '5', "used" => '3'), // (12 of 256) must have parenthesis, if not bound to start line and end line.
      3 => array( "regex" => "/^([\d]+)[\s]*(of|\/)[\s]*([\d]+)$/i",
      "total" => '3', "used" => '1'), // 12 of 256 must e bound by start line and end line.
      4 => array( "regex" => "/^([\d]+)[\s]*-[\s]*([\d]+)[\s]*(\()?(out of|of)[\s]*([\d]+)[\s]*(\))?$/i",
      "total" => '5', "range_start" => '1', "range_end" => '2'), // 1-23 of 256 OR 1-23 out of 256 
      5 => array( "regex" => "/^([\d]+)[\s]*p[a]?g[e]?[s]?[\s]*(Total)?$/i", "used" => '1'), // 16 pages
      6 => array( "regex" => "/^[p\.]*?[\s]*([\d]+)[\s]*-[\s]*([\d]+)[\s]*\(([\d]+)\)[\s]*$/", 
      "range_start" => '1', "range_end" => '2'), // pp. 10 - 13 (4)
      7 => array( "regex" => "/^[pg\.\:]*(Slides)?[\s]*([\d]+)[\s]*-[\s]*([\d]+)[\.,]?$/i", 
      "range_start" => '2', "range_end" => '3'), // pp. 10 - 13
      8 => array( "regex" => "/^[pg\:\.]*[\s]*([\d]+)$/", "one" => '1'), // 12 
      9 => array( "regex" => "/^[p\.]*[\s]*([ivxlcdm]+)$/i", "one" => '1'), // xvii
      10 => array( "regex" => "/^([\/|NULL|N\/A|selected pages|selections|various|chapters]+)$/i"), // EMPTY 
      11 => array( "regex" => "/^[p\.]*[\s]*([ivxlcdm]+)[\s]*-[\s]*([ivxlcdm]+)$/i", 
      "roman_start" => '1', "roman_end" => '2'), // vii - x 
      12 => array( "regex" => "/^[p\.]*?[\s]*([\d]+)[\s]*-[\s]*([\d]+)[\s]*\/[\s]*([\d]+)[\s]*$/", 
      "range_start" => '1', "range_end" => '2', "total" => '3'), // pp. 10 - 13 / 124  
      13 => array( "regex" => "/^([0-9][0-9]?:[0-9][0-9])[\s]*(out of|of)[\s]*([0-9][0-9]?:[0-9][0-9])[\s]*$/i",
      "time_used" => '1', "time_total" => '3'), // 12:34 of 56:78
      14 => array( "regex" => "/^([0-9][0-9]?:[0-9][0-9])$/", "time_used" => '1'), // 12:34
    );

    return($data_patterns);       
  }

  /**
  * @return value of specified parameter indexes.
  * @desc Retrieve the value in the array based on the parameter indexes.
  */  
  function getListPattern($idx1='arabic_of', $idx2='regex') {
    
    $lpat = array(  // LIST PATTERNS
      // arabic_rtotal allows for the (total) to be at the end of a range pattern - the total info is discarded.
      'arabic_of' => array( "regex" => "/^[pg\.\-]*[\s]*([\d]+)[\s]*-[\s]*([\d]+)[\s]*\([\s]*([\d]+)[outf\s]+([\d]+)[\s]*\)\.?$/i", 
      'range_start' => '1', 'range_end' => '2', 'used' => '3', 'total' => '4'), // pp. 10 - 13 (12 of 123)      
      // arabic_rtotal allows for the (total) to be at the end of a range pattern - the total info is discarded.
      'arabic_rtotal' => array( "regex" => "/^[pg\.\-]*[\s]*([\d]+)[\s]*-[\s]*([\d]+)[\s]*\([\d]+\)\.?$/i", 
      'range_start' => '1', 'range_end' => '2'), // pp. 10 - 13   
      // arabic_range is any numeric range
      'arabic_range' => array( "regex" => "/^[pg\.\-]*[\s]*([\d]+)[\s]*-[\s]*([\d]+)p?\.?/i", 
      'range_start' => '1', 'range_end' => '2'), // pp. 10 - 13
      // arabic_ptotal allows for the (total) to be at the end of arabic page pattern - the total info is discarded.
      'arabic_ptotal' => array( "regex" => "/^[pg\.\-]*[\s]*([\d]+)[\s]*\([\d]+\)\.?$/i", 'arabic_used' => '1'), // 12 
      // arabic_page is any arabic page number
      'arabic_one' => array( "regex" => "/^[pg\.\-#]*[\s]*([\d]+)p?\.?$/", 'arabic_one' => '1'), // 12 
      // arabic_page is any arabic page number
      'arabic_ototal' => array( "regex" => "/^[pg\.\-#]*[\s]*([\d]+)p?[\s]*\([\s]*([\d]+)[outf\s]+([\d]+)[\s]*\)\.?$/",        
      'arabic_one' => '1', 'arabic_used' => '2', 'total' => '3'), // 12 (12 of 100)     
      // roman_range is any roman numeral range, this will result in adding 1 to the used page count.
      'roman_range' => array( "regex" => "/^[pg\.]*[\s]*([ivxlcdm]+)\.?[\s]*-[\s]*([ivxlcdm]+)\.?/i",  
      'roman_start' => '1', 'roman_end' => '2'), // vii - x    
      // roman_range is any roman numeral page number, this will result in adding 1 to the used page count.
      'roman_page' => array( "regex" => "/^[p\.]*[\s]*([ivxlcdm]+)\.?$/i", 'roman_used' => '1'), // xvii 
    ); 
    return($lpat[$idx1][$idx2]);    
  }

  /**
  * @return true/false upon successful completion.
  * @desc Process an entry from the db items table pagestime field.
  */  
  public function process_data($orig = null) {
    $this->clear();    
    $ppat_idx = 0;    // this is only used to keep track of index for human readable regex patterns.
    $s = trim($orig); // trim spaces and parenthesis.
    
    if (strpos($s, '(') == 0) $s = trim($orig, " ()");  // strip of () if present around data.
    
    // COMMON PATTERN PROCESSING
    foreach ($this->getDataPattern() as $ppat)   {    
      if (preg_match($ppat["regex"], $s, $matches)) {
        $data_has_been_processed = true;  // a regex match has been found.
        // Each regex pattern uses a variety of variables captured that may include the following:
        // range_start - this is the start of a page range.
        // range_end - this is the end of a page range.
        // roman_start - this is the start of a roman numeral page range.
        // roman_end - this is the end of a roman numeral page range.
        // one - this is the number of pages used in an arabic or roman numeral format.
        // used - this is the number of pages used in the item.
        // total - this is the total number of pages in the item.

        // these variables are used to calculate our final data needs of pages_used, and pages_total.
        if (isset($ppat["total"]) && isset($ppat["range_start"]) && isset($ppat["range_end"])) {
          $this->pgs_total = intval($matches[$ppat["total"]]);
          $this->pgs_used = intval($this->find_range($matches[$ppat["range_start"]], $matches[$ppat["range_end"]]));
        }         
        elseif (isset($ppat["range_start"]) && isset($ppat["range_end"])) {
          $this->pgs_used = intval($this->find_range($matches[$ppat["range_start"]], $matches[$ppat["range_end"]]));
        }
        elseif (isset($ppat["roman_used"]))  {
          $this->pgs_used = intval(Numbers_Roman::toNumber($matches[$ppat["roman_used"]]));
        }
        elseif (isset($ppat["roman_start"]) && isset($ppat["roman_end"])) { // ii - v
          $this->pgs_used = $this->find_range(
            Numbers_Roman::toNumber($matches[$ppat["roman_start"]]), 
            Numbers_Roman::toNumber($matches[$ppat["roman_end"]]));  
        }           
        elseif (isset($ppat["total"]) && isset($ppat["used"])) {  // 12 of 231
          
          $this->pgs_total = intval($matches[$ppat["total"]]);
          $this->pgs_used  = intval($matches[$ppat["used"]]);
          if ($this->pgs_used > $this->pgs_total) {
              $this->pgs_total = $this->pgs_used = 0;
          }        
        }       
        elseif (isset($ppat["used"]))  { // i.e. 12 pages
           $this->pgs_used  = intval($matches[$ppat["used"]]);
        }
        elseif (isset($ppat["one"]))  { // If a single page is found, this equates to one page used.
           $this->pgs_used  = 1;
        }        
        elseif (isset($ppat["time_used"]) && isset($ppat["time_total"])) { // 12:34 of 56:78
          $this->pgs_total = $matches[$ppat["time_total"]];
          $this->pgs_used  = $matches[$ppat["time_used"]];
        } 
        elseif (isset($ppat["time_used"])) { // 12:34
          $this->pgs_used  = $matches[$ppat["time_used"]];
        } 
        $this->pattern = $ppat_idx;       
        
        break;
      }
      $ppat_idx++;  // move on to the next pattern
    }
    
    // LIST PROCESSING
    // None of the regex patterns fit at this point, so now attempt to process data as a list.
    // A list must contain a comma or semicolon in the input data.    
    if (!$data_has_been_processed && ((strpos($s,",") > 0) || (strpos($s,";") > 0))) {
      $data_has_been_processed = true;
      $this->process_list($s);
    }
   
    // some data will just not fit in any of the common patterns, so we will SKIP them
    if (!$data_has_been_processed) {     
      $this->skipped++;       
      $this->pgs_format = trim($orig);  // failed to cleanse data, restore the pages-times field
      $this->pgs_used = "";
    }
    
    // cleanse the pages_times field data. result will be put back into the pages_times field.
    // summary: only keep the range information, because the used and total pgs will be stored in new db fields.
    switch ($this->pattern) 
    {
      case 2:  $this->pgs_format = $matches[1] . "-" . $matches[2];  break; 
      case 4:  // range start - range end    
      case 6:  // range start - range end 
      case 7:  // range start - range end       
      case 12: $this->pgs_format = $matches[$ppat["range_start"]] . "-" . $matches[$ppat["range_end"]];  break;
      case 8:  // this is a single arabic page number, but trash the waste. (i.e. pp.) 
      case 9:  $this->pgs_format = $matches[$ppat["one"]]; break; 
      case 11: $this->pgs_format = $matches[$ppat["roman_start"]] . "-" . $matches[$ppat["roman_end"]];  break;
      case 13: // time used
      case 14: $this->pgs_format = "";  break; 
      case 0:  // did not contain page or range of pages used.          
      case 1:  // did not contain page or range of pages used.        
      case 3:  // did not contain page or range of pages used.
      case 5:  // did not contain page or range of pages used.  
      case 10: // get rid of ambiguous values such as NULL, selections, various, etc. 
      case 98: break;   // this is a list that processed correctly 
      case 99: $this->pgs_format = trim($orig); break;   // this is a list that failed to process.      
      default: $this->pgs_format = "ERROR";  break;       
    }    
  } 
  
  // find_range will evaluate a start and end value to calculate pages used.
  protected function find_range($start, $end) {
    if ($end >= $start)  $range = $end - $start + 1;   // Inclusive, start page is less than the end page.     
    else {  // If the end page is less than the start page, then a partial number may have been used.
      // Some example partial items include (100-1 or 1245-399 or 34-9 or 321-92 or 18932-8
      $new_end = $start;
      for ($i=0; $i<strlen($end); $i++) {
        $zero = $zero . "0";    // create our replacement string of zeros.
      }
      $pos = strlen($start) - $i;
      $new_end = substr_replace($new_end, $zero, $pos);   
      $new_end += $end; // change the partial end value to a full value. ie. 321-92 would convert to 321-392
      $range = $new_end - $start;
      
      // Even so there may be bad data, that results in a negative number.
      if ($range < 0)   {
        $range = 0;
        $data_has_been_processed = false;        
      }
    }
    return $range;
  }
 
  /**
  * @return void
  * @desc Process list or pagestime range.
  */  
  function process_list($list) {
    // This function is used to process any remaining data that will fit a list pattern.
    // A list pattern is data that contains commas or semicolons.
    $ret = true;
    $this->pgs_used = 0; 
    $this->pattern = 99;   // 99 is a failed list pattern. If it passes pattern changes to 98.
    
    $list_arr = split('[,;]', trim($list));
    foreach ($list_arr as $li) {
      if (preg_match($this->getListPattern('arabic_of'), trim($li), $matches)) {  
        $part = intval($this->find_range(
          $matches[$this->getListPattern('arabic_of','range_start')], 
          $matches[$this->getListPattern('arabic_of','range_end')]));
        $this->pgs_format .= $matches[$this->getListPattern('arabic_of','range_start')] 
          . "-" . $matches[$this->getListPattern('arabic_of','range_end')];
        $this->pgs_total = $matches[$this->getListPattern('arabic_of','total')];        
      }      
      elseif (preg_match($this->getListPattern('arabic_range'), trim($li), $matches)) {  
        $part = intval($this->find_range(
          $matches[$this->getListPattern('arabic_range','range_start')], 
          $matches[$this->getListPattern('arabic_range','range_end')]));
        $this->pgs_format .= $matches[$this->getListPattern('arabic_range','range_start')] 
          . "-" . $matches[$this->getListPattern('arabic_range','range_end')];
      }
      elseif (preg_match($this->getListPattern('arabic_rtotal'), trim($li), $matches)) {  
       $part = intval($this->find_range(
        $matches[$this->getListPattern('arabic_rtotal','range_start')], 
        $matches[$this->getListPattern('arabic_rtotal','range_end')]));
       if ($data_has_been_processed == false)   $ret = false;   // there could be bad data      
       $this->pgs_format .= $matches[$this->getListPattern('arabic_rtotal','range_start')] 
        . "-" . $matches[$this->getListPattern('arabic_rtotal','range_end')];       
      }
      elseif (preg_match($this->getListPattern('arabic_ototal'), trim($li), $matches)) {       
        $part = 1;  // this is one page number, not a range        
        $this->pgs_format .= $matches[$this->getListPattern('arabic_ototal','arabic_one')];  
        $this->pgs_total = $matches[$this->getListPattern('arabic_ototal','total')];        
      }
      elseif (preg_match($this->getListPattern('arabic_one'), trim($li), $matches)) {        
        $part = 1;  // this is one page number, not a range        
        $this->pgs_format .= $matches[$this->getListPattern('arabic_one','arabic_one')];        
      }
      elseif (preg_match($this->getListPattern('arabic_ptotal'), trim($li), $matches)) {        
        $part = 1;  // this is one page number, not a range       
        $this->pgs_format.= $matches[$this->getListPattern('arabic_ptotal','arabic_used')];        
      } 
      elseif (preg_match($this->getListPattern('roman_range'), trim($li), $matches)) {       
        $part = intval($this->find_range(
            Numbers_Roman::toNumber($matches[$this->getListPattern('roman_range','roman_start')]), 
            Numbers_Roman::toNumber($matches[$this->getListPattern('roman_range','roman_end')]))); 
        if ($data_has_been_processed == false)   $ret = false;   // there could be bad data  
        $this->pgs_format .= $matches[$this->getListPattern('roman_range','roman_start')] 
          . "-" . $matches[$this->getListPattern('roman_range','roman_end')];
      }        
      elseif (preg_match($this->getListPattern('roman_page'), trim($li), $matches))  {        
        $part = 1;  // this is one page number, not a range        
        $this->pgs_format .= $matches[$this->getListPattern('roman_page','roman_used')];          
      }
      elseif (preg_match("/^pp$/", trim($li), $matches)) {  }   // This is discarded data.
      else {   
        $ret = false; // failed to process this data.
      }
      if (!$ret) { // bad data found, clear it out of the newly formatted string.  
        $data_has_been_processed = false;  
        $this->pgs_used = ""; 
        return $ret;
      }
      $this->pgs_format = $this->pgs_format . ", ";
      $this->pgs_used += $part;
    }
    $this->pgs_format = substr($this->pgs_format, 0, -2);   // remove the trailing comma space.
    $this->pattern = 98;  // Pattern 98 is a correctly processed list pattern.    
    return true;
    
  }
}
?>
