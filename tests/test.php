<?php
if ( empty( $argv[1] ) ) {
    echo 'Usage: '.basename(__FILE__)." <suite>\n";
    echo "Suite can be either unit or functional\n";
}
ini_set("include_path", dirname(__FILE__)."/../inc/" . ':' .
                        dirname(__FILE__)."/" . ':' . 
                        ini_get("include_path"));

$coverage = (getenv('coverage') ? true : false);
if ($coverage) {
    require(dirname(__FILE__).'/coverage_top.php');
}

require_once('simpletest/reporter.php');
require_once('simpletest/unit_tester.php');
require_once('api/autoload.php');


switch ($argv[1]) {
    case 'unit':
        // Add test classes
        $test = &new TestSuite("Okapi Unit");
        if (isset($argv[2])) {
           $test->addTestFile($argv[2]);
        } else {
            foreach (glob('unit/*.php') as $file) {
                $test->addTestFile($file);
            }
        }

        // Set up environment
        $_SERVER['HTTP_HOST'] = 'demo.okapi.org';
        $_GET = array('path' => 'mypath', 'question' => 'does it work?');
        api_init::start();

        // Run
        $test->run(new JunitXMLReporter());
        ob_end_flush();
        break;
        
    case 'functional':
        $_SERVER['HTTP_HOST'] = 'demo.okapi.org';
        $test = &new TestSuite("Okapi Functional");
        if (isset($argv[2])) {
           $test->addTestFile($argv[2]);
        } else {
            foreach (glob('functional/*.php') as $file) {
                $test->addTestFile($file);
            }
        }

        // Run
        $test->run(new JunitXMLReporter());
        ob_end_flush();
        break;
}

if ($coverage) {
    require(dirname(__FILE__).'/coverage_bottom.php');
}
