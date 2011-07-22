<?php
/**
 * Base class for implementing form validations.
 */
class api_validation {
    /**
     * Check if the given values are valid submissions.
     * @param $fields hash: Key/value pairs of the submitted fields.
     * @return hash: Status of the validation. For each non-valid field an
     *               error message is to be returned. The message is some
     *               machine-readable information about why the value is
     *               invalid.
     */
    public function validateFields($fields) {
        return array();
    }
    
    /**
     * Return a list of all fields which are missing in the input.
     * @param $fields hash: Hash with all fields that have a non-empty
     *                      value in the input.
     * @return array: Array of all field names missing in the input.
     */
    public function validateForm($fields) {
        return array();
    }
}
