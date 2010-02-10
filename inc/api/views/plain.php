<?php
/**
*/
class api_views_plain extends api_views_common {
    public function setHeader() {
        $this->response->setContentType('text/plain');
        $this->response->setCharset('utf-8');
    }

    public function dispatch($data, $exceptions = null) {
        $this->setHeader();
        $this->response->addContent($data);
    }
}
