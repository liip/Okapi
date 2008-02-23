<?php
$m = new api_routing();
$m->route('*')
  ->config(array(
    'command' => 'default',
    'view' => array('xsl' => 'default.xsl')));
