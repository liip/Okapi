<?php
class api_params extends ArrayObject {
    private $post = Array();
    private $get  = Array();
    
    
    public function __construct($array = Array()) {
        parent::__construct($array, ArrayObject::ARRAY_AS_PROPS);
    }
    
    /**
     * Set POST Data
     */
    public function setPost($array) {
        $this->post = $array;
        $this->exchangeArray(array_merge($this->getArrayCopy(), $array));
    }
    
    /**
     * Set GET Data
     */
    public function setGet($array) {
        $this->get = $array;
        $this->exchangeArray(array_merge($this->getArrayCopy(), $array));
    }
    
    /**
     * Get POST Data
     */
    public function post($param = null) {
        if (isset($param)) {
            if (empty($this->post[$param])) {
                return FALSE;
            }
            return $this->post[$param];
        }
        return $this->post;
    }
    
    /**
     * Get GET Data
     */
    public function get($param = null) {
        if (isset($param)) {
            if (empty($this->get[$param])) {
                return FALSE;
            }
            return $this->get[$param];
        }
        return $this->get;
    }
}
