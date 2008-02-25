<?php
/**
 * Response class which handles outputting the header and body.
 */
class api_response {
    protected $headers = array();
    protected $contenttype = null;
    protected $charset = 'utf-8';
    protected $code = null;
    
    public static function getInstance($forcenew = false) {
        static $instance;
        if ((!isset($instance) || !($instance instanceof api_response)) || $forcenew) {
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
     * Sets the response code of the current request.
     * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html HTTP status codes
     */
    public function setCode($code) {
        $this->code = $code;
    }
    
    /**
     * Redirects the user to another location. The location can be
     * relative or absolute, but this methods always converts it into
     * an absolute location before sending it to the client.
     *
     * Calls the api_response::send() method to force output of all
     * headers set so far.
     *
     * @param $to string: Location to redirect to.
     * @param $status int: HTTP status code to set. Use one of the following: 301, 302, 303.
     * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html#sec10.3.2 HTTP status codes
     */
    public function redirect($to, $status=301) {
        if (strpos($to, 'http://') === 0 || strpos($to, 'https://') === 0) {
            $url = $to;
        } else {
            $schema = $_SERVER['SERVER_PORT'] == '443' ? 'https' : 'http'; 
            $host = (isset($_SERVER['HTTP_HOST']) && strlen($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
            $to = strpos($to,'/')===0 ? $to : '/'.$to;
            $url = "$schema://$host$to";
        }
        
        $this->setCode($status);
        $this->setHeader('Location', $url);
        $this->send();
        exit();
    }

    /**
     * Send all content to the browser.
     */
    public function send() {
        if (!is_null($this->code)) {
            $this->sendStatus($this->code);
        }
        
        foreach ($this->getHeaders() as $header => $value) {
            header("$header: $value");
        }
        
        ob_end_flush();
    }
    
    /**
     * Send the status header line.
     * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html HTTP status codes
     */
    protected function sendStatus($code) {
        header(' ', true, $code);
    }
}