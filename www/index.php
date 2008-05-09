<?php
require_once dirname(__FILE__) . '/../inc/api/init.php';
api_init::start();

require_once API_LIBS_DIR.'controller.php';
$ctrl = new api_controller();
$ctrl->process();
