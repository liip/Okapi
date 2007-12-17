<?php
/**
 * Tests the api_model_queryinfo class which returns an XML representation
 * of the current request.
 */
class ModelQueryinfoTest extends OkapiTestCase {
    function testModel() {
        $_SERVER['HTTP_HOST'] = 'demo.okapi.org';
        $_SERVER["REQUEST_URI"] = '/mycommand/foo';
        $_GET = array('param1' => 'value1');
        $request = new api_request();
        $route = array('command' => 'mycommand', 'method' => 'foo');

        $model = new api_model_queryinfo($request, $route);
        $dom = $model->getDOM();
        
        $this->assertXPath($dom, '/queryinfo/query/param1', 'value1');
        $this->assertXPath($dom, '/queryinfo/requestURI', 'mycommand/foo?param1=value1');
        $this->assertXPath($dom, '/queryinfo/lang', 'en');
        $this->assertXPath($dom, '/queryinfo/command', 'mycommand');
        $this->assertXPath($dom, '/queryinfo/method', 'foo');
    }
}
?>
