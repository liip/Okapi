<?php
/**
 * Tests the api_model_http class which returns a DOM of an XML response
 * retrieved via HTTP.
 *
 * This test will fail if you don't have internet access.
 */
class ModelHttpTest extends OkapiTestCase {
    function testModel() {
        // Get CURL object and execute it
        $model = new api_model_http('http://extapi.local.ch/0/cities.xml?q=Ol');
        $curl = $model->getCurlObject();
        curl_exec($curl);
        
        $dom = $model->getDOM();
        $this->assertXPath($dom, '/response/@status', 'ok');
        $this->assertXPath($dom, '/response/cities/city[1]/@name', 'Olten');
    }

    /**
     * Test if the model execute the CURL command itself if no controller
     * is available to do that.
     */
    function testModelNoCurl() {
        // Get CURL object and execute it
        $model = new api_model_http('http://extapi.local.ch/0/cities.xml?q=Ol');
        $dom = $model->getDOM();
        $this->assertXPath($dom, '/response/@status', 'ok');
        $this->assertXPath($dom, '/response/cities/city[1]/@name', 'Olten');
    }
}
?>
