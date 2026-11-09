<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HelloController
{
    #[Route('/api/hello', name: 'api-hello')]
    public function hello(): Response
    {
        return new Response(
            sprintf('Hello from PHP %s', PHP_VERSION),
            Response::HTTP_OK,
            [
                'Content-Type' => 'text/plain; charset=utf-8',
                'X-Need2lift-Engine' => 'symfony',
            ]
        );
    }
}
