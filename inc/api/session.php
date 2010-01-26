<?php

/* Licensed under the Apache License, Version 2.0
 * See the LICENSE and NOTICE file for further information
 */

/**
 * handles session storage, flash messages and temporary
 * request storage
 */
class api_session {
    /**
     * session data that will be stored and available
     * in the next requests until deleted
     */
    const STORE     = 1;

    /**
     * temporary session data that is only available
     * throughout the current request
     */
    const REQUEST   = 2;

    /**
     * flash message storage, can be combined with STORE
     * and REQUEST too to define when it expires. however
     * in this case the STORE means expire at next request,
     * i.e. to display a message after a redirect
     */
    const FLASH     = 4;

    /**
     * @var api_session_Idriver
     */
    protected $storage;

    /**
     * @param api_session_Idriver $storage the storage class to use for the session
     */
    public function __construct($storage) {
        $this->storage = $storage;
    }

    public function read($key = null) {
        return $this->storage->read($key);
    }

    public function readFlash($key = null) {
        return $this->storage->read($key, self::FLASH);
    }

    public function write($key, $value) {
        return $this->storage->write($key, $value, self::STORE | self::REQUEST);
    }

    public function writeRequest($key, $value) {
        return $this->storage->write($key, $value, self::REQUEST);
    }

    /**
     * writes a message into the flash storage
     *
     * @param string $key
     * @param mixed $value usually some text to display, but you can pass anything really
     * @param string $type message type, just a commodity you can use to style messages differently
     * @param bool $nextRequest if false, the flash message is only available during this request,
     *                          by default it will be available to this and the next request
     * @return bool success
     */
    public function writeFlash($key, $value, $type = 'notice', $nextRequest = true) {
        return $this->storage->write($key, array('type' => $type, 'value' => $value), self::FLASH | self::REQUEST | ($nextRequest ? self::STORE : 0));
    }

    public function delete($key) {
        return $this->storage->delete($key, self::STORE | self::REQUEST);
    }

    public function deleteRequest($key) {
        return $this->storage->delete($key, self::REQUEST);
    }

    public function deleteFlash($key) {
        return $this->storage->delete($key, self::REQUEST | self::STORE | self::FLASH);
    }

    public function regenerateId($deleteOld = false) {
        return $this->storage->regenerateId($deleteOld);
    }

    public function __get($p) {
        return $this->read($p);
    }

    public function __set($p, $val) {
        return $this->write($p, $val);
    }

    public function __unset($p) {
        return $this->delete($p);
    }

    public function __isset($p) {
        return $this->read($p) !== null;
    }

    public function __destruct() {
        return $this->storage->commit();
    }
}