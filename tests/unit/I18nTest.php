<?php
/**
 * Tests the api_i18n class which accepts a DOM with strings in the
 * i18n namespace and translates all strings based on content from
 * the language files.
 */
class I18nTest extends api_testing_case_unit {
    function setUp() {
        $_SERVER['HTTP_HOST'] = 'demo.okapi.org';
        $_SERVER["REQUEST_URI"] = '/the/command';
        $_GET = array('path' => 'mypath', 'question' => 'does it work?');
        api_init::start();
        api_request::getInstance(true);
        
        $_SERVER["REQUEST_URI"] = '/command/mymethod';
        $this->request = api_request::getInstance(true);
    }

    function testInit() {
        $de = api_i18n::getInstance('de');
        $this->assertIsA($de, 'api_i18n');

        $fr = api_i18n::getInstance('fr');
        $this->assertIsA($fr, 'api_i18n');
        
        $this->assertNotEqual($de, $fr);
    }

    /**
     * Get the translation string.
     */
    function testString() {
        $this->assertEqual('Link', api_i18n::getMessage('en', 'associate'));
        // Must also work case insensitive
        $this->assertEqual('Link', api_i18n::getMessage('en', 'Associate'));
        $this->assertEqual('Link', api_i18n::getMessage('en', 'AssOCiate'));
    }

    /**
     * Simple <i18n:text> replacement.
     */
    function testText() {
        $i18n = api_i18n::getInstance('en');
        $doc = DOMDocument::loadXML('<root><i18n:text xmlns:i18n="http://apache.org/cocoon/i18n/2.1">Associate</i18n:text></root>');
        $i18n->i18n($doc);
        
        $this->assertEqual('root', $doc->documentElement->tagName);
        $this->assertEqual('Link', $doc->documentElement->nodeValue);
        $this->assertEqual("<?xml version=\"1.0\"?>\n<root>Link</root>\n", $doc->saveXML());
    }
    
    /**
     * <i18n:translate> replacement.
     */
    function testTranslate() {
        $i18n = api_i18n::getInstance('en');
        $doc = DOMDocument::loadXML('
            <root><i18n:translate xmlns:i18n="http://apache.org/cocoon/i18n/2.1">
                    <i18n:text>AutocompletionRemaining</i18n:text>
                    <i18n:param name="remain">99</i18n:param>
                </i18n:translate></root>');
        $i18n->i18n($doc);
        
        $this->assertEqual('root', $doc->documentElement->tagName);
        $this->assertEqual('Other 99 results omitted. Limit search further.', $doc->documentElement->nodeValue);
        $this->assertEqual("<?xml version=\"1.0\"?>\n<root>Other 99 results omitted. Limit search further.</root>\n", $doc->saveXML());
    }

    /**
     * <i18n:translate> replacement where the param value itself
     * is also translated using i18n:text.
     */
    function testTranslateI18nParam() {
        $i18n = api_i18n::getInstance('en');
        $doc = DOMDocument::loadXML('
            <root><i18n:translate xmlns:i18n="http://apache.org/cocoon/i18n/2.1">
                    <i18n:text>AutocompletionRemaining</i18n:text>
                    <i18n:param name="remain"><i18n:text>Associate</i18n:text></i18n:param>
                </i18n:translate></root>');
        $i18n->i18n($doc);
        
        $this->assertEqual('root', $doc->documentElement->tagName);
        $this->assertEqual('Other Link results omitted. Limit search further.', $doc->documentElement->nodeValue);
        $this->assertEqual("<?xml version=\"1.0\"?>\n<root>Other Link results omitted. Limit search further.</root>\n", $doc->saveXML());
    }

    /**
     * <i18n:cleartext> replacement with no append or prepend.
     */
    function testTranslateI18nCleartext() {
        $i18n = api_i18n::getInstance('en');
        $doc = DOMDocument::loadXML('
            <root><i18n:cleartext xmlns:i18n="http://apache.org/cocoon/i18n/2.1">
                    <i18n:text>ClassifiedDeactivate</i18n:text>
                </i18n:cleartext></root>');
        $i18n->i18n($doc);
        
        $this->assertEqual('root', $doc->documentElement->tagName);
        $this->assertEqual('Deactivate listing', $doc->documentElement->nodeValue);
        $this->assertEqual("<?xml version=\"1.0\"?>\n<root>Deactivate listing</root>\n", $doc->saveXML());
    }

    /**
     * <i18n:cleartext> replacement with a string appended.
     */
    function testTranslateI18nCleartextAppendString() {
        $i18n = api_i18n::getInstance('en');
        $doc = DOMDocument::loadXML('
            <root><i18n:cleartext xmlns:i18n="http://apache.org/cocoon/i18n/2.1">
                    <i18n:text>ClassifiedDeactivate</i18n:text>
                    <i18n:append> appended.</i18n:append>
                </i18n:cleartext></root>');
        $i18n->i18n($doc);
        
        $this->assertEqual('root', $doc->documentElement->tagName);
        $this->assertEqual('Deactivate listing appended.', $doc->documentElement->nodeValue);
        $this->assertEqual("<?xml version=\"1.0\"?>\n<root>Deactivate listing appended.</root>\n", $doc->saveXML());
    }

    /**
     * <i18n:cleartext> replacement with a string prepended.
     */
    function testTranslateI18nCleartextPrependString() {
        $i18n = api_i18n::getInstance('en');
        $doc = DOMDocument::loadXML('
            <root><i18n:cleartext xmlns:i18n="http://apache.org/cocoon/i18n/2.1">
                    <i18n:text>ClassifiedDeactivate</i18n:text>
                    <i18n:prepend> - this one is prepended - </i18n:prepend>
                </i18n:cleartext></root>');
        $i18n->i18n($doc);
        
        $this->assertEqual('root', $doc->documentElement->tagName);
        $this->assertEqual(' - this one is prepended - Deactivate listing', $doc->documentElement->nodeValue);
        $this->assertEqual("<?xml version=\"1.0\"?>\n<root> - this one is prepended - Deactivate listing</root>\n", $doc->saveXML());
    }

    /**
     * <i18n:cleartext> replacement with a string appended and prepended.
     */
    function testTranslateI18nCleartextAppendPrependString() {
        $i18n = api_i18n::getInstance('en');
        $doc = DOMDocument::loadXML('
            <root><i18n:cleartext xmlns:i18n="http://apache.org/cocoon/i18n/2.1">
                    <i18n:text>ClassifiedDeactivate</i18n:text>
                    <i18n:append> (suffix)</i18n:append>
                    <i18n:prepend>prefix: </i18n:prepend>
                </i18n:cleartext></root>');
        $i18n->i18n($doc);
        
        $this->assertEqual('root', $doc->documentElement->tagName);
        $this->assertEqual('prefix: Deactivate listing (suffix)', $doc->documentElement->nodeValue);
        $this->assertEqual("<?xml version=\"1.0\"?>\n<root>prefix: Deactivate listing (suffix)</root>\n", $doc->saveXML());
    }

    /**
     * <i18n:cleartext> replacement with a value appended which is
     * itself internationalized using i18n:text.
     */
    function testTranslateI18nCleartextAppendI18n() {
        $i18n = api_i18n::getInstance('en');
        $doc = DOMDocument::loadXML('
            <root><i18n:cleartext xmlns:i18n="http://apache.org/cocoon/i18n/2.1">
                    <i18n:text>ClassifiedDeactivate</i18n:text>
                    <i18n:append><i18n:text>chf</i18n:text></i18n:append>
                </i18n:cleartext></root>');
        $i18n->i18n($doc);
        
        $this->assertEqual('root', $doc->documentElement->tagName);
        $this->assertEqual('Deactivate listingCHF', $doc->documentElement->nodeValue);
        $this->assertEqual("<?xml version=\"1.0\"?>\n<root>Deactivate listingCHF</root>\n", $doc->saveXML());
    }

    /**
     * <i18n:cleartext> replacement with a value appended which is
     * itself internationalized using i18n:text. The appendix
     * is a combination of a string and the i18n value.
     */
    function testTranslateI18nCleartextAppendI18nAndString() {
        $i18n = api_i18n::getInstance('en');
        $doc = DOMDocument::loadXML('
            <root><i18n:cleartext xmlns:i18n="http://apache.org/cocoon/i18n/2.1">
                    <i18n:text>ClassifiedDeactivate</i18n:text>
                    <i18n:append> <i18n:text>chf</i18n:text></i18n:append>
                </i18n:cleartext></root>');
        $i18n->i18n($doc);
        
        $this->assertEqual('root', $doc->documentElement->tagName);
        $this->assertEqual('Deactivate listing CHF', $doc->documentElement->nodeValue);
        $this->assertEqual("<?xml version=\"1.0\"?>\n<root>Deactivate listing CHF</root>\n", $doc->saveXML());
    }

    /**
     * <i18n:cleartext> replacement with a value prepended which is
     * itself internationalized using i18n:text.
     */
    function testTranslateI18nCleartextPrependI18n() {
        $i18n = api_i18n::getInstance('en');
        $doc = DOMDocument::loadXML('
            <root><i18n:cleartext xmlns:i18n="http://apache.org/cocoon/i18n/2.1">
                    <i18n:text>ClassifiedDeactivate</i18n:text>
                    <i18n:prepend><i18n:text>chf</i18n:text></i18n:prepend>
                </i18n:cleartext></root>');
        $i18n->i18n($doc);
        
        $this->assertEqual('root', $doc->documentElement->tagName);
        $this->assertEqual('CHFDeactivate listing', $doc->documentElement->nodeValue);
        $this->assertEqual("<?xml version=\"1.0\"?>\n<root>CHFDeactivate listing</root>\n", $doc->saveXML());
    }

    /**
     * <i18n:cleartext> replacement with a value prepended which is
     * itself internationalized using i18n:text. The prefix
     * is a combination of a string and the i18n value.
     */
    function testTranslateI18nCleartextPrependI18nAndString() {
        $i18n = api_i18n::getInstance('en');
        $doc = DOMDocument::loadXML('
            <root><i18n:cleartext xmlns:i18n="http://apache.org/cocoon/i18n/2.1">
                    <i18n:text>ClassifiedDeactivate</i18n:text>
                    <i18n:prepend><i18n:text>chf</i18n:text>: </i18n:prepend>
                </i18n:cleartext></root>');
        $i18n->i18n($doc);
        
        $this->assertEqual('root', $doc->documentElement->tagName);
        $this->assertEqual('CHF: Deactivate listing', $doc->documentElement->nodeValue);
        $this->assertEqual("<?xml version=\"1.0\"?>\n<root>CHF: Deactivate listing</root>\n", $doc->saveXML());
    }
    
    /**
     * <i18n:text> can return XML fragments if asXML="yes" is used in the
     * language file.
     */
    function testGetXMLFragment() {
        $i18n = api_i18n::getInstance('en');
        $doc = DOMDocument::loadXML('<div><i18n:text xmlns:i18n="http://apache.org/cocoon/i18n/2.1">asxmltest</i18n:text></div>');
        $i18n->i18n($doc);
        
        $this->assertEqual('div', $doc->documentElement->tagName);
        $this->assertEqual("<?xml version=\"1.0\"?>\n<div>Some <strong>XML</strong> here.</div>\n", $doc->saveXML());
    }
}
?>
