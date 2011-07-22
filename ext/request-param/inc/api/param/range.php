<?php
/**
 * Range type for api_param.
 * It splits up a value and checks it against the subtypes. You can set them
 * the following way:
 * This is an example with a double nested type
 * 
 * \code
 * $intervall =  api_param::factory('array');
 * $intervall->setKeys(Array("from","to"));
 * $intervall->setSubtypeByName('integer');
 * 
 * $intervallContainer = api_param::factory('range');
 * $intervallContainer->setName('intervall');
 * $intervallContainer->setMaxElements(10);
 * $intervallContainer->setSubtype($intervall);
 *    
 * $cont->setMethod('index')
 *      ->setRequestMethod(api_param::GET)
 *      ->setRequired();
 *   
 * $cont->addParam($intervallContainer);
 * \endcode
 *
 * @package request-param
 */
class api_param_range extends api_param {
    private $strDelimiter = ";";
    
    private $iMaxElements = 0;
    
    public function __construct($params) {
        parent::__construct();
        $this->setHRType("Range");
    }
    
    /**
     * Cleans the value, creates all the subelements and cleans them
     *
     * @return Null|api_param_range
     */
    protected function clean() {
        if (isset($this->_value)) {
            $aVal = explode($this->strDelimiter, $this->_value);
            $this->clearValue = Array();
            
            if (isset($this->apSubtype)) {
                $e = $this->apSubtype;
            } elseif (isset($this->strSubtype)) {
                $e = api_param::factory($this->strSubtype);    
            }
            
            foreach($aVal as $val) {
                $e->setRequired($this->iRequired);
                $e->setValue($val);
                
                if ($e->check() === false) {
                    $this->clearValue = null;
                    return $this;
                }
                
                $this->clearValue[] = $e->value();
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
        return is_array($this->clearValue);
    }
    
    public function setMaxElements($iMax) {
        $this->iMaxElements = $iMax;
        
        return $this;
    }
    
    public function setSubtypeByName($strSubtype) {
        $this->strSubtype = $strSubtype;
        
        return $this;
    }
    
    public function setSubtype(api_param $subtype) {
        $this->apSubtype = $subtype;
        
        return $this;
    }
    
    public function setDelimiter($strDelimiter) {
        $this->strDelimiter = $strDelimiter;
        return $this;
    }
    
}
