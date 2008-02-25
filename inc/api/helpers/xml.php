<?php
/**
 * Static helper methods for XML handling.
 */
class api_helpers_xml {
    /**
     * Converts an array to an XML DOM structure.
     *
     * Example usage:
     * <pre>
     *     $array = array('foo' => 'bar');
     *     $dom = new DOMDocument();
     *     $dom->loadXML("<doc/>");
     *     api_helpers_xml::array2dom($array, $dom, $dom->documentElement);
     * </pre>
     *
     * This will result in the XML document
     * <pre>
     *     \<doc>\<foo>bar\</foo>\</doc>
     * </pre>
     *
     * @param $array array: The array to convert.
     * @param $domdoc DOMDocument: The document into which to insert the
     *        result. The document must already have been created in memory.
     * @param $domnode DOMNode: The element in $domdoc to which the result
     *        is added.
     * @param $cdataNodes array: An array of node names. Every node with
     *        those names is added as a CDATA section to the document.
     * @param $fragmNodes array: An array of node names. Every node with
     *        those names is added verbatim as XML document fragments to the
     *        document.
     * @return void
     */
    public static function array2dom($array, &$domdoc, &$domnode, $cdataNodes = array(), $fragmNodes = array()) {
        if (! is_array($array)) {
            return;
        }

        foreach ($array as $n => $node) {
            $v = $n;
            $n = (is_numeric($n)) ? "entry":$n;

            $elem = $domdoc->createElement($n);
            if ($elem instanceof DOMNode) {
                if ($n === "entry") {
                    $elem->setAttribute('key', $v);
                }

                if (is_array($node)) {
                    self::array2dom($node, $domdoc, $elem, $cdataNodes, $fragmNodes);
                } else {
                    if (is_array($cdataNodes) && in_array($elem->nodeName, $cdataNodes)) {

                        $nodeObj = $domdoc->createCDATASection($node);

                    } else if (is_array($fragmNodes)  && in_array($elem->nodeName, $fragmNodes)) {

                        if (!empty($node)) {
                            $nodeObj = $domdoc->createDocumentFragment();
                            $nodeObj->appendXML($node);
                        } else {
                            $nodeObj = $domdoc->createTextNode("");
                        }

                    }  else if ($node instanceof DOMDocument){
                        $nodeObj = $domdoc->importNode($node->documentElement, true);
                        
                    } else if (is_bool($node)) {
                        $node = $node ? 'true' : 'false';
                        $nodeObj = $domdoc->createTextNode($node);
                        
                    } else if (!is_object($node)) {
                        $nodeObj = $domdoc->createTextNode($node);
                    }

                    if (isset($nodeObj) && $nodeObj instanceof DOMNode) {
                        $elem->appendChild($nodeObj);
                    }

                }

                if ($domnode !== NULL) {
                    $domnode->appendChild($elem);
                } else {
                    $domdoc->appendChild($elem);
                }
            }
        }
    }
}
?>