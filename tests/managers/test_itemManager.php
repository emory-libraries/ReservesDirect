<?php
require_once("../UnitTest.php");
require_once("secure/managers/itemManager.class.php");
require_once("secure/classes/user.class.php");
require_once("secure/classes/courseInstance.class.php");
require_once("secure/classes/reserveItem.class.php");

class TestItemManager extends UnitTest {
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
	  $id = 19762;
	  // edit fixture item
	  $_REQUEST['itemID'] = $id;
	  $_REQUEST['submit_edit_item_meta'] = 1;	// simulate form submission
	  $_REQUEST['title'] = "Cultural Anthropologie";
	  $_REQUEST['author'] = "M.G.E.";
	  // NOTE: currently all fields seem to be required, no checking if param is present
	  $_REQUEST['performer'] = NULL;
	  $_REQUEST['selectedDocIcon'] = NULL;
	  $_REQUEST['volumeTitle'] = NULL;
	  $_REQUEST['volumeEdition'] = NULL;
	  $_REQUEST['pagesTimes'] = NULL;
	  $_REQUEST['source'] = NULL;
	  $_REQUEST['ISBN'] = NULL;
	  $_REQUEST['ISSN'] = NULL;
	  $_REQUEST['OCLC'] = NULL; 
	  $_REQUEST['item_status'] = "ACTIVE";
	  $_REQUEST['material_type'] = "JOURNAL_ARTICLE";
	  $_REQUEST['home_library'] = 1;
	  $this->mgr = new itemManager('editItem', $this->user);


	  // get item from db & check updates
	  $item = new reserveItem($id);
	  $this->assertEqual("Cultural Anthropologie", $item->getTitle());
	  $this->assertEqual("M.G.E.", $item->getAuthor());
	  $this->assertEqual("ACTIVE", $item->getStatus());
	  $this->assertEqual("JOURNAL_ARTICLE", $item->getMaterialType());
	  
					
	}
	
}

if (! defined('RUNNER')) {
	$test = &new TestItemManager();
	$test->run(new HtmlReporter());
}
?>