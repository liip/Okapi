<?php

abstract class api_acl_entry {

    abstract public function addRole ($entry, $definition);
    
    abstract public function addMethodPermission ($perm, $method);
    
    abstract public function methodHasACL ($method);
    
    abstract public function checkACL ($method, $id = null, $uid = null);
    
}
