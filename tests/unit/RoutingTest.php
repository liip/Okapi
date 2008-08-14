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
     * Mapping with one request param which is '0'.
     */
    function testWithOneRequestParamZero() {
        $m = new api_routing();
        $m->route('/test/:param1')->config(array('command' => 'test'));
        
        $request = new mock_request(array('path' => '/test/0'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'test',
            'method' => 'process', 'param1' => '0',
            'view' => array()));
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
        $m->route('/test/:userid/+path')
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
     * Test the plus wildcard, ensuring it allows a value of '0'.
     */
    function testWildcardOneElementZero() {
        $m = new api_routing();
        $m->route('/test/:userid/+data')
          ->config(array('command' => 'test'));
        
        $request = new mock_request(array('path' => '/test/123/0'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'test',
            'method' => 'process',
            'userid' => '123',
            'data'   => '0',
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
    
    
    /**
     * An URL can have multiple Slashes at the beginning or end,
     * they will be normalized to just one
     */
    function testMultipleSlashes() {
        $m = new api_routing();
        $m->route('/test/')->config(array('command' => 'test'));;
        
        // double slashes at start
        $request = new mock_request(array('path' => '//test/'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'test',
            'method' => 'process', 'view' => array()));
        
        //double slashes at end
        $request = new mock_request(array('path' => '/test//'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'test',
            'method' => 'process', 'view' => array()));
        
        // triple slashes at start
        $request = new mock_request(array('path' => '///test/'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'test',
            'method' => 'process', 'view' => array()));
        
        //triple slashes at end
        $request = new mock_request(array('path' => '/test///'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'test',
            'method' => 'process', 'view' => array()));
    }
    
     /**
     * An URL can have multiple Slashes in the middle,
     * they will be normalized to just one
     */   
    
    function testMultipleSlashesBetween() {
        $m = new api_routing();
        $m->route('/test/foo/')->config(array('command' => 'test'));;
        
        // double slashes 
        $request = new mock_request(array('path' => '/test//foo/'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'test',
            'method' => 'process', 'view' => array()));

        // triple slashes 
        $request = new mock_request(array('path' => '/test///foo/'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'test',
            'method' => 'process', 'view' => array()));
        
    }
    
     /**
      * The trailing slash can be omitted, but a route with
      * a trailing slash should still match
      *
      * Tests without default parameter
      */
     
     function testNoTrailingSlashNoParam() {
        $m = new api_routing();
        $m->route('/bar/baz/')->config(array('command' => 'barbaz'));;
        $m->route('/bar/')->config(array('command' => 'bar'));;
        
        $request = new mock_request(array('path' => '/bar/baz'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'barbaz',
            'method' => 'process', 'view' => array()));
        
        $request = new mock_request(array('path' => '/bar'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'bar',
            'method' => 'process', 'view' => array()));
      }  

     /**
      * The trailing slash can be omitted, but a route with
      * a trailing slash should still match
      *
      * Test with default parameter
      */
     
    function testNoTrailingSlashWithParam() {
        $m = new api_routing();
        $m->route('/test/:param')->config(array('command' => 'test', 'param' => 'foo'));;
        
        $request = new mock_request(array('path' => '/test'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'test',
            'param' => 'foo',
            'method' => 'process', 'view' => array()));
        
     }
     
     /**
      * The trailing slash can be omitted, but a route with
      * a trailing slash should still match
      *
      * Tests with default parameter and sub-"folder" in the routing table
      */
      
    function testParamVsSlash() {
        $m = new api_routing();
        $m->route('/bar/baz/')->config(array('command' => 'barbaz'));
        $m->route('/bar/:param')->config(array('command' => 'barparam','param' => 'index'));
        
        $request = new mock_request(array('path' => '/bar/baz'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'barbaz',
            'method' => 'process', 'view' => array()));

        $request = new mock_request(array('path' => '/bar/baz.xml'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'barparam',
            'param' => 'baz.xml',
            'method' => 'process', 'view' => array()));
        
        $request = new mock_request(array('path' => '/bar'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'barparam',
            'param' => 'index',
            'method' => 'process', 'view' => array()));
        
        
     }
     
     
     /**
      * Tests if extensions can be omitted and stripped so the variable 
      * is without extension
      */
    function testOptionalExtensionWithoutExtension() {
        $m = new api_routing();
        $m->route('/:command/:method', array("optionalextension"=>TRUE))->config(Array());
        
        $request = new mock_request(array('path' => '/bar/baz.xml'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'bar',
            'method' => 'baz', 'view' => array()));
    }
    
     /**
      * Tests if extensions can be omitted and stripped so the variable 
      * is without extension
      */
    function testOptionalExtensionWithExtension() {
        $m = new api_routing();
        $m->route('/:command/:method', array("optionalextension"=>FALSE))->config(Array());
        
        $request = new mock_request(array('path' => '/bar/baz.xml'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'bar',
            'method' => 'baz.xml', 'view' => array()));
    }
    
     /**
      * Tests if extensions can be omitted and stripped so the variable 
      * is without extension
      */
    function testOptionalExtensionDefault() {
        $m = new api_routing();
        $m->route('/:command/:method', array())->config(Array());
        
        $request = new mock_request(array('path' => '/bar/baz.xml'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'bar',
            'method' => 'baz.xml', 'view' => array()));
    }
    
    /**
     * Dynamic view parsing test
     */
    function testDynamicView() {
        $m = new api_routing();
        $m->route('/:foo/:command/:method', array("substitute"=>TRUE))
            ->config(Array(
            'view' => Array('xsl' => '{foo}.xsl')));
        $request = new mock_request(array('path' => '/ba\'_r/baz/blubb'));
        
        $route = $m->getRoute($request);
        
        $this->assertEqual($route, Array('command' => 'baz', 'foo'=>"ba'_r", 'method'=>'blubb',
        'view' => Array('xsl' => "ba_r.xsl")));
    }
    
    /**
     * Tests a wildcard route.
     */
    function testWildcardRoute() {
        $m = new api_routing();
        $m->route('*')->config(array('command' => 'catchall'));
        
        $request = new mock_request(array('path' => '/test'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'catchall',
            'method' => 'process', 'view' => array(), 'test'));
        
        $request = new mock_request(array('path' => '/test/another'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'catchall',
            'method' => 'process', 'view' => array(), 'test/another'));
    }
    
    /**
     * Tests if parameters can be extended if the config option is set
     *
     */
    function testRewriteRoute() {
        $m = new api_routing();
        $m->route('/test/:method/:command', array("substitute"=>TRUE))->config(array('command' => 'foo_{command}', 'method'=>'blubb', 'random'=>'{method}_shaboom'));
        
        $request = new mock_request(array('path' => '/test/blah/bar'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command'=>'foo_bar',
            'method' => 'blah', 'random'=>'blah_shaboom', 'view'=> array()));
        
    }
    
    function testNamespaceRoute() {
        $m = new api_routing();
        $m->route('/:namespace/test/:command')->config(array('command' => 'foo', 'method'=>'blah'));
        
        $request = new mock_request(array('path' => '/nsp/test/bar'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command'=>'bar',
            'method' => 'blah', 'view'=> array(), 'namespace' => 'nsp'));
    }

}