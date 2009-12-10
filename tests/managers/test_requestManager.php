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

	function test_addDigitalItem() {
	  global $_REQUEST;
	  $_REQUEST['title'] = "test item";
	  $_REQUEST['material_type'] = "IMAGE";

	  $this->mgr = new requestManager('addDigitalItem', $this->user, $this->ci, array());
	  
	  $item_id = $this->mgr->storeItem();
	  $this->assertNotNull($item_id, "storeItem returns non-null item id '$item_id'");

	  // test that item was correctly stored in db
	  $item = new reserveItem($item_id);
	  $this->assertEqual("test item", $item->getTitle());
	  $this->assertEqual("IMAGE", $item->getMaterialType());

	  // could also test that itemAudit is created...
	  
	  
	}
	
}

if (! defined('RUNNER')) {
	$test = &new TestRequestManager();
	$test->run(new HtmlReporter());
}
?>