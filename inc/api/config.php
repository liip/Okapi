<?php
require_once(dirname(__FILE__) . '/vendor/spyc.php');
 
/**
 * Generic config file class.
 * 
 * Reads a YAML configuration file or config directory and provides the
 * values.
 *
 * The configuration is read from either the file conf/config.yml or
 * the directory conf/config.d. If both exist, only the file is read.
 * When the directory is used, all *.yml files inside that directory
 * are concatenated to one big YAML file and the resulting YAML document
 * is loaded.
 *
 * The YAML file must represent a hash with the top keys representing the
 * different environments. For example this configuration defines the two
 * environments default and trunk:
 *
 * <pre>
 * default:
 *     example: 1
 *
 * trunk:
 *     example: 2</pre>
 * 
 * The environment variable OKAPI_ENV can be used to specify which
 * profile should be loaded. In Apache httpd mod_env can be used to
 * specify the environment. Example: <pre>SetEnv OKAPI_ENV trunk</pre>
 *
 * Values in the configuration file can reference Okapi constants as
 * defined in api_init. To use a constant, the syntax <tt>{CONSTANT}</tt>
 * is used. For example a log file could be configured relative to the
 * project root:
 *
 * <pre>
 * default:
 *     logfile: {API_PROJECT_DIR}logs/app.log</pre>
 *
 * @see http://httpd.apache.org/docs/2.2/mod/mod_env.html Apache httpd mod_env documentation
 * @see http://yaml.kwiki.org/?YamlInFiveMinutes YAML in five minutes
 * @config <b>configCache</b> (bool): Turns config caching on or off.
 */
class api_config {
    /** The default environment. This is used if no OKAPI_ENV environment
      * variable is defined. */
    static private $DEFAULT_ENV = 'default';
    
    /** Directory where the cache file is written to. */
    private $cacheDir = '/tmp/okapi-cache/';
    
    /** The loaded configuration array for the current profile. */
    private $configArray = array();
    
    /** The currently active environment. */
    private $env;
    
    /** api_config instance */
    private static $instance = null;
    
    /** Custom loader. See setLoader() */
    private static $loader = null;
    
    public static function getInstance($force = FALSE) {
        if (! self::$instance instanceof api_config || $force) {
            self::$instance = new api_config();
        }
        
        return self::$instance;
    }
    
    /**
     * Set a custom loader.
     * The loader is an object used to load the configuration. The object
     * must implement a method load($env) which returns a full configuration
     * array. The parameter $env is the environment to load. Used to
     * implement custom loading strategies which don't necessarily use YAML.
     */
    public static function setLoader($loader) {
        self::$loader = $loader;
    }
    
    /**
     * Constructor. Loads the configuration file into memory.
     */
    private function __construct() {
        if (isset($_SERVER['OKAPI_ENV'])) {
            $this->env = $_SERVER['OKAPI_ENV'];
        } else {
            $this->env = self::$DEFAULT_ENV;
        }
        
        if (!is_null(self::$loader)) {
            $this->configArray = self::$loader->load($this->env);
            return;
        }
        
        $base = API_PROJECT_DIR . 'conf/config';
        $configfile = $base . '.yml';
        $configdir = $base . '.d';
        if (file_exists($configfile)) {
            $this->init($configfile);
        } else {
            $yaml = '';
            foreach (glob($configdir . '/*.yml') as $file) {
                $yaml .= file_get_contents($file) . "\n";
            }
            $this->init($yaml);
        }
    }
    
    /**
     * Destructor. Dump the loaded configuration file into a PHP file.
     * On loading the configuration that PHP file is then used instead of
     * the YAML file. Loading is then faster as the YAML parsing can be
     * slow.
     * 
     * This behaviour must be turned explicitly by setting
     * the configCache configuration value to true.
     */
    public function __destruct() {
        // config-caching can be disabled via config (for testing purposes)
        if (! $this->configCache) {
            return;
        }
        
        self::$instance = null;
        
        // write cache
        $cachedata = var_export($this->configArray, true);
        $cache = '<?php $configCache='.$cachedata."; ?>";
        $cachefile = $this->getConfigCachefile();
         
        try {
            file_put_contents($cachefile, $cache);
        } catch(Exception $e) {
            echo "Writing cache failed ...\n";
        }
    }
    
    /**
     * Reads the YAML configuration. Also calls replaceAllConsts on the
     * resulting YAML document to replace constants.
     * 
     * @param $yaml string: File name or complete YAML document as a string.
     */
    private function init($yaml) {
        // read cache
        if (! $this->readCache()) {
            $cfg = Spyc::YAMLLoad($yaml);
            if (!isset($cfg[$this->env])) {
                $this->env = self::$DEFAULT_ENV;
            }
            $this->configArray = $cfg[$this->env];
            
            $this->replaceAllConsts($this->configArray);
        }
    }
    
    /**
     * Magic function to get config values. Returns the value of the
     * configuration value from the currently active profile.
     *
     * For example this will return the "example" configuration value:
     * <pre> $cfg = new api_config();
     * $val = $cfg->example;</pre>
     *
     * @param $name string: Configuration key to return.
     * @return mixed: Configuration value extracted from the config file.
     *                Returns null if the config key doesn't exist.
     */
    public function __get($name) {
        if (empty($name)) {
            return null;
        }
        
        if (isset($this->configArray[$name])) {
            return $this->configArray[$name];
        }
        
        return null;
    }
    
    /**
     * Checks availability of a cachefile and assigns the cached content
     * to the private object variable $configCache.
     */
    private function readCache() {
        $cachefile = $this->getConfigCachefile();
        
        if (file_exists($cachefile) && is_readable($cachefile)) {
            include $cachefile;
            if (isset($configCache) && is_array($configCache)) {
                $this->configArray = $configCache;
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Returns the filename of the configuration cache file to be used.
     */
    private function getConfigCachefile() {
        $project = API_PROJECT_DIR;
        $env = $this->env;
        
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0700, true);
        }
        $fname = $env . '-' . md5($project) . '-cache.php';
        $cachefile = $this->cacheDir . $fname;
        return $cachefile;
    }
    
    /**
     * Replaces all constants in the configuration file. Uses the
     * method replaceConst for the actual replacement. Calls itself
     * recursively.
     *
     * @param $arr array: Configuration array.
     */
    private function replaceAllConsts(&$arr) {
        if (!is_array($arr)) {
            return;
        }
        
        foreach ($arr as $key => &$value) {
            if (is_array($value)) {
                $this->replaceAllConsts($value);
            } else {
                $arr[$key] = $this->replaceConst($value);
            }
        }
    }
    
    /**
     * Replace constants in a value. Constants can be used in the
     * configuration with the {CONSTANT} syntax. For each such
     * occurrence in the value of the constant is substituted if
     * such a constant exists.
     */
    private function replaceConst($value) {
        if (!empty($value)) {
            preg_match_all("#\{.[^\}]+\}#", $value, $matches);
            if (isset($matches[0]) && count($matches[0]) > 0) {
                foreach($matches[0] as $repl) {
                    $constName = substr($repl,1, -1);
                    if (defined($constName)) {
                        $constVal = constant($constName);
                        $value = str_replace($repl, $constVal, $value);
                    }
                }  
            }
        }
        
        return $value;   
    }
}