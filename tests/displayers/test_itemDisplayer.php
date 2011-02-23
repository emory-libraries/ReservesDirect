<?php
require_once("../UnitTest.php");
require_once("secure/displayers/itemDisplayer.class.php");
require_once("secure/interface/instructor.class.php");
require_once("secure/classes/courseInstance.class.php");
require_once("secure/classes/reserveItem.class.php");

class TestItemDisplayer extends UnitTest {
  private $user;
  private $dsp;
  function setUp() {
    $this->dsp = new itemDisplayer();
    global $u;
    $this->loadDB('../fixtures/staff.sql');
    $this->loadDB('../fixtures/requests.sql');
    $this->user = new instructor(109);
    $u = $this->user;
    $this->ci = new courseInstance(7782);
  }
  
  function tearDown() {
    $this->loadDB('../fixtures/truncateTables.sql');
    // clear out any test request variables
    $_REQUEST = array();
  }

  function test_displayEditItemSource() {
    $new_reserve = new reserveItem();   
    $new_reserve->itemGroup = 'ELECTRONIC';

    ob_start();
    $this->dsp->displayEditItemSource($new_reserve);
    $output = ob_get_contents();
    ob_end_clean();

    // FIXME: add tests here...
  }

  function test_displayEditItemMeta() {
    $new_reserve = new reserveItem();   
    $new_reserve->itemGroup = 'ELECTRONIC';

    global $g_copyrightNoticeURL;
    $g_copyrightNoticeURL = "COPYRIGHT_URL";

    ob_start();
    $this->dsp->displayEditItemMeta($new_reserve);
    $output = ob_get_contents();
    ob_end_clean();

    // added copyright when consolidating faculty-specific add/upload item forms
    $this->assertPattern('|I have read the Library\'s <a href="COPYRIGHT_URL".*>copyright notice|',
			 $output,
			 "editItemMeta includes text about & link to copyright notice");

  }

  function test_displayEditItemNotesBrowserType() {
    $new_reserve = new reserveItem();   
    $new_reserve->itemGroup = 'ELECTRONIC';

    global $ajax_browser;

    // test browser that does support the ajax not functionality
    $ajax_browser = false; 
    ob_start();
    $this->dsp->displayEditItemMeta($new_reserve, $new_reserve);
    $output = ob_get_contents();
    ob_end_clean();
    $this->assertNoPattern('|Add/Edit Note|', $output, "editItemMeta does not include ajax note capability");
    
    // test for ajax capable browser that does support the ajax note functionality    
    $ajax_browser = true;
    ob_start();
    $this->dsp->displayEditItemMeta($new_reserve, $new_reserve);
    $output = ob_get_contents();
    ob_end_clean();
    $this->assertPattern('|Add/Edit Note|', $output, "editItemMeta does include ajax note capability");    
  } 
}
?>