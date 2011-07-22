<?php
/**
 * Checks that the value matches the given regex
*/
class api_validator_regex extends api_validator_base {

    protected $invalidMessage = 'validator_regex_invalid';

    public function checkValidity($value, $params) {
        if (!isset($params['regex'])) {
            return false;
        }
        return preg_match($params['regex'], $value);
    }

}
