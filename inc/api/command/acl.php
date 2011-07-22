<?php

/**
 * Base command class which provides general authorization features and easy access 
 * to logging and session handling functionality.
 *
 * @see api_pam
 * @see api_log
 * @see Zend_Acl_Resource_Interface
 * @see Zend_Session_Namespace
 */
abstract class api_command_acl extends api_command implements Zend_Acl_Resource_Interface {

    /**
     * @var api_pam
     */
    protected $pam;

    /**
     * @var api_log
     */
    protected $log;

    public function __construct($route) {
        parent::__construct($route);
        
        // Initialize authentication module
        $this->pam = api_pam::getInstance();
        
        // Initialize logger
        $this->log = api_log::getInstance();
    }

    /**
     * Returns the resource id of the command (the command
     * name to be exact).
     *
     * @return string
     */
    public function getResourceId() {
        return $this->route['command'];
    }

    public function redirect($path) {
        $this->response->redirect(API_WEBROOT . $path, 303);
    }

    /**
     * Asks the pam module if the active user is allowed to access
     * the method of the current request of this command (the command itself
     * is the resource).
     *
     * @return boolean
     */
    public function isAllowed() {
        
        try {
            // Dispatch to authentication module
            return $this->pam->isAllowed($this, $this->route['method']);
        
        } catch (Exception $e) {
            
            // If error occurs while checking permissions
            // deny access to command (for security reasons).
            return false;
        }
    }
}
