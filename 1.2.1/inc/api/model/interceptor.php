<?php
/**
 * Interface for api_model_factory interceptors
 */
 interface api_model_interceptor {
     
     /**
      * Used by the interceptor to instruct the factory
      * whether it wants to intercept the instantiation
      * of the object
      * @param string classname the factory should create
      * @param boolean true if the interceptor wants to intercept
      */
     public function intercepts($classname);
     
     /**
      * Used by the interceptor to create and return an object from the factory.
      * Called only if the intercepts method returns true. Has the same signature
      * as api_model_factory::get()
      * @param $name string: Model name.
      * @param $params array: Parameters in order of their appearance in the constructor.
      * @param $namespace string: Namespace, default "api"
      * @return api_model_common
      */
     public function get($name, $params = array(), $namespace = 'api');
 }
 