<?php
/* Licensed under the Apache License, Version 2.0
 * See the LICENSE and NOTICE file for further information
 */

// Including all the needed stuff by the framework
// TODO: Remove _once
require_once API_LIBS_DIR."request.php";
require_once API_LIBS_DIR."view.php";
require_once API_LIBS_DIR."i18n.php";
require_once API_LIBS_DIR."command.php";
require_once API_LIBS_DIR."model.php";


/**
 * Used in views to indicate that the view has been prepared and
 * can be used now.
 */
define('API_STATE_READY',   1);

/**
 * Used in views to indicate that the view is still it it's uninitialized
 * state.
 */
define('API_STATE_FALSE',   0);

/**
 * Main controller to handle whole request. Should be used in your
 * application's index.php like this:
 *
 * \code
 * $ctrl = new api_controller();
 * $ctrl->process();
 * \endcode
 *
 * @author   Silvan Zurbruegg
 */
class api_controller {
    /**
     * api_request: Request container. Contains parsed information about
     * the current request.
     */
    private $request = null;

    /**
     * api_response: Response object - passed to the command and view to
     * handle response headers.
     */
    private $response = null;

    /**
     * api_views_common: View object which handles the output.
     */
    private $view = null;

    /**
     * array: Route which matched the current request.
     * Return value of api_routing::getRoute().
     */
    private $route = null;

    /**
     * api_command: Command to process the current request.
     */
    private $command = array();

    /**
     * array: All non-fatal exceptions which have been caught.
     */
    private $exceptions = array();

    /**
     * Constructor. Gets instances of api_request and api_response
     * but doesn't yet do anything else.
     */
    public function __construct($request, $response, $routing, $config) {
        $this->request = $request;
        $this->response = $response;
        $this->routing = $routing;
        $this->config = $config;
    }

    /**
     * Process the current request. This loads the correct command,
     * processes it and then uses the view to display the result.
     *
     * In the case of exceptions, api_exceptionhandler is used to handle them.
     *
     * Calls the following methods in that order:
     *    - api_controller::loadCommand()
     *    - api_controller::processCommand()
     *    - api_controller::prepareAndDispatch()
     */
    public function process($command) {
        $this->command = $command;

        try {
            $this->processCommand();
            return $this->getViewName();
        } catch(Exception $e) {
            $this->catchFinalException($e);
        }

        return false;
    }

    /**
     * Load command based on routing configuration. Uses
     * api_routing::getRoute() to get the command name for the current
     * request. The prefix "{namespace}_commands_" is added to the command name
     * to get a class name and that class is initialized.
     * Namespace is also defined in the routing
     *
     * The instance variables command and route are set to the command
     * object and the route returned by api_routing respectively.
     *
     * @exception api_exception_NoCommandFound if no route matched the
     *            current request or if the command class doesn't exist.
     *
     * \deprecated The naming of commands has been renamed on 2008-02-25
     *             from {namespace}_commands_* to {namespace}_command_*. The old behaviour
     *             is currently supported but will be removed in a future
     *             release.
     */
    public function findCommandName($route) {
        if (!($route instanceOf api_routing_route)) {
            throw new api_exception_NoCommandFound();
        }

        $this->route = $route->getParams();
        if (isset($this->route['namespace'])) {
            $this->route['namespace'] = api_helpers_string::clean($this->route['namespace']);
        } else {
            $this->route['namespace'] = API_NAMESPACE;
        }

        $this->config->load($this->route['command']);
        return $this->route['namespace'].'_command_' . $this->route['command'];
    }

    /**
     * Calls the api_command::isAllowed() method to check if the command
     * can be executed. Then api_command::process() is called.
     *
     * @exception api_exception_CommandNotAllowed if api_command::isAllowed()
     *            returns false.
     *
     */
    public function processCommand() {
        try {
            if (!$this->command->isAllowed()) {
                throw new api_exception_CommandNotAllowed("Command access not allowed: ".get_class($this->command));
            }
            $this->command->process();
        } catch(Exception $e) {
            $this->catchException($e, array('command' => $this->route['command']));
        }
    }

    /**
     * Loads the view and uses it to display the response for the
     * current request.
     *
     * Calls the following methods in that order:
     *    - api_controller::updateViewParams()
     *    - api_controller::prepare()
     *    - api_controller::dispatch()
     */
    public function getViewName() {
        $this->updateViewParams();
        if (empty($this->route['view']) || (empty($this->route['view']['ignore']))) {
            if (isset($this->route['view']) && isset($this->route['view']['class'])) {
                $viewName = $this->route['view']['class'];
            } else {
                $viewName = 'default';
            }
            return api_view::getViewName($viewName, $this->request, $this->route);
        }

        // Ignore view
        return;
    }

    /**
     * Adds Exception to exceptions array. The catchException() method
     * calls this method for any non-fatal exception. The array of
     * collected exceptions is later passed to the view so it can still
     * display them.
     *
     * Exceptions are added to the array $this->exceptions.
     *
     * @param $e api_exception: Thrown exception
     * @param $prms array: Additional params passed to catchException()
     */
    private function aggregateException(api_exception $e, array $prms) {
        if (!empty($prms)) {
            foreach($prms as $n=>$v) {
                if (!empty($v)) {
                    $e->setParam($n, $v);
                }
            }
        }

        array_push($this->exceptions, $e);
    }

    public function getExceptions() {
        return $this->exceptions;
    }

    /**
     * Catches any exception which has either been rethrown by the
     * catchException() method or was thrown outside of it's scope.
     *
     * Calls api_exceptionhandler::handle() with the thrown exception.
     *
     * @param   $e api_exception: Thrown exception, passed to the exceptionhandler.
     */
    private function catchFinalException(Exception $e) {
        api_exceptionhandler::handle($e, $this);
        if ($this->response === null) {
            die();
        }
    }

    /**
     * Catches an exception. Non-fatal and fatal exceptions are handled
     * differently:
     *    - fatal: Re-thrown so they abort the current request. Fatal
     *             exceptions are later passed on to catchFinalException().
     *    - non-fatal: Processed using aggregateException(). Additionally
     *                 they are logged by calling api_exceptionhandler::log().
     *
     * Exceptions of type api_exceptions (and subclasses) have a getSeverity()
     * method which indicates if the exception is fatal. All other exceptions
     * are assumed to always be fatal.
     *
     * @param $e api_exception: Thrown exception.
     * @param $prms array: Parameters to give more context to the exception.
     */
    private function catchException(Exception $e, $prms=array()) {
        if ($e instanceof api_exception && $e->getSeverity() === api_exception::THROW_NONE) {
            $this->aggregateException($e, $prms);
            api_exceptionhandler::log($e);
        } else {
            throw $e;
        }
    }

    /**
     * Override the XSLT style sheet to load. Currently used by the
     * exception handler to load another view.
     *
     * @param $xsl string: XSLT stylesheet path, relative to the theme folder.
     */
    public function setXsl($xsl) {
        $this->route['view']['xsl'] = $xsl;
    }

    /**
     * Uses api_command::getXslParams() method to overwrite the
     * view parameters. All parameters returned by the command
     * are written into the 'view' array of the route.
     */
    private function updateViewParams() {
        $this->route['view'] = array_merge($this->route['view'],
                $this->command->getXslParams());
    }

    /**
     * Returns the command name, needed by tests
     *
     */
    public function getCommandName() {
        return get_class($this->command);
    }

    /**
     * Returns the final, dispatched view  name, needed by tests
     *
     */
    public function getFinalViewName() {
        return get_class($this->view);
    }
}
