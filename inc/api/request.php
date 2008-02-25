<?php
require('api/params.php');
/**
 * Parses the request parameters and stored them in a
 * easily accessible form which can be passed around to
 * commands.
 */
class api_request {
    protected $host = '';
    protected $lookupHost = '';
    protected $sld = '';
    protected $tld = '';
    protected $path = '';
    protected $url = '';
    protected $verb = '';       // HTTP verb of the current request
    protected $lang = '';
    protected $params = null;
    protected $filename = '';
    protected $extension = false;
    
    /**
     * Gets an instance of api_request.
     *
     * @param $forceReload bool: If true, forces instantiation of a new instance.
     */
    public static function getInstance($forceReload = false) {
        static $instance;
        
        if  ($forceReload || !isset($instance) || !($instance instanceof api_request)) {
            $instance = new api_request;
        }
        
        return $instance;
    }
    
    /**
     * Constructor. Parses the request and fills in all the
     * values it can.
     */
    protected function __construct() {
        $this->host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        $this->lookupHost = $this->host;
        
        $config = api_config::getInstance();
        $this->outputLangs = $config->lang['languages'];
        $this->defaultLang = $config->lang['default'];
        if (is_null($this->outputLangs)) {
            $this->outputLangs = array('en');
        }
        if (is_null($this->defaultLang)) {
            $this->defaultLang = 'en';
        }
        
        // Parse host, get SLD / TLD
        $hostinfo = api_init::getHostConfig($this->host);
        if ($hostinfo) {
            $this->sld = $hostinfo['sld'];
            $this->tld = $hostinfo['tld'];
            $this->lookupHost = $hostinfo['host'];
        }
        
        $path = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        if (strpos($path, '?') !== FALSE) {
            $path = substr($path, 0, strpos($path, '?'));
        }
        
        // Get language from the beginning of the URL
        $lang = $this->getLanguageFromPath($path);
        if ($lang !== null) {
            $this->lang = $lang['lang'];
            $path = $lang['path'];
        }
        
        // Strip out path prefix from path
        if (isset($hostinfo['path'])) {
            if (strpos($path, $hostinfo['path']) === 0) {
                $path = substr($path, strlen($hostinfo['path']));
            }
            if (substr($path, 0, 1) !== '/') {
                $path = '/' . $path;
            }
        }

        // HTTP verb - assume GET as default
        $this->verb = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
        
        $this->params = new api_params();

        $this->params->setGet($_GET);
        
        // Get / Post parameters
        if ($this->verb == 'POST') {
            $this->params->setPost($_POST);
        }

        if ($this->lang === '') {
            $lang = $this->parseLanguage($path);
            $this->lang = $lang['lang'];
            $path = $lang['path'];
        }
        $this->url = API_HOST . $this->lang . API_MOUNTPATH . substr($path, 1);
        $this->path = $path;
        
        // Path
        $this->filename = $this->parseFilename($this->path);
        
        $matches = array();
        if ($this->filename != '') {
            preg_match("#\.([a-z]{3,4})$#", $this->filename, $matches);
            if (isset($matches[1]) && !empty($matches[1])) {
                $this->extension = $matches[1];
            }
        }
    }
    
    /**
     * Returns the hostname of the current request.
     */
    public function getHost() {
        return $this->host;
    }
    
    /**
     * Returns the hostname to be used for route lookups. This
     * is either the host attribute of the matching host in the config
     * or it's sld or name attribute. (in this order of priority)
     */
    public function getLookupHost() {
        return $this->lookupHost;
    }

    /**
     * Returns the subdomain of the current request's hostname.
     */
    public function getSld() {
        return $this->sld;
    }

    /**
     * Returns the top domain of the current request's hostname.
     */
    public function getTld() {
        return $this->tld;
    }
    
    /**
     * Returns the path of the current request.
     */
    public function getPath() {
        return preg_replace('#/{2,}#','/',$this->path);
    }
    
    /**
     * Returns the full URL of the current request. (not
     * including query parameters)
     */
    public function getUrl() {
        return $this->url;
    }
    
    /**
     * Returns the verb / request method of the current request.
     * The verb is always upper case.
     */
    public function getVerb() {
        return $this->verb;
    }
    
    /**
     * Returns the detected language of the current request.
     */
    public function getLang() {
        return $this->lang;
    }
    
    /**
     * Returns a list of all configured languages.
     */
    public function getLanguages() {
        return $this->outputLangs;
    }
    
    /**
     * Returns the configured default language.
     */
    public function getDefaultLanguage() {
        return $this->defaultLang;
    }
    
    /**
     * Returns the file name of the current request.
     */
    public function getFilename() {
        return $this->filename;
    }
    
    /**
     * Returns the request parameters.
     */
    public function getParameters() {
        return $this->params;
    }
    
    /**
     * Returns a single request parameter.
     * You can pass in a default value which is returned in case the
     * param does not exist. Null is returned by default.
     */
    public function getParam($param, $default = null) {
        if (isset($this->params[$param])) {
            return $this->params[$param];
        } else {
            return $default;
        }
    }
    
    /**
     * Returns the extension
     */
    public function getExtension() {
        return $this->extension;
    }
    /**
     * Parses out a file name from the current path.
     * The last path component is returned if it contains an extension
     * of at least one character.
     */
    private function parseFilename($path) {
        preg_match("#[\s\w\xc0-\xff\-\_\%2F\+]*\.[a-z0-9]{1,}$#i", $path, $matches);
        if (isset($matches[0])) {
            return api_helpers_string::ensureUtf8(urldecode($matches[0]));
        }
        
        return '';
    }
    
    /**
     * Gets the language from the given path.
     * On finding a language, an associative array is returned
     * containing the new path and the language.
     * If no language is found, null is returned.
     */
    private function getLanguageFromPath($path) {
        // Path
        preg_match("#^\/([a-z]{2})\/#", $path, $matches);
        if (isset($matches[1]) && in_array($matches[1], $this->outputLangs)) {
            $lang = $matches[1];
            $newpath = substr($path, 3);
            return array('path' => $newpath,
                         'lang' => $lang);
        }
        
        return null;
    }
    
    /**
     * Gets a language from the current request. The following
     * positions are checked for a language:
     *   - Path (beginning of path).
     *   - HTTP Accept headers.
     *   - Default.
     */
    private function parseLanguage($path) {
        $newpath = $path;
        
        if ($retval = $this->getLanguageFromPath($path)) {
            return $retval;
        }

        // lang is in ACCEPT_LANGUAGE
        $config = api_config::getInstance();
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && $config->acceptLanguage !== 'false') {
            $accls = explode(",", $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            if (is_array($accls)) {
                foreach($accls as $accl) {
                    // Does not respect coefficient
                    $l = substr($accl, 0, 2);
                    if (in_array($l, $this->outputLangs)) {
                        return array('path' => $newpath,
                                     'lang' => $l);
                    }
                }
            }
        }

        return array('path' => $newpath,
                     'lang' => $this->defaultLang);
    }
}
