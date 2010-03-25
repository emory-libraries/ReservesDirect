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
    global $_REQUEST;
    $this->loadDB('../fixtures/truncateTables.sql');
    // clear out test request variables
    $_REQUEST = array();
  }

  function test_addDigitalItem() {
    global $_REQUEST;
    $_REQUEST['title'] = "test item";
    $_REQUEST['author'] = "Da Vinci";
    $_REQUEST['material_type'] = "IMAGE";
    $_REQUEST['submit_edit_item_meta'] = 1;
    $_REQUEST['ci'] = $this->ci->courseInstanceID;
        
    $mgr = new itemManager('addDigitalItem', $this->user, $this->ci, array());


    $this->assertEqual("requestDisplayer", $mgr->displayClass,
           "display class should be 'requestDisplayer' after adding new digital item; got '" . $mgr->displayClass . "'");
    $this->assertEqual("displaySelectCIForItem", $mgr->displayFunction,
           "display function should be 'displaySelectCIForItem' after adding new digital item; got '" . $mgr->displayClass . "'");

    // first argument should be new item id
    $item_id = $mgr->argList[0];

    $this->assertNotNull($item_id, "non-null item id '$item_id' set in arguments for dispaly");
    
    // test that item was correctly stored in db
    $item = new reserveItem($item_id);
    $this->assertEqual("test item", $item->getTitle());
    $this->assertEqual("Da Vinci", $item->getAuthor());
    $this->assertEqual("IMAGE", $item->getMaterialType());

    // third argument should be an array of selected course instance ids
    // (needed so that a new item can be assigned to a course)
    $selected_ci = $mgr->argList[2];
    $this->assertEqual($selected_ci[0], $this->ci->courseInstanceID,
           "course instance id '" . $this->ci->courseInstanceID .
           "' from request passed on as argument to displaySelectCIForItem; got '"
           . $selected_ci[0] . "'");

    
    // could also test that itemAudit is created...
  }


  function test_editItem() {
    global $_REQUEST;
    $id = 19762;
    // edit fixture item
    $_REQUEST['itemID'] = $id;
    $_REQUEST['submit_edit_item_meta'] = 1; // simulate form submission
    $_REQUEST['title'] = "Cultural Anthropologie";
    $_REQUEST['author'] = "M.G.E.";
    // NOTE: currently all fields seem to be required, no checking if param is present
    $_REQUEST['performer'] = NULL;
    $_REQUEST['selectedDocIcon'] = NULL;
    $_REQUEST['volume_title'] = "Journal of Mall Culture";
    $_REQUEST['volume_edition'] = "vol1";
    $_REQUEST['times_pages'] = "12-33";
    $_REQUEST['source'] = "1902";
    $_REQUEST['ISBN'] = NULL;
    $_REQUEST['ISSN'] = "12304003";
    $_REQUEST['OCLC'] = NULL; 
    $_REQUEST['item_status'] = "ACTIVE";
    $_REQUEST['material_type'] = "JOURNAL_ARTICLE";
    $_REQUEST['home_library'] = 1;
    $_REQUEST['publisher'] = "HBJ";
    $_REQUEST['availability'] = 1;
    
    // NOTE: Comment out for the Type of Material release
    // $_REQUEST['total_times_pages'] = 233;
    
    $this->mgr = new itemManager('editItem', $this->user);


    // get item from db & check updates
    $item = new reserveItem($id);
    $this->assertEqual("Cultural Anthropologie", $item->getTitle());
    $this->assertEqual("Journal of Mall Culture", $item->getVolumeTitle());
    $this->assertEqual("vol1", $item->getVolumeEdition());
    $this->assertEqual("M.G.E.", $item->getAuthor());
    $this->assertEqual("ACTIVE", $item->getStatus());
    $this->assertEqual("JOURNAL_ARTICLE", $item->getMaterialType());
    $this->assertEqual("HBJ", $item->getPublisher());
    $this->assertEqual(1, $item->getAvailability());
    $this->assertEqual("12-33", $item->getPagesTimes());
    $this->assertEqual("12304003", $item->getISSN());
    $this->assertEqual("1902", $item->getSource());
    
    // NOTE: Comment out for the Type of Material release
    // $this->assertEqual(233, $item->getTotalPagesTimes());
  }

  function test_editItemValidation() {
    global $_REQUEST;
    $_REQUEST = array();  // clear out any request
    $mgr = new itemManager('addDigitalItem', $this->user, $this->ci, array());
    $err = $mgr->editItemValidation();
    $this->assertIsA($err, "Array", "addDigitalItemValidation returns an array");
    $this->assertTrue(in_array("Type of material is required.", $err),
          "error list includes type of material required");

    $_REQUEST["documentType"] = "URL";
    $err = $mgr->editItemValidation();
    $this->assertTrue(in_array("Selected 'add a link', but no URL was specified.", $err),
          "url required when document type is url");
    $_REQUEST["url"] = "http://some.thi.ng";
    $err = $mgr->editItemValidation();
    $this->assertFalse(in_array("Selected 'add a link', but no URL was specified.", $err),
          "no url error when document type is url & url specified");
    
    $_REQUEST["documentType"] = "DOCUMENT";
    $err = $mgr->editItemValidation();
    $this->assertTrue(in_array("Selected 'upload a document', but no file was uploaded.", $err),
          "file required when document type is document");

    // spot-check per-item requirements checking
    unset($_REQUEST["documentType"]);
    $_REQUEST["material_type"] = "BOOK_PORTION";
    $err = $mgr->editItemValidation();
    $this->assertTrue(in_array("Title is required.", $err),
          "title is required when material type is book portion");
    
  }

  function testGetReserveItem() {
    global $ci; // function sets a global course instance variable
    $mgr = new itemManager('', $this->user);

    // find item by reserve id
    $_REQUEST['reserveID'] = '202864';  // reserve id from fixture
    list($item, $reserve) = $mgr->getReserveItem();
    $this->assertIsA($item, "reserveItem", "getReserveItem by reserve id returns reserveItem");
    $this->assertIsA($reserve, "reserve", "getReserveItem by reserve id returns reserve");
    $this->assertIsA($ci, "courseInstance", "getReserveItem by reserve id sets global course instance");
    $this->assertEqual(202864, $reserve->reserveID,
           "reserve returned by getReserveItem has correct id");
    $this->assertEqual(63031, $item->itemID,
           "reserveItem returned by getReserveItem has correct id");
    $this->assertEqual(11496, $ci->courseInstanceID,
           "course instance set by getReserveItem has correct id");

    // find item by item id
    unset($_REQUEST['reserveID']);
    $_REQUEST['itemID'] = '19762';  // item id from fixture
    list($item, $reserve) = $mgr->getReserveItem();
    $this->assertIsA($item, "reserveItem", "getReserveItem by item id returns reserveItem");
    $this->assertNull($reserve, "getReserveItem by item id returns null for reserve");
    $this->assertEqual(19762, $item->itemID,
           "reserveItem returned by getReserveItem has correct id");
  }


  
  function testPrepEditItem() {
    global $page, $loc, $help_article;
    $loc = "";
    
    $mgr = new itemManager('', $this->user);
    $item = new reserveItem('19762');
    $reserve = new reserve('202864');

    $mgr->prepEditItem($item, $reserve);
    $this->assertIsA($mgr->argList, "Array", "argList is set to an array after running prepEditItem");
    $this->assertEqual($mgr->argList[0], $item,
           "item passed to prepEditItem is set in argList");
    $this->assertEqual($mgr->argList[1], $reserve,
           "reserve passed to prepEditItem is set in argList");
    $this->assertEqual("addReserve", $page,
           "global page variable initialized by prepEditItem (should be 'addReserve', got '" 
           . $loc . "'"); 
    $this->assertEqual("edit item", $loc,
           "global loc variable initialized by prepEditItem (should be 'edit item', got '"
           . $loc . "'");
    $this->assertNotNull($help_article,
           "global help_article variable set by prepEditItem");
    $this->assertEqual("33", $help_article,
           "global help_article variable set to '33' by prepEditItem (got '"
           . $help_article . "'");
    $this->assertIsA($mgr->argList[2], "Array",
         "third element in arg list should be an array (dubArray)");


    // dub array params should be set in argList if set in request
    $_REQUEST['dubReserve'] = "dubme";
    $_REQUEST['selected_instr'] = "Dumbledore";

    $mgr->prepEditItem($item, $reserve);
    $this->assertTrue(isset($mgr->argList[2]['dubReserve']),
          "dubReserve is set in argList when set in request");
    $this->assertEqual($mgr->argList[2]['dubReserve'], "dubme",
         "argList dubArray includes dubReserve info");
    $this->assertTrue(isset($mgr->argList[2]['selected_instr']),
          "selected_instr is set in argList when set in request");
    $this->assertEqual($mgr->argList[2]['selected_instr'], "Dumbledore",
         "argList dubArray includes selected instructor");


    // course id from request should be passed on to display
    $_REQUEST['ci'] = "course_id";
    $mgr->prepEditItem($item, $reserve);
    $this->assertTrue(isset($mgr->argList['ci']),
          "course id is set in argList when set in request");
    $this->assertEqual($mgr->argList['ci'], "course_id",
          "course id value set in argList when set in request");

  }


  /**
   * NOTE: currently not writing tests for saveReserve or storeItem
   * - these functions are both made up existing code that was moved, either
   * from one manager to another, or a block of code pulled out into a function.
   */



  
}

if (! defined('RUNNER')) {
  $test = &new TestItemManager();
  $test->run(new HtmlReporter());
}
?>
