<?php
/**
 *
 *
 * @copyright 2011 Liip AG
 * @link http://openid.net/specs/openid-authentication-2_0.html
 *
 */
class api_openid_client_response {

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
     * Determines the current openid mode
     *
     * @return null|boolean
     */
    public function getMode()
    {
        return $this->getOpenIdObject()->mode;
    }

    /**
     * Performs OpenID verification with the OP.
     *
     * @return boolean  Whether the verification was successful.
     * @thorws OpenIdErrorException in case the response did not validate.
     */
    public function Validate()
    {
        try {
            return $this->getOpenIdObject()->validate();
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
     * Gets AX/SREG attributes provided by OP. should be used only after successful validaton.
     *
     * Note that it does not guarantee that any of the required/optional parameters will be present,
     * or that there will be no other attributes besides those specified.
     * In other words. OP may provide whatever information it wants to.
     * SREG names will be mapped to AX names.
     *
     * @return array Set of attributes with keys being the AX schema names, e.g. 'contact/email'
     * @link https://jira.liip.ch/secure/attachment/24885/MGB-M-connect-Integrationsleitfaden-DRAFT-V1.4.6.pdf
     * @link http://openid.net/specs/openid-attribute-exchange-1_0.html
     */
    public function getAttributes() {
        return $this->getOpenIdObject()->getAttributes();
    }

    /**
     * Normalizes the given attributes by applying the given attributesMap.
     *
     * @param array $attributes
     * @param array $attributesMap
     * @return array
     */
    public function normalizeAttributeNames(array $attributes, array $attributesMap)
    {
        $normalized = array();
        foreach ($attributes as $key => $value) {
            $key = isset($attributesMap[$key]) ? $attributesMap[$key] : $key;
            $normalized[$key] = $value;
        }
        return $normalized;
    }

    /**
     * Handles the ongoing process if the authetication request was cancelled.
     *
     * @param string $returnUrl
     * @param api_response $response
     */
    public function cancelled($returnUrl, api_response $response)
    {
        $response->redirect($returnUrl, 304);
    }

    /*************************************************************************/
    /* Helper methods                                                        */
    /*************************************************************************/

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