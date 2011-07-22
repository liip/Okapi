<?php

abstract class openIdTestCase extends PHPUnit_Framework_TestCase {


    /*************************************************************************/
    /* Fixtures                                                              */
    /*************************************************************************/

    /**
     * Provides an instance of the LightOpenId library.
     *
     * @return LightOpenId
     */
    protected function getLightOpenIdFixture()
    {
        return $this->getMockBuilder('LightOpenId')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Proviedes a standard configuration.
     *
     * @return array
     */
    protected function getConfiguration()
    {
        return array(
            'identityUrl' => 'http://example.com/path/',
            'siteId'  => 'siteId',
            'secret'  => 'password',
        );
    }

    /**
     * Provides an instance of the api_request class.
     *
     * @param array $methods
     * @return api_request
     */
    protected function getRequestFixture(array $methods = array()) {
        return $this->getMockBuilder('api_request')
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * Provides an instance of the api_response class.
     *
     * @param array $methods
     * @return api_response
     */
    protected function getResponseFixture(array $methods = array()) {
        return $this->getMockBuilder('api_response')
            ->setMethods($methods)
            ->getMock();
    }
}