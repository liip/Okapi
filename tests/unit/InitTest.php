<?php
/**
 * Tests the api_init class which handles initialisation of the
 * API. Sets constants, variables, etc.
 */
class InitTest extends api_testing_case_phpunit {
    function setUp() {
        $_SERVER['HTTP_HOST'] = 'demo.okapi.org';
        $_SERVER["REQUEST_URI"] = '/the/command';
        $_GET = array('path' => 'mypath', 'question' => 'does it work?');
        api_init::start();
        api_request::getInstance(true);
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
     * Verify that config can be used.
     */
    function testInitConfig() {
        $tmpdir = api_config::getInstance()->tmpdir;
        $this->assertEqual($tmpdir, API_PROJECT_DIR . 'tmp/');
    }
    
    /**
     * Verify that the temp directory is created.
     */
    function testTempDir() {
        $tmpdir = api_config::getInstance()->tmpdir;
        $this->assertEqual(API_TEMP_DIR, $tmpdir);
        $this->assertTrue(file_exists($tmpdir));
        $this->assertTrue(is_dir($tmpdir));
    }
    
    /**
     * Test host parsing for a configured host.
     */
    function testParseKnownHost() {
        $this->assertEqual(api_init::getHostConfig('demo.okapi.org'),
            array('host' => 'demo',
                  'sld' => 'demo', 'tld' => 'okapi.org',
                  'path' => '/'));
    }
    
    /**
     * Test host parsing for a host which is not known.
     */
    function testParseUnknownHost() {
        $this->assertEqual(api_init::getHostConfig('unknonw.okapi.ch'),
            null);
    }
    
    /**
     * Test host configuration without a tld but an sld. The TLD
     * must be calculated automatically.
     */
    function testHostconfigWithoutTld() {
        $this->assertEqual(api_init::getHostConfig('notld.okapi.org'),
            array('host' => 'notld',
                  'sld'  => 'notld',
                  'tld'  => 'okapi.org',
                  'path' => '/'));
    }
    
    /**
     * Test host parsing for a configured host with a prefix path.
     */
    function testParseHostWithPathPrefix() {
        $this->assertEqual(api_init::getHostConfig('pathdemo.okapi.org'),
            array('host' => 'pathdemo.okapi',
                  'sld' => 'pathdemo', 'tld' => 'okapi.org',
                  'path' => '/xyz'));
    }
    
    /**
     * Test host parsing for a host with a wildcard.
     */
    function testParseWildcardHostSimple() {
        $this->assertEqual(api_init::getHostConfig('ssoa'),
            array('host' => 'ssoa',
                  'tld' => null, 'sld' => null,
                  'path' => '/ssoa/'));
    }

    /**
     * Helper to test if a constant is defined.
     */
    private function assertDefined($constant) {
        $this->assertTrue(defined($constant), "Constant $constant is not defined.");
    }
}
?>
