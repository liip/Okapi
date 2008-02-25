<?php
/**
* Default view class. 
*
* Does xsl transformations and dispatches output
*
* @author   Silvan Zurbruegg
*/

class api_views_default extends api_views_common {
    
    /**
    * Xsl dom document
    *
    * @var      object
    */
    protected $xsldom= null;
    
    
    /**
    * Xsl processor instance
    *
    * @var      object
    */
    protected $xslproc = null;
    
    
    /**
    * Whether xml prolog should be included in output
    *
    * @var      bol
    */
    protected $omitXmlDecl = true;
    
    private $xslfile = '';
    
    public function __construct($route) {
        parent::__construct($route);
       
    }
    
    
    /**
    * Output transformed xml
    *
    * @param    object  xmldom  dom document
    * @see      api_views_default::prepare()
    * @return   void
    */
    public function dispatch($data, $exceptions = null) {
		if ($this->state != API_STATE_READY) {
            $this->prepare();
        }
        
        
        
        $xmldom = $this->getDom($data, $exceptions);
        // ?XML=1 trick
        if ($this->request->getParam('XML') == '1') {
            $this->setXMLHeaders();
            // Ported from popoon: mozilla does not display the XML neatly, if there's a xhtml namespace in it, so we spoof it here (mainly used for XML=1 purposes)
            print str_replace("http://www.w3.org/1999/xhtml","http://www.w3.org/1999/xhtml#trickMozillaDisplay", $xmldom->saveXML());
            $this->sendResponse();
            return;
        }
        if ($xmldom instanceof DOMDocument && $this->xsldom && $this->xslproc) {
            $xml = @$this->xslproc->transformToDoc($xmldom);
            if ($xml instanceof DOMDocument) {
                $this->transformI18n($this->request->getLang(), $xml);
                
                $xmlstr = $xml->saveXML();
                if ($this->omitXmlDecl) {
                    $xmlstr = $this->cleanXml($xmlstr);
                }
                
                $this->contentLength = strlen($xmlstr);
                $this->setHeaders();
                echo $xmlstr;
                $this->sendResponse();
                return;
                
            } else {
                throw new api_exception_XsltParseError(api_exception::THROW_FATAL, $this->xslfile,
                        nl2br(var_export(libxml_get_errors(), true)));
            }
        }
    }
    
    
    protected function getDom($data, $exceptions) {
        $xmldom = null;
        
        // Use DOM or load XML from string or array.
        if ($data instanceof DOMDocument) {
            $xmldom = $data;
        } else if (is_string($data) && !empty($data)) {
            $xmldom = DOMDocument::loadXML($data);
        } else if (is_array($data)) {
            $xmldom = DOMDocument::loadXML("<command/>");
            api_helpers_xml::array2dom($data, $xmldom, $xmldom->documentElement);
        }
        
        if (count($exceptions) > 0) {
             $this->mergeExceptions($xmldom, $exceptions);
        }
        
        return $xmldom;
    }
    
    /**
    * Merges collected Exception info with xml
    *
    * @param    DOMDocument        xmldom        domxml object
    * @param    array            exceptions    exception info
    * @return    void
    */
    protected function mergeExceptions(&$xmldom, $exceptions) {
        if (count($exceptions) > 0) {
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

        return null;
    }
    
    
    
    
    /**
     * Removes content from the XML which will cause problems in
     * browsers.
     *
     * Called from dispatch right before sending out the response body.
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
    * Prepares for xsl transformation
    *
    * @see      api_views_default::dispatch()
    * @return   bool
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
            $this->contentType = $attrib['contenttype'];
        } else {
            $this->contentType = $this->contentTypeDefault;
        }

        if (isset($attrib['encoding']) && !empty($attrib['encoding'])) {
            $this->contentEncoding = $attrib['encoding'];
        } else {
            $this->contentEncoding = $this->contentEncodingDefault;
        }
        
        $this->xslfile='';
        if (!isset($attrib['theme'])) {
            $attrib['theme'] = 'default';
        }
        
        if (isset($attrib['theme'])) {
            $this->xslfile = API_THEMES_DIR.$attrib['theme']."/".$attrib['xsl'];
        } 
        if ($this->request->getParam('XML') == '1') {
            $this->contentType = 'text/xml';
            $this->state = API_STATE_READY;
            return TRUE;
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
     * Set XSLT parameters which are passed in.
     *
     * @param $xslproc XsltProcessor: The XSLT object (instance of XsltProcessor)
     * @param $attrib array:  The view attributes from the route.
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
    
    protected function setHeaders() {
        $this->response->setContentType($this->contentType);
        $this->response->setCharset($this->contentEncoding);
    }
    
    /**
     * Sends the response using the methods of api_response.
     */
    protected function sendResponse() {
        $this->response->send();
    }
}
