<?php
require_once("../UnitTest.php");
require_once("secure/classes/request.class.php");

class TestRequestClass extends UnitTest {
	function setUp()
	{
		$this->loadDB('../fixtures/requests.sql');
		$this->loadDB('../fixtures/libraries.sql');
	}
	
	function tearDown()
	{
		$this->loadDB('../fixtures/truncateTables.sql');
	}
	
    function TestOfConstructor() 
    {
		$r = new request(1800);
		$this->assertEqual($r->getRequestID(), 1800, 'Loaded incorrect request');
    }

}

if (! defined('RUNNER')) 
{
	$test = &new TestRequestClass();
	$test->run(new HtmlReporter());
}
?>