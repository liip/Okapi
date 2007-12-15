<?php
/**
 * Tests the api_routing class which stores routing configuration and
 * allows queries on the configured routes.
 */
class RoutingTest extends UnitTestCase {
    /**
     * Root URL goes to api_commands_index command.
     */
    function testEmpty() {
        $m = new api_routing();
        $m->add('/', array('controller' => 'index'));
        
        $request = new mock_request(array('path' => '/'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('controller' => 'index',
            'method' => 'process'));
    }

    /**
     * Mapping with one request param.
     */
    function testWithOneRequestParam() {
        $m = new api_routing();
        $m->add('/test/:param1', array('controller' => 'test'));
        
        $request = new mock_request(array('path' => '/test/abc'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('controller' => 'test',
            'method' => 'process', 'param1' => 'abc'));
    }

    /**
     * Mapping with one request param but wrong URI.
     */
    function testWithOneRequestParamNoMatch() {
        $m = new api_routing();
        $m->add('/test/:param1', array('controller' => 'test'));
        
        $request = new mock_request(array('path' => '/test/'));
        $route = $m->getRoute($request);
        $this->assertNull($route);

        $request = new mock_request(array('path' => '/mytest/abc'));
        $route = $m->getRoute($request);
        $this->assertNull($route);
    }
    
    /**
     * Mapping with several request params and URL components.
     */
    function testWithMultipleRequestParam() {
        $m = new api_routing();
        $m->add('/:user/:controller/test/def/:foo/:bar/superuser', array('controller' => 'test'));
        
        $request = new mock_request(array('path' => '/pneff/index/test/def/myfoo/something/superuser'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('controller' => 'index',
            'method' => 'process', 'user' => 'pneff', 'foo' => 'myfoo',
            'bar' => 'something'));
    }
    
    /**
     * 
     */
    function testWithMultipleRequestParamNoMatch() {
        $m = new api_routing();
        $m->add('/:user/:controller/test/def/:foo/:bar/superuser', array('controller' => 'test'));
        
        $request = new mock_request(array('path' => '/pneff/index/test/def/myfoo/something'));
        $route = $m->getRoute($request);
        $this->assertNull($route);
        
        $request = new mock_request(array('path' => '/index/test/def/myfoo/something/superuser'));
        $route = $m->getRoute($request);
        $this->assertNull($route);
    }

    /**
     * Routes should be preserved between two different routing
     * objects.
     */
    function testPreserveRoutes() {
        $m = new api_routing();
        $m->add('/test/:param1', array('controller' => 'test'));
        
        $request = new mock_request(array('path' => '/test/abc'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('controller' => 'test',
            'method' => 'process', 'param1' => 'abc'));
        
        $m = new api_routing();
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('controller' => 'test',
            'method' => 'process', 'param1' => 'abc'));
    }
    
    /**
     * Add the HTTP verb as a requisite to the route.
     */
    function testVerbDefaultGET() {
        $m = new api_routing();
        $m->add('/test/:param1', array('controller' => 'test',
                                       '#conditions' => array('verb' => 'GET')));
        
        $request = new mock_request(array('path' => '/test/abc'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('controller' => 'test',
            'method' => 'process', 'param1' => 'abc'));
    }
    
    /**
     * Add the HTTP verb as a requisite to the route.
     */
    function testVerbExplicitGET() {
        $m = new api_routing();
        $m->add('/test/:param1', array('controller' => 'test',
                                       '#conditions' => array('verb' => 'GET')));
        
        $request = new mock_request(array('path' => '/test/abc', 'verb' => 'GET'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('controller' => 'test',
            'method' => 'process', 'param1' => 'abc'));
    }
    
    /**
     * Asserts that the route does not match if the verb is different.
     */
    function testVerbExplicitGETNoMatch() {
        $m = new api_routing();
        $m->add('/test/:param1', array('controller' => 'test',
                                       '#conditions' => array('verb' => 'GET')));
        
        $request = new mock_request(array('path' => '/test/abc', 'verb' => 'POST'));
        $route = $m->getRoute($request);
        $this->assertNull($route);
    }
}
?>
