<?php
/**
 * A simple integer type for api_param. Implements a few checks like setMax
 * and setMin. To be extended for more checks
 *
 * @package request-param
 */
class api_param_integer extends api_param {
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
        parent::__construct();
        $this->setHRType("Integer");
    }
    
    /**
     * Cleans the value (intvals it) if the the _value is set
     *
     * @return Null|api_param_integer
     */
    protected function clean() {
        if (isset($this->_value)) {
            $this->clearValue = intval($this->_value);
            if ($this->clearValue === 0 && $this->_value !== "0") {
                $this->bTypeMissmatch = true;
            }
        } else {
            $this->clearValue = null;
        }
        
        return $this;
    }
    
    /**
     * Check if the type is correct.
     *
     * @return Boolean: True if it matches, false otherwise
     */
    public function checkType() {
        return !$this->bTypeMissmatch;
    }
    
    /**
     * Sets the max value and subscribes the checkMax checker
     *
     * @param Integer $iMax: Maximum value
     * @return api_param_integer
     */
    public function setMax($iMax) {
        $this->iMax = $iMax;
        
        $this->subscribeCheck("checkMax");
        return $this;
    }
    
    /**
     * Checks if the value is above it's maximum and writes an error message
     *
     * @return Boolean
     */
    public function checkMax() {
        $val = $this->clearValue;
        
        if (isset($this->iMax) && $val > $this->iMax) {
            $this->strErrorMessage .= "Value ($val) too big ($this->iMax). ";
            return false;
        }
        
        return true;
    }
    
    /**
     * Sets the min value and subscribes the checkMin checker
     *
     * @param Integer $iMin: Minimum value
     * @return api_param_integer
     */
    public function setMin($iMin) {
        $this->iMin = $iMin;
        
        $this->subscribeCheck("checkMin");
        return $this;
    }

    /**
     * Checks if the value is below it's minimum and writes an error message
     *
     * @return Boolean
     */
    public function checkMin() {
        $val = $this->clearValue;
            
        if (isset($this->iMin) && $val < $this->iMin) {
            $this->strErrorMessage .= "Value ($val) too small ($this->iMin). ";
            return false;
        }
        
        return true;
    }
}
