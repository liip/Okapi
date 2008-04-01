<?php
/**
 * Database factory. Creates database connections based on the
 * configuration settings.
 *
 * Example of a config.yml entry:
 *
 * \code
 * db:
 *    default:
 *        driver: mdb2
 *        dsn: blah@localhost/foo
 *    ordbm:
 *        driver: doctrine
 *        dsn: blubb@localhost/baz
 *        modeldir: localinc/api/model/data
 *    raw:
 *        driver: pdo
 *        dsn: mysql:localhost;dbname=cookie
 *        user: scooby
 *        pass: doo
 * \endcode
 *
 * @config <b>db</b> (hash): Contains named database connections. The
 *         keys are the names, each connection is a hash with the config
 *         values.
 * @config <b>db-><em>conn</em>->driver</b> (string): Specifies the driver
 *         to use. "api_db_" is prepended to the string to get a class
 *         name to load. The default driver is "mdb2". \n
 *         The "conn" part is the connection name and can be
 *         changed to whatever name you desire.
 * @config <b>db-><em>conn</em>->dsn</b> (string): Database source name.
 *         This is the connection string for the database. It's exact
 *         syntax depends on the driver. \n
 *         The "conn" part is the connection name and can be
 *         changed to whatever name you desire.
 */
class api_db {
    /** All instances loaded so far by api_db::factory(). */
    protected static $instances = array();
    
    /**
     * Returns the database connection specified by $name.
     * 
     * @param $name string: Database connection name
     * @return DatabaseConnection: Database connection or false if the
     *         database connection doesn't exist. The driver's
     *         getDBConnection() method is used to retrieve that connection.
     */
    public static function factory($name = "default") {
        static $instances;
        
        if (isset($instances[$name])) {
            return $instances[$name];
        }
        
        $db = api_config::getInstance()->db;
        if (empty($db[$name])) {
            return false;
        }
        
        $instances[$name] = self::get($db[$name]);
        return $instances[$name];
    }
    
    /**
     * Clear all loaded database connections. Useful for enforce new
     * connections in testing scenarios.
     */
    public static function reset() {
        self::$instances = array();
    }
    
    /**
     * Constructor. Private according to the singleton pattern.
     */
    private function __construct() {
    }
    
    /**
     * Returns a connection from the driver using it's getDBConnection()
     * method.
     *
     * @param $config array: Configuration for the connection to load.
     * @return DatabaseConnection: Database connection retrieved from
     *         the driver.
     */
    private static function get($config) {
        if (empty($config['dsn'])) {
            return false;
        }
        
        if (empty($config['driver'])) {
            $config['driver'] = "mdb2";
        }
        
        $driver = "api_db_".$config['driver'];
        $db = new $driver;
        return $db->getDBConnection($config);
    }
}
