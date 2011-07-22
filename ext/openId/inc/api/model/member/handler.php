<?php
/**
 * @license Licensed under the Apache License, Version 2.0. See the LICENSE and NOTICE file for further information
 * @copyright 2011 Liip AG
 */
class api_model_member_handler {

    protected $dbh;
    protected $request;
    protected $realm;
    protected $secret;
    protected $hash;
    protected $data = array();

    public function __construct($dbh, api_request $request) {
        $this->dbh = $dbh;
        $this->request = $request;
    }

    /**
     * Provides information about a member identified by the given realm.
     *
     * @param string $realm  Host name of the requester.
     * @return api_model_member_handler
     */
    public function find($realm) {
        $member = $this->getMemberFromDB($realm);

        $this->realm = $realm;
        $this->secret = $member['secret'];
        $this->data   = $member['data'];
        return $this;
    }

    /**
     * Verifies if the requester is the same
     *
     * @param string $hash Unique Identifier of the
     */
    public function verify($hash) {
        return $hash == $this->getHash();
    }

    /**
     * Provides the authentication hash to verify the access permissions.
     *
     * Once a hash is created it is cached for the object lifetime.
     * The hash will be calculated from the host name of the requester and a previous shared secret.
     *
     * @retrun string
     */
    public function getHash() {
        if (!isset($this->hash)) {
            $this->hash = sha1($this->realm.$this->secret);
        }
        return $this->hash;
    }

    /**
     * Fetches information about a openid member identified by the given reqlm.
     *
     * @param string $realm
     * @return array
     */
    protected function getMemberFromDB($realm) {
        $realm = $this->dbh->quote($realm);
        $sql = "SELECT * FROM " . OPENID_DB_TABLE_MEMBERS ." WHERE realm = $realm LIMIT 1";
        $member = $this->dbh->fetchAll($sql);

        $member['data'] = unserialize($value);
        $member['secret'] = $member['data']['secret'];
    }
}