<?php
final class api_views_xmlhead extends api_views_default {
    public function __construct($route) {
        parent::__construct($route);
    }

    protected function setHeaders() {
        $this->setXMLHeaders();
    }
}

