<?php
require_once("../UnitTest.php");
require_once("secure/classes/proxyHost.class.php");

class TestProxyHost extends UnitTest {
  function setUp() {
    $this->loadDB('../fixtures/proxies.sql');
  }
  
  function tearDown() {
    $this->loadDB('../fixtures/truncateTables.sql');
  }

  function testProxyUrl() {
    $url = proxyHost::proxyURL("http://online.sagepub.com/some/article/a/1", "testuser");
    $this->assertPattern("|^http://proxy.me/?|", $url, "exact domain match gets proxied");
    $url = proxyHost::proxyURL("http://eab.sagepub.com/cgi/content/abstract/37/3/364", "user");
    $this->assertPattern("|^http://proxy.me/?|", $url, "subdomain with partial domain match gets proxied");

    $url = proxyHost::proxyURL("http://sagepub.com/cgi/content/abstract/37/3/364", "user");
    $this->assertPattern("|^http://proxy.me/?|", $url, "domain matching partial domain match gets proxied");

    $url = proxyHost::proxyURL("http://inline.com/days/365", "user");
    $this->assertNoPattern("|^http://proxy.me/?|", $url, "non-matching domain does not get proxied");
 
    // agepub.com should NOT be proxied because sagepub.com is in the db
    $url = proxyHost::proxyURL("http://some.other.agepub.com/days/365", "user");
    $this->assertNoPattern("|^http://proxy.me/?|", $url, "non-matching domain with same domain ending does not get proxied");
  }


  /** not explicitly tested:
      doSearch, generateEZproxyTicket
   */
  

    
}

if (! defined('RUNNER')) {
  $test = &new TestProxyHost();
  $test->run(new HtmlReporter());
}
?>