<?php
/* Licensed under the Apache License, Version 2.0
 * See the LICENSE and NOTICE file for further information
 */

/**
 * Array model object. Represents an array and returns an XML DOM
 * for that array in getDOM().
 *
 * @author   Patrice Neff
 */
class api_model_array extends api_model {
    /** string: Name of the root node to be set. */
    protected $root = '';

    /** array: Array which is represented in this object. */
    protected $array = null;

    /**
     * Create a new data object which returns an array as DOM.
     *
     * @param $string string: The text to map to the XML DOM.
     * @param $type string: Names the type of the string to be create a dom of.
     * @param $root string: Root node tag name.
     */
    public function __construct($string, $type = 'string', $root = 'response') {
        $this->string = $string;
        $this->root = $root;
    }

    public function getDOM() {
        if (!empty($root)) {
            $string = sprintf('<%1$s>%2$s</%1$s>', $root, $string);
        }

        $dom = new DOMDocument();
        $dom->loadXML($string);
        $dom->documentElement->setAttribute('type', 'string');
        return $dom;
    }

    public function getData() {
        return $this->string;
    }
}
