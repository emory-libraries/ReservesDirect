<?php
require_once("../UnitTest.php");
require_once("secure/classes/courseInstance.class.php");

class TestCourseInstanceClass extends UnitTest {
  
  function setUp()
  {
    $this->loadDB('../fixtures/copyright.sql');
    $this->ci = new courseInstance(57900); 
    $this->dept_id = 129;
    $this->course_number = 129;
    $this->course_name = "Reactivated Course";
    $this->section = "003";
    $this->year = 2010;
    $this->term = "SUMMER";  
  }
  
  function tearDown()
  {
    $this->loadDB('../fixtures/truncateTables.sql');
  }
  
  function TestOfConstructor() 
  {
    $this->assertEqual($this->ci->getCourseInstanceID(), 57900, 'Loaded incorrect course instance');
  }
  
  function TestCopyReserves_EntireCourse() 
  {
    // Copy the entire course
    $new_ci = 60905;
    $dst_ci = new courseInstance($new_ci);
    $dst_ci->createCourseInstance($this->dept_id, $this->course_number, $this->course_name, $this->section, $this->year, $this->term);
    $reserves = reserve::getReservesForCourse($new_ci); 
    $this->assertEqual(count($reserves), 0, "Before copy a newly created course has no reserves."); 
    $this->ci->copyReserves($new_ci);             // copy all 16 items in 57900 to new course 60905
    $reserves = reserve::getReservesForCourse($new_ci); 
    $this->assertEqual(count($reserves), 16, "Imported entire course reserves into a new course.");   
    foreach ($reserves as $reserve_id)   { 
      $r = new Reserve($reserve_id);
      $this->assertEqual($r->getCopyrightStatus(), 'NEW', "Reserve $reserve_id should be [NEW] when reactivating an entire course."); 
    }
  }
  
  function TestCopyReserves_SelectedReserves() 
  {
    // Copy two reserves from source course to destination course.
    $new_ci = 60906;
    $dst_ci = new courseInstance($new_ci);
    $dst_ci->createCourseInstance($this->dept_id, $this->course_number, $this->course_name, $this->section, $this->year, $this->term);
    $reserves = reserve::getReservesForCourse($new_ci); 
    $this->assertEqual(count($reserves), 0, "Before copy a newly created course has no reserves.");    
    $this->ci->copyReserves($new_ci, array(419683,371613));  // copy defined array of reserves.
    $reserves = reserve::getReservesForCourse($new_ci); 
    $this->assertEqual(count($reserves), 2, "Copied two reserves items into a new course.");    // newly created course has no reserves. 
    
    foreach ($reserves as $reserve_id)   { 
      $r = new Reserve($reserve_id);
      $this->assertEqual($r->getCopyrightStatus(), 'NEW', "Reserve $reserve_id should be [NEW] when reactivating selected reserves."); 
    }    
  }
  
  function testSetActivationDate() {
    $ci = new courseInstance(60904);
    $adate = '2010-05-12';
    $this->assertEqual($ci->getActivationDate(), '2009-12-01', "before setting ActivationDate should be [2009-12-01], got [" . $ci->getActivationDate() . "]"); 
    $ci->setActivationDate($adate,5);
    $this->assertEqual($adate, $ci->getActivationDate(),
     "ActivationDate set correctly in reserve; should be [$adate], got [" . $ci->getActivationDate() . "]");
  }
  
  function testSetExpirationDate() {
    $ci = new courseInstance(60904);
    $edate = '2010-05-12';
    $this->assertEqual($ci->getExpirationDate(), '2010-05-16', "before setting ExpirationDate should be [2010-05-16], got [" . $ci->getExpirationDate() . "]"); 
    $ci->setExpirationDate($edate,5);
    $this->assertEqual($edate, $ci->getExpirationDate(),
     "ExpirationDate set correctly in reserve; should be [$edate], got [" . $ci->getExpirationDate() . "]");
  }
  
  function testSetStatus() {
    $ci = new courseInstance(60904);
    $status = 'DENIED';
    $this->assertEqual($ci->getStatus(), 'ACTIVE', "before setting status, should be [ACTIVE], got [" . $ci->getStatus() . "]");
    $ci->setStatus($status);
    $this->assertEqual($ci->getStatus(), $status,
     "status set correctly in reserve; should be [$status], got [" . $ci->getStatus() . "]");
  }
  
  function testSetEnrollment() {
    $ci = new courseInstance(60904);
    $enrollment = 'CLOSED';
    $this->assertEqual($ci->getEnrollment(), 'OPEN', "before setting enrollment, should be [OPEN], got [" . $ci->getEnrollment() . "]");
    $ci->setEnrollment($enrollment);
    $this->assertEqual($ci->getEnrollment(), $enrollment,
     "enrollment set correctly in reserve; should be [$enrollment], got [" . $ci->getEnrollment() . "]");
  }
}

if (! defined('RUNNER')) 
{
  $test = &new TestCourseInstanceClass();
  $test->run(new HtmlReporter());
}
?>
