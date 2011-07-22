<?php


class api_param_plain extends api_param {
    
    public function __construct($params) {
        parent::__construct();
        $this->setHRType("Plain");
    }
        
    protected function checkType() {
        return true;
    }
    
    protected function clean() {
        $this->clearValue = $this->_value;
        
        return $this;
    }
}

