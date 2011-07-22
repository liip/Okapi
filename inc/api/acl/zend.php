<?php

class api_acl_zend extends Zend_Acl {

    protected $target = null;

    public function setTarget($target) {
        $this->target = $target;
    }

    public function getTarget() {
        return $this->target;
    }
}
