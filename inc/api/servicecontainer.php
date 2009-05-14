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
}
