<?php
/**
 * Dom model object. Represents an XML DOM and returns an XML DOM
 * for that DOM in getDOM().
 * 
 */
class api_model_dom {
    /**
     * The dom-object
     *
     * @var DOMDocument
     */
    private $dom = NULL;
    
    /**
     * creates a new api_model_dom from a dom object
     *
     * @param DOMDocument $dom
     */
    public function __construct($dom) {
        $this->dom = $dom;
    }
    

    /**
     * Returns the saved DOM for the view
     * 
     * @return DOMDocument
     */
    public function getDOM() {
        return $this->dom;
    }
}
