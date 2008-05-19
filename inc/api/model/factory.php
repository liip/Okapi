<?php
/**
 * This factory is used to centralize the api_model calls and make them easily
 * replaceable with dummies for the unit and functional testing.
 * It should be used in the commands like that:
 * api_modeL::factory('backend_get', array(...));
 * The result will be an object of api_model with the given named params.
 */
class api_model_factory {
    /**
     * Model Factory
     *
     * @param $name string: Model name.
     * @param $params array: Parameters in order of their appearance in the constructor.
     * @return api_model_common
     */
    public static function get($name, $params = array()) {
        $name = 'api_model_' . $name;
        if (count($params) == 0) {
            return new $name;
        } else {
            $class = new ReflectionClass($name);
            return $class->newInstanceArgs($params);
        }

    }
}