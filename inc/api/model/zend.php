<?php

class api_model_zend extends Zend_Db_Table_Abstract {

    private static $cache = null;

    public function __construct($config = array()) {

        if(!isset($config['db'])) {
            $config['db'] = api_db::factory();
        }

        parent::__construct($config);
    }
}
