<?php
/* Licensed under the Apache License, Version 2.0
 * See the LICENSE and NOTICE file for further information
 */

class api_exception_noXsltFound extends api_exception {
    public function __construct($message='No XSLT Found', $code=0, $params=array(), $severity=self::THROW_FATAL) {
        parent::__construct($message, $code, $params, $severity);
    }
}
