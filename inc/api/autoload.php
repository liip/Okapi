<?php
/* Licensed under the Apache License, Version 2.0
 * See the LICENSE and NOTICE file for further information
 */

/**
 * @file autoload.php
 * Defines an autoloader function.
 * @see http://www.php.net/autoload
 */

/**
 * Autoloader.
 */
class autoload {
    static $cache = true;
    static $dirs = false;
    static $class_file_map;

    static public function initDirs() {
        self::$dirs = array(
            API_LIBS_DIR.'vendor/symfony' => 'symfony',
        );
        foreach (glob(API_PROJECT_DIR . 'ext/*') as $dir) {
            self::$dirs[$dir] = 'pear';
            if (is_dir($dir."/lib")) {
                self::$dirs[$dir."/lib"] = 'pear';
            }
        }
    }

    static public function setCustomDirs($dirs) {
        self::$dirs = array_merge(self::$dirs, $dirs);
    }

    /**
     * When a class is instantiated the autoloader replaces each
     * underline in a classname with a slash and loads the corresponding file
     * from the file system.
     * @param $class string: Name of class to load.
     */
    public static function load($class) {
        /*
        * Well, we could prevent a fatal error with checking if the file exists..
        * This would result in a nice fatal error exception page.. do we want this?
        */

        /*  TODO: Look into fopen use:
        <lsmith> chregu: jo .. Wez meinte das kann zu race conditions, locking problemen etc fuehren
        <lsmith> ZF hat das frueher auch so gemacht
        <lsmith> ich glaube jetzt iterieren sie ueber den include path
        */

        $incFile = str_replace("_", DIRECTORY_SEPARATOR, $class).".php";
        if (@fopen($incFile, "r", true)) {
            include($incFile);
            return $incFile;
        }

        // load class file map if not yet done
        if (is_null(self::$class_file_map)) {
            $class_file_map = self::getClassFileMapCacheName();
            if (!self::$cache || !file_exists($class_file_map)) {
                if (!self::$dirs) {
                    self::initDirs();
                }
                self::$class_file_map = autoload::generateClassFileMap($class_file_map);
            } else {
                $return = include $class_file_map;
                self::$class_file_map = ($return && $mapping) ? $mapping : false;
            }
        }

        // check class file map
        if (self::$class_file_map && isset(self::$class_file_map[$class])) {
            $incFile = self::$class_file_map[$class];
            include($incFile);
            return $incFile;
        }

        return false;
    }

    public static function getClassFileMapCacheName() {
        return api_init::getCacheFilename('autoload_class_file_map', $_SERVER['OKAPI_ENV']);
    }

    /**
     * Generates a file in the cache use for mapping class names to files.
     * @param $cache_file string: File name in which the class file map will be cached
     * @param $dir string: Name of the root path from which to search
     */
    public static function generateClassFileMap($cache_file) {
        $mapping = array();
        foreach (self::$dirs as $dir => $style) {
            // TODO: ignore .svn etc directories
            $objects = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach($objects as $file => $object) {
                if (substr($file, -4) !== '.php') {
                    continue;
                }

                $file = strtr($file, '\\', '/');
                if ($style === 'symfony') {
                    $class = basename($file, '.php');
                    $class = basename($class, '.class');
                } else {
                    $class = str_replace('.php', '', $file);
                    $class = str_replace(strtr($dir, '\\', '/').'/', '', $class);
                    $class = str_replace('/', '_', $class);
                }

                $content = file_get_contents($file);
                if (stripos($content, 'class '.$class) !== false
                    || stripos($content, 'interface '.$class) !== false
                ) {
                    $mapping[$class] = $file;
                }
            }
        }

        if (empty($mapping)) {
            $mapping = false;
        }

        if (self::$cache) {
            $mappingstring = '<?php $mapping = '. var_export($mapping, true).'; return true;';
            file_put_contents($cache_file, $mappingstring);
        }

        return $mapping;
    }
}
