<?php
require_once("../bootstrap.php");

require_once(SIMPLE_TEST . 'unit_tester.php');
require_once(SIMPLE_TEST . 'reporter.php');

class UnitGroupTest extends GroupTest {
  function UnitGroupTest() {
    $this->GroupTest('Unit tests');
    
	$dir = "../units/";
	
	// Open a known directory, and proceed to read its contents
	if (is_dir($dir)) {
	    if ($dh = opendir($dir)) {
	        while (($file = readdir($dh)) !== false) {
	        	if (filetype($dir . $file) == 'file' )
	        	{
	            	$this->addTestFile("$dir$file");	            
	        	}
	        }
	        closedir($dh);
	    }
	}    
  }
}

if (! defined('RUNNER')) {
  define('RUNNER', true);
  $test = &new UnitGroupTest();
  $test->run(new HtmlReporter());
}
?>