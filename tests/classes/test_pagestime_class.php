<?php
require_once("../UnitTest.php");
require_once("secure/classes/pagesTime.class.php");

class TestPagesTime extends UnitTest {
  function setUp() {
  }
  
  function tearDown() {
  }
  
  function testRegexPatterns() { 
    
    $succeed_data_patterns = array( 
      array("name" => '0', "testdata" => '0 / 0'),
      array("name" => '1', "testdata" => '0 - 0'),   
      array("name" => '2', "testdata" => '1-11 (12 of 256)'),   
      array("name" => '3', "testdata" => '12 of 256'),
      array("name" => '4', "testdata" => '1-23 out of 256'),
      array("name" => '5', "testdata" => '16 pages'),   
      array("name" => '6', "testdata" => 'pp. 10 - 13 (4)'),   
      array("name" => '7', "testdata" => 'pp. 10 - 13'), 
      array("name" => '8', "testdata" => '12'), 
      array("name" => '9', "testdata" => 'xvii'), 
      array("name" => '10', "testdata" => 'selected pages'), 
      array("name" => '11', "testdata" => 'vii - x'), 
      array("name" => '12', "testdata" => 'pp. 10 - 13 / 124'), 
      array("name" => '13', "testdata" => '12:34 of 56:78'), 
      array("name" => '14', "testdata" => '12:34'),                                                              
    );
    
    $dp_array = pagesTime::getDataPattern();
    foreach ($succeed_data_patterns as $pset)   {    
      $regex_pattern = $dp_array[$pset["name"]]['regex']; 
      $msg = "Test data pattern [" . $pset["name"] . "] pattern exact match";
      $this->assertPattern($regex_pattern, $pset["testdata"], $msg);
    }
    
    $succeed_list_patterns = array(
      // arabic_rtotal allows for the (total) to be at the end of a range pattern.
      array("name" => 'arabic_of', "testdata" => 'pp. 10 - 13 (12 of 123)'),
      // arabic_rtotal allows for the (total) to be at the end of a range pattern.
      array("name" => 'arabic_rtotal', "testdata" => 'pp. 10 - 13 (23)'),
      // arabic_range is any numeric range    
      array("name" => 'arabic_range', "testdata" => 'pp. 10 - 13'),
      // arabic_ptotal allows for the (total) to be at the end of roman page pattern.    
      array("name" => 'arabic_ptotal', "testdata" => '12 (20)'),
      // arabic_page is any arabic page number
      array("name" => 'arabic_one', "testdata" => '12'),
      // arabic_page is any arabic page number with used of total in parens.
      array("name" => 'arabic_ototal', "testdata" => '12 (12 of 100)'),
      // roman_range is any roman numeral range    
      array("name" => 'roman_range', "testdata" => 'vii - x'),
      // roman_range is any roman numeral page number    
      array("name" => 'roman_page', "testdata" => 'xvii'),                    
    );
    
    foreach ($succeed_list_patterns as $pset)   {    
      $regex_pattern = pagesTime::getListPattern($pset["name"]); 
      $msg = "Test list pattern [" . $pset["name"] . "] exact match";
      $this->assertPattern($regex_pattern, $pset["testdata"], $msg);
    }    
  }
  function testExpansion() { 
    $test_filename = realpath(dirname(__FILE__)) . "/../fixtures/pagestimes.dat";
    $verify_filename = realpath(dirname(__FILE__)) . "/../fixtures/pagestimes.out";
    
    $cleanup = new PagesTime();    

    $count = 1;
    $passed = 0;
    $failed = 0;
    $arr = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0, 7=>0, 8=>0, 9=>0, 10=>0, 11=>0, 12=>0, 50=>0, 98=>0, 99=>0);

    $test = fopen($test_filename, 'r') or exit("Unable to open test file: $test_filename\n");  
    $verify = fopen($verify_filename, 'r') or exit("Unable to open verify file:  $verify_filename\n"); 
    //Output a line of the file until the end is reached
    while(!feof($test))
    {
      if ($iline =trim(fgets($test))) {
        $vline =trim(fgets($verify));
        $comma1 = strpos($vline, ",");                // the position of the first comma
        $vtotal = substr($vline, 0, $comma1);         // the first item is the total pages
        $comma2 = strpos($vline, ",", $comma1+1);     // the position of the second comma
        $vused = substr($vline, $comma1+2, $comma2-$comma1-2);// the second item is pages used
        $vpage = substr($vline, $comma2+2);           // the remainder of the string is pages_times
        // verify results, then pass or fail test.
        $cleanup->process_data($iline);

        if ($vtotal == $cleanup->pgs_total && $vused == $cleanup->pgs_used && $vpage == $cleanup->pgs_format) {
          $passed++;
          $verbool = "PASSED";
          $pat_arr[$cleanup->pattern]++;
        }
        else {
          $failed++;
          $verbool = "FAILED";
          // ONLY PRINT OUTPUT IF THE TEST FAILS
          $m1 = "\nVerify => c1=" . $comma1 . " c2=" . $comma2 . " tot[" . $vtotal . "] use[" . $vused . "] pag[" . $vpage . "]  ";
          $m1 .= "pattern=[" . $cleanup->pattern . "]\t total=[" . $cleanup->pgs_total . "]\tpgs_used=[" . $cleanup->pgs_used . "]\tpages_times=[" . $cleanup->pgs_format . "]\n";
          $msg = "TEST " . $count . " " . $verbool . " : ";
          $msg .= " orig=[" . $iline . "]\n";      
          $this->assert("ERROR: $m1 $msg");
          $pat_arr[50]++; 
        }
        $count++;;      
      }
    }
    fclose($test);
    fclose($verify);  
    
    // OUTPUT RESULTS
    echo "\n";
    foreach ($pat_arr as  $key=>$value) { 
      $msg = "Processed expansion $key pattern $value times"; 
      switch ($key) 
      {
        case 0:  $this->assertEqual($value, 136, $msg);  break;
        case 1:  $this->assertEqual($value, 9, $msg);  break;
        case 2:  $this->assertEqual($value, 15, $msg);  break;
        case 3:  $this->assertEqual($value, 9, $msg);  break;
        case 4:  $this->assertEqual($value, 2, $msg);  break;
        case 5:  $this->assertEqual($value, 13, $msg);  break;
        case 6:  $this->assertEqual($value, 2, $msg);  break;
        case 7:  $this->assertEqual($value, 23, $msg);  break;
        case 8:  $this->assertEqual($value, 10, $msg);  break;
        case 9:  $this->assertEqual($value, 3, $msg);  break;
        case 10: $this->assertEqual($value, 7, $msg);  break;
        case 11: $this->assertEqual($value, 7, $msg);  break;
        case 12: $this->assertEqual($value, 2, $msg);  break;
        case 13: $this->assertEqual($value, 8, $msg);  break;
        case 14: $this->assertEqual($value, 10, $msg);  break;
        case 98: $this->assertEqual($value, 67, $msg);  break;
        case 99: $this->assertEqual($value, 40, $msg);  break;                                                                                                                        
        default: $this->assert("ERROR: pattern $key is not defined.");  break;       
      }    
    }    
  }  
} 

if (! defined('RUNNER')) {
  $test = &new TestpagesTime();
  $test->run(new HtmlReporter());
}
?>
