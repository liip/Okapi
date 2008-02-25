<?php
/**
*/
class api_pam_perm_acl extends api_pam_perm implements api_pam_interface_perm {

    public function __construct($opts) {
        parent::__construct($opts);
    }
    
    public function isAllowed($uid, $acObject, $acValue) {
        return true;
    }

}
