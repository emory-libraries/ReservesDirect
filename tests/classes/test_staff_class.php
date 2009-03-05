<?php
require_once("../UnitTest.php");
require_once("secure/interface/staff.class.php");

class TestStaffClass extends UnitTest {
	function setUp()
	{
		$this->loadDB('../fixtures/requests.sql');
		$this->loadDB('../fixtures/libraries.sql');
		$this->loadDB('../fixtures/staff.sql');
	}
	
	function tearDown()
	{
		$this->loadDB('../fixtures/truncateTables.sql');
	}
	
    function TestOfConstructor() 
    {
		$s = new staff('nobody1');
		$this->assertEqual($s->getUserID(), 33166, "returned incorrect staff member");
    }
    
    function TestOfGetRequests()
    {
    	$s = new staff('nobody1');
    	
    	$r_list = $s->getRequests();
    	$this->assertEqual(count($r_list), 4, "Test with no parmeters returned incorrect count");
    	$sort = array($r_list[0]->getRequestID(), 
    				  $r_list[1]->getRequestID(), 
    				  $r_list[2]->getRequestID(), 
    				  $r_list[3]->getRequestID())
    	;
    	$this->assertEqual($sort, array(5776, 5777, 6247, 7130), "Test with no parmeters returned results in incorrect order");
    	
    	$r_list = $s->getRequests(1);
    	$this->assertEqual(count($r_list), 3, "Test for unit 1 returned incorrect count");
    	$sort = array($r_list[0]->getRequestID(), 
    				  $r_list[1]->getRequestID(),  
    				  $r_list[2]->getRequestID())
    	;    	
    	$this->assertEqual($sort, array(5776, 5777, 7130), "Test for unit 1 returned results in incorrect order");
    				  
    	$r_list = $s->getRequests('all', 'In Process');
    	$this->assertEqual(count($r_list), 2, "Test for all In Process returned incorrect count");
    	$sort = array($r_list[0]->getRequestID(), 
    				  $r_list[1]->getRequestID())  
    	;    	    	
    	$this->assertEqual($sort, array(6247, 7130), "Test for all In Process returned results in incorrect order");
    	
    	$r_list = $s->getRequests('all', 'all', 'class');
    	$this->assertEqual(count($r_list), 4, "Test all units all status sorted by class returned incorrect count");
    	$sort = array($r_list[0]->getRequestID(), 
    				  $r_list[1]->getRequestID(), 
    				  $r_list[2]->getRequestID(), 
    				  $r_list[3]->getRequestID())
    	;
    	$this->assertEqual($sort, array(7130, 6247, 5777, 5776), "Test all units all status sorted by class returned results in incorrect order");    	
    }

}

if (! defined('RUNNER')) 
{
	$test = &new TestStaffClass();
	$test->run(new HtmlReporter());
}
?>