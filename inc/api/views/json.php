<?php
/**
*/
class api_views_json extends api_views_common {
    public function setHeader() {
        $this->response->setContentType('application/json');
        $this->response->setCharset('utf-8');
    }

    public function dispatch($data, $exceptions = null) {
        if (is_string($data)) {
            // assume that strings are already json encoded
            echo $data;
        } elseif ($data instanceof DOMDocument) {
            $json = api_helpers_json::xmlStringToJson($data->saveXML());
            echo $json;
        } elseif (is_array($data) || is_object($data)) {
            if (function_exists('json_encode')) {
                echo json_encode($data);
            } else {
                // fallback if json extension is missing (PHP < 5.2.0)
                // requires the Zend Framework
                echo Zend_Json::encode($data);
            }
        } else {
            // FIXME: huh? what's this?
            throw new api_exception_queryserver(array());
            return;
        }

        $this->setHeader();
        $this->response->send();
    }
}
