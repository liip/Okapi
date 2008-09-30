<?php
/**
 * Wrapper class for Zend_Log which reads configuration from api_config
 * and creates the corresponding Log objects.
 *
 * The configured logger is available through api_log::$logger.
 */
class api_log {
    
    const EMERG   = 0;  // Emergency: system is unusable
    const ALERT   = 1;  // Alert: action must be taken immediately
    const CRIT    = 2;  // Critical: critical conditions
    const ERR     = 3;  // Error: error conditions
    const WARN    = 4;  // Warning: warning conditions
    const NOTICE  = 5;  // Notice: normal but significant condition
    const INFO    = 6;  // Informational: informational messages
    const DEBUG   = 7;  // Debug: debug messages
    
    
    /** Log: Configured logger. */
    public static $logger = null;
    
    
    /** api_log instance */
    protected static $instance = null;
    
    /**
     * Initialize the logger.
     */
    
    public function __construct() {
        if (self::$logger !== null) {
            // Already initialized
            return;
        }
        
        $configs = api_config::getInstance()->log;
        if (!$configs ||  count($configs) == 0 || !$configs[0]['class']) {
             self::$logger = false;
             return;
        }
        
        self::$logger = new Zend_Log();
        
        foreach ($configs as $cfg) {
            $log = $this->createLogObject($cfg['class'], $cfg);
            self::$logger->addWriter($log);
        }
    }
    
    /**
     * Gets an instance of api_config.
     * @param $forceReload bool: If true, forces instantiation of a
     *        new instance. Used for testing.
     * @return api_log an api_log instance;
     */
    public static function getInstance($forceReload = FALSE) {
        if (! self::$instance instanceof api_log || $forceReload) {
            self::$instance = new api_log();
        }

        return self::$instance;
    }
    
    
    public function __call($method, $params) {
        $prio = self::getMaskFromLevel($method);
        $message = array_shift($params);
        if (count($params) > 0) {
            $message = vsprintf($message, $params);
        }
        
        $this->log($message, $prio);
    }
    
    protected function createLogObject($name, $config) {
        $classname = 'Zend_Log_' . $name;
        $params = isset($config['cfg']) ? $config['cfg'] : array();
        if (!is_array($params)) {
            $params = array($params);
        }
        $class = new ReflectionClass($classname);
        $object = $class->newInstanceArgs($params);
        
        if (isset($config['priority'])) {
            $object->addFilter(
                new Zend_Log_Filter_Priority(
                    $this->getMaskFromLevel($config['priority'])));
        }
        
        return $object;
    }
    
    /**
     * Return a api_log mask for a string level.
     */
    protected function getMaskFromLevel($level) {
        $masks = array(
            'EMERG'   => api_log::EMERG,
            'ALERT'   => api_log::ALERT,
            'CRIT'    => api_log::CRIT,
            'ERR'     => api_log::ERR,
            'WARN'    => api_log::WARN,
            'NOTICE'  => api_log::NOTICE,
            'INFO'    => api_log::INFO,
            'DEBUG'   => api_log::DEBUG,
        );
        return $masks[strtoupper($level)];
    }

    public function log($message,$prio = null) {
        if (self::$logger === false) {
            return;
        }
        if (!$prio) {
            $prio = self::INFO;
        }
        self::$logger->log($message,$prio);
    }
}
