<?php
class api_form_misc_contact {

    public static function getFields() {
        
        $fields = array(
            'contactdata' => array(
                'fields' => array(
                    'firstname' => array(
                        'description' => 'First name',
                        'type' => 'text',
                        'required' => TRUE,
                    ),
                    'name' => array(
                        'description' => 'Last name',
                        'type' => 'text',
                        'required' => TRUE,
                    ),
                    'streetaddress' => array(
                        'description' => 'Street',
                        'type' => 'text',
                        'required' => TRUE,
                    ),
                    'zipcode' => array(
                        'description' => 'Zip',
                        'type' => 'text',
                        'required' => TRUE,
                    ),
                    'city' => array(
                        'description' => 'City',
                        'type' => 'text',
                        'required' => TRUE,
                    ),
                    'country' => array(
                        'description' => 'Country',
                        'type' => 'text',
                        'required' => TRUE,
                    ),
                    'phone' => array(
                        'description' => 'Telephone',
                        'type' => 'text',
                        'required' => TRUE,
                    ),
                    'email' => array(
                        'description' => 'E-Mail',
                        'type' => 'text',
                        'rules' => Array('email'),
                        'required' => TRUE,
                    ),
                ),
            ),
            'messagearea' => array(
                'description' => 'Section Title',
                'fields' => array(
                    'message' => array(
                        'description' => 'Message',
                        'type' => 'textarea',
                    ),
                ),
            ),
        );
        
        return $fields;
    }
       
}
