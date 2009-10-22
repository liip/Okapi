<?php
/* Licensed under the Apache License, Version 2.0
 * See the LICENSE and NOTICE file for further information
 */

/**
 * Exception thrown by api_controller if a command access is not allowed
 * for the current request.
 */
class api_exception_commandNotAllowed extends api_exception {
    public function __construct($message='Command Access Not Allowed!', $code=0, $params=array(), $severity=self::THROW_FATAL) {
        parent::__construct($message, $code, $params, $severity);
    }
}
