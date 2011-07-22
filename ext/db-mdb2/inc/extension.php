<?php
/**
 * @package db_mdb2
 *
 * Okapi DB driver for connecting to databases using the MDB2 library.
 * 
 * @config <b>db-><em>conn</em>->dsn</b> (string): Database source name.
 *         This is the connection string for the database. Use the MDB2
 *         DSN format. \n
 *         The "conn" part is the connection name and can be
 *         changed to whatever name you desire.
 * @config <b>db-><em>conn</em>->dboptions</b> (hash): Options passed to
 *         MDB2 connect method. \n
 *         The "conn" part is the connection name and can be
 *         changed to whatever name you desire.
 * @see http://pear.php.net/package/MDB2 MDB2 package
 * @see http://pear.php.net/manual/en/package.database.mdb2.intro-dsn.php MDB2 DSN reference
 * @see http://pear.php.net/package/MDB2/docs/latest/MDB2/MDB2.html#methodconnect MDB2 connect method
 */

return array(
    'name'        => 'db-mdb2',
    'author'      => 'Marc Ammann',
    'maintainer'  => Array('Marc Ammann'),
    'version'     => '1.0.0',
    'okapi'       => '1.0.*',
    'description' => 'Okapi DB driver for connecting to databases using MDB2.',
);