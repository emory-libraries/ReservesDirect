<?php
require_once("../bootstrap.inc.php");
define('RUNNER', true);

// test groups
require_once("ClassTests.php");
require_once("ManagerTests.php");
require_once("../" . SIMPLE_TEST . "xmltime.php");

$suite = new TestSuite('ReservesDirect Unit Tests');

$suite->addTestCase(new ClassGroupTest());	// classess
$suite->addTestCase(new ManagerGroupTest());	// managers
$suite->run(new XmlTimeReporter());


?>
