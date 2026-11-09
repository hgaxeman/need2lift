<?php

declare(strict_types=1);

namespace App\Bridge;

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Boots the Symfony kernel for a bridged request and returns its response.
 */
final class SymfonyFrontController
{
    public function handle(): Response
    {
        $projectDir = dirname(__DIR__, 2);
        if (is_file($projectDir . '/.env')) {
            (new Dotenv())->bootEnv($projectDir . '/.env');
        }

        $env = $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'dev';
        $debug = (bool) ($_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? true);

        $kernel = new Kernel(is_string($env) ? $env : 'dev', $debug);
        $request = Request::createFromGlobals();
        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        return $response;
    }
}
