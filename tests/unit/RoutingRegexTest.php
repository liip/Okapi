<?php
/**
 * Tests the api_routing class which stores routing configuration and
 * allows queries on the configured routes.
 */
class RoutingRegexTest extends api_testing_case_phpunit {
    function setUp() {
        // Remove all existing routes
        $m = new api_routing_regex();
        $m->clear();
    }
    
    /**
     * Root URL goes to api_commands_index command.
     */
    function testEmpty() {
        $m = new api_routing_regex();
        $m->route('/')->config(array('command' => 'index'));
        
        $request = new mock_request(array('path' => '/'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'index',
            'method' => 'process', 'view' => array()));
    }

    /**
     * Test a simple match, no fancy mapping
     */
    function testNoURLParams() {
        $m = new api_routing_regex();
        $m->route('/test/foo/bar')->config(array('command' => 'index'));
        
        $request = new mock_request(array('path' => '/test/foo/bar'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'index',
                                         'method' => 'process', 'view' => array()));
    }
    
    /**
     * Test a simple no-match route
     */
    function testNoURLParamsNoMatch() {
        $m = new api_routing_regex();
        $m->route('/test/fooblah')->config(array('command' => 'test'));
        
        $request = new mock_request(array('path' => '/blah/laberblah'));
        $route = $m->getRoute($request);
        $this->assertNull($route);
    }
    
    /**
     * Test a mapped route
     */
    function testMapParametersNumerical() {
        $m = new api_routing_regex();
        $m->route('/([a-zA-Z]+)(/?)(.*)')
        ->config(array('command' => 'doesnotexist'))
        ->map(array('command' => 1,
                    'query' => 3));
        
        $request = new mock_request(array('path' => '/foobar/lalala'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'foobar',
                                         'method' => 'process',
                                         'query' => 'lalala',
                                         'view' => array()));
    }
    
    /**
     * Test a mapped route but with no match in the url
     */
    function testMapParametersNumericalNoMatch() {
        $m = new api_routing_regex();
        $m->route('/([a-zA-Z]+)(/?)(.*)')
        ->config(array('command' => 'mydefault'))
        ->map(array('command' => 5,
                    'query' => 3));
        
        $request = new mock_request(array('path' => '/foobar/lalala'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'mydefault',
                                         'method' => 'process',
                                         'query' => 'lalala',
                                         'view' => array()));
    }
    
    /**
     * Test a mapped route with named subpatterns
     */
    function testMapWithNamedPatterns() {
        $m = new api_routing_regex();
        $m->route('/([a-zA-Z]+)(/?)(?<query>.*)')
        ->config(array('command' => 'mydefault'))
        ->map(array('manual_query' => 3));
        
        $request = new mock_request(array('path' => '/foobar/lalala'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'mydefault',
                                         'method' => 'process',
                                         'query' => 'lalala',
                                         'manual_query' => 'lalala',
                                         'view' => array()));
    }
    
    /**
     * Test a mapped route with named subpatterns conditional
     */
    function testMapWithNamedPatternsNoMatch() {
        $m = new api_routing_regex();
        $m->route('/([a-zA-Z]+)(/?)(?<query>.*)')
        ->config(array('command' => 'somecommand',
                       'query' => 'defaultquery')); // Should be preserved
        
        $request = new mock_request(array('path' => '/foobar'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'somecommand',
                                         'method' => 'process',
                                         'query' => 'defaultquery',
                                         'view' => array()));
    }
    
    /**
     * Test that there is a match if request and condition are POST
     */
    function testVerbExplicitPOST() {
        $m = new api_routing_regex();
        $m->route('/test/(?<foo>.*)')
          ->config(array('command' => 'test'))
          ->when(array('verb' => 'POST'));
        
        $request = new mock_request(array('path' => '/test/abc',
                                          'verb' => 'POST'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'test',
                                         'method' => 'process',
                                         'foo' => 'abc',
                                         'view' => array()));
    }
    
    /**
     * Test that there is no match if the verb is wrong
     */
    function testVerbExplicitGETNoMatch() {
        $m = new api_routing_regex();
        $m->route('/test/(?<foo>.*)')
          ->config(array('command' => 'test'))
          ->when(array('verb' => 'GET'));
        
        $request = new mock_request(array('path' => '/test/abc',
                                          'verb' => 'POST'));
        $route = $m->getRoute($request);
        $this->assertNull($route);
    }
    
    /**
     * Test that routes get preserved
     */
    function testPreserveRegexRoutes() {
        $m = new api_routing_regex();
        $m->route('/test/(.*)')->config(array('command' => 'test'))
          ->map(array('query' => 1));
        
        $request = new mock_request(array('path' => '/test/abc'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'test',
                                         'method' => 'process',
                                         'query' => 'abc',
                                         'view' => array()));
        
        $m = new api_routing_regex();
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'test',
                                         'method' => 'process',
                                         'query' => 'abc',
                                         'view' => array()));
    }
    
    /**
     * Test that we can add regex and standard routes
     */
    function testMixRegexAndNonRegexRoutes() {
        $x = new api_routing_regex();
        $m = new api_routing();
        
        $x->route('/test/(?<regexParam>[a-zA-Z]+)')
          ->config(array('command' => 'regex'));
        
        $m->route('/other/:defaultParam')
          ->config(array('command' => 'default'));
        
        $request = new mock_request(array('path' => '/test/abc'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'regex',
                                         'method' => 'process',
                                         'regexParam' => 'abc',
                                         'view' => array()));
        
        $request = new mock_request(array('path' => '/other/def'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'default',
                                         'method' => 'process',
                                         'defaultParam' => 'def',
                                         'view' => array()));
    }
    
    /**
     * Group regex routes to use common rules
     */
    function testGroupingRegex() {
        $m = new api_routing_regex();
        $g = new api_routing_regex_route();
        $g = $g->config(array('command' => 'default',
                              'view' => array('xsl' => 'default.xsl')))
               ->when(array('verb' => 'GET'))
               ->map(array('query' => 1));
        
        $m->add($g->dup()->route('/test/(.*)')); // Should use the default
        $m->add($g->dup()->route('/other/(.*)'))
                  ->config(array('command' => 'other'));
        
        $request = new mock_request(array('path' => '/test/foo'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'default',
                                         'method' => 'process',
                                         'query' => 'foo',
                                         'view' => array('xsl' => 'default.xsl')));
        
        $request = new mock_request(array('path' => '/other/foobar'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'other',
                                         'method' => 'process',
                                         'query' => 'foobar',
                                         'view' => array('xsl' => 'default.xsl')));
        
    }
    
    /**
     * Test that parameters can be substituted
     */
    function testSubsitute() {
        $m = new api_routing_regex();
        $m->route('/(?<command>[a-zA-Z0-9]+)(/?)(.*)')
          ->config(array('otherparam' => '{command}_{method}',
                         'view' => array('xsl' => 'folder/{command}/{method}.xsl')))
          ->map(array('method' => 3));
        
        $request = new mock_request(array('path' => '/test/foo9'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'test',
                                         'method' => 'foo9',
                                         'otherparam' => 'test_foo9',
                                         'view' => array('xsl' =>
                                                         'folder/test/foo9.xsl')));
        
        $request = new mock_request(array('path' => '/test/'));
        $route = $m->getRoute($request);
        $this->assertEqual($route, array('command' => 'test',
                                         'method' => 'process',
                                         'otherparam' => 'test_process',
                                         'view' => array('xsl' =>
                                                         'folder/test/process.xsl')));
    }
    
}
