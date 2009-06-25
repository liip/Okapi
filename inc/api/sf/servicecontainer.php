<?php

class api_sf_servicecontainer extends sfServiceContainer {

    protected $shared = array();

    public function setService($id, $service) {
        if ($id == 'route') {
            $this->shared['route'] = $service;
        }
        parent::setService($id, $service);
    }
}

