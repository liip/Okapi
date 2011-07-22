<?php
class HelperXpathTest extends api_testing_case_unit {
    /**
     * Check default namespaces
     */
    function testGetNamespacesDefault() {
        $namespaces = api_helpers_xpath::getNamespaces();
        $this->assertEqual(3, count($namespaces));

        $this->assertTrue(array_key_exists('x', $namespaces));
        $this->assertEqual($namespaces['x'], 'http://www.w3.org/1999/xhtml');
        $this->assertTrue(array_key_exists('xhtml', $namespaces));
        $this->assertEqual($namespaces['xhtml'], 'http://www.w3.org/1999/xhtml');
        $this->assertTrue(array_key_exists('i18n', $namespaces));
        $this->assertEqual($namespaces['i18n'], 'http://apache.org/cocoon/i18n/2.1');
    }

    /**
     * Check that dom is not available when required namespace for node is not set
     */
    function testNodeNotAvailableWithoutNamespaces() {
        $node = api_helpers_xpath::getNode($this->getDom(), '/urlset/url');
        $this->assertFalse($node instanceOf DOMElement);
    }

    /**
     * Check if a new inserted namespace is returned by calling the getter method
     */
    function testGetNamespaces() {
        api_helpers_xpath::setNamespaces(array(
            's' => 'http://www.sitemaps.org/schemas/sitemap/0.9'
        ));

        $namespaces = api_helpers_xpath::getNamespaces();
        $this->assertTrue(array_key_exists('s', $namespaces));
        $this->assertEqual($namespaces['s'], 'http://www.sitemaps.org/schemas/sitemap/0.9');
    }

    /**
     * Check that a node is callable when using the new inserted namespace
     */
    function testNodeAvailableWithNamespaces() {
        api_helpers_xpath::setNamespaces(array(
            's' => 'http://www.sitemaps.org/schemas/sitemap/0.9'
        ));
        $node = api_helpers_xpath::getNode($this->getDom(), '/s:urlset/s:url');
        $this->assertTrue($node instanceOf DOMElement);
    }

    /*
     * Return dom with a namespace example
     */
    protected function getDom() {
        $dom = new DOMDocument();
        $file = dirname(__FILE__) . '/../fixtures/unit_xpath_tests.xml';
        $this->assertTrue($dom->load($file), "Could not load fixture file.");
        $dom->xinclude();
        return $dom;
    }
}