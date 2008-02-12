<?php
class ResponseTest extends UnitTestCase {
    function tearDown() {
        @ob_end_clean();
    }
    
    function testInit() {
        $r = new api_response();
        $this->assertIsA($r, 'api_response');
    }
    
    /**
     * Make sure that output buffering is turned on in the
     * constructor of api_response.
     */
    function testBuffer() {
        $r = new api_response();
        $out = "******** test output\n";
        echo $out;
        $this->assertEqual($out, ob_get_clean());
    }
    
    /**
     * Add a header and expect to get it back again.
     */
    function testHeaders() {
        $r = new api_response();

        $this->assertEqual(array(), $r->getHeaders());
        $r->setHeader('Pragma', 'no-cache');
        $this->assertEqual(array('Pragma' => 'no-cache'),
            $r->getHeaders());
    }
    
    /**
     * Add content type header.
     */
    function testContentType() {
        $r = new api_response();
        
        echo "hi there\n";
        
        $r->setContentType('text/xml');
        $this->assertEqual(array('Content-Type' => 'text/xml; charset=utf-8'),
            $r->getHeaders());
    }
    
    /**
     * Set different charset. When no content type is set, this should
     * not change the header list.
     */
    function testCharset() {
        $r = new api_response();
        
        $r->setCharset('iso-8859-1');
        $this->assertEqual(array(), $r->getHeaders());
    }
    
    /**
     * Set content type and charset.
     */
    function testContentTypeAndCharset() {
        $r = new api_response();

        $r->setContentType('text/xml');
        $r->setCharset('iso-8859-15');
        $this->assertEqual(array('Content-Type' => 'text/xml; charset=iso-8859-15'),
            $r->getHeaders());
    }
    
    /**
     * Set content type and charset.
     */
    function testContentTypeWithoutCharset() {
        $r = new api_response();

        $r->setContentType('binary/something');
        $r->setCharset(null);
        $this->assertEqual(array('Content-Type' => 'binary/something'),
            $r->getHeaders());
    }
}
?>
