<?php

class api_db_doctrine implements api_Idb {
    
    public function __construct() {
        require_once ("Doctrine/Doctrine.php");
        spl_autoload_register ( array ('Doctrine', 'autoload' ) );
    }
    
    /**
     * Open a database connection based on config settings.
     */
    public function getDBConnection($cfg) {
        if (!$cfg) {
            throw new api_exception_Db(api_exception::THROW_FATAL, null, null, "Cannot find configuration settings");
        }
        
        if (isset ( $cfg ['modeldir'] )) {
            $path = ini_get ( "include_path" );
            set_include_path ( API_PROJECT_DIR . $cfg ['modeldir'] . PATH_SEPARATOR . $path );
        }

        if (isset($cfg['name'])) {
            $name = $cfg['name'];
        } else {
            $name = null;
        }
        
        try {
            $manager = Doctrine_Manager::getInstance();
            $db = $manager->openConnection ($cfg['dsn'], $name);
            //$charset = isset($cfg['charset']) ? $cfg['charset'] : 'utf8';
            //$db->setCharset($charset);
        } catch ( Exception $e ) {
            throw new api_exception_Db ( api_exception::THROW_FATAL, null, null, $e->getMessage () );
        }
        
        return $db;
    }
}
