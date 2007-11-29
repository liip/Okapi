<?php
/**
 * Ignores exceptions. Used for test suite ATM.
 */
class api_exceptionhandler_ignore extends api_exceptionhandler_base {
    public function __construct() {
        parent::__construct();
    }
    
    public function handle(Exception $e) {
    }
}
?>
