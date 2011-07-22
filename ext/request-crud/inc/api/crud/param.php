<?php
/**
 * Class to clean and check the defined parameters
 * It's intended use is to fill an object with all parameters for all commands
 * and in the command itself you check for them. It is also possible to require
 * parameters for multiple commands.
 * 
 * Example:
 * 
 * in constructor:
 * $this->r = new api_crud_param();
 * $r->setCommand('read')->addParam('id', api_crud_param::TINT, api_crud_param::MGET, api_crud_param::RMANDATORY, 'cmd_id');
 * 
 * $r->setCommand(Array('read', 'write'))->addParam('pass', api_crud_param::TSTRING, api_crud_param::MPOST, api_crud_param::RMANDATORY);
 * 
 * in read:
 * $r = $this->r->setCommand('read');
 * if (!$r->checkParams('read')) { return false; }
 * $rgParam = $r->getParams('read');
 * 
 *
 */
Class api_crud_param {
    /**
     * Arra of the param_value objects
     *
     * @var api_crud_param_value
     */
    public $aParam = Array();
    
    /**
     * The integer value of the Type
     *
     * @var Integer: Used for binary-magic
     */
    protected $iType = self::TINT;
    
    /**
     * The integer value of the Method
     *
     * @var Integer: Used for binary-magic
     */
    protected $iMethod = self::MGET;

    /**
     * The integer value of the Need
     *
     * @var Need: Used for binary-magic
     */
    protected $iNeed = self::ROPTIONAL;
    
    /**
     * The Array of the commands which use the parameters
     *
     * @var Array
     */
    protected $aCommand = Array();
    
    /**
     * Holds the boolean value if all parameters are clear and set
     *
     * @var Boolean
     */    
    private $bClear = TRUE;

    /**
     * Array of the request attributes
     *
     * @var Array
     */
    public static $attribs = NULL;
    
    /**
     * Request Instance
     *
     * @var api_request
     */
    protected $rRequest = NULL;
    
    /** Defining Type Constants */
    const TBOOL = 1;
    const TINT  = 2;
    const TSTRING = 4;
    const TDATE = 8;
    const TTIMESTAMP = 16;
    const TARRAY = 32;
    
    protected static $rgType = Array(1=>"Boolean", 2=>"Integer", 4=>"String", 8=>"Date", 16=>"Timestamp", 32=>"Array");
    
    /** Defining Method Constants */
    const MGET = 1;
    const MPOST = 2;
    const MPUT = 4;
    const MDELETE = 8;
    const MATTRIBUTE = 16;
    
    protected static $rgMethod = Array(1=>"GET", 2=>"POST", 4=>"PUT", 8=>"DELETE", 16=>"PATH");
    
    /** Defining Required Constants */
    const RMANDATORY = 1;
    const ROPTIONAL = 2;
    
    protected static $rgNeed = Array(1=>"mandatory", 2=>"optional");

    
    public function __construct(&$attribs) {
        $this->attribs = $attribs;       
        $this->rRequest = api_request::getInstance();
    }
    
    /**
     * Sets the command for the parameters
     *
     * @param String|Array(String) $aCommand: Array of command-names
     * @return api_crud_param
     */
    public function setCommand($aCommand) {
        if (!is_array($aCommand)) {
            $aCommand = Array($aCommand);
        }
        
        $this->aCommand = $aCommand;
        
        return $this;
    }
    
    /**
     * Sets the type of a parameter
     *
     * @param Int $iType: Is one of api_crud_param::TINT / TBOOL / TSTRING / TDATE
     * @return api_crud_param : The current object
     */
    public function setType($iType) {
        $this->iType = $iType;
        
        return $this;
    }
    
    /**
     * Sets the req. method of a parameter
     *
     * @param Int $iMethod: Is one of api_crud_param::MGET / MPOST / MATTRIBUTE
     * @return api_crud_param : The current object
     */
    public function setMethod($iMethod) {
        $this->iMethod = $iMethod;
        
        return $this;
    }
    
    /**
     * Sets the neededness of a parameter
     *
     * @param Int $iNeed: Is one of api_crud_param::RMANDATORY / ROPTIONAL
     * @return api_crud_param : The current object
     */
    public function setNeed($iNeed) {
        $this->iNeed = $iNeed;
        
        return $this;
    }
    
    /**
     * Adds a parameter to the list of to-be-checked parameters
     *
     * @param String $strName Name of the parameter in the request
     * @param String $strStoredName Name of the parameter in the application
     * @return api_crud_param
     */
    public function addParam($strName, $strStoredName = null) {
        $p = new api_crud_param_value($strName, $this->iType, $this->iMethod, $this->iNeed, $strStoredName);
        $p->setAttribs($this->attribs);
        foreach ($this->aCommand as $command) {
            $this->aParam[$command][] = $p;
        }
        
        return $this;
    }    
    
    /**
     * Checks if all parameters are valid according to the definition
     *
     * @param String $strCommand
     * @return Boolean
     */
    public function checkParams($strCommand = null) {
        $this->strCommand = $strCommand = ($strCommand) ? $strCommand : $this->strCommand; 
        if (!$strCommand || is_array($strCommand)) {
            return FALSE;
        }
        
        if (!isset($this->aParam[$strCommand])) {
            return TRUE;
        }
        
        foreach ($this->aParam[$strCommand] as $param) {
            if (!$param->checkParam()) {
                return $this->bClear = FALSE;
            }
        }
        
        return $this->bClear;
    }
    
    /**
     * Returns all values cleared according to their definition
     *
     * @param String $strCommand
     * @return Array
     */
    public function getParams($strCommand = null) {
        $this->strCommand = $strCommand = ($strCommand) ? $strCommand : $this->strCommand; 
        if (!$this->strCommand || is_array($strCommand)) {
            return FALSE;
        }
        
        if (!isset($this->aParam[$strCommand])) {
            $req = api_request::getInstance();
            return (Array) $req->getParameters();
        }
        
        if (!isset($this->aParam[$strCommand])) {
            $this->checkParams($strCommand);
        }
        
        
        $val = Array();
        foreach($this->aParam[$strCommand] as $param) {
            $val[$param->strStoredName] = $param->value();
        }
        return $val;
    }
    
    /**
     * Returns a model of all information about the parameters needed / used
     * for the current command. It returns the parsed variables
     *
     * @return api_model_dom: Which can be used to store into $this->data[]
     */
    public function getInfo() {
        $dom = new DOMDocument();

        $p = $dom->createElement('params');
        $dom->appendChild($p);
        
        foreach ($this->aParam[$this->strCommand] as $command) {
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
    }
    

    /**
     * Returns whatever values are set in human readable format for a certain
     * Array which belongs to a constant
     *
     * @param Array $rgValue: Array of id => string for the constants
     * @param Integer $value: The Integer value which is used as bit pattern
     * @return Array: Array of Strings which tell what values are set
     */
    private static function getSet($rgValue, $value) {
        $val = Array();
        
        foreach ($rgValue as $bin => $name) {
            if (($value & $bin) == $bin) {
                $val[] = $rgValue[$bin];
            }
        }

        return $val;
    }
}


Class api_crud_param_value extends api_crud_param {
    protected $strName = null;
    protected $iType = null;
    protected $iMethod = null;
    protected $iNeed = null;
    protected $strStoredName = null;
    private $rawValue;
    private $mValue;
    
    /**
     * Creates a new parameter for a command
     *
     * @param String $strName: The name of the variable as it is sent by the client
     * @param Integer/Class constant $iType
     * @param Integer/Class constant $iMethod
     * @param Integer/Class constant $iNeed
     * @param String $strStoredName: The name of the variable in the application
     */
    public function __construct ($strName, $iType, $iMethod, $iNeed, $strStoredName = null) {
        $this->strName = $strName;
        $this->iType = $iType;
        $this->iMethod = $iMethod;
        $this->iNeed = $iNeed;
        
        if ($strStoredName == null) {
            $this->strStoredName = $strName;    
        } else {
            $this->strStoredName = $strStoredName;
        }
        $this->rRequest = api_request::getInstance();
    }
    
    /**
     * Checks and clears the value of a parameter according to it's type and
     * return TRUE if the parameter is still valid after beeing cleared, FALSE 
     * otherwise
     *
     * @return Boolean
     */
    public function checkParam () {
        if (isset($this->strName)) {
            $val = $this->getValue();
            // when val is set or is of type boolean (which would result in empty == true) or if it's not even required, return true
            if (!empty($val) || (($this->iType & self::TBOOL) == self::TBOOL && $val === FALSE) || (($this->iNeed & self::ROPTIONAL) == self::ROPTIONAL)) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
    }
    
    /**
     * Receives the parameter according to the Request Method and clears it and
     * checks it
     *
     * @return Mixed: Value of the variable
     */
    private function getValue () {
        $val = NULL;

        if (($this->iMethod & self::MGET) == self::MGET) {
                $val = ($tmp = $this->rRequest->getParameters()->get($this->strName)) ? $tmp : $val;
        }
        if (($this->iMethod & self::MPOST) == self::MPOST) {
                $val = ($tmp = $this->rRequest->getParameters()->post($this->strName)) ? $tmp : $val;
        }
        if (($this->iMethod & self::MATTRIBUTE) == self::MATTRIBUTE) {
            if (isset($this->attribs[$this->strName])) {
                $val = $this->attribs[$this->strName];
            }
        }
        
        $this->rawValue = $val;
        
        return $this->clearValue();
    }
    
    /**
     * Clears the value with simple functions according to it's type
     *
     * @return Mixed: Value of the variable
     */
    private function clearValue () {
        $iType = $this->iType;
        
        if (($iType == self::TARRAY)== self::TARRAY) {
            if (empty($clrValue)) {
                $clrValue = split(";",$this->rawValue);
                if (isset($clrValue[0]) && empty($clrValue[0])) {
                    $clrValue = FALSE;
                }
            }
        }
        if (($iType == self::TDATE)== self::TDATE) {
            if (empty($clrValue)) {
                $clrValue = strtotime($this->rawValue);
            }
        }
        if (($iType & self::TINT) == self::TINT || ($iType & self::TTIMESTAMP) == self::TTIMESTAMP) {
            if (empty($clrValue)) {
                $clrValue = intval($this->rawValue);
            }
        } elseif (($iType & self::TSTRING) == self::TSTRING) {
            if (empty($clrValue)) {
                $clrValue = strval($this->rawValue);
            }
        }
        if (($iType & self::TBOOL) == self::TBOOL) {
            // Yes, this will make stuff like ?foo=false to $foo be really false..
            if (empty($clrValue) || $this->clrValue == "false") {
                $clrValue = FALSE;
            }
        }
        
        $this->mValue = $clrValue;
        
        return $clrValue;
    }
    
    /**
     * Used to set the attributes of the request
     *
     * @param Array $attribs
     */
    protected function setAttribs(&$attribs) {
        $this->attribs = $attribs;
    }

    /**
     * Returns the value of the parameter in the current request 
     *
     * @return Mixed: Parsed value of the parameter
     */
    public function value() {
        return $this->mValue;
    }
}
