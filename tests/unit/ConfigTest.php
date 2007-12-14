<?php
/**
 * Tests the api_config class which handles reading of configuration
 * XML files.
 */
class ConfigTest extends UnitTestCase {
    function setUp() {
        unset($_SERVER['OKAPI_ENV']);
    }
    
    function tearDown() {
        unset($_SERVER['OKAPI_ENV']);
    }
    
    function testDefault() {
        $cfg = new api_config();
        $this->assertEqual($cfg->config_test, 'main');
    }

    function testEnvDev() {
        $_SERVER['OKAPI_ENV'] = 'debug';
        $cfg = new api_config();
        $this->assertEqual($cfg->config_test, 'debug');
    }

    /**
     * Test if include work.
     */
    function testEnvInclude() {
        $_SERVER['OKAPI_ENV'] = 'debug';
        $cfg = new api_config();
        $this->assertEqual($cfg->commandmap['regex'], true);
    }
}
?>
