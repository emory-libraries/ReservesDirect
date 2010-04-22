<?php
require_once('../UnitTest.php');

class TestNavHtml extends UnitTest {

  function setUp()
  {
    $this->includePath = '../../secure/html/';
  }

  function htmlOutput($path)
  {
    ob_start();
    include ($this->includePath . $path);
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
  }

  function testBasicNav()
  {
    $output = $this->htmlOutput('main_nav.inc.html');

    $this->assertPattern('/div/', $output,
        "basic nav contains mainNav");
    $this->assertNoPattern('/\bCopyright\b/', $output,
        "basic nav doesn't comtain copyright tab");
    $this->assertNoPattern('/\bAdmin\b/', $output,
        "basic nav doesn't contain admin");
  }

  function testStaffNav()
  {
    $_SESSION['userclass'] = 'staff';
    $output = $this->htmlOutput('main_nav.inc.html');

    $this->assertPattern('/<div id="mainNav">/', $output,
        "staff nav contains mainNav");
    $this->assertPattern('/\bCopyright\b/', $output,
        "staff nav doesn't comtain copyright tab");
    $this->assertNoPattern('/\bAdmin\b/', $output,
        "staff nav doesn't contain admin");
  }

  function testAdminNav()
  {
    $_SESSION['userclass'] = 'admin';
    $output = $this->htmlOutput('main_nav.inc.html');

    $this->assertPattern('/<div id="mainNav">/', $output,
        "admin nav contains mainNav");
    $this->assertPattern('/\bCopyright\b/', $output,
        "admin nav comtains copyright tab");
    $this->assertPattern('/\bAdmin\b/', $output,
        "admin nav contains admin");
  }

}

?>
