<?php
/**
 * Interface with common methods for adding routes which is implemented
 * by all of the different routing classes. This permits consistent
 * adding and configuring of routes.
 */
interface api_Irouting {
    /**
     * Defines the actual route expression for the given route. See
     * api_routing_route::parseRoute() for how the route is parsed.
     * 
     * The following configuration values are recognized:
     *    - dynamicview: (See api_routing_route::parseRoute())
     *    - optionalextension: (See api_routing_route::getPath())
     *
     * @param $route string: Route expression.
     * @param $config hash: Configuration for route parsing.
     * @return api_routing_route: The route object (returned to allow chaining)
     */
    public function route($route, $config = array());

    /**
     * Defines the parameters for the given route and returns the
     * object.
     * 
     * @param $params hash: Values which will be returned when the
     *        route matches.
     * @return api_routing_route: The route object (returned to allow chaining)
     */
    public function config($params);
    
    /**
     * Adds conditions to the current route and returns the object.
     * 
     * The following conditions can be set:
     *    - verb: HTTP verb must match the value for the route to match.
     * 
     * @param $conditions hash: Conditions.
     * @return api_routing_route: The route object (returned to allow chaining)
     */
    public function when($conditions);
}

/**
 * Configures how requests are routed to controllers.
 */
class api_routing implements api_Irouting {
    /** Array of api_routing_route: List of all configured routes. */
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
     * of api_Irouting but this method is needed in case
     * you want to manually instantiate api_routing_route objects.
     *
     * @param $route api_routing_route: Route to add.
     */
    public function add($route) {
        self::$routes[] = $route;
        return $route;
    }
    
    public function route($route, $config = array()) {
        $r = new api_routing_route();
        return $this->add($r->route($route, $config));
    }
    
    public function config($params) {
        $r = new api_routing_route();
        return $this->add($r->config($params));
    }
    
    public function when($conditions) {
        $r = new api_routing_route();
        return $this->add($r->when($conditions));
    }
    
    /**
     * Returns the correct route for the given request. Returns null
     * if no route matches.
     * 
     * Returns all parameters (set with api_routing_route::config())
     * merged with parameters extracted from the request.
     * 
     * @param $request api_request: api_request object.
     * @return hash: Route params.
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
 * Standard route. Matches path to commands.
 */
class api_routing_route implements api_Irouting {
    /** Default parameters. */
    private $default = array(
        'method' => 'process',
        'view' => array());
    
    /** Route expression to match against the path. */
    private $route      = null;
    /** Additional route configuration which influences how the route
        is processed. */
    private $routeConfig = array();
    /** Parameter hash for the route. Returned when the route matches. */
    private $params     = array();
    /** Additional conditions which does not match the path. */
    private $conditions = array();
    
    /**
     * Constructor. Sets the default params.
     */
    public function __construct() {
        $this->params = $this->default;
    }
    
    /**
     * Returns a deep copy of this route. Can be used when an existing
     * route is to be re-used and modified slightly. Add it to the routing
     * table with api_routing::add().
     */
    public function dup() {
        return clone($this);
    }
    
    public function route($route, $config = array()) {
        if ($route[0] != '/') {
            $route = '/' . $route;
        }
        $this->route = $route;
        $this->routeConfig = $config;
        return $this;
    }
    
    public function config($params) {
        $this->params = array_merge($this->params, $params);
        return $this;
    }
    
    public function when($conditions) {
        $this->conditions = array_merge($this->conditions, $conditions);
        return $this;
    }
    
    /**
     * Returns the route object for the command if this route matches the
     * passed request. Returns null otherwise.
     * @param $request api_request: Request object.
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
     * Matches the current route to the given request. Returns null if
     * it doesn't match, returns the extracted parameters otherwise.
     *
     * A route defines different parts separated by slash. Depending on the
     * first character, the parts match like this:
     *    - Colon (:)    - Named parameter, matches the path up to the next
     *                     slash. The paramater name comes after the
     *                     colon. All named parameters are added to the
     *                     paramaters hash.
     *    - Plus (+)     - As named parameters but eats up all the rest
     *                     of the path.
     *    - Asterisk (*) - Same as plus, but optional.
     *    - Others       - Has to match literally in the path.
     *
     * If the `substitute' route configuration is set, then variables in
     * the configuration are replaced by the extracted parameters.
     * An example route:
     *
     * \code
     * $m = new api_routing();
     * $m->route('/:foo/:command/:method', array("substitute"=>true))
     *     ->config(array('view' => array('xsl' => '{foo}.xsl')));
     * \endcode
     *
     * @param $request api_request: Request object.
     */
    protected function parseRoute($request) {
        if (isset($this->conditions['verb']) && $this->conditions['verb'] != $request->getVerb()) {
            return null;
        }
        
        // Get clean path and route
        $path = $this->getPath($request);
        $route = rtrim($this->route,"/");
        // --
        
        // Get all parameter-names
        $paramKeys = Array();
        preg_match_all("%(:|\*|\+)([\w\d_-]+)%", $route, $paramKeys);
        $paramKeys = $paramKeys[2]; // Just that we can add brackets and don't have to change them anywhere else
        // --
        
        /*
         * We replace the user-defined route with one regex here:
         * 
         * When the user enters /:foo/bar, then :foo is a required parameter, which spans until the next slash, that said, /bar or /foo/boo/bar will not match
         * When the user enters /bar/:foo then :foo is an optional parameter, which still spans until the next slash. /bar/boo and /bar will match, but not /bar/boo/bii
         * When the user enters /*foogly/muh then *foogly is an optional parameter, which just eats up, what is there, as long as it ends with /muh. /blah/blubb/blih/muh will match as well as /muh. but not /foo/.
         * When the user enters /+foogly/muh then +foogly is a required paramter, which eats one or more path-parts. /blah/blubb/blih/muh will match. but not /muh nor /foo/bar
         * When the user enters /*foogly/+mooh/:id then *foogly and :id are optional. /blah will match with `mooh' set to `blah'. /blah1/blah2 will match with `foogly' set to `blah1'
         * and `mooh' to `blah2'. /test/123/woo/sa will also match with `foogly' set to `test', `mooh' set to `123/woo' and `id' set to `sa'. As you can see, the right most wildcard parameter
         * eats up everything, if every wanted-parameter is set. This is due to lazy evaluation of the route.                      
         */
        // TODO: Cache this
        $routeRegex = preg_replace(Array ("%/:([\w\d_-]+)$%",  // Named parameter at end of the route, which is optional, if a default value exists (check later)
                                        "%/:([\w\d_-]+)%", // Named parameter in the middle of the route, not optional 
                                        "%/\*([\w\d_-]*)%", // Wildcard parameter anywhere, will be optional
                                        "%/\+([\w\d_-]+)%"), // Wildcard parameter anywhere, will be mandatory
                                 Array ("(/[^/]*)?", 
                                        "(/[^/]+)",
                                        "(/.*?)?", 
                                        "(/.+?)"), $route);
        
        // Match the whole regex against the path
        $paramMatches = Array();
        $cnt = preg_match_all("%^".$routeRegex."$%", $path, $paramMatches);
        // If no match - nothing to do here
        if (!$cnt) {
            return null;
        }
        // --
            
        // Fill in all the missing parameters from the default-array
        $params = Array();
        foreach ($paramKeys as $key => $val) { // $key is used for lookup in the match array, $value is the name of the key
            if (($match = $paramMatches[$key+1][0]) != false) { // We have a match in the uri
                $params[$val] = substr($match,1);
            } elseif (isset($this->params[$val])) {             // No match, but in defaults
                if (is_array($this->params[$val])) {
                    $params[$val] = $this->params[$val][0];
                } else {
                    $params[$val] = $this->params[$val];
                }
            } else {                                            // No match, return null
                return null;
            }
        }
        // --
        
        // Substitute the placeholders in the route here. (the {foo} thing)
        $replacedParams = Array();
        if ( isset($this->routeConfig['substitute']) && $this->routeConfig['substitute']) {
            foreach ($this->params as $key => &$item) {
                if ($key != "view") {
                    $replacedParams[$key] = $this->fetchParam($item, $key, $params); // We store this temporary, so that no already replaced value is being replaced into another
                } else { // This is a bit ugly since `view' is kinda special in the routing
                    foreach($item as $k => &$v) {
                        $params["view"][$k] = $this->fetchParam($v, $k, $params);                  
                    }
                }
            }
        }
        $params = array_merge($params, $replacedParams);
        // --
        
        // There might be a pure wildcard match with asterisk in the route
        if (count($paramKeys) == 0 && isset($paramMatches[1][0])) {
            $params[] = substr($paramMatches[1][0],1);
        }
        // --
        
        
        return $params;
    }

    /**
     * Returns the replaced parameter stripped down to char/integer
     *
     * @param Array|String $item An item of a parameter in api_routing
     * @param String $key
     * @param The params array as parsed so far $parsedParams
     * @return String The replaced parameter
     */
    private function fetchParam(&$item, &$key, $parsedParams) {
        if (is_array($item)) {
            return preg_replace("/\{([\w\d]+?)\}/e", 'api_helpers_string::stripToCharInteger($parsedParams[\'$1\'])', $item[1], -1, $repl);
        } else {
            return $parsedParams[$key];
        }
    }
    
    
    /**
     * Returns the path of the current request to be used for routing.
     * If optionalextension route configuration is set, then the path
     * is returned without the extensions. So in that case the route
     * matches no matter what extension is used.
     *
     * @param $request api_request: Request to get path of.
     */
    private function getPath($request) {
        $path = $request->getPath();
        $ext = $request->getExtension();
        
        // Remove the extension if the user wished so
        if ($ext != '' && isset($this->routeConfig['optionalextension']) && $this->routeConfig['optionalextension']) {
            $path = substr($path, 0, -(strlen($ext)+1));
        }
        
        $path = rtrim($path, '/');
        return $path;
    }
}
