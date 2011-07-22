<?php
/**
 * Checks that the value is a valid date
*/
class api_validator_date extends api_validator_base {

    protected $invalidMessage = 'validator_date_invalid';

    /**
     * @param $value a date in a format accepted by the php date_parse function
    */
    public function checkValidity($value, $params) {
        $valid = false;
        $parsed = date_parse($value);
        if (!($parsed === false || $parsed['error_count'] > 0)) {
            $valid = checkdate($parsed['month'], $parsed['day'], $parsed['year']);
        }
        return $valid;
    }

}
