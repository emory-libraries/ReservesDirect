<?php
require_once("../bootstrap.php");

require_once(SIMPLE_TEST . 'unit_tester.php');
require_once(SIMPLE_TEST . 'reporter.php');

class ManagerTest extends GroupTest {
  function ManagerTest() {
    $this->GroupTest('Controller tests');
    
	$dir = "../managers/";
	
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
  $test = &new ManagerTest();
  $test->run(new HtmlReporter());
}
?>