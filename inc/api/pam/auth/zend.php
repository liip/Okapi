<?php

require_once('Zend/Auth.php');

class api_pam_auth_zend extends api_pam_common  implements api_pam_Iauth {

    /**
     * Enter description here...
     *
     * @var Zend_Auth
     */
    private static $zaAuth;

    /**
     * Enter description here...
     *
     * @var Zend_Auth_Adapter_Interface
     */
    private static $zaAdapter;

    public function __construct($opts) {
        parent::__construct($opts);

        self::$zaAuth = Zend_Auth::getInstance();
        self::$zaAuth->setStorage(new Zend_Auth_Storage_Session('Okapi_Auth'));
    }

    public  function checkAuth() {
        if (self::$zaAuth->hasIdentity()) {
            return TRUE;
        } else if (isset(self::$zaAdapter)) {

            $zaResult = self::$zaAuth->authenticate(self::$zaAdapter);
            
            $msg = $zaResult->getMessages();

            // @see Zend_Auth_Result
            if($zaResult->getCode() !== 1){
                throw new api_exception_Auth(api_exception::THROW_FATAL, array(), 0, $msg[1]);
            }

            return $zaResult->isValid();
        }

        return FALSE;
    }

    public  function getAuthData() {

    }

    public  function getUserId() {

    }


    public  function getUserName() {
        return self::$zaAuth->getIdentity();
    }

    public  function login($user, $pass) {
        self::$zaAuth->clearIdentity();
        $rgOpts = $this->opts['container'];

        $strAdapter = $rgOpts['driver'];

        switch($strAdapter) {
            case "ldap":
                // TODO: Omg this is very ugly
                foreach ($rgOpts as $host => $opts) {
                    if ($host != "driver") {
                        self::$zaAdapter = new Zend_Auth_Adapter_Ldap(Array($host => $opts), $user, $pass);
                    }
                }
                break;
            default:
                error_log("OKAPI: Zend_Auth_Adapter_".$strAdapter." not yet usable in Okapi");
                return FALSE;
        }

        return $this->checkAuth();

    }

    public  function logout() {
        self::$zaAuth->clearIdentity();
    }
}