<?php

class api_sf_servicecontainerdumperphp extends sfServiceContainerDumperPhp {



    public function dump(array $options = array()) {
        $class = isset($options['class']) ? $options['class'] : 'ProjectServiceContainer';
        $extends = isset($options['extends']) ? $options['extends'] : 'sfServiceContainer';
        return $this->startClass2($class, $extends) . $this->addServices() . $this->endClass();
    }

    protected function startClass2($class, $extends) {
        $code = <<<EOF
<?php

class $class extends $extends
{
  protected \$shared = array();

EOF;

        if ($this->container->getParameters()) {
            $code .= <<<EOF

  public function __construct()
  {
    parent::__construct(\$this->getDefaultParameters());
  }

EOF;
        }

        return $code;
    }
}
