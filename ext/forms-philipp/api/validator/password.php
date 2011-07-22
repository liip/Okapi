<?php
require_once 'Zend/Validate/Abstract.php';

class api_validator_password extends api_validator_base
{

    protected $invalidMessage = 'validator_password_weak';

    public function checkValidity($value, $options)
    {
        if (!empty($options['length'])) {
            if (strlen($value) < $options['length']) {
                return false;
            }
        }
        if (!empty($options['upper'])) {
            if (!preg_match('/[A-Z]/', $value)) {
                return false;
            }
        }
        if (isset($options['lower'])) {
            if (!preg_match('/[a-z]/', $value)) {
                return false;
            }
        }
        if (isset($options['digit'])) {
            if (!preg_match('/\d/', $value)) {
                return false;
            }
        }
        if (isset($options['special_char'])) {
            if (!preg_match('/\W/', $value)) {
                return false;
            }
        }
        return true;
    }
}

