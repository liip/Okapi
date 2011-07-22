<?php

class api_command_sample extends api_command {

    public function contact() {
        
        $this->viewParams['xsl'] = 'form/misc_contact.xsl';
        $fields = api_form_misc_contact::getFields();
        
        if ($this->request->getVerb() === 'POST') {

            $params = $this->request->getParameters()->post();

            $redirectTo = !empty($params['hidden_from']) ? $params['hidden_from'] : null;

            if (api_helpers_form::validateFields($fields, $params)) {
                $data = api_helpers_form::getMappedData($fields, $params);
                api_helpers_email::sendMiscContact($data);                
                $this->redirect($redirectTo);
            }
            
        } else {
            $params = array();
        }

        $fieldDom = api_helpers_form::fields2Dom($fields, $params);
        $this->data[] = api_model_factory::get('dom', array($fieldDom));
        
    }

}
