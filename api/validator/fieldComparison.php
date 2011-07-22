<?php
/**
 * Checks that the value compares well to another field (how to compare is determined by $params['operator']
*/
class api_validator_fieldComparison extends api_validator_base {

    protected $invalidMessage = 'validator_fields_comparison_fails';
    private $fields = null;
    private $data = null;

    public function __construct($params) {
        $this->fields = $params['fields'];
        $this->data = $params['data'];
    }

    public function checkValidity($value, $params) {
        $fieldName = $params['field'];
        $operator = $params['operator'];
        $field = api_helpers_form::getFieldByName($fieldName, $this->fields);
        $value2 = api_helpers_form::getFieldValue($fieldName, $field, $this->data);
        $valid = false;

        if (!is_null($value2)) {        
            switch ($operator) {
                case '>':
                    $valid = $value > $value2;
                    break;
                case '>=':
                    $valid = $value >= $value2;
                    break;
                case '<':
                    $valid = $value < $value2;
                    break;
                case '<=':
                    $valid = $value <= $value2;
                    break;
                case '=':
                case '==':
                    $valid = $value == $value2;
                    break;
                case '===':
                    $valid = $value === $value2;
                    break;
                default:
                    throw new api_exception_badParam('Unrecognized operator');
                    break;
            }
        }
        return $valid;
    }

}
