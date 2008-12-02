<?php
/**
 * Tests the api_helpers_xml class.
 */
class HelperXmlTest extends api_testing_case_unit {
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
        
        $this->assertText($dom, '/doc/name', 'tester');
        $this->assertText($dom, '/doc/address/street', 'example street');
        $this->assertText($dom, '/doc/address/house', '22');
        $this->assertText($dom, '/doc/address/po', '');
        $this->assertText($dom, '/doc/interested', 'false');
        $this->assertText($dom, '/doc/alive', 'true');
        $this->assertText($dom, '/doc/html', '<strong>I feel good.</strong>');
        
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
        
        $this->assertText($dom, '/doc/name', 'tester');
        $this->assertText($dom, '/doc/address/street', 'example street');
        $this->assertText($dom, '/doc/address/house', '22');
        $this->assertText($dom, '/doc/address/po', '');
        $this->assertText($dom, '/doc/interested', 'false');
        $this->assertText($dom, '/doc/alive', 'true');
        $this->assertText($dom, '/doc/html', '<strong>I feel good.</strong>');
        
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
        
        $this->assertText($dom, '/doc/alive', 'true');
        $this->assertText($dom, '/doc/html/strong', 'I feel good.');
    }
}
?>