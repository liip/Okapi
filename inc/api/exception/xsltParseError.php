<?php
/* Licensed under the Apache License, Version 2.0
 * See the LICENSE and NOTICE file for further information
 */

/**
 * Exception when an XSLT file could not be parsed.
 */
class api_exception_xsltParseError extends api_exception_libxml {
    /**
     * Constructor.
     *
     * @param $severity int: Indicates whether the exception is fatal or not.
     *        Use api_exception::THROW_NONE or api_exception::THROW_FATAL.
     * @param $filename string: Name of the file that caused the exception.
     */
    public function __construct($message='Command Access Not Allowed!', $code=0, $params=array(), $severity=self::THROW_FATAL) {
        if (isset($params['errors'])) {
            foreach ($params['errors'] as $error) {
                $message .= "\n".$error->message;
            }
        }
        parent::__construct($message, $code, $params, $severity);
    }
}
