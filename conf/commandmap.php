<?php
$m = new api_routing();

/** routes used by testing */
$m->route('/command/:method')
  ->config(array(
      'command' => 'nocommand',
      'view' => array('xsl' => 'command.xsl')));

$m->route('/helloworld/:method')
    ->config(array(
        'command' => 'helloworld',
        'view' => array('xsl' => 'helloworld.xsl')));

$m->route('/namespacetest/:namespace/:command/:method')
    ->config(array(
        'command' => 'nocommand',
        'view' => array('xsl' => 'command.xsl')));

$m->route('/noviewtest/')
    ->config(array(
        'command' => 'nocommand',
        'view' => array('class' => 'notexisting',
        'xsl' => 'command.xsl')));

$m->route('/noxslttest/')
    ->config(array(
        'command' => 'nocommand'
      ));

/** default hallo world example */
$m->route('/helloworld')
    ->config(array(
        'command' => 'default',
        'view' => array ('xsl' => 'default.xsl')
    ));

