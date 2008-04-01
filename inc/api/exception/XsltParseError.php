<?php
/**
 * Exception when an XSLT file could not be parsed.
 */
class api_exception_XsltParseError extends api_exception_LibxmlError {
    /**
     * Constructor. 
     *
     * @param $severity int: Indicates whether the exception is fatal or not.
     *        Use api_exception::THROW_NONE or api_exception::THROW_FATAL.
     * @param $filename string: Name of the file that caused the exception.
     */
    public function __construct($severity, $filename) {
        parent::__construct($severity, $filename);
        $this->message = "XSLT error in {$filename}";
    }
}
