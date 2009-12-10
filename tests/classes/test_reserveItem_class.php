<?php
require_once("../UnitTest.php");
require_once("secure/classes/reserveItem.class.php");

class TestReserveItemClass extends UnitTest {
	function setUp() {
	  $this->loadDB('../fixtures/requests.sql');
	  $this->loadDB('../fixtures/libraries.sql');
	}
	
	function tearDown() {
	  $this->loadDB('../fixtures/truncateTables.sql');
	}
	
    function testInitFromDB() {
      $item = new reserveItem(19762);
      $this->assertPattern("/Anthropology as cultural critique/", $item->getTitle());
      $this->assertEqual("Marcus, George E.", $item->getAuthor());
      $this->assertEqual("ocm12614452", $item->getLocalControlKey());
      $this->assertEqual("BOOK_PORTION", $item->getMaterialType());
    }

    function testSetMaterialType() {
      $item = new reserveItem(19762);
      $new_type = "JOURNAL_ARTICLE";
      $item->setMaterialType($new_type);
      $this->assertEqual($new_type, $item->getMaterialtype(),
			 "material type set correctly in reserve item; should be '$new_type', got '"
			 . $item->getMaterialType() . "'");
      $item = new reserveItem(19762);
      $this->assertEqual($new_type, $item->getMaterialtype(),
			 "material type set correctly in reserve item after db init; should be '$new_type', got '" . $item->getMaterialType() . "'");

      $item->setMaterialType("OTHER", "flower cuttings");
      $this->assertEqual("OTHER:flower cuttings", $item->getMaterialtype('full'),
			 "other material type set correctly in item; should be 'OTHER:flower cuttings', got '"
			 . $item->getMaterialType() . "'");
    }

    function testGetMaterialType() {
      $item = new reserveItem(19762);
      $item->setMaterialType("OTHER", "flower cuttings");
      $this->assertEqual("OTHER", $item->getMaterialType('base'),
			 "base material type for OTHER:... should be 'OTHER', got '" .
			 $item->getMaterialType('base') . "'");
      $this->assertEqual("flower cuttings", $item->getMaterialType('detail'), 
			 "detail material type for 'OTHER:flower cuttings' be 'flower cuttings', got '" .
			 $item->getMaterialType('detail') . "'");
      
    }

}

if (! defined('RUNNER')) {
	$test = &new TestReserveItemClass();
	$test->run(new HtmlReporter());
}
?>