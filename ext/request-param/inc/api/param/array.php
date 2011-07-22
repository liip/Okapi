<?php
/**
 * Array type for api_param.
 * It only takes as many values as you have indexes defined
 * It can take subtypes like the following:
 * 
 * \code
 * $intervall =  api_param::factory('array');
 * $intervall->setKeys(Array("from","to"));
 * $intervall->setSubtypeByName('integer');
 *   
 * $cont->setMethod('index')
 *      ->setRequestMethod(api_param::GET)
 *      ->setRequired();
 *   
 * $cont->addParam($intervall);
 * \endcode
 *
 * @package request-param
 */
class api_param_array extends api_param {
    private $strDelimiter = ",";
    
    /**
     * Enter description here...
     *
     * @var api_paramcontainer
     */
    private $aValues = Array();
    
    private $strSubtype = null;
    
    private $apSubtype;
    
    private $aKeys = Array();
    
    public function __construct($params) {
        parent::__construct();
        $this->setHRType("Array");
    }
    
    /**
     * Cleans the value and cleans all it's subtypes and creates them.
     *
     * @return Null|api_param_array
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
            
            reset($aVal);
            foreach($this->aKeys as $key) {
                $val = current($aVal);

                if ($val === false) {
                    $this->clearValue = null;
                    return $this;
                }
                
                $e->setRequired($this->iRequired);
                $e->setValue(current($aVal));
                
                if ($e->check() !== true) {
                    $this->clearValue = null;
                    return $this;
                }
                
                
                $this->clearValue[$key] = $e->value();
                next($aVal);
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
    
    public function setKeys(Array $aKeys) {
        $this->aKeys = $aKeys;
        
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
