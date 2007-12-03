<?php
require_once 'inc/api/init.php';
api_init::start();
error_reporting(E_ALL);

$ctrl = new api_controller();
$ctrl->process();
?>
