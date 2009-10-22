<?php
/* Licensed under the Apache License, Version 2.0
 * See the LICENSE and NOTICE file for further information
 */

/**
 * Base exception for any libxml errors.
 */
class api_exception_libxml extends api_exception {
    public function __construct($message, $code=0, $params=array(), $severity=self::THROW_FATAL) {
        $this->userInfo = "";

        $errors = libxml_get_errors();
        $errorHash = array();

        if ($errors) {
            foreach ($errors as $error) {
                $this->userInfo .= $error->message;
                if ($error->file) {
                    $this->userInfo .= " in file ".$error->file ." line:".$error->line ;
                }
                $this->userInfo .= "<br/>";

                array_push($errorHash, array(
                    'level' => $error->level,
                    'code' => $error->column,
                    'message' => $error->message,
                    'file' => $error->file,
                    'line' => $error->line,
                ));
            }
        }
        libxml_clear_errors();

        $params['xmlerrors'] = $errorHash;
        parent::__construct($message, $code, $params, $severity);
    }
}
