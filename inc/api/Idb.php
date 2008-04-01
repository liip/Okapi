<?php
/**
 * Interface for database drivers.
 */
interface api_Idb {
    /**
     * Return a valid connection to the database.
     * @param $config array: Configuration for the connection to load.
     */
    public function getDBConnection($config);
}
