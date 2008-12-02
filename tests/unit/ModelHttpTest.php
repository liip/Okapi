<?php
/**
 * Tests the api_model_http class which returns a DOM of an XML response
 * retrieved via HTTP.
 *
 * This test will fail if you don't have internet access.
 */
class ModelHttpTest extends api_testing_case_unit {
    function testModel() {
        // Get CURL object and execute it
        $model = new api_model_http('http://extapi.local.ch/0/cities.xml?q=Ol');
        $curl = $model->getCurlObject();
        curl_exec($curl);
        
        $dom = $model->getDOM();
        $this->assertText($dom, '/response/@status', 'ok');
        $this->assertText($dom, '/response/cities/city[1]/@name', 'Olten');
    }

    /**
     * Test if the model execute the CURL command itself if no controller
     * is available to do that.
     */
    function testModelNoCurl() {
        // Get CURL object and execute it
        $model = new api_model_http('http://extapi.local.ch/0/cities.xml?q=Ol');
        $dom = $model->getDOM();
        $this->assertText($dom, '/response/@status', 'ok');
        $this->assertText($dom, '/response/cities/city[1]/@name', 'Olten');
    }
    
    /**
     * Test if the model sets the right headers to the curl
     */
    function testModelCurlHeaders() {
        // Get CURL object
        $model = new api_model_http('http://extapi.local.ch/0/cities.xml?q=Ol');
        $curl = $model->getCurlObject();
        
        curl_setopt($curl, CURLINFO_HEADER_OUT, true); //To be able to get the headers of the curl
        curl_exec($curl);
        
        $headers = curl_getinfo($curl, CURLINFO_HEADER_OUT);
        
        //This is to say the prefered language is english.
        //If it's not available it takes something else
        $this->assertPattern('/Accept-Language: en/', $headers);
    }
}
?>
