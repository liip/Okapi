<?php
class RequestTest extends UnitTestCase {
    function testInit() {
        $r = new api_request();
        $this->assertIsA($r, 'api_request');
    }

    /**
     * Test if the host name is correctly set from the HTTP
     * host header.
     */
    function testRequestHost() {
        $_SERVER['HTTP_HOST'] = 'demo.okapi.org';
        $r = new api_request();
        $this->assertEqual('demo.okapi.org', $r->getHost());
    }

    /**
     * Test if the path is correctly parsed from the URI.
     */
    function testRequestPath() {
        $_SERVER["REQUEST_URI"] = '/the/command';
        $r = new api_request();
        $this->assertEqual('/the/command', $r->getPath());
    }

    /**
     * Test if the filename is correctly parsed from the path.
     */
    function testFilename() {
        $_SERVER["REQUEST_URI"] = '/document.pdf';
        $r = new api_request();
        $this->assertEqual($r->getFilename(), 'document.pdf');
    }
    
    /**
     * Test if the filename is correctly parsed from the path.
     * The last component of the path is taken.
     */
    function testFilenameHierarchy() {
        $_SERVER["REQUEST_URI"] = '/subfolder/document.pdf';
        $r = new api_request();
        $this->assertEqual($r->getFilename(), 'document.pdf');
    }
    
    /**
     * Test if the filename is correctly parsed from the path.
     * An extension is required, so in this case an empty filename is
     * returned.
     */
    function testFilenameExtension() {
        $_SERVER["REQUEST_URI"] = '/document';
        $r = new api_request();
        $this->assertEqual($r->getFilename(), '');
    }
    
    /**
     * Test if the filename is correctly parsed from the path.
     * An extension is required, so in this case an empty filename is
     * returned.
     */
    function testFilenameExtensionDot() {
        $_SERVER["REQUEST_URI"] = 'document.';
        $r = new api_request();
        $this->assertEqual($r->getFilename(), '');
    }

    /**
     * Test that language parsing works correctly for a path which does
     * not contain any language (the default language must be used).
     */
    function testLangDefault() {
        $r = new api_request();
        $this->assertEqual($r->getLang(), 'en');
    }
    
    /**
     * Read the language from the path. This case tests reading the
     * language when it's the default language.
     */
    function testLangPath() {
        $_SERVER["REQUEST_URI"] = '/en/the/command';
        $r = new api_request();
        $this->assertEqual($r->getLang(), 'en');
        $this->assertEqual($r->getPath(), '/the/command');
    }
    
    /**
     * Read the language from the path. This case tests reading the
     * language when it's not the default language.
     */
    function testLangPathGerman() {
        $_SERVER["REQUEST_URI"] = '/de/the/command';
        $r = new api_request();
        $this->assertEqual($r->getLang(), 'de');
        $this->assertEqual($r->getPath(), '/the/command');
    }
    
    /**
     * Read the language from the path. This case verifies
     * only known languages are used.
     */
    function testLangPathSpanish() {
        $_SERVER["REQUEST_URI"] = '/es/the/command';
        $r = new api_request();
        $this->assertEqual($r->getLang(), 'en');
        $this->assertEqual($r->getPath(), '/es/the/command');
    }
    
    /**
     * Tests if the output languages are correctly parsed from the
     * configuration file.
     */
    function testLanguages() {
        $r = new api_request();
        $this->assertEqual($r->getLanguages(),
            array('en', 'de'));
    }
    
    /**
     * Tests if the default language is correctly parsed from the
     * configuration file.
     */
    function testDefaultLanguage() {
        $r = new api_request();
        $this->assertEqual($r->getDefaultLanguage(), 'en');
    }
    
    /**
     * Tests if the parameters are correctly set from GET array.
     */
    function testParametersGet() {
        $_GET = array('path' => 'mypath', 'question' => 'does it work?');
        $r = new api_request();
        $this->assertEqual($r->getParameters(), array(
            'path' => 'mypath',
            'question' => 'does it work?'));
    }
}
?>
