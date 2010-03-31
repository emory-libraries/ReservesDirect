#!/usr/local/bin/php -q
<?php
  /*
  * This test is covered in test suite classes/test_pagestime.class.php
  * However, if you would like to run this test outside of the test suite.
  * Usage: php tests/scripts/test_pagestimes.php
  * There are two input files: ../../tests/fixtures/pagestimes.*
  */  
  // load the pagestimes class
  require_once 'secure/classes/pagesTime.class.php'; 

  set_include_path(get_include_path() . PATH_SEPARATOR . realpath('../..').'/');  
  require_once('secure/config.inc.php');

  //global $dsn;
  
  $test_filename = "tests/fixtures/pagestimes.dat";
  $verify_filename = "tests/fixtures/pagestimes.out";

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
        echo $m1;
        $msg = "TEST " . $count . " " . $verbool . " : ";
        $msg .= " orig=[" . $iline . "]\n";      
        echo $msg;
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
    echo "Processed pattern " . $key . "\t" . $value . " times.\n"; 
  }
  
  echo "\nRESULTS OF TEST:\n";
  echo "\t" . $passed . " tests passed.\n";
  echo "\t" . $failed . " tests failed.\n";    
?>
