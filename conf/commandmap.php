<?php
$m = new api_routing();
$m->add('/command/:method', array(
    'command' => 'nocommand',
    'view' => array('xsl' => 'command.xsl'),
));
?>
