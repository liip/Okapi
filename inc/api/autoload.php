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
 * Autoloader. When a class is instantiated the autoloader replaces each
 * underline in a classname with a slash and loads the corresponding file
 * from the file system.
 * @param $class string: Name of class to load.
 */
class autoload {
    static $class_file_map;

    public static function load($class) {
        $incFile = str_replace("_", DIRECTORY_SEPARATOR, $class).".php";
        /*
        * Well, we could prevent a fatal error with checking if the file exists..
        * This would result in a nice fatal error exception page.. do we want this?
        */

        /*  TODO: Look into that:
        <lsmith> chregu: jo .. Wez meinte das kann zu race conditions, locking problemen etc fuehren
        <lsmith> ZF hat das frueher auch so gemacht
        <lsmith> ich glaube jetzt iterieren sie ueber den include path
        */

        if (@fopen($incFile, "r", true)) {
            include($incFile);

            return $incFile;
        }

        if (is_null(self::$class_file_map)) {
            $class_file_map = API_TEMP_DIR.'class_file_map.php';
            if (!file_exists($class_file_map)) {
                autoload::generateClassFileMap($class_file_map, API_LIBS_DIR.'vendor');
            }
            $return = include $class_file_map;
            self::$class_file_map = $return ? $mapping : false;
        }

        if (self::$class_file_map && isset(self::$class_file_map[$class])) {
            $incFile = self::$class_file_map[$class];
            include($incFile);
        }

        return false;
    }

    public static function generateClassFileMap($class_file_map, $dir) {
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::SELF_FIRST);
        foreach($objects as $file => $object) {
            if (substr($file, -4) !== '.php') {
                continue;
            }

            $file = str_replace(DIRECTORY_SEPARATOR, '/', $file);
            $class = basename($file, '.php');

            $content = file_get_contents($file);
            if (stripos($content, 'class '.$class) !== false
                || stripos($content, 'interface '.$class) !== false
            ) {
                $mapping[$class] = $file;
            }
        }
        $mappingstring = empty($mapping)
            ? '<?php return false;'
            : '<?php $mapping = '. var_export($mapping, true).'; return true;';
        file_put_contents($class_file_map, $mappingstring);
    }
}
