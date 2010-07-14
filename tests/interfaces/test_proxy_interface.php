<?php
require_once("../UnitTest.php");
require_once("secure/classes/course.class.php");
require_once("secure/classes/courseInstance.class.php");
require_once("secure/interface/instructor.class.php");
require_once("secure/interface/proxy.class.php");

class TestProxyInterface extends UnitTest {
  function setUp() {  
    $this->loadDB('../fixtures/staff.sql');    
    $this->loadDB('../fixtures/requests.sql');   
  }
  
  function tearDown() {
    $this->loadDB('../fixtures/truncateTables.sql');
  }
  
  function TestProxyInterface_AddCrosslisting() 
  {
    $u = new instructor(109); 
    $ci = new courseInstance(20782);
    $dept       = 6;
    $courseNo   = '58';
    $section    = '00P';
    $courseName = 'Spec Tops: Anthropology'; 
    $ca = 19645;
    
    // Validate before the crosslisting is performed.
    $course = new course($ca);
    $override_feed = $course->getOverrideFeed();
    $this->assertEqual($override_feed, 0, "Override_feed should be 0 before addCrossListing, got $override_feed");
    $ca_ciid = $course->getCA_CIID();
    $this->assertEqual($ca_ciid, 14214, "Course_Instance_ID should be 14214 before addCrossListing, got $ca_ciid"); 

    // Add the crosslisting
    $u->addCrossListing($ci, $dept, $courseNo, $section, $courseName, $ca);
    $this->assertEqual("test 01", 'test 01', "Test 01 passed.");
    
    // Validate after the crosslisting is performed.
    $course = new course($ca);    
    $override_feed = $course->getOverrideFeed();     
    $this->assertEqual($override_feed, 2, "Override_feed should be 2 after addCrossListing, got $override_feed");
    $ca_ciid = $course->getCA_CIID();
    $this->assertEqual($ca_ciid, 20782, "Course_Instance_ID should be 20782 after addCrossListing, got $ca_ciid");       
  }  
  
}

if (! defined('RUNNER')) {
  $test = &new TestProxyInterface();
  $test->run(new HtmlReporter());
}
?>
