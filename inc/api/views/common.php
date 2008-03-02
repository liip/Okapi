<?php
/**
 * Abstract class to be extended by views
 *
 * @author   Silvan Zurbruegg
 */
abstract class api_views_common {
    /** api_response: Response object. */
    protected $response = null;
    
    /**
     * Set the response object to use.
     * @param $response api_response: Response object.
     */
    public function setResponse($response) {
        $this->response = $response;
    }
    
    /**
     * Set the request object to use.
     * @param $request api_request: Request object.
     */
    public function setRequest($request) {
        $this->request = $request;
    }
    
    /**
     * Constructor.
     * @param $route hash: Route parameters.
     */
    public function __construct($route) {
        $this->request = api_request::getInstance();
        $this->route = $route;
        $this->response = api_response::getInstance();
    }
    
    /**
     * Prepare for dispatching
     *
     * Gets called before dispatch()
     * Useful for instantiation of DOM objects etc.
     */
    public function prepare() {
       return true;
    }
    
    /**
     * To be implemented by views for outputting response.
     * @param $data DOMDocument: DOM document to transform.
     * @param $exceptions array: Array of exceptions merged into the DOM.
     */
    abstract function dispatch($data, $exceptions = null);
    
    /**
     * Sends text/xml content type headers.
     *
     * @return   void
     */
    protected function setXMLHeaders() {
        $this->response->setContentType('text/xml');
        $this->response->setCharset('utf-8');
    }
    
    /**
     * Usable by views for setting specific headers
     * Should use the $this->response object to set headers.
     */
    protected function setHeaders() {
    }
    
    /**
     * Translates content in the given DOM using api_i18n.
     *
     * @param $lang string: Language to translate to.
     * @param $xmlDoc DOMDocument: DOM to translate.
     * @config <b>lang['i18ntransform']</b> (bool): If set to false,
     *         no transformations are done. Defaults to true.
     */
    protected function transformI18n($lang, $xmlDoc) {
        $cfg = api_config::getInstance()->lang;
        if(isset($cfg['i18ntransform']) && $cfg['i18ntransform'] === false){
            return;
        }
        
        $i = api_i18n::getInstance($lang);
        $i->i18n($xmlDoc);
    }
}
