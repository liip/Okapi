<?php
/**
*/
class api_pam_auth_pearwrapper extends api_pam_auth implements api_pam_interface_auth {
    
    private $pAuth = null;
    private $pIdField = 'id';
    
    public function __construct($opts) {
        parent::__construct($opts);
        
        $this->pAuth = $this->getPearAuth();
        $this->setPearDefaults(); 
    
    }
    
    
    public function login($username, $passwd) {
        
        if ($this->pAuth) {
            
            $this->pAuth->username = $username;
            $this->pAuth->password = $passwd;
            $this->pAuth->login();
            
            return $this->checkAuth();
            
        }
        
        return false;
    }
    
    
    public function logout() {
        
        if ($this->pAuth) {
            return $this->pAuth->logout();
        }
        
        return false;
    }
    
    
    public function checkAuth() {
        
        if ($this->pAuth) {
            return $this->pAuth->checkAuth();
        }    
        
        return false;
    }
    
    
    public function getUserName() {
        
        if ($this->pAuth) {
            return $this->pAuth->getUsername();
        }
        
        return null;
    
    }
    
    /**
     * Returns the user's ID. Not all containers support this. For
     * containers that have no ID, the user name is returned instead.
     */
    public function getUserId() {
        $authData = $this->getAuthData();
        
        if (isset($authData[$this->pIdField])) {
            return $authData[$this->pIdField];
        } else {
            return $this->getUserName();
        }
    }
    
    public function getAuthData() {
        if ($this->pAuth) {
            return $this->pAuth->getAuthData();
        }
        
        return array();
    }
    
    
    private function setPearDefaults() {
        if ($this->pAuth) {
            
            $this->pAuth->setShowLogin(false);
            
        }
    }
    
    
    private function getPearAuth() {
        
        $container = $this->getOpt('container', 'MDB2');
        
        $a = new Auth($container, $this->opts);
        if ($a instanceof Auth) {
            return $a;
        }
        
        return false;
            
    }

}
