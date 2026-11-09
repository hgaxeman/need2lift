<?php

declare(strict_types=1);

use App\Bridge\SymfonyFrontController;
use App\Bridge\SymfonyPathList;
use RuntimeException;
use Zend\Mvc\Application;

chdir(dirname(__DIR__));

require __DIR__ . '/../vendor/autoload.php';

$bridgePaths = require __DIR__ . '/../config/symfony_bridge_paths.php';
if (!is_array($bridgePaths)) {
    throw new RuntimeException('Symfony bridge paths must return an array');
}

/** @var list<string> $bridgePaths */
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
if (!is_string($requestUri)) {
    $requestUri = '/';
}

if ((new SymfonyPathList($bridgePaths))->matches($requestUri)) {
    (new SymfonyFrontController())->handle()->send();

    return;
}

$config = require __DIR__ . '/../config/application.config.php';
if (!is_array($config)) {
    throw new RuntimeException('Application config must return an array');
}

Application::init($config)->run();
