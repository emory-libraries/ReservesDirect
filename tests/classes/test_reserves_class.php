<?php
require_once('../UnitTest.php');
require_once('secure/classes/reserves.class.php');


class TestReservesClass extends UnitTest {

  function setUp() {
    $this->loadDB('../fixtures/staff.sql');    
    $this->loadDB('../fixtures/requests.sql'); 
    $this->loadDB('../fixtures/copyright.sql');   
  }

  function tearDown() {
    $this->loadDB('../fixtures/truncateTables.sql');
  }
  
  function testCopyrightReviewReserves() {
    $reserves = reserve::getCopyrightReviewReserves();
    $this->assertEqual(count($reserves), 4);
    $reserves = reserve::getCopyrightReviewReserves(0,2);
    $this->assertEqual(count($reserves), 2);    
  }
  
  function testCopyrightStatusAcceptedIsRemovedFromQueue() {
    $reserves = reserve::getCopyrightReviewReserves();
    $this->assertEqual(count($reserves), 4);
    $reserves = new Reserve(202864);
    $this->assertEqual($reserves->getCopyrightStatus(), 'NEW', "The copyright_status is NEW"); 
    // Set the copyright status to accepted, then get it to make sure it was set properly
    $status = "ACCEPTED";
    $reserves->setCopyrightStatus($status);
    $reserves = reserve::getCopyrightReviewReserves();
    $this->assertEqual(count($reserves), 3);    
  }
  
  function testCopyrightStatusDeniedIsRemovedFromQueue() {
    $reserves = reserve::getCopyrightReviewReserves();
    $this->assertEqual(count($reserves), 4);
    $reserves = new Reserve(202864);
    $this->assertEqual($reserves->getCopyrightStatus(), 'NEW', "The copyright_status is NEW"); 
    // Set the copyright status to denied, then get it to make sure it was set properly
    $status = "DENIED";
    $reserves->setCopyrightStatus($status);
    $reserves = reserve::getCopyrightReviewReserves();
    $this->assertEqual(count($reserves), 3);    
  }  
  
  function testGetCopyrightStatus() {
    $reserves = new Reserve(412602);
    $this->assertEqual($reserves->getCopyrightStatus(), 'NEW', "The copyright_status is NEW");
    $reserves = new Reserve(405012);
    $this->assertEqual($reserves->getCopyrightStatus(), 'DENIED', "The copyright_status is DENIED"); 
    $reserves = new Reserve(411008);
    $this->assertEqual($reserves->getCopyrightStatus(), 'PENDING', "The copyright_status is PENDING");
    $reserves = new Reserve(407981);
    $this->assertEqual($reserves->getCopyrightStatus(), 'ACCEPTED', "The copyright_status is ACCEPTED");            
  }
    
  function testSetCopyrightStatus() {
    $reserves = new Reserve(412602);
    $this->assertEqual($reserves->getCopyrightStatus(), "NEW",
     "copyright status for new reserve should default to [NEW]");    
    $reserves = new Reserve(412602);
    // Set the copyright status to accepted, then get it to make sure it was set properly
    $status = "ACCEPTED";
    $reserves->setCopyrightStatus($status);
    $this->assertEqual($status, $reserves->getCopyrightStatus(),
     "copyright status set correctly in reserve; should be [NEW], got ["
     . $reserves->getCopyrightStatus() . "]");
  }   
    
  function testGetCopyrightStatusDisplay() {
    $u = new staff('nobody1'); 
    $reserves = new Reserve(412602);
    $expectedOutput = '<tr id="copyright_status"><th>Copyright Status:</th><td><select id="copyrightstatus" name="copyright_status" ><option value="NEW" selected="selected">NEW</option><option value="PENDING">PENDING</option><option value="ACCEPTED">ACCEPTED</option><option value="DENIED">DENIED</option></select></td></tr>';
    $display = $reserves->getCopyrightStatusDisplay($u, 20);
    $this->assertEqual($display, $expectedOutput, "Copyright status is NEW");
    
    $reserves = new Reserve(405012);
    $expectedOutput = '<tr id="copyright_status"><th>Copyright Status:</th><td><select id="copyrightstatus" name="copyright_status" ><option value="NEW">NEW</option><option value="PENDING">PENDING</option><option value="ACCEPTED">ACCEPTED</option><option value="DENIED" selected="selected">DENIED</option></select></td></tr>';
    $display = $reserves->getCopyrightStatusDisplay($u, 20);
    $this->assertEqual($display, $expectedOutput, "Copyright status is DENIED");
    
    $reserves = new Reserve(411008);
    $expectedOutput = '<tr id="copyright_status"><th>Copyright Status:</th><td><select id="copyrightstatus" name="copyright_status" ><option value="NEW">NEW</option><option value="PENDING" selected="selected">PENDING</option><option value="ACCEPTED">ACCEPTED</option><option value="DENIED">DENIED</option></select></td></tr>';
    $display = $reserves->getCopyrightStatusDisplay($u, 20);
    $this->assertEqual($display, $expectedOutput, "Copyright status is PENDING");
    
    $reserves = new Reserve(407981);
    $expectedOutput = '<tr id="copyright_status"><th>Copyright Status:</th><td><select id="copyrightstatus" name="copyright_status" ><option value="NEW">NEW</option><option value="PENDING">PENDING</option><option value="ACCEPTED" selected="selected">ACCEPTED</option><option value="DENIED">DENIED</option></select></td></tr>';
    $display = $reserves->getCopyrightStatusDisplay($u, 20);
    $this->assertEqual($display, $expectedOutput, "Copyright status is ACCEPTED");        
  } 
  
  function testCreateNewReserve() {
    $r = new reserve();
    $reserves = reserve::getReservesForCourse(57900);   // Get reserves count for course instance BEFORE adding a new reserve.
    $this->assertEqual(count($reserves), 16);    
    $reserves = $r->createNewReserve(57900, 142753);    // Create a new reserve
    $reserves = reserve::getReservesForCourse(57900);   // Get reserves count for course instance AFTER adding a new reserve.
    $this->assertEqual(count($reserves), 17); 
    $reserves = new Reserve(419684);                    // Get the newly created course    
    $this->assertEqual($reserves->getCopyrightStatus(), 'NEW',  // Check to see the default copyright status is NEW.
     "copyright status default should be [NEW], got [" . $reserves->getCopyrightStatus() . "]");
  }
  
  function testSetParent() {
    $reserve = new reserve(371613);
    $parent = 142752;
    $this->assertNull($reserve->getParent(), "before setting parent, parent is NULL");    
    $reserve->setParent($parent);
    $this->assertEqual($parent, $reserve->getParent(),
     "parent set correctly in reserve; should be [$parent], got [" . $reserve->getParent() . "]");
  }
  
  function testSetActivationDate() {
    $reserve = new reserve(371613);
    $adate = '2010-05-12';
    $this->assertEqual($reserve->getActivationDate(), '2009-12-01', "before setting ActivationDate should be [$adate], got [" . $reserve->getActivationDate() . "]"); 
    $reserve->setActivationDate($adate);
    $this->assertEqual($adate, $reserve->getActivationDate(),
     "ActivationDate set correctly in reserve; should be [$adate], got [" . $reserve->getActivationDate() . "]");
  }
  
  function testSetExpirationDate() {
    $reserve = new reserve(371613);
    $edate = '2010-05-12';
    $this->assertEqual($reserve->getExpirationDate(), '2010-05-16', "before setting ExpirationDate should be [$edate], got [" . $reserve->getExpirationDate() . "]"); 
    $reserve->setExpirationDate($edate);
    $this->assertEqual($edate, $reserve->getExpirationDate(),
     "ExpirationDate set correctly in reserve; should be [$edate], got [" . $reserve->getExpirationDate() . "]");
  }
  
  function testSetStatus() {
    $reserve = new reserve(371613);
    $status = 'DENIED';
    $this->assertEqual($reserve->getStatus(), 'ACTIVE', "before setting status, should be [ACTIVE], got [" . $reserve->getStatus() . "]");
    $reserve->setStatus($status);
    $this->assertEqual($reserve->getStatus(), $status,
     "status set correctly in reserve; should be [$status], got [" . $reserve->getStatus() . "]");
  }
  
  function testSetRequestedLoanPeriod() {
    $reserve = new reserve(371613);
    $loadperiod = '2 Hours';
    $this->assertEqual($reserve->getRequestedLoanPeriod(), "", "before setting RequestedLoanPeriod should be empty"); 
    $reserve->setRequestedLoanPeriod($loadperiod);
    $this->assertEqual($loadperiod, $reserve->getRequestedLoanPeriod(),
     "RequestedLoanPeriod set correctly in reserve; should be [$loadperiod], got [" . $reserve->getRequestedLoanPeriod() . "]");
  }  
}

if (! defined('RUNNER')) {
  $test = &new TestReservesClass();
  $test->run(new HtmlReporter());
}
