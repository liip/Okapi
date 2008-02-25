<?php
/**
*/
interface api_pam_interface_perm {

    public function isAllowed($uid, $acObject, $acValue);
    
}

