<?php
/**
 * Simple exception handler. Just prints the exception base name to
 * the browser.
 */
class api_exceptionhandler_plain extends api_exceptionhandler_base {
    public function handle(Exception $e) {
        print "<h1>". api_helpers_class::getBaseName($e) ." Exception</h1>";
        return true;
    }
}