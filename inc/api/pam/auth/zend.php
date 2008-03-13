<?php
require_once('Zend/Auth.php');

/**
 * Implements a PAM authentication class using the Zend Auth
 * classes.
 * @see http://framework.zend.com/manual/zend.auth.html
 */
class api_pam_auth_zend extends api_pam_common  implements api_pam_Iauth {
    /** Zend_Auth instance which is proxied through this class. */
    protected static $zaAuth = null;
    
    /** Zend_Auth_Adapter: Active adapter which handles the user lookups. */
    private static $zaAdapter;
    
    /**
     * Constructor.
     */
    public function __construct($opts) {
        parent::__construct($opts);
        self::$zaAuth = Zend_Auth::getInstance();
        self::$zaAuth->setStorage(new Zend_Auth_Storage_Session('Okapi_Auth'));
    }
    
    /**
     * Check if the user is currently logged in.
     * @return boolean true if authenticated successfuly
     * @see Zend_Auth_Result
     */
    public  function checkAuth() {
        if (self::$zaAuth->hasIdentity()) {
            return true;
        } else if (isset(self::$zaAdapter)) {
            $zaResult = self::$zaAuth->authenticate(self::$zaAdapter);
            $msg = $zaResult->getMessages();
            if($zaResult->getCode() !== 1){
                throw new api_exception_Auth(api_exception::THROW_FATAL, array(), 0, $msg[1]);
            }
            return $zaResult->isValid();
        }
        return false;
    }

    /**
     * Returns the identity from storage or null if no identity is available
     *
     * @return mixed|null
     */
    public  function getAuthData() {
        return self::$zaAuth->getIdentity();
    }

    /**
     * Returns the identity from storage or null if no identity is available
     *
     * @return mixed|null
     * @todo is supposed to only return the ID, instead of the whole identity
     */
    public  function getUserId() {
        return self::$zaAuth->getIdentity();
    }

    /**
     * Returns the identity from storage or null if no identity is available
     *
     * @return mixed|null
     * @todo is supposed to only return the Username instead of the whole identidy
     */
    public  function getUserName() {
        return self::$zaAuth->getIdentity();
        
    }

    /**
     * Trigger the Login-Process
     *
     * @param $user string: Username
     * @param $pass string: Password
     * @return boolean true if authenticated successfuly
     * @todo clean up the "very ugly stuff"
     */
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
                return false;
        }
        return $this->checkAuth();
    }
    
    /**
     * Clears the identity from persistent storage
     *
     * @return void
     */
    public  function logout() {
        self::$zaAuth->clearIdentity();
    }
}