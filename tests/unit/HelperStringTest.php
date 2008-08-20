<?php
/**
 * Tests the api_helpers_string class.
 */
class HelperStringTest extends OkapiTestCase {
    /**
     * Asserts the contract of api_helpers_string::escapeJSValue().
     */
    function testEscapeJSValue() {
        $in = "some\n random'string'\r for this thingy.";
        $out = "some\\n random\\'string\\'\\r for this thingy.";
        $this->assertEqual(
            api_helpers_string::escapeJSValue($in),
            $out
        );
    }
}
