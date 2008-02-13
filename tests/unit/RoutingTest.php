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
        $m->route('/')->config(array('command' => 'index'));
        
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
        $m->route('/test/:param1')->config(array('command' => 'test'));
        
        $request = new mock_request(array('path' => '/test/abc'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'test',
            'method' => 'process', 'param1' => 'abc',
            'view' => array()));
        
        $request = new mock_request(array('path' => '/test/abc/def'));
        $route = $m->getRoute($request);
        $this->assertNull($route);
    }
    
    /**
     * Generic mapping plus a specific one.
     */
    function testGenericMapping() {
        $m = new api_routing();
        $m->route('/user/:method/:id')->config(array('command' => 'user'));
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
        $m->config(array('command' => 'test'))->route('/test/:param1');
        
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
          ->config(array('command' => 'test'));
        
        $request = new mock_request(array('path' => '/pneff/index/test/def/myfoo/something/superuser'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'index',
            'method' => 'process', 'user' => 'pneff', 'foo' => 'myfoo',
            'bar' => 'something', 'view' => array()));
    }
    
    function testWithMultipleRequestParamNoMatch() {
        $m = new api_routing();
        $m->route('/:user/:command/test/def/:foo/:bar/superuser')
          ->config(array('command' => 'test'));
        
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
        $m->route('/test/:param1')->config(array('command' => 'test'));
        
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
          ->config(array('command' => 'test'))
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
          ->config(array('command' => 'test'))
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
          ->config(array('command' => 'test'))
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
        $g = $g->config(array('command' => 'test',
                              'view' => array('xsl' => 'test.xsl')))
               ->when(array('verb' => 'GET'));
        
        $m->add($g->dup()->route('/test/:param1'));       // Uses defaults
        $m->add($g->dup()
                  ->route('/users/:uid')
                  ->config(array('command' => 'user')));
        
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
    
    /**
     * Final slash at the end should be ignored.
     */
    function testAdditionalSlash() {
        $m = new api_routing();
        $m->route('/test/:param1')
          ->config(array('command' => 'test'));
        
        // Empty param
        $request = new mock_request(array('path' => '/test/'));
        $route = $m->getRoute($request);
        $this->assertNull($route);
        
        // extra slash
        $request = new mock_request(array('path' => '/test/myparam/'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'test',
            'method' => 'process', 'param1' => 'myparam',
            'view' => array()));
    }
    
    /**
     * Double slash leads to an empty param and is thus not allowed
     * in the URL.
     */
    function testDoubleSlash() {
        $m = new api_routing();
        $m->route('/test/:param1/:param2')
          ->config(array('command' => 'test'));
        
        // Empty param
        $request = new mock_request(array('path' => '/test//b'));
        $route = $m->getRoute($request);
        $this->assertNull($route);
    }
    
    /**
     * The last param can be a wildcard parameter which will
     * eat up and return all remaining parameters.
     *
     * This test makes sure it only matches when it should.
     */
    function testWildcardNoMatch() {
        $m = new api_routing();
        $m->route('/test/:userid/*path')
          ->config(array('command' => 'test'));
        
        $request = new mock_request(array('path' => '/test/123'));
        $route = $m->getRoute($request);
        $this->assertNull($route);
    }
    
    /**
     * The last param can be a wildcard parameter which will
     * eat up and return all remaining parameters.
     */
    function testWildcardOneElement() {
        $m = new api_routing();
        $m->route('/test/:userid/*path')
          ->config(array('command' => 'test'));
        
        $request = new mock_request(array('path' => '/test/123/something'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'test',
            'method' => 'process',
            'userid' => '123',
            'path'   => 'something',
            'view' => array()));
    }
    
    /**
     * The last param can be a wildcard parameter which will
     * eat up and return all remaining parameters.
     */
    function testWildcardMultipleElements() {
        $m = new api_routing();
        $m->route('/test/:userid/*path')
          ->config(array('command' => 'test'));
        
        $request = new mock_request(array('path' => '/test/123/foo/bar/go+on'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'test',
            'method' => 'process',
            'userid' => '123',
            'path'   => 'foo/bar/go+on',
            'view' => array()));
    }

    /**
     * Optional parameter if a default value has been given.
     */
    function testOptionalParam() {
        $m = new api_routing();
        $m->route('/test/:param1')->config(array('command' => 'test', 'param1' => 'foo'));
        
        // Param given, should be in route
        $request = new mock_request(array('path' => '/test/bar'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'test',
            'method' => 'process', 'param1' => 'bar',
            'view' => array()));

        // Param not given, default should be used
        $request = new mock_request(array('path' => '/test/'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'test',
            'method' => 'process', 'param1' => 'foo',
            'view' => array()));

        // Param not given and slash missing, default should be used
        $request = new mock_request(array('path' => '/test'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'test',
            'method' => 'process', 'param1' => 'foo',
            'view' => array()));
    }

    /**
     * Optional parameter only work at the end of the URL - verify.
     */
    function testOptionalParamOnlyTrailing() {
        $m = new api_routing();
        $m->route('/test/:param1/abc/:param2')
          ->config(array('command' => 'test', 'param1' => 'foo', 'param2' => 'bar'));
        
        // Two parts missing at the end
        $request = new mock_request(array('path' => '/test/func'));
        $route = $m->getRoute($request);
        $this->assertNull($route);
        
        // param1 left out
        $request = new mock_request(array('path' => '/test/abc/func'));
        $route = $m->getRoute($request);
        $this->assertNull($route);
    }
}
?>
