<?php


class api_param_string extends api_param {
    
    public function __construct($params) {
        parent::__construct();
        $this->setHRType("String");
    }
        
    protected function checkType() {
        return is_string($this->clearValue);
    }
    
    protected function clean() {
        
    	$this->clearValue = null;
        
    	if (isset($this->_value)) {
            $value = trim(strval($this->_value));
            if($value !== '') {
                $this->clearValue = $value;
            }
        }
        
        return $this;
    }
}

