<?php
/**
 * @license Licensed under the Apache License, Version 2.0. See the LICENSE and NOTICE file for further information
 * @copyright 2011 Liip AG
 */
interface api_model_permission_handler_interface {

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
    public function isAllowed(api_request $request);

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
    public function generateAssociationKey();

    /**
     * Loads information about the association asked for by handle.
     *
     * @param string $handle
     * @return array
     */
    public function getAssoc($handle);

    /**
     * Stores an association.
     *
     * @param String $handle Association handle -- should be used as a key.
     * @param Array $assoc Association data.
     * @throws OkapiOpenidAssociationBindingFailed
     */
    public function setAssoc($handle, $assoc);

    /**
     * Deletes an association.
     *
     * @param String $handle Association handle.
     * @throws OkapiOpenidAssociationDeletionFailed
     */
    public function delAssoc($handle);
}