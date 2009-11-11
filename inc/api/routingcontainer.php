<?php

class api_routingcontainer {
    public function __construct($routing) {
        // Load routing configuration
        require API_PROJECT_DIR . 'conf/commandmap.php';
    }
}
