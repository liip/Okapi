<?php
/* Licensed under the Apache License, Version 2.0
 * See the LICENSE and NOTICE file for further information
 */

/**
 * Initializes the Okapi environment by setting up global constants
 * and include paths.
 *
 *
 */
class api_init {
    /** bool: True if Okapi has been initialized already. Used so that
      * api_init::start() can be called repeatedly without problems. */
    private static $initialized = false;

    /**
     * @var array bootstrap config array
     */
    private static $cfg;

    /**
     * Sets up the Okapi environment.
     *
     * Defines a whole bunch of constants and sets the include path.
     * The include path contains the following components in this order:
     *    -# API_LOCAL_INCLUDE_DIR - API_LOCAL_VENDOR_DIR
     *    -# All directories in the "ext/" directory. If there are two
     *       directories inside that directory, both are added individually
     *       to the include path.
     *    -# All lib dirs in the exts
     *    -# API_INCLUDE_DIR - API_VENDOR_DIR
     *    -# existing include_path
     *
     * @define API_PROJECT_DIR
     *         Root directory of the project. The Okapi is expected to be in
     *         the inc/api directory of your project, so the project
     *         directory is assumed to be two levels up of the Okapi directory.
     * @define API_INCLUDE_DIR
     *         Include path where additional libraries are expected to be.
     *         Set to the "inc" directory inside API_PROJECT_DIR.
     * @define API_LIBS_DIR
     *         Okapi directory path.
     * @define API_LOCAL_INCLUDE_DIR
     *         Include path where the project's PHP code files are located.
     *         Points to the "localinc" directory inside API_PROJECT_DIR.
     * @define API_LOCAL_VENDOR_DIR
     *         Include path of the lib directory under localinc
     * @define API_VENDOR_DIR
     *         Include path of the lib directory under inc
     * @define API_THEMES_DIR
     *         Include path where the XSLT themes are located. Points to the
     *         "themes" directory inside API_PROJECT_DIR.
     * @define DEVEL
     *         True (1) if Okapi is in DEVEL mode. This may in the future
     *         define some special behaviours. For now this is unused.
     *         Defaults to 0. Set to 1 before calling api_init::start()
     *         if you want to use this.
     * @define API_HOST
     *         Absolute URL to the HTTP root of the current host.
     * @define API_WEBROOT
     *         Absolute URL to the root of the current application. This is
     *         equal to API_HOST with the mount path appended.
     * @define API_MOUNTPATH
     *         Mount path of the current application. This is equal to the
     *         "path" configuration value of the current host.
     * @define API_WEBROOT_STATIC
     *         Absolute URL to the root of the static files. Defaults to the
     *         "static/" directory inside API_WEBROOT but can be configured
     *         using <b>webpaths['static']</b>.
     * @define API_TEMP_DIR
     *         Directory for temporary files on the file system. Always "tmp/"
     *
     * @config <b>webpaths['static']</b> (string): Absolute or relative URI to
     *         be used as API_WEBROOT_STATIC value. Used if the static files
     *         are located on another host than the applications.
     */
    public static function start() {
        if (self::$initialized) {
            return self::$cfg;
        }
        if (!defined('DEVEL')) {
            define('DEVEL',0);
        }

        define('API_NAMESPACE', "api");
        if (!defined('API_PROJECT_DIR')) {
            define('API_PROJECT_DIR', realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
        }
        define('API_INCLUDE_DIR', API_PROJECT_DIR."inc".DIRECTORY_SEPARATOR);
        define('API_LIBS_DIR', API_INCLUDE_DIR."api".DIRECTORY_SEPARATOR);
        define('API_LOCAL_INCLUDE_DIR', API_PROJECT_DIR.'localinc'.DIRECTORY_SEPARATOR);
        define('API_THEMES_DIR', API_PROJECT_DIR.'themes'.DIRECTORY_SEPARATOR);
        define('API_VENDOR_DIR', API_INCLUDE_DIR.'lib'.DIRECTORY_SEPARATOR);
        define('API_LOCAL_VENDOR_DIR', API_LOCAL_INCLUDE_DIR.'lib'.DIRECTORY_SEPARATOR);

        // set PHP include path (localinc - localinc/lib - inc - inc/lib - include_path)
        $incPath = API_INCLUDE_DIR;
        if (file_exists(API_VENDOR_DIR)){
            $incPath .= PATH_SEPARATOR . API_VENDOR_DIR;
        }
        $incPath .= PATH_SEPARATOR.get_include_path();

        if (file_exists(API_LOCAL_VENDOR_DIR)) {
            $incPath = API_LOCAL_VENDOR_DIR . PATH_SEPARATOR . $incPath;
        }
        $incPath = API_LOCAL_INCLUDE_DIR . PATH_SEPARATOR . $incPath;

        set_include_path($incPath);

        // Create temporary directory
        define('API_TEMP_DIR', API_PROJECT_DIR.'tmp/');
        if (!is_dir(API_TEMP_DIR)) {
            mkdir(API_TEMP_DIR, 0777, true);
        }

        // Load and read config
        if (!isset($_SERVER['OKAPI_ENV'])) {
            $_SERVER['OKAPI_ENV'] = 'default';
        }

        $cachefile = self::getCacheFilename('bootstrap', $_SERVER['OKAPI_ENV']);
        if (file_exists($cachefile)) {
            $cfg = unserialize(file_get_contents($cachefile));
            if ($cfg['configCache'] === 'auto'
                && $cfg['cachetime'] < filemtime(API_PROJECT_DIR . 'conf/bootstrap.yml')
            ) {
                unset($cfg);
            }
        }
        if (empty($cfg)) {
            require_once API_LIBS_DIR.'/vendor/symfony/sfYaml/sfYaml.php';
            $cfg = sfYaml::load(API_PROJECT_DIR . 'conf/bootstrap.yml');
            $cfg = isset($cfg[$_SERVER['OKAPI_ENV']]) ? $cfg[$_SERVER['OKAPI_ENV']] : $cfg['default'];
            if (!empty($cfg['configCache'])) {
                $cfg['cachetime'] = $_SERVER['REQUEST_TIME'];
                file_put_contents($cachefile, serialize($cfg));
            }
        }

        // Load autoloader
        if (empty($cfg['autoload'])) {
            $autoload = API_LIBS_DIR."autoload.php";
            require_once $autoload;
            autoload::$cache = empty($cfg['configCache']) ? false : $cfg['configCache'];
            if (isset($cfg['dirs'])) {
                autoload::initDirs();
                autoload::setCustomDirs($dirs);
            }
            spl_autoload_register(array('autoload', 'load'));
        } else {
            require_once API_LOCAL_INCLUDE_DIR.$cfg['autoload'];
        }

        // Construct URL for Web home (root of current host)
        $hostname = (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
        $schema = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ? 'https' : 'http';
        define('API_HOST', $schema.'://' . $hostname);
        // FIXME: this would be needed to support okapi installs inside a subdir of a domain
        define('API_MOUNTPATH', '/');

        $reqHostPath = '';
        if ($hostname != '') {
            $reqHostPath = API_HOST;
            if (isset($_SERVER['SCRIPT_NAME']) && $_SERVER['SCRIPT_NAME'] != '/index.php') {
                $reqHostPath .= substr($_SERVER['SCRIPT_NAME'],0,-9);
            }
        }
        define('API_WEBROOT', $reqHostPath.'/');

        // Define webrootStatic constant. From config file or computed
        // from webroot.
        if (!empty($cfg['static_path'])) {
            if (strpos($cfg['static_path'], 'http://') === 0 || strpos($cfg['static_path'], '/') === 0) {
                // Complete URI or Absolute URL
                define('API_WEBROOT_STATIC', $cfg['static_path']);
            } else {
                // Relative URL
                define('API_WEBROOT_STATIC', API_WEBROOT . $cfg['static_path']);
            }
        } else {
            define('API_WEBROOT_STATIC', API_WEBROOT.'static/');
        }

        // Enable libxml internal errors
        libxml_use_internal_errors(true);

        self::$initialized = true;
        self::$cfg = $cfg;

        return $cfg;
    }

    public static function createServiceContainer($cfg = null) {
        if (is_null($cfg)) {
            $cfg = self::start();
        }

        // Create ServiceContainer
        if (isset($cfg['serviceContainer'])) {
            $api_container_file = empty($cfg['configCache'])
                ? false
                : self::getCacheFilename('servicecontainer', $_SERVER['OKAPI_ENV']);

            if (!$api_container_file || !file_exists($api_container_file)) {
                $sc = new sfServiceContainerBuilder();
                $loader = $cfg['serviceContainer']['loader'];
                $loader = new $loader($sc);
                $file = isset($cfg['serviceContainer']['file'])
                    ? $cfg['serviceContainer']['file'] : $_SERVER['OKAPI_ENV'];
                $file.= $cfg['serviceContainer']['extension'];
                $loader->load(API_PROJECT_DIR.'conf/servicecontainer/'.$file);

                if ($api_container_file) {
                    $dumper = new sfServiceContainerDumperPhp($sc);
                    $dumper_cfg = array(
                        'class' => $cfg['serviceContainer']['class']
                    );
                    $code = $dumper->dump($dumper_cfg);
                    file_put_contents($api_container_file, $code);
                }

                return $sc;
            }

            $serviceContainerClass = $cfg['serviceContainer']['class'];
            require_once $api_container_file;
        } else {
            $serviceContainerClass = 'api_servicecontainer';
        }

        $sc = new $serviceContainerClass();
        return $sc;
    }

    /**
     * Returns the filename of the configuration cache file to be used.
     */
    public static function getCacheFilename($name, $env) {
        if (!is_writable(API_TEMP_DIR)) {
            return null;
        }

        $file = API_TEMP_DIR . $name. '-cache_' . $env;

        return $file . '.php';
    }
}
