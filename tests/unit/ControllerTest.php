<?php
class ControllerTest extends UnitTestCase {
    function setUp() {
        $_SERVER['HTTP_HOST'] = 'demo.okapi.org';
        $_SERVER["REQUEST_URI"] = '/the/command';
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
     * Verify that the path of the controller is passed through the right way.
     */
    function testPath() {
        $c = $this->getController();
        $this->assertEqual($c['ctrl']->path, '/the/command');
    }
    
    /**
     * Test that language parsing works correctly for a path which does
     * not contain any language (the default language must be used).
     */
    function testLangDefault() {
        $c = $this->getController();
        $this->assertEqual($c['params']['lang'], 'en');
    }
    
    /**
     * Read the language from the path. This case tests reading the
     * language when it's the default language.
     */
    function testLangPath() {
        $c = $this->getController(array('path' => '/en/the/command'));
        $this->assertEqual($c['params']['lang'], 'en');
        $this->assertEqual($c['ctrl']->path, '/the/command');
    }
    
    /**
     * Read the language from the path. This case tests reading the
     * language when it's not the default language.
     */
    function testLangPathGerman() {
        $c = $this->getController(array('path' => '/de/the/command'));
        $this->assertEqual($c['params']['lang'], 'de');
        $this->assertEqual($c['ctrl']->path, '/the/command');
    }
    
    /**
     * Read the language from the path. This case verifies
     * only known languages are used.
     */
    function testLangPathSpanish() {
        $c = $this->getController(array('path' => '/es/the/command'));
        $this->assertEqual($c['params']['lang'], 'en');
        $this->assertEqual($c['ctrl']->path, '/es/the/command');
    }
    
    /**
     * Test if the method is correctly parsed from the path.
     * In case of an existing command, the method is expected
     * to be the first component after the command.
     */
    function testMethod() {
        $c = $this->getController(array('path' => '/command/mymethod'));
        $this->assertEqual($c['params']['method'], 'mymethod');
    }

    /**
     * Test if the method is correctly parsed from the path also when
     * additional parameters come after the method.
     */
    function testMethodWithParams() {
        $c = $this->getController(array('path' => '/command/ls/def'));
        $this->assertEqual($c['params']['method'], 'ls');
    }
    
    /**
     * Test if the method is correctly parsed from the path.
     * In case of an non-existing command, the method is expected
     * to be empty-
     */
    function testMethodInvalidCommand() {
        $c = $this->getController(array('path' => '/the/command'));
        $this->assertEqual($c['params']['method'], '');
    }
    
    /**
     * Helper method to initialize a new controller with some
     * overwritten parameters. Uses api_init::getControllerConfig()
     * and overwrites the keys which are given.
     */
    private function getController($opts = array()) {
        $cfg = api_init::getControllerConfig();
        $cfg = array_merge($cfg, $opts);
        $controller = new api_controller($cfg);
        
        return array('ctrl' => $controller,
                     'params' => $controller->getRequestParams());
    }
}
?>
