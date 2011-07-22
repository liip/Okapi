<?php

class api_acl_entry_doctrine extends api_acl_entry {
    /**
     * Base query used to check for ACLs
     *
     * @var Doctrine_Query
     */
    private $_baseQuery = null;
    
    /**
     * Array containing roles which contain query-parts
     *
     * @var Array
     */
    private $_aRoles = Array ();
    
    /**
     * Array containing Arrays of ASTs (Abstract Syntax Tree)
     *
     * @var Array
     */
    private $_aPermissions = Array();
    
    /**
     * Stores the already loaded ACLs. This is used, when you load acls for multiple
     * ids and then want to check them
     *
     * @var Array
     */
    private $_aACL = Array();
    
    /**
     * Keeps information if the acls are already loaded
     *
     * @var Array
     */
    private $_aLoadedACL = Array();
    
    public function addMethodPermission($perm, $method) {
        $this->_aPermissions[$method][] = $perm;
    }
    
    public function addRole($role, $definition) {
        $this->_aRoles[$role] = $definition;
    }
    
    public function checkACL($method, $id = null, $uid = null) {
        if (!$this->methodHasACL($method)) {
            return True;       
        }
        
        // If our ACLs are already loaded, don't load them again
        // Just check against the keys
        if ($this->_isACLLoaded($method)) {
            if ($this->_aACL[$method] === False) {
                return True;
            }
            return (in_array($id, $this->getKeys($method, $id, $uid)));
        }
        
        $q = $this->_buildQuery($method, $id, $uid);
        
        // TODO: let user decide what connection to take
        api_db::factory('read');
        
        $iRows = count($q->execute(Array(), Doctrine::HYDRATE_ARRAY));
        
        return (bool) $iRows;
    }
    
    public function loadACL($method, $id, $uid = null) {
        if (!$this->methodHasACL($method)) {
            return False;       
        }
        
        $q = $this->_buildQuery($method, $id, $uid);
        
        // TODO: let user decide what connection to take
        api_db::factory('read');
        
        $this->_aACL[$method] = $q->execute(Array(), Doctrine::HYDRATE_ARRAY);
        
        $this->_aLoadedACL[$method] = True;
    }
    
    public function unloadACL($method) {
        $this->_aLoadedACL[$method] = False;
    }
    
    public function getKeys($method, $id, $uid = null) {
        if (!$this->methodHasACL($method)) {
            return False;
        }
        
        if ($this->_isACLLoaded($method)) {
            if ($this->_aACL[$method] === False) {
                return Array();
            }

            $aRows = $this->_aACL[$method];
        } else {
            $q = $this->_buildQuery($method, $id, $uid);
            
            // TODO: let user decide what connection to take
            api_db::factory('read');
            $aRows = $q->execute(Array(), Doctrine::HYDRATE_ARRAY);
        }
                    
        if (!count($aRows)) {
            return Array();
        }
        
        $ret = Array();
        
        foreach ($aRows as $row) {
            $ret[] = $row['ACLid'];
        }
        
        return $ret;
    }
    
    public function methodHasACL($method) {
        return (isset($this->_aPermissions[$method]) && is_array($this->_aPermissions[$method]));
    }
    
    public function setBaseQuery($query, $args = null) {
        $this->_baseQuery = Array('q' => $query, 'args' => $args);
    }
    
    private function _parseAST ($subtree) {
        if (is_string($subtree)) {
            if ($this->_hasRole($subtree)) {
                return "(" . $this->_aRoles[$subtree] . ")";
            }
        }
        
        if (count($subtree) != 3) {
            throw new api_exception_Backend(null, null, null, "Subtree parsing could not complete, check your ACL definition");
        }

        switch($subtree[0]) {
            case 1:
                return "( " . $this->_parseAST($subtree[1]) . " OR " . $this->_parseAST($subtree[2]) . " )";
                break;
            case 2:
                return "( " . $this->_parseAST($subtree[1]) . " AND " . $this->_parseAST($subtree[2]) . " )"; 
                break;
            default:
                return "( " . $this->_parseAST($subtree[1]) . "OR" . $this->_parseAST($subtree[2]) . " )";
                break;
        }
    }
    
    private function _hasRole ($role) {
        return (isset($this->_aRoles[$role]) && isset($this->_aRoles[$role]['q']));
    }
    
    /**
     * Used to build together the Doctrine_Queryg from all gathered information
     *
     * @param String $method method
     * @param String|Array $id The ids for which the acls need to be processed
     * @param Integer $uid The userid (optional)
     * @return Doctrine_Query
     */
    private function _buildQuery ($method, $id = null, $uid = null) {
        $q = $this->_baseQuery['q'];
        
        // Loop through all permissions for given method and parse
        // them and add them as where
        foreach ($this->_aPermissions[$method] as $perm) {
            $where = $this->_parseAST($perm);
            $where = str_replace("{uid}", $uid, $where);
            $q->addWhere($where);
        }
        
        // If there is no $id, then it must be something which does not have an id ;) and queries differently
        if (!empty($id) && is_array($id)) {
            $elems = "id IN (".implode(",", $id).")";
        } elseif (!empty($id) && $id) {
            $elems = "id = ".$id;
            $q->limit(1);
        } else {
            $elems = "True";
            $q->limit(1);
        }
        
        $q->addWhere($elems);
        
        return $q;
    }
    
    private function _isACLLoaded($method) {
        return (isset($this->_aLoadedACL[$method]) && $this->_aLoadedACL[$method] );
    }
}

?>
