<?php
/**
* Wrapper for the PDO-Driver
*
* Sample YAML-Configuration:
*
* /code
*   default:
*       driver: pdo
*       dsn: mysql:host=localhost;dbname=mydb
*       user: myproject_master
*       pass: 20myprO1ect08
*   write:
*       driver: pdo
*       dsn: mysql:host=localhost;dbname=modmon
*       user: myproject_master
*       pass: 20myprO1ect08
*   read:
*       driver: pdo
*       dsn: mysql:host=localhost;dbname=modmon
*       user: myproject_slave
*       pass: 20myprO1ect08
*       # GRANT SELECT ON modmon.* to modmon_read@localhost IDENTIFIED BY '10modmon07';
*  /endcode
*
* @see http://www.php.net/manual/ref.pdo.php
*
*/
class api_db_pdo implements api_Idb {

    /**
     * Constructor
     */
    public function __construct() {
        if (! extension_loaded ( 'pdo' )) {
            throw new api_exception(api_exception::THROW_NONE, null, null, 'PDO Extension not installed');
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
            if (!isset($cfg['pass'])) {
                $cfg['pass'] = '';
            }
            $db = new PDO ( $cfg ['dsn'], $cfg ['user'], $cfg ['pass'] );
        } catch ( PDOException $e ) {
            throw new api_exception_Backend(api_exception::THROW_FATAL, array(), 1, $e->getMessage());
        }

        $log = api_log::getInstance();
        if ($log->isLogging()) {
            $db = new api_db_pdo_logWrapper($db, $log);
        }

        return $db;
    }
}
