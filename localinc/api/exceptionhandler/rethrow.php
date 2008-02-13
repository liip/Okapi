<?php
/**
 * Rethrows exceptions. Used for test suite to test for exceptions.
 */
class api_exceptionhandler_rethrow extends api_exceptionhandler_default {
    public function handle(Exception $e) {
        throw $e;
    }
}
?>
