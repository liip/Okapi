<?php
/**
 * Exception thrown by api_controller if a command can't be loaded
 * for the current request.
 */
class api_exception_NoXsltFound extends api_exception {
    /**
     * Constructor.
     *
     * @param $msg string: User message.
     */
    public function __construct($msg = 'No Xslt Found!') {
        parent::__construct();
        $this->setMessage($msg);
        $this->setSeverity(api_exception::THROW_FATAL);
    }
}
