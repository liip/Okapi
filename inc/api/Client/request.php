<?php
/**
 *
 *
 * @copyright 2011 Liip AG
 * @link http://openid.net/specs/openid-authentication-2_0.html
 *
 */

class api_openId_client_request {

    /**
     * Set of configuration data
     * @var array
     */
    protected $configuration = array();

    /**
     * Instance of the openId class
     * @var LightOpenID
     */
    protected $openId = null;


    /**
     *
     * @param array $configuration
     */
    public function __construct(array $configuration) {
        $this->setConfiguration($configuration);
    }

    /*************************************************************************/
    /* API                                                                   */
    /*************************************************************************/

    /**
     * Returns authentication url. Usually, you want to redirect your user to it.
     *
     * @param  string $immediate May be used to prevent another discovery process,
     *                           if the openid Provider returned an 'openid_user_setup_url' in a future response.
     * @return string            The authentication url.
     *
     * @throws OpenIdErrorException in case of:
     *   * no identity url was defined.
     *   * no openId server was found using the given identity.
     *   * an endless redirection (5 jumps are allowed).
     */
    function authUrl($immediate = false) {
        try {
            return $this->getOpenIdObject()->authUrl($immediate);
        } catch (ErrorException $e) {
            throw new OpenIdErrorException(
                $e->getMessage(),
                $e->getCode(),
                $e->getSeverity(),
                $e->getFile(),
                $e->getLine(),
                $e
            );
        }
    }

    /**
     * Registers the path the openId identity provider will call on your trustedRoot (realm) after authentication.
     *
     * @param string $path
     */
    public function setReturnPath($path) {
        $this->getOpenIdObject()->returnUrl = $this->getRealm() . $path;
    }

    /**
     * Defines the list of required and optional AX_Attribute to be received from the identity provider.
     *
     * NOTICE:
     * This will override every existing entry. User addRequiredArrtibute() or addOptionalAttribute() to
     * prevent other entries from being overridden.
     *
     * structure:
     * array(
     *   'required' => array( 'email', … ),
     *   'optional' => array( 'userName', … ),
     * )
     *
     * @param array $attributes
     *
     * @link http://openid.net/specs/openid-attribute-exchange-1_0.html
     */
    public function setAttributes(array $attributes) {
        if (isset($attributes['required'])) {
            $this->getOpenIdObject()->required = $attributes['required'];
        }
        if (isset($attributes['optional'])) {
            $this->getOpenIdObject()->optional = $attributes['optional'];
        }
    }

    /**
     * Adds the given attribute to the list of required AX-Attributes to receive from the ID-Provider.
     *      *
     * Information about available attributes may be found in chapter 5.1.1 of the linked PDF.
     *
     * @param string $attribute
     *
     * @see addAttribute()
     * @link https://projects.liip.ch/download/attachments/131596393/MGB-M-connect-Integrationsleitfaden-DRAFT-V1.4.6.pdf
     * @link http://www.axschema.org/types/
     * @link http://openid.net/specs/openid-attribute-exchange-1_0.html
     */
    public function addRequiredAttribute($attribute) {
        $this->addAttribute($attribute, 'required');
    }

    /**
     * Adds the given attribute to the list of optional AX-Attributes to receive from the ID-Provider.
     *
     * Information about available attributes may be found in chapter 5.1.1 of the linked PDF.
     *
     * @param string $attribute
     *
     * @see addAttribute()
     * @link https://projects.liip.ch/download/attachments/131596393/MGB-M-connect-Integrationsleitfaden-DRAFT-V1.4.6.pdf
     * @link http://www.axschema.org/types/
     * @link http://openid.net/specs/openid-attribute-exchange-1_0.html
     */
    public function addOptionalAttribute($attribute) {
        $this->addAttribute($attribute, 'optional');
    }

    /**
     * Defines the domain (incl. protocol (HTTP(S)?://) to be trusted for the next request.
     *
     * Usually it is the current http host and the used protocol.
     * e.g.
     *     http://example.org
     *
     * @param string $url
     *
     */
    public function setTrustRoot($url) {
        $this->getOpenIdObject()->trustRoot = $url;
    }

    /**
     * Provides the url to the host currently defined as trustworthy to communicat with.
     *
     * @link http://openid.net/specs/openid-authentication-2_0.html#realms
     */
    public function getTrustRoot() {
        return $this->getOpenIdObject()->trustRoot;
    }

    /**
     * Alias to setTrustRoot();
     *
     * @param string $url
     * @see setTrustRoot()
     */
    public function setRealm($url) {
        $this->setTrustRoot($url);
    }

    /**
     * Alias to getTrustroot().
     *
     * @see getTrustRoot()
     */
    public function getRealm() {
        return $this->getTrustRoot();
    }

    /**
     * Defines the url where to ask for the openid identity.
     *
     * In case you want to override the url generated by setConfiguration(), you use this method.
     *
     * Example:
     *     https://dev.m-connect.ch/member/
     *
     * @param string $identity
     */
    public function setIdentity($identity) {
        return $this->getOpenIdObject()->identity = $identity;
    }

    /**
     * Provides the identity url.
     *
     * @return string
     */
    public function getIdentity()
    {
        return $this->getOpenIdObject()->identity;
    }

    /**
     * Checks if the server specified in the url exists.
     *
     * @param $url url to check
     * @return true, if the server exists; false otherwise
     */
    public function hostExists($url) {
        return $this->getOpenIdObject()->hostExists($url);
    }

    /*************************************************************************/
    /* Helper methods                                                        */
    /*************************************************************************/

    /**
     * Adds an AX attribute identifier to the list of AX-Attributes to be received from the ID-Provider.
     *
     * @param atring $attribute
     * @param string $type
     *
     * @throws OpenIdInvalidAxAttributeTypeException in case the given attribute type does not match 'required' or 'optional'.
     * @throws OpenIdInvalidArgumentException in case the type of the given attribute is not string.
     * @link http://openid.net/specs/openid-attribute-exchange-1_0.html
     */
    protected function addAttribute($attribute, $type) {

        if (!in_array($type, array('required', 'optional'))) {
            throw new OpenIdInvalidAxAttributeTypeException(
                'There is no attribute type like: ' . $type . '! Either use "required" or "optional".'
            );
        }

        if (!is_string($attribute)) {
            throw new OpenIdInvalidArgumentException('The name of the attribute has to be a string.');
        }

        // prevent duplication
        if (!in_array($attribute, $this->getOpenIdObject()->$type)) {
            $types = $this->getOpenIdObject()->$type;
            $types[] = $attribute;
            $this->getOpenIdObject()->$type = $types;
        }
    }

    /**
     * Registeres the given configuration set and verifies if the mandatory fields are set.
     *
     * Mandatory fields:
     *  * baseUrl : Url identifying the system the request shall be sent to. No trailing slash.
     *  * path    : path identifying the service to be called. Leading slash required.
     *
     * @param array $configuration
     *
     * @throws OpenIdInvalidArgumentException in case a mandatory field is not set of has an invalid value.
     */
    protected function setConfiguration(array $configuration)
    {
        $mandatoryFields = array('identityUrl');
        $intersections = array_intersect($mandatoryFields, array_keys($configuration));

        if (count($mandatoryFields) != count($intersections)) {
            throw new OpenIdInvalidArgumentException(
                'Mandatory fields are missing: ' . implode(', ', array_diff($mandatoryFields, $intersections))
            );
        }
        $this->configuration = $configuration;
    }

    /**
     * Provides an instance of the openId class
     * @return LightOpenID
     */
    protected function getOpenIdObject()
    {
        if (empty($this->openId)) {
            $this->openId = new LightOpenID();
            $this->openId->identity = $this->configuration['identityUrl'];
        }
        return $this->openId;
    }

}