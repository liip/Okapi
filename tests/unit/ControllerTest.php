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
     * Check that the headers are set correctly.
     */
    function testResponseHeaders() {
        $this->controller->process();
        
        $this->assertEqual(array('Content-Type' => 'text/html; charset=UTF-8'),
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
