<?php
/**
*/
class api_views_html extends api_views_common {
    public function setHeader() {
        $this->response->setContentType('text/html');
        $this->response->setCharset('utf-8');
    }

    public function dispatch($data, $exceptions = null) {
        if (is_array($data)) {
            echo '<pre>';
            print_r($data);
        }
        echo $data;

        $this->setHeader();
        $this->response->send();
    }
}
