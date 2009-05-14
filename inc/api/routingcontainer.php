<?php

class api_routingcontainer {
    public function __construct($routing) {
        // Load routing configuration
        require_once API_PROJECT_DIR . 'conf/commandmap.php';
    }
}
