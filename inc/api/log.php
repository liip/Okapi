<?php
/**
 * Wrapper class for Zend_Log which reads configuration from api_config
 * and creates the corresponding Log objects.
 *
 * The configured logger is available through api_log::$logger.
 */
class api_log {
    /** Log: Configured logger. */
    public static $logger = null;
    
    /**
     * Initialize the logger.
     */
    public function __construct($force = false) {
        if (self::$logger != null && !$force) {
            // Already initialized
            return;
        }
        
        self::$logger = new Zend_Log();
        
        $configs = api_config::getInstance()->log;
        if (is_null($configs) || count($configs) == 0) {
            self::$logger->addWriter(new Zend_Log_Writer_Null());
            return;
        }
        
        foreach ($configs as $cfg) {
            $log = $this->createLogObject($cfg['class'], $cfg);
            self::$logger->addWriter($log);
        }
    }
    
    public function __call($method, $params) {
        $prio = self::getMaskFromLevel($method);
        $message = array_shift($params);
        if (count($params) > 0) {
            $message = vsprintf($message, $params);
        }
        
        self::$logger->log($message, $prio);
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
     * Return a Zend_Log mask for a string level.
     */
    protected function getMaskFromLevel($level) {
        $masks = array(
            'EMERG'   => Zend_Log::EMERG,
            'ALERT'   => Zend_Log::ALERT,
            'CRIT'    => Zend_Log::CRIT,
            'ERR'     => Zend_Log::ERR,
            'WARN'    => Zend_Log::WARN,
            'NOTICE'  => Zend_Log::NOTICE,
            'INFO'    => Zend_Log::INFO,
            'DEBUG'   => Zend_Log::DEBUG,
        );
        return $masks[strtoupper($level)];
    }
}
