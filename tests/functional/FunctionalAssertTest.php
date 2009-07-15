<?php
class FunctionalAssertTest extends api_testing_case_functional {
    
    function testNode() {
        $this->get('/helloworld');
        $this->assertNode('//h1');
    }
    
    function testNotNode() {
        $this->get('/helloworld');
        $this->assertNotNode('//h2');
    }
    
    function testText() {
        $this->get('/helloworld');
        $this->assertText('//h1', 'Hello World!');
    }
    
    function testAttribute() {
        $this->get('/helloworld');
        $this->assertAttribute('//h1@attr', 'Hulla World');
    }
    
    function testRedirect() {
        $this->expectRedirect('/helloworld', 'false', 'en');
        $this->get('/helloworld/bye');
        
    }
}