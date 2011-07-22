<?php
/**
 * @license Licensed under the Apache License, Version 2.0. See the LICENSE and NOTICE file for further information
 * @copyright 2011 Liip AG
 */
class api_model_permission_handler implements api_model_permission_handler_interface {

    /**
     * Contains the central configuration array
     * @var array
     */
    protected $configuration = array();


    public function __construct() {
        $this->loadConfiguration();
    }

    /**
     * Decides if the requesting party is trusted.
     *
     * A trusted or registered member has a private secret and ist registered
     * with its requesting url as identifier.
     * To prove its right to access the member has to hash this two strings together and
     * provide it with each request in a field named 'hash'.
     *
     * @param api_request $request
     * @return boolean True, if trusted/registered, else false
     */
    public function isAllowed(api_request $request) {
        $hash = $request->getParam('hash');
        if (! isset($hash)) {
            return false;
        }

        // get info about the member
        $member = $this->getMember($request)->find($request->getHost());
        if ($member->verify($hash)) {
            return true;
        }

        return false;
    }

    /*************************************************************************/
    /* handle associatitions                                                 */
    /*************************************************************************/

    /**
     * Generates a new unique association handle.
     *
     * This has to be very unique!!
     *
     * @return string the 40 chars long key representing an association.
     */
    public function generateAssociationKey() {
        return sha1(mt_srand(microtime()));
    }

    /**
     * Loads information about the association asked for by handle.
     *
     * @param string $handle
     * @return array
     */
    public function getAssoc($handle) {
        $associations = array();
        $sql = "SELECT * from " . OPENID_DB_TABLE_ASSOCIATIONS . " WHERE handle = '$handle';";
        $associations = $this->getDatabaseReadHandle()->fetchAll($sql);
        $associations['data'] = unserialize($associations['data']);
        return $associations;
    }

    /**
     * Stores an association.
     *
     * @param String $handle Association handle -- should be used as a key.
     * @param Array $assoc Association data.
     * @throws OkapiOpenidAssociationBindingFailed
     */
    public function setAssoc($handle, $assoc) {
        $sql ="INSERT INTO " . OPENID_DB_TABLE_ASSOCIATIONS ." ('handle', 'data') VALUES ($handle, serialize($assoc))";

        try {
            $this->getDatabaseWriteHandle()->query($sql);
        } catch (Exception $e) {
            throw new OkapiOpenidAssociationBindingFailed($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Deletes an association.
     *
     * @param String $handle Association handle.
     * @throws OkapiOpenidAssociationDeletionFailed
     */
    public function delAssoc($handle) {
        $sql = 'DELETE FROM ' . OPENID_DB_TABLE_ASSOCIATIONS . ' WHERE handle = "'. $handle .'";';

        try {
            $this->getDatabaseWriteHandle()->query($sql);
        } catch (Exception $e) {
            throw new OkapiOpenidAssociationDeletionFailed($e->getMessage(), $e->getCode(), $e);
        }
    }


    /**
     * Load configuration from default.yml
     */
    protected function loadConfiguration() {
        $config = api_config::getInstance();
        $this->configuration = $config->openid;
    }

    /**
     * Provides an instance of the api_model_member_handler class.
     *
     * @param api_request $request Instance of the request object.
     * @return api_model_memberhandler
     */
    protected function getMember(api_request $request) {
        if (!isset($this->member)) {
            $this->member =
                new $this->configuration['provider']['member_handler']['classname']($this->getDatabaseReadHandle(), $request);
        }
        return $this->member;
    }

    /**
     * Provides an instance of a database read handler.
     *
     * @param booelan $force
     * @return api_db_zend
     */
    protected function getDatabaseReadHandle($force = false) {
        return api_db::factory('read', $force);
    }

    /**
     * Provides an instance of a database write handler.
     *
     * @param booelan $force
     * @return api_db_zend
     */
    protected function getDatabaseWriteHandle($force = false) {
        return api_db::factory('write', $force);
    }
}