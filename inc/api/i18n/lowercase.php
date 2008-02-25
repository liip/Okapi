<?php
/**
 * I18n retriever same as api_i18n_default but key names in the
 * catalog are expected to be all lower case.
 */
class api_i18n_lowercase extends api_i18n_default {
    public function get($key) {
        $key = mb_strtolower($key, 'UTF-8');
        return parent::get($key);
    }
}
