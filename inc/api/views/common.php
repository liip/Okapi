<?php
/**
* Abstract class to be extended by views
*
* @author   Silvan Zurbruegg
*/

abstract class api_views_common {
    /**
     * State
     * 
     * @var     int 
     */
    public $state = API_STATE_FALSE;
   
    /**
    * Default Content-Type
    * @var      string
    */
    protected $contentTypeDefault = 'text/html';

    /*
    * Content Type
    *
    * @var      string
    */
    protected $contentType = '';


    /**
    * Default Encoding 
    * @var      string
    */
    protected $contentEncodingDefault= 'UTF-8';
 
    
    /**
    * Encoding
    *
    * @var      string
    */ 
    protected $contentEncoding = '';

    /**
    * Content Length
    *
    * Can be set from dispatch method and used for example
    * by setHeaders()
    * 
    * @var      int
    */
    protected $contentLength = 0;
    
    /**
     * 
     * @var api_response
     *
     */
    
    protected $response = null;
    
    public function setResponse($response) { $this->response = $response; }
    public function setRequest($request) { $this->request = $request; }

    /**
     * Constructor
     * 
     * @param   request  api_request  Request information.
     * @param   route    array        Route information.
     * @param   response api_response Response object.
     */
    protected function __construct($route) {
        $this->request = api_request::getInstance();
        $this->route = $route;
        $this->response = api_response::getInstance();
    }
    
    
    /**
    * Prepare for dispatching
    *
    * Gets called before dispatch()
    * Useful for instantiation of dom objects etc.
    *
    * @param    array   params  array of request params
    * @see      api_views_common::dispatch()
    * @return   void
    */
    public function prepare() {
       return true;
    }
    
    
    /**
    * Sends text/xml Content-type
    *
    * @return   void
    */
    protected function setXMLHeaders() {
        $this->response->setContentType('text/xml');
        $this->response->setCharset('utf-8');
    }
    
    
    /**
     * Usable by views for setting specific headers
     *
     * Should use the $this->response object to set headers.
     *
     * @return   void
     */
    protected function setHeaders() {
    }
    
    
    /**
    * To be implemented by views for dispatching output
    *
    * @return       void
    */
    abstract function dispatch($xmldom);
    
    
    protected function transformI18n($lang, $xmlDoc) {
	
        $cfg = api_config::getInstance()->lang;
        if(isset($cfg['i18ntransform']) && $cfg['i18ntransform'] === false){
            return;
        }
        
        $i = api_i18n::getInstance($lang);
        $i->i18n($xmlDoc);
    }
}
?>
