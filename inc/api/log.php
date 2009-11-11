<?php
/* Licensed under the Apache License, Version 2.0
 * See the LICENSE and NOTICE file for further information
 */

/**
 * Wrapper class for Zend_Log
 *
 * Usage examples:
 * $log->warn('message: %s', $someError)
 * $log->log(api_log::WARN, 'message: %s', $someError)
 * @see __call()
 * @see log()
 */
class api_log {

    const EMERG = 0; // Emergency: system is unusable
    const ALERT = 1; // Alert: action must be taken immediately
    const CRIT = 2; // Critical: critical conditions
    const ERR = 3; // Error: error conditions
    const WARN = 4; // Warning: warning conditions
    const NOTICE = 5; // Notice: normal but significant condition
    const INFO = 6; // Informational: informational messages
    const DEBUG = 7; // Debug: debug messages

    protected $masks = array(
        'EMERG' => self::EMERG,
        'ALERT' => self::ALERT,
        'CRIT' => self::CRIT,
        'FATAL' => self::CRIT,
        'ERR' => self::ERR,
        'ERROR' => self::ERR,
        'WARN' => self::WARN,
        'NOTICE' => self::NOTICE,
        'INFO' => self::INFO,
        'DEBUG' => self::DEBUG
    );

    /** Log: Configured logger. */
    public $logger = null;

    /** lowest priority **/
    protected $priority = null;

    /** default priority **/
    protected $defaultPriority = self::ERR;

    /**
     * Initialize the logger.
     */
    public function __construct($logger, $priority = null, $global = false) {
        $this->logger = $logger;

        $priority = is_null($priority)
            ? $this->defaultPriority : $this->getMaskFromLevel($priority);
        $this->logger->addFilter($priority);
        $this->priority = $priority;

        if ($global) {
            $GLOBALS['log'] = $this;
        }
    }

    /**
     * call this class with any of the defined constants as the method
     * name to easily log a message with that priority level
     *
     * i.e. $log->warn('message: %s', $someError) equals $log->log(api_log::WARN, 'message: %s', $someError)
     *
     * any extra parameter following the message will be used to inject data in the message through vsprintf
     */
    public function __call($method, $params) {
        $priority = $this->getMaskFromLevel($method);
        $this->logMessage($priority, $params);
    }

    public function isLogging() {
        return $this->logger !== false;
    }

    public function getPriority() {
        return $this->priority;
    }

    /**
     * Return a api_log mask for a string level.
     */
    protected function getMaskFromLevel($level) {
        return $this->masks[strtoupper($level)];
    }

    /**
     * Log a message if a logger is configured
     *
     * @param int $priority one of the api_log constants defining the logging priority or a string (see __call)
     * @param string $message the message value
     * @param mixed $rest any other parameter will be used to inject data in the message through vsprintf
     * @return bool success
     */
    public function log($priority, $message = "") {
        if ($this->logger === false) {
            return false;
        }

        $params = func_get_args();
        array_shift($params);

        if (is_string($priority)) {
            $priority = $this->getMaskFromLevel($priority);
        }
        return $this->logMessage($priority, $params);
    }

    /**
     * sends the message to the internal logger instance
     */
    protected function logMessage($priority, $params) {
        if ($this->logger === false) {
            return false;
        }

        $message = array_shift($params);
        if (!empty($params)) {
            $message = vsprintf($message, $params);
        }

        if (!is_int($priority)) {
            $priority = $this->defaultPriority;
        }

        return $this->logger->log($message, $priority);
    }
}
