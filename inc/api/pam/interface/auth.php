<?php
/**
*/
interface api_pam_interface_auth {
    
    public function login($user, $pass);
    
    public function logout();
    
    public function checkAuth(); 
    
    public function getUserId();
    
    public function getUserName();
    
   
}

