<?php
require_once("../UnitTest.php");
require_once("secure/classes/library.class.php");

class TestLibraryClass extends UnitTest {
	function setUp()
	{
		$this->loadDB('../fixtures/libraries.sql');
	}
	
	function tearDown()
	{
		$this->loadDB('../fixtures/truncateTables.sql');
	}
	
    function TestOfConstructor() 
    {
    	$l = new library(1);
    	$this->assertEqual($l->getLibraryNickname(), "general", "Did not return the correct Nickname");
    }

}

if (! defined('RUNNER')) 
{
	$test = &new TestLibraryClass();
	$test->run(new HtmlReporter());
}
?>