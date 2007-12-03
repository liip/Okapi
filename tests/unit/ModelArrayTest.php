<?php
/**
 * Tests the api_model_array class which returns an XML representation
 * of an array.
 */
class ModelArrayTest extends OkapiTestCase {
    function testModel() {
        $arr = array('bool' => true, 'int' => 5,
            'float' => 3.75, 'string' => 'yep.');
        $model = new api_model_array($arr);
        $dom = $model->getDOM();
        
        $this->assertXPath($dom, '/response/bool', 'true');
        $this->assertXPath($dom, '/response/int', '5');
        $this->assertXPath($dom, '/response/float', '3.75');
        $this->assertXPath($dom, '/response/string', 'yep.');
    }
}
?>
