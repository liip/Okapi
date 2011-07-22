<?php
/**
 * Checks that the value matches the value of another field
*/
class api_validator_equalsField extends api_validator_base {

    protected $invalidMessage = 'validator_fields_not_equal';
    private $fields = null;
    private $data = null;

    public function __construct($params) {
        $this->fields = $params['fields'];
        $this->data = $params['data'];
    }

    public function checkValidity($value, $params) {
        $field_name = $params['field'];
        $field = api_helpers_form::getFieldByName($field_name, $this->fields);
        $value2 = api_helpers_form::getFieldValue($field_name, $field, $this->data);
        return $value === $value2;
    }

}
