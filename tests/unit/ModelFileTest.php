<?php
/**
 * Tests the api_model_file class which returns an XML file verbatim
 */
class ModelFileTest extends OkapiTestCase {
    function testModel() {
        $model = new api_model_file(dirname(__FILE__).'/../../lang/lang_en.xml');
        
        $dom = $model->getDOM();
        $this->assertXPath($dom, '/catalogue/message[@key="chf"]', 'CHF');
    }

    /**
     * Model is expected to throw an exception when the file
     * can't be found on the file system.
     */
    function testFilenotFound() {
        $this->expectException(new api_exception_FileNotFound(api_exception::THROW_FATAL, "config.xml"));
        $model = new api_model_file('config.xml');
    }
}
?>
