<?php
/* Licensed under the Apache License, Version 2.0
 * See the LICENSE and NOTICE file for further information
 */

/**
 * config values:
 *   bool optionalextension : makes the .xx extension optional
 *   bool ssl : forces ssl on that route
 */
class api_routing_route extends sfRoute implements ArrayAccess, Countable {

    public function getParams() {
        return $this->options;
    }

    public function config($params) {
        $this->options = array_merge($this->options, $params);
        return $this;
    }

    public function setViewParam($param, $value) {
        $this->options['view'][$param] = $value;
    }

    /**
     * merges the parsed parameters from the url into the options array so we
     * can read from one place, shouldn't be called except from api_routing
     *
     * @private
     */
    public function mergeProperties() {
        $this->options = array_merge($this->options, $this->parameters);
    }

    /**
     * Returns a deep copy of this route. Can be used when an existing
     * route is to be re-used and modified slightly. Add it to the routing
     * table with api_routing::add().
     */
    public function dup() {
        return clone($this);
    }

    /**
     * ArrayAccess methods
     */
    public function offsetSet($offset, $value) {
        $this->options[$offset] = $value;
    }

    public function offsetExists($offset) {
        return isset($this->options[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->options[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->options[$offset]) ? $this->options[$offset] : null;
    }

    public function count() {
        return count($this->options);
    }
}
