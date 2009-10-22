<?php
/* Licensed under the Apache License, Version 2.0
 * See the LICENSE and NOTICE file for further information
 */

/**
 * Exception thrown by api_controller if a command can't be loaded
 * for the current request.
 */
class api_exception_noCommandFound extends api_exception {
    public function __construct($message='No Command Found', $code=0, $params=array(), $severity=self::THROW_FATAL) {
        parent::__construct($message, $code, $params, $severity);
    }
}
