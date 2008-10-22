<?php
/**
 * Tests the api_config class which handles reading of configuration
 * XML files.
 */
class LogTest extends UnitTestCase {

    function setUp() {
        unset($_SERVER['OKAPI_ENV'] );

    }

    function tearDown() {
        unset($_SERVER['OKAPI_ENV'] );
        api_config::getInstance(TRUE);
    }

    function testDefault() {
        unset($_SERVER['OKAPI_ENV']);
        $log = api_log::getInstance(TRUE);
        $this->assertFalse($log->isLogging());

    }

    function testLoggingBasic() {
        $_SERVER['OKAPI_ENV'] = 'loggingAll';
        api_config::getInstance(TRUE);

        $log = api_log::getInstance(TRUE);
        $this->assertTrue($log->isLogging());

    }

    /** using api_log::log() */

    function testWithStatic() {
        $_SERVER['OKAPI_ENV'] = 'loggingAll';
        api_config::getInstance(TRUE);
        //set everything to null
        api_log::$instance = null;
        api_log::$logger = null;
        api_log::$mockWriter = null;

        api_log::log(api_log::EMERG, "Test Emergency");
        api_log::log(api_log::ALERT, "Test Alert");
        api_log::log(api_log::CRIT, "Test Critical");
        api_log::log(api_log::ERR, "Test Error");
        api_log::log(api_log::WARN, "Test Warning");
        api_log::log(api_log::NOTICE, "Test Notice");
        api_log::log(api_log::INFO, "Test Info");
        api_log::log(api_log::DEBUG, "Test Debug");

        $this->assertEqual(api_log::$mockWriter->events[0]['priority'], api_log::EMERG);
        $this->assertEqual(api_log::$mockWriter->events[0]['message'], "Test Emergency");

        $this->assertEqual(api_log::$mockWriter->events[1]['priority'], api_log::ALERT);
        $this->assertEqual(api_log::$mockWriter->events[1]['message'], "Test Alert");

        $this->assertEqual(api_log::$mockWriter->events[2]['priority'], api_log::CRIT);
        $this->assertEqual(api_log::$mockWriter->events[2]['message'], "Test Critical");

        $this->assertEqual(api_log::$mockWriter->events[3]['priority'], api_log::ERR);
        $this->assertEqual(api_log::$mockWriter->events[3]['message'], "Test Error");

        $this->assertEqual(api_log::$mockWriter->events[4]['priority'], api_log::WARN);
        $this->assertEqual(api_log::$mockWriter->events[4]['message'], "Test Warning");

        $this->assertEqual(api_log::$mockWriter->events[5]['priority'], api_log::NOTICE);
        $this->assertEqual(api_log::$mockWriter->events[5]['message'], "Test Notice");

        $this->assertEqual(api_log::$mockWriter->events[6]['priority'], api_log::INFO);
        $this->assertEqual(api_log::$mockWriter->events[6]['message'], "Test Info");

        $this->assertEqual(api_log::$mockWriter->events[7]['priority'], api_log::DEBUG);
        $this->assertEqual(api_log::$mockWriter->events[7]['message'], "Test Debug");
    }

    /** using api_log::getInstance() */
    function testWithInstance() {
        $_SERVER['OKAPI_ENV'] = 'loggingAll';
        api_config::getInstance(TRUE);

        $log = api_log::getInstance(TRUE);

        $log->emerg("Test Emergency");
        $log->alert("Test Alert");
        $log->crit("Test Critical");
        $log->err("Test Error");
        $log->warn("Test Warning");
        $log->notice("Test Notice");
        $log->info("Test Info");
        $log->debug("Test Debug");

        $this->assertEqual(api_log::$mockWriter->events[0]['priority'], api_log::EMERG);
        $this->assertEqual(api_log::$mockWriter->events[0]['message'], "Test Emergency");

        $this->assertEqual(api_log::$mockWriter->events[1]['priority'], api_log::ALERT);
        $this->assertEqual(api_log::$mockWriter->events[1]['message'], "Test Alert");

        $this->assertEqual(api_log::$mockWriter->events[2]['priority'], api_log::CRIT);
        $this->assertEqual(api_log::$mockWriter->events[2]['message'], "Test Critical");

        $this->assertEqual(api_log::$mockWriter->events[3]['priority'], api_log::ERR);
        $this->assertEqual(api_log::$mockWriter->events[3]['message'], "Test Error");

        $this->assertEqual(api_log::$mockWriter->events[4]['priority'], api_log::WARN);
        $this->assertEqual(api_log::$mockWriter->events[4]['message'], "Test Warning");

        $this->assertEqual(api_log::$mockWriter->events[5]['priority'], api_log::NOTICE);
        $this->assertEqual(api_log::$mockWriter->events[5]['message'], "Test Notice");

        $this->assertEqual(api_log::$mockWriter->events[6]['priority'], api_log::INFO);
        $this->assertEqual(api_log::$mockWriter->events[6]['message'], "Test Info");

        $this->assertEqual(api_log::$mockWriter->events[7]['priority'], api_log::DEBUG);
        $this->assertEqual(api_log::$mockWriter->events[7]['message'], "Test Debug");
    }

    /** Using new api_log() */
    function testWithNew() {
        $_SERVER['OKAPI_ENV'] = 'loggingAll';
        api_config::getInstance(TRUE);

        $log = new api_log();
        $log->emerg("Test Emergency");
        $log->alert("Test Alert");

        $log = new api_log();
        $log->crit("Test Critical");

        $this->assertEqual(api_log::$mockWriter->events[0]['priority'], api_log::EMERG);
        $this->assertEqual(api_log::$mockWriter->events[0]['message'], "Test Emergency");

        $this->assertEqual(api_log::$mockWriter->events[1]['priority'], api_log::ALERT);
        $this->assertEqual(api_log::$mockWriter->events[1]['message'], "Test Alert");

        $this->assertEqual(api_log::$mockWriter->events[2]['priority'], api_log::CRIT);
        $this->assertEqual(api_log::$mockWriter->events[2]['message'], "Test Critical");

    }

    /** Test if not all log messages are logged, when priority level is higher */
    function testWithPriority() {
        $_SERVER['OKAPI_ENV'] = 'loggingAlert';
        api_config::getInstance(TRUE);
        $log = api_log::getInstance(TRUE);

        $log->emerg("Test Emergency");
        $log->alert("Test Alert");
        $log->crit("Test Critical");

        $this->assertEqual(api_log::$mockWriter->events[0]['priority'], api_log::EMERG);
        $this->assertEqual(api_log::$mockWriter->events[0]['message'], "Test Emergency");

        $this->assertEqual(api_log::$mockWriter->events[1]['priority'], api_log::ALERT);
        $this->assertEqual(api_log::$mockWriter->events[1]['message'], "Test Alert");

        $this->assertEqual(count(api_log::$mockWriter->events),2);

    }

    /** using api_log::log() */

    function testWithStaticDump() {
        $_SERVER['OKAPI_ENV'] = 'loggingAll';
        api_config::getInstance(TRUE);
        //set everything to null
        api_log::$instance = null;
        api_log::$logger = null;
        api_log::$mockWriter = null;

        api_log::dump("Test Dump");

        $this->assertEqual(api_log::$mockWriter->events[0]['priority'], api_log::ERR);
        $this->assertEqual(api_log::$mockWriter->events[0]['message'], "Test Dump");
    }

}
