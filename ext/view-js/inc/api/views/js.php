<?php
/**
*/

class api_views_js extends api_views_common {
    public function __construct($route) {
        parent::__construct($route);
    }

    public function setHeader() {
        $this->response->setContentType('text/javascript');
        $this->response->setCharset('utf-8');
    }

    public function dispatch($data, $exceptions = null) {
        if (is_string($data)) {
            // assume that strings are already javascript code
            echo $data;
        } elseif (is_array($data) || is_object($data)) {
            // requires the Zend Framework
            // for example add the following to your projects svn:externals
            // localinc/Zend http://framework.zend.com/svn/framework/tag/release-1.5.1/library/Zend/
            if (empty($data['varname'])) {
                echo 'data = '.Zend_Json::encode($data).';';
            } else {
                echo $data['varname'].' = '.Zend_Json::encode((empty($data['data']) ? '' : $data['data'])).';';
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
