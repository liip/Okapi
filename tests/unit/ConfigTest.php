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
        $cfg = api_config::getInstance(TRUE);
        $this->assertEqual($cfg->config_test, 'main');
    }

    function testEnvDev() {
        $_SERVER['OKAPI_ENV'] = 'debug';
        $cfg = api_config::getInstance(TRUE);
        $this->assertEqual($cfg->config_test, 'debug');
    }

    /**
     * Test if include work.
     */
    function testEnvInclude() {
        $_SERVER['OKAPI_ENV'] = 'debug';
        $cfg = api_config::getInstance(TRUE);
        $this->assertEqual($cfg->configCache, false);
    }
}
?>
