<?php
/* Licensed under the Apache License, Version 2.0
 * See the LICENSE and NOTICE file for further information
 */

/**
 * Helper methods for processing XPath.
 */
class api_helpers_xpath {
    /**
     * Default available namespaces
     */
    protected static $namespaces = array(
        'x' => 'http://www.w3.org/1999/xhtml',
        'xhtml' => 'http://www.w3.org/1999/xhtml',
        'i18n' => 'http://apache.org/cocoon/i18n/2.1'
    );
    
    /**
     * Gets all used namespaces
     */
    public static function getNamespaces() {
        return self::$namespaces;
    }
    
    /**
     * Set namespaces
     * @param $nameSpaces Array: namespaces to be set
     */
    public static function setNamespaces(array $namespaces) {
        self::$namespaces = $namespaces;
    }
    
    /**
     * Gets the first DOM node matching the given XPath expression.
     * @param $dom DOMDocument: DOM to search in
     * @param $xpath string: XPath expression to search in DOM
     */
    public static function getNode(DOMDocument $dom, $xpath) {
        $xp = new DOMXPath($dom);
        foreach (self::$namespaces as $ns => $uri) {
            $xp->registerNamespace($ns, $uri);
        }

        $res = $xp->query($xpath);
        if (!$res || $res->length == 0) {
            return null;
        } else {
            return $res->item(0);
        }
    }

    /**
     * Gets the node value of the first node found with the given XPath.
     * @param $dom DOMDocument: DOM to search in
     * @param $xpath string: XPath expression to search in DOM
     */
    public static function getText($dom, $xpath) {
        $node = self::getNode($dom, $xpath);
        if ($node) {
            $text = $node->nodeValue;
            // Strip multiple whitespaces
            $text = trim(preg_replace("#[ \t\xC2\xA0]+#u", ' ', $text));
            return $text;
        }
        return null;
    }

    /**
     * Gets the node values of all nodes found with the given XPath.
     * @param $dom DOMDocument: DOM to search in
     * @param $xpath string: XPath expression to search in DOM
     */
    public static function getTexts($dom, $xpath) {
        $nodes = self::getNodes($dom, $xpath);

        $ret = array();
        foreach ($nodes as $node) {
            $text = $node->nodeValue;
            // Strip multiple whitespaces
            $text = preg_replace("#[ \t\xC2\xA0]+#u", ' ', $text);
            array_push($ret, $text);
        }
        return $ret;
    }

    /**
     * Gets an attributed specified with the given XPath. The attribute
     * name is specified in the XPath after the last `@' character.
     * @param $dom DOMDocument: DOM to search in
     * @param $xpath string: XPath expression to search in DOM
     */
    public static function getAttribute($dom, $xpath) {
        $attribute = substr($xpath, strrpos($xpath, '@') + 1);
        $xpath = substr($xpath, 0, strrpos($xpath, '@'));

        $node = self::getNode($dom, $xpath);
        if ($node) {
            $text = $node->getAttribute($attribute);
            // Strip multiple whitespaces
            $text = preg_replace("#[ \t]+#", ' ', $text);
            return $text;
        }
        return null;
    }

    /**
     * Gets an array which contains all matching nodes.
     * @param $dom DOMDocument: DOM to search in
     * @param $xpath string: XPath expression to search in DOM
     *
     * @return array    containing all matching nodes
     */
    public static function getNodes($dom, $xpath) {
        $xp = new DOMXPath($dom);
        foreach (self::$namespaces as $ns => $uri) {
            $xp->registerNamespace($ns, $uri);
        }

        $ret = array();
        $nodes = $xp->query($xpath);
        foreach ($nodes as $node) {
            array_push($ret, $node);
        }
        return $ret;
    }

    /**
     * Gets an array of attributes specified with the given XPath.
     * The attribute name is specified in the XPath after the last
     * `@' character.
     * @param $dom DOMDocument: DOM to search in
     * @param $xpath string: XPath expression to search in DOM
     *
     * @return array    all matching attributes
     */
    public static function getAttributes($dom, $xpath) {
        $attribute = substr($xpath, strrpos($xpath, '@') + 1);
        $xpath = substr($xpath, 0, strrpos($xpath, '@'));

        $ret = array();

        $nodes = self::getNodes($dom, $xpath);
        foreach ($nodes as $node) {
            $text = $node->getAttribute($attribute);
            // Strip multiple whitespaces
            $text = preg_replace("#[ \t]+#", ' ', $text);

            array_push($ret, $text);
        }
        return $ret;
    }
}
