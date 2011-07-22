<?php
/**
 * Tests api_cache - just test we get the right instance
 */
class ViewTest extends api_testing_case_phpunit {
    
    function setUp() {
        // Remove all existing routes
        $m = new api_routing();
        $m->clear();
    }
    
    function testNoOmitextension() {
        // create view without omitextension
        $route = new api_routing();
        $route->route('/index.xml')->config(array('command' => 'index', 'view' => array('class' => 'plain')));
        $request = new mock_request(array('path' => '/index.xml'));
        $response = new api_response();
        $route = $route->getRoute($request);
        $view = api_view::factory('plain', $request, $route, $response);
        // assert view class is used
        $this->assertIsA($view, 'api_views_plain');
    }
    
    function testOmitextensionTrue() {
        // create view with omitextension TRUE
        $route = new api_routing();
        $route->route('/index.xml')->config(array('command' => 'index', 'view' => array('class' => 'plain', 'omitextension' => TRUE)));
        $request = new mock_request(array('path' => '/index.xml'));
        $response = new api_response();
        $route = $route->getRoute($request);
        $view = api_view::factory('plain', $request, $route, $response);
        // assert view class is used
        $this->assertIsA($view, 'api_views_plain');
    }
    
    function testOmitextensionFalse() {
        // create view with omitextension FALSE
        $route = new api_routing();
        $route->route('/index.xml')->config(array('command' => 'index', 'view' => array('class' => 'plain', 'omitextension' => FALSE)));
        $request = new mock_request(array('path' => '/index.xml'));
        $response = new api_response();
        $route = $route->getRoute($request);
        $view = api_view::factory('plain', $request, $route, $response);
        // assert view class is overridden
        $this->assertIsA($view, 'api_views_xml');
    }
}