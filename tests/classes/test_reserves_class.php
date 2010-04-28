<?php
require_once('../UnitTest.php');
require_once('secure/classes/reserves.class.php');

class TestReservesClass extends UnitTest {

  function setUp() {
    $this->loadDB('../fixtures/requests.sql');
  }

  function tearDown() {
    $this->loadDB('../fixtures/truncateTables.sql');
  }
  
  function testCopyrightReviewReserves() {
    $reserves = reserve::getCopyrightReviewReserves();
    $this->assertEqual(count($reserves), 4);
  }

}
