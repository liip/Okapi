<?php
/**
 * Checks that the value is plausibly a swiss zipcode
*/
class api_validator_plz extends api_validator_base {

    protected $invalidMessage = 'validator_plz_invalid';

    public function checkValidity($value, $params) {
        return preg_match('/\d{4}/', $value);
    }

}
