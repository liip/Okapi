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
    protected $config = array();
    protected static $instances = array();
    
    public function __construct($config = array()) {
        if (is_array($config)) {
            $this->config = $config;
        }
    }
    
    /**
     * Returns a newly created object.
     */
    public function create($base, $init = array()) {
        $class = $this->getClassConfig($base);
        $name = $class['class'];
        $init = array_merge($class['init'], $init);
        
        if (count($init) == 0) {
            return new $name();
        } else {
            $classObj = new ReflectionClass($name);
            return $classObj->newInstanceArgs($init);
        }
    }
    
    /**
     * Returns an instance of the given class.
     * Always returns the same instance.
     */
    public function get($base, $init = array()) {
        if (!isset(self::$instances[$base])) {
            self::$instances[$base] = $this->create($base, $init);
        }
        return self::$instances[$base];
    }
    
    protected function getClassConfig($base) {
        $cfg = array(
            'class' => 'api_' . $base,
            'init' => array());
        
        if (!isset($this->config[$base])) {
            return $cfg;
        }
        
        $thisCfg = $this->config[$base];
        if (is_array($thisCfg)) {
            $cfg = array_merge($cfg, $thisCfg);
        } else {
            $cfg['class'] = $thisCfg;
        }
        
        return $cfg;
    }
}
