<?php
/**
 * Checks that the value is in the given range
*/
class api_validator_inRange extends api_validator_base {

    protected $invalidMessage = 'validator_not_in_range';

    /**
     * @param $value
     * @param $params array(min, max)
    */
    public function checkValidity($value, $params) {
        $valid = false;
        if (is_numeric($value)) {
            $min = $params[0];
            $max = $params[1];
            $intval = intval($value);
            $valid = ($value >= $min && $value <= $max);
        }
        return $valid;
    }

}
