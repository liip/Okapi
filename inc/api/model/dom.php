<?php
/**
 * Dom model object. Represents an XML DOM and returns an XML DOM
 * for that DOM in getDOM().
 * 
 */
class api_model_dom {
    
    private $dom = NULL;
    
    public function __construct($dom) {
        $this->dom = $dom;
    }
    

    // Returns the saved DOM for the view
    public function getDOM() {
        return $this->dom;
    }
}
