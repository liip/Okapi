<?php
/**
* Wrapper for the Zend_Db-Driver
*
* Sample YAML-Configuration:
* 'modeldir' is optional; it can be used to specify a directory containing zend_db table definitions.
* 'charset' is optional; it defaults to utf8 if not specified.
*
* /code
*   db:
*       #  GRANT ALL PRIVILEGES ON yourdbname.* TO yourwriteuser@yourdbserver IDENTIFIED BY "yourwriteuserspassword";
*       default:
*           driver: zend
*           adapter: Pdo_Mysql
*           dsn:
*               host: yourdbserver
*               dbname: yourdbname
*               username: yourwriteuser
*               password: yourwriteuserspassword
*           modeldir: localinc/api/model/tables
*           charset: utf8
*       # GRANT SELECT ON yourdbname.* TO yourreaduser@yourdbserver IDENTIFIED BY "yourreaduserspassword";
*       read:
*           driver: zend
*           adapter: Pdo_Mysql
*           dsn:
*               host: yourdbserver
*               dbname: yourdbname
*               username: yourreaduser
*               password: yourreaduserspassword
*
*  /endcode
*
* @see http://framework.zend.com/manual/en/zend.db.html
*
*/
class api_db_zend implements api_Idb {

    /**
     * Constructor
     */
    public function __construct() {
        if (! class_exists('Zend_Db') ) {
            throw new api_exception ( api_exception::THROW_NONE, null, null, 'Zend_Db not around' );
        }
    }

    /**
     * Open a database connection based on config settings.
     */
    public function getDBConnection($cfg) {
        if (! $cfg) {
            throw new api_exception_db(api_exception::THROW_FATAL, null, null, "Cannot find configuration settings");
        }


        try {
            if(!isset($cfg['dsn']['charset'])) {
                $cfg['dsn']['charset'] = isset($cfg['charset']) ? $cfg['charset'] : 'utf8';
            }
            $db = Zend_Db::factory($cfg['adapter'], $cfg['dsn']);
            Zend_Db_Table_Abstract::setDefaultAdapter($db);

            // backwards compatibility for charset support for old Zend Framework versions (also for PHP 5.3 http://framework.zend.com/issues/browse/ZF-7428)
            if ($db instanceof Zend_Db_Adapter_Pdo_Mysql) {
                $db->query('SET NAMES ' . $cfg['dsn']['charset']);
            }

            $log = api_log::getInstance();
            if ($log->isLogging()
                && class_exists('api_db_pdo_logWrapper', true)
            ) {
                $db = new api_db_pdo_logWrapper($db, $log);
            }
       } catch (Zend_Db_Adapter_Exception $e) {
           // perhaps a failed login credential, or perhaps the RDBMS is not running
           throw new api_exception_Backend(1, array(), 1, $e->getMessage());

       } catch (Zend_Exception $e) {
           // perhaps factory() failed to load the specified Adapter class
           throw new api_exception_Backend(1, array(), 1, $e->getMessage());
       }
       return $db;
    }

}
