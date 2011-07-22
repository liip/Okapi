<?php
/**
 * A simple float type for api_param. Implements a few checks like setMax
 * and setMin. To be extended for more checks
 *
 * @package request-param
 */
class api_param_float extends api_param_integer {
    /**
     * Boolean $bTypeMissmatch: If there is a value, but it has the wrong type, this is set to false
     */
    private $bTypeMissmatch = false;
    
    /**
     * Integer $iMax: Maximum value of an integer
     */
    private $iMax = null;
    
    /**
     * Integer $iMin: Minimum value of an integer
     */
    private $iMin = null;

    public function __construct($params) {
        parent::__construct($params);
        $this->setHRType("Float");
    }
    
    /**
     * Cleans the value (intvals it) if the the _value is set
     *
     * @return Null|api_param_integer
     */
    protected function clean() {
        if (isset($this->_value)) {
            $this->clearValue = floatval($this->_value);
            if ($this->clearValue === 0 && $this->_value !== 0) {
                $this->bTypeMissmatch = true;
            }
        } else {
            $this->clearValue = null;
        }
        
        return $this;
    }
}
