<?php
/**
 * Tests the api_routing class which stores routing configuration and
 * allows queries on the configured routes.
 */
class RoutingTest extends UnitTestCase {
    function setUp() {
        // Remove all existing routes
        $m = new api_routing();
        $m->clear();
    }
    
    /**
     * Root URL goes to api_commands_index command.
     */
    function testEmpty() {
        $m = new api_routing();
        $m->route('/')->params(array('command' => 'index'));
        
        $request = new mock_request(array('path' => '/'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'index',
            'method' => 'process', 'view' => array()));
    }

    /**
     * Mapping with one request param.
     */
    function testWithOneRequestParam() {
        $m = new api_routing();
        $m->route('/test/:param1')->params(array('command' => 'test'));
        
        $request = new mock_request(array('path' => '/test/abc'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'test',
            'method' => 'process', 'param1' => 'abc',
            'view' => array()));
    }
    
    /**
     * Generic mapping plus a specific one.
     */
    function testGenericMapping() {
        $m = new api_routing();
        $m->route('/user/:method/:id')->params(array('command' => 'user'));
        $m->route('/:command/:method/:id');
        
        $request = new mock_request(array('path' => '/user/save/3'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'user',
            'method' => 'save', 'id' => '3', 'view' => array()));
        
        $request = new mock_request(array('path' => '/list/get/7'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'list',
            'method' => 'get', 'id' => '7', 'view' => array()));
    }

    /**
     * Mapping with one request param but wrong URI.
     */
    function testWithOneRequestParamNoMatch() {
        $m = new api_routing();
        $m->params(array('command' => 'test'))->route('/test/:param1');
        
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
        $m->route('/:user/:command/test/def/:foo/:bar/superuser')
          ->params(array('command' => 'test'));
        
        $request = new mock_request(array('path' => '/pneff/index/test/def/myfoo/something/superuser'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'index',
            'method' => 'process', 'user' => 'pneff', 'foo' => 'myfoo',
            'bar' => 'something', 'view' => array()));
    }
    
    /**
     * 
     */
    function testWithMultipleRequestParamNoMatch() {
        $m = new api_routing();
        $m->route('/:user/:command/test/def/:foo/:bar/superuser')
          ->params(array('command' => 'test'));
        
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
        $m->route('/test/:param1')->params(array('command' => 'test'));
        
        $request = new mock_request(array('path' => '/test/abc'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'test',
            'method' => 'process', 'param1' => 'abc',
            'view' => array()));
        
        $m = new api_routing();
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'test',
            'method' => 'process', 'param1' => 'abc',
            'view' => array()));
    }
    
    /**
     * Add the HTTP verb as a requisite to the route.
     */
    function testVerbDefaultGET() {
        $m = new api_routing();
        $m->route('/test/:param1')
          ->params(array('command' => 'test'))
          ->when(array('verb' => 'GET'));
        
        $request = new mock_request(array('path' => '/test/abc'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'test',
            'method' => 'process', 'param1' => 'abc',
            'view' => array()));
    }
    
    /**
     * Add the HTTP verb as a requisite to the route.
     */
    function testVerbExplicitGET() {
        $m = new api_routing();
        $m->route('/test/:param1')
          ->params(array('command' => 'test'))
          ->when(array('verb' => 'GET'));
        
        $request = new mock_request(array('path' => '/test/abc', 'verb' => 'GET'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'test',
            'method' => 'process', 'param1' => 'abc',
            'view' => array()));
    }
    
    /**
     * Asserts that the route does not match if the verb is different.
     */
    function testVerbExplicitGETNoMatch() {
        $m = new api_routing();
        $m->route('/test/:param1')
          ->params(array('command' => 'test'))
          ->when(array('verb' => 'GET'));
        
        $request = new mock_request(array('path' => '/test/abc', 'verb' => 'POST'));
        $route = $m->getRoute($request);
        $this->assertNull($route);
    }
    
    /**
     * Groups of routes allow for common conditions for all the given
     * routes.
     */
    function testGrouping() {
        $m = new api_routing();
        $g = new api_routing_route();
        $g = $g->params(array('command' => 'test',
                              'view' => array('xsl' => 'test.xsl')))
               ->when(array('verb' => 'GET'));
        
        $m->add($g->dup()->route('/test/:param1'));       // Uses defaults
        $m->add($g->dup()
                  ->route('/users/:uid')
                  ->params(array('command' => 'user')));
        
        $request = new mock_request(array('path' => '/test/abc', 'verb' => 'POST'));
        $route = $m->getRoute($request);
        $this->assertNull($route);
        
        $request = new mock_request(array('path' => '/test/abc', 'verb' => 'GET'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'test',
            'method' => 'process', 'param1' => 'abc',
            'view' => array('xsl' => 'test.xsl')));
        
        $request = new mock_request(array('path' => '/users/userid', 'verb' => 'POST'));
        $route = $m->getRoute($request);
        $this->assertNull($route);
        
        $request = new mock_request(array('path' => '/users/userid', 'verb' => 'GET'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'user',
            'method' => 'process', 'uid' => 'userid',
            'view' => array('xsl' => 'test.xsl')));
    }
}
?>
