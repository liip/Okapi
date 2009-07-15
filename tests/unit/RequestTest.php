<?php
class RequestTest extends api_testing_case_phpunit {
    function testInit() {
        $r = api_request::getInstance();
        $this->assertIsA($r, 'api_request');
    }

    /**
     * Test if the host name is correctly set from the HTTP
     * host header.
     */
    function testRequestHost() {
        $_SERVER['HTTP_HOST'] = 'demo.okapi.org';
        $r = api_request::getInstance(true);
        $this->assertEqual('demo.okapi.org', $r->getHost());
    }

    /**
     * Test if the subdomain is correctly parsed from the
     * HTTP host header.
     */
    function testSubdomain() {
        $_SERVER['HTTP_HOST'] = 'demo.okapi.org';
        $r = api_request::getInstance(true);
        $this->assertEqual('demo', $r->getSld());
    }

    /**
     * Test if the "top-level" domain is correctly parsed from the
     * HTTP host header.
     */
    function testTopdomain() {
        $_SERVER['HTTP_HOST'] = 'demo.okapi.org';
        $r = api_request::getInstance(true);
        $this->assertEqual('okapi.org', $r->getTld());
    }

    /**
     * Test if the path is correctly parsed from the URI.
     */
    function testRequestPath() {
        $_SERVER["REQUEST_URI"] = '/the/command';
        $r = api_request::getInstance(true);
        $this->assertEqual('/the/command', $r->getPath());
    }

    /**
     * Test if the path is correctly parsed and double-slashes
     * are removed.
     */
    function testRequestPathRemoveDoubleSlashes() {
        $_SERVER["REQUEST_URI"] = '/the//command';
        $r = api_request::getInstance(true);
        $this->assertEqual('/the/command', $r->getPath());
    }

    /**
     * Path must not include any URL parameters.
     */
    function testRequestPathWithoutParams() {
        $_SERVER["REQUEST_URI"] = '/the/command?XML=1';
        $r = api_request::getInstance(true);
        $this->assertEqual('/the/command', $r->getPath());
    }

    /**
     * Test if the path is correctly parsed from the URI for a
     * host which defines a path prefix.
     */
    function testRequestPathWithPrefix() {
        $_SERVER['HTTP_HOST'] = 'pathdemo.okapi.org';
        $_SERVER["REQUEST_URI"] = '/xyz/command/foo/bar';
        $r = api_request::getInstance(true);
        $this->assertEqual('/command/foo/bar', $r->getPath());
    }

    /**
     * Test if the filename is correctly parsed from the path.
     */
    function testFilename() {
        $_SERVER["REQUEST_URI"] = '/document.pdf';
        $r = api_request::getInstance(true);
        $this->assertEqual($r->getFilename(), 'document.pdf');
    }
    
    /**
     * Test if the filename is correctly parsed from the path.
     * The last component of the path is taken.
     */
    function testFilenameHierarchy() {
        $_SERVER["REQUEST_URI"] = '/subfolder/document.pdf';
        $r = api_request::getInstance(true);
        $this->assertEqual($r->getFilename(), 'document.pdf');
    }
    
    /**
     * Test if the filename is correctly parsed from the path.
     * An extension is required, so in this case an empty filename is
     * returned.
     */
    function testFilenameExtension() {
        $_SERVER["REQUEST_URI"] = '/document';
        $r = api_request::getInstance(true);
        $this->assertEqual($r->getFilename(), '');
    }
    
    /**
     * Test if the filename is correctly parsed from the path.
     * An extension is required, so in this case an empty filename is
     * returned.
     */
    function testFilenameExtensionDot() {
        $_SERVER["REQUEST_URI"] = 'document.';
        $r = api_request::getInstance(true);
        $this->assertEqual($r->getFilename(), '');
    }

    /**
     * Test that language parsing works correctly for a path which does
     * not contain any language (the default language must be used).
     */
    function testLangDefault() {
        $r = api_request::getInstance(true);
        $this->assertEqual($r->getLang(), 'en');
    }
    
    /**
     * Read the language from the path. This case tests reading the
     * language when it's the default language.
     */
    function testLangPath() {
        $_SERVER["REQUEST_URI"] = '/en/the/command';
        $r = api_request::getInstance(true);
        $this->assertEqual($r->getLang(), 'en');
        $this->assertEqual($r->getPath(), '/the/command');
    }
    
    /**
     * Read the language from the path. This case tests reading the
     * language when it's not the default language.
     */
    function testLangPathGerman() {
        $_SERVER["REQUEST_URI"] = '/de/the/command';
        $r = api_request::getInstance(true);
        $this->assertEqual($r->getLang(), 'de');
        $this->assertEqual($r->getPath(), '/the/command');
    }
    
    /**
     * Read the language from the path. This case verifies
     * only known languages are used.
     */
    function testLangPathSpanish() {
        $_SERVER["REQUEST_URI"] = '/es/the/command';
        $r = api_request::getInstance(true);
        $this->assertEqual($r->getLang(), 'en');
        $this->assertEqual($r->getPath(), '/es/the/command');
    }
    
    /**
     * Tests if the output languages are correctly parsed from the
     * configuration file.
     */
    function testLanguages() {
        $r = api_request::getInstance(true);
        $this->assertEqual($r->getLanguages(),
            array('en', 'de'));
    }
    
    /**
     * Tests if the default language is correctly parsed from the
     * configuration file.
     */
    function testDefaultLanguage() {
        $r = api_request::getInstance(true);
        $this->assertEqual($r->getDefaultLanguage(), 'en');
    }
    
    /**
     * Tests if the parameters are correctly set from GET array.
     */
    function testParametersGet() {
        $_GET = array('path' => 'mypath', 'question' => 'does it work?');
        $r = api_request::getInstance(true);
        $this->assertEqual($r->getParameters()->get(), array(
            'path' => 'mypath',
            'question' => 'does it work?'));
    }
    
    /**
     * Tests if a normal URL is returned correctly
     */
    function testRequestURL() {
        $_SERVER['REQUEST_URI'] = '/the/foobar';
        $r = api_request::getInstance(true);
        $this->assertEqual($r->getUrl(), 'http://demo.okapi.org/en/the/foobar');
    }
    
    /**
     * Tests if a normal client IP is returned correctly.
     */
    function testClientIp() {
        $_SERVER['REMOTE_ADDR'] = '172.10.12.15';
        $r = api_request::getInstance(true);
        $this->assertEqual($r->getClientIp(), '172.10.12.15');
    }
    
    /**
     * Tests if a cluster client IP is returned correctly.
     */
    function testClientIpClutster() {
        $_SERVER['REMOTE_ADDR'] = '195.210.10.20';
        $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'] = '172.19.10.130';
        $r = api_request::getInstance(true);
        $this->assertEqual($r->getClientIp(), '172.19.10.130');
    }
    
    /**
     * Tests if a HTTPS URL is returned correctly
     */
    // function testRequestURLWithSSL() {
    //     $_SERVER['REQUEST_URI'] = '/the/foobar';
    //     $_SERVER['HTTPS'] = 'on';
    //     $_SERVER['SERVER_PORT'] = 443;
    //     $r = api_request::getInstance(true);
    //     $this->assertEqual($r->getUrl(), 'https://demo.okapi.org/en/the/foobar');
    // }
}
?>
