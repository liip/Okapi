<?php
/**
*/
class api_views_xml extends api_views_common {
    public function __construct($route) {
        parent::__construct($route);
    }
	
	public function dispatch($xmldom) {
		$this->setXMLHeaders();
		$this->response->send();
     	echo $xmldom->saveXML();
        return;
	}
	
}

