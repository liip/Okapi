<?php
/**
 * Tests the api_model_queryinfo class which returns an XML representation
 * of the current request.
 */
class ModelQueryinfoTest extends api_testing_case_unit {
    function setUp() {
        parent::setUp();
        $_SERVER['HTTP_HOST'] = 'demo.okapi.org';
        $_SERVER["REQUEST_URI"] = '/mycommand/foo';
        $_GET = array();
    }
    
    function tearDown() {
        $_GET = array();
    }
    
    function testModel() {
        $_GET = array('param1' => 'value1');
        $request = api_request::getInstance(true);
        $route = array('command' => 'mycommand', 'method' => 'foo');

        $model = new api_model_queryinfo($request, $route);
        $dom = $model->getDOM();
        
        $this->assertText($dom, '/queryinfo/query/param1', 'value1');
        $this->assertText($dom, '/queryinfo/requestURI', 'mycommand/foo?param1=value1');
        $this->assertText($dom, '/queryinfo/lang', 'en');
        $this->assertText($dom, '/queryinfo/command', 'mycommand');
        $this->assertText($dom, '/queryinfo/method', 'foo');
    }
    
    function testArrayParams() {
        $_GET = array('param1' => array('foo', 'bar'));
        $request = api_request::getInstance(true);
        $route = array('command' => 'mycommand', 'method' => 'foo');
        
        $model = new api_model_queryinfo($request, $route);
        $dom = $model->getDOM();
        $this->assertText($dom, '/queryinfo/requestURI', 'mycommand/foo?param1%5B0%5D=foo&param1%5B1%5D=bar');
    }
    
    function testRequestUriWithoutParams() {
        $request = api_request::getInstance(true);
        $route = array('command' => 'mycommand', 'method' => 'foo');
        
        $model = new api_model_queryinfo($request, $route);
        $dom = $model->getDOM();
        $this->assertText($dom, '/queryinfo/requestURI', 'mycommand/foo');
    }
    
    function testWrongParams() {
        $_GET = array('123' => 'wrongParam', 'param2' => 'validParam');
        $request = api_request::getInstance(true);
        $route = array('command' => 'mycommand', 'method' => 'foo');
        
        $model = new api_model_queryinfo($request, $route);
        $dom = $model->getDOM();
        
        $this->assertNotNode($dom, '/queryinfo/query/123');
        $this->assertText($dom, '/queryinfo/query/param2', 'validParam');
        $this->assertText($dom, '/queryinfo/requestURI', 'mycommand/foo?0=wrongParam&param2=validParam');
        $this->assertText($dom, '/queryinfo/lang', 'en');
        $this->assertText($dom, '/queryinfo/command', 'mycommand');
        $this->assertText($dom, '/queryinfo/method', 'foo');
    }
}
