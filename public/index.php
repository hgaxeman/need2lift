<?php

declare(strict_types=1);

use App\Bridge\SymfonyFrontController;

chdir(dirname(__DIR__));

require __DIR__ . '/../vendor/autoload.php';

(new SymfonyFrontController())->handle()->send();
