<?php
class api_openId_client_response_IntegrationTest extends openIdTestCase {

    public function setUp() {
        $_SERVER['HTTP_HOST']   = 'localhost';
        $_SERVER['HTTPS']       = 'off';
        $_SERVER['REQUEST_URI'] = '?openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0';
    }

    /*************************************************************************/
    /* Tests                                                                 */
    /*************************************************************************/

    /**
     * @covers api_openId_client_response::getOpenIdObject
     */
    public function testGetOpenIdObject() {
        $openid = new api_openid_client_response($this->getConfiguration());
        $openid->getMode();
        $this->assertAttributeInstanceOf('LightOpenID', 'openId', $openid);
    }
}