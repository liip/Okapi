<?php
/**
 * Default view class. Does XSLT transformations and dispatches output.
 *
 * @author   Silvan Zurbruegg
 */
class api_views_default extends api_views_common {
    /** DOMDocument: XSLT document used for the transformations. */
    protected $xsldom = null;
    
    /** XsltProcessor: Instantiated XSLT processor. */
    protected $xslproc = null;
    
    /**
     * bool: If set to true, XML data which gives problem in HTML output
     * is stripped from output using api_views_default::cleanXml().
     */
    protected $omitXmlDecl = true;
    
    /** string: XSLT file used for transforming the output. */
    protected $xslfile = '';
    
    /**
     * Outputs the responses by transforming it using the loaded XSLT.
     * If the XML request parameter is set to 1, the DOM is output
     * directly.
     *
     * @param $data mixed: See api_views_default::getDom()
     * @param $exceptions array: Array of exceptions merged into the DOM.
     * @exception api_exception_XsltParseError if the XSLT transformation
     *            did not return a valid XML document.
     */
    public function dispatch($data, $exceptions = null) {
        if ($this->state != API_STATE_READY) {
            $this->prepare();
        }
        
        $xmldom = $this->getDom($data, $exceptions);
        
        // ?XML=1 trick
        if ($this->request->getParam('XML') == '1') {
            $this->setXMLHeaders();
            /* Ported from popoon: mozilla does not display the XML
               neatly, if there's a xhtml namespace in it, so we spoof it
               here (mainly used for XML=1 purposes) */
            print str_replace("http://www.w3.org/1999/xhtml","http://www.w3.org/1999/xhtml#trickMozillaDisplay", $xmldom->saveXML());
            $this->sendResponse();
            return;
        }
        
        if (! $xmldom instanceof DOMDocument && $this->xsldom && $this->xslproc) {
            return;
        }
        
        $xml = @$this->xslproc->transformToDoc($xmldom);
        if ($xml instanceof DOMDocument) {
            $this->transformI18n($this->request->getLang(), $xml);
            
            $xmlstr = $xml->saveXML();
            if ($this->omitXmlDecl) {
                $xmlstr = $this->cleanXml($xmlstr);
            }
            
            $this->setHeaders();
            echo $xmlstr;
            $this->sendResponse();
            return;
        } else {
            throw new api_exception_XsltParseError(api_exception::THROW_FATAL, $this->xslfile,
                    nl2br(var_export(libxml_get_errors(), true)));
        }
    }
    
    /**
     * Returns a merged DOMDocument of the given data and exception list.
     * 
     * Data can be any of these three things:
     *    - DOMDocument: Used directly
     *    - string: Treated as an XML string and loaded into a DOMDocument
     *    - array: Converted to a DOMDocument using api_helpers_xml::array2dom
     * 
     * The exceptions are merged into the DOM using the method
     * api_views_default::mergeExceptions()
     * 
     * @param $data mixed: See above
     * @param $exceptions array: Array of exceptions merged into the DOM.
     * @return DOMDocument: DOM with exceptions
     */
    protected function getDom($data, $exceptions) {
        $xmldom = null;
        
        // Use DOM or load XML from string or array.
        if ($data instanceof DOMDocument) {
            $xmldom = $data;
        } else if (is_string($data) && !empty($data)) {
            $xmldom = DOMDocument::loadXML($data);
        } else if (is_array($data)) {
            @$xmldom = DOMDocument::loadXML("<command/>");
            api_helpers_xml::array2dom($data, $xmldom, $xmldom->documentElement);
        }
        
        if (count($exceptions) > 0) {
             $this->mergeExceptions($xmldom, $exceptions);
        }
        
        return $xmldom;
    }
    
    /**
     * Merges exceptions into the DOM Document.
     * Appends a node <exceptions> to the root node of the given DOM
     * document.
     *
     * @param $xmldom DOMDocument: Response DOM document.
     * @param $exceptions array: List of exceptions
    */
    protected function mergeExceptions(&$xmldom, $exceptions) {
        if (count($exceptions) == 0) {
            return;
        }
        
        $exceptionsNode = $xmldom->createElement('exceptions');
        foreach($exceptions as $exception) {
            $exceptionNode = $xmldom->createElement('exception');
            foreach($exception->getSummary() as $name => $value) {
                $child = $xmldom->createElement($name);
                $child->nodeValue = $value;
                $exceptionNode->appendChild($child);
            }
            $exceptionsNode->appendChild($exceptionNode);
        }
        
        $xmldom->documentElement->appendChild($exceptionsNode);
    }
    
    /**
     * Removes content from the XML which will cause problems in
     * browsers.
     * Called from dispatch right before sending out the response body.
     *
     * @param $xmlstr string: XML string
     */
    protected function cleanXml($xmlstr) {
        $xmlstr = preg_replace("#^<\?xml.*\?>#","", $xmlstr);
        $xmlstr = preg_replace("#<!\[CDATA\[\W*\]\]>#","",$xmlstr);
        // strip CDATA just after <script>
        $xmlstr = preg_replace("#(<script[^>]*>)\W*<!\[CDATA\[#","$1",$xmlstr);
        // strip ]]> just before </script>
        $xmlstr =  preg_replace("#\]\]>\W*(</script>)#","$1",$xmlstr);
        // strip CDATA just after <style>
        $xmlstr = preg_replace("#(<style[^>]*>)\W*<!\[CDATA\[#","$1",$xmlstr);
        // strip ]]> just before </style>
        $xmlstr =  preg_replace("#\]\]>\W*(</style>)#","$1",$xmlstr);
        
        // Strip namespaces
        $xmlstr = preg_replace('#(<[^>]*)xmlns=""#', "$1", $xmlstr);
        $xmlstr = preg_replace('#(<[^>]*)xmlns:i18n[0-9]*="http://apache.org/cocoon/i18n/2.1"#', "$1", $xmlstr);
        $xmlstr = preg_replace('#(<[^>]*)xmlns="http://www.w3.org/1999/xhtml"#', "$1", $xmlstr);
        $xmlstr = preg_replace('#(<[^>]*)i18n[0-9]*:attr="[^"]+"#', "$1", $xmlstr);
        
        return trim($xmlstr);
    }
    
    /**
     * Prepares for the XSLT transformation. Loads the XSLT stylesheet.
     * 
     * @exception api_exception_FileNotFound if the XSLT stylesheet does
     *            not exist.
     * @exception api_exception_XmlParseError if the XSLT stylesheet
     *            does not contain valid XML.
     */
    public function prepare() {
        $defaults = array('theme' => 'default', 'css' => 'default',
                          'view' => 'default', 'passdom' => 'no');
        $attrib = $this->route['view'];
        $attrib = array_merge($defaults, $attrib);
        
        if (!isset($attrib['xsl'])) {
            die("No XSLT stylesheet was specified for this route.");
        }
        
        if (isset($attrib['contenttype']) && !empty($attrib['contenttype'])) {
            $this->response->setContentType($attrib['contenttype']);
        }
        
        if (isset($attrib['encoding']) && !empty($attrib['encoding'])) {
            $this->response->setCharset($attrib['encoding']);
        }
        
        $this->xslfile = '';
        if (!isset($attrib['theme'])) {
            $attrib['theme'] = 'default';
        }
        
        if (isset($attrib['theme'])) {
            $this->xslfile = API_THEMES_DIR.$attrib['theme']."/".$attrib['xsl'];
        } 
        if ($this->request->getParam('XML') == '1') {
            $this->setXMLHeaders();
            $this->state = API_STATE_READY;
            return true;
        }
        
        $this->xsldom = new DOMDocument();
        if(!$this->xsldom->load($this->xslfile)) {
            if(!file_exists($this->xslfile)) {
                throw new api_exception_FileNotFound(api_exception::THROW_FATAL, $this->xslfile);
            }
            throw new api_exception_XmlParseError(api_exception::THROW_FATAL, $this->xslfile);
        }
        
        if ($this->xsldom instanceof DOMDocument) {
            $this->xslproc = new XsltProcessor();
            $this->xslproc->importStylesheet($this->xsldom);
            
            $this->setXslParameters($this->xslproc, $attrib);
            $this->xslproc->registerPHPFunctions();
            $this->state = API_STATE_READY;
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Set XSLT parameters passed in to the stylesheet.
     *
     * @param $xslproc XsltProcessor: The XSLT object.
     * @param $attrib array: The view attributes from the route.
     */
    protected function setXslParameters($xslproc, $attrib) {
        $this->xslproc->setParameter("", "webroot", API_WEBROOT);
        $this->xslproc->setParameter("", "webrootStatic", API_WEBROOT_STATIC);
        $this->xslproc->setParameter("", "mountpath", API_MOUNTPATH);
        $this->xslproc->setParameter("", "theme", $attrib['theme']);
        $this->xslproc->setParameter("", "themeCss", $attrib['css']);
        $this->xslproc->setParameter("", "lang", $this->request->getLang());
        $this->xslproc->setParameter("", "projectDir", API_PROJECT_DIR);
        
        if(isset($attrib['xslproc']) && is_array($attrib['xslproc']) ) {
            foreach($attrib['xslproc'] as $key => $val) {
                $this->xslproc->setParameter("", $key, $val);
            }
        }
    }
    
    /**
     * Sends the response using the methods of api_response.
     */
    protected function sendResponse() {
        $this->response->send();
    }
}
