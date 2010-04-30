<?php
require_once("../UnitTest.php");
require_once("secure/classes/reserveItem.class.php");

class TestItemClass extends UnitTest {
  function setUp() {
    $this->loadDB('../fixtures/copyright.sql');
  }
  
  function tearDown() {
    $this->loadDB('../fixtures/truncateTables.sql');
  }
  
  function testGetCopyrightData() {
    $item = new reserveItem(133509);
    $query = 'SELECT i.item_id, i.pages_times_used, i.pages_times_total FROM items as i
            LEFT JOIN reserves as r on r.item_id = i.item_id
            WHERE r.course_instance_id = ? and i.ISBN = ?';  
    $course_instance = 57900; 
    $isbn = '0140444734';
    $params = array($course_instance, $isbn);   
    $copyrightData = intval($item->getCopyrightData($query, $params, null));
    $this->assertEqual('26', $copyrightData, "Should return correct copyright data = $copyrightData.");
    
    $isbn = '0300033060';
    $params = array($course_instance, $isbn);   
    $copyrightData = intval($item->getCopyrightData($query, $params, null));
    $this->assertEqual('41', $copyrightData, "Should return correct copyright data = $copyrightData.");
    
    $isbn = '046500699';
    $params = array($course_instance, $isbn);   
    $copyrightData = intval($item->getCopyrightData($query, $params, null));
    $this->assertEqual('23', $copyrightData, "Should return correct copyright data = $copyrightData.");
    
    $isbn = '047026649';
    $params = array($course_instance, $isbn);   
    $copyrightData = intval($item->getCopyrightData($query, $params, null));
    $this->assertEqual('22', $copyrightData, "Should return correct copyright data = $copyrightData.");
    
    $isbn = '0788504886';
    $params = array($course_instance, $isbn);   
    $copyrightData = intval($item->getCopyrightData($query, $params, null));
    $this->assertEqual('35', $copyrightData, "Should return correct copyright data = $copyrightData.");    
    
  }
  
}

if (! defined('RUNNER')) {
  $test = &new TestItemClass();
  $test->run(new HtmlReporter());
}
?>
