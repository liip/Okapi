<?php
/* Licensed under the Apache License, Version 2.0
 * See the LICENSE and NOTICE file for further information
 */

class api_exception_noViewFound extends api_exception {
    public function __construct($message='No View Found', $code=0, $params=array(), $severity=self::THROW_FATAL) {
        parent::__construct($message, $code, $params, $severity);
    }
}
