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

// Set up environment
$_SERVER['HTTP_HOST'] = 'demo.okapi.org';
$_SERVER["REQUEST_URI"] = '/the/command';
$_GET = array('path' => 'mypath', 'question' => 'does it work?');
api_init::start();

// Run
$test->run(new JunitXMLReporter());
?>
