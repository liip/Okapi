<?php

/**
 * ACL permissions checking
 */
class api_pam_perm_zend extends api_pam_common implements api_pam_Iperm {

    const ACL_WILDCARD = '*';

    protected $acl;

    public function __construct($opts) {
        parent::__construct($opts);
        
        $config = api_config::getInstance()->acl;
        $this->acl = new api_acl_zend();
        
        if (isset($config['roles'])) {
            foreach ($config['roles'] as $role) {
                $this->acl->addRole(new Zend_Acl_Role($role));
            }
        }
        
        if (isset($config['resources'])) {
            foreach ($config['resources'] as $resource => $permissions) {
                $permissions = is_array($permissions) ? $permissions : array(
                        $permissions
                );
                
                if ($resource != self::ACL_WILDCARD) {
                    $this->acl->add(new Zend_Acl_Resource($resource));
                    
                    foreach ($permissions as $privilege => $role) {
                        if ($privilege != self::ACL_WILDCARD) {
                            $this->acl->allow($role, $resource, $privilege);
                        } else {
                            $this->acl->allow($role, $resource);
                        }
                    }
                
                } else {
                    foreach ($permissions as $role) {
                        $this->acl->allow($role);
                    }
                }
            }
        }
    }

    public function isAllowed($uid, $acObject, $acValue) {
        
        // redirect to login
        if (empty($uid)) {
            api_response::getInstance()->redirect(API_WEBROOT . $this->getOpt('loginpath'), 303);
        }
        
        // set target
        if ($acObject instanceof api_resource_zend) {
            $this->acl->setTarget($acObject->getTarget());
        }
        
        $database = api_db::factory();
        
        $select = $database->select();
        $select->from($this->getOpt('table'), $this->getOpt('namecol'));
        $select->where($this->getOpt('condition'), $uid);
        
        $statement = $database->query($select);
        
        foreach ($statement->fetchAll(Zend_Db::FETCH_NUM) as $role) {
            
            // ask zend_acl if role has permission on resource $acObject and privilege $acValue
            if ($this->acl->isAllowed(reset($role), $acObject, $acValue)) {
                return true;
            }
        }
        
        // not allowed
        return false;
    }
}
