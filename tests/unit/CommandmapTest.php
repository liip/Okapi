<?php
/**
 * Tests the api_commandmap class which handles parsing of the commandmap
 * configuration file.
 */
class CommandmapTest extends UnitTestCase {
    function setUp() {
        $_SERVER['HTTP_HOST'] = 'demo.okapi.org';
        $_SERVER["REQUEST_URI"] = '/the/command';
        $_GET = array('path' => 'mypath', 'question' => 'does it work?');
        api_init::start();
        
        $_SERVER["REQUEST_URI"] = '/command/mymethod';
        $this->request = new api_request();
    }
    
    function testCommands() {
        $commands = new api_commandmap($this->request);
        $c = $commands->getCommands();
        $this->assertNotEqual(0, count($c), "No commands were loaded.");
    }
    
    function testDirectiveHost() {
        $commands = new api_commandmap($this->request);
        $this->assertEqual($commands->getDirectiveHost(), 'demo.okapi.org');
    }
    
    function testDirectivePath() {
        $commands = new api_commandmap($this->request);
        $this->assertEqual($commands->getDirectivePath(), '/command/');
    }
    
    function testMethod() {
        $commands = new api_commandmap($this->request);
        $this->assertEqual($commands->getMethod(), 'mymethod');
    }
    
    /**
     * Test if the method is correctly parsed from the path also when
     * additional parameters come after the method.
     */
    function testMethodWithParams() {
        $_SERVER["REQUEST_URI"] = '/command/ls/def';
        $request = new api_request();
        $commands = new api_commandmap($request);
        $this->assertEqual($commands->getMethod(), 'ls');
    }
    
    /**
     * Test if the method is correctly parsed from the path.
     * In case of an non-existing command, the method is expected
     * to be empty.
     */
    function testMethodInvalidCommand() {
        $_SERVER["REQUEST_URI"] = '/the/command';
        $request = new api_request();
        $this->expectException(new api_exception_NoCommandFound());
        $commands = new api_commandmap($request);
        $this->assertEqual($commands->getMethod(), '');
    }
    
    function testCommandAttributes() {
        $commands = new api_commandmap($this->request);
        $this->assertEqual($commands->getCommandAttributes(), array(
            'host'    => "demo.okapi.org",
            'path'    => '/command/',
            'view'    => 'default',
            'xsl'     => 'command.xsl',
            'theme'   => 'default',
            'css'     => 'default',
            'passdom' => 'no',
        ));
    }
}
?>
