<?php

$m = new api_routing();

$m->route('/openid/response/:cmd')
    ->config(array(
        'command' => 'openid_response_{cmd}',
        'method'  => 'index',
    ));
