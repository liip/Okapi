<?php
/**
 * Tests the api_model_array class which returns an XML representation
 * of an array.
 */
class ModelArrayTest extends api_testing_case_unit {
    function testModel() {
        $arr = array('bool' => true, 'int' => 5,
            'float' => 3.75, 'string' => 'yep.');
        $model = new api_model_array($arr);
        $dom = $model->getDOM();
        
        $this->assertText($dom, '/response/bool', 'true');
        $this->assertText($dom, '/response/int', '5');
        $this->assertText($dom, '/response/float', '3.75');
        $this->assertText($dom, '/response/string', 'yep.');
    }
}
?>
