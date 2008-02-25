<?php
/**
* Used for text/plain output. 
*
* Extends api_views_default but sends text/plain headers
* 
* @see      api_views_default
* @author   Silvan Zurbruegg
*/


final class api_views_plain extends api_views_default {
    public function __construct($route) {
        parent::__construct($route);
        $this->omitXmlDecl=true;
    }
    
    
    /**
    * Sends text/plain Content-type
    *
    * @return   void
    */
    protected function setHeaders() {
        $this->response->setContentType('text/plain');
    }
}

