<?php
/**
 * Exception thrown by api_controller if a command can't be loaded
 * for the current request.
 */
class api_exception_NoCommandFound extends api_exception {
    /**
     * Constructor.
     *
     * @param $msg string: User message.
     */
    public function __construct($msg = 'No Command Found!') {
        parent::__construct();
        $this->setMessage($msg);
        $this->setSeverity(api_exception::THROW_FATAL);
    }
}
