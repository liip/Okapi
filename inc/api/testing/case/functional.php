<?php
/**
 * Base class for functional tests. Provides methods to trigger a
 * complete Okapi request without actually doing any HTTP request.
 * Instead api_controller::process() is called directly.
 */
abstract class api_testing_case_functional extends api_testing_case_phpunit {
    /** @var DOMDocument DOM instance containing the response if it's not json. */
    protected $responseDom;
    /** @var api_response response object */
    protected $response;
    /** @var string response content */
    protected $responseText;
    /** @var sfServiceContainer service container instance */
    protected $sc;

    /**
     * Set mocked services
     */
    public function setMockedServices($sc, $mocks = array()) {
        if (!is_array($mocks)) {
            return;
        }
        foreach ($mocks as $service => $object) {
            $sc->setService($service, $object);
        }
    }

    public function tearDown() {
        unset($this->sc);
        parent::tearDown();
    }

    /**
     * Executes the given request internally using the GET method.
     * @param string $route the route name to call
     * @param array $params the route parameters
     * @return string|array response text or command's data property if ext is json
     */
    protected function get($route, $params=array(), $mocks=array(), $extension=null) {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        return $this->request($route, $params, array(), $mocks, $extension);
    }

    /**
     * Executes the given request internally using the HEAD method.
     * @param string $route the route name to call
     * @param array $params the route parameters
     * @param array $mocks mocked services that will be injected into the service container before execution
     * @param string $extension allows the query's extension to be specified
     * @return string|array response text or command's data property if ext is json
     */
    protected function head($route, $params=array(), $mocks=array(), $extension=null) {
        $_SERVER['REQUEST_METHOD'] = 'HEAD';
        return $this->request($route, $params, array(), $mocks, $extension);
    }

    /**
     * Executes the given request internally using the POST method.
     * @param string $route the route name to call
     * @param array $params the route parameters
     * @param array $post POST parameters to pass to the request.
     * @param array $mocks mocked services that will be injected into the service container before execution
     * @param string $extension allows the query's extension to be specified
     * @return string|array response text or command's data property if ext is json
     */
    protected function post($route, $params=array(), $post=array(), $mocks=array(), $extension=null) {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        return $this->request($route, $params, $post, $mocks, $extension);
    }

    /**
     * Executes the given request internally using the PUT method.
     * @param string $route the route name to call
     * @param array $params the route parameters
     * @param array $post POST parameters to pass to the request.
     * @param array $mocks mocked services that will be injected into the service container before execution
     * @param string $extension allows the query's extension to be specified
     * @return string|array response text or command's data property if ext is json
     */
    protected function put($route, $params=array(), $post=array(), $mocks=array(), $extension=null) {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        return $this->request($route, $params, $post, $mocks, $extension);
    }

    /**
     * Executes the given request internally using the DELETE method.
     * @param string $route the route name to call
     * @param array $params the route parameters
     * @param array $mocks mocked services that will be injected into the service container before execution
     * @param string $extension allows the query's extension to be specified
     * @return string|array response text or command's data property if ext is json
     */
    protected function delete($route, $params=array(), $mocks=array(), $extension=null) {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        return $this->request($route, $params, array(), $mocks, $extension);
    }

    /**
     * Common request handling for get/post.
     * @param string $route the route name to call
     * @param array $routeParams the route parameters
     * @param array $params POST parameters to pass to the request.
     * @param array $mocks mocked services that will be injected into the service container before execution
     * @param string $extension allows the query's extension to be specified
     * @return string|array response text or command's data property if ext is json
     */
    private function request($route, $routeParams, $params=array(), $mocks=array(), $extension=null) {
        $this->sc = api_init::createServiceContainer();
        $this->sc->routingcontainer;
        $path = $this->sc->routing->gen($route, (array) $routeParams);

        // append extension at the end or before get params
        if ($extension) {
            $path = preg_replace('{(\?.*$|$)}', '.'.$extension.'$1', $path);
        }

        $components = parse_url($path);
        $_GET = $_POST = $_REQUEST = $_FILES = array();
        $_SERVER["REQUEST_URI"] = $components['path'];

        if (isset($components['query'])) {
            $_SERVER['REQUEST_URI'] .= '?'.$components['query'];
            $query = array();
            parse_str($components['query'], $query);
            $_GET = $query;
        }
        if (isset($compoenents['fragment'])) {
            $_SERVER['REQUEST_URI'] .= '#'.$components['fragment'];
        }
        $_POST = (array) $params;
        $this->uploadFiles($_POST, $_FILES);
        $_REQUEST = array_merge($_GET, $_POST);

        $this->sc = api_init::createServiceContainer();
        $this->sc->routingcontainer;

        $request = $this->sc->request;
        $response = $this->sc->response;

        $this->sc->routing->matchRoute($request);
        $route = $this->sc->routing->getRoute();

        $this->setMockedServices($this->sc, $mocks);
        $this->command = $this->sc->getService('api_command_'.$route['command']);
        $method = $route['method'];
        $allowed = $this->command->isAllowed();
        if (!$allowed) {
            throw new Exception('Calling this command was not allowed, isAllowed() returned false');
        }
        if (is_string($allowed)) {
            $method = $allowed;
        }
        $method = 'execute'.ucfirst($method);
        $this->command->{$method}();
        $ext = isset($route['view']['class'])
            ? $route['view']['class'] : $request->getExtension();
        $response->viewParams = array_merge($route['view'], $response->viewParams);

        $view = $this->sc->$ext;
        $view->setResponse($response);
        $view->dispatch($this->command->getData());

        $this->removeUploadedFiles();
        return $this->loadResponse($response, $ext);
    }

    /**
     * Loads the response into the DOM.
     * May be overwritten in implementations where the response
     * is not expected to be XML.
     * @param api_response $response
     * @param string $extension
     * @return string|array response text or command's data property if ext is json
     */
    protected function loadResponse($response, $extension = "xml") {
        $resp = $response->getContent();
        $this->responseText = $resp;
        if ($extension == 'xml' || $extension == 'html') {
            $this->responseDom = new DOMDocument();
            $method = 'load'.$extension;
            $this->responseDom->$method($resp);
            $this->assertIsA($this->responseDom, 'DOMDocument',
                "The view didn't output valid XML. (%s)");
        }
        if ($extension == 'json') {
            return $this->command->getData();
        }
        return $this->responseText;
    }

    /**
     * Takes all paramaters form the POST array whose value's
     * start with an `@' and interprets those as file uploads.
     * This is compatible with the way curl handles uploads.
     *
     * Example usage with file uploads:
     * \code
     * $this->post('test', array(), array(
     *     'Type' => 'IMAGE',
     *     'File' => '@vw_golf.jpg',
     * ));
     * \endcode
     *
     * This passes a normal POST parameter "Type" and additionally
     * uploads the picture vw_golf.jpg (which has to exist in the
     * current working directory for that example).
     */
    private function uploadFiles(&$post, &$files) {
        foreach ($post as $key => $value) {
            if (is_string($value) && strlen($value) >= 1 && $value[0] == '@') {
                $orig_file = substr($value, 1);
                $upload_file = '';
                if (file_exists($orig_file)) {
                    $upload_file = sys_get_temp_dir() . '/_file_' . count($files);
                    copy($orig_file, $upload_file);
                }

                // Get MIME type
                if (function_exists('finfo_open')) {
                    $finfo = finfo_open(FILEINFO_MIME);
                    $type = finfo_file($finfo, $orig_file);
                    finfo_close($finfo);
                } else if (function_exists('mime_content_type')) {
                    $type = mime_content_type($orig_file);
                } else {
                    $type = '';
                }

                // File upload
                $files[$key] = array(
                    'name' => basename($orig_file),
                    'type' => $type,
                    'size' => filesize($orig_file),
                    'tmp_name' => $upload_file,
                    'error' => 0,
                );
                unset($_POST[$key]);
            }
        }
    }

    /**
     * Remove all uploaded files from the current request.
     */
    private function removeUploadedFiles() {
        foreach ($_FILES as $key => $arr) {
            if (is_file($arr['tmp_name'])) {
                unlink($arr['tmp_name']);
            }
        }
    }

    /**
     * Constructs the correct URI for the given route path.
     * @param $route string: Relative URL from the application root.
     * @param $lang string: Language to include in the path.
     */
    protected function getURI($route, $lang = 'de') {
        return API_HOST . $lang . API_MOUNTPATH . substr($route, 1);
    }

    /**
     * Evaluates an xpath expression and returns the result or a DOMNodeList of all the matched nodes
     */
    protected function evaluateXPath($xpath) {
        return api_helpers_xpath::evaluate($this->responseDom, $xpath);
    }

    /**
     * Evaluates whether an xpath expression returns true
     */
    protected function assertXPathTrue($xpath, $message = null) {
        $this->assertTrue($this->evaluateXPath($xpath), $message);
    }

    /**
     * Constructs the correct path relative to the root of the host for
     * the given route path. Prepends the mount path and language.
     * @param $route string: Relative URL from the application root.
     * @param $lang string: Language to include in the path.
     */
    protected function getPath($route, $lang = 'de') {
        return '/' . $lang . API_MOUNTPATH . substr($route, 1);
    }

    /**
     * Gets the DOM node matching the given XPath expression.
     * @param $xpath string: XPath expression to return node for.
     * @see api_helpers_xpath::getNode()
     */
    protected function getNode($xpath) {
        return api_helpers_xpath::getNode($this->responseDom, $xpath);
    }

    /**
     * Asserts that the given node exists.
     * @param $xpath string: XPath expression to test.
     * @param $message string: Message to output in case of failure.
     */
    public function assertNode($xpath, $message = null) {
        if ($message != null) {
            $message = "$message :: ";
        }
        $this->assertNotNull($this->getNode($xpath), "{$message}No node found for $xpath");
    }

    /**
     * Asserts that the given node does not exist.
     * @param $xpath string: XPath expression to test.
     * @param $message string: Message to output in case of failure.
     */
    public function assertNotNode($xpath, $message = null) {
        if ($message != null) {
            $message = "$message :: ";
        }
        $this->assertNull($this->getNode($xpath), "{$message}Node found for $xpath but none was expected.");
    }

    /**
     * Gets the first result of the current page by XPath.
     * @param $xpath string: XPath expression to return text for.
     * @see api_helpers_xpath::getText()
     */
    public function getText($xpath) {
        return api_helpers_xpath::getText($this->responseDom, $xpath);
    }

    /**
     * Asserts that the text retrieved by an XPath expression matches.
     * @param $xpath string: XPath expression to test.
     * @param $expected string: Expected value returned by the XPath
     *                          expression.
     * @param $message string: Message to output in case of failure.
     */
    public function assertText($xpath, $expected, $message = '%s') {
        return $this->assertEqual($expected, $this->getText($xpath), $message);
    }

    /**
     * Gets the first result of the current page by XPath.
     * @param $xpath string: XPath expression to return attribute for. The
     *                       XPath expression must contain two components
     *                       separated by `a'. First the expression to find
     *                       the node, then the attribute to return the value
     *                       for.
     * @see api_helpers_xpath::getAttribute()
     */
    public function getAttribute($xpath) {
        return api_helpers_xpath::getAttribute($this->responseDom, $xpath);
    }

    /**
     * Asserts that the attribute retrieved by an XPath expression matches.
     * @param $xpath string: XPath expression.
     *                       @see api_testing_case_functional::getAttribute().
     * @param $expected string: Expected value returned by the XPath
     *                          expression.
     * @param $message string: Message to output in case of failure.
     */
    public function assertAttribute($xpath, $expected, $message = '%s') {
        return $this->assertEqual($expected, $this->getAttribute($xpath), $message);
    }

    /**
     * Opens the rendered HTML directly in the browser.
     */
    protected function openInBrowser() {
        $responseString = $this->responseDom->saveXML();
        $file = tempnam(sys_get_temp_dir(), 'okapitest') . '.html';
        file_put_contents($file, $responseString);
        if( strtoupper (substr(PHP_OS, 0,3)) == 'WIN' ) {
            throw new Exception("api_testing_case_functional::openInBrowser does not work on Windows.");
        }
        system("python -c \"import webbrowser; webbrowser.open('" . $file . "');\"");
    }

}
