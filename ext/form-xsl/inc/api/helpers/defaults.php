<?php

/**
 * Basically this class helps you to provide a form with default values and
 * override those values with the params from the request.
 * 
 * $defaults = new api_helpers_defaults($this->request, array('foo' => 'Hello world', 'bar' => 'Test'));
 * $this->data[] = $defaults;
 * 
 * will result with the request /?bar=Nomore in the following xml
 * 
 * <form_values>
 *     <foo>Hello world</foo>
 *     <bar>Nomore</bar>
 * </form_values>
 * 
 * @author fabian
 */
class api_helpers_defaults extends api_model {

    protected $values = array();

    public function __construct(api_request $request, $defaults) {

        foreach ($defaults as $name => $default) {
            $this->values[$name] = $request->getParam($name, $default);
        }
    }

    public function __get($name){
        return isset($this->values[$name]) ? $this->values[$name] : '';
    }

    public function __set($name, $value) {
        $this->values[$name] = $value;
    }

    public function filter(array $keys, array $add = array()) {

        $filtered = array();

        foreach ($keys as $key => $value) {
            if (is_string($key)) {
                if (isset($this->values[$value])) {
                    $filtered[$key] = $this->values[$value];
                }
            } elseif (isset($this->values[$value])) {
                $filtered[$value] = $this->values[$value];
            }
        }

        return array_merge($filtered, $add);
    }

    public function getDOM() {

        $dom = new DOMDocument();
        $root = $dom->createElement('form_values');

        foreach ($this->values as $name => $value) {
            $node = $dom->createElement($name);
            if (is_array($value)) {
                api_helpers_xml::array2dom($value, $dom, $node);
            } else {
                $node->appendChild($dom->createTextNode($value));
            }
            $root->appendChild($node);
        }

        $dom->appendChild($root);

        // Return that!
        return $dom;
    }
}
