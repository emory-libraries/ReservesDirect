<?php
require_once("../bootstrap.inc.php");
define('RUNNER', true);

// test groups
require_once("ClassTests.php");
require_once("ManagerTests.php");

$suite = new TestSuite('ReservesDirect Tests');

$suite->addTestCase(new ClassGroupTest());	// classes
$suite->addTestCase(new ManagerGroupTest());	// managers


$suite->run(new HtmlReporter());

?>
