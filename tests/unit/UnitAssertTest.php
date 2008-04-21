<?php

class UnitAssertTest extends api_testing_case_unit {
    protected $dom = null;
    
    public function setUp() {
        parent::setUp();
        $this->dom = $this->loadFixtureXML('unit_assert_tests.xml');
    }
    
    function testNode() {
        $this->assertNode($this->dom, '/dummyXML/singleText');
    }
    
    function testNotNode() {
        $this->assertNotNode($this->dom, '/dummyXML/thisDoesntExist');
    }
    
    function testText() {
        $this->assertText($this->dom, '//singleText', 'This is a test', 'Blup');
    }
    
    function testTexts() {
        $this->assertTexts($this->dom, '//multiText', array('Those are tests!', 'Those are more tests!'));
    }
    
    function testAttribute() {
        $this->assertAttribute($this->dom, '//attributeNode@attr', 'Friendly Attribute');
    }
}