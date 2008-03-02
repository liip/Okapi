<?php
/**
 * View which outputs the DOM received from the command directly.
 */
class api_views_xml extends api_views_common {
    /**
     * Outputs the XML DOM directly without any modifications. The
     * exceptions are not output.
     * @param $data DOMDocument: DOM document to transform.
     * @param $exceptions array: Array of exceptions merged into the DOM.
     * @todo Output exceptions as well.
     */
    public function dispatch($data, $exceptions = null) {
        $data = $this->getDom($data, $exceptions);
        
        $this->setXMLHeaders();
        $this->response->send();
        echo $data->saveXML();
        return;
    }
}
