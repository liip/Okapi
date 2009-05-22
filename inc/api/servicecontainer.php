<?php

class api_servicecontainer extends sfServiceContainer
{
  protected $shared = array();

  protected function getRoutingcontainerService()
  {
    $instance = new api_routingcontainer($this->getRoutingService());

    return $instance;
  }

  protected function getRoutingService()
  {
    if (isset($this->shared['routing'])) return $this->shared['routing'];

    $instance = new api_routing();

    return $this->shared['routing'] = $instance;
  }

  protected function getControllerService()
  {
    if (isset($this->shared['controller'])) return $this->shared['controller'];

    $instance = new api_controller();

    return $this->shared['controller'] = $instance;
  }

  protected function getRequestService()
  {
    if (isset($this->shared['request'])) return $this->shared['request'];

    $instance = new api_request();

    return $this->shared['request'] = $instance;
  }

  protected function getResponseService()
  {
    if (isset($this->shared['response'])) return $this->shared['response'];

    $instance = new api_response();

    return $this->shared['response'] = $instance;
  }
}
