<?php
/**
 * Default DB Connection Factory for MDB2 Connections
 */
class api_db_mdb2 implements api_Idb {
    
    /**
     * We may switch to static classes since they are faster and provide all we 
     * think we need
     *
     */
    public function __construct() {
        require_once("MDB2.php");
    }
    
   /**
     * Open a database connection based on config settings.
     */
    public function getDBConnection($cfg) {
        if (! $cfg) {
            throw new api_exception_Db(api_exception::THROW_FATAL, null, null, "Cannot find configuration settings");
        }
             
        $dboptions = (isset($cfg['dboptions']) && isset($cfg['dboptions']) ? $cfg['dboptions'] : NULL);
        
        $dsninfo = MDB2::parseDSN($cfg['dsn']);
        $dsninfo['new_link'] = true; 
        $db = MDB2::connect($dsninfo, $dboptions);
        
        if (PEAR::isError($db)) {
            throw new api_exception_Db(api_exception::THROW_FATAL, null, null, "Could not open database connection $name: " . $db->getMessage());
        }
        
        if (isset($cfg['portability'])) {
            $db->options['portability'] = $cfg['portability'];
        }
        
        if (isset($cfg['charset']) && !empty($cfg['charset'])) {
            $db->exec("SET NAMES '" . $cfg['charset'] . "'");
        } else {
            $db->exec("set names 'utf8'");
        }
 
        return $db;
    }
}