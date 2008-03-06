<?php
/**
 * Counts the number of calls to the send() method api api-response.
 */
class testResponse extends api_response {
    protected $sent = 0;
    
    public function send() {
        $this->sent += 1;
    }
    
    public function getSent() {
        return $this->sent;
    }
}

class ControllerTest extends UnitTestCase {
    function setUp() {
        $_SERVER['HTTP_HOST'] = 'demo.okapi.org';
        $_SERVER["REQUEST_URI"] = '/command/the';
        $_GET = array('question' => 'does it work?');
        api_init::start();
        api_request::getInstance(true);
        
        $this->controller = new api_controller();
        $this->response = new testResponse();
        $this->controller->setResponse($this->response);
    }
    
    function tearDown() {
        @ob_end_clean();
    }
    
    /**
     * Check that controller can be correctly instantiated.
     */
    function testInit() {
        $this->assertIsA($this->controller, 'api_controller');
    }
    
    /**
     * Check that process method works.
     */
    function testProcess() {
        $this->controller->process();
        $contents = ob_get_contents();
        $this->assertNotEqual($contents, '');
    }
    
    /**
     * Check that process method throws a correct exception when no
     * route matches.
     */
    function testProcessWithoutRoute() {
        $_SERVER["REQUEST_URI"] = '/some/obscure/test';
        api_request::getInstance(true);
        $this->controller = new api_controller();
        $this->response = new testResponse();
        $this->controller->setResponse($this->response);
        
        $this->expectException(new api_exception_NoCommandFound());
        $this->controller->process();
    }
    
    /**
     * Check that the command is set right when we use namespaces
     *
     */
    function testNamespaces() {
        $_SERVER['REQUEST_URI'] = '/namespacetest/foo/bar/blah';
        api_request::getInstance(true);
        
        $this->controller = new api_controller();
        $this->response = new testResponse();
        $this->controller->setResponse($this->response);
        $this->controller->process();

        $this->assertEqual($this->controller->getCommandName(), 'foo_command_bar');
        $this->assertEqual($this->controller->getFinalViewName(), 'foo_views_default');
        
    }
    
    /**
     * Check exception if command does not exist
     *
     */
    function testNamespacesNotExisting() {
        $_SERVER['REQUEST_URI'] = '/namespacetest/blah/nocommand/the';
        api_request::getInstance(true);
        
        $this->controller = new api_controller();
        $this->response = new testResponse();
        $this->controller->setResponse($this->response);


        $this->expectException(new api_exception_NoCommandFound('Command blah_commands_nocommand or blah_command_nocommand not found.'));
        $this->controller->process();
    }
    
    
    /**
     * Check default-namespace-view usage if there is no view in this namespace 
     *
     */
    function testNamespacesWithoutView() {
        $_SERVER['REQUEST_URI'] = '/namespacetest/bar/foo/blah';
        api_request::getInstance(true);
        
        $this->controller = new api_controller();
        $this->response = new testResponse();
        $this->controller->setResponse($this->response);
        $this->controller->process();

        $this->assertEqual($this->controller->getCommandName(), 'bar_command_foo');
        $this->assertEqual($this->controller->getFinalViewName(), 'api_views_default');
        
    }
    
    /**
     * Check that the headers are set correctly.
     */
    function testResponseHeaders() {
        $this->controller->process();
        
        $this->assertEqual(array('Content-Type' => 'text/html; charset=utf-8'),
            $this->response->getHeaders());
    }
    
    /**
     * Check that the response is sent out.
     */
    function testResponseSentOnce() {
        $this->controller->process();
        
        $this->assertEqual(1, $this->response->getSent());
    }
}
?>
