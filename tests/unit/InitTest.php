<?php
/**
 * Tests the api_init class which handles initialisation of the
 * API. Sets constants, variables, etc.
 */
class InitTest extends UnitTestCase {
    function setUp() {
        $_SERVER['HTTP_HOST'] = 'demo.okapi.org';
        $_SERVER["REQUEST_URI"] = '/the/command';
        $_GET = array('path' => 'mypath', 'question' => 'does it work?');
        api_init::start();
    }
    
    /**
     * Verify that api_init initializes the API_DIRECTORY_SEPARATOR
     * constant.
     */
    function testInitConstantsDirectorySeparator() {
        $this->assertDefined('API_DIRECTORY_SEPARATOR');
        $this->assertEqual(API_DIRECTORY_SEPARATOR, '/');
    }
    
    /**
     * Verify that api_init initializes the API_PATH_SEPARATOR
     * constant.
     */
    function testInitConstantsPathSeparator() {
        $this->assertDefined('API_PATH_SEPARATOR');
        $this->assertEqual(API_PATH_SEPARATOR, ':');
    }
    
    /**
     * Verify that api_init initializes the API_PROJECT_DIR
     * constant which points to the root of the current project.
     */
    function testInitConstantsProjectDir() {
        $this->assertDefined('API_PROJECT_DIR');
        $this->assertEqual(API_PROJECT_DIR, realpath(dirname(__FILE__).'/../../').'/');
        $this->assertEqual(substr(API_PROJECT_DIR, -1), '/',
            "API_PROJECT_DIR should end with a slash.");
        $this->assertNotEqual(substr(API_PROJECT_DIR, -2), '//',
            "API_PROJECT_DIR should not end with a double-slash.");
    }
    
    /**
     * Verify that api_init initializes the API_INCLUDE_DIR
     * constant which points to the `inc' directory of the current
     * project.
     */
    function testInitConstantsIncludeDir() {
        $this->assertDefined('API_INCLUDE_DIR');
        $this->assertEqual(API_INCLUDE_DIR, API_PROJECT_DIR.'inc/');
    }
    
    /**
     * Verify that api_init initializes the API_LIBS_DIR
     * constant which points to the Okapi root (which should
     * be in /inc/api of the project).
     */
    function testInitConstantsLibraryDir() {
        $this->assertDefined('API_LIBS_DIR');
        $this->assertEqual(API_LIBS_DIR, API_PROJECT_DIR.'inc/api/');
    }
    
    /**
     * Verify that api_init initializes the API_LOCAL_INCLUDE_DIR
     * constant which points to the library directory for the current
     * project (/localinc/).
     */
    function testInitConstantsLocalincDir() {
        $this->assertDefined('API_LOCAL_INCLUDE_DIR');
        $this->assertEqual(API_LOCAL_INCLUDE_DIR, API_PROJECT_DIR.'localinc/');
    }
    
    /**
      * Verify that api_init initializes the API_THEMES_DIR
      * constant which points to the directory where the XSLT stylesheets
      * are stored.
      */
    function testInitConstantsThemesDir() {
        $this->assertDefined('API_THEMES_DIR');
        $this->assertEqual(API_THEMES_DIR, API_PROJECT_DIR.'themes/');
    }
    
    /**
      * Verify that api_init initializes the API_WEBROOT
      * constant which points to the HTTP root of the current
      * subdomain.
      */
    function testInitConstantsWebroot() {
        $this->assertDefined('API_WEBROOT');
        $this->assertEqual(API_WEBROOT, 'http://demo.okapi.org/');
    }
    
    /**
      * Verify that api_init initializes the API_WEBROOT_STATIC
      * constant which points to the HTTP root of the static directory
      * of the current host.
      */
    function testInitConstantsWebrootStatic() {
        $this->assertDefined('API_WEBROOT_STATIC');
        $this->assertEqual(API_WEBROOT_STATIC, 'http://demo.okapi.org/static/');
    }

    /**
      * Verify that api_init initializes api_init::$apiPath which points
      * to the current URI inside the application.
      */
    function testInitPath() {
        $this->assertEqual(api_init::$path, '/the/command');
    }
    
    /**
     * Verify that POOL is initialized.
     */
    function testInitPool() {
        $this->assertTrue(isset($GLOBALS['POOL']), "POOL is not defined.");
        
        $pool = $GLOBALS['POOL'];
        $this->assertIsA($pool, 'api_pool');
        $this->assertIsA($pool->config, 'api_config');
    }

    /**
     * Verify that config can be used.
     */
    function testInitConfig() {
        $tmpdir = $GLOBALS['POOL']->config->tmpdir;
        $this->assertEqual($tmpdir, API_PROJECT_DIR . 'tmp/');
    }
    
    /**
     * Verify that the temp directory is created.
     */
    function testTempDir() {
        $tmpdir = $GLOBALS['POOL']->config->tmpdir;
        $this->assertEqual(API_TEMP_DIR, $tmpdir);
        $this->assertTrue(file_exists($tmpdir));
        $this->assertTrue(is_dir($tmpdir));
    }
    
    /**
     * Test that sld and tld are set correctly.
     */
    function testInitHost() {
        $this->assertEqual(api_init::$sld, 'demo');
        $this->assertEqual(api_init::$tld, 'okapi.org');
    }
    
    /**
     * Test host parsing for a configured host.
     */
    function testParseKnownHost() {
        $this->assertEqual(api_init::getHostConfig('demo.okapi.org'),
            array('sld' => 'demo', 'tld' => 'okapi.org', 'path' => '/'));
    }
    
    /**
     * Test host parsing for a host which is not known.
     */
    function testParseUnknownHost() {
        $this->assertEqual(api_init::getHostConfig('unknonw.okapi.ch'),
            null);
    }
    
    /**
     * Tests the controller configuration as returned by
     * api_init::getControllerConfig()
     */
    function testControllerConfig() {
        $this->assertEqual(api_init::getControllerConfig(),
            array(
                'basedir'              => API_PROJECT_DIR,
                'path'                 => api_init::$path,
                'commandconf'          => API_PROJECT_DIR."conf/commandmap.xml",
                'cachedir'             => API_TEMP_DIR,
                'themesdir'            => API_THEMES_DIR,
                'webroot'              => API_WEBROOT,
                'webrootStatic'        => API_WEBROOT_STATIC,
                'host'                 => 'demo',
            ));
    }
    
    /**
     * Test host parsing for a configured host with a prefix path.
     */
    function testParseHostWithPathPrefix() {
        $this->assertEqual(api_init::getHostConfig('pathdemo.okapi.org'),
            array('sld' => 'pathdemo', 'tld' => 'okapi.org', 'path' => '/xyz'));
    }

    /**
     * Helper to test if a constant is defined.
     */
    private function assertDefined($constant) {
        $this->assertTrue(defined($constant), "Constant $constant is not defined.");
    }
}
?>
