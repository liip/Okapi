<?php
/**
 * Tests the api_helpers_class class.
 */
class HelperClassTest extends api_testing_case_phpunit {
    /**
     * Test that api_helpers_class::getBasename() works for strings.
     */
    function testGetBasenameFromString() {
        $this->assertEqual('class', api_helpers_class::getBasename('my_test_class'));
        $this->assertEqual('first', api_helpers_class::getBasename('first'));
    }

    /**
     * Test that api_helpers_class::getBasename() works for an object.
     */
    function testGetBasenameFromObject() {
        $this->assertEqual('HelperClassTest', api_helpers_class::getBasename($this));
    }
}
?>
