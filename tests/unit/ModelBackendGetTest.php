<?php
/**
 * Tests the api_model_backend_get class which returns a DOM of
 * an XML response retrieved via HTTP. The URL of the request
 * is determined via the configuration file.
 *
 * This test will fail if you don't have internet access.
 */
class ModelBackendGetTest extends OkapiTestCase {
    function testModel() {
        // Get CURL object and execute it
        $model = new api_model_backend_get('extapi', 'cities', array('q' => 'Ol'));
        $curl = $model->getCurlObject();
        curl_exec($curl);
        
        $dom = $model->getDOM();
        $this->assertXPath($dom, '/response/@status', 'ok');
        $this->assertXPath($dom, '/response/@server', 'extapi');
        $this->assertXPath($dom, '/response/@command', 'cities');
        $this->assertXPath($dom, '/response/cities/city[1]/@name', 'Olten');
    }

    function testModelInvalidCommand() {
        $this->expectException(new api_exception_Backend(api_exception::THROW_FATAL,
            array('server' => 'extapi', 'command' => 'nonexisting'),
            0,
            "Command extapi/nonexisting not found in backend configuration."));

        $model = new api_model_backend_get('extapi', 'nonexisting');
    }
    
    function testNodeAttributes() {
        // Get CURL object and execute it
        $model = new api_model_backend_get('extapi', 'cities', array('q' => 'Ol'));
        $model->setNodeAttributes(array('test' => 'abc'));
        
        $dom = $model->getDOM();
        $this->assertXPath($dom, '/response/@status', 'ok');
        $this->assertXPath($dom, '/response/@server', 'extapi');
        $this->assertXPath($dom, '/response/@command', 'cities');
        $this->assertXPath($dom, '/response/@test', 'abc');
        $this->assertXPath($dom, '/response/cities/city[1]/@name', 'Olten');
    }
}
?>
