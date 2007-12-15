<?php
/**
 * Extends api_request with the possibility to set params.
 */
class mock_request extends api_request {
    public function __construct($params) {
        parent::__construct();
        
        foreach ($params as $key => $value) {
            $this->$key = $value;
        }
    }
}
?>
