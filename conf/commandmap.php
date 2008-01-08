<?php
$m = new api_routing();
$m->route('/command/:method')
  ->config(array(
      'command' => 'nocommand',
      'view' => array('xsl' => 'command.xsl')));
?>
