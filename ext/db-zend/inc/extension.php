<?php
/**
 * @package db_zend
 *
 * Okapi DB driver for connecting to databases using the Zend_Db library.
 * The intended PDO version is 1.0
 * 
 * @config <b>db-><em>conn</em>->dsn</b> (string): Database source name.
 *         This is the connection string for the database. Use the PDO
 *         DSN format. \n
 *         The "conn" part is the connection name and can be
 *         changed to whatever name you desire.
 *
 * @see http://framework.zend.com/manual/en/zend.db.html
 */
return array(
    'name'        => 'db-zend',
    'author'      => 'Alain Petignat',
    'maintainer'  => Array('Alain Petignat'),
    'version'     => '1.0.0',
    'okapi'       => '1.0.*',
    'pdo'         => '1.0',
    'description' => 'Okapi DB driver for connecting to databases using Zend_Db.',
);