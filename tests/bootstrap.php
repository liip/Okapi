<?php
define("API_PROJECT_DIR", realpath(dirname(__FILE__)."/../") . '/');
ini_set("include_path", dirname(__FILE__)."/../inc/" . ':' .
                        dirname(__FILE__)."/" . ':' . 
                        ini_get("include_path"));

require_once('api/testing/mocks/functional/api/response.php');
# require_once('api/testing/mocks/functional/api/model/factory.php');
require_once('api/autoload.php');