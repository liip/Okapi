<?php

class api_validator_base {

    protected $message = null;
    protected $invalidMessage = 'validator_invalid_value';

    public function isValid($value, $params=array()) {
        $this->clearMessage();
        if (isset($params['message'])) {
            $this->invalidMessage = $params['message'];
        }
        if (!$this->checkValidity($value, $params)) {
            $this->setMessage($this->invalidMessage);
            return false;
        } else {
            return true;
        }
    }

    protected function checkValidity($value, $params) {
        return false;
    }
    
    public function getMessage() {
        return $this->message;
    }

    protected function setMessage($message) {
        $this->message = $message;
    }

    protected function clearMessage() {
        $this->message = null;
    }
    
}
