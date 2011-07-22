<?php
/**
 * @license Licensed under the Apache License, Version 2.0. See the LICENSE and NOTICE file for further information
 * @copyright 2011 Liip AG
 */
class api_openid_providerTest extends openIdTestCase {

    public function setUp() {
        $_SERVER['HTTP_HOST'] = 'example.org';
        $_SERVER['REQUEST_URI'] = '';
        $_GET = array('openid_return_to' => 'http://example.org');
        $_POST = array();

        if(!defined('OKAPI_OPENID_AUTHENTICATION_FAILED')) {
            define('OKAPI_OPENID_AUTHENTICATION_FAILED', 403);
        }
    }

    /*************************************************************************/
    /* Fixtures                                                              */
    /*************************************************************************/


    /*************************************************************************/
    /* Tests                                                                 */
    /*************************************************************************/

    /**
     * @covers api_openid_provider::checkid
     * @covers api_openid_provider::init
     */
    public function testCheckIdRequestCancelled() {
        $request = $this->getRequestFixture(array('getParam'));
        $request
            ->expects($this->once())
            ->method('getParam')
            ->will($this->returnValue(true));

        $response = $this->getResponseFixture(array('redirect'));
        $response
            ->expects($this->once())
            ->method('redirect')
            ->with(
                $this->equalTo('http://example.org?openid.mode=cancel&openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0'),
                $this->equalTo(304)
            );

        $user = $this->getMock('api_openid_user_interface');
        $ph = $this->getMock('api_model_permission_handler_interface');
        $attributes = array();

        $provider = new api_openid_provider();
        $provider->init($response, $request, $user, $ph);
        $provider->checkid('example.org', $attributes);
    }

    /**
     * @covers api_openid_provider::checkid
     */
    public function testCheckId() {
        $request = $this->getRequestFixture(array('getParam'));
        $request
            ->expects($this->once())
            ->method('getParam')
            ->will($this->returnValue(false));

        $user = $this->getMock('api_openid_user_interface');
        $ph = $this->getMock('api_model_permission_handler_interface');
        $response = $this->getResponseFixture();
        $attributes = array();

        $provider = new api_openid_provider();
        $provider->init($response, $request, $user, $ph);
        $this->assertFalse($provider->checkid('example.org', $attributes));
    }

    /**
     * @expectedException OkapiOpenidAssociationRequest
     * @covers api_openid_provider::setup
     * @covers api_openid_provider::getUserInformation
     */
    public function testSetup() {
        $request = $this->getRequestFixture(array('getParam'));
        $request
            ->expects($this->exactly(2))
            ->method('getParam')
            ->will($this->returnValue(
                $this->onConsecutiveCalls(
                    $this->equalTo('password'),
                    $this->equalTo('Tux')
                )
            ));
        $user = $this->getMockBuilder('api_openid_user_interface')
            ->setMethods(array('find', 'isValid', 'login', 'getData'))
            ->getMock();
        $user
            ->expects($this->once())
            ->method('find')
            ->will($this->returnValue(null));
        $user
            ->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));
        $user
            ->expects($this->once())
            ->method('login')
            ->will($this->returnValue($user));
        $user
            ->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(array()));

        $ph = $this->getMock('api_model_permission_handler_interface');
        $response = $this->getResponseFixture();
        $attributes = array();

        $provider = new api_openid_provider();
        $provider->init($response, $request, $user, $ph);
        $provider->setup(
            'http://example.org/member/12345678901234567890',
            'example.org',
            '12345678901234567890',
            array()
        );
    }

    /**
     * @expectedException OkapiOpenidAssociationRequest
     * @covers api_openid_provider::getUserInformation
     */
    public function testGetUserInformation() {
        $user = $this->getMockBuilder('api_openid_user_interface')
            ->setMethods(array('find', 'isLoggedIn', 'getData'))
            ->getMock();
        $user
            ->expects($this->once())
            ->method('find')
            ->will($this->returnValue($user));
        $user
            ->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(true));
        $user
            ->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(array()));

        $ph = $this->getMock('api_model_permission_handler_interface');
        $response = $this->getResponseFixture();
        $request = $this->getRequestFixture();
        $attributes = array();

        $provider = new api_openid_provider();
        $provider->init($response, $request, $user, $ph);
        $provider->setup(
            'http://example.org/member/12345678901234567890',
            'example.org',
            '12345678901234567890',
            array()
        );
    }

    /**
     * @expectedException OkapiOpenidAuthenticationRequest
     * @covers api_openid_provider::getUserInformation
     */
    public function testGetUserInformationLogin() {
        $user = $this->getMockBuilder('api_openid_user_interface')
            ->setMethods(array('find', 'isLoggedIn'))
            ->getMock();
        $user
            ->expects($this->once())
            ->method('find')
            ->will($this->returnValue($user));
        $user
            ->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $request = $this->getRequestFixture(array('getParam'));
        $request
            ->expects($this->exactly(2))
            ->method('getParam')
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue(''),
                    $this->returnValue('')
                )
            );
        $ph = $this->getMock('api_model_permission_handler_interface');
        $response = $this->getResponseFixture();
        $attributes = array();

        $provider = new api_openid_provider();
        $provider->init($response, $request, $user, $ph);
        $provider->setup(
            'http://example.org/member/12345678901234567890',
            'example.org',
            '12345678901234567890',
            array()
        );
    }

    /**
     * @expectedException OkapiOpenidAuthenticationRequest
     * @covers api_openid_provider::getUserInformation
     */
    public function testGetUserInformationAuthenticationError() {
        $request = $this->getRequestFixture(array('getParam'));
        $request
            ->expects($this->exactly(2))
            ->method('getParam')
            ->will($this->returnValue(
                $this->onConsecutiveCalls(
                    $this->equalTo('password'),
                    $this->equalTo('Tux')
                )
            ));
        $user = $this->getMockBuilder('api_openid_user_interface')
            ->setMethods(array('find', 'login'))
            ->getMock();
        $user
            ->expects($this->once())
            ->method('find')
            ->will($this->returnValue(null));
        $user
            ->expects($this->once())
            ->method('login')
            ->will($this->returnValue(false));

        $ph = $this->getMock('api_model_permission_handler_interface');
        $response = $this->getResponseFixture();
        $attributes = array();

        $provider = new api_openid_provider();
        $provider->init($response, $request, $user, $ph);
        $provider->setup(
            'http://example.org/member/12345678901234567890',
            'example.org',
            '12345678901234567890',
            array()
        );
    }

    /**
     * @expectedException OkapiOpenidAuthenticationFailed
     * @covers api_openid_provider::getUserInformation
     */
    public function testGetUserInformationAuthenticationFailed() {
        $attributes = array();
        $user = $this->getMock('api_openid_user_interface');
        $ph = $this->getMock('api_model_permission_handler_interface');
        $response = $this->getResponseFixture();
        $request = $this->getRequestFixture();

        $provider = new api_openid_provider();
        $provider->init($response, $request, $user, $ph);
        $provider->setup(
            'http://example.org/member/12345678901234567890',
            'example.org',
            '',
            array()
        );
    }

}