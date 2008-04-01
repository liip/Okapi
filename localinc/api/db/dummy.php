<?php
/**
 * DB connection factory used in tests. Just returns itself.
 */
class api_db_dummy implements api_Idb {
   /**
     * Open a database connection based on config settings.
     */
    public function getDBConnection($cfg) {
        return $this;
   }
}
