<?php
require_once("../UnitTest.php");
require_once("secure/classes/reserveItem.class.php");

class TestReserveItemClass extends UnitTest {
  function setUp() {
    $this->loadDB('../fixtures/requests.sql');
    $this->loadDB('../fixtures/rightsholders.sql');
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

    function testSetPublisher() {
      $item = new reserveItem(19762);
      $pub = "Oxford Press";
      $item->setPublisher($pub);
      $this->assertEqual($pub, $item->getPublisher(),
       "publisher set correctly in reserve item; should be '$pub', got '"
       . $item->getPublisher() . "'");
      $item = new reserveItem(19762);
      $this->assertEqual($pub, $item->getPublisher(),
       "publisher set correctly in reserve item after db init; should be '$pub', got '" .
       $item->getPublisher() . "'");
    }

    function testSetUsedPagesTimes() {
      $item = new reserveItem(19762);
      $pages = "300";
      $item->setUsedPagesTimes($pages);
      $this->assertEqual($pages, $item->getUsedPagesTimes(),
       "used pages/times set correctly in reserve item; should be '$pages', got '"
       . $item->getUsedPagesTimes() . "'");
      $item = new reserveItem(19762);
      $this->assertEqual($pages, $item->getUsedPagesTimes(),
       "used pages/times set correctly in reserve item after db init; should be '$pages', got '" .
       $item->getUsedPagesTimes() . "'");
    }
    
    function testSetTotalPagesTimes() {
      $item = new reserveItem(19762);
      $pages = "300";
      $item->setTotalPagesTimes($pages);
      $this->assertEqual($pages, $item->getTotalPagesTimes(),
       "total pages/times set correctly in reserve item; should be '$pages', got '"
       . $item->getTotalPagesTimes() . "'");
      $item = new reserveItem(19762);
      $this->assertEqual($pages, $item->getTotalPagesTimes(),
       "total pages/times set correctly in reserve item after db init; should be '$pages', got '" .
       $item->getTotalPagesTimes() . "'");
    }    

    function testSetAvailability() {
      $item = new reserveItem(19762);
      $item->setAvailability(0);
      $this->assertEqual(false, $item->getAvailability(),
       "availability set correctly in reserve item; should be false, got '"
       . $item->getAvailability() . "'");
      $item = new reserveItem(19762);
      $this->assertEqual(false, $item->getAvailability(),
       "availability set correctly in reserve item after db init; should be false, got '" .
       $item->getAvailability() . "'");
      $item->setAvailability(1);
      $this->assertEqual(true, $item->getAvailability(),
       "availability set correctly in reserve item; should be true, got '"
       . $item->getAvailability() . "'");
      $item = new reserveItem(19762);
      $this->assertEqual(1, $item->getAvailability(),
       "availability set correctly in reserve item after db init; should be true, got '" .
       $item->getAvailability() . "'");
    }

    function testGetRightsholder() {
      $item = new reserveItem(19762); # isbn with rightsholder info
      $rh = $item->getRightsholder();
      $this->assertEqual($rh->getName(), 'Somebody Book Publishers, inc');

      $item = new reserveItem(96261); # isbn without rightsholder info
      $rh = $item->getRightsholder();
      $this->assertNotNull($rh);
      $this->assertNull($rh->getName());

      $item = new reserveItem(63031); # null isbn
      $rh = $item->getRightsholder();
      $this->assertNull($rh);
    }
    
    function testCountISBNUsage() {
      $item = new reserveItem(19762); # isbn with rightsholder info
      $this->assertEqual($item->getISBN(), 'FAKE ISBN', "count ISBN correctly; should be FAKE ISBN, got '" .
      $item->getISBN() . "'");
      $this->assertEqual($item->countISBNUsage(), 3, "count ISBN correctly; should be 3, got '" .
       $item->countISBNUsage() . "'");
    }
    
    function testSetISBN() {
      $item = new reserveItem(19762);
      $isbn = "1234567890";
      $item->setISBN($isbn);
      $this->assertEqual($isbn, $item->getISBN(),
       "ISBN set correctly in reserve item; should be '$isbn', got '"
       . $item->getISBN() . "'");
      $item = new reserveItem(19762);
      $this->assertEqual($isbn, $item->getISBN(),
       "ISBN set correctly in reserve item after db init; should be '$isbn', got '" .
       $item->getISBN() . "'");
      // Test to see that alpha characters are removed from the string before saving.
      $isbn = "ABC1234567890";
      $isbn_result = "1234567890";   
      $item->setISBN($isbn);
      $this->assertEqual($isbn_result, $item->getISBN(),
       "ISBN set correctly in reserve item when removing alpha chars; should be '$isbn_result', got '"
       . $item->getISBN() . "'");       
    }
}

if (! defined('RUNNER')) {
  $test = &new TestReserveItemClass();
  $test->run(new HtmlReporter());
}
?>
