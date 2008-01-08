<?php
$m = new api_routing();
$m->route('/command/:method')
  ->params(array(
      'command' => 'nocommand',
      'view' => array('xsl' => 'command.xsl')));
?>
