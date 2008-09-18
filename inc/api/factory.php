<?php
/**
 * Inversion of control container for Okapi. Returns initialized objects
 * based on its basename. Has three strategies to initialize objects:
 *    1. Completely from configuration - Both the actual class name and all
 *       constructor parameters.
 *    2. Partial configuration - Class name form configuration and
 *       constructor parameters from the callee.
 *    3. Default - Class name is the base name prepended with "api_" and
 *       constructor parameters from the callee.
 *
 * This factory should get initialized by conf/classes.php.
 */
class api_factory {
    public function get($base) {
        $class = 'api_' . $base;
        return new $class();
    }
}
