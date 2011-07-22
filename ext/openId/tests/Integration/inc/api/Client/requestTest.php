<?php
class api_openId_client_request_IntegrationTest extends openIdTestCase {

    public function setUp() {
        $_SERVER['HTTP_HOST']   = 'localhost';
        $_SERVER['HTTPS']       = 'off';
        $_SERVER['REQUEST_URI'] = '?openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0';
    }

    /*************************************************************************/
    /* Tests                                                                 */
    /*************************************************************************/

    /**
     * @covers api_openId_client_request::addRequiredAttribute
     * @covers api_openId_client_request::addAttribute
     * @covers api_openId_client_request::getOpenIdObject
     */
    public function testAddRequiredAttribute() {
        $openId = new api_openId_client_request($this->getConfiguration());
        $openId->addRequiredAttribute('email');

        $lightopenId = $this->readAttribute($openId, 'openId');
        $this->assertEquals(array('email'), $lightopenId->required);
    }

    /**
     * @covers api_openId_client_request::addOptionalAttribute
     * @covers api_openId_client_request::addAttribute
     */
    public function testAddOptionalAttribute() {
        $openId = new api_openId_client_request($this->getConfiguration());
        $openId->addOptionalAttribute('firstname');

        $lightopenId = $this->readAttribute($openId, 'openId');
        $this->assertEquals(array('firstname'), $lightopenId->optional);
    }

    /**
     * @expectedException OpenIdInvalidArgumentException
     * @covers api_openId_client_request::addAttribute
     */
    public function testAddAttributeExpectingOpenIdInvalidArgumentException() {
        $openId = new api_openId_client_request($this->getConfiguration());
        $openId->addOptionalAttribute(array('firstname'));
    }

    /**
     * @dataProvider setAttributesDataprovider
     * @covers api_openId_client_request::setAttributes
     */
    public function testSetAttributes($expected, $attributes) {
        $openId = new api_openId_client_request($this->getConfiguration());
        $openId->setAttributes($attributes);
        $lightopenid = $this->readAttribute($openId, 'openId');

        $attrs = array();
        $attrs['required'] = $lightopenid->required;
        $attrs['optional'] = $lightopenid->optional;

        $this->assertEquals($expected, $attrs);
    }

    /*************************************************************************/
    /* Dataprovider                                                          */
    /*************************************************************************/

    public static function setAttributesDataprovider() {
        return array(
            'with required attrs' => array(
                array('required' => array('tux'), 'optional' => array()),
                array('required' => array('tux'))
            ),
            'with optional attrs' => array(
                array('optional' => array('tux'), 'required' => array()),
                array('optional' => array('tux'))
            ),
            'with both attrs' => array(
                array('optional' => array('tux'), 'required' => array('beastie')),
                array('optional' => array('tux'), 'required' => array('beastie'))
            ),
        );
    }
}