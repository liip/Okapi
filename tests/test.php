<?php
ini_set("include_path", dirname(__FILE__)."/../inc/" . ':' . 
                        dirname(__FILE__)."/" . ':' . 
                        ini_get("include_path"));

require_once('simpletest/reporter.php');
require_once('simpletest/unit_tester.php');
require_once('api/autoload.php');

// Add test classes
$test = &new TestSuite("Okapi");
foreach (glob('unit/*.php') as $file) {
    $test->addTestFile($file);
}

// Run
$test->run(new JunitXMLReporter());
?>
