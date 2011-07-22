<?php
/**
 * Extends api_request with the possibility to set params.
 */
class mock_request extends api_request {
    public function __construct($params) {
        // When we instanciate api_request, we do not have the right path
        if (isset($params['path'])) {
            $_SERVER['REQUEST_URI'] = $params['path'];
        }
        parent::__construct();
        
        foreach ($params as $key => $value) {
            if ($key != 'path')
                $this->$key = $value;
        }
    }
}
?>
