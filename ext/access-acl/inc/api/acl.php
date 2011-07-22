<?php
/**
 * ACL Class
 * 
 * This contains just a bunch of ACL entries
 *
 */
class api_acl {
    /**
     * Attribs given from command
     *
     * @var Array
     */
    public $attribs;
    
    /**
     * Constant to define if a role is concatenated by AND
     */
    const ACL_OR = 1;

    /**
     * Constant to define if a role is concatenated by OR
     */
    const ACL_AND = 2;
    
    /**
     * Array of Entries (commands/models/whatever) which have ACLs
     *
     * @var unknown_type
     */
    private $_rgEntries = Array ();
    
    /**
     * List of already used classes which don't have a loadACL method
     *
     * @var Array
     */
    private $_rgBlacklist = Array ();
    
    /**
     * We make the constructor private since api_acl is a singleton
     */
    private function __construct($attribs) {
        $this->attribs = $attribs;
    }
    
    /**
     * Singleton constructor
     *
     * @param Boolean $forceReload Used for testing, when you really want to get
     * a new api_acl
     * @return api_acl
     */
    public static function getInstance($attribs, $forceReload = false) {
        static $instance;
        
        if (!isset($instance) || $forceReload) {
            $instance = new api_acl($attribs);
        }
        
        return $instance;
    }
    
    public function checkACL ($command, $method, $id = null, $uid = null) {
        if (!$this->_loadEntry($command)) {
            return True;
        }

        $e = $this->_rgEntries[$command];
        
        return $e->checkACL($method, $id, $uid);
    }
    
    public function getACL ($command) {
        if (!$this->_loadEntry($command)) {
            return Null;
        }
        
        $e = $this->_rgEntries[$command];
        
        return $e;
    }
    
    public function addEntry ($name, api_acl_entry $entry) {
        $this->_rgEntries[$name] = $entry;
    }
    
    private function _loadClass ($class) {
        // TODO: Take namespaces into account
        $classname = $this->attribs['namespace']. "_command_".$class;
        return new $classname($this->attribs);
    }
    
    private function _loadEntry ($name) {
        if ($this->_entryLoaded($name)) {
            return True;
        }

        if ($this->_entryBlacklisted($name)) {
            return False;
        }
        
        $c = $this->_loadClass($name);
        
        if (method_exists($c, "loadACL")) {
            $e = $c->loadACL();
            if (isset($e) and $e instanceof api_acl_entry) {
                $this->addEntry($name, $e);
                
                return True;
            }
        } else {
            $this->_rgBlacklist[] = $name;
        }
        
        return False;
    }
    
    private function _entryLoaded ($name) {
        return (key_exists($name, $this->_rgEntries) && $this->_rgEntries[$name] instanceof api_acl_entry);
    }
    
    private function _entryBlacklisted ($name) {
        return in_array($name, $this->_rgBlacklist);
    }
}

