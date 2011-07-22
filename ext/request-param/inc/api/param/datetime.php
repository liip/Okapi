<?php
class api_param_datetime extends api_param {
    
    private $bTypeMissmatch = false;
    
    private $strDelimiter = "-";
    
    public function __construct($params) {
        parent::__construct();
        $this->setHRType("Datetime");
    }
    
    protected function checkType() {
        return !$this->bTypeMissmatch;
    }
    
    protected function clean() {
        if (isset($this->_value)) {
            $val = strtotime($this->_value);
            $this->clearValue = date("Y-m-d H:i:s", $val);
            if ($val === 0 && $this->_value !== 0) {
                $this->bTypeMissmatch = true;
            }
        } else {
            $this->clearValue = null;
        }
        
        return $this;
               
    }
}
