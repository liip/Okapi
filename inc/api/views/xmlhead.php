<?php
/**
 * View which sets XML content type headers.
 * @author   Silvan Zurbruegg
 */
class api_views_xmlhead extends api_views_default {
    protected function setHeaders() {
        parent::setHeaders();
        $this->setXMLHeaders();
    }
}

