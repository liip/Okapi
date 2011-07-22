<?php
// TODO: uncomment for production
//require_once dirname(__FILE__) . '/paramcontainer.php';
//require_once dirname(__FILE__) . '/param/integer.php';

/**
 * The base class for parameters. Every particular type extends this class to
 * get basic functionallity every parameter needs
 * 
 * \code
 * $p = api_param::factory('integer');
 * 
 * $p->setName('fooparam')->setRequired()->setRequestMethod(api_param::GET)
 * ->setMethod('doWhatIWantMethod');
 * 
 * $cont = new api_paramcontainer($attribs);
 * $cont->addParam($p);
 * \endcode
 *
 * @package request-param
 */
abstract class api_param extends api_paramcontainer {
    /**
     * Request Method Constants
     *
     */
    const GET = 1;
    const POST = 2;
    const PUT = 4;
    const DELETE = 8;
    const URLPATH = 16;
    const FILE = 32;
     
    /**
     * String $strName: The parameters name as it is transferred
     */
    protected $strName = null;

    /**
     * String $strStoredName: The parameters name as it is stored in the app.
     * Default is $strName
     */
    protected $strStoredName = null;
    
    /**
     * String $strHRType: The human readable (HR) Type of the parameter
     */
    protected $strHRType = null;

    /**
     * Mixed $_value: The `dirty' value of the parameter as it is entered by
     * the user
     */
    protected $_value = null;

    /**
     * Mixed $_value: The cleaned value of the p'meter
     */
    protected $clearValue = null;
    
    /**
     * Array $aRequestMethods: The Array of the RequestMethods as strings
     */
    protected $aRequestMethods = Array();

    /**
     * String $strErrorMessage: The error message string (human readable)
     */
    protected $strErrorMessage = null;

    /**
     * Array $aChecks: The array of checks which are subscribed to a parameter
     */
    protected $aChecks = Array();
    
    /* To be implemented by the effective parameters **************************/
    
    /**
     * Function to clean the actual parameter. This is delegated to the
     * according type. The cleaned value is stored in $this->clearValue, should
     * store null if this was not possible.
     * 
     * @return api_param
     */
    abstract protected function clean();
    
    /**
     * Function to check if the type matches the intended-type of the parameter
     * 
     * @return Boolean
     */
    abstract protected function checkType();
    
    /* Param setters which are the same for all parameters ********************/
    public function __construct() {   
        $this->rRequest = api_request::getInstance();
    }
    
    /**
     * Sets the stored name as it is used in the application paramter array
     *
     * @param String $strStoredName: The name
     * @return api_param
     */
    public function setStoredName($strStoredName) {
        $this->strStoredName = $strStoredName;
        
        return $this;
    }
    
    /**
     * Sets the name as the parameter should be fetched by
     *
     * @param String $strName: The name
     * @return api_param
     */
    public function setName($strName) {
        $this->strName = $strName;
        
        return $this;
    }

    public function setHRType($strHRType) {
        $this->strHRType = $strHRType;        
    }
    
    /**
     * Returns the stored name of a parameter
     *
     * @return String
     */
    public function getStoredName() {
        if (isset($this->strStoredName)) {
            return $this->strStoredName;
        } else {
            return $this->strName;
        }
    }

    /**
     * Returns the HumanReadable type string
     *
     * @return String
     */
    public function getHRType() {
        return $this->strHRType;
    }

    /**
     * Sets the attributes of the request
     *
     * @param Array $attribs
     * @return api_param
     */
    public function setAttribs(&$attribs) {
        self::$attribs = $attribs;

        return $this;
    }
    
    /**
     * Fetches the parameter value according to the request types. The order in
     * which overrides occur is the following:
     * GET, POST, PUT, DELETE, URLPATH
     * This means that you should actually avoid using 'id' as a parameter name
     * when you want to include URLPATH
     *
     * @return api_param
     */
    public function fetch() {
        $this->_value = null;
        
        if ($this->iRequestMethod & api_param::GET) {
            $this->aRequestMethods[] = "GET";
            if ($this->rRequest->getParameters()->get($this->strName) !== null) {
                $this->_value = $this->rRequest->getParameters()->get($this->strName);
            }
        }
        
        if ($this->iRequestMethod & api_param::POST) {
            $this->aRequestMethods[] = "POST";
            if ($this->rRequest->getParameters()->post($this->strName) !== null) {
                $this->_value = $this->rRequest->getParameters()->post($this->strName);
            }
        }
        
        if ($this->iRequestMethod & api_param::PUT) {
            $this->aRequestMethods[] = "PUT";
            //if ($this->rRequest->getParameters()->put($this->strName)) {
            //  $this->_value = $this->rRequest->getParameters()->delete($this->strName);
            //}
        }
        
        if ($this->iRequestMethod & api_param::DELETE) {
            $this->aRequestMethods[] = "DELETE";
            //if ($this->rRequest->getParameters()->delete($this->strName)) {
            //  $this->_value = $this->rRequest->getParameters()->delete($this->strName);
            //}
        }
        
        if ($this->iRequestMethod & api_param::URLPATH) {
            $this->aRequestMethods[] = "URLPATH";
            if (isset(parent::$attribs[$this->strName])) {
                $this->_value = parent::$attribs[$this->strName];
            }
        }
        
        if ($this->iRequestMethod & api_param::FILE) {
            $this->aRequestMethods[] = "FILE";
            if (isset($_FILES[$this->strName]) && $_FILES[$this->strName]['size']) {
                $this->_value = $_FILES[$this->strName];
            }
        }
        
        
        
        return $this;
    }
    
    protected function setValue($val) {
        $this->_value = $val;
    }
    
    /**
     * Returns the cleared value of a parameter
     *
     * @return Mixed
     */
    public function value() {
        if (!isset($this->clearValue)) {
            return $this->default;
        }
        
        return $this->clearValue;
    }
    
    /**
     * Subscribes a function to a parameter for checking
     * 
     * The function must not be static and must be in the type-class or any
     * of it's parents
     * 
     * Every function should return Boolean (true on success and false on error)
     * and write something in the $strErrorMessage
     *
     * @param unknown_type $strCheck
     */
    public function subscribeCheck($strCheck) {
        $this->aChecks[$this->strName][] = $strCheck;
    }
    
    /**
     * Runs through all checks of a parameter and fills the error string.
     * If the check fails, use getMessage() to get the reason.
     *
     * @return boolean
     */
    public function check() {
        $this->clean();
        $strMessage = "";
        $val = $this->clearValue;
        // If the value is not set: return false;
        if ($val === null && $this->checkType()) {
            $strMessage .= "Not set. ";
            $this->strErrorMessage .= $strMessage;
            
            if ($this->iRequired == true) {
                return false;
            }
            return true;
        } elseif (!$this->checkType() && $val !== null) {
            $this->strErrorMessage = "Types don't match (".$this->getHRType().")";
            
            return false;
        }
        
        // Run all subscribed checks
        if (isset($this->aChecks[$this->strName])){
            foreach ($this->aChecks[$this->strName] as $check) {
                $this->$check();
            }
        }
        
        // If error message is set: something's wrong
        if ($this->strErrorMessage && $this->iRequired) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Returns the human readable error message for a parameter
     *
     * @return String
     */
    public function getMessage() {
        return $this->strErrorMessage;
    }
    
    /**
     * Creates a new api_param_$strType object with $aParams as the parameter
     * Array for the class (or whatever)
     *
     * @param String $aType: Array of the names of the type (appended to api_param_)
     * @param Mixed $aParams: Whatever you want to add as params to the type 
     * @return api_param
     */
    public static function factory($aType, $aParams = null) {
        if (!is_array($aType) || (isset($aType[0]) && $aType = $aType[0])) {
            $class = "api_param_".$aType;
            if (class_exists($class, true)) {
                $p = new $class($aParams);
                
                return $p;
            }
        } elseif ($strBasetype = array_keys($aType)) {
            $class = "api_param_".$strBasetype[0];
            if (class_exists($class, true)) {
                $p = new $class($aParams);
                
                return $p;
            }
        }
        throw new api_exception_Backend(api_exception::THROW_FATAL, null, null, "Could not create param $aType");
        return false;
    }
}