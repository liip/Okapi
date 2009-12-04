<?php
/* Licensed under the Apache License, Version 2.0
 * See the LICENSE and NOTICE file for further information
 */

/**
 * Base command to be extended by all commands.
 *
 * A command is responsible for getting the data and executing all the
 * actions for the current request.
 *
 * @author   Silvan Zurbruegg
 */
abstract class api_command {
    /**
     * api_request: Request object containing information about the current
     * request.
     * @var api_request
     */
    protected $request = null;

    /**
     * api_response: Response object used to send output to the client.
     * @var api_response
     */
    protected $response = null;

    /**
     * @var api_routing
     */
    protected $routing;

    /**
     * array: Route definition of the current request. This is the return
     * value of api_routing::getRoute().
     * @var api_routing_route
     */
    protected $route = array();

    /**
     * array of api_model: Data objects to be passed to the XSLT
     * stylesheets.
     * @var array
     */
    protected $data = array();

    /**
     * string: Default Method. Gets called by api_command::process() when
     * the route does not contain a method.
     * @var string
     */
    protected $defaultMethod = 'index';

    /**
     * Constructor. Initializes the object's attributes but does not have
     * any side effects.
     * @param    $route array: The attributes as returned by
     *                         api_routing::getRoute().
     */
    public function __construct(api_routing $routing, api_request $request, api_response $response) {
        $this->routing = $routing;
        $this->route = $routing->getRoute();
        $this->request = $request;
        $this->response = $response;
        $this->response->command = $this;
        $this->response->getDataCallback = array($this,'getData');
        $this->command = api_helpers_class::getBaseName($this);
    }

    public function preAction() {
        return true;
    }

    public function postAction() {
        $this->response->viewParams = array_merge($this->response->viewParams, $this->getXslParams());
        return true;
    }

    /**
     * Get XSL parameters from command. Used to overwrite view configuration
     * from the route.
     * @return  array: Associative array with params.
     */
    public function getXslParams() {
        return array();
    }

    /**
     * Process request. This is the entry point of a command which calls
     * the method as passed in from the routing engine. If no method has
     * been defined, then the method specified by api_command::$defaultMethod
     * is called.
     * @return void
     */
    public function process() {
        $route = $this->route->getParams();
        if (isset($route['method']) && $route['method'] != 'process') {
            $method = 'execute'.ucfirst($route['method']);
            if (method_exists($this, $method) || is_callable(array($this, $method))) {
                $this->$method();
                return $this->response;
            }
            // TODO: remove BC hack
            trigger_error('Please update '.$this->command.'::'.$route['method'].' to be prefixed with "execute"', E_USER_NOTICE);
            if (method_exists($this, $route['method']) || is_callable(array($this, $route['method']))) {
                $this->{$route['method']}();
                return $this->response;
            }
        }
        throw new api_exception_noMethodFound('Incorrect method name '.get_class($this).'::'.$route['method'].
            ', you may want to implement __call or check the return value of isAllowed if you returned a custom method name');
    }

    /**
     * Default method called by api_command::process (as specified with
     * api_command::$defaultMethod).
     *
     * If you want a catch-all method that is executed on every request,
     * overwrite api_command::process(). If you just want a fall-back for
     * the case when a method specified in the route doesn't exist in this
     * class, then overwrite api_command::defaultRequest().
     * @return void
     */
    public function defaultRequest() {
    }

    /**
     * Checks permission. To prevent a user from accessing a command, the
     * command has to redirect the user somewhere else in this method.
     *
     * When this method returns false the controller throws a
     * api_exception_commandNotAllowed exception.
     *
     * You can also return a string to another method of the same command
     * which will be called instead of the original one, this would typically
     * be a login method that displays a login form
     *
     * @return bool|string
     */
    public function isAllowed() {
        return true;
    }

    /**
     * Merge the response of all data objects in api_command::$data into
     * one XML DOM and return the DOM. This calls api_model::$getDOM()
     * on every element of the data array.
     * @return DOMDocument: Document with root tag "command". The root tag
     *                      has an attribute "name" which is set to the base
     *                      name of the command class
     *                      (see api_helpers_class::getBaseName()).
     */
    public function getData() {
        $dom = new DOMDocument();
        $dom->loadXML("<command/>");
        $cmdNode = $dom->documentElement;
        $cmdNode->setAttribute("name", $this->command);

        foreach ($this->data as $d) {
            $dataDom = $d->getDOM();
            if (!is_null($dataDom) && $dataDom->documentElement) {
                $node = $dom->importNode($dataDom->documentElement, true);
                $dom->documentElement->appendChild($node);
            }
        }

        return $dom;
    }

    public function setLogger($logger) {
        $this->log = $logger;
    }
}
