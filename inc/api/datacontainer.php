<?php
/* Licensed under the Apache License, Version 2.0
 * See the LICENSE and NOTICE file for further information
 */

class api_datacontainer implements ArrayAccess {
    protected $data = array();

    /**
     * returns an array containing all the data of this node, and converts all
     * sub-nodes into arrays on the fly
     *
     * @return array all data
     */
    public function toArray() {
        $array = array();
        foreach ($this->data as $idx=>$item) {
            if ($item instanceof api_datacontainer) {
                $array[$idx] = $item->toArray();
            } else {
                $array[$idx] = $item;
            }
        }
        return $array;
    }

    /**
     * implementation of the magic get/setters
     *
     * the get function returns a new api_datacontainer by default so that you can
     * use it like $container->foo->bar = "baz"; and foo will automatically be
     * an api_datacontainer instance within $container
     */
    public function __get($name) {
        if (!isset($this->data[$name])) {
            $this->data[$name] = new api_datacontainer;
        }
        return $this->data[$name];
    }

    public function __isset($name) {
        return isset($this->data[$name]);
    }

    public function __set($name, $value) {
        $this->data[$name] = $value;
    }

    public function __unset($name) {
        unset($this->data[$name]);
    }

    /**
     * implementation of the ArrayAccess interface
     *
     * the get function returns a new api_datacontainer by default so that you can
     * use it like $container->foo->bar = "baz"; and foo will automatically be
     * an api_datacontainer instance within $container
     */
    public function offsetGet($name) {
        if (!isset($this->data[$name])) {
            $this->data[$name] = new api_datacontainer;
        }
        return $this->data[$name];
    }

    public function offsetExists($name) {
        return isset($this->data[$name]);
    }

    public function offsetSet($name, $value) {
        $this->data[$name] = $value;
    }

    public function offsetUnset($name) {
        unset($this->data[$name]);
    }
}
