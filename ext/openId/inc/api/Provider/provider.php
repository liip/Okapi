<?php
/**
 * This class implements the business logic for an openid identity provider.
 *
 * @copyright 2011 Liip AG
 *
 */
class api_openid_provider extends LightOpenIDProvider{

    /**
     * Instance of the api_response class.
     * @var api_response
     */
    protected $response;

    /**
     * Instance of the api_reques class.
     * @var api_reques
     */
    protected $request;

    /**
     * Instance of an implementation api_openid_user_interface.
     * @var api_openid_user_interface
     */
    protected $user;

    /**
     * Instance of the api_model_permission_handler class.
     * @var api_model_permission_handler
     */
    protected $ph;

    /*************************************************************************/
    /* Interface implementation                                              */
    /*************************************************************************/

    /**
     * Checks whether an user is authenticated.
     *
     * The function should determine what fields it wants to send to the RP,
     * and put them in the $attributes array.
     *
     * @param Array $attributes
     * @param String $realm Realm used for authentication.
     *
     * @return String OP-local identifier of an authenticated user, or an empty value.
     */
    public function checkid($realm, &$attributes) {

        // if the authentication request was cancelled .. fullstop.
        if($this->request->getParam('cancel')) {
            return $this->cancel();
        }

        try {
            // get user data
            $data = $this->getUserInformation();

            // read requested attributes from $data
            $attributes = $this->processDataAttributes($attributes);
            return $this->serverLocation . '?' . $data['openid_guid'];

        } catch (OkapiOpenidAuthenticationFailed $e) {
            return false;
        }

    }

    /**
     * Displays an user interface for inputing user's login and password.
     *
     * Attributes are always AX field namespaces, with stripped host part.
     * For example, the $attributes array may be:
     * array( 'required' => array('namePerson/friendly', 'contact/email'),
     *        'optional' => array('pref/timezone', 'pref/language')
     *
     * @param String $identity Discovered identity string. May be used to extract login, unless using $this->select_id
     * @param String $realm Realm used for authentication.
     * @param String Association handle. must be sent as openid.assoc_handle in $_GET or $_POST in subsequent requests.
     * @param Array User attributes requested by the RP.
     */
    public function setup($identity, $realm, $assoc_handle, $attributes) {

        // fetch user information to fill in the requested attribute information
        $data = $this->getUserInformation($assoc_handle);
        throw new OkapiOpenidAssociationRequest();
    }

    /*************************************************************************/
    /* implementation specific                                               */
    /*************************************************************************/

    /**
     * Initializes the openid server.
     *
     * @param api_response                           Instance of the api_response class.
     * @param api_request                            Instance of the api_request class.
     * @param api_openid_user_interface              Implementation of the api_openid_user_interface class.
     * @param api_model_permission_handler_interface Implementation of the api_model_permission_handler_interface class.
     */
    public function init(api_response $response, api_request $request, api_openid_user_interface $user,
                           api_model_permission_handler_interface $ph
                        ) {
        $this->response = $response;
        $this->request  = $request;
        $this->user     = $user;
        $this->ph       = $ph;
    }

    /**
     * Trys to get information about the user identified by its association handle.
     *
     * If the handle is set this method finds the user associated to this and finds out
     * if the requester is logged in.
     *
     * @param  string $assoc_handle
     * @throws OkapiOpenidAuthenticationRequest in case the requester is not logged in.
     * @throws OkapiOpenidAuthenticationFailed in case the login of the requester failed.
     */
    protected function getUserInformation($assoc_handle = null) {
        if (!empty($assoc_handle)) {
            // identifier given
            $userHandle = $this->user->find($assoc_handle);
            $error = -1;

            // user identified and user is logged in
            if (isset($userHandle) && $userHandle->isLoggedIn()) {
                return $userHandle->getData();
            }

            $pwd = $this->request->getParam('password', '');
            $usr = $this->request->getParam('username', '');

            if (!empty($pwd) && !empty($usr)) {
                if ($userHandle = $this->user->login($usr, $pwd)) {
                    // login form send
                    // check if credentials are correct
                    if ($userHandle->isValid()) {
                        return $userHandle->getData();
                    }
                }
                // login failed
                // display error
                $error = OKAPI_OPENID_AUTHENTICATION_FAILED;
            }
            throw new OkapiOpenidAuthenticationRequest('', $error);

        } else {
            // return with 403
            throw new OkapiOpenidAuthenticationFailed();
        }
    }

    /**
     * Stores an association.
     *
     * If you want to use php sessions in your provider code, you have to replace it.
     *
     * @param String $handle Association handle -- should be used as a key.
     * @param Array $assoc Association data.
     */
    protected function setAssoc($handle, $assoc) {
        $this->ph->setAssoc($handle, $assoc);
    }

    /**
     * Retreives association data.
     *
     * If you want to use php sessions in your provider code, you have to replace it.
     * In order to pass additional data (e.g. configuration) to the server, use a key
     * named 'data' to store the data as a named array.
     *
     * @param string $handle Association handle.
     *
     * @return array Association data.
     */
    protected function getAssoc($handle) {
        return $this->ph->getAssoc($handle);
    }

    /**
     * Deletes an association.
     *
     * If you want to use php sessions in your provider code, you have to replace it.
     *
     * @param String $handle Association handle.
     */
    protected function delAssoc($handle) {
        $this->ph->delAssoc($handle);
    }

    /**
     * Generates a new unique association handle.
     *
     * This has to be very unique!!
     *
     * @return string
     */
    protected function assoc_handle() {
        return $this->ph->generateAssociationKey();
    }

    /**
     * Redirects the user to the given location.
     *
     * @param String  $location The url that the user will be redirected to.
     * @param integer $status   The Http status code to be returned.
     *
     * @link http://en.wikipedia.org/wiki/List_of_HTTP_status_codes#3xx_Redirection
     */
    protected function redirect($location, $status = 304) {
        $this->response->redirect($location, $status);
    }

    /**
     * Generates a random shared secret.
     *
     * @param string Identifying the length of the created secret.
     * @return string
     */
    protected function shared_secret($hash){
        return parent::shared_secret($hash);
    }

    /**
     * Generates a private key.
     * @param int $length Length of the key.
     */
    protected function keygen($length) {
        return parent::keygen($length);
    }
}