<?php
/**
 * @package log
 *
 * Okapi wrapper for Zend_Log. Reads the "log" configuration and
 * instantiates the corresponding log objects.
 *
 * Use api_log::start(); to configure the log. This call is recommended
 * directly after the call to api_init::start().
 * 
 * @config <b>log</b> (array): Log configuration. Each array element must
 *          be a hash of configuration for a writer.
 * @config <b>log<em>[0]</em>->class</b> (string): Writer class to
 *          instantiate. "Zend_Log_" is prepended to that string.
 * @config <b>log<em>[0]</em>->cfg</b> (array): Constructor parameters
 *          to pass to the writer class.
 * @config <b>log<em>[0]</em>->priority</b> (string): Assigns a
 *          Zend_Log_Filter_Priority class with the given priority to the
 *          writer.
 *
 * You need to have the Zend framework classes Zend_Log and Zend_Exception
 * in your path.
 * 
 * @see http://framework.zend.com/manual/en/zend.log.html Zend_Log
 */

return array(
    'name'        => 'log',
    'author'      => 'Patrice Neff',
    'maintainer'  => array('Patrice Neff'),
    'version'     => '1.0.0',
    'okapi'       => '1.0.*',
    'description' => 'Okapi wrapper for Zend_Log.',
);
