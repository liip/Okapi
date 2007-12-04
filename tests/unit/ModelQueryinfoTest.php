<?php
/**
 * Tests the api_model_queryinfo class which returns an XML representation
 * of the current request.
 */
class ModelQueryinfoTest extends OkapiTestCase {
    function testModel() {
        $_SERVER['HTTP_HOST'] = 'demo.okapi.org';
        $_SERVER["REQUEST_URI"] = '/command/foo';
        $_GET = array('param1' => 'value1');
        $request = new api_request();
        $commands = new api_commandmap($request);

        $model = new api_model_queryinfo($request, $commands);
        $dom = $model->getDOM();
        
        $this->assertXPath($dom, '/queryinfo/query/param1', 'value1');
        $this->assertXPath($dom, '/queryinfo/requestURI', 'command/foo?param1=value1');
        $this->assertXPath($dom, '/queryinfo/lang', 'en');
        $this->assertXPath($dom, '/queryinfo/method', 'foo');
        $this->assertXPath($dom, '/queryinfo/directivePath', '/command/');
        $this->assertXPath($dom, '/queryinfo/directiveHost', 'demo');
    }
}
?>
