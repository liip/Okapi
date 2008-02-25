<?php
/**
*/
class api_pam_common {
    
    protected $opts = array();
    
    protected function __construct($opts) {
        $this->opts = $opts;
    }
    
    protected function getOpt($name, $default=null) {
        if (isset($this->opts[$name]) && !empty($this->opts[$name])) {
            return $this->opts[$name];
        }
        
        return $default;
    }
    
}

