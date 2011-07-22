<?php
/**
 * @package db_pdo
 *
 * Okapi DB driver for connecting to databases using the PDO library.
 * The intended PDO version is 1.0
 * 
 * @config <b>db-><em>conn</em>->dsn</b> (string): Database source name.
 *         This is the connection string for the database. Use the PDO
 *         DSN format. \n
 *         The "conn" part is the connection name and can be
 *         changed to whatever name you desire.
 * @see http://www.php.net/pdo PDO manual
 * @see http://pecl.php.net/package/PDO PDO package
 */

return array(
    'name'        => 'db-pdo',
    'author'      => 'Marc Ammann',
    'maintainer'  => Array('Marc Ammann'),
    'version'     => '1.0.0',
    'okapi'       => '1.0.*',
    'pdo'         => '1.0',
    'description' => 'Okapi DB driver for connecting to databases using PDO.',
);