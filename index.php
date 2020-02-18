<?php
require './vendor/autoload.php';
define('APPLICATION_PATH', __DIR__ . '/demo/application/');
Tiny\Tiny::createApplication(APPLICATION_PATH, './demo/application/config/profile.php')->run();
?>