<?php
class ControllerTest extends UnitTestCase {
    function setUp() {
        $_SERVER['HTTP_HOST'] = 'demo.okapi.org';
        $_SERVER["REQUEST_URI"] = '/command/the';
        $_GET = array('question' => 'does it work?');
        api_init::start();
    }
    
    /**
     * Check that controller can be correctly instantiated.
     */
    function testInit() {
        $c = $this->getController();
        $this->assertIsA($c['ctrl'], 'api_controller');
    }
    
    /**
     * Check that process method works.
     */
    function testProcess() {
        $c = $this->getController();
        ob_start();
        $c['ctrl']->process();
        $contents = ob_get_contents();
        ob_end_clean();

        $this->assertNotEqual($contents, '');
    }

    /**
     * Helper method to initialize a new controller with some
     * overwritten parameters. Uses api_init::getControllerConfig()
     * and overwrites the keys which are given.
     */
    private function getController($opts = array()) {
        $controller = new api_controller();
        
        return array('ctrl' => $controller,
                     'params' => $controller->getRequestParams());
    }
}
?>
