<?php
/**
 * This class acts as controller for an openid identity provider.
 *
 * Its main purpose is to dispatch the in coming requests to the real implementation and
 * filter unauthorized access requests.
 *
 * If the requester is not allowed to access the provider the Http 403 Authentication required will be
 * responded.
 *
 * @license Licensed under the Apache License, Version 2.0. See the LICENSE and NOTICE file for further information
 * @copyright 2011 Liip AG
 */

require __DIR__ . '/constants.php';

class api_command_openid_provider extends api_command {

    /**
     * URI of the acting server
     * @var string
     */
    protected $domainUrl = '';

    /**
     * Instance of the permission handler.
     * @var api_model_permission_handler
     */
    protected $ph = null;

    /**
     * Contains the central configuration array
     * @var array
     */
    protected $configuration = array();

    /**
     * List of defined errors
     * @var array
     */
    protected $errors = array(
        'OKAPI_OPENID_AUTHENTICATION_FAILED' => array(
            'code'        => OKAPI_OPENID_AUTHENTICATION_FAILED,
            'type'        => 'fatal',
            'description' => 'failedLogin',
            'text'        => 'Login failed - The provided credentials do not fit any existing user.'
        ),
    );


    /**
     *
     * @param array $attribs The attributes as returned by api_routing::getRoute().
     */
    public function __construct($attribs) {
        $this->loadConfiguration();
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
        $this->response->redirect($this->request->getReferer(), 404);
    }

    /**
     * Alias for the discover method.
     *
     * @param string $guid
     * @route /member/+guid
     * @see discover()
     */
    public function find($guid) {
        $this->discover();
    }

    /**
     * Answers on a openid discovery request.
     *
     * @route /member
     *
     */
    public function discover() {
        // is requester allowed to get information?
        if (!$this->getPermissionHandler()->isAllowed($this->request)) {
            $this->sendAuthenticationFailure();
        }

        switch ($this->request->getVerb()) {
            case 'HEAD':
                // signalize that there is no x-xrds-location available
                $this->response->setContentType('text/html');
                $this->response->setCode(200);
                $this->response->send();
                exit();
                break;
            case 'GET':
                $xml = '
                    <openid_discovery>
                        <openid rel="v1">
                            <server rel="openid.server" href="'.$this->getDomainUrl().'/server" />
                        </openid>
                        <openid rel="v2">
                            <server rel="openid2.provider" href="'.$this->getDomainUrl().'/server" />
                        </openid>
                    </openid_discovery>
                ';
                $this->data[] = api_model_factory::get('string', array($xml));
                break;
            default:
                $this->response->setCode(405);
                $this->response->send();
                exit();
        }
        return true;
    }

    /**
     * Called to request for authentication.
     *
     * Usually this happens after a discovery request.
     *
     * @route /server?<queryString>
     */
    public function authenticate() {
        // is requester allowed to get information?
        if (!$this->getPermissionHandler()->isAllowed($this->request)) {
            $this->sendAuthenticationFailure();
        }

        try {
            // access granted
            $provider = $this->getOpenIdProvider();
            $provider->init(
                $this->response,
                $this->request,
                $this->getUserHandler(),
                $this->getPermissionHandler()
            );
            $provider->server();

        } catch (OkapiOpenidAuthenticationRequest $e) {
            $this->data[] = api_model_factory::get('string', array($this->getLoginFormXml($e->getCode())));
            return;

        } catch(OkapiOpenidAssociationRequest $e) {
            $this->data[] = api_model_factory::get('string', array($this->getAssociationRequestFormXml()));
            return;

        } catch (OkapiOpenidAuthenticationFailed $e) {
            $this->sendAuthenticationFailure();

        } catch (Exception $e) {
            // something internal failed
            $this->response->setCode(500);
            $this->response->send();

            // write to log
            if ($msg = $e->getMessage()) {
                print_r($msg);
            }
            exit();
        }
    }


    /*************************************************************************/
    /* Helpers                                                               */
    /*************************************************************************/

    /**
     * Get XSL parameters from command. Used to overwrite view configuration
     * from the route.
     * @return  array: Associative array with params.
     */
    public function getXslParams() {
        $view = $this->route['view'];

        // manipulate location of the template
        $view['xsl'] = sprintf(
            '%s%s',
            '../../' . substr(__DIR__, strlen(API_PROJECT_DIR)) . '/../../../themes/openid/',
            $view['xsl']
        );

        return $view;
    }
    /**
     * Generates the xml representing the bind request from.
     *
     * @return string XML representation of a bind request form.
     */
    protected function getAssociationRequestFormXml() {
        $form = '
            <openid_bind>
            </openid_bind>
        ';
        return $form;
    }

    /**
     * Generates the xml representing a login form.
     *
     * @param integer $error Error number to be resolved to a string and be displayed.
     * @return string XML representation of a login form.
     */
    protected function getLoginFormXml($error) {

        $form = '
            <openid_login>
                <form method="post" action="">
                    Username: <input type="text" value="" name="usr" />
                    Passwort: <input type="password" value="" name="password" />
                    <input type="cancel" value="cancel" name="cancel" />
                    <input type="submit" value="submit" name="submit" />
                    <input type="hidden" name="openid.assoc_handle" value="' . $this->request->getParam['openid.assoc_handle'] . '">
                </form>';

        if (-1 < $error) {
            $form .= sprintf(
                '<error type="%s" description="%s">%s</error>',
                $this->errors[$error]['type'],
                $this->errors[$error]['description'],
                $this->errors[$error]['text']
            );
        }
        $form .= '
            </openid_login>
        ';
        return $form;
    }

    /**
     * Sends a response with http status code 403
     */
    protected function sendAuthenticationFailure() {
        $this->response->setCode(403);
        $this->response->send();
        exit();
    }

    /**
     * Provides an instance of the api_openid_provider.
     *
     * @return api_openid_provider
     */
    protected function getOpenIdProvider() {
        if (!isset($this->ip)) {
            $this->ip = new api_openid_provider();
        }
        return $this->ip;
    }

    /**
     * Provides an instance of the api_model_permission_handler.
     *
     * Get permission handler class name from configuration object
     *
     * @return api_model_permission_handler
     */
    protected function getPermissionHandler() {
        if (!isset($this->ph)) {
            $this->ph = new $this->configuration['provider']['permission_handler']['classname']();
        }
        return $this->ph;
    }

    /**
     * Load configuration from default.yml
     */
    protected function loadConfiguration() {
        $config = api_config::getInstance();
        $this->configuration = $config->openid;
    }

    /**
     * Provides the Url of the current domain.
     *
     * Examples:
     *   http://migipedia.ch
     *   https://migipedia.ch
     *
     * @return string
     */
    protected function getDomainUrl() {
        if (empty($this->domainUrl)) {
            $this->domainUrl = sprintf('%s://%s', $this->request->getSchema(), $this->request->getHost());
        }
        return $this->domainUrl;
    }
}