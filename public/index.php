<?php

use Zend\Mvc\Application;
use RuntimeException;

chdir(dirname(__DIR__));

require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/application.config.php';
if (!is_array($config)) {
    throw new RuntimeException('Application config must return an array');
}

Application::init($config)->run();
