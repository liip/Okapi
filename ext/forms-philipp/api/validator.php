<?php

class api_validator {

    static private $instances = Array();

    static private $fields = null;
    static private $data = null;

    public function __construct($fields, $data) {
        self::$fields = $fields;
        self::$data = $data;
    }
    
    public static function get($validatorName) {
        if(!isset(self::$instances[$validatorName])) {
            $className = 'api_validator_'.$validatorName;
            $params = array(
                'fields' => self::$fields,
                'data' => self::$data,
                );
            self::$instances[$validatorName] = new $className($params);
        }
        return self::$instances[$validatorName];
    }
    
    public static function isValid($validatorName, $value, $params = Array()) {
        $validator = self::get($validatorName);
        return $validator->isValid($value, $params);
    }

    public static function setData($data) {
        self::$data = $data;
    }

    public static function setFields($fields) {
        self::$fields = $fields;
    }
    
    public static function getMessage($validatorName) {
        $validator = self::get($validatorName);
        return $validator->getMessage();
    }

}




