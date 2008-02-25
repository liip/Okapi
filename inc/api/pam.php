<?php
/**
* Okapi Permission and Authentication Modules base
*
* @author   Silvan Zurbruegg
*
*/
class api_pam {

    private static $instance = null;

    private $clsNameBase = 'api_pam';
    private $permPrefx = 'perm';
    private $authPrefx = 'auth';

    private $authObj = array();
    private $permObj = array();

    private $authConf = array();
    private $permConf = array();

    private $confDefaultName = 'default';

    private $authSchemeDefault = "default";
    private $permSchemeDefault = "default";

    private $authScheme = '';
    private $permScheme = '';


    private function __construct() {

        $pamConf = api_config::getInstance()->pam;
        $this->pamLoadConfig($pamConf);

    }


    public function getInstance() {

        if (!self::$instance instanceof api_pam) {
            self::$instance = new api_pam();
        }

        return self::$instance;

    }


    public function login($user, $pass) {

        if (($ao = $this->getAuthObj()) !== false) {
            return $ao->login($user, $pass);
        }

        return false;
    }


    public function logout() {

        if (($ao = $this->getAuthObj()) !== false) {
            return $ao->logout();
        }

        return false;
    }


    public function checkAuth() {
        if (($ao = $this->getAuthObj()) !== false) {
            return $ao->checkAuth();
        }

        return false;
    }


    public function getUserId() {
        if (($ao = $this->getAuthObj()) !== false) {
            return $ao->getUserId();
        }

        return 0;
    }


    public function getUserName() {
        if (($ao = $this->getAuthObj()) !== false) {
            return $ao->getUserName();
        }

        return "";
    }


    public function getAuthData() {
        if (($ao = $this->getAuthObj()) !== false) {
            return $ao->getAuthData();
        }

        return "";
    }


    public function isAllowed($acObject, $acValue) {

        if (($po = $this->getPermObj()) !== false) {
            $uid = $this->getUserId();
            return $po->isAllowed($uid, $acObject, $acValue);

        }

        return 0;

    }


    public function setAuthScheme($schemeName) {
        if (isset($this->authConf[$schemeName]) || $schemeName == $this->authSchemeDefault) {
            $this->authScheme = $schemeName;
            return true;
        }

        return false;
    }


    public function getAuthScheme() {
        return (empty($this->authScheme)) ? $this->authSchemeDefault : $this->authScheme;
    }


    public function setPermScheme($schemeName) {
        if (isset($this->permConf[$schemeName]) || $schemeName == $this->permConfDefault) {
            $this->permScheme = $schemeName;
            return true;
        }

        return false;
    }


    public function getPermScheme() {
        return (empty($this->permScheme)) ? $this->permSchemeDefault : $this->permScheme;
    }


    public function redirect() {

        $cfg = $this->pamGetAuthConf();

        if (isset($cfg['login']['@redirect']) && $cfg['login']['@redirect'] == 'true') {

            $login      =  $cfg['login'];
            $authloc    = (isset($cfg['auth'])) ? $cfg['auth'] : $login;

            if ($login !== $_SERVER['REQUEST_URI'] && $authloc !== $_SERVER['REQUEST_URI']) {

                header(sprintf("Location: %s", $login));
                exit(0);
            }
        }
    }


    private function getPermObj($scheme=null) {
        $scheme = (empty($scheme)) ? $this->getPermScheme() : $scheme;
        return $this->pamGetObject($this->permPrefx, $scheme);
    }


    private function getAuthObj($scheme=null) {

        $scheme = (empty($scheme)) ? $this->getAuthScheme() : $scheme;
        return $this->pamGetObject($this->authPrefx, $scheme);

    }


    private function pamGetAuthConf($scheme=null) {

        $scheme = (empty($scheme)) ? $this->getPermScheme() : $scheme;
        if (isset($this->authConf[$scheme])) {
            return $this->authConf[$scheme];
        }

        return array();
    }


    private function pamGetObject($prefx, $scheme) {
        $objArr = $prefx."Obj";
        $cfgArr = $prefx."Conf";

        $instBase = $this->clsNameBase."_".$prefx;
        
        if (isset($this->{$objArr}[$scheme]) && ($this->{$objArr}[$scheme] instanceof $instBase)) {

            return $this->{$objArr}[$scheme];

        } else if (isset($this->{$cfgArr}[$scheme])) {
            return $this->pamLoadObject($prefx, $this->{$cfgArr}[$scheme]);

        }

        return false;

    }


    private function pamLoadObject($prefx, $cfg) {
        $className = $this->clsNameBase . "_" . $prefx . "_" . $cfg['class'];
        if (class_exists($className)) {
            $opts = isset($cfg['options']) ? $cfg['options'] : array();
            $obj = new $className($opts);
            if ($obj instanceof $className) {

                return $obj;

            }
        }

        return false;

    }


    private function pamLoadConfig($pamConf) {

        if (isset($pamConf['auth'])) {
            $this->authConf = $this->pamLoadComponentConfig($pamConf['auth']);
        }

        if (isset($pamConf['perm'])) {
            $this->permConf = $this->pamLoadComponentConfig($pamConf['perm']);
        }

    }


    private function pamLoadComponentConfig($compArr) {
        $cfg = array();
        if (isset($compArr[0]) && is_array($compArr[0])) {
            foreach($compArr as $conf) {
                $confName = (isset($conf['name'])) ? $conf['name'] : $this->confDefaultName;
                $cfg[$confName] = $conf;
            }
        } else {

            $confName = (isset($compArr['name'])) ? $compArr['name'] : $this->confDefaultName;
            $cfg[$confName] = $compArr;
        }

        return $cfg;

    }

}
