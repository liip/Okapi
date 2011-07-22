<?php

class api_openid_client_response_UnitTest extends openIdTestCase {

    /*************************************************************************/
    /* Tests                                                                 */
    /*************************************************************************/

    /**
     * @covers api_openId_client_response::__construct
     * @covers api_openId_client_response::setConfiguration
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
        $openid = new api_openId_client_response($config);
        $this->assertAttributeEquals($expected, 'configuration', $openid);
    }

    /**
     * @expectedException OpenIdInvalidArgumentException
     * @covers api_openId_client_response::setConfiguration
     */
    public function testSetConfigurationExpectingInvalidArgumentException()
    {
        $config = array(
            'baseUrl' => 'http://example.com',
        );
        $openId = new api_openId_client_response($config);
    }

    /**
     * @covers api_openId_client_response::getMode
     */
    public function testGetMode() {
        $openIdMock = $this->getLightOpenIdFixture();
        $openIdMock
            ->expects($this->once())
            ->method('__get')
            ->will($this->returnValue('Tux'));

        $openId = new api_openId_client_responseProxy($this->getConfiguration());
        $openId->openId = $openIdMock;
        $this->assertEquals('Tux', $openId->getMode());
    }

    /**
     * @covers api_openId_client_response::validate
     */
    public function testValidate()
    {
        $openIdMock = $this->getLightOpenIdFixture();
        $openIdMock
            ->expects($this->once())
            ->method('Validate')
            ->will($this->returnValue(true));

        $openId = new api_openId_client_responseProxy($this->getConfiguration());
        $openId->openId = $openIdMock;

        $this->assertTrue($openId->Validate());
    }

    /**
     * @expectedException OpenIdErrorException
     * @covers api_openId_client_response::Validate
     */
    public function testValidateexpectingOpenIdErrorException() {
        $openIdMock = $this->getLightOpenIdFixture();
        $openIdMock
            ->expects($this->once())
            ->method('Validate')
            ->will($this->throwException(new ErrorException()));

        $openId = new api_openId_client_responseProxy($this->getConfiguration());
        $openId->openId = $openIdMock;

        $this->assertTrue($openId->Validate());
    }

    /**
     * @covers api_openId_client_response::getAttributes
     */
    public function testGetAttrbutes()
    {
        $attributes = array(
            '.net/contact/phone/business' => 'telCompany',
        );

        $openIdMock = $this->getLightOpenIdFixture();
        $openIdMock
            ->expects($this->once())
            ->method('getAttributes')
            ->will($this->returnValue($attributes));

        $openId = new api_openId_client_responseProxy($this->getConfiguration());
        $openId->openId = $openIdMock;
        $this->assertContains('telCompany', $openId->getAttributes());
    }

    /**
     * @covers api_openId_client_response::normalizeAttributeNames
     */
    public function testNormalizeAttributeNames() {
        $axAttributesMap = array(
            '.net/metadata' => 'userName',
            '.net/namePerson/first' => 'firstName',
            '.net/namePerson/last' => 'lastName',
            '.net/namePerson' => 'fullName',
            '.net/birthdate' => 'birthDate',
            '.net/person/gender' => 'profile_user_sex',
            '.net/perf/language' => 'language',
        );
        $attributes = array(
            '.net/metadata'        => 'Tux',
            '.net/namePerson/last' => 'Linus',
            '.net/person/gender'   => 'male',
        );

        $openid = new api_openId_client_response($this->getConfiguration());
        $this->assertEquals(
            array(
                'userName'         => 'Tux',
                'lastName'         => 'Linus',
                'profile_user_sex' => 'male'
            ),
            $openid->normalizeAttributeNames($attributes, $axAttributesMap)
        );
    }

    /**
     * @covers api_openId_client_response::getOpenIdObject
     */
    public function testGetOpenIdObjectFromCache() {
        $openid = new api_openId_client_responseProxy($this->getConfiguration());
        $openid->openId = new stdClass;
        $this->assertInstanceOf('stdClass', $openid->getOpenIdObject());
    }

    /**
     * @covers api_openId_client_response::cancelled
     */
    public function testCancelled() {
        $response = $this->getMockBuilder('api_response')
            ->disableOriginalConstructor()
            ->setMethods(array('redirect'))
            ->getMock();
        $response
            ->expects($this->once())
            ->method('redirect')
            ->with(
                $this->equalTo('http://example.org'),
                $this->equalTo('304')
            );

        $openid = new api_openId_client_response($this->getConfiguration());
        $openid->cancelled('http://example.org', $response);
    }
}


class api_openId_client_responseProxy extends api_openId_client_response {

    public $openId;

    public function getOpenIdObject() {
        return parent::getOpenIdObject();
    }
}