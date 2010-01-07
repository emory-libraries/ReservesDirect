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

	function test_addDigitalItemValidation() {
	  global $_REQUEST;
	  $_REQUEST = array();	// clear out any request
	  $mgr = new requestManager('addDigitalItem', $this->user, $this->ci, array());
	  $err = $mgr->addDigitalItemValidation();
	  $this->assertIsA($err, "Array", "addDigitalItemValidation returns an array");
	  $this->assertTrue(in_array("Type of material is required.", $err),
			    "error list includes type of material required");

	  $_REQUEST["documentType"] = "URL";
	  $err = $mgr->addDigitalItemValidation();
	  $this->assertTrue(in_array("Selected 'add a link', but no URL was specified.", $err),
			    "url required when document type is url");
	  $_REQUEST["url"] = "http://some.thi.ng";
	  $err = $mgr->addDigitalItemValidation();
	  $this->assertFalse(in_array("Selected 'add a link', but no URL was specified.", $err),
			    "no url error when document type is url & url specified");
	  
	  $_REQUEST["documentType"] = "DOCUMENT";
	  $err = $mgr->addDigitalItemValidation();
	  $this->assertTrue(in_array("Selected 'upload a document', but no file was uploaded.", $err),
			    "file required when document type is document");

	  // spot-check per-item requirements checking
	  unset($_REQUEST["documentType"]);
	  $_REQUEST["material_type"] = "BOOK_PORTION";
	  $err = $mgr->addDigitalItemValidation();
	  $this->assertTrue(in_array("Title is required.", $err),
			    "title is required when material type is book portion");
	  
	}
	
}

if (! defined('RUNNER')) {
	$test = &new TestRequestManager();
	$test->run(new HtmlReporter());
}
?>