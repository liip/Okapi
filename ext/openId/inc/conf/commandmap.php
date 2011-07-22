<?php

$m = new api_routing();

// route for openID client returnUrl
$m->route('/openid/response/:service/:cmd')
        ->config(array(
            'command' => 'openid_response_{service}',
            'method'  => '{cmd}',
));

// route for the openid discovery process
$m->route('/member')
        ->config(array(
            'command' => 'openid_provider',
            'method'  => 'discover',
            'view'    => array('xsl' => 'openid_discovery.xsl')
));

// don't know if there any use of this. Currently it is an alias for the /member route.
$m->route('/member/+guid')
        ->config(array(
            'command' => 'openid_provider',
            'method'  => 'find',
            'view'    => array('xsl' => 'openid_discovery.xsl')
));

// route to sent the openid authentication request to
$m->route('/server')
        ->config(array(
            'command' => 'openid_provider',
            'method'  => 'authenticate',
            'view'    => array('xsl' => 'openid_authenticate.xsl')
));