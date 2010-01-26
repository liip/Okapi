<?php

/* Licensed under the Apache License, Version 2.0
 * See the LICENSE and NOTICE file for further information
 */

/**
 * session driver interface
 * @see api_session_php for docs
 */
interface api_session_Idriver {
    public function read($key = null, $mode = 0);
    public function write($key, $value, $mode = 0);
    public function delete($key, $mode = 0);
    public function commit();
    public function regenerateId($deleteOld = false);
}