<?php

class api_i18n_dispatcher {

    /** array: Instances as returned by the getInstance() method.
     * One instance is stored for each language. */
    private static $instances = array();
    protected  $retrieverName = null;

    public function __construct($config) {
        $this->config = $config;
    }

    /**
     * Return a new instance of this class for the given language.
     *
     * @param $lang string: Language into which the object will translate.
     */
    public function getInstance($lang) {
        if (!isset(self::$instances[$lang])) {

            self::$instances[$lang] = new api_i18n($lang, $this->config);
        }
        return self::$instances[$lang];
    }

    /**
     * Get the translation for a key for a certain language. This method
     * is intended for commands to retrieve single translations. To
     * transform documents the i18n() must should be used.
     *
     * @param $lang string: Language to translate into.
     * @param $key string: Language key to retrieve translation for.
     */
    public static function getMessage($lang, $key) {
        $i = self::getInstance($lang);
        return $i->i18nGetMessage($key);
    }
}

?>