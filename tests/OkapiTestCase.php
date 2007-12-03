<?php
/**
 * Base test case class.
 */
class OkapiTestCase extends UnitTestCase {
    protected function assertXPath($dom, $xpath, $value, $count = 1) {
        $xp = new DOMXPath($dom);
        $res = $xp->query($xpath);
        
        $this->assertEqual($count, $res->length, "Expected $count results, but got " . $res->length);
        if ($res->length == 0) {
            return;
        }
        
        $this->assertEqual($value, $res->item(0)->nodeValue);
    }
}
?>