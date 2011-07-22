<?php
/**
 * Checks that the value is a valid longitude
*/
class api_validator_longitude extends api_validator_base {

    protected $invalidMessage = 'validator_longitude_invalid';

    public function checkValidity($value, $params) {
        $valid = false;
        if (is_numeric($value)) {
            $longitude = floatval($value);
            $valid = ($longitude >= -180 && $longitude <= 180); 
        }
        return $valid;
    }
}
