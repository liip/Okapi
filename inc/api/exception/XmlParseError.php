<?php
/**
 * Exception when an XML file could not be parsed.
 */
class api_exception_XmlParseError extends api_exception_LibxmlError {
    public function __construct($severity, $filename) {
        parent::__construct($severity, $filename);
        $this->message = "XML parse error in {$filename}";
    }
}
