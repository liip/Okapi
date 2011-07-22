<?php
/**
 * Configures how requests are routed to controllers.
 */
class api_routing_crud extends api_routing_regex {   
    public function route($route, $config = array()) {
        $r = new api_routing_crud_route();
        return $this->add($r->route($route, $config));
    }
    
    public function config($params) {
        $r = new api_routing_crud_route();
        return $this->add($r->config($params));
    }
    
    public function when($conditions) {
        $r = new api_routing_crud_route();
        return $this->add($r->when($conditions));
    }
    
    public function mapMethods($mappings) {
        $r = new api_routing_crud_route();
        return $this->add($r->mapMethods($mappings));
    }
    
    public function requireId($idmethods) {
        $r = new api_routing_crud_route();
        return $this->add($r->requireId($idmethods));
    }
    
    public function disallowId($nonidmethods) {
        $r = new api_routing_crud_route();
        return $this->add($r->disallowId($nonidmethods));
    }
}

/**
 * Regex Route. Tries to match a pattern to a request
 */
class api_routing_crud_route extends api_routing_regex_route {
    protected $methodMappings = array('delete' => 'delete',
                                      'update' => 'update',
                                      'read' => 'read',
                                      'index' => 'index');
    
    protected $presets = array();
    
    protected $nonIdMethods = array('create', 'index');
    
    protected $idMethods = array('update', 'delete', 'update', 'read');
    
    public function route($route, $config = array()) {
        $this->presets = $route;
        $this->routeConfig = $config;
        return $this;
    }
    
    public function mapFunctions($mappings) {
        $this->methodMappings = $mappings;
    }
    
    public function requireId($idMethods) {
        $this->idMethods = array_merge($this->idMethods, $idMethods);
        return $this;
    }
    
    public function disallowId($nonIdMethods) {
        $this->nonIdMethods = array_merge($this->nonIdMethods, $nonIdMethods);
        return $this;
    }
    
    protected function parseRoute($request) {
        if (isset($this->presets['command'])) {
            $patternCommand = "(/(?<command>" . $this->presets['command'] . "))";
        } else {
            $patternCommand = "(/(?<command>[\w\d._-]+))";
        }
        
        if (isset($this->presets['method'])) {
            $patternMethod = "(/(?<method>" . $this->presets['method'] . "))";
        } else {
            $patternMethod = "(/(?<method>[\w\d._-]+))?";
        }
        
        if (isset($this->presets['id'])) {
            $patternId = "(/(?<id>" . $this->presets['id'] . "))";
        } else {
            $patternId = "(/(?<id>([\w\d._-]+)))";
        }
        
        if (isset($this->presets['query'])) {
            $patternQuery = "(/(?<query>" . $this->presets['id'] . "))?";
        } else {
            $patternQuery = "(/(?<query>.*))?";
        }
    
        //(/(?<command>[\w\d]+))(/(?<id>[\w\d]+))?(/(?<method>[\w\d]+))
        
        if (!isset($this->routeConfig['optionalextension'])) {
            $this->routeConfig['optionalextension'] = True;
        }
        
        $this->route = $patternCommand . $patternId . $patternMethod . $patternQuery;
        $this->params['method'] = 'read';
        $params = parent::parseRoute($request);
        
        
        
        if (in_array($params['id'], $this->nonIdMethods) || $params == null) {
            $this->params['method'] = 'index';
            $this->params['id'] = '';
            $this->route = $patternCommand . $patternMethod . $patternQuery;
            $params = parent::parseRoute($request);
        }
        
        $this->replaceMappedMethod($params);
        
        return $params;
    }
    
    public function replaceMappedMethod(&$params) {
        foreach ($this->methodMappings as $needle => $replace) {
            if ($params['method'] == $needle) {
                $params['method'] = $replace;
            }
        }
    }
    
    
}
