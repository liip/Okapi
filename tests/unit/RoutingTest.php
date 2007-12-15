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
        $m->add('', array('controller' => 'index'));
        
        $route = $m->getRoute('/');
        $this->assertEqual($route, array('controller' => 'index',
            'method' => 'process'));
    }

    /**
     * Mapping with one request param.
     */
    function testWithOneRequestParam() {
        $m = new api_routing();
        $m->add('test/:param1', array('controller' => 'test'));
        
        $route = $m->getRoute('/test/abc');
        $this->assertEqual($route, array('controller' => 'test',
            'method' => 'process', 'param1' => 'abc'));
    }

    /**
     * Mapping with one request param but wrong URI.
     */
    function testWithOneRequestParamNoMatch() {
        $m = new api_routing();
        $m->add('test/:param1', array('controller' => 'test'));
        
        $route = $m->getRoute('/test/');
        $this->assertNull($route);

        $route = $m->getRoute('/mytest/abc');
        $this->assertNull($route);
    }
    
    /**
     * Mapping with several request params and URL components.
     */
    function testWithMultipleRequestParam() {
        $m = new api_routing();
        $m->add(':user/:controller/test/def/:foo/:bar/superuser', array('controller' => 'test'));
        
        $route = $m->getRoute('/pneff/index/test/def/myfoo/something/superuser');
        $this->assertEqual($route, array('controller' => 'index',
            'method' => 'process', 'user' => 'pneff', 'foo' => 'myfoo',
            'bar' => 'something'));
    }
    
    /**
     * 
     */
    function testWithMultipleRequestParamNoMatch() {
        $m = new api_routing();
        $m->add(':user/:controller/test/def/:foo/:bar/superuser', array('controller' => 'test'));
        
        $route = $m->getRoute('/pneff/index/test/def/myfoo/something');
        $this->assertNull($route);
        
        $route = $m->getRoute('index/test/def/myfoo/something/superuser');
        $this->assertNull($route);
    }

    /**
     * Routes should be preserved between two different routing
     * objects.
     */
    function testPreserveRoutes() {
        $m = new api_routing();
        $m->add('test/:param1', array('controller' => 'test'));
        
        $route = $m->getRoute('/test/abc');
        $this->assertEqual($route, array('controller' => 'test',
            'method' => 'process', 'param1' => 'abc'));
        
        $m = new api_routing();
        $route = $m->getRoute('/test/abc');
        $this->assertEqual($route, array('controller' => 'test',
            'method' => 'process', 'param1' => 'abc'));
    }
}
?>
