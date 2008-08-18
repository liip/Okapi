<?php
/**
 * Tests the api_model_queryinfo class which returns an XML representation
 * of the current request.
 */
class ModelQueryinfoTest extends OkapiTestCase {
    function setUp() {
        parent::setUp();
        $_SERVER['HTTP_HOST'] = 'demo.okapi.org';
        $_SERVER["REQUEST_URI"] = '/mycommand/foo';
    }
    
    function testModel() {
        $_GET = array('param1' => 'value1');
        $request = api_request::getInstance(true);
        $route = array('command' => 'mycommand', 'method' => 'foo');

        $model = new api_model_queryinfo($request, $route);
        $dom = $model->getDOM();
        
        $this->assertXPath($dom, '/queryinfo/query/param1', 'value1');
        $this->assertXPath($dom, '/queryinfo/requestURI', 'mycommand/foo?param1=value1');
        $this->assertXPath($dom, '/queryinfo/lang', 'en');
        $this->assertXPath($dom, '/queryinfo/command', 'mycommand');
        $this->assertXPath($dom, '/queryinfo/method', 'foo');
    }
    
    function testArrayParams() {
        $_GET = array('param1' => array('foo', 'bar'));
        $request = api_request::getInstance(true);
        $route = array('command' => 'mycommand', 'method' => 'foo');
        
        $model = new api_model_queryinfo($request, $route);
        $dom = $model->getDOM();
        $this->assertXPath($dom, '/queryinfo/requestURI', 'mycommand/foo?param1%5B0%5D=foo&param1%5B1%5D=bar');
    }
}
