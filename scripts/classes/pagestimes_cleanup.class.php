<?php
// This class processes the pages_times column data in the items table.
// The processed data is used to populate the total_pages_time and 
// the used_pages_time columns in the items table.

// The Pear Roman library is needed to process the roman number conversions.
require_once 'Numbers/Roman.php';   // To install: pear install Numbers_Roman

class RdItemsPagesTimesCleanup {
	
  // DECLARATIONS
  protected $filename;      // This filename of the test data. 
  protected $skipped = 0;   // This item did not get processed because of bad data.
  protected $pgs_total = 0; // The total number of pages in the item.
  protected $pgs_used = 0;  // The total number of pages used from the item.
  protected $used_list = 0; // The total number of pages determined by list processing.
  
  // Defined regex, where pages used and/or pages_total may be extracted.
  protected $page_patterns = array(
    "p_00" => array( "regex" => "/\([\s]*([\d]+)[\s]*(of|\/)[\s]*([\d]+)[\s]*(=[\s]*[\d]+[\s]*%[\s]*)?\)[\s]*/i",
    "total" => '3', "used" => '1'), // (12 of 256) must have parenthesis, if not bound to start line and end line.
    "p_01" => array( "regex" => "/^([\d]+)[\s]*(of|\/)[\s]*([\d]+)$/i",
    "total" => '3', "used" => '1'), // 12 of 256 must e bound by start line and end line.
    "p_02" => array( "regex" => "/^([\d]+)[\s]*-[\s]*([\d]+)[\s]*(\()?(out of|of)[\s]*([\d]+)[\s]*(\))?$/i",
    "total" => '5', "range_start" => '1', "range_end" => '2'), // 1-23 of 256 OR 1-23 out of 256 
    // p_03 is now being processed as a list, handled in the fall though regex patterns processing.
    //"p_03" => array( "regex" => "/^[p\.]*?[\s]*([ivxlcdm]+)[\s]*-[\s]*([ivxlcdm]+)[\s]*[\,|\;][\s]*([\d]+)[\s]*[-|\:][\s]*([\d]+)[\s]*$/i", 
    //"roman_start" => '1', "roman_end" => '2', "range_start" => '3', "range_end" => '4'), // xv-xlvi; 1-49
    "p_03" => array( "regex" => "/^([\d]+)[\s]*p[a]?g[e]?[s]?[\s]*(Total)?$/i", "used" => '1'), // 16 pages
    "p_04" => array( "regex" => "/^[p\.]*?[\s]*([\d]+)[\s]*-[\s]*([\d]+)[\s]*\(([\d]+)\)[\s]*$/", 
    "range_start" => '1', "range_end" => '2'), // pp. 10 - 13 (4)
    "p_05" => array( "regex" => "/^[pg\.\:]*(Slides)?[\s]*([\d]+)[\s]*-[\s]*([\d]+)[\.,]?$/i", 
    "range_start" => '2', "range_end" => '3'), // pp. 10 - 13
    "p_06" => array( "regex" => "/^[pg\:\.]*[\s]*([\d]+)$/", "used" => '1'), // 12 
    "p_07" => array( "regex" => "/^([\/|NULL|N\/A|selected pages|selections|various]+)$/i"), // EMPTY 
    "p_08" => array( "regex" => "/^[p\.]*[\s]*([ivxlcdm]+)[\s]*-[\s]*([ivxlcdm]+)$/i", 
    "roman_start" => '1', "roman_end" => '2'), // vii - x    
    "p_09" => array( "regex" => "/^[p\.]*[\s]*([ivxlcdm]+)$/i", "roman_used" => '1'), // xvii 
    "p_10" => array( "regex" => "/^([0]+[\s]*\/[\s]*[0]+)$/"), // 0 - 0  
  );
  
  // Here you can get a fairly human readable description of the regex that was applied to the data.
  protected $pattern_desc = array(
    0=>'OF (12 of 256)', 1=>'OF ^12 of 256$', 2=>'RANGE w/ TOTAL 23-25 of 546',
    3=>'PAGES 16 pages', 4=>'DASH RANGE w/ TOTAL pp. 10 - 13 (3)', 5=>'DASH RANGE pp. 10 - 13', 6=>'SINGLE 12', 
    7=>'EMPTY', 8=>'Roman_Range vii - x', 9=>'Roman_Used xvii', 10=>'EMPTY 0 / 0', 11=>'Test');
      
  // CONSTRUCTOR
  public function __construct($filename) {   
    $this->filename = $filename;    // The name of the input data file.
    $this->data_ignition();         // Ignites the processing of the data.
  }

  // Ignite this process - for testing read input file, process lines.
  protected function data_ignition() {
    $lines = file($this->filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    echo "Item\tUsed\tTotal\tTRIMMED\tORIGINAL STRING\n";
    foreach ($lines as $line_num => $orig) {
      if (isset($orig))  $this->process_data($line_num, $orig);
    }
    // Last output line to display count of items that did not get processed (because of bad data)
    echo "\n================\nRESULTS:\n";
    echo $this->skipped . " items were not processed (bad data or unique data found).\n";  
    echo ($line_num -  $this->skipped) . " items were processed using regex patterns.\n";
    echo $this->used_list . " items were processed using list detection (TO DO).\n";    
    echo "---------------------\n" . $line_num . " Total items in test.\n================\n";
  }

  protected function process_data($line_num = 0, $orig = null) {
    $this->pgs_used = 0;
    $this->pgs_total = 0;
    $data_has_been_processed = false; 
    $i = 0; // this is only used to keep track of index for human readable regex patterns.
    $s = trim($orig);    // trim spaces and parenthesis.
    
    if (strpos($s, '(') == 0) $s = trim($orig, " ()");  // strip of () if present around data.
    $i = 0;
    foreach ($this->page_patterns as $ppat)   {
      if (preg_match($ppat["regex"], $s, $matches)) {
        $data_has_been_processed = true;  // a regex match has been found.
        // Each regex pattern uses a variety of variables captured that may include the following:
        // range_start - this is the start of a page range.
        // range_end - this is the end of a page range.
        // roman_start - this is the start of a roman numeral page range.
        // roman_end - this is the end of a roman numeral page range.
        // roman_used - this is the number of pages used in a roman numeral format.
        // used - this is the number of pages used in the item.
        // total - this is the total number of pages in the item.

        // these variables are used to calculate our final data needs pages_used, and pages_total.
        // the majority (~93%) of the processing will happen using these regex patterns.
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
        elseif (isset($ppat["roman_start"]) && isset($ppat["roman_end"])) {
          $this->pgs_used = $this->find_range(
            Numbers_Roman::toNumber($matches[$ppat["roman_start"]]), 
            Numbers_Roman::toNumber($matches[$ppat["roman_end"]]));  
        }           
        elseif (isset($ppat["total"]) && isset($ppat["used"])) {
          $this->pgs_total = intval($matches[$ppat["total"]]);
          $this->pgs_used  = intval($matches[$ppat["used"]]);
          if ($this->pgs_used > $this->pgs_total) {
              $this->pgs_total = $this->pgs_used = 0;
          }
        }       
        elseif (isset($ppat["used"]))  {
           $this->pgs_used  = intval($matches[$ppat["used"]]);
        }
        break;
      }
      $i++; // this is only done to track the human readable regex pattern.
    }
    
    // None of the regex patterns fit at this point, so now attempt to process data as a list.
    // Expect to process additional 1.7% of the items as a list.
    if (!$data_has_been_processed && (strpos($s,",") ||  strpos($s,";"))) {  
      // Some example list items include (vi-xi, 19-27 OR pp109-119, 275-289, 311-323, 345-354
      // ONLY make the effort if there is a comma or semicolon in the input data.
      $this->process_list($s);
    }

    // Approximately 5.2% of the data is in an unusable format
    if (!$data_has_been_processed) {
      $this->skipped++; 
      $this->pgs_used = "SKIP";
      // This will output the lines that did not get processed - because the format was not readable.
      print "$line_num\tused[$this->pgs_used]\ttotal[$this->pgs_total]\ttrim[$s]\torig[$orig]\tpattern[ " . $this->pattern_desc[$i] . "]\n";
    }
    else {
      // Here you can print out the data that was processed noting the regex pattern that found the match.
      // print "$line_num\tused[$this->pgs_used]\ttotal[$this->pgs_total]\ttrim[$s]\torig[$orig]\tpattern[ " . $this->pattern_desc[$i] . "]\n";    
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
      $new_end += $end; 
      $range = $new_end - $start;
      
      // Even so there may be bad data, that results in a negative number.
      if ($range < 0)   {
        $range = 0;
        $this->pgs_used = "SKIP";
      }
    }
    return $range;
  }
    
  protected function process_list($list) {
    // TO DO 
  }
}
new RdItemsPagesTimesCleanup("../../tests/fixtures/data_test_pagestimes.txt");
?>
