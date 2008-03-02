<?php
/**
 * View which sets text/plain content type headers.
 * @author   Silvan Zurbruegg
 */
class api_views_plain extends api_views_default {
    /**
     * Sends text/plain Content-type
     */
    protected function setHeaders() {
        parent::setHeaders();
        $this->response->setContentType('text/plain');
    }
}
