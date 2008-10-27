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

    function testFactory() {
        // in inc
        $model = api_model_factory::get('array', Array('foo'));
        $this->assertIsA($model, 'api_model_array');
        // in localinc
        $anotherModel = api_model_factory::get('somemodel');
        $this->assertIsA($anotherModel, 'api_model_somemodel');
    }

    function testFactoryNamespace() {
        $model = api_model_factory::get('test', null, 'bar');
        $this->assertIsA($model, 'bar_model_test');
    }
}
