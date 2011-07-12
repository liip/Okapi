<?php

class api_command_openid_response_mconnect extends api_command {

    public function __construct($attribs) {
        parent::__construct($attribs);
    }

    public function index() {
        $configuration = api_config::getInstance();
        $openIdResponse = new api_openid_client_response($configuration->openid['client']);

        if ($openid->getMode() == 'cancel') {
            // user decided not to cancel the process.
            $openIdResponse->cancelled($this->getReferrer(), $this->response);

        } else {
            // authentication done.. check for validity
            if ($openIdResponse->Validate()) {
                // process response

            } else {
                // invalid response, send home
                $this->response->redirect($this->getReferrer(), 400);
            }
        }
    }

    /**
     * Determines where to return to.
     *
     * @return string Url where to be redirected to.
     */
    protected function getReferrer($foreignService = '') {
        $configuration = api_config::getInstance();

        // if no referrer is set go home ;)
        $referrer = '/';

        if (!empty($foreignService)
            && isset($configuration->openid['client']['foreignServices'][$foreignService]['returnUrl'])) {
            $referrer = $configuration->openid['client']['foreignServices']['mconnect']['returnUrl'];
        } else if (isset($_SERVER[’HTTP_REFERER’])) {
            $referrer = $_SERVER[’HTTP_REFERER’];
        }

        return $referrer;
    }
}
