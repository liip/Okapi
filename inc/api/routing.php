<?php
/**
 * Common methods for adding routes which is implemented by
 * all of the different routing classes. This permits
 * consistent adding and configuring of routes.
 */
interface api_routing_interface {
    /**
     * Defines the actual route expression for the given route
     * and returns the object.
     */
    public function route($route, $config = array());

    /**
     * Defines the parameters for the given route and returns the
     * object.
     */
    public function config($params);
    
    /**
     * Adds conditions to the current route and returns the object.
     */
    public function when($conditions);
}

/**
 * This class configures how requests are routed to controllers.
 */
class api_routing implements api_routing_interface {
    static $routes = array();
    
    /**
     * Removes all defined routes.
     */
    public function clear() {
        self::$routes = array();
    }
    
    /**
     * Adds an api_routing_route object to the routing table.
     *
     * This happens automatically with methods which are part
     * of api_routing_interface but this method is needed in case
     * you want to manually instantiate api_routing_route objects.
     */
    public function add($route) {
        self::$routes[] = $route;
        return $route;
    }
    
    /**
     * Adds a new route which only contains the route path for now.
     * Calls route() of a new api_routing_route object.
     */
    public function route($route, $config = array()) {
        $r = new api_routing_route();
        return $this->add($r->route($route, $config));
    }

    /**
     * Adds a new route which only contains the parameters for now.
     * Calls config() of a new api_routing_route object.
     */
    public function config($params) {
        $r = new api_routing_route();
        return $this->add($r->config($params));
    }

    /**
     * Adds a new route which only contains conditions for now.
     * Calls when() of a new api_routing_route object.
     */
    public function when($conditions) {
        $r = new api_routing_route();
        return $this->add($r->when($conditions));
    }
    
    /**
     * Returns the correct route for the given request.
     *
     * @param $request api_request: api_request object.
     */
    public function getRoute($request) {
        $uri = $request->getPath();
        
        foreach (self::$routes as $route) {
            if ($retval = $route->match($request)) {
                return $retval;
            }
        }
        
        return null;
    }
}

/**
 * Standard route. Matches path of the form
 * /path/:param1/:param2/pathpart to commands.
 */
class api_routing_route implements api_routing_interface {
    // Default params
    private $default = array(
        'method' => 'process',
        'view' => array());
    
    private $route      = null;
    private $routeConfig = array();
    private $params     = array();
    private $conditions = array();
    
    public function __construct() {
        $this->params = $this->default;
    }
    
    /**
     * Returns a deep copy of this route.
     */
    public function dup() {
        return clone($this);
    }
    
    /**
     * Configures the actual route expression for this route.
     * Returns itself.
     */
    public function route($route, $config = array()) {
        if ($route[0] != '/') {
            $route = '/' . $route;
        }
        $this->route = $route;
        $this->routeConfig = $config;
        return $this;
    }
    
    /**
     * Adds some configuration to this route and returns
     * itself.
     */
    public function config($params) {
        $this->params = array_merge($this->params, $params);
        return $this;
    }
    
    /**
     * Adds conditions to this route. Currently only one condition
     * is recognized:
     *    - verb: Matches against the request method (verb). Must be
     *            specified in all upper case (GET, POST, ...).
     */
    public function when($conditions) {
        $this->conditions = array_merge($this->conditions, $conditions);
        return $this;
    }
    
    /**
     * Returns the route object for the command if this route matches the
     * passed request. Returns null otherwise.
     */
    public function match($request) {
        $uri = $request->getPath();
        
        if ($this->route == $uri) {
            return $this->params;
        } else if (($params = $this->parseRoute($request)) !== null) {
            return array_merge($this->params, $params);
        } else {
            return null;
        }
    }
    
    /**
     * Matches the current to the given request. Returns null if
     * it doesn't match, returns the extracted parameters otherwise.
     */
    private function parseRoute($request) {
        if (isset($this->conditions['verb']) && $this->conditions['verb'] != $request->getVerb()) {
            return null;
        }
        
        $path = $this->getPath($request);
        $uriParts = explode('/', $path);
        $routeParts = explode('/', $this->route);
        
        // If there is a wildcard match at the end, URI may have
        // more components than the route.
        $last = $routeParts[count($routeParts)-1];
        if (strlen($last) && $last[0] != '*' && count($uriParts) > count($routeParts)) {
            return null;
        }

        
        // Fill in params from URL
        $params = array();
        foreach ($routeParts as $idx => $part) {
            $partKey = substr($part, 1);
            
            if ($part != '' && $part[0] == ':') {
                // Named param
                if (count($uriParts) > 0) {
                    // Param can be filled from the URI
                    $param = array_shift($uriParts);
                    if (empty($param)) {
                        return null;
                    }
                    $params[$partKey] = $param;
                } else if (isset($this->params[$partKey])) {
                    // Last param(s), use default
                    $params[$partKey] = $this->params[$partKey];
                } else {
                    // Param has no match in URL
                    return null;
                }
            
            } else if ($part != '' && $part[0] == '*') {
			    // Wildcard, 0 or more hits
			    $param = implode('/', array_slice($uriParts, 0));
			    $params[$partKey] = $param;
			    $uriParts = array();
			    break;
			    
			} else if ($part != '' && $part[0] == '+') {
			    // Wildcard, eat up all the rest and return
			    // 1 or more hits
			    $param = implode('/', array_slice($uriParts, 0));
			    if (empty($param)) {
			        return null;
			    }
			    $params[$partKey] = $param;
			    $uriParts = array();
			    break;
			    
			} else if (count($uriParts) > 0 && $part == $uriParts[0]) {
                // URI part - matches exatly
                array_shift($uriParts);
            } else if (count($uriParts) == 0 && $part == "") {
            	break;
            } else {
            
                // URI part doesn't match
                return null;
            }
        }

        
        if (isset($this->routeConfig['dynamicview']) && $this->routeConfig['dynamicview']) {
            foreach ($this->params['view'] as &$setting) {
                $setting = preg_replace("/\{([\w\d]+)\}/e", 'api_helpers_string::clean($params[\'$1\'])', $setting);
            }
        }
        
        return $params;
    }
    
    /**
     * Returns the path of the current request to be used for routing.
     */
    private function getPath($request) {
        // Remove the extension if the user wished so
        
        $path = $request->getPath();
        
        $ext = $request->getExtension();
        if ($ext != '' && isset($this->routeConfig['optionalextension']) && $this->routeConfig['optionalextension']) {
            $path = substr($path, 0, -(strlen($ext)+1));
        }
        
        $path = rtrim($path, '/');
        
        return $path;
    }
    
}
