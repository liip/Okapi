<?php
/**
 * Tests the api_helpers_string class.
 */
class HelperStringTest extends api_testing_case_unit {
    /**
     * Asserts the contract of api_helpers_string::escapeJSValue().
     */
    function testEscapeJSValueAllTogether() {
        $in = "\xe2\x80\xa8some\n random'string'\r for this\xe2\x80\xA9 thingy.";
        $out = "some\\n random\\'string\\'\\r for this thingy.";
        $this->helperAssertEscapeJSValue($in, $out);
    }
    
    function testEscapeJSValueNewLinesR() {
        $in = "some random string\r for this thingy.";
        $out = "some random string\\r for this thingy.";
        $this->helperAssertEscapeJSValue($in, $out);
    }
    
    function testEscapeJSValueNewLinesN() {
        $in = "some random string\n for this thingy.";
        $out = "some random string\\n for this thingy.";
        $this->helperAssertEscapeJSValue($in, $out);
    }
    
    function testEscapeJSValueApostrophe() {
        $in = "some random 'string' for this thingy.";
        $out = "some random \'string\' for this thingy.";
        $this->helperAssertEscapeJSValue($in, $out);
    }
    
    function testEscapeJSValueUnicodeLineSeparator() {
        $in = "Hallo dies ist ein\xe2\x80\xa8 Test";
        $out = "Hallo dies ist ein Test";
        $this->helperAssertEscapeJSValue($in, $out);
    }

    function testEscapeJSValueUnicodeParagraphSeparator() {
        $in = "Hallo dies ist ein\xe2\x80\xA9 Test";
        $out = "Hallo dies ist ein Test";
        $this->helperAssertEscapeJSValue($in, $out);
    }
    
    function helperAssertEscapeJSValue($desc, $expected) {
        $this->assertEqual(api_helpers_string::escapeJSValue($desc), $expected);
    }
}
