<?php
/**
 * @package db_mysqli
 *
 * Okapi DB driver to connecting to a databases MySQL db using the 
 * MySQLi extension, its API is similar to ZendDb so the switch is 
 * instantaneous if you need more power.
 *
 * @config <b>db-><em>conn</em>->host</b> (string): Host name of the db server\n
 *         The "conn" part is the connection name and can be
 *         changed to whatever name you desire.
 * @config <b>db-><em>conn</em>->username</b> (string): Username\n
 *         The "conn" part is the connection name and can be
 *         changed to whatever name you desire.
 * @config <b>db-><em>conn</em>->password</b> (string): Password\n
 *         The "conn" part is the connection name and can be
 *         changed to whatever name you desire.
 * @config <b>db-><em>conn</em>->dbname</b> (string): Database name to connect to\n
 *         The "conn" part is the connection name and can be
 *         changed to whatever name you desire.
 */

return array(
    'name'        => 'db-mysqli',
    'author'      => 'Jordi Boggiano',
    'maintainer'  => Array('Jordi Boggiano'),
    'version'     => '1.0.0',
    'okapi'       => '1.0.*',
    'description' => 'Okapi DB driver to connecting to a databases MySQL db using the MySQLi extension, its API is similar to ZendDb so the switch is instantaneous if you need more power.',
);