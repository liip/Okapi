<?php
//include_once('api/model/factory.php');
//class ModelInterceptorTest extends api_testing_case_unit {
//    
//    function testNotIntercepted() {
//        api_model_factory::registerInterceptor(new test_interceptor());
//        $model = api_model_factory::get('array',array(array()));
//        $this->assertEqual(get_class($model), 'api_model_array');
//    }
//    
//    function testIntercepted() {
//        $interceptor = new test_interceptor();
//        $interceptor->intercepts = true;
//        api_model_factory::registerInterceptor($interceptor);
//        $model = api_model_factory::get('array',array(array()));
//        $this->assertEqual(get_class($model), 'test_model');
//    }
//}
//
//class test_interceptor implements api_model_interceptor {
//    
//    public $intercepts = false;
//    
//    public function intercepts($classname) {
//        return $this->intercepts;
//    }
//    
//    public function get($name, $params = array(), $namespace= "api") {
//        return new test_model();
//    }
//}
//
//class test_model extends api_model {}