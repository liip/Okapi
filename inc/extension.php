<?php
/**
 * @package db_doctrine
 *
 * Okapi DB driver for connecting to databases using the Doctrine library.
 * Intended installation of Doctrine is through PEAR or using it as an external
 * in vendor/Doctrine/ (NOT INCLUDED!)
 * This Driver is intended to work with Doctrine <= 0.10, but it may work with
 * further versions
 * 
 * @config <b>db-><em>conn</em>->dsn</b> (string): Database source name.
 *         This is the connection string for the database. Use the Doctrine
 *         DSN format. \n
 *         The "conn" part is the connection name and can be
 *         changed to whatever name you desire.
 * @config <b>db-><em>conn</em>->modeldir</b> (hash): This is the directory
 *          where your Doctrine_Record subclasses are. This will be added to
 *          the include-path so that they will be autoloaded (you need to name
 *          your classes accordingly)
 * @see http://www.phpdoctrine.org/ Doctrine package
 * @see http://www.phpdoctrine.org/documentation/manual/0_10?one-page#connection-management Doctrine connect method
 */

return array(
    'name'        => 'db-doctrine',
    'author'      => 'Marc Ammann',
    'maintainer'  => Array('Marc Ammann'),
    'version'     => '1.0.0',
    'okapi'       => '1.0.*',
    'doctrine'    => '0.10',
    'description' => 'Okapi DB driver for connecting to databases using Doctrine.',
);