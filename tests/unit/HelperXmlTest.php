<?php
/**
 * Tests the api_helpers_xml class.
 */
class HelperXmlTest extends OkapiTestCase {
    /**
     * Tests api_helpers_class::array2dom() method.
     */
    function testArray2Dom() {
        $array = array(
            'name' => 'tester',
            'address' => array(
                'street' => 'example street',
                'house'  => 22,
                'po'     => null,
            ),
            'interested' => false,
            'alive'      => true,
            'html'       => '<strong>I feel good.</strong>',
        );
        
        $dom = new DOMDocument();
        $dom->loadXML("<doc/>");
        api_helpers_xml::array2dom($array, $dom, $dom->documentElement);
        
        $this->assertXPath($dom, '/doc/name', 'tester');
        $this->assertXPath($dom, '/doc/address/street', 'example street');
        $this->assertXPath($dom, '/doc/address/house', '22');
        $this->assertXPath($dom, '/doc/address/po', '');
        $this->assertXPath($dom, '/doc/interested', 'false');
        $this->assertXPath($dom, '/doc/alive', 'true');
        $this->assertXPath($dom, '/doc/html', '<strong>I feel good.</strong>');
        
        $xp = new DOMXPath($dom);
        $res = $xp->query('/doc/html');
        $this->assertEqual($dom->saveXML($res->item(0)),
            '<html>&lt;strong&gt;I feel good.&lt;/strong&gt;</html>');
    }

    /**
     * Tests api_helpers_class::array2dom() method with some CDATA nodes.
     */
    function testArray2DomWithCDATANodes() {
        $array = array(
            'name' => 'tester',
            'address' => array(
                'street' => 'example street',
                'house'  => 22,
                'po'     => null,
            ),
            'interested' => false,
            'alive'      => true,
            'html'       => '<strong>I feel good.</strong>',
        );
        
        $dom = new DOMDocument();
        $dom->loadXML("<doc/>");
        api_helpers_xml::array2dom($array, $dom, $dom->documentElement,
                array('html'));
        
        $this->assertXPath($dom, '/doc/name', 'tester');
        $this->assertXPath($dom, '/doc/address/street', 'example street');
        $this->assertXPath($dom, '/doc/address/house', '22');
        $this->assertXPath($dom, '/doc/address/po', '');
        $this->assertXPath($dom, '/doc/interested', 'false');
        $this->assertXPath($dom, '/doc/alive', 'true');
        $this->assertXPath($dom, '/doc/html', '<strong>I feel good.</strong>');
        
        $xp = new DOMXPath($dom);
        $res = $xp->query('/doc/html');
        $this->assertEqual($dom->saveXML($res->item(0)),
            '<html><![CDATA[<strong>I feel good.</strong>]]></html>');
    }

    /**
     * Tests api_helpers_class::array2dom() method with a fragment node.
     */
    function testArray2DomWithFragmentNodes() {
        $array = array(
            'alive'      => true,
            'html'       => '<strong>I feel good.</strong>',
        );
        
        $dom = new DOMDocument();
        $dom->loadXML("<doc/>");
        api_helpers_xml::array2dom($array, $dom, $dom->documentElement,
                null, array('html'));
        
        $this->assertXPath($dom, '/doc/alive', 'true');
        $this->assertXPath($dom, '/doc/html/strong', 'I feel good.');
    }
}
?>