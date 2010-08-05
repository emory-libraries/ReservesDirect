<?php
require_once("../bootstrap.inc.php");
define('RUNNER', true);

// test groups
require_once("ClassTests.php");
require_once("ManagerTests.php");
require_once("DisplayerTests.php");
require_once("HtmlTests.php");
require_once("InterfaceTests.php");

$suite = new TestSuite('ReservesDirect Tests');

$suite->addTestCase(new ClassGroupTest());  // classes
$suite->addTestCase(new ManagerGroupTest());  // managers
$suite->addTestCase(new DisplayerGroupTest());  // displayers
$suite->addTestCase(new HtmlGroupTest());  // html
$suite->addTestCase(new InterfaceGroupTest());  // interfaces


$suite->run(new HtmlReporter());

?>
