<?php
require_once dirname(__FILE__) . '/inc/api/init.php';
api_init::start();

require_once dirname(__FILE__) . '/inc/api/controller.php';
$ctrl = new api_controller();
$ctrl->process();
