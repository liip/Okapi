<?php
class api_openId_client_request_UnitTest extends openIdTestCase {

    /*************************************************************************/
    /* Tests                                                                 */
    /*************************************************************************/

    /**
     * @covers api_openId_client_request::__construct
     * @covers api_openId_client_request::setConfiguration
     */
    public function testSetConfiguration() {
        $config = array(
            'identityUrl' => 'http://example.com/path/',
            'siteId'  => 'siteId',
            'secret'  => 'password',
        );
        $expected = array(
            'siteId'  => 'siteId',
            'secret'  => 'password',
            'identityUrl' => 'http://example.com/path/',
        );
        $openid = new api_openId_client_request($config);
        $this->assertAttributeEquals($expected, 'configuration', $openid);
    }

    /**
     * @expectedException OpenIdInvalidArgumentException
     * @covers api_openId_client_request::setConfiguration
     */
    public function testSetConfigurationExpectingInvalidArgumentException()
    {
        $config = array(
            'baseUrl' => 'http://example.com',
        );
        $openId = new api_openId_client_request($config);
    }

    /**
     * @covers api_openId_client_request::authUrl
     */
    public function testGetAuthUrl()
    {
        $expected = 'https://example.com/server?openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0';
        $url = 'https://example.com/server?openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0';

        $openIdMock = $this->getLightOpenIdFixture();
        $openIdMock
            ->expects($this->once())
            ->method('authUrl')
            ->will($this->returnValue($url));

        $openId = new api_openId_client_requestProxy($this->getConfiguration());
        $openId->openId = $openIdMock;
        $this->assertEquals($expected, $openId->authUrl());
    }

    /**
     * @covers api_openId_client_request::setReturnPath
     */
    public function testSetReturnPath() {
        $openIdMock = $this->getLightOpenIdFixture();
        $openIdMock
            ->expects($this->once())
            ->method('__get')
            ->with($this->equalTo('trustRoot'))
            ->will($this->returnValue('http://example.org'));

        $openId = new api_openId_client_requestProxy($this->getConfiguration());
        $openId->openId = $openIdMock;
        $openId->setReturnPath('/Tux');
        $openIdObject = $this->readAttribute($openId, 'openId');
        $this->assertEquals('http://example.org/Tux', $openIdObject->returnUrl);
    }

    /**
     * @expectedException OpenIdErrorException
     * @covers api_openId_client_request::authUrl
     */
    public function testGetAuthUrlExpectingOpenIdErrorException()
    {
        $expected = 'https://example.com/server?openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0';
        $url = 'https://example.com/server?openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0';

        $openIdMock = $this->getLightOpenIdFixture();
        $openIdMock
            ->expects($this->once())
            ->method('authUrl')
            ->will($this->throwException(new ErrorException()));

        $openId = new api_openId_client_requestProxy($this->getConfiguration());
        $openId->openId = $openIdMock;
        $openId->authUrl();
    }

    /**
     * @covers api_openId_client_request::setTrustRoot
     */
    public function testSetTrustRoot() {
        $openIdMock = $this->getLightOpenIdFixture();
        $openIdMock
            ->expects($this->once())
            ->method('__set')
            ->with(
                $this->equalTo('trustRoot'),
                $this->equalTo('Beastie')
            );

        $openId = new api_openId_client_requestProxy($this->getConfiguration());
        $openId->openId = $openIdMock;
        $openId->setTrustRoot('Beastie');
    }

    /**
     * @covers api_openId_client_request::getTrustRoot
     */
    public function testGetTrustRoot() {
        $openIdMock = $this->getLightOpenIdFixture();
        $openIdMock
            ->expects($this->once())
            ->method('__get')
            ->with($this->equalTo('trustRoot'))
            ->will($this->returnValue('Beastie'));

        $openId = new api_openId_client_requestProxy($this->getConfiguration());
        $openId->openId = $openIdMock;
        $this->assertEquals('Beastie', $openId->getTrustRoot());
    }

    /**
     * @covers api_openId_client_request::setRealm
     */
    public function testSetRealm() {
        $openIdMock = $this->getLightOpenIdFixture();
        $openIdMock
            ->expects($this->once())
            ->method('__set')
            ->with(
                $this->equalTo('trustRoot'),
                $this->equalTo('Beastie')
            );

        $openId = new api_openId_client_requestProxy($this->getConfiguration());
        $openId->openId = $openIdMock;
        $openId->setRealm('Beastie');
    }

    /**
     * @covers api_openId_client_request::getRealm
     */
    public function testGetRealm() {
        $openIdMock = $this->getLightOpenIdFixture();
        $openIdMock
            ->expects($this->once())
            ->method('__get')
            ->with($this->equalTo('trustRoot'))
            ->will($this->returnValue('Beastie'));

        $openId = new api_openId_client_requestProxy($this->getConfiguration());
        $openId->openId = $openIdMock;
        $this->assertEquals('Beastie', $openId->getRealm());
    }

    /**
     * @covers api_openId_client_request::setIdentity
     */
    public function testSetIdentity() {
        $openIdMock = $this->getLightOpenIdFixture();
        $openIdMock
            ->expects($this->once())
            ->method('__set')
            ->with(
                $this->equalTo('identity'),
                $this->equalTo('Beastie')
            );

        $openId = new api_openId_client_requestProxy($this->getConfiguration());
        $openId->openId = $openIdMock;
        $openId->setIdentity('Beastie');
    }

    /**
     * @covers api_openId_client_request::getIdentity
     */
    public function testGetIdentity() {
        $openIdMock = $this->getLightOpenIdFixture();
        $openIdMock
            ->expects($this->once())
            ->method('__get')
            ->with($this->equalTo('identity'))
            ->will($this->returnValue('https://dev.m-connect.ch/member/'));

        $openId = new api_openId_client_requestProxy($this->getConfiguration());
        $openId->openId = $openIdMock;
        $this->assertEquals('https://dev.m-connect.ch/member/', $openId->getIdentity());
    }

    /**
     * @covers api_openId_client_request::hostExists
     */
    public function testHostExists() {
        $openIdMock = $this->getLightOpenIdFixture();
        $openIdMock
            ->expects($this->once())
            ->method('hostExists')
            ->will($this->returnValue(true));

        $openId = new api_openId_client_requestProxy($this->getConfiguration());
        $openId->openId = $openIdMock;

        $this->assertTrue($openId->hostExists('Puffy'));
    }

    /**
     * @expectedException OpenIdInvalidAxAttributeTypeException
     * @covers api_openId_client_request::addAttribute
     */
    public function testAddAttributeExpectingOpenIdInvalidAxAttributeTypeException() {
        $openid = new api_openId_client_requestProxy($this->getConfiguration());
        $openid->addAttribute('Tux', 'invalidAttributeType');
    }

    /**
     * @covers api_openId_client_request::getOpenIdObject
     */
    public function testGetOpenIdObjectFromCache() {
        $openid = new api_openId_client_requestProxy($this->getConfiguration());
        $openid->openId = new stdClass;
        $this->assertInstanceOf('stdClass', $openid->getOpenIdObject());
    }

    /*************************************************************************/
    /* Dataprovider                                                          */
    /*************************************************************************/

}

class api_openId_client_requestProxy extends api_openId_client_request {

    public $openId;

    public function addAttribute($attribute, $type) {
        return parent::addAttribute($attribute, $type);
    }
    public function getOpenIdObject() {
        return parent::getOpenIdObject();
    }
}