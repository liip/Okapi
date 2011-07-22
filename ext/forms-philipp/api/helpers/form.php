<?php

/**
 * Form fields can have the following entries: 
 *   'definition':      string. 
 *                      Label associated with form field 
 * 
 *   'type':            one of these values:
 *                      checkbox, file, hidden, password, select, text, text-tiny, textarea 
 * 
 *   'values':          Array
 *                      Data to use for select field types.
 * 
 *   'required':        bool (default false).
 *                      Error message generated if not submitted.
 *                      Submitted non-required fields with a value of '' are excluded from the output of getMappedData() unless they have allowEmpty set.
 * 
 *   'allowEmpty':     bool (default: false)
 *                      These values, if submitted, will be included in the output of getMappedData() even if they have a value of ''  
 *                      If allowEmpty is set, and the submitted value is '', then none of the rules for the field will be validated. 
 * 
 *   'rules':           Array
 *                      Rules to validate the submitted data.  See api_validate and api_validate_* 
 * 
 *   'mapTo':          string
 *                      String representing an array key, used to map data to a flat array (e.g. for a model record).
 *                      See getMappedData() and getReverseMappedData()
 * 
*/ 

class api_helpers_form  {

    protected static $xml;

    protected static $errors_node = null;
    
    protected static function initXml() {
        if(!self::$xml instanceof DomDocument) {
            self::$xml = new DomDocument;
            self::$xml->appendChild(self::$xml->createElement('form'));
        }
    }
    
    public static function addMessage($message) {
    	self::initXml();
    	$msgNode = self::$xml->createElement('message', $message);
    	self::$xml->documentElement->appendChild($msgNode);
    }
    
    public static function fields2Dom($fields, $data = Array()) {

        self::initXml();        
        
        $ssNode = self::$xml->createElement('sections');
        self::$xml->documentElement->appendChild($ssNode);
        
        foreach($fields as $sName => $section) {
            
            $sNode = self::$xml->createElement('section');
            $sNode->setAttribute('name', $sName);
            if(!empty($section['description'])) {
                $sNode->setAttribute('description', $section['description']);
            }
            $ssNode->appendChild($sNode);
            
            foreach($section['fields'] as $fName => $field) {
                
                $fieldName = $sName.'_'.$fName;
                
                $fNode = self::$xml->createElement('field');
                $fNode->setAttribute('name', $fieldName);
                $fNode->setAttribute('type', $field['type']);
                
                if(isset($field['required']) && $field['required'] === TRUE) {
                    $fNode->setAttribute('required', 'required');
                }
                
                if(!empty($field['description'])) {
                    $fNode->setAttribute('description', $field['description']);
                    if(strlen($field['description']) > 25) {
                        $fNode->setAttribute('longdescription', '');
                    }
                }

                if(!empty($field['text'])) {
                    $fNode->setAttribute('text', $field['text']);
                }
                
                if(isset($data[$fieldName])) {
                    $fNode->setAttribute('value', $data[$fieldName]);
                }

                if(isset($field['values']) && is_array($field['values'])) {
                    $vsNode = self::$xml->createElement('values');
                    
                    foreach($field['values'] as $value => $name) {
                        if(is_array($name)) {
                            $ogNode = self::$xml->createElement('optgroup');
                            $ogNode->setAttribute('label', $value);
                            
                            foreach($name as $ogValue => $ogName) {
                                $vNode = self::$xml->createElement('option', $ogName);
                                $vNode->setAttribute('value', $ogValue);
                                $ogNode->appendChild($vNode);
                            }
                            
                            $vsNode->appendChild($ogNode);
                            
                        } else {
                            $vNode = self::$xml->createElement('option', $name);
                            $vNode->setAttribute('value', $value);
                            $vsNode->appendChild($vNode);
                        }
                    }
                    $fNode->appendChild($vsNode);
                }
                
                $sNode->appendChild($fNode);
                
            }
            
        }
        
        return self::$xml;
    }
    
    public static function validateFields($fields, $data) {
        self::initXml();        

        self::$errors_node = self::$xml->createElement('errors');
        self::$xml->documentElement->appendChild(self::$errors_node);
        
        $formValid = TRUE;

        $validator = new api_validator($fields, $data);
        
        foreach($fields as $sName => $section) {
            foreach($section['fields'] as $fName => $field) {
                $fieldName = $sName.'_'.$fName;
                $value = self::getFieldValue($fieldName, $field, $data);
                $isValueEmpty = $value === '';
                
                $errorType = FALSE;
                $errorMsg = '';

                if(isset($field['required']) && $field['required'] === TRUE) {
                    if (is_null($value) || ($isValueEmpty && empty($field['allowEmpty']))) {
                        $formValid = FALSE;
                        $errorType = 'required';
                        self::appendError($errorMsg, $fieldName, $errorType, $field);
                    }
                }

                if(!$errorType && !$isValueEmpty) {
                    if (isset($field['rules']) && is_array($field['rules']) && !empty($field['rules'])) {
                        foreach($field['rules'] as $rkey => $rvalue) {
                            if(is_int($rkey)) {
                                $rule = $rvalue;
                                $params = Array();
                            } else {
                                $rule = $rkey;
                                $params = $rvalue;
                            }
                            if(!$validator->isValid($rule, $value, $params)) {
                                $formValid = FALSE;
                                $errorType = 'rule';
                                $errorMsg = $validator->getMessage($rule);
                                self::appendError($errorMsg, $fieldName, $errorType, $field);
                            }
                        }
                    }
                }
            }
        }

        return $formValid;
    }

    public static function appendError($errorMsg, $fieldName, $errorType, $field) {
        $eNode = self::$xml->createElement('error', $errorMsg);
        $eNode->setAttribute('fieldname', $fieldName);
        $eNode->setAttribute('type', $errorType);
        if(!empty($field['description'])) {
            $eNode->setAttribute('description', $field['description']);
        }
        self::$errors_node->appendChild($eNode);
    }

    public static function getData($fields, $data) {
        $out = Array();
        
        foreach($fields as $sName => $section) {
            foreach($section['fields'] as $fName => $field) {
                $fieldName = $sName.'_'.$fName;

                $value = self::getFieldValue($fieldName, $field, $data);
                
                if(!is_null($value)) {
                    $out[$fieldName] = Array();
                    $out[$fieldName]['field'] = $field;
                    $out[$fieldName]['value'] = $value;
                }
                
            }
        }
        
        return $out;
    }


    /**
     * Gets a $key=>$value array based on the $fields and $data.
     * If the field has a mapTo entry, that will be used as the key.  Otherwise,
     * the field name itself will be used as the key.
     * If mapTo is false, the field will not be included in the return array.
     *
     * @param array $fields field definition
     * @param array $data submitted data
     * @param bool $includeEmpty = false  If true, an entry will exist in the return array for a field even if it is an empty string
     * @return array
    */
    public static function getMappedData($fields, $data,  $includeEmpty = false) {
        $out = Array();
        
        foreach($fields as $sName => $section) {
            foreach($section['fields'] as $fName => $field) {

                $mapToExists =  isset($field['mapTo']);
                if (!($mapToExists && ($field['mapTo'] === false))) {
                    $fieldName = $sName.'_'.$fName;
                    $value = self::getFieldValue($fieldName, $field, $data);
                    if (!is_null($value)) {
                        if ($includeEmpty || strlen($value) || !empty($field['allowEmpty'])) {
                            $name = $mapToExists ? $field['mapTo'] : $fName;
                            $out[$name] = $value;
                        }
                    }
                }
            }
        }
        
        return $out;
    }

    /**
     * Maps data to an array containing form_element_name=>value pairs.
    */
    public static function getReverseMappedData($fields, $data,  $exclude=array()) {
        $out = Array();
        foreach($fields as $sName => $section) {
            foreach($section['fields'] as $fName => $field) {
                $isMapped = isset($field['mapTo']);
                if (!$isMapped || $field['mapTo'] !== false) {
                    $sourceName = $isMapped ? $field['mapTo'] : $fName;
                    if (isset($data[$sourceName]) && !in_array($sourceName, $exclude)) {
                        $fieldName = $sName.'_'.$fName;
                        $out[$fieldName] = $data[$sourceName];
                    }
                }
            }
        }
        
        return $out;
    }

    public static function formatData($data) {
        $out = '';
        
        foreach($data as $fName => $field) {
            $out .= "{$fName} ({$field['field']['description']}):\n";
            
            if($field['field']['type'] === 'file') {
                $out .= "{$field['value']['name']}\n\n";
                
            } else if($field['field']['type'] === 'checkbox' && empty($value)) {
                $out .= "[x]\n\n";
                
            } else if($field['field']['type'] === 'select') {
                $out .= "{$field['field']['values'][$field['value']]}\n\n";
                
            } else {
                $out .= "{$field['value']}\n\n";
                
            }
        }
        
        return $out;
 
    }
    
    public static function getFieldValue($fieldName, $field, $data, $missingValue=null) {
        if($field['type'] === 'file') {
            $value = isset($_FILES[$fieldName]) && !empty($_FILES[$fieldName]['tmp_name']) ? $_FILES[$fieldName] : $missingValue;
        }
        if (!isset($data[$fieldName])) {
            $value = $missingValue;
        } else {
            $value = $data[$fieldName];
        }
        return $value;
    }

    public static function getFieldByName($fieldName, $fields) {
        list($sectionName, $shortName) = explode('_', $fieldName, 2); 
        if (isset($fields[$sectionName][$shortName])) {
            return $fields[$sectionName][$shortName];
        } else {
            return null;
        }
    }
 
}


