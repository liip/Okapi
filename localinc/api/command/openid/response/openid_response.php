<?php

class api_command_openid_response_mconnect extends api_command {

    public function __construct($attribs) {
        parent::__construct($attribs);
    }

    /**
     * Default method called by api_command::process (as specified with
     * api_command::$defaultMethod).
     *
     * If you want a catch-all method that is executed on every request,
     * overwrite api_command::process(). If you just want a fall-back for
     * the case when a method specified in the route doesn't exist in this
     * class, then overwrite api_command::defaultRequest().
     *
     */
    public function defaultRequest() {

        $this->response->redirect($this->getReferrer(), 404);
    }

    /**
     *
     * @route /openid/response/mconnect/index
     */
    public function index() {
        $configuration = api_config::getInstance();
        $openIdResponse = new api_openid_client_response($configuration->openid['client']['mconnect']);

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
            && isset($configuration->openid['client'][$foreignService]['redirectUrl'])) {
            $referrer = $configuration->openid['client']['mconnect']['redirectUrl'];
        } else if (isset($_SERVER[’HTTP_REFERER’])) {
            $referrer = $_SERVER[’HTTP_REFERER’];
        }

        return $referrer;
    }
}
