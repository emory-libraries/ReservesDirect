<?php
require_once("../UnitTest.php");
require_once("secure/managers/requestManager.class.php");
require_once("secure/classes/user.class.php");
require_once("secure/classes/courseInstance.class.php");
require_once("secure/classes/reserveItem.class.php");

class TestRequestManager extends UnitTest {
  private $user;
  function setUp() {
    global $u;
    $this->loadDB('../fixtures/staff.sql');
    $this->loadDB('../fixtures/requests.sql');
    $this->user = new user(109);
    $u = $this->user;
    $this->ci = new courseInstance(7782);
  }
  
  function tearDown() {
    $this->loadDB('../fixtures/truncateTables.sql');
  }

  
}

if (! defined('RUNNER')) {
  $test = &new TestRequestManager();
  $test->run(new HtmlReporter());
}
?>
