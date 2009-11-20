<?php
/**
 * Response class which handles outputting the header and body.
 *
 * Output buffering is used and the buffer is flushed only when calling
 * api_response::send().
 */
class mock_response extends api_response {
    /** Headers to send to the client. */
    protected $headers = array();
    /** Cookies to set. */
    protected $cookies = array();

    /**
     * Constructor. Turns on output buffering.
     */
    public function __construct($buffering = false) {
        parent::__construct($buffering);
    }

    /**
     * Re-implements send of api_response with a no-op.
     */
    public function send() {
        // NOOP
    }

    /**
     * Catch redirects and thrown a testing exception for that.
     */
    public function redirect($to, $status=301) {
        throw new api_testing_exception("Redirect $status => $to");
    }

    /**
     * Set a single header. Overwrites an existing header of the same
     * name if it exists.
     * @param $header string: Header name.
     * @param $value string: Value of the header.
     */
    public function setHeader($header, $value) {
        $this->headers[$header] = $value;
    }

    /**
     * Returns an associative array of all set headers.
     * @return hash: All headers which have been set.
     */
    public function getHeaders() {
        $headers = $this->headers;

        if (!is_null($this->contenttype)) {
            $ct = $this->contenttype;
            if (!is_null($this->charset)) {
                $ct .= '; charset=' . $this->charset;
            }
            $headers['Content-Type'] = $ct;
        }

        return $headers;
    }

    /**
     * Sets a cookie with the given value.
     * Overwrites an existing Cookie if it's the same name
     *
     * @param string Name of the cookie
     * @param string Value of the cookie
     * @param int Maxage of the cookie
     * @param string Path where the cookie can be used
     * @param string Domain which can read the cookie
     * @param bool Secure mode?
     * @param bool Only allow HTTP usage?
     */
    public function setCookie($name, $value = '', $maxage = 0, $path = '', $domain = '',
                              $secure = false, $HTTPOnly = false) {
        $this->cookies[rawurlencode($name)] = rawurlencode($value)
                                            . (empty($domain) ? '' : '; Domain='.$domain)
                                            . (empty($maxage) ? '' : '; Max-Age='.$maxage)
                                            . (empty($path) ? '' : '; Path='.$path)
                                            . (!$secure ? '' : '; Secure')
                                            . (!$HTTPOnly ? '' : '; HttpOnly');
    }

    /**
     * Returns an associative array of all set cookies.
     * @return hash: All Cookies which have been set.
     */
    public function getCookies() {
        return $this->cookies;
    }
}
