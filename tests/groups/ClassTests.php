<?php
require_once("../bootstrap.inc.php");

require_once("../" . SIMPLE_TEST . "unit_tester.php");
require_once("../" . SIMPLE_TEST . "reporter.php");

class ClassGroupTest extends GroupTest {
  function ClassGroupTest() {
    $this->GroupTest('Class tests');
    
	$dir = "../classes/";
	
	// Open a known directory, and proceed to read its contents
	if (is_dir($dir)) {
	    if ($dh = opendir($dir)) {
	        while (($file = readdir($dh)) !== false) {
		  if (filetype($dir . $file) == 'file' && preg_match("/.php$/", $file)){
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
  $test = &new ClassGroupTest();
  $test->run(new HtmlReporter());
}
?>