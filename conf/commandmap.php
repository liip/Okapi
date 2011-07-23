<?php
$m = new api_routing();
/** default hallo world example */
$m->route('/')
   ->config(array(
       'command' => 'nocommand',
       'view' => array ('xsl' => 'default.xsl')));

/** routes used by testing */
$m->route('/command/:method')
  ->config(array(
      'command' => 'nocommand',
      'view' => array('xsl' => 'command.xsl')));

$m->route('/helloworld')
    ->config(array(
        'command' => 'helloworld',
        'view' => array('xsl' => 'helloworld.xsl')));

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
        'command' => 'nocommand'));

