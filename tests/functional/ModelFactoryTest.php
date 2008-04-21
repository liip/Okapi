<?php

class ModelFactoryTest extends api_testing_case_functional {
    
    function testRealModel() {
        $this->get('/helloworld');
        $this->assertText('//p', 'world');
    }
    
    function testReplacedModel() {
        api_model_factory::setFixture('array', 'helloworld.xml');
        $this->get('/helloworld');
        $this->assertText('//p', 'world2');
    }
}