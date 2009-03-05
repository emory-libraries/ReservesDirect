<?php
require_once("../bootstrap.inc.php");
define('RUNNER', true);

// test groups
require_once("ClassTests.php");

$suite = new TestSuite('All Tests');


$suite->addTestCase(new ClassGroupTest());	// classess


$suite->run(new HtmlReporter());


?>
