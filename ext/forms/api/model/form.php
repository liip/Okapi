<?php
/**
 * Represents form data as entered by the user.
 */
class api_model_form extends api_model {
    /** api_request: Current request. */
    protected $request;
    
    /** api_validation: Validation context. */
    protected $validation = null;
    
    /** hash: Array of form elements including validation results. */
    protected $form = null;
    
    /**
     * Constructor.
     * @param $request api_request: Current request context.
     * @param $validation api_validation: Object to validate form against.
     *                    Can be null in which case no validation is done.
     */
    function __construct($request, $validation = null) {
        $this->request = $request;
        $this->validation = $validation;
    }
    
    /**
     * Return true if the form has been submitted.
     * @param $name string: If set, the form with the given name has to
     *                      have been submitted. If false, any form will do.
     */
    public function isSubmitted($name = false) {
        $submittedName = $this->request->getParam('f__name');
        if ($name) {
            return ($submittedName == $name);
        } else {
            return ($submittedName != '');
        }
    }
    
    /**
     * Return true if the form has any errors.
     */
    public function hasErrors() {
        if (!$this->isSubmitted()) {
            return false;
        }
        $formName = $this->request->getParam('f__name');
        if (is_null($this->form)) {
            $this->form = $this->getFormArray($formName);
        }
        
        foreach ($this->form[$formName] as &$value) {
            if (isset($value['errors']) && is_array($value['errors']) && count($value['errors']) > 0) {
                return true;
            }
        }
    }
    
    /**
     * Return the DOM for this form request. This is where the
     * form validation is processed as well.
     */
    public function getDom() {
        $dom = new DOMDocument();
        $dom->loadXML('<form/>');
        
        if (!$this->isSubmitted()) {
            return $dom;
        }
        if (is_null($this->form)) {
            $formName = $this->request->getParam('f__name');
            $this->form = $this->getFormArray($formName);
        }
        
        api_helpers_xml::array2dom($this->form, $dom, $dom->documentElement);
        return $dom;
    }
    
    /**
     * Create the array to represent all required information about this
     * form.
     */
    protected function getFormArray($formName) {
        $fields = array();
        
        $params = $this->request->getParameters();
        foreach ($params as $key => $value) {
            // "Configuration" separated by underlines
            $e = explode('_', $key, 3);
            
            // Fields must start with f_
            if (count($e) < 2 || $e[0] != 'f')
                continue;
            
            // f_i_bla fields are treated as integer
            if (count($e) > 2 && $e[1] == 'i')
                $value = intval($value);
            
            // fill formElements
            $fields[$key] = array('values' => $value);
        }
        
        $this->validateFields($fields);
        
        $missingFields = $this->getMissingFields($fields);
        foreach ($missingFields as $missing) {
            $fields[$missing] = array('values' => null, 'errors' => array('FieldMustExist'));
        }
        
        return array($formName => $fields);
    }
    
    /**
     * Validate the fields of the current form.
     */
    protected function validateFields(&$fields) {
        if (is_null($this->validation)) {
            return;
        }
        
        $values = array();
        foreach ($fields as $key => $value) {
            if (trim($value['values']) != '') {
                $values[$key] = $value;
            }
        }
        
        $errors = $this->validation->validateFields($values);
        if (is_array($errors)) {
            foreach ($errors as $key => $message) {
                $fields[$key]['errors'] = array($message);
            }
        }
    }
    
    /**
     * Return an array of all missing fields.
     * @param $fields hash: All fields which were present in the input.
     */
    protected function getMissingFields($fields) {
        if (is_null($this->validation)) {
            return array();
        }
        
        $filled = array();
        
        foreach ($fields as $key => $params) {
            $value = $params['values'];
            if (!is_null($value) && trim($value) != '') {
                array_push($filled, $key);
            }
        }
        
        $missing = $this->validation->validateForm($filled);
        if (!is_array($missing)) {
            return array();
        }
        return $missing;
    }
}
