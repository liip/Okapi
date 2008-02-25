<?php
/**
 * Response class which handles outputting the header and body.
 */
class api_response {
    protected $headers = array();
    protected $contenttype = null;
    protected $charset = 'utf-8';
    
    
    
    public static function getInstance($forcenew = false) {
        static $instance;
        if ((!isset($instance) || !($instance instanceof api_response)) 
        	|| $forcenew) {
            $instance = new api_response();
        }
        return $instance;
    }
    
    /**
     * Constructor. Turns on output buffering.
     */
    public function __construct() {
        ob_start();
    }
    
    /**
     * Set a single header. Overwrites existing header if it exists.
     */
    public function setHeader($header, $value) {
        $this->headers[$header] = $value;
    }
    
    /**
     * Returns an associative array of all set headers.
     */
    public function getHeaders() {
        $headers = $this->headers;
        
        if (!is_null($this->contenttype)) {
            $ct = $this->contenttype;
            if (!is_null($this->charset)) {
                $ct .= '; charset=' . $this->charset;
            }
            
            $headers['Content-Type'] = $ct;
        }
        
        return $headers;
    }
    
    /**
     * Sets the content type of the current request. By default no
     * content type is set.
     */
    public function setContentType($contenttype) {
        $this->contenttype = $contenttype;
    }
    
    /**
     * Sets the character set of the current request. The character
     * set is only used when content type has been set. The default
     * character set is utf-8 - set to null if you want to send
     * a Content-Type header without character set information.
     */
    public function setCharset($charset) {
        $this->charset = $charset;
    }

    /**
     * Send all content to the browser.
     */
    function send() {
        foreach ($this->getHeaders() as $header => $value) {
            header("$header: $value");
        }
        
        ob_end_flush();
    }
}