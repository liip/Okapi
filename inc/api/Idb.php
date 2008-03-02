<?php
/**
 * Interface for database drivers.
 */
interface api_Idb {
    /**
     * Return a valid connection to the database.
     */
    public function getDBConnection($cfg);
}
