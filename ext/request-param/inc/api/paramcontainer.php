<?php
// TODO: Uncomment for production
//require_once dirname(__FILE__) . '/param.php';
/**
 * A Container class, which handles different kinds of api_param objects.
 * It is used to add parameters and manipulate bunch of parameters in a single
 * place.
 * 
 * \code
 * $p = api_param::factory('integer');
 * $p   ->setName('fooparam')
 *      ->setRequired()
 *      ->setRequestMethod(api_param::GET)
 *      ->setMethod('doWhatIWantMethod');
 * 
 * 
 * $cont = new api_paramcontainer($attribs);
 * $cont->addParam($p);
 * 
 * $cont->setType('String')
 *      ->setOptional()
 *      ->setRequestMethod(api_param::POST ^ api_param::GET)
 *      ->setMethod(Array('blah','doSomethingElse')); // You can also do this conditionalwise depending on the $attribs['method'] parameter
 * 
 * $cont->addParamByName('foo2param');
 * $cont->setRequired()->addParamByName('foo3param');
 * 
 * if (!$cont->checkParams($attribs['method'])) {
 *      echo $cont->getInfo();
 *      die(); // Or some usage screen or whatever
 * }
 * \endcode
 * 
 * @package request-param
 */
Class api_paramcontainer {
    /**
     * Array $rgMethods: Range of Methods. This holds the methods to which parameters are subscribed
     */
    private $rgMethods = Array();
    
    /**
     * Array $aParams: Array of api_param objects
     */
    private $aParams = Array();
    
    /**
     * String $aType: The type of the param objects to add
     */
    protected $aType = null;
    
    /**
     * Boolean $iRequired: Trigger if a parameter is required or optional 
     */
    protected $iRequired = false;
    
    /**
     * Int $iRequestMethod: Integer value of the request-methods used as binary value
     */    
    protected $iRequestMethod = 0;

    /**
     * mixed $default: Default value for parameter
     */
    protected $default = null;
    
    /**
     * Array $aErrorMessage: Array of Strings which hold all the error messages for the parameters
     */
    protected $aErrorMessages = Array();
    
    /**
     * Array $attribs: Array of the request attributes
     */
    public static $attribs = null;
    
    /**
     * api_request $rRequest: The request object
     */
    protected $rRequest = null;
    
    public function __construct(&$attribs) {
        self::$attribs = $attribs;
        $this->rRequest = api_request::getInstance();
    }
    
    /* Setters ****************************************************************/
    /**
     * Sets the methods to which a parameter is subscribed. If only a string is 
     * given it automatically converts it to an array.
     *
     * @param Array|String $rgMethods: The Array of methodsnames as strings
     * @return api_paramcontainer
     */
    public function setMethod($rgMethods) {
        if (!is_array($rgMethods)) {
            $rgMethods = Array($rgMethods);
        }
        
        $this->rgMethods = $rgMethods;
        
        return $this;
    }
    
    /**
     * Sets the type for a parameter. This is used to create the parameter object
     *
     * @param String|Array $aType: Name of the type (integer, string, date..) or Array of types (Array('array' => 'integer'))
     * @return api_paramcontainer
     */
    public function setType($aType) {
        if (!is_array($aType)) {
            $aType = Array($aType);
        }
        
        $this->aType = $aType;
        
        return $this;
    }
    
    /**
     * NOT YET IMPLEMENTED
     * Adds a condition to the requiredness of a parameter.
     * Not yet sure how this is gonna be implemented (it does not work as of now)
     *
     * @param Array $rgName: Range of parameter names
     * @param Mixed $mValue: Their should-be values
     * @return api_paramcontainer
     */    
    public function addCondition($rgName, $mValue) {
        // do some fancy and naughty stuff here!
        
        return $this;
    }
    
    /**
     * Sets a parameter to be required or whatever $bool holds
     *
     * @param Boolean $bool: True if the parameter should be required, False otherwise
     * @return api_paramcontainer
     */
    public function setRequired($bool = true) {
        $this->iRequired = $bool;
        
        return $this;
    }
    
    /**
     * Sets a parameter to be optional or whatever $bool holds
     *
     * @param Boolean $bool: True if the parameter should be optional, False if required
     * @return api_paramcontainer
     */
    public function setOptional($bool = true) {
        $this->iRequired = !$bool;
        
        return $this;
    }
    
    /**
     * Sets the supported request methods with some binary magic
     *
     * \code
     * $foo->setRequestMethod(api_param::GET ^ api_param::POST); // Now checks both: GET and POST
     * \endcode
     * 
     * @param Integer $iRequestMethod: Use api_param::GET/POST/DELETE/PUT/URLPATH here
     * @return api_paramcontainer
     */
    public function setRequestMethod($iRequestMethod) {
        $this->iRequestMethod = $iRequestMethod;
        
        return $this;
    }
    
    public function getDefault() {
    	return $this->default;
    }
    
    /**
     * Sets the default value for parameters.
     *
     * @param mixed $default: Any default value you like
     * @return api_paramcontainer
     */
    public function setDefault($default = null) {
        $this->default = $default;

        return $this;
    }
    
    /* Parameter adding *******************************************************/
    /**
     * Adds a parameter to the container. This one adds a predefined api_param object
     * and directly fetches its values so they are cleaned already when added.
     *
     * @param api_param $p
     * @param boolean $isMethodSet: Only if one of the set methods match, add the parameter, this is helpful for not having to mess with some switch statements and the like
     * @return api_paramcontainer
     */
    public function addParam(api_param $p, $isMethodSet = false) {
        $p->setAttribs(self::$attribs);
        // TODO: Check if not already set
        $p->setRequestMethod($this->iRequestMethod);
        $p->setRequired($this->iRequired);
        $p->setMethod($this->rgMethods);
        if($this->default != null) {
            $p->setDefault($this->default);
        }
        $p->fetch();
        
        // if we don't know the method yet, ignore isMethodSet
        if ($isMethodSet === true && isset(self::$attribs['method'])) {
            if (in_array(self::$attribs['method'], $this->rgMethods)) {
                $this->aParams[self::$attribs['method']][$p->getStoredName()] = $p;    
            } else {
                return $this;
            }
        } else {
            foreach ($this->rgMethods as $method) {
                $this->aParams[$method][$p->getStoredName()] = $p;
            }
        }
        
        return $this;
    }
    
    /**
     * Adds a parameter to the container by it's name. This is useful if you
     * want to add parameters just for basic checking and cleaning without
     * having fancy checks like checkMax(). A real api_param object is created
     * and added with addParam()
     *
     * @param String $strName: Name of the parameter to fetch
     * @param Boolean $isMethodSet: Only if one of the set methods match, add the parameter, this is helpful for not having to mess with some switch statements and the like
     * @param String $strStoredName: Name of the parameter as it should be stored (Default is the first parameter $strName)
     * @return api_paramcontainer
     */
    public function addParamByName($strName, $isMethodSet = false, $strStoredName = null) {
        if ($isMethodSet === true && isset(self::$attribs['method']) && !in_array(self::$attribs['method'], $this->rgMethods)) {
            return $this;
        }
        
        $p = api_param::factory($this->aType);
        $p->setName($strName);
        $p->setStoredName($strStoredName);
        
        $this->addParam($p, $isMethodSet);
        
        return $this;
    }

    /* Checking, fetching and returning ***************************************/
    /**
     * Returns the Array of all cleared values for a method. The keys are their
     * strStoredName values.
     *
     * @param String $strMethod: The method name
     * @return Null|Array: If no method-name can be retrieved, returns null, Array of values otherwise
     */
    public function arrayValues($strMethod = null) {
        if (!$strMethod && isset($this->rgMethod[0])) { 
            $strMethod = $this->rgMethod[0];
        } elseif ((!$strMethod && !isset($this->rgMethod[0]))) {
            // check if strMethod is set
            return null;
        }
        
        if (!isset($this->aParams[$strMethod])) {
            if (!is_array($this->rRequest->getParameters())) {
                return self::$attribs;
            }
            
            return array_merge($this->rRequest->getParameters(), self::$attribs);
        }
        
        $aRet = Array();
        foreach ($this->aParams[$strMethod] as $param) {
            // get all values
            $aRet[$param->getStoredName()] = $param->value();
        }
        
        return $aRet;
    }
    
    /**
     * Returns a particular parameter object
     *
     * @param String $strName: Parameter name (not stored name)
     * @param String $strMethod: For which method
     * @return Null|api_param: Null if $strMethod is set nowhere
     */
    public function getParam($strName, $strMethod = null) {
        if (!$strMethod && isset($this->rgMethod[0])) { 
            $strMethod = $this->rgMethod[0];
        } elseif (!$strMethod && !isset($this->rgMethod[0])) {
            return null;
        }
        
        return $this->aParams[$strMethod][$strName];  
    }
    
    /**
     * Checks all the contained parameters for a particular method. This is helpful
     * if you want to continue execution, even if the parameters are  not set or wrong.
     * It will write all error messages into $aErrorMessages as keyed array for each parameter.
     * It will return however, only true or false, depending on the outcome of course
     *
     * @param String $strMethod: The name of the method
     * @return Null|Boolean: Null if $strMethod is set nowhere
     */
    public function checkParams($strMethod = null) {
        if (!$strMethod && isset($this->rgMethod[0])) { 
            $strMethod = $this->rgMethod[0];
        } elseif (!$strMethod && !isset($this->rgMethod[0])) {
            return null;
        }
        
        $aErr = Array();
        if (!isset($this->aParams[$strMethod])) {
            return true;
        }
        foreach ($this->aParams[$strMethod] as $param) {
            if ($param->check() === false) {
                $aErr[$param->getStoredName()] = $param->getMessage();
            }
        }
        
        if (count($aErr) > 0) {
            $this->aErrorMessages = $aErr;
            return false;
        }
        
        return true;
    }
    
    
    /**
     * Returns a model of all information about the parameters needed / used
     * for the current command. It returns the parsed variables
     *
     * @return api_model: Which can be used to store into $this->data[]
     */
    public function getInfo() {
        // TODO: Return information instead of error messages
        return new api_model_array($this->aErrorMessages);
        /*$dom = new DOMDocument();

        $p = $dom->createElement('params');
        $dom->appendChild($p);
        
        foreach ($this->aParams[$this->rgMethods[0]] as $command) {
            $c = $dom->createElement('param');
            $name = $dom->createElement('name');
            $name->appendChild($dom->createTextNode($command->strName));
            
            $val = $dom->createElement('value');
            $val->appendChild($dom->createTextNode($command->value()));

            $type = $dom->createElement('types');
            $aType = self::getSet(self::$rgType ,$command->iType);
            foreach ($aType as $element) {
                $t = $dom->createElement('type');
                $t->appendChild($dom->createTextNode($element));
                $type->appendChild($t);
            }
            
            $method = $dom->createElement('methods');
            $aMethod = self::getSet(self::$rgMethod ,$command->iMethod);
            foreach ($aMethod as $element) {
                $m = $dom->createElement('method');
                $m->appendChild($dom->createTextNode($element));
                $method->appendChild($m);
            }
            
            $need = $dom->createElement('needs');
            $aNeed = self::getSet(self::$rgNeed ,$command->iNeed);
            foreach ($aNeed as $element) {
                $m = $dom->createElement('need');
                $m->appendChild($dom->createTextNode($element));
                $need->appendChild($m);
            }
            

            
            $c->appendChild($name);
            $c->appendChild($val);
            $c->appendChild($type);
            $c->appendChild($method);
            $c->appendChild($need);

            
            $p->appendChild($c);
        }
        
        return new api_model_dom($dom);
        */
    }
}

