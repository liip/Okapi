<?php
/**
* Wrapper for the Mock-DB
*
* Sample YAML-Configuration:
*
* /code
*   db: 
*       default: 
*           driver: mock
*  /endcode
* 
*/
class api_db_mock implements api_Idb {

    /**
     * Constructor
     */
    public function __construct() {

    }
    
    /**
     * Open a database connection based on config settings.
     */
    public function getDBConnection($cfg) {
        return $this;
    }

    public function quote($str){
        return "'".$str."'";
    }
    
    public function query(){
        return null;
    }
    

}
