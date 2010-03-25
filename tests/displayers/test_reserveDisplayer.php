<?php
require_once("../UnitTest.php");
require_once("secure/displayers/reservesDisplayer.class.php");
require_once("secure/classes/user.class.php");
require_once("secure/classes/courseInstance.class.php");
require_once("secure/classes/reserveItem.class.php");

class TestReservesDisplayer extends UnitTest {
  private $user;
  private $dsp;
  function setUp() {
    $this->dsp = new reservesDisplayer();
    global $u;
    $this->loadDB('../fixtures/staff.sql');
    $this->loadDB('../fixtures/requests.sql');
    $this->user = new user(109);
    $u = $this->user;
    $this->ci = new courseInstance(7782);
  }
  
  function tearDown() {
    $this->loadDB('../fixtures/truncateTables.sql');
    // clear out any test request variables
    $_REQUEST = array();
  }

  function test_displaySearchItemMenu() {
    // capture output for testing
    ob_start();
    $this->dsp->displaySearchItemMenu($this->ci->courseInstanceID);
    $output = ob_get_contents();
    ob_end_clean();
    // link to new, consolidated add/edit item form
    $this->assertPattern('|<a href="index.php\?cmd=addDigitalItem&ci=7782">Add an electronic item</a>|',
			 $output,
			 "faculty searchItemMenu contains link to addDigitalItem");
    // previous, separate commands
    $this->assertNoPattern('|\?cmd=uploadDocument|',
			 $output,
			 "faculty searchItemMenu no longer contains uploadDocument");
    $this->assertNoPattern('|\?cmd=addURL|',
			 $output,
			 "faculty searchItemMenu no longer contains addURL");
    
  }

}
?>