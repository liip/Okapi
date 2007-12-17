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
        $c = new api_controller();
        $this->assertIsA($c, 'api_controller');
    }
    
    /**
     * Check that process method works.
     */
    function testProcess() {
        $c = new api_controller();
        ob_start();
        $this->expectError(new PatternExpectation('/Cannot modify header information/i'));
        $c->process();
        $contents = ob_get_contents();
        ob_end_clean();

        $this->assertNotEqual($contents, '');
    }
}
?>
