<?php
/**
 * Checks that the value is a valid latitude
*/
class api_validator_latitude extends api_validator_base {

    protected $invalidMessage = 'validator_latitude_invalid';

    public function checkValidity($value, $params) {
        $valid = false;
        if (is_numeric($value)) {
            $latitude = floatval($value);
            $valid = ($latitude >= -90 && $latitude <= 90); 
        }
        return $valid;
    }

}
