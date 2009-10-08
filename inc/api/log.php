<?php
/* Licensed under the Apache License, Version 2.0
 * See the LICENSE and NOTICE file for further information
 */

/**
 * Wrapper class for Zend_Log which reads configuration from api_config
 * and creates the corresponding Log objects.
 *
 * The configured logger is available through api_log::$logger.
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
        'EMERG' => api_log::EMERG,
        'ALERT' => api_log::ALERT,
        'CRIT' => api_log::CRIT,
        'FATAL' => Zend_Log::CRIT,
        'ERR' => api_log::ERR,
        'ERROR' => Zend_Log::ERR,
        'WARN' => api_log::WARN,
        'NOTICE' => api_log::NOTICE,
        'INFO' => api_log::INFO,
        'DEBUG' => api_log::DEBUG
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

    public function __call($method, $params) {
        $prio = $this->getMaskFromLevel($method);
        $this->logMessage($params, $prio);
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
     */
    public function log($prio) {
        if ($this->logger === false) {
            return false;
        }

        $params = func_get_args();
        array_shift($params);

        return $this->logMessage($params, $prio);
    }

    protected function logMessage($params, $prio) {
        if ($this->logger === false) {
            return false;
        }

        $message = array_shift($params);
        if (!empty($params)) {
            $message = vsprintf($message, $params);
        }

        if (!is_int($prio)) {
            $prio = $this->defaultPriority;
        }

        return $this->logger->log($message, $prio);
    }
}
